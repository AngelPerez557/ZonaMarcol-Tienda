<?php

/**
 * OrdenServicioPagoModel — Pagos contra órdenes de servicio.
 *
 * Sin SPs (igual que el resto del módulo). Caller (controller) es
 * responsable de:
 *   1. Llamar insert() con el caja_sesion_id activo del usuario.
 *   2. Llamar OrdenServicioModel::recalcularPagado() después.
 *
 * Genera `recibo_numero` con formato RS-NNNNNN (basado en MAX(id)+1).
 */
class OrdenServicioPagoModel extends BaseModel
{
    protected string $table = 'orden_servicio_pagos';

    private function baseSelect(): string
    {
        return "SELECT osp.*, u.nombre AS user_nombre
                FROM orden_servicio_pagos osp
                JOIN users u ON u.id = osp.user_id";
    }

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    public function findByOrden(int $ordenId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                $this->baseSelect() . " WHERE osp.orden_id = ? ORDER BY osp.fecha ASC, osp.id ASC"
            );
            $stmt->execute([$ordenId]);
            return array_map(
                fn($r) => OrdenServicioPagoEntity::fromArray($r),
                $stmt->fetchAll()
            );
        } catch (\PDOException $e) {
            error_log('[OrdenServicioPagoModel::findByOrden] ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): OrdenServicioPagoEntity
    {
        try {
            $stmt = $this->pdo->prepare($this->baseSelect() . " WHERE osp.id = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row
                ? OrdenServicioPagoEntity::fromArray($row)
                : new OrdenServicioPagoEntity();
        } catch (\PDOException $e) {
            error_log('[OrdenServicioPagoModel::findById] ' . $e->getMessage());
            return new OrdenServicioPagoEntity();
        }
    }

    public function sumarTotal(int $ordenId): float
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(monto), 0) AS total
                   FROM orden_servicio_pagos
                  WHERE orden_id = ?"
            );
            $stmt->execute([$ordenId]);
            return (float) ($stmt->fetch()['total'] ?? 0);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioPagoModel::sumarTotal] ' . $e->getMessage());
            return 0.0;
        }
    }

    /** Hay al menos un pago registrado para esta orden? */
    public function tienePagos(int $ordenId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT 1 FROM orden_servicio_pagos WHERE orden_id = ? LIMIT 1"
            );
            $stmt->execute([$ordenId]);
            return (bool) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log('[OrdenServicioPagoModel::tienePagos] ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    public function generarReciboNumero(): string
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT COALESCE(MAX(id), 0) + 1 AS siguiente FROM orden_servicio_pagos"
            );
            $n = (int) ($stmt->fetch()['siguiente'] ?? 1);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioPagoModel::generarReciboNumero] ' . $e->getMessage());
            $n = (int) (time() % 1000000);
        }
        return 'RS-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
    }

    /** Inserta un pago. Devuelve id, 0 si falla. */
    public function insert(array $d): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO orden_servicio_pagos
                    (orden_id, tipo, monto, metodo, caja_sesion_id,
                     recibo_numero, user_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                (int) $d['orden_id'],
                $d['tipo'],
                round((float) $d['monto'], 2),
                $d['metodo'],
                !empty($d['caja_sesion_id']) ? (int) $d['caja_sesion_id'] : null,
                $d['recibo_numero'] ?? null,
                (int) $d['user_id'],
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[OrdenServicioPagoModel::insert] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Anula un pago — DELETE directo. Útil cuando se cargó por error.
     * El caller debe llamar a OrdenServicioModel::recalcularPagado() después.
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM orden_servicio_pagos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioPagoModel::delete] ' . $e->getMessage());
            return false;
        }
    }
}
