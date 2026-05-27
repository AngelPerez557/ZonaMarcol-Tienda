<?php

/**
 * SolicitudServicioEntity — Solicitud online de servicio técnico.
 * El cliente la envía desde la tienda; un empleado en recepción la
 * convierte en una `orden_servicio` real cuando el equipo llega.
 */
class SolicitudServicioEntity extends BaseEntity
{
    public ?int    $id                 = null;
    public ?int    $cliente_id         = null;
    public ?string $equipo_descripcion = null;
    public ?string $falla_reportada    = null;
    public ?string $telefono_contacto  = null;
    public ?string $estado             = 'Pendiente';
    public ?int    $orden_servicio_id  = null;
    public ?string $motivo_rechazo     = null;
    public ?string $created_at         = null;
    public ?string $atendida_at        = null;

    // JOIN
    public ?string $cliente_nombre = null;
    public ?string $cliente_email  = null;
    public ?string $codigo_orden   = null;   // codigo de la orden_servicio si fue atendida

    public function isPendiente(): bool { return $this->estado === 'Pendiente'; }
    public function isAtendida(): bool  { return $this->estado === 'Atendida'; }
    public function isRechazada(): bool { return $this->estado === 'Rechazada'; }

    public function getEstadoBadge(): string
    {
        return match ($this->estado) {
            'Pendiente'  => 'bg-warning text-dark',
            'Atendida'   => 'bg-success',
            'Rechazada'  => 'bg-danger',
            default      => 'bg-secondary',
        };
    }

    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->cliente_id)) {
            $this->addError('Cliente obligatorio.');
        }
        if (empty($this->equipo_descripcion)) {
            $this->addError('La descripción del equipo es obligatoria.');
        }

        return !$this->hasErrors();
    }
}
