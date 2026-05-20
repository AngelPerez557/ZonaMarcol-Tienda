<?php

class EquipacionModel extends BaseModel
{
    protected string $table      = 'equipaciones';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        $rows = $this->callSP('sp_equipaciones_findAll');
        return array_map(fn($r) => EquipacionEntity::fromArray($r), $rows);
    }

    public function findById(int $id): EquipacionEntity
    {
        $row = $this->callSPSingle('sp_equipaciones_findById', [$id]);
        if (!$row) return new EquipacionEntity();
        return EquipacionEntity::fromArray($row);
    }

    /**
     * Filtra por equipo + versión (opcional) + temporada (opcional).
     * Es el SP que usa el configurador público para mostrar las camisas.
     */
    public function findByEquipoVersion(int $equipoId, ?string $version = null, ?int $temporadaId = null): array
    {
        $rows = $this->callSP('sp_equipaciones_findByEquipoVersion', [
            $equipoId,
            $version    ?: null,
            $temporadaId ?: 0,
        ]);
        return array_map(fn($r) => EquipacionEntity::fromArray($r), $rows);
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_equipaciones_insert', [
            $data['equipo_id'],
            $data['temporada_id'],
            $data['tipo_equipacion_id'],
            $data['version'],
            $data['imagen_path']  ?? '',
            (float) $data['precio_base'],
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_equipaciones_update', [
            $data['id'],
            $data['equipo_id'],
            $data['temporada_id'],
            $data['tipo_equipacion_id'],
            $data['version'],
            $data['imagen_path']  ?? null,
            (float) $data['precio_base'],
        ]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        return $this->callSPExecute('sp_equipaciones_toggleActivo', [$id, $activo]) >= 0;
    }
}
