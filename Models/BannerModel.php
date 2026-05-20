<?php

class BannerModel extends BaseModel
{
    protected string $table      = 'banners';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        return $this->callSP('sp_banners_findAll');
    }

    public function findActivos(): array
    {
        return $this->callSP('sp_banners_findActivos');
    }

    public function findById(int $id): ?array
    {
        return $this->callSPSingle('sp_banners_findById', [$id]);
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_banners_insert', [
            $data['titulo']     ?? null,
            $data['imagen_url'],
            $data['enlace']     ?? null,
            $data['orden']      ?? 0,
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_banners_update', [
            $data['id'],
            $data['titulo']     ?? null,
            $data['imagen_url'] ?? null,
            $data['enlace']     ?? null,
            $data['orden']      ?? 0,
        ]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        return $this->callSPExecute('sp_banners_toggleActivo', [$id, $activo]) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->callSPExecute('sp_banners_delete', [$id]) > 0;
    }
}