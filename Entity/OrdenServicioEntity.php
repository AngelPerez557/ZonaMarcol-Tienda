<?php

/**
 * OrdenServicioEntity — Una orden de reparación del taller.
 * El cliente trae un equipo, recepción lo registra, un técnico diagnostica
 * y repara, y finalmente se entrega cobrando el saldo.
 *
 * Flujo de estados (rígido):
 *   Recibido → Diagnostico → Esperando aprobacion → En reparacion
 *            → Listo → Entregado     (Cancelado en cualquier punto previo)
 */
class OrdenServicioEntity extends BaseEntity
{
    public ?int    $id                    = null;
    public ?string $codigo                = null;
    public ?int    $cliente_id            = null;
    public ?int    $user_recepcion_id     = null;
    public ?int    $tecnico_id            = null;
    public ?string $equipo_descripcion    = null;
    public ?string $serial                = null;
    public ?string $accesorios_entregados = null;
    public ?string $diagnostico_inicial   = null;
    public ?string $estado                = 'Recibido';
    public ?float  $total_actual          = 0.00;
    public ?float  $total_pagado          = 0.00;
    public ?float  $saldo                 = 0.00;
    public ?string $fecha_recepcion       = null;
    public ?string $fecha_entrega         = null;
    public ?string $fecha_garantia_hasta  = null;
    public ?string $motivo_cancelacion    = null;
    public ?string $observaciones         = null;
    public ?string $created_at            = null;
    public ?string $updated_at            = null;

    // Campos de JOIN (no son columnas de ordenes_servicio)
    public ?string $cliente_nombre   = null;
    public ?string $cliente_telefono = null;
    public ?string $tecnico_nombre   = null;
    public ?string $recepcion_nombre = null;

    /** Etiqueta legible del estado (la BD guarda los valores sin tilde). */
    public function getEstadoLabel(): string
    {
        return match ($this->estado) {
            'Recibido'             => 'Recibido',
            'Diagnostico'          => 'Diagnóstico',
            'Esperando aprobacion' => 'Esperando aprobación',
            'En reparacion'        => 'En reparación',
            'Listo'                => 'Listo',
            'Entregado'            => 'Entregado',
            'Cancelado'            => 'Cancelado',
            default                => $this->estado ?? '—',
        };
    }

    /** Clase de badge Bootstrap según el estado. */
    public function getEstadoBadge(): string
    {
        return match ($this->estado) {
            'Recibido'             => 'bg-secondary',
            'Diagnostico'          => 'bg-info text-dark',
            'Esperando aprobacion' => 'bg-warning text-dark',
            'En reparacion'        => 'bg-primary',
            'Listo'                => 'bg-success',
            'Entregado'            => 'bg-dark',
            'Cancelado'            => 'bg-danger',
            default                => 'bg-secondary',
        };
    }

    public function getTotalFormateado(): string
    {
        return 'L. ' . number_format((float) $this->total_actual, 2);
    }

    public function getSaldoFormateado(): string
    {
        return 'L. ' . number_format((float) $this->saldo, 2);
    }

    /** Una orden ya entregada o cancelada está cerrada (sin acciones). */
    public function estaCerrada(): bool
    {
        return in_array($this->estado, ['Entregado', 'Cancelado'], true);
    }

    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->cliente_id)) {
            $this->addError('Debe seleccionarse un cliente.');
        }
        if (empty($this->equipo_descripcion)) {
            $this->addError('La descripción del equipo es obligatoria.');
        }

        return !$this->hasErrors();
    }
}
