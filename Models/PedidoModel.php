<?php

class PedidoModel extends BaseModel
{
    protected string $table      = 'pedidos';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    public function findAll(): array
    {
        $rows = $this->callSP('sp_pedidos_findAll');
        return array_map(fn($row) => PedidoEntity::fromArray($row), $rows);
    }

    public function findById(int $id): PedidoEntity
    {
        $row = $this->callSPSingle('sp_pedidos_findById', [$id]);
        if (!$row) return new PedidoEntity();
        return PedidoEntity::fromArray($row);
    }

    public function findByEstado(string $estado): array
    {
        $rows = $this->callSP('sp_pedidos_findByEstado', [$estado]);
        return array_map(fn($row) => PedidoEntity::fromArray($row), $rows);
    }

    public function findDetalle(int $pedidoId): array
    {
        return $this->callSP('sp_pedidos_findDetalle', [$pedidoId]);
    }

    public function findHistorial(int $pedidoId): array
    {
        return $this->callSP('sp_pedidos_findHistorial', [$pedidoId]);
    }

    public function countByEstado(string $estado): int
    {
        $row = $this->callSPSingle('sp_pedidos_countByEstado', [$estado]);
        return $row ? (int) $row['total'] : 0;
    }

    public function countHoy(): int
    {
        $row = $this->callSPSingle('sp_pedidos_countHoy');
        return $row ? (int) $row['total'] : 0;
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_pedidos_insert', [
            $data['codigo'],
            $data['cliente_id']      ?? null,
            $data['wa_numero']       ?? null,
            $data['tipo_entrega'],
            $data['metodo_pago']     ?? 'Transferencia',
            $data['direccion_envio'] ?? null,
            $data['zona_id']         ?? null,
            $data['subtotal'],
            $data['costo_envio']     ?? 0,
            $data['total'],
            $data['nota']            ?? null,
        ]);
    }

    public function insertDetalle(array $data): bool
    {
        $affected = $this->callSPExecute('sp_pedidos_insertDetalle', [
            $data['pedido_id'],
            $data['producto_id'],
            $data['variante_id']     ?? null,
            $data['nombre_producto'],
            $data['precio_unit'],
            $data['cantidad'],
            $data['subtotal'],
        ]);
        return $affected >= 0;
    }

    public function updateEstado(int $id, string $estado, int $userId, string $nota = ''): bool
    {
        $affected = $this->callSPExecute('sp_pedidos_updateEstado', [
            $id, $estado, $userId, $nota ?: null
        ]);
        return $affected >= 0;
    }

    // Genera código único de 8 caracteres alfanumérico
    public function generarCodigo(): string
    {
        do {
            $codigo = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
            $existe = $this->callSPSingle('sp_pedidos_existeCodigo', [$codigo]);
        } while ($existe && (int)$existe['total'] > 0);

        return $codigo;
    }

    public function findByCliente(int $clienteId): array
    {
        $rows = $this->callSP('sp_pedidos_findByCliente', [$clienteId]);
        return array_map(fn($row) => PedidoEntity::fromArray($row), $rows);
    }

    public function marcarPagado(int $pedidoId, int $userId): bool
    {
        $affected = $this->callSPExecute('sp_pedidos_marcarPagado', [
            $pedidoId, $userId
        ]);
        return $affected >= 0;
    }
}