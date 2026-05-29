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

    /**
     * Notificaciones con id > $sinceId — usado por el endpoint SSE para
     * empujar solo lo nuevo, sin recargar todo el feed.
     * PDO directo porque no hay SP dedicado para esto.
     */
    public function findRecent(int $sinceId, int $limit = 20): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, tipo, titulo, mensaje, url, leida, created_at
                   FROM notificaciones
                  WHERE id > ?
                  ORDER BY id ASC
                  LIMIT ?"
            );
            $stmt->bindValue(1, $sinceId, \PDO::PARAM_INT);
            $stmt->bindValue(2, $limit,   \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[NotificacionModel::findRecent] ' . $e->getMessage());
            return [];
        }
    }

    /** Máximo id actual — usado por SSE como cursor inicial. */
    public function maxId(): int
    {
        try {
            $stmt = $this->pdo->query("SELECT COALESCE(MAX(id), 0) AS m FROM notificaciones");
            return (int) ($stmt->fetch()['m'] ?? 0);
        } catch (\PDOException $e) {
            error_log('[NotificacionModel::maxId] ' . $e->getMessage());
            return 0;
        }
    }

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