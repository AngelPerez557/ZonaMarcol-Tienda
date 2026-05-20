<?php

class RoleEntity extends BaseEntity
{
    public ?int    $id          = null;
    public ?string $nombre      = null;
    public ?string $slug        = null;
    public ?string $descripcion = null;
    public ?int    $activo      = 1;

    // Retorna true si el rol está activo
    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    // Validación
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre del rol es obligatorio.');
        }

        if (empty($this->slug)) {
            $this->addError('El slug del rol es obligatorio.');
        } elseif (!preg_match('/^[a-z0-9\-]+$/', $this->slug)) {
            $this->addError('El slug solo puede contener letras minúsculas, números y guiones.');
        }

        return !$this->hasErrors();
    }
}