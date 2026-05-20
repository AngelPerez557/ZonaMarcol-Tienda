<?php

class AuthController
{
    // Carga la vista del formulario de login
    public function index(): void
    {
        if (Auth::isLoggedIn()) {
            header('Location: ' . APP_URL . 'Dashboard/index');
            exit();
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        $extraCss = ['Content/Dist/css/login.css'];

        require_once VIEWS_PATH . 'Auth' . DS . 'login.php';
    }

    // Procesa el formulario de login
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        // F-18 — Validación CSRF en login. Previene Login CSRF
        // (atacante forzando a la víctima a loguearse en cuenta del atacante).
        if (!Csrf::validate()) {
            $_SESSION['login_error'] = 'Sesión inválida. Por favor recargá e intentá de nuevo.';
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::check($ip)) {
            $minutos = RateLimiter::minutosRestantes($ip);
            $_SESSION['login_error'] = "Por seguridad el acceso está bloqueado. Intenta en {$minutos} minuto(s).";
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        // F-16 — sin htmlspecialchars/strip_tags al email.
        // El saneo en input rompía caracteres válidos (& en local-parts) y
        // hacía fallar password_verify con passwords que contuvieran < o >.
        // Política: escape on output (la View ya escapa). Trim es suficiente.
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Por favor completá todos los campos.';
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmailOrUsername($email);

        // Verifica que el usuario exista — Found = false si no existe
        if (!$user->Found) {
            RateLimiter::registrarFallo($ip);
            $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        // Verifica la contraseña contra el hash
        if (!password_verify($password, $user->password)) {
            RateLimiter::registrarFallo($ip);
            $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        // Verifica que el usuario esté activo
        if (!$user->isActivo()) {
            $_SESSION['login_error'] = 'Tu cuenta está desactivada. Contactá al administrador.';
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }

        // Obtiene los permisos del rol
        $roleModel = new RoleModel();
        $permisos  = $roleModel->getPermissionsByRole($user->rol_id);

        RateLimiter::limpiar($ip);

        // Inicia la sesión
        Auth::login([
            'id'              => $user->id,
            'nombre'          => $user->nombre,
            'email'           => $user->email,
            'rol_id'          => $user->rol_id,
            'rol_slug'        => $user->rol_slug,
            'permisos'        => $permisos,
            'tour_completado' => (int) ($user->tour_completado ?? 0),
        ]);

        // F-05 / F-17 — session_token ahora se gestiona dentro de Auth::login().
        // El controller solo orquesta: ya no toca BD ni session_token directamente.

        header('Location: ' . APP_URL . 'Dashboard/index');
        exit();
    }

    // Cierra la sesión.
    // F-09 — Requiere token CSRF (en POST o en GET via ?csrf=...).
    // Previene logout CSRF: un <img src="/Auth/logout"> sin token no desloguea.
    public function logout(): void
    {
        $token = $_GET['csrf'] ?? $_POST['csrf_token'] ?? '';

        if (!Csrf::validate($token)) {
            // Token inválido — NO cerramos sesión. Redirigimos al Dashboard.
            // Si no hay sesión activa, igual rebota al login por Auth::check.
            header('Location: ' . APP_URL . 'Dashboard/index');
            exit();
        }

        Auth::logout();
    }

    // Sincroniza dark mode con la sesión PHP
    public function darkMode(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $_SESSION['dark_mode'] = isset($data['dark_mode']) && $data['dark_mode'] === true;

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
}