<?php

class ZonaModel extends BaseModel
{
    protected string $table      = 'zonas_envio';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        return $this->callSP('sp_zonas_findAll');
    }

    public function findActivas(): array
    {
        return $this->callSP('sp_zonas_findActivas');
    }

    public function findById(int $id): ?array
    {
        return $this->callSPSingle('sp_zonas_findById', [$id]);
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_zonas_insert', [
            $data['nombre'],
            $data['costo'],
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_zonas_update', [
            $data['id'],
            $data['nombre'],
            $data['costo'],
        ]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        return $this->callSPExecute('sp_zonas_toggleActivo', [$id, $activo]) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->callSPExecute('sp_zonas_delete', [$id]) > 0;
    }
}