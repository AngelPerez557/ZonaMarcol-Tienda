<?php

class PermissionModel extends BaseModel
{
    protected string $table      = 'permissions';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    public function findAll(): array
    {
        $rows = $this->callSP('sp_permissions_findAll');
        return array_map(fn($row) => PermissionEntity::fromArray($row), $rows);
    }

    public function findById(int $id): PermissionEntity
    {
        $row = $this->callSPSingle('sp_permissions_findById', [$id]);
        if (!$row) return new PermissionEntity();
        return PermissionEntity::fromArray($row);
    }

    public function findByModule(string $modulo): array
    {
        $rows = $this->callSP('sp_permissions_findByModule', [$modulo]);
        return array_map(fn($row) => PermissionEntity::fromArray($row), $rows);
    }

    public function getModules(): array
    {
        $rows = $this->callSP('sp_permissions_getModules');
        return array_column($rows, 'modulo');
    }

    public function count(): int
    {
        $row = $this->callSPSingle('sp_permissions_count');
        return $row ? (int) $row['total'] : 0;
    }

    public function slugExists(string $slug, int $excludeId = 0): bool
    {
        $row = $this->callSPSingle('sp_permissions_slugExists', [$slug, $excludeId]);
        return $row ? (int) $row['existe'] > 0 : false;
    }

    public function isAssigned(int $id): bool
    {
        $row = $this->callSPSingle('sp_permissions_isAssigned', [$id]);
        return $row ? (int) $row['total'] > 0 : false;
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_permissions_insert', [
            $data['nombre'],
            $data['slug'],
            $data['modulo'],
            $data['descripcion'] ?? null,
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_permissions_update', [
            $data['id'],
            $data['nombre'],
            $data['slug'],
            $data['modulo'],
            $data['descripcion'] ?? null,
        ]);
        return $affected > 0;
    }

    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_permissions_delete', [$id]);
        return $affected > 0;
    }
}