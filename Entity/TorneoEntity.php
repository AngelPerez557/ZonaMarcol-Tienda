<?php

/**
 * TorneoEntity — Liga de clubes o competición de selecciones.
 * Unifica antes-llamado "ligas" + competiciones nacionales (Mundial, Eurocopa).
 * Cada torneo tiene su LOGO visible como botón desplegable en el configurador.
 */
class TorneoEntity extends BaseEntity
{
    public ?int    $id         = null;
    public ?string $nombre     = null;
    public ?string $tipo       = 'liga_club';   // liga_club | seleccion | copa_continental | otro
    public ?string $pais       = null;
    public ?string $logo_path  = null;
    public ?int    $orden      = 0;
    public ?int    $activo     = 1;
    public ?string $created_at = null;

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    public function getLogoUrl(): string
    {
        if (!empty($this->logo_path)) {
            return APP_URL . 'Content/Demo/img/Ligas/' . $this->logo_path;
        }
        return APP_URL . 'Content/Demo/img/default/torneo_default.svg';
    }

    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            'liga_club'        => 'Liga de clubes',
            'seleccion'        => 'Selecciones',
            'copa_continental' => 'Copa continental',
            default            => 'Otro',
        };
    }

    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre del torneo es obligatorio.');
        }

        if (!in_array($this->tipo, ['liga_club', 'seleccion', 'copa_continental', 'otro'], true)) {
            $this->addError('Tipo de torneo no válido.');
        }

        return !$this->hasErrors();
    }
}
