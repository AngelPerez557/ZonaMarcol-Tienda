<?php

/**
 * EquipoEntity — Club o selección dentro de un torneo.
 */
class EquipoEntity extends BaseEntity
{
    public ?int    $id          = null;
    public ?int    $torneo_id   = null;
    public ?string $nombre      = null;
    public ?string $escudo_path = null;
    public ?int    $orden       = 0;
    public ?int    $activo      = 1;
    public ?string $created_at  = null;

    // JOIN
    public ?string $torneo_nombre = null;

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    public function getEscudoUrl(): string
    {
        if (!empty($this->escudo_path)) {
            return APP_URL . 'Content/Demo/img/Equipos/' . $this->escudo_path;
        }
        return APP_URL . 'Content/Demo/img/default/equipo_default.svg';
    }

    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre del equipo es obligatorio.');
        }
        if (empty($this->torneo_id)) {
            $this->addError('Debe asignarse a un torneo.');
        }

        return !$this->hasErrors();
    }
}
