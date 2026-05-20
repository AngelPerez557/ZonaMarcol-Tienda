<?php

/**
 * Auth.php — Manejo centralizado de autenticación y autorización
 *
 * Cubre los hallazgos de Sesión D:
 *   F-05 / F-17 — session_token se genera y persiste DENTRO de Auth::login()
 *                  (antes vivía disperso en AuthController)
 *   F-06        — Auth::check() ya no falla silenciosamente si BD cae:
 *                  loguea el error y permite continuar de forma controlada
 *   F-15        — Auth::require() llama a check() primero. Si no hay sesión,
 *                  redirige al login en vez de mostrar un 403 confuso
 *   F-21        — Permisos refrescados con TTL de 5 min. Si el admin
 *                  revoca permisos, el usuario los pierde sin esperar logout
 */
class Auth
{
    // Clave del array en $_SESSION donde se almacena el usuario autenticado
    private static string $sessionKey = 'user';

    // F-21 — refrescar permisos cada N segundos (5 min por defecto)
    private const PERMISOS_TTL = 300;

    // ─────────────────────────────────────────────
    // AUTENTICACIÓN — LOGIN / LOGOUT
    // ─────────────────────────────────────────────

    // Almacena los datos del usuario en sesión al iniciar sesión correctamente.
    // F-05 / F-17 — incluye generación y persistencia del session_token
    // (antes esto vivía disperso en AuthController::login)
    public static function login(array $userData): void
    {
        // Regenera el ID de sesión para prevenir Session Fixation
        session_regenerate_id(true);

        $_SESSION[self::$sessionKey] = [
            'id'              => $userData['id'],
            'nombre'          => $userData['nombre'],
            'email'           => $userData['email'],
            'rol_id'          => $userData['rol_id'],
            'rol_slug'        => $userData['rol_slug'],
            'permisos'        => $userData['permisos']        ?? [],
            'tour_completado' => $userData['tour_completado'] ?? 0,
            // F-21 — timestamp del último refresh de permisos
            'permisos_at'     => time(),
        ];

        // F-05 / F-17 — Genera y persiste el session_token aquí (no en el Controller)
        $sessionToken = bin2hex(random_bytes(16));
        $_SESSION['session_token'] = $sessionToken;

        try {
            $userModel = new UserModel();
            $userModel->updateSessionToken((int) $userData['id'], $sessionToken);
        } catch (\Throwable $e) {
            // Si BD falla aquí, la sesión queda iniciada pero sin verificación única
            // El próximo Auth::check detectará la inconsistencia
            error_log('[Auth::login] No se pudo persistir session_token: ' . $e->getMessage());
        }
    }

    // Destruye la sesión completamente y redirige al login
    public static function logout(): void
    {
        // Limpia todas las variables de sesión
        $_SESSION = [];

        // Elimina la cookie de sesión del navegador
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destruye la sesión en el servidor
        session_destroy();

        header('Location: ' . APP_URL . 'Auth/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // VERIFICACIÓN DE SESIÓN
    // ─────────────────────────────────────────────

    // Retorna true si hay un usuario autenticado en sesión
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION[self::$sessionKey]['id']);
    }

    // Fuerza autenticación — redirige al login si no hay sesión activa
    // Se llama al inicio de cada método de controlador protegido
    public static function check(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        // ── F-06: Verificación de sesión única con manejo de error robusto ──
        // Antes este try/catch tenía un catch vacío — si BD se caía,
        // la verificación se omitía silenciosamente y sesiones robadas pasaban.
        try {
            $db   = Conexion::getInstance();
            $stmt = $db->prepare("SELECT session_token FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([self::id()]);
            $row  = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si el token en BD no coincide → otro dispositivo inició sesión → logout
            if (!$row || $row['session_token'] !== ($_SESSION['session_token'] ?? '')) {
                self::logout();
            }
        } catch (\Throwable $e) {
            // F-06 — Log explícito en lugar de fallar silencioso.
            // No bloqueamos el flujo (BD caída no debería tirar abajo el sistema)
            // pero el incidente queda registrado para análisis.
            error_log('[Auth::check] Verificación de sesión única falló: ' . $e->getMessage());
        }

        // ── F-21: Refrescar permisos si el TTL expiró ──
        // Si el admin revocó/agregó permisos, se reflejan al siguiente request
        // después de PERMISOS_TTL segundos (sin esperar a que el usuario haga logout).
        self::refreshPermisosIfStale();
    }

    // F-21 — Refresca los permisos del usuario actual desde BD si pasó el TTL
    private static function refreshPermisosIfStale(): void
    {
        $permisosAt = $_SESSION[self::$sessionKey]['permisos_at'] ?? 0;
        if ((time() - (int) $permisosAt) < self::PERMISOS_TTL) {
            return; // Aún válidos en cache
        }

        try {
            $roleId    = (int) ($_SESSION[self::$sessionKey]['rol_id'] ?? 0);
            $roleModel = new RoleModel();
            $permisos  = $roleModel->getPermissionsByRole($roleId);

            $_SESSION[self::$sessionKey]['permisos']    = $permisos;
            $_SESSION[self::$sessionKey]['permisos_at'] = time();
        } catch (\Throwable $e) {
            error_log('[Auth::refreshPermisos] ' . $e->getMessage());
            // Si BD falla, mantenemos los permisos cacheados — no degradamos UX
        }
    }

    // ─────────────────────────────────────────────
    // ACCESO A DATOS DEL USUARIO EN SESIÓN
    // ─────────────────────────────────────────────

    public static function user(): ?array
    {
        return $_SESSION[self::$sessionKey] ?? null;
    }

    public static function get(string $field): mixed
    {
        return $_SESSION[self::$sessionKey][$field] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION[self::$sessionKey]['id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION[self::$sessionKey]['rol_slug'] ?? null;
    }

    // ─────────────────────────────────────────────
    // RBAC — CONTROL DE PERMISOS
    // ─────────────────────────────────────────────

    public static function can(string $permission): bool
    {
        $permisos = $_SESSION[self::$sessionKey]['permisos'] ?? [];
        return in_array($permission, $permisos, true);
    }

    public static function canAny(array $permissions): bool
    {
        $permisos = $_SESSION[self::$sessionKey]['permisos'] ?? [];
        return !empty(array_intersect($permissions, $permisos));
    }

    public static function canAll(array $permissions): bool
    {
        $permisos = $_SESSION[self::$sessionKey]['permisos'] ?? [];
        return empty(array_diff($permissions, $permisos));
    }

    // Fuerza verificación de permiso.
    // F-15 — Ahora llama a check() primero. Si no hay sesión,
    // redirige al login en vez de mostrar 403 sin contexto.
    public static function require(string $permission): void
    {
        // F-15 — primero validar sesión activa
        self::check();

        if (!self::can($permission)) {
            http_response_code(403);

            if (defined('APP_ENV') && APP_ENV === 'development') {
                die("<h2 style='font-family:monospace;color:#c0392b;'>403 | Sin permiso: '{$permission}'</h2>");
            }

            $view403 = VIEWS_PATH . '403' . DS . 'index.php';
            if (file_exists($view403)) {
                require_once $view403;
            } else {
                die('<h2>403 | Acceso denegado.</h2>');
            }
            exit();
        }
    }

    public static function hasRole(string $roleSlug): bool
    {
        return self::role() === $roleSlug;
    }
}
