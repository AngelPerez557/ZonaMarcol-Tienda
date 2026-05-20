<?php

/**
 * EquipacionEntity — Camisa específica.
 * Llave única: (equipo, temporada, tipo, version).
 * Ej: Real Madrid 2024/25 Local Hombre.
 */
class EquipacionEntity extends BaseEntity
{
    public ?int    $id                 = null;
    public ?int    $equipo_id          = null;
    public ?int    $temporada_id       = null;
    public ?int    $tipo_equipacion_id = null;
    public ?string $version            = null;   // hombre | mujer | infantil
    public ?string $imagen_path        = null;
    public ?float  $precio_base        = 0.00;
    public ?int    $activo             = 1;
    public ?string $created_at         = null;

    // JOIN
    public ?string $equipo_nombre    = null;
    public ?string $escudo_path      = null;
    public ?string $torneo_nombre    = null;
    public ?string $torneo_logo      = null;
    public ?string $temporada_nombre = null;
    public ?string $tipo_nombre      = null;

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    public function getImagenUrl(): string
    {
        if (!empty($this->imagen_path)) {
            return APP_URL . 'Content/Demo/img/Equipaciones/' . $this->imagen_path;
        }
        return APP_URL . 'Content/Demo/img/default/equipacion_default.svg';
    }

    public function getVersionLabel(): string
    {
        return match ($this->version) {
            'hombre'   => 'Hombre',
            'mujer'    => 'Mujer',
            'infantil' => 'Infantil',
            default    => '—',
        };
    }

    public function getPrecioFormateado(): string
    {
        return 'L. ' . number_format((float) $this->precio_base, 2);
    }

    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->equipo_id))          $this->addError('Equipo es obligatorio.');
        if (empty($this->temporada_id))       $this->addError('Temporada es obligatoria.');
        if (empty($this->tipo_equipacion_id)) $this->addError('Tipo de equipación es obligatorio.');
        if (!in_array($this->version, ['hombre', 'mujer', 'infantil'], true)) {
            $this->addError('Versión no válida.');
        }
        if ((float) $this->precio_base <= 0) {
            $this->addError('El precio base debe ser mayor a cero.');
        }

        return !$this->hasErrors();
    }
}
