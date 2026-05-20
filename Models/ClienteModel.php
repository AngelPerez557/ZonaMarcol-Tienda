<?php

class ClienteModel extends BaseModel
{
    protected string $table      = 'clientes';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    // Retorna todos los clientes
    // Llama a: CALL sp_clientes_findAll()
    public function findAll(): array
    {
        $rows = $this->callSP('sp_clientes_findAll');
        return array_map(fn($row) => ClienteEntity::fromArray($row), $rows);
    }

    // Retorna un cliente por su ID
    // Llama a: CALL sp_clientes_findById(?)
    public function findById(int $id): ClienteEntity
    {
        $row = $this->callSPSingle('sp_clientes_findById', [$id]);

        if ($row === null || empty($row)) {
            return new ClienteEntity();
        }

        return ClienteEntity::fromArray($row);
    }

    // Retorna un cliente por su email — login tienda
    // Llama a: CALL sp_clientes_findByEmail(?)
    public function findByEmail(string $email): ClienteEntity
    {
        $row = $this->callSPSingle('sp_clientes_findByEmail', [$email]);

        if ($row === null || empty($row)) {
            return new ClienteEntity();
        }

        return ClienteEntity::fromArray($row);
    }

    // Busca clientes por nombre, email o teléfono — para la Caja
    // Llama a: CALL sp_clientes_search(?)
    // Retorna array plano para JSON
    public function search(string $query): array
    {
        return $this->callSP('sp_clientes_search', [$query]);
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    // Inserta un nuevo cliente
    // Llama a: CALL sp_clientes_insert(?)
    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_clientes_insert', [
            $data['nombre'],
            $data['email']     ?? null,
            $data['telefono']  ?? null,
            $data['password']  ?? null,
        ]);
    }

    // Actualiza un cliente existente
    // Llama a: CALL sp_clientes_update(?)
    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_clientes_update', [
            $data['id'],
            $data['nombre'],
            $data['email']     ?? null,
            $data['telefono']  ?? null,
            $data['direccion'] ?? null,
        ]);
        return $affected > 0;
    }

    // Activa o desactiva un cliente
    // Llama a: CALL sp_clientes_toggleActivo(?)
    public function toggleActivo(int $id, int $activo): bool
    {
        $affected = $this->callSPExecute('sp_clientes_toggleActivo', [$id, $activo]);
        return $affected > 0;
    }

    // ─────────────────────────────────────────────
    // CONTEOS
    // ─────────────────────────────────────────────

    public function count(): int
    {
        $row = $this->callSPSingle('sp_clientes_count');
        return $row ? (int) $row['total'] : 0;
    }

    public function emailExists(string $email): bool
    {
        $row = $this->callSPSingle('sp_clientes_emailExists', [$email]);
        return $row ? (int)$row['existe'] > 0 : false;
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $affected = $this->callSPExecute('sp_clientes_updatePassword', [$id, $hash]);
        return $affected >= 0;
    }

    public function emailExistsForUpdate(string $email, int $excludeId): bool
    {
        $row = $this->callSPSingle('sp_clientes_emailExistsForUpdate', [$email, $excludeId]);
        return $row && (int)$row['existe'] > 0;
    }
    
}