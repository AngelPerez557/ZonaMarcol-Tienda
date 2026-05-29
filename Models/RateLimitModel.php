<?php

/**
 * RateLimitModel.php — Persistencia del rate limiter en BD
 *
 * F-08 — Antes el rate limit vivía en $_SESSION (un atacante borraba
 *        cookies y reseteaba el contador). Ahora persiste en la tabla
 *        rate_limits indexada por (IP, scope) — sobrevive cierres de
 *        navegador y separa contadores por endpoint (login admin,
 *        login tienda, registro, etc.).
 *
 * Los SPs ahora exigen scope. Para compatibilidad de llamadas viejas,
 * el wrapper RateLimiter aplica 'global' como default cuando no se pasa.
 */
class RateLimitModel extends BaseModel
{
    protected string $table = '';

    /**
     * Obtiene estado del rate limit para (IP, scope).
     * Retorna array con: intentos, bloqueado, minutos_restantes.
     */
    public function check(string $ip, string $scope): array
    {
        $row = $this->callSPSingle('sp_rate_limits_check', [$ip, $scope]);
        if (!$row) {
            return ['intentos' => 0, 'bloqueado' => 0, 'minutos_restantes' => 0];
        }
        return [
            'intentos'          => (int) ($row['intentos']          ?? 0),
            'bloqueado'         => (int) ($row['bloqueado']         ?? 0),
            'minutos_restantes' => (int) ($row['minutos_restantes'] ?? 0),
        ];
    }

    /**
     * Registra un intento fallido. Si supera el umbral, bloquea por N minutos.
     */
    public function registrarFallo(
        string $ip,
        string $scope,
        int $maxIntentos,
        int $bloqueoMin
    ): void {
        $this->callSPExecute('sp_rate_limits_register_fallo', [
            $ip, $scope, $maxIntentos, $bloqueoMin
        ]);
    }

    /**
     * Limpia el rate limit para (IP, scope) — al success de la operación.
     */
    public function limpiar(string $ip, string $scope): void
    {
        $this->callSPExecute('sp_rate_limits_limpiar', [$ip, $scope]);
    }
}
