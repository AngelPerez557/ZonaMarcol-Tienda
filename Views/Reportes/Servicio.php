<?php
/**
 * Views/Reportes/Servicio.php — KPIs, gráficos y tablas del módulo
 * Servicio Técnico. Usa Chart.js (ya cargado en otras vistas de reportes).
 */
$badgeEstado = [
    'Recibido'             => 'bg-secondary',
    'Diagnostico'          => 'bg-info text-dark',
    'Esperando aprobacion' => 'bg-warning text-dark',
    'En reparacion'        => 'bg-primary',
    'Listo'                => 'bg-success',
    'Entregado'            => 'bg-dark',
    'Cancelado'            => 'bg-danger',
];
?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-wrench me-2" style="color:#F5A800;"></i>
            <?= htmlspecialchars($pageTitle) ?>
        </h4>
    </div>

    <!-- ─── KPIs ─── -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-clipboard-list fa-2x mb-2" style="color:#F5A800;"></i>
                    <div class="h3 mb-0"><?= (int) $resumen['total_ordenes'] ?></div>
                    <small class="text-muted">Total órdenes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-spinner fa-2x mb-2 text-primary"></i>
                    <div class="h3 mb-0"><?= (int) $resumen['abiertas'] ?></div>
                    <small class="text-muted">Abiertas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                    <div class="h3 mb-0"><?= (int) $resumen['entregadas'] ?></div>
                    <small class="text-muted">Entregadas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-2x mb-2 text-danger"></i>
                    <div class="h3 mb-0"><?= (int) $resumen['canceladas'] ?></div>
                    <small class="text-muted">Canceladas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase">Ingresos totales</small>
                            <div class="h4 mb-0 text-success">
                                L. <?= number_format((float) $resumen['ingresos_totales'], 2) ?>
                            </div>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase">Saldo pendiente</small>
                            <div class="h4 mb-0 text-danger">
                                L. <?= number_format((float) $resumen['saldo_pendiente'], 2) ?>
                            </div>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Gráficos ─── -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i>Ingresos por mes (últimos 12)
                </div>
                <div class="card-body">
                    <canvas id="chartIngresos" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i>Órdenes por estado
                </div>
                <div class="card-body">
                    <canvas id="chartEstados" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Tiempo promedio ─── -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <small class="text-muted text-uppercase">Tiempo promedio</small>
                    <div class="h4 mb-0">
                        <?= number_format((float) ($tiempoPromedio['horas_promedio'] ?? 0), 1) ?> h
                    </div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted text-uppercase">Más rápida</small>
                    <div class="h4 mb-0 text-success">
                        <?= number_format((float) ($tiempoPromedio['horas_min'] ?? 0), 1) ?> h
                    </div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted text-uppercase">Más lenta</small>
                    <div class="h4 mb-0 text-warning">
                        <?= number_format((float) ($tiempoPromedio['horas_max'] ?? 0), 1) ?> h
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Técnicos productivos ─── -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
            <span><i class="fas fa-user-cog me-2" style="color:#F5A800;"></i>Técnicos productivos</span>
            <small class="text-muted">Solo entregadas</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">#</th>
                            <th>Técnico</th>
                            <th class="text-center">Entregadas</th>
                            <th class="text-end">Ingresos generados</th>
                            <th class="text-center">Tiempo prom.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tecnicosProductivos)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Sin datos.</td></tr>
                        <?php else: foreach ($tecnicosProductivos as $i => $t): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($t['nombre'] ?? '—') ?></td>
                            <td class="text-center"><?= (int) $t['ordenes_entregadas'] ?></td>
                            <td class="text-end fw-semibold">
                                L. <?= number_format((float) $t['ingresos_generados'], 2) ?>
                            </td>
                            <td class="text-center">
                                <?= number_format((float) ($t['horas_promedio'] ?? 0), 1) ?> h
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ─── Garantías vigentes ─── -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
            <span><i class="fas fa-shield-halved me-2" style="color:#F5A800;"></i>Garantías vigentes</span>
            <span class="badge bg-warning text-dark"><?= (int) $totalGarantias ?> activas</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">Orden</th>
                            <th>Cliente</th>
                            <th>Equipo</th>
                            <th>Ítem</th>
                            <th class="text-center">Vence el</th>
                            <th class="text-center">Días restantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($garantiasVigentes)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">No hay garantías vigentes.</td></tr>
                        <?php else: foreach ($garantiasVigentes as $g): ?>
                        <tr>
                            <td class="ps-4"><code><?= htmlspecialchars($g['orden_codigo']) ?></code></td>
                            <td>
                                <?= htmlspecialchars($g['cliente_nombre']) ?>
                                <?php if ($g['cliente_telefono']): ?>
                                <small class="d-block text-muted"><?= htmlspecialchars($g['cliente_telefono']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><small><?= htmlspecialchars($g['equipo_descripcion']) ?></small></td>
                            <td><small><?= htmlspecialchars($g['descripcion']) ?></small></td>
                            <td class="text-center"><small><?= htmlspecialchars($g['vence_el']) ?></small></td>
                            <td class="text-center">
                                <?php $dr = (int) $g['dias_restantes']; ?>
                                <span class="badge <?= $dr <= 7 ? 'bg-danger' : ($dr <= 30 ? 'bg-warning text-dark' : 'bg-success') ?>">
                                    <?= $dr ?>d
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const ingresos = <?= json_encode($ingresosPorMes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const estados  = <?= json_encode($ordenesPorEstado, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    // Ingresos por mes — line chart
    const ctxIng = document.getElementById('chartIngresos');
    if (ctxIng && ingresos.length) {
        new Chart(ctxIng.getContext('2d'), {
            type: 'line',
            data: {
                labels: ingresos.map(r => r.mes),
                datasets: [{
                    label: 'Ingresos (L.)',
                    data:  ingresos.map(r => parseFloat(r.ingresos)),
                    borderColor: '#F5A800',
                    backgroundColor: 'rgba(245,168,0,0.15)',
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: { responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{ display:false } } },
        });
    }

    // Estados — doughnut
    const ctxEst = document.getElementById('chartEstados');
    if (ctxEst && estados.length) {
        const colores = {
            'Recibido':'#6c757d','Diagnostico':'#0dcaf0','Esperando aprobacion':'#ffc107',
            'En reparacion':'#0d6efd','Listo':'#28a745','Entregado':'#1a1a1a','Cancelado':'#dc3545',
        };
        new Chart(ctxEst.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: estados.map(r => r.estado),
                datasets: [{
                    data:  estados.map(r => parseInt(r.total)),
                    backgroundColor: estados.map(r => colores[r.estado] || '#888'),
                    borderWidth: 0,
                }],
            },
            options: { responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{ position:'bottom', labels:{ font:{ size:10 } } } } },
        });
    }
})();
</script>
