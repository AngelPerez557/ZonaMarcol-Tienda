<div class="container-fluid py-4">

    <!-- ─── CABECERA ─────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-shopping-bag me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="descargarPDFPedidos()"
                    class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </button>
            <button onclick="descargarExcelPedidos()"
                    class="btn btn-sm btn-success">
                <i class="fas fa-file-excel me-1"></i>Excel
            </button>
            <a href="<?= APP_URL ?>Reportes/ventas"
               class="btn btn-outline-secondary btn-sm">Ventas</a>
            <a href="<?= APP_URL ?>Reportes/inventario"
               class="btn btn-outline-secondary btn-sm">Inventario</a>
        </div>
    </div>

    <!-- ─── CARDS RESUMEN ────────────────────────── -->
    <div class="row g-3 mb-4">
        <?php
        $estados = [
            ['label'=>'Total pedidos','valor'=>$resumen['total']      ?? 0,'color'=>'#F5A800'],
            ['label'=>'Pendientes',   'valor'=>$resumen['pendientes'] ?? 0,'color'=>'#ffc107'],
            ['label'=>'Preparación',  'valor'=>$resumen['preparacion']?? 0,'color'=>'#17a2b8'],
            ['label'=>'Listos',       'valor'=>$resumen['listos']     ?? 0,'color'=>'#007bff'],
            ['label'=>'En camino',    'valor'=>$resumen['en_camino']  ?? 0,'color'=>'#6f42c1'],
            ['label'=>'Entregados',   'valor'=>$resumen['entregados'] ?? 0,'color'=>'#28a745'],
            ['label'=>'Cancelados',   'valor'=>$resumen['cancelados'] ?? 0,'color'=>'#dc3545'],
        ];
        foreach ($estados as $e):
        ?>
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <div class="fw-bold" style="font-size:1.8rem;color:<?= $e['color'] ?>;">
                        <?= $e['valor'] ?>
                    </div>
                    <div class="text-muted" style="font-size:0.8rem;"><?= $e['label'] ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <!-- ─── GRÁFICA DONUT ESTADOS ─────────────── -->
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i>Pedidos por estado
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartEstados" style="max-height:280px;"></canvas>
                </div>
            </div>
        </div>
        <!-- ─── GRÁFICA LÍNEA PEDIDOS POR DÍA ─────── -->
        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i>Pedidos últimos 30 días
                </div>
                <div class="card-body">
                    <canvas id="chartPedidosDia" style="max-height:280px;"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const datosEstados = <?= json_encode(array_values($pedidosPorEstado)) ?>;
    const datosDia     = <?= json_encode(array_values($pedidosPorDia)) ?>;

    const coloresEstados = {
        'Pendiente':      '#ffc107',
        'En preparacion': '#17a2b8',
        'Listo':          '#007bff',
        'En camino':      '#6f42c1',
        'Entregado':      '#28a745',
        'Cancelado':      '#dc3545',
    };

    const ctxEstados = document.getElementById('chartEstados').getContext('2d');
    new Chart(ctxEstados, {
        type: 'doughnut',
        data: {
            labels: datosEstados.map(d => d.estado),
            datasets: [{
                data: datosEstados.map(d => parseInt(d.total)),
                backgroundColor: datosEstados.map(d => coloresEstados[d.estado] ?? '#aaa'),
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: ctx => ` ${ctx.raw} pedidos` } }
            }
        }
    });

    const ctxDia = document.getElementById('chartPedidosDia').getContext('2d');
    new Chart(ctxDia, {
        type: 'bar',
        data: {
            labels: datosDia.map(d => d.fecha),
            datasets: [{
                label: 'Pedidos',
                data: datosDia.map(d => parseInt(d.total_pedidos)),
                backgroundColor: 'rgba(245,168,0,0.3)',
                borderColor: '#F5A800',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});

function descargarPDFPedidos() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const fecha = new Date().toLocaleDateString('es-HN');

    doc.setFillColor(222, 119, 125);
    doc.rect(0, 0, 210, 28, 'F');
    doc.setTextColor(255,255,255);
    doc.setFontSize(16); doc.setFont('helvetica','bold');
    doc.text('Zona Marcol', 14, 12);
    doc.setFontSize(11); doc.setFont('helvetica','normal');
    doc.text('Reporte de Pedidos — ' + fecha, 14, 22);
    doc.setTextColor(0,0,0);

    const resumenData = <?= json_encode($resumen) ?>;
    doc.autoTable({
        startY: 36,
        head: [['Estado', 'Cantidad']],
        body: [
            ['Total pedidos',  resumenData.total      ?? 0],
            ['Pendientes',     resumenData.pendientes  ?? 0],
            ['En preparación', resumenData.preparacion ?? 0],
            ['Listos',         resumenData.listos      ?? 0],
            ['En camino',      resumenData.en_camino   ?? 0],
            ['Entregados',     resumenData.entregados  ?? 0],
            ['Cancelados',     resumenData.cancelados  ?? 0],
        ],
        styles: { fontSize: 10 },
        headStyles: { fillColor: [222,119,125], textColor: 255 },
        alternateRowStyles: { fillColor: [253,240,241] },
        margin: { left: 14, right: 14 },
    });

    const datosDia = <?= json_encode(array_values($pedidosPorDia)) ?>;
    if (datosDia.length > 0) {
        doc.autoTable({
            startY: doc.lastAutoTable.finalY + 10,
            head: [['Fecha', 'Total Pedidos', 'Monto Total (L.)']],
            body: datosDia.map(d => [
                d.fecha,
                d.total_pedidos,
                'L. ' + parseFloat(d.total_monto).toFixed(2)
            ]),
            styles: { fontSize: 9 },
            headStyles: { fillColor: [222,119,125], textColor: 255 },
            alternateRowStyles: { fillColor: [253,240,241] },
            margin: { left: 14, right: 14 },
            columnStyles: { 1: { halign: 'center' }, 2: { halign: 'right' } }
        });
    }

    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8); doc.setTextColor(150,150,150);
        doc.text(`Generado el ${fecha} | Zona Marcol | Página ${i} de ${pageCount}`, 14, 290);
    }

    doc.save(`reporte-pedidos-${fecha.replace(/\//g,'-')}.pdf`);
}

