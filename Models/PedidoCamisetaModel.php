<?php

class PedidoCamisetaModel extends BaseModel
{
    protected string $table      = 'pedidos_camiseta';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        $rows = $this->callSP('sp_pedidos_camiseta_findAll');
        return array_map(fn($r) => PedidoCamisetaEntity::fromArray($r), $rows);
    }

    public function findByTemporada(int $temporadaId, ?string $estado = null): array
    {
        $rows = $this->callSP('sp_pedidos_camiseta_findByTemporada', [$temporadaId, $estado]);
        return array_map(fn($r) => PedidoCamisetaEntity::fromArray($r), $rows);
    }

    public function findById(int $id): PedidoCamisetaEntity
    {
        $row = $this->callSPSingle('sp_pedidos_camiseta_findById', [$id]);
        if (!$row) return new PedidoCamisetaEntity();
        return PedidoCamisetaEntity::fromArray($row);
    }

    public function findDetalle(int $pedidoId): array
    {
        return $this->callSP('sp_pedidos_camiseta_findDetalle', [$pedidoId]);
    }

    public function generarCodigo(): string
    {
        do {
            $codigo = 'PC-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));
            $existe = $this->callSPSingle('sp_pedidos_camiseta_existeCodigo', [$codigo]);
        } while ($existe && (int) $existe['total'] > 0);
        return $codigo;
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_pedidos_camiseta_insert', [
            $data['codigo'],
            $data['cliente_id'],
            $data['temporada_id'],
            (float) $data['subtotal'],
            (float) $data['total'],
            $data['nota'] ?? null,
        ]);
    }

    public function insertDetalle(array $data): bool
    {
        $affected = $this->callSPExecute('sp_pedidos_camiseta_insertDetalle', [
            $data['pedido_id'],
            $data['equipacion_id'],
            $data['talla_hombre_id']    ?? null,
            $data['talla_mujer_id']     ?? null,
            $data['talla_infantil_id']  ?? null,
            $data['nombre_personalizado'] ?? null,
            $data['numero_personalizado'] ?? null,
            $data['competicion_id']     ?? null,
            (float) $data['precio_unitario'],
            (float) ($data['precio_extras'] ?? 0),
            (int) $data['cantidad'],
            (float) $data['subtotal'],
        ]);
        return $affected >= 0;
    }

    public function registrarAnticipo(int $id, float $monto): bool
    {
        return $this->callSPExecute('sp_pedidos_camiseta_registrarAnticipo', [$id, $monto]) >= 0;
    }

    public function updateEstado(int $id, string $estado): bool
    {
        return $this->callSPExecute('sp_pedidos_camiseta_updateEstado', [$id, $estado]) >= 0;
    }

    public function exportarLote(int $temporadaId): array
    {
        return $this->callSP('sp_pedidos_camiseta_exportar', [$temporadaId]);
    }
}
