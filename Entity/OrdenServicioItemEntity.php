<?php

/**
 * OrdenServicioItemEntity — Línea del presupuesto de una orden de servicio.
 *
 * Tres tipos de item:
 *   - servicio_catalogo:    referencia a `servicios_catalogo.id`
 *                           (limpieza/reparación/diagnóstico predefinido).
 *   - repuesto_libre:       repuesto físico ingresado manualmente por el
 *                           técnico (memoria RAM, cable HDMI, etc.).
 *   - mano_obra_adicional:  trabajo extra fuera del catálogo.
 *
 * `subtotal` se calcula server-side (precio_unitario * cantidad) y se
 * persiste para no recomputar en cada listado.
 *
 * `aprobado_cliente` permite cobrar SOLO los items que el cliente aceptó
 * tras el presupuesto inicial.
 */
class OrdenServicioItemEntity extends BaseEntity
{
    public ?int    $id                   = null;
    public ?int    $orden_id             = null;
    public ?string $tipo                 = null;   // ENUM
    public ?int    $servicio_catalogo_id = null;
    public ?string $descripcion          = null;
    public ?int    $cantidad             = 1;
    public ?float  $precio_unitario      = 0.00;
    public ?float  $subtotal             = 0.00;
    public ?int    $aprobado_cliente     = 0;
    public ?int    $dias_garantia        = 30;
    public ?int    $agregado_por         = null;
    public ?string $agregado_en          = null;

    // Campos de JOIN (no columnas)
    public ?string $usuario_nombre  = null;   // quién lo agregó
    public ?string $catalogo_nombre = null;   // si tipo=servicio_catalogo

    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            'servicio_catalogo'    => 'Servicio de catálogo',
            'repuesto_libre'       => 'Repuesto',
            'mano_obra_adicional'  => 'Mano de obra',
            default                => '—',
        };
    }

    public function getTipoBadge(): string
    {
        return match ($this->tipo) {
            'servicio_catalogo'    => 'bg-info text-dark',
            'repuesto_libre'       => 'bg-warning text-dark',
            'mano_obra_adicional'  => 'bg-primary',
            default                => 'bg-secondary',
        };
    }

    public function getSubtotalFormateado(): string
    {
        return 'L. ' . number_format((float) $this->subtotal, 2);
    }

    public function isAprobado(): bool
    {
        return (int) $this->aprobado_cliente === 1;
    }

    public function isValid(): bool
    {
        $this->clearErrors();
        if (empty($this->orden_id)) {
            $this->addError('orden_id es obligatorio.');
        }
        if (!in_array($this->tipo, ['servicio_catalogo','repuesto_libre','mano_obra_adicional'], true)) {
            $this->addError('Tipo de ítem inválido.');
        }
        if (empty($this->descripcion)) {
            $this->addError('La descripción del ítem es obligatoria.');
        }
        if ((int) $this->cantidad <= 0) {
            $this->addError('La cantidad debe ser mayor a cero.');
        }
        if ((float) $this->precio_unitario < 0) {
            $this->addError('El precio no puede ser negativo.');
        }
        return !$this->hasErrors();
    }
}