function descargarExcelPedidos() {
    const wb   = XLSX.utils.book_new();
    const fecha = new Date().toLocaleDateString('es-HN');
    const resumenData = <?= json_encode($resumen) ?>;
    const datosDia    = <?= json_encode(array_values($pedidosPorDia)) ?>;

    const wsResumen = XLSX.utils.aoa_to_sheet([
        ['ZONA MARCOL — REPORTE DE PEDIDOS'],
        ['Generado:', fecha],
        [],
        ['RESUMEN POR ESTADO'],
        ['Estado', 'Cantidad'],
        ['Total pedidos',  resumenData.total      ?? 0],
        ['Pendientes',     resumenData.pendientes  ?? 0],
        ['En preparación', resumenData.preparacion ?? 0],
        ['Listos',         resumenData.listos      ?? 0],
        ['En camino',      resumenData.en_camino   ?? 0],
        ['Entregados',     resumenData.entregados  ?? 0],
        ['Cancelados',     resumenData.cancelados  ?? 0],
    ]);
    XLSX.utils.book_append_sheet(wb, wsResumen, 'Resumen');

    const wsDia = XLSX.utils.aoa_to_sheet([
        ['Fecha', 'Total Pedidos', 'Monto Total (L.)'],
        ...datosDia.map(d => [d.fecha, parseInt(d.total_pedidos), parseFloat(d.total_monto)])
    ]);
    XLSX.utils.book_append_sheet(wb, wsDia, 'Pedidos por Día');

    XLSX.writeFile(wb, `reporte-pedidos-${fecha.replace(/\//g,'-')}.xlsx`);
}
</script>