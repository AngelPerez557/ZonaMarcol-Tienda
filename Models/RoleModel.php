<?php

class RoleModel extends BaseModel
{
    // Nombre de la tabla — BaseModel construye el prefijo 'sp_roles'
    protected string $table      = 'roles';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    // Retorna todos los roles del sistema
    // Llama a: CALL sp_roles_findAll()
    public function findAll(): array
    {
        $rows = $this->callSP('sp_roles_findAll');
        return array_map(fn($row) => RoleEntity::fromArray($row), $rows);
    }

    // Retorna un rol por su ID
    // Llama a: CALL sp_roles_findById(?)
    // Retorna RoleEntity con Found = true si existe
    public function getById(int $id): RoleEntity
    {
        $row = $this->callSPSingle('sp_roles_findById', [$id]);

        if ($row === null || empty($row)) {
            return new RoleEntity();
        }

        return RoleEntity::fromArray($row);
    }

    // Retorna un rol por su slug
    // Llama a: CALL sp_roles_findBySlug(?)
    // Ej: $roleModel->getBySlug('admin') → RoleEntity
    public function getBySlug(string $slug): RoleEntity
    {
        $row = $this->callSPSingle('sp_roles_findBySlug', [$slug]);

        if ($row === null || empty($row)) {
            return new RoleEntity();
        }

        return RoleEntity::fromArray($row);
    }

    // Retorna todos los permisos asignados a un rol
    // Llama a: CALL sp_roles_getPermissions(?)
    // Retorna array de slugs de permisos
    // Ej: ['usuarios.ver', 'usuarios.crear', 'reportes.exportar']
    public function getPermissionsByRole(int $rolId): array
    {
        $rows = $this->callSP('sp_roles_getPermissions', [$rolId]);

        // Extrae solo los slugs de permisos para Auth::can()
        return array_column($rows, 'slug');
    }

    // Retorna todos los permisos de un rol como entidades completas
    // Llama a: CALL sp_roles_getPermissions(?)
    // Útil para mostrar los permisos en la vista de edición de rol
    public function getPermissionsFullByRole(int $rolId): array
    {
        $rows = $this->callSP('sp_roles_getPermissions', [$rolId]);
        return array_map(fn($row) => PermissionEntity::fromArray($row), $rows);
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    // Inserta un nuevo rol en la BD
    // Llama a: CALL sp_roles_insert(?, ?, ?)
    // Retorna el ID del rol creado
    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_roles_insert', [
            $data['nombre'],
            $data['slug'],
            $data['descripcion'] ?? null,
        ]);
    }

    // Actualiza los datos de un rol existente
    // Llama a: CALL sp_roles_update(?, ?, ?, ?)
    // Retorna true si se actualizó correctamente
    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_roles_update', [
            $data['id'],
            $data['nombre'],
            $data['slug'],
            $data['descripcion'] ?? null,
        ]);
        return $affected > 0;
    }

    // Elimina un rol permanentemente
    // Llama a: CALL sp_roles_delete(?)
    // ADVERTENCIA: verificar que no haya usuarios con este rol antes de eliminar
    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_roles_delete', [$id]);
        return $affected > 0;
    }

    // ─────────────────────────────────────────────
    // PERMISOS — Asignación a roles
    // ─────────────────────────────────────────────

    // Asigna un permiso a un rol
    // Llama a: CALL sp_roles_assignPermission(?, ?)
    public function assignPermission(int $rolId, int $permissionId): bool
    {
        $affected = $this->callSPExecute('sp_roles_assignPermission', [
            $rolId,
            $permissionId,
        ]);
        return $affected > 0;
    }

    // Revoca un permiso de un rol
    // Llama a: CALL sp_roles_revokePermission(?, ?)
    public function revokePermission(int $rolId, int $permissionId): bool
    {
        $affected = $this->callSPExecute('sp_roles_revokePermission', [
            $rolId,
            $permissionId,
        ]);
        return $affected > 0;
    }

    // Sincroniza todos los permisos de un rol en una sola operación
    // Elimina los permisos actuales y asigna los nuevos
    // Llama a: CALL sp_roles_syncPermissions(?, ?)
    // Usa transacción para garantizar atomicidad
    public function syncPermissions(int $rolId, array $permissionIds): bool
    {
        $this->beginTransaction();
        try {
            // Elimina todos los permisos actuales del rol
            $this->callSPExecute('sp_roles_revokeAllPermissions', [$rolId]);

            // Asigna los nuevos permisos uno por uno
            foreach ($permissionIds as $permissionId) {
                $this->callSPExecute('sp_roles_assignPermission', [
                    $rolId,
                    (int) $permissionId,
                ]);
            }

            $this->commit();
            return true;

        } catch (RuntimeException $e) {
            $this->rollback();
            return false;
        }
    }

    // ─────────────────────────────────────────────
    // VERIFICACIONES
    // ─────────────────────────────────────────────

    // Verifica si un slug ya está registrado en la BD
    // Útil para validar duplicados antes de insertar
    // Llama a: CALL sp_roles_slugExists(?, ?)
    // El segundo parámetro excluye un ID — útil en edición
    public function slugExists(string $slug, int $excludeId = 0): bool
    {
        $row = $this->callSPSingle('sp_roles_slugExists', [$slug, $excludeId]);
        return $row ? (int) $row['existe'] === 1 : false;
    }

    // Verifica si un rol tiene usuarios asignados
    // Útil para prevenir eliminación de roles en uso
    // Llama a: CALL sp_roles_hasUsers(?)
    public function hasUsers(int $rolId): bool
    {
        $row = $this->callSPSingle('sp_roles_hasUsers', [$rolId]);
        return $row ? (int) $row['total'] > 0 : false;
    }

    // ─────────────────────────────────────────────
    // CONTEOS
    // ─────────────────────────────────────────────

    // Retorna el total de roles registrados
    // Llama a: CALL sp_roles_count()
    public function count(): int
    {
        $row = $this->callSPSingle('sp_roles_count');
        return $row ? (int) $row['total'] : 0;
    }
}