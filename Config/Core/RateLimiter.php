<?php

/**
 * RateLimiter.php — Rate limiter persistente en BD
 *
 * Cubre los hallazgos:
 *   F-08 — Datos en BD (tabla rate_limits) en vez de $_SESSION.
 *          Un atacante que borre cookies ya no resetea su contador.
 *   F-23 — Manejo de error explícito si BD falla — se loguea, NO se
 *          bloquea silenciosamente al usuario legítimo (fail open por UX).
 *   F-32 — Sin md5() (era keying débil + innecesario). La IP se usa
 *          directo como PRIMARY KEY de la tabla.
 *
 * Política de "fail open" cuando BD falla:
 *   - check() retorna true (permite el intento)
 *   - registrarFallo() loguea pero no rompe el flujo
 *   - Justificación: BD caída es excepcional; bloquear todos los logins
 *     en ese escenario es peor que perder protección contra brute force
 *     durante el incidente. El incidente queda en error_log.
 */
class RateLimiter
{
    private static int $maxIntentos     = 5;
    private static int $bloqueoMinutos  = 15;

    /**
     * Retorna true si la IP puede intentar login (no está bloqueada).
     */
    public static function check(string $ip): bool
    {
        try {
            $model  = new RateLimitModel();
            $estado = $model->check($ip);
            return (int) $estado['bloqueado'] === 0;
        } catch (\Throwable $e) {
            // F-23 — log + fail open (permitir intento)
            error_log('[RateLimiter::check] BD no disponible: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Incrementa el contador de fallos. Bloquea si supera el umbral.
     */
    public static function registrarFallo(string $ip): void
    {
        try {
            $model = new RateLimitModel();
            $model->registrarFallo($ip, self::$maxIntentos, self::$bloqueoMinutos);
        } catch (\Throwable $e) {
            error_log('[RateLimiter::registrarFallo] ' . $e->getMessage());
        }
    }

    /**
     * Limpia los intentos (al login exitoso).
     */
    public static function limpiar(string $ip): void
    {
        try {
            $model = new RateLimitModel();
            $model->limpiar($ip);
        } catch (\Throwable $e) {
            error_log('[RateLimiter::limpiar] ' . $e->getMessage());
        }
    }

    /**
     * Cuántos minutos faltan para que el bloqueo expire (0 si no está bloqueado).
     */
    public static function minutosRestantes(string $ip): int
    {
        try {
            $model  = new RateLimitModel();
            $estado = $model->check($ip);
            return (int) $estado['minutos_restantes'];
        } catch (\Throwable $e) {
            error_log('[RateLimiter::minutosRestantes] ' . $e->getMessage());
            return 0;
        }
    }
}
