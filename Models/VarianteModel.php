<?php

class VarianteModel extends BaseModel
{
    protected string $table      = 'producto_variantes';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    // Retorna todas las variantes de un producto
    // Llama a: CALL sp_variantes_findByProducto(?)
    public function findByProducto(int $productoId): array
    {
        $rows = $this->callSP('sp_variantes_findByProducto', [$productoId]);
        return array_map(fn($row) => VarianteEntity::fromArray($row), $rows);
    }

    // Retorna una variante por su ID
    // Llama a: CALL sp_variantes_findById(?)
    public function findById(int $id): VarianteEntity
    {
        $row = $this->callSPSingle('sp_variantes_findById', [$id]);

        if ($row === null || empty($row)) {
            return new VarianteEntity();
        }

        return VarianteEntity::fromArray($row);
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    // Inserta una nueva variante
    // Llama a: CALL sp_variantes_insert(...)
    // Retorna el ID de la variante creada
    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_variantes_insert', [
            $data['producto_id'],
            $data['nombre'],
            $data['precio']        ?? null,
            $data['stock']         ?? 0,
            $data['codigo_barras'] ?? null,
            $data['image_url']     ?? null,
            $data['orden']         ?? 0,
        ]);
    }

    // Actualiza una variante existente
    // Llama a: CALL sp_variantes_update(...)
    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_variantes_update', [
            $data['id'],
            $data['nombre'],
            $data['precio']        ?? null,
            $data['stock']         ?? 0,
            $data['codigo_barras'] ?? null,
            $data['image_url']     ?? null,
            $data['orden']         ?? 0,
        ]);
        return $affected > 0;
    }

    // Activa o desactiva una variante
    // Llama a: CALL sp_variantes_toggleActivo(?, ?)
    public function toggleActivo(int $id, int $activo): bool
    {
        $affected = $this->callSPExecute('sp_variantes_toggleActivo', [$id, $activo]);
        return $affected > 0;
    }

    // Elimina una variante permanentemente
    // Llama a: CALL sp_variantes_delete(?)
    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_variantes_delete', [$id]);
        return $affected > 0;
    }

    // Descuenta stock de una variante al vender
    // Llama a: CALL sp_variantes_updateStock(?, ?)
    // Retorna true si había stock suficiente
    public function descontarStock(int $id, int $cantidad): bool
    {
        $row = $this->callSPSingle('sp_variantes_updateStock', [$id, $cantidad]);
        return $row ? (int) $row['afectado'] > 0 : false;
    }
}