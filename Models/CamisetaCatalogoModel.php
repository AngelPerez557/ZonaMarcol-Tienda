<?php

/**
 * CamisetaCatalogoModel — Catálogos auxiliares del módulo camisetas.
 * Agrupa: tipos_equipacion, tallas_hombre/mujer/infantil, precios_extras_camisa, config_camisetas.
 * No mapea a una tabla — agrupa lecturas read-only de catálogos fijos.
 */
class CamisetaCatalogoModel extends BaseModel
{
    protected string $table = '';

    // ── Tipos de equipación ───────────────────
    public function tiposEquipacionActivos(): array
    {
        return $this->callSP('sp_tipos_equipacion_findActivos');
    }

    // ── Tallas (3 tablas separadas) ───────────
    public function tallasHombreActivas(): array
    {
        return $this->callSP('sp_tallas_hombre_findActivas');
    }

    public function tallasMujerActivas(): array
    {
        return $this->callSP('sp_tallas_mujer_findActivas');
    }

    public function tallasInfantilActivas(): array
    {
        return $this->callSP('sp_tallas_infantil_findActivas');
    }

    public function tallasPorVersion(string $version): array
    {
        return match ($version) {
            'hombre'   => $this->tallasHombreActivas(),
            'mujer'    => $this->tallasMujerActivas(),
            'infantil' => $this->tallasInfantilActivas(),
            default    => [],
        };
    }

    // ── Precios extras ────────────────────────
    public function preciosExtras(): array
    {
        return $this->callSP('sp_precios_extras_camisa_findAll');
    }

    public function getPrecioExtra(string $concepto): float
    {
        foreach ($this->preciosExtras() as $p) {
            if ($p['concepto'] === $concepto) return (float) $p['precio'];
        }
        return 0.0;
    }

    // ── Configuración general ─────────────────
    public function getConfig(): ?array
    {
        return $this->callSPSingle('sp_config_camisetas_get');
    }
}
