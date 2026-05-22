<?php

/**
 * ServicioCatalogoEntity — Un servicio técnico ofrecido por el taller
 * (limpieza, reparación, diagnóstico). Es el catálogo del que se eligen
 * ítems al armar el presupuesto de una orden de servicio.
 */
class ServicioCatalogoEntity extends BaseEntity
{
    public ?int    $id          = null;
    public ?string $nombre      = null;
    public ?string $descripcion = null;
    public ?float  $precio      = 0.00;
    public ?string $categoria   = 'limpieza';   // limpieza | reparacion | diagnostico | otro
    public ?int    $activo      = 1;
    public ?string $created_at  = null;

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    public function getCategoriaLabel(): string
    {
        return match ($this->categoria) {
            'limpieza'    => 'Limpieza',
            'reparacion'  => 'Reparación',
            'diagnostico' => 'Diagnóstico',
            default       => 'Otro',
        };
    }

    public function getPrecioFormateado(): string
    {
        return 'L. ' . number_format((float) $this->precio, 2);
    }

    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre del servicio es obligatorio.');
        }
        if ((float) $this->precio < 0) {
            $this->addError('El precio no puede ser negativo.');
        }
        if (!in_array($this->categoria, ['limpieza', 'reparacion', 'diagnostico', 'otro'], true)) {
            $this->addError('Categoría no válida.');
        }

        return !$this->hasErrors();
    }
}
