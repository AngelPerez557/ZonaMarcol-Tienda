<?php

/**
 * PedidoCamisetaEntity — Pedido del broker de camisetas.
 * Flujo: Pendiente_pago → Confirmado → En_proveedor → Recibido → Entregado.
 */
class PedidoCamisetaEntity extends BaseEntity
{
    public ?int    $id              = null;
    public ?string $codigo          = null;
    public ?int    $cliente_id      = null;
    public ?int    $temporada_id    = null;
    public ?string $estado          = 'Pendiente_pago';
    public ?float  $subtotal        = 0.00;
    public ?float  $total           = 0.00;
    public ?float  $anticipo_pagado = 0.00;
    public ?float  $saldo           = 0.00;
    public ?string $exportado_at    = null;
    public ?string $nota            = null;
    public ?string $comprobante_path = null;   // Ronda B — comprobante de transferencia
    public ?string $created_at      = null;
    public ?string $updated_at      = null;

    // JOIN
    public ?string $cliente_nombre   = null;
    public ?string $cliente_telefono = null;
    public ?string $cliente_email    = null;
    public ?string $temporada_nombre = null;

    public function getCodigoFormateado(): string
    {
        return '#' . ($this->codigo ?? str_pad($this->id ?? 0, 6, '0', STR_PAD_LEFT));
    }

    public function getBadgeEstado(): string
    {
        return match ($this->estado) {
            'Pendiente_pago' => 'bg-warning text-dark',
            'Confirmado'     => 'bg-info text-dark',
            'En_proveedor'   => 'bg-primary',
            'Recibido'       => 'bg-secondary',
            'Entregado'      => 'bg-success',
            'Cancelado'      => 'bg-danger',
            default          => 'bg-secondary',
        };
    }

    public function getEstadoLabel(): string
    {
        return str_replace('_', ' ', $this->estado);
    }

    public function getTotalFormateado(): string
    {
        return 'L. ' . number_format((float) $this->total, 2);
    }

    public function getAnticipoMinimo(int $pct = 50): float
    {
        return round((float) $this->total * $pct / 100, 2);
    }

    public function isAnticipoPagado(int $pct = 50): bool
    {
        return $this->anticipo_pagado >= $this->getAnticipoMinimo($pct);
    }

    public function isValid(): bool
    {
        $this->clearErrors();
        if (empty($this->cliente_id))   $this->addError('Cliente es obligatorio.');
        if (empty($this->temporada_id)) $this->addError('Temporada es obligatoria.');
        return !$this->hasErrors();
    }
}
