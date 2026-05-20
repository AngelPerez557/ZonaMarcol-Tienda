<?php

class EquipoModel extends BaseModel
{
    protected string $table      = 'equipos';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        $rows = $this->callSP('sp_equipos_findAll');
        return array_map(fn($r) => EquipoEntity::fromArray($r), $rows);
    }

    public function findByTorneo(int $torneoId): array
    {
        $rows = $this->callSP('sp_equipos_findByTorneo', [$torneoId]);
        return array_map(fn($r) => EquipoEntity::fromArray($r), $rows);
    }

    public function findById(int $id): EquipoEntity
    {
        $row = $this->callSPSingle('sp_equipos_findById', [$id]);
        if (!$row) return new EquipoEntity();
        return EquipoEntity::fromArray($row);
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_equipos_insert', [
            $data['torneo_id'],
            $data['nombre'],
            $data['escudo_path'] ?? '',
            (int) ($data['orden'] ?? 0),
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_equipos_update', [
            $data['id'],
            $data['torneo_id'],
            $data['nombre'],
            $data['escudo_path'] ?? null,
            (int) ($data['orden'] ?? 0),
        ]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        return $this->callSPExecute('sp_equipos_toggleActivo', [$id, $activo]) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->callSPExecute('sp_equipos_delete', [$id]) > 0;
    }

    // ── Competiciones del equipo (N:M) ───────────────────
    public function findCompeticiones(int $equipoId): array
    {
        return $this->callSP('sp_equipo_competicion_findByEquipo', [$equipoId]);
    }

    public function syncCompeticiones(int $equipoId, array $competicionIds): bool
    {
        $this->beginTransaction();
        try {
            $this->callSPExecute('sp_equipo_competicion_sync', [$equipoId]);
            foreach ($competicionIds as $cId) {
                $this->callSPExecute('sp_equipo_competicion_assign', [$equipoId, (int) $cId]);
            }
            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            return false;
        }
    }
}
