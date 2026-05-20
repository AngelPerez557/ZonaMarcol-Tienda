<?php

class ClienteEntity extends BaseEntity
{
    // ─────────────────────────────────────────────
    // PROPIEDADES — idénticas a columnas de la BD
    // ─────────────────────────────────────────────
    public ?int    $id         = null;
    public ?string $nombre     = null;
    public ?string $email      = null;
    public ?string $telefono   = null;
    public ?string $direccion  = null;
    public ?string $password   = null;
    public ?int    $activo     = 1;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    // Retorna el contacto principal — email o teléfono
    public function getContacto(): string
    {
        if (!empty($this->email)) return $this->email;
        if (!empty($this->telefono)) return $this->telefono;
        return '—';
    }

    // Hashea la contraseña antes de guardar
    public function hashPassword(): void
    {
        if (!empty($this->password) && !str_starts_with($this->password, '$2y$')) {
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        }
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // ─────────────────────────────────────────────
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre del cliente es obligatorio.');
        }

        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('El formato del correo electrónico no es válido.');
        }

        if (empty($this->email) && empty($this->telefono)) {
            $this->addError('Debe ingresar al menos un correo o teléfono.');
        }

        return !$this->hasErrors();
    }
}