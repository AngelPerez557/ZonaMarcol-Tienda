<?php

class TemporadaModel extends BaseModel
{
    protected string $table      = 'temporadas';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        $rows = $this->callSP('sp_temporadas_findAll');
        return array_map(fn($r) => TemporadaEntity::fromArray($r), $rows);
    }

    public function findActivas(): array
    {
        $rows = $this->callSP('sp_temporadas_findActivas');
        return array_map(fn($r) => TemporadaEntity::fromArray($r), $rows);
    }

    public function findAbiertas(): array
    {
        $rows = $this->callSP('sp_temporadas_findAbiertas');
        return array_map(fn($r) => TemporadaEntity::fromArray($r), $rows);
    }

    public function findById(int $id): TemporadaEntity
    {
        $row = $this->callSPSingle('sp_temporadas_findAll');
        // No hay SP single; usar findAll y filtrar (mejorar después con SP dedicado)
        foreach ($this->findAll() as $t) {
            if ((int) $t->id === $id) return $t;
        }
        return new TemporadaEntity();
    }

    public function cerrarVencidas(): void
    {
        $this->callSPExecute('sp_temporadas_cerrarVencidas');
    }

    // ─────────────────────────────────────────────
    // ESCRITURA — prepared statements directos (no hay SP de write).
    // MVC estricto: el SQL vive solo aquí.
    // ─────────────────────────────────────────────

    public function insert(array $d): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO temporadas (nombre, anio_inicio, anio_fin, activo)
                 VALUES (?, ?, ?, 1)"
            );
            $stmt->execute([
                $d['nombre'],
                (int) $d['anio_inicio'],
                (int) $d['anio_fin'],
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[TemporadaModel::insert] ' . $e->getMessage());
            return 0;
        }
    }

    public function update(array $d): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE temporadas SET nombre = ?, anio_inicio = ?, anio_fin = ? WHERE id = ?"
            );
            return $stmt->execute([
                $d['nombre'],
                (int) $d['anio_inicio'],
                (int) $d['anio_fin'],
                (int) $d['id'],
            ]);
        } catch (\PDOException $e) {
            error_log('[TemporadaModel::update] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Activar/desactivar. Soft-delete: las temporadas están referenciadas
     * por equipaciones y pedidos_camiseta — borrado físico rompería historial.
     */
    public function toggleActivo(int $id, int $activo): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE temporadas SET activo = ? WHERE id = ?");
            return $stmt->execute([$activo, $id]);
        } catch (\PDOException $e) {
            error_log('[TemporadaModel::toggleActivo] ' . $e->getMessage());
            return false;
        }
    }
}
