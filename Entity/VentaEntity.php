<?php

class VentaEntity extends BaseEntity
{
    public ?int    $id             = null;
    public ?int    $cliente_id     = null;
    public ?int    $user_id        = null;
    public ?string $metodo_pago    = null;
    public ?float  $subtotal       = 0.00;
    public ?float  $descuento      = 0.00;
    public ?float  $total          = 0.00;
    public ?float  $monto_recibido = null;
    public ?float  $cambio         = null;
    public ?string $nota           = null;
    public ?string $created_at     = null;
    public ?string $cliente_nombre = null;
    public ?string $cajero_nombre  = null;

    public function getTotalFormateado(): string
    {
        return 'L. ' . number_format((float) $this->total, 2);
    }

    public function getIsv(): float
    {
        return (float) $this->total * 15 / 115;
    }

    public function getSubtotalSinIsv(): float
    {
        return (float) $this->total / 1.15;
    }

    public function getBadgeMetodoPago(): string
    {
        return match ($this->metodo_pago) {
            'Efectivo'      => 'bg-success',
            'Tarjeta'       => 'bg-primary',
            'Transferencia' => 'bg-info',
            default         => 'bg-secondary',
        };
    }

    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->metodo_pago)) {
            $this->addError('El método de pago es obligatorio.');
        }

        if ((float) $this->total <= 0) {
            $this->addError('El total debe ser mayor a cero.');
        }

        return !$this->hasErrors();
    }
}
