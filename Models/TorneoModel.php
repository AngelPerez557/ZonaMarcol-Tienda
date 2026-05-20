<?php

class TorneoModel extends BaseModel
{
    protected string $table      = 'torneos';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        $rows = $this->callSP('sp_torneos_findAll');
        return array_map(fn($r) => TorneoEntity::fromArray($r), $rows);
    }

    public function findActivos(): array
    {
        $rows = $this->callSP('sp_torneos_findActivos');
        return array_map(fn($r) => TorneoEntity::fromArray($r), $rows);
    }

    public function findByTipo(string $tipo): array
    {
        $rows = $this->callSP('sp_torneos_findByTipo', [$tipo]);
        return array_map(fn($r) => TorneoEntity::fromArray($r), $rows);
    }

    public function findById(int $id): TorneoEntity
    {
        $row = $this->callSPSingle('sp_torneos_findById', [$id]);
        if (!$row) return new TorneoEntity();
        return TorneoEntity::fromArray($row);
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_torneos_insert', [
            $data['nombre'],
            $data['tipo']  ?? 'liga_club',
            $data['pais']  ?? null,
            $data['logo_path'] ?? '',
            (int) ($data['orden'] ?? 0),
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_torneos_update', [
            $data['id'],
            $data['nombre'],
            $data['tipo']  ?? 'liga_club',
            $data['pais']  ?? null,
            $data['logo_path'] ?? null,
            (int) ($data['orden'] ?? 0),
        ]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        return $this->callSPExecute('sp_torneos_toggleActivo', [$id, $activo]) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->callSPExecute('sp_torneos_delete', [$id]) > 0;
    }
}
