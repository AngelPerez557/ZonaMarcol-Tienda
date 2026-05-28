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

    // ─────────────────────────────────────────────
    // ETAPA 3 — WORKFLOW DE LA ORDEN
    // ─────────────────────────────────────────────

    /** Transiciones legales — usado por cambiarEstado() y por el controller. */
    public const TRANSICIONES = [
        'Recibido'             => ['Diagnostico', 'Cancelado'],
        'Diagnostico'          => ['Esperando aprobacion', 'Cancelado'],
        'Esperando aprobacion' => ['En reparacion', 'Cancelado'],
        'En reparacion'        => ['Listo', 'Cancelado'],
        'Listo'                => ['Entregado', 'Cancelado'],
        'Entregado'            => [],   // estado terminal
        'Cancelado'            => [],   // estado terminal
    ];

    /**
     * Cambia el estado de una orden. Valida la transición contra la
     * whitelist y deja registro en `servicio_historial`. Transaccional:
     * o se actualizan los dos rows, o nada.
     */
    public function cambiarEstado(int $ordenId, string $nuevo, ?string $motivo, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT estado, saldo FROM ordenes_servicio WHERE id = ? LIMIT 1"
            );
            $stmt->execute([$ordenId]);
            $row = $stmt->fetch();
            if (!$row) return false;

            $anterior = (string) $row['estado'];
            $legales  = self::TRANSICIONES[$anterior] ?? [];
            if (!in_array($nuevo, $legales, true)) {
                return false;
            }

            // Guard de Etapa 4: no se entrega con saldo pendiente.
            if ($nuevo === 'Entregado' && (float) $row['saldo'] > 0.009) {
                return false;
            }

            $this->beginTransaction();

            // Para Cancelado guardamos el motivo en la propia orden y se
            // fija fecha_entrega = NULL. Para Entregado fijamos fecha_entrega.
            if ($nuevo === 'Cancelado') {
                $up = $this->pdo->prepare(
                    "UPDATE ordenes_servicio
                        SET estado = ?, motivo_cancelacion = ?
                      WHERE id = ?"
                );
                $up->execute([$nuevo, $motivo ?: null, $ordenId]);
            } elseif ($nuevo === 'Entregado') {
                $up = $this->pdo->prepare(
                    "UPDATE ordenes_servicio
                        SET estado = ?, fecha_entrega = CURRENT_TIMESTAMP
                      WHERE id = ?"
                );
                $up->execute([$nuevo, $ordenId]);
            } else {
                $up = $this->pdo->prepare(
                    "UPDATE ordenes_servicio SET estado = ? WHERE id = ?"
                );
                $up->execute([$nuevo, $ordenId]);
            }

            $hist = $this->pdo->prepare(
                "INSERT INTO servicio_historial
                    (orden_id, estado_anterior, estado_nuevo, motivo, user_id)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $hist->execute([$ordenId, $anterior, $nuevo, $motivo ?: null, $userId]);

            $this->commit();
            return true;
        } catch (\PDOException $e) {
            $this->rollback();
            error_log('[OrdenServicioModel::cambiarEstado] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recalcula `total_actual` y `saldo` a partir de los items.
     * Por convención: total = suma de TODOS los items (aprobados o no).
     * El criterio de cobrar solo aprobados se aplica en caja (Etapa 4).
     */
    public function recalcularTotal(int $ordenId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE ordenes_servicio os
                    SET total_actual = (
                            SELECT COALESCE(SUM(subtotal), 0)
                              FROM orden_servicio_items
                             WHERE orden_id = os.id
                        ),
                        saldo = (
                            SELECT COALESCE(SUM(subtotal), 0)
                              FROM orden_servicio_items
                             WHERE orden_id = os.id
                        ) - total_pagado
                  WHERE os.id = ?"
            );
            return $stmt->execute([$ordenId]);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::recalcularTotal] ' . $e->getMessage());
            return false;
        }
    }

    /** Histórico de cambios de estado, más reciente primero. */
    public function findHistorial(int $ordenId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT sh.*, u.nombre AS user_nombre
                   FROM servicio_historial sh
                   JOIN users u ON u.id = sh.user_id
                  WHERE sh.orden_id = ?
                  ORDER BY sh.fecha DESC, sh.id DESC"
            );
            $stmt->execute([$ordenId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::findHistorial] ' . $e->getMessage());
            return [];
        }
    }

    /** Actualiza solo el diagnóstico técnico — separado del update general. */
    public function actualizarDiagnostico(int $ordenId, string $diagnostico): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE ordenes_servicio SET diagnostico_inicial = ? WHERE id = ?"
            );
            return $stmt->execute([$diagnostico ?: null, $ordenId]);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::actualizarDiagnostico] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recalcula `total_pagado` y `saldo` a partir de orden_servicio_pagos.
     * Atómica: una sola consulta. La llama el controller después de cada
     * insert/delete de pago.
     */
    public function recalcularPagado(int $ordenId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE ordenes_servicio os
                    SET total_pagado = (
                            SELECT COALESCE(SUM(monto), 0)
                              FROM orden_servicio_pagos
                             WHERE orden_id = os.id
                        ),
                        saldo = total_actual - (
                            SELECT COALESCE(SUM(monto), 0)
                              FROM orden_servicio_pagos
                             WHERE orden_id = os.id
                        )
                  WHERE os.id = ?"
            );
            return $stmt->execute([$ordenId]);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::recalcularPagado] ' . $e->getMessage());
            return false;
        }
    }

    /** Asigna o cambia el técnico responsable. */
    public function asignarTecnico(int $ordenId, ?int $tecnicoId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE ordenes_servicio SET tecnico_id = ? WHERE id = ?"
            );
            return $stmt->execute([$tecnicoId ?: null, $ordenId]);
        } catch (\PDOException $e) {
            error_log('[OrdenServicioModel::asignarTecnico] ' . $e->getMessage());
            return false;
        }
    }
}
