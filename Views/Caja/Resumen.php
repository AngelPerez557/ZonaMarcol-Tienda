<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cierre de Caja #<?= str_pad($sesion['id'], 4, '0', STR_PAD_LEFT) ?></title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 4mm;
            color: #000;
            background: #fff;
        }
        .center  { text-align:center; }
        .right   { text-align:right; }
        .bold    { font-weight:bold; }
        .line    { border-top:1px dashed #000; margin:4px 0; }
        .doble   { border-top:2px solid #000; margin:4px 0; }
        table    { width:100%; border-collapse:collapse; }
        table td { padding:2px 0; vertical-align:top; }
        @media print {
            body { margin:0; padding:2mm; }
            .no-print { display:none !important; }
            @page { size:80mm auto; margin:0; }
        }
    </style>
</head>
<body>

    <?php
    $dif           = (float) $sesion['diferencia'];
    $fondoInicial  = (float) $sesion['monto_apertura'];
    $montoCierre   = (float) $sesion['monto_cierre'];
    $montoSistema  = (float) $sesion['monto_sistema'];
    $totalVentas   = (float) $sesion['total_ventas'];
    $totalEfectivo = (float) $sesion['total_efectivo'];
    $totalTarjeta  = (float) $sesion['total_tarjeta'];
    $totalTransf   = (float) $sesion['total_transferencia'];
    $totalAnuladas = (float) $sesion['total_anuladas'];
    ?>

    <div class="center bold" style="font-size:14px;">ZONA MARCOL</div>
    <div class="center">CIERRE DE CAJA</div>
    <div class="center">#<?= str_pad($sesion['id'], 4, '0', STR_PAD_LEFT) ?></div>

    <div class="line"></div>

    <div>Cajero: <strong><?= htmlspecialchars($sesion['cajero_nombre']) ?></strong></div>
    <div>Apertura: <?= date('d/m/Y H:i', strtotime($sesion['abierta_at'])) ?></div>
    <div>Cierre:   <?= $sesion['cerrada_at'] ? date('d/m/Y H:i', strtotime($sesion['cerrada_at'])) : '—' ?></div>

    <div class="line"></div>

    <div class="center bold">RESUMEN DE VENTAS</div>
    <div class="line"></div>

    <table>
        <tr>
            <td>Total ventas:</td>
            <td class="right bold">L. <?= number_format($totalVentas, 2) ?></td>
        </tr>
        <tr>
            <td>  · Efectivo:</td>
            <td class="right">L. <?= number_format($totalEfectivo, 2) ?></td>
        </tr>
        <tr>
            <td>  · Tarjeta:</td>
            <td class="right">L. <?= number_format($totalTarjeta, 2) ?></td>
        </tr>
        <tr>
            <td>  · Transferencia:</td>
            <td class="right">L. <?= number_format($totalTransf, 2) ?></td>
        </tr>
        <?php if ($totalAnuladas > 0): ?>
        <tr>
            <td>  · Anuladas:</td>
            <td class="right">L. <?= number_format($totalAnuladas, 2) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <div class="line"></div>

    <div class="center bold">CONTEO DE EFECTIVO</div>
    <div class="line"></div>

    <table>
        <tr>
            <td>Fondo inicial:</td>
            <td class="right">L. <?= number_format($fondoInicial, 2) ?></td>
        </tr>
        <tr>
            <td>+ Cobros efectivo:</td>
            <td class="right">L. <?= number_format($totalEfectivo, 2) ?></td>
        </tr>
        <tr>
            <td class="bold">Sistema espera:</td>
            <td class="right bold">L. <?= number_format($montoSistema, 2) ?></td>
        </tr>
        <tr>
            <td>Efectivo contado:</td>
            <td class="right">L. <?= number_format($montoCierre, 2) ?></td>
        </tr>
    </table>

    <div class="doble"></div>

    <table>
        <tr>
            <td class="bold">Diferencia:</td>
            <td class="right bold">
                <?= $dif >= 0 ? '+' : '' ?>L. <?= number_format($dif, 2) ?>
                <?= abs($dif) < 0.01 ? '(OK)' : ($dif > 0 ? '(SOBRANTE)' : '(FALTANTE)') ?>
            </td>
        </tr>
    </table>

    <?php if ($sesion['nota_cierre']): ?>
    <div class="line"></div>
    <div>Nota: <?= htmlspecialchars($sesion['nota_cierre']) ?></div>
    <?php endif; ?>

    <div class="line"></div>
    <div class="center">Firma cajero: ___________________</div>
    <div class="center">Firma supervisor: _______________</div>
    <div class="line"></div>
    <div class="center">Generado: <?= date('d/m/Y H:i') ?></div>

    <br>

    <div class="no-print" style="text-align:center; margin-top:20px; display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
        <button onclick="window.print()"
                style="background:#F5A800;color:#fff;border:none;padding:10px 24px;
                    border-radius:8px;font-size:14px;cursor:pointer;white-space:nowrap;">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <a href="<?= APP_URL ?>Caja/historial"
        style="background:#6c757d;color:#fff;padding:10px 24px;
                border-radius:8px;font-size:14px;text-decoration:none;white-space:nowrap;">
            Volver al historial
        </a>
    </div>

</body>
</html>