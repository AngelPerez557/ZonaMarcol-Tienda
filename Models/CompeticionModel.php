<?php

class CompeticionModel extends BaseModel
{
    protected string $table      = 'competiciones';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        $rows = $this->callSP('sp_competiciones_findAll');
        return array_map(fn($r) => CompeticionEntity::fromArray($r), $rows);
    }

    public function findActivas(): array
    {
        $rows = $this->callSP('sp_competiciones_findActivas');
        return array_map(fn($r) => CompeticionEntity::fromArray($r), $rows);
    }

    public function findById(int $id): CompeticionEntity
    {
        $row = $this->callSPSingle('sp_competiciones_findById', [$id]);
        if (!$row) return new CompeticionEntity();
        return CompeticionEntity::fromArray($row);
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_competiciones_insert', [
            $data['nombre'],
            $data['parche_path']  ?? '',
            (float) ($data['precio_extra'] ?? 0),
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_competiciones_update', [
            $data['id'],
            $data['nombre'],
            $data['parche_path']  ?? null,
            (float) ($data['precio_extra'] ?? 0),
        ]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        return $this->callSPExecute('sp_competiciones_toggleActivo', [$id, $activo]) >= 0;
    }
}
