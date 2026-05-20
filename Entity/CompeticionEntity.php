<?php

/**
 * CompeticionEntity — Parche cosible (LaLiga, Champions, Mundial).
 * Tiene logo y precio_extra que se agrega al pedido al elegirla.
 */
class CompeticionEntity extends BaseEntity
{
    public ?int    $id           = null;
    public ?string $nombre       = null;
    public ?string $parche_path  = null;
    public ?float  $precio_extra = 0.00;
    public ?int    $activo       = 1;

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    public function getParcheUrl(): string
    {
        if (!empty($this->parche_path)) {
            return APP_URL . 'Content/Demo/img/Competiciones/' . $this->parche_path;
        }
        return APP_URL . 'Content/Demo/img/default/parche_default.svg';
    }

    public function getPrecioFormateado(): string
    {
        return 'L. ' . number_format((float) $this->precio_extra, 2);
    }

    public function isValid(): bool
    {
        $this->clearErrors();
        if (empty($this->nombre)) $this->addError('El nombre de la competición es obligatorio.');
        return !$this->hasErrors();
    }
}
