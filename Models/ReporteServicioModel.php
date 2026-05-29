<?php

/**
 * ReporteServicioModel — Métricas del módulo Servicio Técnico.
 *
 * Todas las queries son agregaciones de las tablas:
 *   - ordenes_servicio
 *   - orden_servicio_items
 *   - orden_servicio_pagos
 *
 * Sin SPs (el módulo no los tiene). Prepared statements directos.
 * Lectura pura — no muta nada.
 */
class ReporteServicioModel extends BaseModel
{
    protected string $table = 'ordenes_servicio';

    // ─────────────────────────────────────────────
    // 1) RESUMEN — KPIs principales
    // ─────────────────────────────────────────────

    /**
     * Devuelve KPIs de cabecera: total órdenes, abiertas, entregadas,
     * canceladas, ingresos totales y saldo pendiente global.
     */
    public function resumen(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT
                    COUNT(*) AS total_ordenes,
                    SUM(CASE WHEN estado NOT IN ('Entregado','Cancelado') THEN 1 ELSE 0 END) AS abiertas,
                    SUM(CASE WHEN estado = 'Entregado' THEN 1 ELSE 0 END) AS entregadas,
                    SUM(CASE WHEN estado = 'Cancelado' THEN 1 ELSE 0 END) AS canceladas,
                    COALESCE(SUM(total_pagado), 0) AS ingresos_totales,
                    COALESCE(SUM(CASE WHEN estado NOT IN ('Entregado','Cancelado') THEN saldo ELSE 0 END), 0) AS saldo_pendiente
                 FROM ordenes_servicio"
            );
            $row = $stmt->fetch();
            return $row ?: [
                'total_ordenes'=>0,'abiertas'=>0,'entregadas'=>0,'canceladas'=>0,
                'ingresos_totales'=>0,'saldo_pendiente'=>0,
            ];
        } catch (\PDOException $e) {
            error_log('[ReporteServicioModel::resumen] ' . $e->getMessage());
            return ['total_ordenes'=>0,'abiertas'=>0,'entregadas'=>0,'canceladas'=>0,
                    'ingresos_totales'=>0,'saldo_pendiente'=>0];
        }
    }

    // ─────────────────────────────────────────────
    // 2) INGRESOS POR MES — últimos N meses
    // ─────────────────────────────────────────────

    public function ingresosPorMes(int $meses = 12): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                        COUNT(*) AS pagos,
                        COALESCE(SUM(monto), 0) AS ingresos
                   FROM orden_servicio_pagos
                  WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                  GROUP BY DATE_FORMAT(fecha, '%Y-%m')
                  ORDER BY mes ASC"
            );
            $stmt->execute([$meses]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[ReporteServicioModel::ingresosPorMes] ' . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────
    // 3) ÓRDENES POR ESTADO — distribución actual
    // ─────────────────────────────────────────────

    public function ordenesPorEstado(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT estado, COUNT(*) AS total
                   FROM ordenes_servicio
                  GROUP BY estado"
            );
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[ReporteServicioModel::ordenesPorEstado] ' . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────
    // 4) GARANTÍAS VIGENTES
    // ─────────────────────────────────────────────

    /**
     * Items entregados cuya garantía aún no venció.
     * Garantía vigente = fecha_entrega + dias_garantia >= HOY.
     * Solo se cuentan items aprobados (los borradores no aplican).
     */
    public function garantiasVigentes(int $limit = 50): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT osi.id, osi.descripcion, osi.dias_garantia,
                        os.codigo AS orden_codigo, os.equipo_descripcion,
                        os.fecha_entrega,
                        c.nombre   AS cliente_nombre,
                        c.telefono AS cliente_telefono,
                        DATE_ADD(os.fecha_entrega, INTERVAL osi.dias_garantia DAY) AS vence_el,
                        DATEDIFF(
                            DATE_ADD(os.fecha_entrega, INTERVAL osi.dias_garantia DAY),
                            CURDATE()
                        ) AS dias_restantes
                   FROM orden_servicio_items osi
                   JOIN ordenes_servicio os ON os.id = osi.orden_id
                   JOIN clientes c          ON c.id  = os.cliente_id
                  WHERE osi.aprobado_cliente = 1
                    AND os.estado = 'Entregado'
                    AND os.fecha_entrega IS NOT NULL
                    AND DATE_ADD(os.fecha_entrega, INTERVAL osi.dias_garantia DAY) >= CURDATE()
                  ORDER BY dias_restantes ASC
                  LIMIT ?"
            );
            $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[ReporteServicioModel::garantiasVigentes] ' . $e->getMessage());
            return [];
        }
    }

    public function contarGarantiasVigentes(): int
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT COUNT(*) AS n
                   FROM orden_servicio_items osi
                   JOIN ordenes_servicio os ON os.id = osi.orden_id
                  WHERE osi.aprobado_cliente = 1
                    AND os.estado = 'Entregado'
                    AND os.fecha_entrega IS NOT NULL
                    AND DATE_ADD(os.fecha_entrega, INTERVAL osi.dias_garantia DAY) >= CURDATE()"
            );
            return (int) ($stmt->fetch()['n'] ?? 0);
        } catch (\PDOException $e) {
            error_log('[ReporteServicioModel::contarGarantiasVigentes] ' . $e->getMessage());
            return 0;
        }
    }

    // ─────────────────────────────────────────────
    // 5) TÉCNICOS PRODUCTIVOS
    // ─────────────────────────────────────────────

    /**
     * Ranking de técnicos por órdenes entregadas + ingresos generados.
     * Solo cuentan órdenes entregadas (las canceladas no aportan).
     */
    public function tecnicosProductivos(int $limit = 10): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT u.id, u.nombre,
                        COUNT(os.id) AS ordenes_entregadas,
                        COALESCE(SUM(os.total_actual), 0) AS ingresos_generados,
                        AVG(
                            TIMESTAMPDIFF(
                                HOUR,
                                os.fecha_recepcion,
                                os.fecha_entrega
                            )
                        ) AS horas_promedio
                   FROM users u
                   JOIN ordenes_servicio os ON os.tecnico_id = u.id
                  WHERE os.estado = 'Entregado'
                    AND os.fecha_entrega IS NOT NULL
                  GROUP BY u.id, u.nombre
                  ORDER BY ordenes_entregadas DESC, ingresos_generados DESC
                  LIMIT ?"
            );
            $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[ReporteServicioModel::tecnicosProductivos] ' . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────
    // 6) TIEMPO PROMEDIO DE REPARACIÓN (global)
    // ─────────────────────────────────────────────

    public function tiempoPromedio(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT
                    AVG(TIMESTAMPDIFF(HOUR, fecha_recepcion, fecha_entrega)) AS horas_promedio,
                    MIN(TIMESTAMPDIFF(HOUR, fecha_recepcion, fecha_entrega)) AS horas_min,
                    MAX(TIMESTAMPDIFF(HOUR, fecha_recepcion, fecha_entrega)) AS horas_max
                 FROM ordenes_servicio
                 WHERE estado = 'Entregado'
                   AND fecha_entrega IS NOT NULL"
            );
            $row = $stmt->fetch();
            return $row ?: ['horas_promedio'=>0,'horas_min'=>0,'horas_max'=>0];
        } catch (\PDOException $e) {
            error_log('[ReporteServicioModel::tiempoPromedio] ' . $e->getMessage());
            return ['horas_promedio'=>0,'horas_min'=>0,'horas_max'=>0];
        }
    }
}
