<div class="container-fluid py-4">

    <!-- ─── CABECERA ─────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-chart-line me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Botones de descarga -->
            <button onclick="descargarPDF('ventas')"
                    class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </button>
            <button onclick="descargarExcel('ventas')"
                    class="btn btn-sm btn-success">
                <i class="fas fa-file-excel me-1"></i>Excel
            </button>
            <a href="<?= APP_URL ?>Reportes/pedidos"
               class="btn btn-outline-secondary btn-sm">Pedidos</a>
            <a href="<?= APP_URL ?>Reportes/inventario"
               class="btn btn-outline-secondary btn-sm">Inventario</a>
        </div>
    </div>

    <!-- ─── CARDS RESUMEN ────────────────────────── -->
    <div class="row g-3 mb-4" id="cards-ventas">
        <?php
        $cards = [
            ['label'=>'Total ventas',    'valor'=>$resumen['total_ventas']   ?? 0, 'icono'=>'fas fa-shopping-cart',  'color'=>'#F5A800', 'formato'=>'numero'],
            ['label'=>'Ingresos totales','valor'=>$resumen['total_monto']    ?? 0, 'icono'=>'fas fa-money-bill-wave','color'=>'#28a745', 'formato'=>'lempira'],
            ['label'=>'Venta promedio',  'valor'=>$resumen['promedio_venta'] ?? 0, 'icono'=>'fas fa-chart-bar',      'color'=>'#007bff', 'formato'=>'lempira'],
            ['label'=>'Total hoy',       'valor'=>$resumen['total_hoy']      ?? 0, 'icono'=>'fas fa-calendar-day',   'color'=>'#fd7e14', 'formato'=>'lempira'],
            ['label'=>'Total este mes',  'valor'=>$resumen['total_mes']      ?? 0, 'icono'=>'fas fa-calendar-alt',   'color'=>'#6f42c1', 'formato'=>'lempira'],
        ];
        foreach ($cards as $card):
        ?>
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;flex-shrink:0;background:<?= $card['color'] ?>22;">
                        <i class="<?= $card['icono'] ?>" style="color:<?= $card['color'] ?>;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= $card['label'] ?></div>
                        <div class="fw-bold" style="font-size:1.1rem;color:<?= $card['color'] ?>;">
                            <?= $card['formato'] === 'lempira'
                                ? 'L. ' . number_format((float)$card['valor'], 2)
                                : number_format((int)$card['valor']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4 mb-4">
        <!-- ─── GRÁFICA VENTAS POR DÍA ───────────── -->
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line me-2"></i>Ventas últimos 30 días</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-primary active" id="btnDias">Por día</button>
                        <button type="button" class="btn btn-outline-primary" id="btnMeses">Por mes</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chartVentas" style="max-height:280px;"></canvas>
                </div>
            </div>
        </div>
        <!-- ─── GRÁFICA MÉTODOS DE PAGO ──────────── -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i>Métodos de pago
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartMetodos" style="max-height:250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── TOP PRODUCTOS ────────────────────────── -->
    <div class="card" id="tabla-top-productos">
        <div class="card-header">
            <i class="fas fa-trophy me-2"></i>Top 10 productos más vendidos
        </div>
        <div class="row g-0">
            <div class="col-12 col-lg-6">
                <div class="card-body">
                    <canvas id="chartTopProductos" style="max-height:300px;"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0" id="tablaTopProductos">
                        <thead>
                            <tr style="background:rgba(245,168,0,0.08);">
                                <th class="ps-3">#</th>
                                <th>Producto</th>
                                <th class="text-center">Vendidos</th>
                                <th class="text-end pe-3">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProductos as $i => $p): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($p['nombre_producto']) ?></td>
                                <td class="text-center">
                                    <span class="badge" style="background:#F5A800;">
                                        <?= $p['total_vendido'] ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3" style="color:#F5A800;font-weight:600;">
                                    L. <?= number_format((float)$p['total_monto'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Librerías -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const colorPrimario = '#F5A800';
    const colorFondo    = 'rgba(245,168,0,0.15)';

    const datosDia     = <?= json_encode(array_values($ventasPorDia)) ?>;
    const datosMes     = <?= json_encode(array_values($ventasPorMes)) ?>;
    const datosMetodos = <?= json_encode(array_values($ventasPorMetodo)) ?>;
    const datosTop     = <?= json_encode(array_values($topProductos)) ?>;
    const resumen      = <?= json_encode($resumen) ?>;

    // ── Gráfica ventas ────────────────────────────
    const ctxVentas = document.getElementById('chartVentas').getContext('2d');
    let chartVentas = new Chart(ctxVentas, {
        type: 'line',
        data: {
            labels: datosDia.map(d => d.fecha),
            datasets: [{
                label: 'Total (L.)',
                data: datosDia.map(d => parseFloat(d.total_monto)),
                borderColor: colorPrimario,
                backgroundColor: colorFondo,
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colorPrimario,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => 'L. ' + v.toLocaleString() } }
            }
        }
    });

    document.getElementById('btnDias').addEventListener('click', function () {
        chartVentas.data.labels = datosDia.map(d => d.fecha);
        chartVentas.data.datasets[0].data = datosDia.map(d => parseFloat(d.total_monto));
        chartVentas.update();
        this.classList.add('active','btn-primary'); this.classList.remove('btn-outline-primary');
        document.getElementById('btnMeses').classList.remove('active','btn-primary');
        document.getElementById('btnMeses').classList.add('btn-outline-primary');
    });

    document.getElementById('btnMeses').addEventListener('click', function () {
        chartVentas.data.labels = datosMes.map(d => d.mes_label);
        chartVentas.data.datasets[0].data = datosMes.map(d => parseFloat(d.total_monto));
        chartVentas.update();
        this.classList.add('active','btn-primary'); this.classList.remove('btn-outline-primary');
        document.getElementById('btnDias').classList.remove('active','btn-primary');
        document.getElementById('btnDias').classList.add('btn-outline-primary');
    });

    // ── Gráfica métodos ───────────────────────────
    const ctxMetodos = document.getElementById('chartMetodos').getContext('2d');
    new Chart(ctxMetodos, {
        type: 'doughnut',
        data: {
            labels: datosMetodos.map(d => d.metodo_pago),
            datasets: [{
                data: datosMetodos.map(d => parseFloat(d.total_monto)),
                backgroundColor: ['#F5A800','#28a745','#007bff','#fd7e14'],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: ctx => ` L. ${ctx.raw.toLocaleString()}` } }
            }
        }
    });

    // ── Gráfica top productos ─────────────────────
    const ctxTop = document.getElementById('chartTopProductos').getContext('2d');
    new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: datosTop.map(d => d.nombre_producto.substring(0,15)),
            datasets: [{
                label: 'Unidades vendidas',
                data: datosTop.map(d => parseInt(d.total_vendido)),
                backgroundColor: colorFondo,
                borderColor: colorPrimario,
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });

});

