<?php

/**
 * RateLimitModel.php — Persistencia del rate limiter en BD
 *
 * F-08 — Antes el rate limit vivía en $_SESSION (un atacante borraba
 *        cookies y reseteaba el contador). Ahora persiste en la tabla
 *        rate_limits indexada por IP — sobrevive cierres de navegador.
 */
class RateLimitModel extends BaseModel
{
    // No mapea a una tabla concreta para CRUD genérico — usa SPs específicos
    protected string $table = '';

    /**
     * Obtiene estado del rate limit para una IP.
     * Retorna array con: intentos, bloqueado, minutos_restantes
     */
    public function check(string $ip): array
    {
        $row = $this->callSPSingle('sp_rate_limits_check', [$ip]);
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
    public function registrarFallo(string $ip, int $maxIntentos, int $bloqueoMin): void
    {
        $this->callSPExecute('sp_rate_limits_register_fallo', [
            $ip, $maxIntentos, $bloqueoMin
        ]);
    }

    /**
     * Limpia el rate limit para una IP (al login exitoso).
     */
    public function limpiar(string $ip): void
    {
        $this->callSPExecute('sp_rate_limits_limpiar', [$ip]);
    }
}
