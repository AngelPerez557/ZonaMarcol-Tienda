<?php

class NotificacionModel extends BaseModel
{
    protected string $table      = 'notificaciones';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        return $this->callSP('sp_notificaciones_findAll');
    }

    public function countNoLeidas(): int
    {
        $row = $this->callSPSingle('sp_notificaciones_countNoLeidas');
        return $row ? (int) $row['total'] : 0;
    }

    public function insert(string $tipo, string $titulo, string $mensaje, string $url = ''): int
    {
        return $this->callSPInsert('sp_notificaciones_insert', [$tipo, $titulo, $mensaje, $url ?: null]);
    }

    public function marcarLeida(int $id): void
    {
        $this->callSPExecute('sp_notificaciones_marcarLeida', [$id]);
    }

    public function marcarTodasLeidas(): void
    {
        $this->callSPExecute('sp_notificaciones_marcarTodasLeidas');
    }

    public function eliminar(int $id): void
    {
        $this->callSPExecute('sp_notificaciones_delete', [$id]);
    }

    // ─────────────────────────────────────────────
    // HELPERS — crear notificaciones específicas
    // ─────────────────────────────────────────────

    public function nuevoPedido(string $codigo, string $clienteNombre, float $total): void
    {
        $this->insert(
            'pedido',
            '🛍️ Nuevo pedido recibido',
            "Pedido #{$codigo} de {$clienteNombre} — L. " . number_format($total, 2),
            'Pedidos/index'
        );
    }
}