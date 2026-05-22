<?php

/**
 * OrdenServicioModel — Acceso a datos de las órdenes de servicio técnico.
 *
 * Igual que ServicioCatalogoModel: consultas preparadas PDO directas
 * (el módulo no tiene stored procedures). MVC estricto — el SQL vive
 * solo aquí. Hereda de BaseModel por la conexión PDO y las transacciones.
 *
 * Esta Etapa 2 cubre alta en recepción, listado y detalle. Las
 * transiciones de estado, ítems y pagos se agregan en etapas posteriores.
 */
class OrdenServicioModel extends BaseModel
{
    protected string $table = 'ordenes_servicio';

    /**
     * SELECT base con los JOIN de cliente y usuarios (técnico y recepción).
     * users se une dos veces con alias distintos (ut = técnico, ur = recepción).
     */
    private function baseSelect(): string
    {
        return "SELECT os.*,
                       c.nombre   AS cliente_nombre,
                       c.telefono AS cliente_telefono,
                       ut.nombre  AS tecnico_nombre,
                       ur.nombre  AS recepcion_nombre
                FROM ordenes_servicio os
                JOIN clientes c     ON c.id  = os.cliente_id
                LEFT JOIN users ut  ON ut.id = os.tecnico_id
                JOIN users ur       ON ur.id = os.user_recepcion_id";
    }

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    /** Todas las órdenes, más recientes primero. */
    public function findAll(): array
    {
        try {
            $stmt = $this->pdo->query(
                $this->baseSelect() . " ORDER BY os.fecha_recepcion DESC"
            );
            return array_map(fn($r) => OrdenServicioEntity::fromArray($r), $stmt->fetchAll());
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::findAll] ' . $e->getMessage());
            return [];
        }
    }

    /** Órdenes filtradas por estado. */
    public function findByEstado(string $estado): array
    {
        try {
            $stmt = $this->pdo->prepare(
                $this->baseSelect() . " WHERE os.estado = ? ORDER BY os.fecha_recepcion DESC"
            );
            $stmt->execute([$estado]);
            return array_map(fn($r) => OrdenServicioEntity::fromArray($r), $stmt->fetchAll());
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::findByEstado] ' . $e->getMessage());
            return [];
        }
    }

    /** Una orden por id, con sus datos de JOIN. Entidad vacía si no existe. */
    public function findById(int $id): OrdenServicioEntity
    {
        try {
            $stmt = $this->pdo->prepare(
                $this->baseSelect() . " WHERE os.id = ? LIMIT 1"
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? OrdenServicioEntity::fromArray($row) : new OrdenServicioEntity();
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::findById] ' . $e->getMessage());
            return new OrdenServicioEntity();
        }
    }

    /** Órdenes de un cliente — usado por el seguimiento en la tienda (Etapa 5). */
    public function findByCliente(int $clienteId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                $this->baseSelect() . " WHERE os.cliente_id = ? ORDER BY os.fecha_recepcion DESC"
            );
            $stmt->execute([$clienteId]);
            return array_map(fn($r) => OrdenServicioEntity::fromArray($r), $stmt->fetchAll());
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::findByCliente] ' . $e->getMessage());
            return [];
        }
    }

    /** Conteo de órdenes por estado — alimenta los filtros del listado. */
    public function contarPorEstado(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT estado, COUNT(*) AS total FROM ordenes_servicio GROUP BY estado"
            );
            $out = [];
            foreach ($stmt->fetchAll() as $row) {
                $out[$row['estado']] = (int) $row['total'];
            }
            return $out;
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::contarPorEstado] ' . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    /**
     * Genera un código único de orden: OS-00001, OS-00002...
     * Basado en MAX(id)+1 — suficiente para el volumen de un taller; la
     * columna `codigo` tiene UNIQUE como red de seguridad.
     */
    public function generarCodigo(): string
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT COALESCE(MAX(id), 0) + 1 AS siguiente FROM ordenes_servicio"
            );
            $n = (int) ($stmt->fetch()['siguiente'] ?? 1);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::generarCodigo] ' . $e->getMessage());
            $n = (int) (time() % 100000);
        }
        return 'OS-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Crea una orden en recepción (estado inicial 'Recibido').
     * Transaccional: inserta la orden y su primer registro de historial
     * juntos, o ninguno. Devuelve el id nuevo, o 0 si falló.
     */
    public function insert(array $d): int
    {
        try {
            $this->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO ordenes_servicio
                    (codigo, cliente_id, user_recepcion_id, tecnico_id,
                     equipo_descripcion, serial, accesorios_entregados,
                     diagnostico_inicial, observaciones, estado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Recibido')"
            );
            $stmt->execute([
                $d['codigo'],
                (int) $d['cliente_id'],
                (int) $d['user_recepcion_id'],
                !empty($d['tecnico_id']) ? (int) $d['tecnico_id'] : null,
                $d['equipo_descripcion'],
                $d['serial']                ?: null,
                $d['accesorios_entregados'] ?: null,
                $d['diagnostico_inicial']   ?: null,
                $d['observaciones']         ?: null,
            ]);
            $id = (int) $this->pdo->lastInsertId();

            // Primer registro de la bitácora de estados.
            $hist = $this->pdo->prepare(
                "INSERT INTO servicio_historial
                    (orden_id, estado_anterior, estado_nuevo, motivo, user_id)
                 VALUES (?, NULL, 'Recibido', 'Orden creada en recepción', ?)"
            );
            $hist->execute([$id, (int) $d['user_recepcion_id']]);

            $this->commit();
            return $id;
        } catch (\PDOException $e) {
            $this->rollback();
            error_log('[OrdenServicioModel::insert] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Actualiza los datos de recepción de una orden (no toca estado ni
     * totales — eso es del workflow de la Etapa 3).
     */
    public function update(array $d): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE ordenes_servicio
                    SET cliente_id = ?, tecnico_id = ?, equipo_descripcion = ?,
                        serial = ?, accesorios_entregados = ?,
                        diagnostico_inicial = ?, observaciones = ?
                  WHERE id = ?"
            );
            return $stmt->execute([
                (int) $d['cliente_id'],
                !empty($d['tecnico_id']) ? (int) $d['tecnico_id'] : null,
                $d['equipo_descripcion'],
                $d['serial']                ?: null,
                $d['accesorios_entregados'] ?: null,
                $d['diagnostico_inicial']   ?: null,
                $d['observaciones']         ?: null,
                (int) $d['id'],
            ]);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::update] ' . $e->getMessage());
            return false;
        }
    }
}
