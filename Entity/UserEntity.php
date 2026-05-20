<?php

class UserEntity extends BaseEntity
{
    // ─────────────────────────────────────────────
    // PROPIEDADES — idénticas a columnas de la BD
    // ─────────────────────────────────────────────
    public ?int    $id         = null;
    public ?string $nombre     = null;
    public ?string $email      = null;
    public ?string $username   = null;
    public ?string $password   = null;
    public ?int    $rol_id     = null;
    public ?int    $activo     = 1;
    public ?string $foto       = null;
    public ?string $telefono   = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    // Campos adicionales del JOIN con roles
    public ?string $rol_slug   = null;
    public ?string $rol_nombre = null;

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    public function isAdmin(): bool
    {
        return $this->rol_slug === 'admin';
    }

    public function getNombreRol(): string
    {
        return $this->rol_nombre ?? '—';
    }

    public function getFotoUrl(): string
    {
        if (!empty($this->foto)) {
            return APP_URL . 'Content/Demo/img/Usuarios/' . $this->foto;
        }
        return APP_URL . 'Content/Demo/img/default/usuario.svg';
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // ─────────────────────────────────────────────
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre es obligatorio.');
        }

        if (empty($this->email)) {
            $this->addError('El correo es obligatorio.');
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('El formato del correo no es válido.');
        }

        if (empty($this->rol_id)) {
            $this->addError('El rol es obligatorio.');
        }

        return !$this->hasErrors();
    }
}