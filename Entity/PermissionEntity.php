<?php

class PermissionEntity extends BaseEntity
{
    public ?int    $id          = null;
    public ?string $nombre      = null;
    public ?string $slug        = null;
    public ?string $modulo      = null;
    public ?string $descripcion = null;

    // Validación
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre del permiso es obligatorio.');
        }

        if (empty($this->slug)) {
            $this->addError('El slug del permiso es obligatorio.');
        } elseif (!preg_match('/^[a-z0-9\.\-]+$/', $this->slug)) {
            // El slug permite puntos para el formato 'modulo.accion'
            $this->addError('El slug solo puede contener letras minúsculas, números, puntos y guiones.');
        }

        if (empty($this->modulo)) {
            $this->addError('El módulo del permiso es obligatorio.');
        }

        return !$this->hasErrors();
    }
}