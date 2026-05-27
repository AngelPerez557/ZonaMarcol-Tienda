<?php

/**
 * SolicitudServicioModel — Solicitudes online de servicio técnico.
 *
 * Igual que el resto de Servicio: prepared statements directos (el módulo
 * no tiene SPs). MVC estricto — todo el SQL vive aquí.
 *
 * Flujo:
 *   - El cliente desde la tienda crea la solicitud (insert).
 *   - En el admin, un empleado la atiende: la convierte en orden_servicio
 *     real (la orden la crea OrdenServicioModel) y marca esta solicitud
 *     como Atendida con el FK a la orden creada.
 *   - O la rechaza con un motivo.
 */
class SolicitudServicioModel extends BaseModel
{
    protected string $table = 'solicitudes_servicio';

    /** SELECT base con JOIN a clientes y opcionalmente a la orden creada. */
    private function baseSelect(): string
    {
        return "SELECT ss.*,
                       c.nombre AS cliente_nombre,
                       c.email  AS cliente_email,
                       os.codigo AS codigo_orden
                FROM solicitudes_servicio ss
                JOIN clientes c             ON c.id  = ss.cliente_id
                LEFT JOIN ordenes_servicio os ON os.id = ss.orden_servicio_id";
    }

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    public function findAll(): array
    {
        try {
            $stmt = $this->pdo->query(
                $this->baseSelect() . " ORDER BY ss.created_at DESC"
            );
            return array_map(fn($r) => SolicitudServicioEntity::fromArray($r), $stmt->fetchAll());
        } catch (\PDOException $e) {
            error_log('[SolicitudServicioModel::findAll] ' . $e->getMessage());
            return [];
        }
    }

    public function findByEstado(string $estado): array
    {
        try {
            $stmt = $this->pdo->prepare(
                $this->baseSelect() . " WHERE ss.estado = ? ORDER BY ss.created_at DESC"
            );
            $stmt->execute([$estado]);
            return array_map(fn($r) => SolicitudServicioEntity::fromArray($r), $stmt->fetchAll());
        } catch (\PDOException $e) {
            error_log('[SolicitudServicioModel::findByEstado] ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): SolicitudServicioEntity
    {
        try {
            $stmt = $this->pdo->prepare($this->baseSelect() . " WHERE ss.id = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row
                ? SolicitudServicioEntity::fromArray($row)
                : new SolicitudServicioEntity();
        } catch (\PDOException $e) {
            error_log('[SolicitudServicioModel::findById] ' . $e->getMessage());
            return new SolicitudServicioEntity();
        }
    }

    /** Solicitudes de un cliente — usado en "Mis solicitudes" en la tienda. */
    public function findByCliente(int $clienteId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                $this->baseSelect() . " WHERE ss.cliente_id = ? ORDER BY ss.created_at DESC"
            );
            $stmt->execute([$clienteId]);
            return array_map(fn($r) => SolicitudServicioEntity::fromArray($r), $stmt->fetchAll());
        } catch (\PDOException $e) {
            error_log('[SolicitudServicioModel::findByCliente] ' . $e->getMessage());
            return [];
        }
    }

    public function contarPendientes(): int
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT COUNT(*) AS n FROM solicitudes_servicio WHERE estado = 'Pendiente'"
            );
            return (int) ($stmt->fetch()['n'] ?? 0);
        } catch (\PDOException $e) {
            error_log('[SolicitudServicioModel::contarPendientes] ' . $e->getMessage());
            return 0;
        }
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    /** Crea una solicitud desde la tienda. Devuelve id, 0 si falla. */
    public function insert(array $d): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO solicitudes_servicio
                    (cliente_id, equipo_descripcion, falla_reportada,
                     telefono_contacto, estado)
                 VALUES (?, ?, ?, ?, 'Pendiente')"
            );
            $stmt->execute([
                (int) $d['cliente_id'],
                $d['equipo_descripcion'],
                $d['falla_reportada']   ?: null,
                $d['telefono_contacto'] ?: null,
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[SolicitudServicioModel::insert] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Marca como Atendida con FK a la orden_servicio creada.
     * El controller hace la orden primero, luego llama acá con el id.
     */
    public function marcarAtendida(int $solicitudId, int $ordenServicioId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE solicitudes_servicio
                    SET estado = 'Atendida',
                        orden_servicio_id = ?,
                        atendida_at = CURRENT_TIMESTAMP
                  WHERE id = ?"
            );
            return $stmt->execute([$ordenServicioId, $solicitudId]);
        } catch (\PDOException $e) {
            error_log('[SolicitudServicioModel::marcarAtendida] ' . $e->getMessage());
            return false;
        }
    }

    /** Rechazar con motivo. */
    public function rechazar(int $id, string $motivo): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE solicitudes_servicio
                    SET estado = 'Rechazada',
                        motivo_rechazo = ?,
                        atendida_at = CURRENT_TIMESTAMP
                  WHERE id = ?"
            );
            return $stmt->execute([$motivo, $id]);
        } catch (\PDOException $e) {
            error_log('[SolicitudServicioModel::rechazar] ' . $e->getMessage());
            return false;
        }
    }
}
