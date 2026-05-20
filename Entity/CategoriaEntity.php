<?php

class CategoriaEntity extends BaseEntity
{
    // ─────────────────────────────────────────────
    // PROPIEDADES — idénticas a columnas de la BD
    // ─────────────────────────────────────────────
    public ?int    $id          = null;
    public ?string $nombre      = null;
    public ?string $descripcion = null;
    public ?int    $activo      = 1;
    public ?string $created_at  = null;

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // ─────────────────────────────────────────────
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre de la categoría es obligatorio.');
        }

        return !$this->hasErrors();
    }
}