<?php

/**
 * TemporadaEntity — Ej: 2024/25, Mundial 2026.
 * La ventana de pedidos vive en `config_temporadas` (campos extra del JOIN).
 */
class TemporadaEntity extends BaseEntity
{
    public ?int    $id          = null;
    public ?string $nombre      = null;
    public ?int    $anio_inicio = null;
    public ?int    $anio_fin    = null;
    public ?int    $activo      = 1;
    public ?string $created_at  = null;

    // JOIN con config_temporadas
    public ?string $fecha_inicio      = null;
    public ?string $fecha_fin         = null;
    public ?int    $abierta           = null;
    public ?string $lote_exportado_at = null;

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    public function isAbierta(): bool
    {
        return (int) $this->abierta === 1;
    }

    public function getEstadoLabel(): string
    {
        if ($this->fecha_fin && $this->fecha_fin < date('Y-m-d')) return 'Cerrada';
        if ($this->isAbierta())                                   return 'Abierta';
        return 'Pendiente';
    }

    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre))      $this->addError('El nombre de la temporada es obligatorio.');
        if (empty($this->anio_inicio)) $this->addError('Año de inicio es obligatorio.');
        if (empty($this->anio_fin))    $this->addError('Año de fin es obligatorio.');

        return !$this->hasErrors();
    }
}
