<?php

class ProductoModel extends BaseModel
{
    protected string $table      = 'productos';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    // Retorna todos los productos con su categoría
    // Llama a: CALL sp_productos_findAll()
    public function findAll(): array
    {
        $rows = $this->callSP('sp_productos_findAll');
        return array_map(fn($row) => ProductoEntity::fromArray($row), $rows);
    }

    // Retorna solo productos activos
    // Llama a: CALL sp_productos_findActivos()
    public function findActivos(): array
    {
        $rows = $this->callSP('sp_productos_findActivos');
        return array_map(fn($row) => ProductoEntity::fromArray($row), $rows);
    }

    // Retorna un producto por su ID
    // Llama a: CALL sp_productos_findById(?)
    public function findById(int $id): ProductoEntity
    {
        $row = $this->callSPSingle('sp_productos_findById', [$id]);

        if ($row === null || empty($row)) {
            return new ProductoEntity();
        }

        return ProductoEntity::fromArray($row);
    }

    // Busca productos por nombre — para la caja
    // Llama a: CALL sp_productos_findByNombre(?)
    public function findByNombre(string $nombre): array
    {
        $rows = $this->callSP('sp_productos_findByNombre', [$nombre]);
        return array_map(fn($row) => ProductoEntity::fromArray($row), $rows);
    }

    // Busca variante por código de barras — para la caja
    // Llama a: CALL sp_productos_findByBarras(?)
    public function findByBarras(string $barras): ?array
    {
        return $this->callSPSingle('sp_productos_findByBarras', [$barras]);
    }

    // Busca producto simple (sin variantes) por código de barras — para la caja
    // Llama a: CALL sp_productos_findSimpleByBarras(?)
    public function findSimpleByBarras(string $barras): ?array
    {
        return $this->callSPSingle('sp_productos_findSimpleByBarras', [$barras]);
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    // Inserta un nuevo producto
    // Llama a: CALL sp_productos_insert(...)
    // Retorna el ID del producto creado
    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_productos_insert', [
            $data['categoria_id'],
            $data['nombre'],
            $data['descripcion']     ?? null,
            $data['precio_base']     ?? null,
            $data['tiene_variantes'] ?? 0,
            $data['stock']           ?? 0,
            $data['codigo_barras']   ?? null,
            $data['image_url']       ?? null,
        ]);
    }

    // Actualiza un producto existente
    // Llama a: CALL sp_productos_update(...)
    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_productos_update', [
            $data['id'],
            $data['categoria_id'],
            $data['nombre'],
            $data['descripcion']     ?? null,
            $data['precio_base']     ?? null,
            $data['stock']           ?? 0,
            $data['codigo_barras']   ?? null,
            $data['image_url']       ?? null,
        ]);
        return $affected > 0;
    }

    // Activa o desactiva un producto (soft delete)
    // Llama a: CALL sp_productos_toggleActivo(?)
    public function toggleActivo(int $id, int $activo): bool
    {
        $affected = $this->callSPExecute('sp_productos_toggleActivo', [$id, $activo]);
        return $affected > 0;
    }

    // Elimina un producto (soft delete — pone activo = 0)
    // Llama a: CALL sp_productos_delete(?)
    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_productos_delete', [$id]);
        return $affected > 0;
    }

    // Descuenta stock de un producto simple al vender
    // Llama a: CALL sp_productos_updateStock(?, ?)
    // Retorna true si había stock suficiente
    public function descontarStock(int $id, int $cantidad): bool
    {
        $row = $this->callSPSingle('sp_productos_updateStock', [$id, $cantidad]);
        return $row ? (int) $row['afectado'] > 0 : false;
    }

    // ─────────────────────────────────────────────
    // CONTEOS
    // ─────────────────────────────────────────────

    // Retorna el total de productos
    // Llama a: CALL sp_productos_count()
    public function count(): int
    {
        $row = $this->callSPSingle('sp_productos_count');
        return $row ? (int) $row['total'] : 0;
    }

    // Retorna el total de productos activos
    // Llama a: CALL sp_productos_countActivos()
    public function countActivos(): int
    {
        $row = $this->callSPSingle('sp_productos_countActivos');
        return $row ? (int) $row['total'] : 0;
    }
    
    public function findVariantes(int $productoId): array
    {
        $rows = $this->callSP('sp_productos_findVariantes', [$productoId]);
        return array_map(fn($row) => VarianteEntity::fromArray($row), $rows);
    }
    // ── Actualizar stock de producto simple ────────
    public function updateStock(int $id, int $cantidad): bool
    {
        $affected = $this->callSPExecute('sp_productos_updateStock', [$id, $cantidad]);
        return $affected >= 0;
    }

    // ── Actualizar stock de variante ───────────────
    public function updateVarianteStock(int $id, int $cantidad): bool
    {
        $affected = $this->callSPExecute('sp_variantes_updateStock', [$id, $cantidad]);
        return $affected >= 0;
    }

    // ── Buscar variante por ID ─────────────────────
    public function findVarianteById(int $id): ?VarianteEntity
    {
        $row = $this->callSPSingle('sp_variantes_findById', [$id]);
        if (!$row) return null;
        return VarianteEntity::fromArray($row);
    }
    
    public function toggleVisibleTienda(int $id, int $visible): bool
    {
        $affected = $this->callSPExecute('sp_productos_toggleVisibleTienda', [$id, $visible]);
        return $affected > 0;
    }
}