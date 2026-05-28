<?php

/**
 * OrdenServicioPagoEntity — Pago cobrado contra una orden de servicio.
 *
 * Tipos:
 *   - anticipo:  pago inicial antes/durante reparación
 *   - saldo:     pago final al momento de la entrega
 *   - abono:     pago parcial intermedio
 *
 * `caja_sesion_id` referencia la caja abierta al momento del cobro
 * (NULL si se registró fuera de una sesión de caja activa).
 */
class OrdenServicioPagoEntity extends BaseEntity
{
    public ?int    $id             = null;
    public ?int    $orden_id       = null;
    public ?string $tipo           = null;
    public ?float  $monto          = 0.00;
    public ?string $metodo         = null;
    public ?int    $caja_sesion_id = null;
    public ?string $recibo_numero  = null;
    public ?int    $user_id        = null;
    public ?string $fecha          = null;

    // JOIN
    public ?string $user_nombre = null;

    public function getMontoFormateado(): string
    {
        return 'L. ' . number_format((float) $this->monto, 2);
    }

    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            'anticipo' => 'Anticipo',
            'saldo'    => 'Saldo final',
            'abono'    => 'Abono',
            default    => '—',
        };
    }

    public function getTipoBadge(): string
    {
        return match ($this->tipo) {
            'anticipo' => 'bg-info text-dark',
            'saldo'    => 'bg-success',
            'abono'    => 'bg-warning text-dark',
            default    => 'bg-secondary',
        };
    }

    public function getMetodoIcon(): string
    {
        return match ($this->metodo) {
            'Efectivo'      => 'fa-money-bill-wave',
            'Tarjeta'       => 'fa-credit-card',
            'Transferencia' => 'fa-right-left',
            default         => 'fa-coins',
        };
    }
}