// ══════════════════════════════════════════════════
// DESCARGA PDF — Reporte de Ventas
// ══════════════════════════════════════════════════
function descargarPDF(tipo) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

    const fecha = new Date().toLocaleDateString('es-HN', {
        day:'2-digit', month:'2-digit', year:'numeric'
    });

    // Cabecera
    doc.setFillColor(222, 119, 125);
    doc.rect(0, 0, 210, 28, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(16);
    doc.setFont('helvetica', 'bold');
    doc.text('Zona Marcol', 14, 12);
    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.text('Reporte de Ventas — ' + fecha, 14, 22);

    doc.setTextColor(0, 0, 0);
    let y = 36;

    // Resumen
    const resumenData = <?= json_encode($resumen) ?>;
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('Resumen General', 14, y);
    y += 6;

    doc.autoTable({
        startY: y,
        head: [['Indicador', 'Valor']],
        body: [
            ['Total de ventas',    resumenData.total_ventas  ?? '0'],
            ['Ingresos totales',   'L. ' + parseFloat(resumenData.total_monto   ?? 0).toFixed(2)],
            ['Venta promedio',     'L. ' + parseFloat(resumenData.promedio_venta?? 0).toFixed(2)],
            ['Total hoy',          'L. ' + parseFloat(resumenData.total_hoy     ?? 0).toFixed(2)],
            ['Total este mes',     'L. ' + parseFloat(resumenData.total_mes     ?? 0).toFixed(2)],
        ],
        styles: { fontSize: 10 },
        headStyles: { fillColor: [222, 119, 125], textColor: 255 },
        alternateRowStyles: { fillColor: [253, 240, 241] },
        margin: { left: 14, right: 14 },
    });

    y = doc.lastAutoTable.finalY + 10;

    // Top productos
    const topData = <?= json_encode(array_values($topProductos)) ?>;
    if (topData.length > 0) {
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.text('Top 10 Productos Más Vendidos', 14, y);
        y += 4;

        doc.autoTable({
            startY: y,
            head: [['#', 'Producto', 'Unidades', 'Total (L.)']],
            body: topData.map((p, i) => [
                i + 1,
                p.nombre_producto,
                p.total_vendido,
                'L. ' + parseFloat(p.total_monto).toFixed(2)
            ]),
            styles: { fontSize: 9 },
            headStyles: { fillColor: [222, 119, 125], textColor: 255 },
            alternateRowStyles: { fillColor: [253, 240, 241] },
            margin: { left: 14, right: 14 },
            columnStyles: { 0: { halign: 'center' }, 2: { halign: 'center' }, 3: { halign: 'right' } }
        });
    }

    // Pie de página
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(150, 150, 150);
        doc.text(
            `Generado el ${fecha} | Zona Marcol — DeskCod | Página ${i} de ${pageCount}`,
            14, 290
        );
    }

    doc.save(`reporte-ventas-${fecha.replace(/\//g,'-')}.pdf`);
}

