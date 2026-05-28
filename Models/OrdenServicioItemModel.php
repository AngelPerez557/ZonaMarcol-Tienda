<?php

/**
 * OrdenServicioItemModel — Acceso a datos de los ítems del presupuesto
 * de una orden de servicio. Sin SPs (igual que el resto del módulo).
 *
 * Reglas de negocio:
 *   - El `subtotal` se calcula y persiste server-side. Nunca se confía
 *     en lo que envíe el cliente.
 *   - Insertar/eliminar items NO actualiza `ordenes_servicio.total_actual`
 *     directamente — el caller (controller) llama a
 *     OrdenServicioModel::recalcularTotal() después para mantener la
 *     responsabilidad clara y poder recalcular tras cambios en lote.
 */
class OrdenServicioItemModel extends BaseModel
{
    protected string $table = 'orden_servicio_items';

    private function baseSelect(): string
    {
        return "SELECT osi.*,
                       u.nombre  AS usuario_nombre,
                       sc.nombre AS catalogo_nombre
                FROM orden_servicio_items osi
                JOIN users u                 ON u.id  = osi.agregado_por
                LEFT JOIN servicios_catalogo sc ON sc.id = osi.servicio_catalogo_id";
    }

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    /** Items de una orden, en el orden en que fueron agregados. */
    public function findByOrden(int $ordenId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                $this->baseSelect() . " WHERE osi.orden_id = ? ORDER BY osi.agregado_en ASC, osi.id ASC"
            );
            $stmt->execute([$ordenId]);
            return array_map(
                fn($r) => OrdenServicioItemEntity::fromArray($r),
                $stmt->fetchAll()
            );
        } catch (\PDOException $e) {
            error_log('[OrdenServicioItemModel::findByOrden] ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): OrdenServicioItemEntity
    {
        try {
            $stmt = $this->pdo->prepare($this->baseSelect() . " WHERE osi.id = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row
                ? OrdenServicioItemEntity::fromArray($row)
                : new OrdenServicioItemEntity();
        } catch (\PDOException $e) {
            error_log('[OrdenServicioItemModel::findById] ' . $e->getMessage());
            return new OrdenServicioItemEntity();
        }
    }

    /** Suma de subtotales para recalcular el total de la orden. */
    public function sumarTotal(int $ordenId, bool $soloAprobados = false): float
    {
        try {
            $sql = "SELECT COALESCE(SUM(subtotal), 0) AS total
                      FROM orden_servicio_items
                     WHERE orden_id = ?";
            if ($soloAprobados) {
                $sql .= " AND aprobado_cliente = 1";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$ordenId]);
            return (float) ($stmt->fetch()['total'] ?? 0);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioItemModel::sumarTotal] ' . $e->getMessage());
            return 0.0;
        }
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    /** Inserta un ítem. subtotal se recomputa server-side. Devuelve id, 0 si falla. */
    public function insert(array $d): int
    {
        try {
            $cantidad = max(1, (int) $d['cantidad']);
            $precio   = max(0, (float) $d['precio_unitario']);
            $subtotal = round($precio * $cantidad, 2);

            $stmt = $this->pdo->prepare(
                "INSERT INTO orden_servicio_items
                    (orden_id, tipo, servicio_catalogo_id, descripcion,
                     cantidad, precio_unitario, subtotal,
                     aprobado_cliente, dias_garantia, agregado_por)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                (int) $d['orden_id'],
                $d['tipo'],
                !empty($d['servicio_catalogo_id']) ? (int) $d['servicio_catalogo_id'] : null,
                $d['descripcion'],
                $cantidad,
                $precio,
                $subtotal,
                isset($d['aprobado_cliente']) ? (int) $d['aprobado_cliente'] : 0,
                isset($d['dias_garantia']) ? (int) $d['dias_garantia'] : 30,
                (int) $d['agregado_por'],
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[OrdenServicioItemModel::insert] ' . $e->getMessage());
            return 0;
        }
    }

    /** Borra un ítem por id. Devuelve la orden_id afectada o 0. */
    public function delete(int $id): int
    {
        try {
            // Capturamos el orden_id antes de borrar para que el controller
            // pueda recalcular el total de esa orden.
            $stmt = $this->pdo->prepare("SELECT orden_id FROM orden_servicio_items WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) return 0;

            $ordenId = (int) $row['orden_id'];
            $del = $this->pdo->prepare("DELETE FROM orden_servicio_items WHERE id = ?");
            return $del->execute([$id]) ? $ordenId : 0;
        } catch (\PDOException $e) {
            error_log('[OrdenServicioItemModel::delete] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Marca/desmarca un ítem como aprobado por el cliente. Útil cuando el
     * cliente revisa el presupuesto y aprueba items selectivamente.
     */
    public function marcarAprobado(int $id, bool $aprobado): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE orden_servicio_items SET aprobado_cliente = ? WHERE id = ?"
            );
            return $stmt->execute([$aprobado ? 1 : 0, $id]);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioItemModel::marcarAprobado] ' . $e->getMessage());
            return false;
        }
    }

    /** Aprueba todos los items de una orden de una vez (bulk). */
    public function aprobarTodos(int $ordenId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE orden_servicio_items SET aprobado_cliente = 1 WHERE orden_id = ?"
            );
            return $stmt->execute([$ordenId]);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioItemModel::aprobarTodos] ' . $e->getMessage());
            return false;
        }
    }
}
