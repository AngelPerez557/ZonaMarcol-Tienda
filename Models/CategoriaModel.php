<?php

class CategoriaModel extends BaseModel
{
    protected string $table      = 'categorias';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    // Retorna todas las categorías
    // Llama a: CALL sp_categorias_findAll()
    public function findAll(): array
    {
        $rows = $this->callSP('sp_categorias_findAll');
        return array_map(fn($row) => CategoriaEntity::fromArray($row), $rows);
    }

    // Retorna solo categorías activas — para selects en formularios
    // Llama a: CALL sp_categorias_findActivas()
    public function findActivas(): array
    {
        $rows = $this->callSP('sp_categorias_findActivas');
        return array_map(fn($row) => CategoriaEntity::fromArray($row), $rows);
    }

    // Retorna una categoría por su ID
    // Llama a: CALL sp_categorias_findById(?)
    public function findById(int $id): CategoriaEntity
    {
        $row = $this->callSPSingle('sp_categorias_findById', [$id]);

        if ($row === null || empty($row)) {
            return new CategoriaEntity();
        }

        return CategoriaEntity::fromArray($row);
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    // Inserta una nueva categoría
    // Llama a: CALL sp_categorias_insert(?, ?)
    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_categorias_insert', [
            $data['nombre'],
            $data['descripcion'] ?? null,
        ]);
    }

    // Actualiza una categoría existente
    // Llama a: CALL sp_categorias_update(?, ?, ?)
    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_categorias_update', [
            $data['id'],
            $data['nombre'],
            $data['descripcion'] ?? null,
        ]);
        return $affected > 0;
    }

    // Activa o desactiva una categoría
    // Llama a: CALL sp_categorias_toggleActivo(?, ?)
    public function toggleActivo(int $id, int $activo): bool
    {
        $affected = $this->callSPExecute('sp_categorias_toggleActivo', [$id, $activo]);
        return $affected > 0;
    }

    // Soft delete — desactiva la categoría
    // Llama a: CALL sp_categorias_delete(?)
    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_categorias_delete', [$id]);
        return $affected > 0;
    }

    // ─────────────────────────────────────────────
    // VERIFICACIONES
    // ─────────────────────────────────────────────

    // Verifica si la categoría tiene productos activos
    // Útil para prevenir desactivar categorías en uso
    // Llama a: CALL sp_categorias_hasProductos(?)
    public function hasProductos(int $id): bool
    {
        $row = $this->callSPSingle('sp_categorias_hasProductos', [$id]);
        return $row ? (int) $row['total'] > 0 : false;
    }

    // ─────────────────────────────────────────────
    // CONTEOS
    // ─────────────────────────────────────────────

    // Retorna el total de categorías
    // Llama a: CALL sp_categorias_count()
    public function count(): int
    {
        $row = $this->callSPSingle('sp_categorias_count');
        return $row ? (int) $row['total'] : 0;
    }
}