// ══════════════════════════════════════════════════
// DESCARGA EXCEL — Reporte de Ventas
// ══════════════════════════════════════════════════
function descargarExcel(tipo) {
    const wb = XLSX.utils.book_new();
    const fecha = new Date().toLocaleDateString('es-HN');

    // Hoja 1 — Resumen
    const resumenData = <?= json_encode($resumen) ?>;
    const wsResumen = XLSX.utils.aoa_to_sheet([
        ['ZONA MARCOL — REPORTE DE VENTAS'],
        ['Generado:', fecha],
        [],
        ['RESUMEN GENERAL'],
        ['Indicador', 'Valor'],
        ['Total de ventas',   resumenData.total_ventas   ?? 0],
        ['Ingresos totales',  parseFloat(resumenData.total_monto    ?? 0)],
        ['Venta promedio',    parseFloat(resumenData.promedio_venta ?? 0)],
        ['Total hoy',         parseFloat(resumenData.total_hoy      ?? 0)],
        ['Total este mes',    parseFloat(resumenData.total_mes      ?? 0)],
    ]);
    XLSX.utils.book_append_sheet(wb, wsResumen, 'Resumen');

    // Hoja 2 — Ventas por día
    const datosDia = <?= json_encode(array_values($ventasPorDia)) ?>;
    const wsDia = XLSX.utils.aoa_to_sheet([
        ['Fecha', 'Total Ventas', 'Monto Total (L.)'],
        ...datosDia.map(d => [d.fecha, parseInt(d.total_ventas), parseFloat(d.total_monto)])
    ]);
    XLSX.utils.book_append_sheet(wb, wsDia, 'Ventas por Día');

    // Hoja 3 — Ventas por mes
    const datosMes = <?= json_encode(array_values($ventasPorMes)) ?>;
    const wsMes = XLSX.utils.aoa_to_sheet([
        ['Mes', 'Total Ventas', 'Monto Total (L.)'],
        ...datosMes.map(d => [d.mes_label, parseInt(d.total_ventas), parseFloat(d.total_monto)])
    ]);
    XLSX.utils.book_append_sheet(wb, wsMes, 'Ventas por Mes');

    // Hoja 4 — Top productos
    const topData = <?= json_encode(array_values($topProductos)) ?>;
    const wsTop = XLSX.utils.aoa_to_sheet([
        ['#', 'Producto', 'Unidades Vendidas', 'Monto Total (L.)'],
        ...topData.map((p, i) => [i+1, p.nombre_producto, parseInt(p.total_vendido), parseFloat(p.total_monto)])
    ]);
    XLSX.utils.book_append_sheet(wb, wsTop, 'Top Productos');

    // Hoja 5 — Métodos de pago
    const datosMetodos = <?= json_encode(array_values($ventasPorMetodo)) ?>;
    const wsMetodos = XLSX.utils.aoa_to_sheet([
        ['Método de Pago', 'Total Ventas', 'Monto Total (L.)'],
        ...datosMetodos.map(d => [d.metodo_pago, parseInt(d.total_ventas), parseFloat(d.total_monto)])
    ]);
    XLSX.utils.book_append_sheet(wb, wsMetodos, 'Métodos de Pago');

    XLSX.writeFile(wb, `reporte-ventas-${fecha.replace(/\//g,'-')}.xlsx`);
}
</script>