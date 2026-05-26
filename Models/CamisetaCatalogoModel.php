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

    // ─────────────────────────────────────────────
    // ESCRITURA (admin) — prepared statements directos.
    // Tallas viven en 3 tablas (hombre/mujer/infantil) con la MISMA estructura;
    // resolvemos la tabla a partir del tipo, validando contra una whitelist
    // para evitar SQLi. Tipos_equipacion es una sola tabla.
    // ─────────────────────────────────────────────

    /** Resuelve nombre de tabla de tallas. Whitelist contra inyección. */
    private function tablaTalla(string $tipo): ?string
    {
        return match ($tipo) {
            'hombre'   => 'tallas_hombre',
            'mujer'    => 'tallas_mujer',
            'infantil' => 'tallas_infantil',
            default    => null,
        };
    }

    // ── TALLAS — todas (activas + inactivas) para el admin ──
    public function tallasFindAll(string $tipo): array
    {
        $tabla = $this->tablaTalla($tipo);
        if ($tabla === null) return [];
        try {
            $stmt = $this->pdo->query("SELECT * FROM {$tabla} ORDER BY orden ASC, nombre ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[CamisetaCatalogoModel::tallasFindAll] ' . $e->getMessage());
            return [];
        }
    }

    public function tallaInsert(string $tipo, array $d): int
    {
        $tabla = $this->tablaTalla($tipo);
        if ($tabla === null) return 0;
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$tabla} (nombre, orden, activo) VALUES (?, ?, 1)"
            );
            $stmt->execute([$d['nombre'], (int) ($d['orden'] ?? 0)]);
            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[CamisetaCatalogoModel::tallaInsert] ' . $e->getMessage());
            return 0;
        }
    }

    public function tallaUpdate(string $tipo, array $d): bool
    {
        $tabla = $this->tablaTalla($tipo);
        if ($tabla === null) return false;
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE {$tabla} SET nombre = ?, orden = ? WHERE id = ?"
            );
            return $stmt->execute([$d['nombre'], (int) ($d['orden'] ?? 0), (int) $d['id']]);
        } catch (\PDOException $e) {
            error_log('[CamisetaCatalogoModel::tallaUpdate] ' . $e->getMessage());
            return false;
        }
    }

    public function tallaToggle(string $tipo, int $id, int $activo): bool
    {
        $tabla = $this->tablaTalla($tipo);
        if ($tabla === null) return false;
        try {
            $stmt = $this->pdo->prepare("UPDATE {$tabla} SET activo = ? WHERE id = ?");
            return $stmt->execute([$activo, $id]);
        } catch (\PDOException $e) {
            error_log('[CamisetaCatalogoModel::tallaToggle] ' . $e->getMessage());
            return false;
        }
    }

    // ── TIPOS DE EQUIPACIÓN — todos (admin) ──
    public function tiposFindAll(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT * FROM tipos_equipacion ORDER BY orden ASC, nombre ASC"
            );
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[CamisetaCatalogoModel::tiposFindAll] ' . $e->getMessage());
            return [];
        }
    }

    public function tipoInsert(array $d): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO tipos_equipacion (nombre, orden, activo) VALUES (?, ?, 1)"
            );
            $stmt->execute([$d['nombre'], (int) ($d['orden'] ?? 0)]);
            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[CamisetaCatalogoModel::tipoInsert] ' . $e->getMessage());
            return 0;
        }
    }

    public function tipoUpdate(array $d): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE tipos_equipacion SET nombre = ?, orden = ? WHERE id = ?"
            );
            return $stmt->execute([$d['nombre'], (int) ($d['orden'] ?? 0), (int) $d['id']]);
        } catch (\PDOException $e) {
            error_log('[CamisetaCatalogoModel::tipoUpdate] ' . $e->getMessage());
            return false;
        }
    }

    public function tipoToggle(int $id, int $activo): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE tipos_equipacion SET activo = ? WHERE id = ?"
            );
            return $stmt->execute([$activo, $id]);
        } catch (\PDOException $e) {
            error_log('[CamisetaCatalogoModel::tipoToggle] ' . $e->getMessage());
            return false;
        }
    }
}
