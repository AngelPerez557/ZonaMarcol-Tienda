<?php

/**
 * RateLimiter.php — Rate limiter persistente en BD con scopes por endpoint
 *
 * Cubre los hallazgos:
 *   F-08 — Datos en BD (tabla rate_limits) en vez de $_SESSION.
 *          Un atacante que borre cookies ya no resetea su contador.
 *   F-23 — Manejo de error explícito si BD falla — se loguea, NO se
 *          bloquea silenciosamente al usuario legítimo (fail open por UX).
 *   F-32 — Sin md5() (era keying débil + innecesario). La IP se usa
 *          directo como parte del PRIMARY KEY compuesto (ip, scope).
 *
 * NUEVO (Etapa B) — Scopes:
 *   Cada endpoint sensible tiene su propio contador. Brute force al
 *   login admin ya no bloquea el login de tienda y viceversa.
 *
 * Política de "fail open" cuando BD falla:
 *   - check() retorna true (permite el intento)
 *   - registrarFallo() loguea pero no rompe el flujo
 *   - Justificación: BD caída es excepcional; bloquear todos los
 *     endpoints públicos en ese escenario es peor que perder protección
 *     temporal. El incidente queda en error_log.
 *
 * Uso típico:
 *   if (!RateLimiter::check($ip, RateLimiter::LOGIN_ADMIN)) {
 *       $min = RateLimiter::minutosRestantes($ip, RateLimiter::LOGIN_ADMIN);
 *       // mostrar error de bloqueo
 *   }
 *   // ... lógica del endpoint ...
 *   RateLimiter::registrarFallo($ip, RateLimiter::LOGIN_ADMIN);   // si falla
 *   RateLimiter::limpiar($ip, RateLimiter::LOGIN_ADMIN);          // si éxito
 */
class RateLimiter
{
    // ─────────────────────────────────────────────
    // SCOPES — nombres canónicos de endpoint
    // ─────────────────────────────────────────────
    public const LOGIN_ADMIN        = 'login_admin';
    public const LOGIN_TIENDA       = 'login_tienda';
    public const REGISTRO_TIENDA    = 'registro_tienda';
    public const SOLICITUD_SERVICIO = 'solicitud_servicio';
    public const PEDIDO_CAMISETA    = 'pedido_camiseta';
    public const GLOBAL_SCOPE       = 'global';

    /**
     * Política por scope: [maxIntentos, bloqueoMinutos].
     * Endpoints públicos sin login → ventanas más cortas y umbrales más
     * altos para no bloquear usuarios legítimos por error.
     */
    private const POLITICAS = [
        self::LOGIN_ADMIN        => ['max' => 5,  'min' => 15],
        self::LOGIN_TIENDA       => ['max' => 5,  'min' => 15],
        self::REGISTRO_TIENDA    => ['max' => 3,  'min' => 60],
        self::SOLICITUD_SERVICIO => ['max' => 10, 'min' => 10],
        self::PEDIDO_CAMISETA    => ['max' => 8,  'min' => 15],
        self::GLOBAL_SCOPE       => ['max' => 5,  'min' => 15],
    ];

    // ─────────────────────────────────────────────
    // API PÚBLICA
    // ─────────────────────────────────────────────

    /**
     * Retorna true si la IP puede intentar la operación (no está bloqueada).
     * `$scope` por defecto es 'global' (backward-compatible con llamadas viejas).
     */
    public static function check(string $ip, string $scope = self::GLOBAL_SCOPE): bool
    {
        try {
            $model  = new RateLimitModel();
            $estado = $model->check($ip, $scope);
            return (int) $estado['bloqueado'] === 0;
        } catch (\Throwable $e) {
            error_log('[RateLimiter::check] BD no disponible: ' . $e->getMessage());
            return true;   // fail open
        }
    }

    /**
     * Incrementa el contador de fallos. Bloquea si supera el umbral del scope.
     */
    public static function registrarFallo(string $ip, string $scope = self::GLOBAL_SCOPE): void
    {
        try {
            $pol   = self::POLITICAS[$scope] ?? self::POLITICAS[self::GLOBAL_SCOPE];
            $model = new RateLimitModel();
            $model->registrarFallo($ip, $scope, $pol['max'], $pol['min']);
        } catch (\Throwable $e) {
            error_log('[RateLimiter::registrarFallo] ' . $e->getMessage());
        }
    }

    /**
     * Limpia los intentos (al éxito de la operación).
     */
    public static function limpiar(string $ip, string $scope = self::GLOBAL_SCOPE): void
    {
        try {
            $model = new RateLimitModel();
            $model->limpiar($ip, $scope);
        } catch (\Throwable $e) {
            error_log('[RateLimiter::limpiar] ' . $e->getMessage());
        }
    }

    /**
     * Cuántos minutos faltan para que el bloqueo expire (0 si no está bloqueado).
     */
    public static function minutosRestantes(string $ip, string $scope = self::GLOBAL_SCOPE): int
    {
        try {
            $model  = new RateLimitModel();
            $estado = $model->check($ip, $scope);
            return (int) $estado['minutos_restantes'];
        } catch (\Throwable $e) {
            error_log('[RateLimiter::minutosRestantes] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper de uso común — aborta la request con HTTP 429 si está bloqueado.
     * Útil en endpoints JSON/AJAX donde no hay redirect a una vista de error.
     */
    public static function enforceOr429(string $ip, string $scope): void
    {
        if (!self::check($ip, $scope)) {
            $min = self::minutosRestantes($ip, $scope);
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'error'   => 'rate_limited',
                'message' => "Demasiados intentos. Espera {$min} minuto(s).",
                'retry_after_minutes' => $min,
            ]);
            exit();
        }
    }
}
