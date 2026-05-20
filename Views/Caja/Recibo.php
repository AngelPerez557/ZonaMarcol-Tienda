<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #<?= str_pad($venta['id'] ?? 0, 8, '0', STR_PAD_LEFT) ?></title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
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

        /* ── Logo ── */
        .logo-img {
            display: block;
            margin: 0 auto 6px;
            max-width: 48mm;   /* respeta el ancho del ticket 80mm */
            max-height: 20mm;
            object-fit: contain;
        }

        /* ── Tabla de artículos ── */
        table { width:100%; border-collapse:collapse; }
        table td {
            padding: 1px 2px;
            vertical-align: top;
            white-space: nowrap;
            overflow: hidden;
        }

        .col-desc   { width:42%; white-space:normal; word-break:break-word; }
        .col-cant   { width:8%;  text-align:center; }
        .col-precio { width:22%; text-align:right; padding-right:4px; }
        .col-total  { width:28%; text-align:right; }

        .totales td { padding:2px 2px; white-space:nowrap; }
        .totales .lbl { width:65%; }
        .totales .val { width:35%; text-align:right; }

        .logo-empresa { font-size:13px; font-weight:bold; text-align:center; margin-bottom:2px; }

        /* ── Sello ANULADA ── */
        .sello-anulada {
            position:fixed; top:50%; left:50%;
            transform:translate(-50%,-50%) rotate(-35deg);
            font-size:48px; font-weight:bold;
            color:rgba(220,53,69,0.25);
            border:6px solid rgba(220,53,69,0.25);
            padding:8px 20px; border-radius:8px;
            pointer-events:none; z-index:999;
            white-space:nowrap; letter-spacing:6px;
        }
        .aviso-anulada {
            background:#dc3545; color:#fff; text-align:center;
            font-weight:bold; padding:4px; margin-bottom:4px;
            font-size:13px; letter-spacing:2px;
        }

        @media print {
            body { margin:0; padding:2mm; }
            .no-print { display:none !important; }
            @page { size:80mm auto; margin:0; }
        }
    </style>
</head>
<body>

    <?php if ((int)($venta['anulada'] ?? 0) === 1): ?>
    <div class="sello-anulada">ANULADA</div>
    <div class="aviso-anulada">★ FACTURA ANULADA ★</div>
    <?php endif; ?>

    <!-- ENCABEZADO -->
    <img src="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png"
         alt="Zona Marcol"
         class="logo-img">

    <div class="logo-empresa"><?= htmlspecialchars($config['nombre_fiscal'] ?? 'ZONA MARCOL') ?></div>
    <div class="center">R.T.N: <?= htmlspecialchars($config['rtn'] ?? '') ?></div>
    <div class="center"><?= htmlspecialchars($config['direccion_fiscal'] ?? '') ?></div>
    <div class="center">Tel: 9987-3125</div>

    <div class="line"></div>

    <div class="center bold">CAI: <?= htmlspecialchars($config['cai'] ?? '') ?></div>
    <div class="center bold">
        Factura # <?= htmlspecialchars(
            ($config['establecimiento'] ?? '000') . '-' .
            ($config['punto_emision']   ?? '001') . '-01-' .
            str_pad($venta['correlativo'] ?? 0, 8, '0', STR_PAD_LEFT)
        ) ?>
    </div>

    <div class="line"></div>

    <div>Fecha: <?= date('d/m/Y', strtotime($venta['created_at'] ?? 'now')) ?>
         &nbsp; Hora: <?= date('h:i:s a', strtotime($venta['created_at'] ?? 'now')) ?></div>
    <div>Cliente: <?= htmlspecialchars($venta['cliente_nombre'] ?? 'Consumidor final') ?></div>
    <div>RTN: N/A</div>

    <?php if ((int)($venta['anulada'] ?? 0) === 1): ?>
    <div class="line"></div>
    <div class="bold">MOTIVO ANULACIÓN: <?= htmlspecialchars($venta['motivo_anulacion'] ?? '—') ?></div>
    <?php endif; ?>

    <div class="line"></div>

    <!-- ENCABEZADO TABLA -->
    <div class="center bold">Descripción:</div>
    <table>
        <thead>
            <tr>
                <td class="col-desc bold">Artículo</td>
                <td class="col-cant bold">Cant</td>
                <td class="col-precio bold">P.Unit</td>
                <td class="col-total bold">Total</td>
            </tr>
        </thead>
    </table>

    <div class="line"></div>

    <!-- DETALLE -->
    <table>
        <tbody>
            <?php
            $subtotal = 0;
            foreach ($detalle as $item):
                $lineTotal = (float)$item['precio_unit'] * (int)$item['cantidad'];
                $subtotal += $lineTotal;
            ?>
            <tr>
                <td class="col-desc"><?= htmlspecialchars($item['nombre_producto']) ?></td>
                <td class="col-cant"><?= $item['cantidad'] ?></td>
                <td class="col-precio"><?= number_format((float)$item['precio_unit'], 2) ?></td>
                <td class="col-total"><?= number_format($lineTotal, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="line"></div>

    <!-- TOTALES -->
    <?php
    $total          = (float)($venta['total'] ?? $subtotal);
    $subtotalSinIsv = $total / 1.15;
    $isv            = $total - $subtotalSinIsv;
    $montoRecibido  = (float)($venta['monto_recibido'] ?? 0);
    $cambio         = (float)($venta['cambio'] ?? 0);
    ?>
    <table class="totales">
        <tr><td class="lbl">Sub Total:</td>           <td class="val">L. <?= number_format($subtotalSinIsv, 2) ?></td></tr>
        <tr><td class="lbl">Monto Exento:</td>         <td class="val">L. 0.00</td></tr>
        <tr><td class="lbl">Monto Exonerado:</td>      <td class="val">L. 0.00</td></tr>
        <tr><td class="lbl bold">Gravado 15%:</td>     <td class="val bold">L. <?= number_format($subtotalSinIsv, 2) ?></td></tr>
        <tr><td class="lbl">I.S.V. 15%:</td>           <td class="val">L. <?= number_format($isv, 2) ?></td></tr>
        <tr><td class="lbl bold">Gravado 18%:</td>     <td class="val bold">L. 0.00</td></tr>
        <tr><td class="lbl">I.S.V 18%:</td>            <td class="val">L. 0.00</td></tr>
    </table>

    <div class="doble"></div>

    <table class="totales">
        <tr>
            <td class="lbl bold" style="font-size:12px;">Total a pagar:</td>
            <td class="val bold" style="font-size:12px;">L. <?= number_format($total, 2) ?></td>
        </tr>
        <tr><td class="lbl">Desc. Y Rebajas:</td><td class="val">L. 0.00</td></tr>
        <?php if ($venta['metodo_pago'] === 'Efectivo'): ?>
        <tr><td class="lbl">Efectivo:</td>  <td class="val">L. <?= number_format($montoRecibido, 2) ?></td></tr>
        <tr><td class="lbl">Cambio:</td>    <td class="val">L. <?= number_format($cambio, 2) ?></td></tr>
        <?php else: ?>
        <tr><td class="lbl">Método de pago:</td><td class="val"><?= htmlspecialchars($venta['metodo_pago']) ?></td></tr>
        <?php endif; ?>
    </table>

    <div class="line"></div>

    <!-- SON -->
    <?php
    function numeroALetras(float $numero): string {
        $entero   = intval($numero);
        $unidades = ['','UN','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE',
                     'DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS',
                     'DIECISIETE','DIECIOCHO','DIECINUEVE'];
        $decenas  = ['','','VEINTE','TREINTA','CUARENTA','CINCUENTA',
                     'SESENTA','SETENTA','OCHENTA','NOVENTA'];
        $centenas = ['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS',
                     'SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS'];
        if ($entero === 0) return 'CERO';
        if ($entero < 20)  return $unidades[$entero];
        if ($entero < 100) {
            $d = intval($entero / 10);
            $u = $entero % 10;
            return $decenas[$d] . ($u ? ' Y ' . $unidades[$u] : '');
        }
        if ($entero === 100) return 'CIEN';
        if ($entero < 1000) {
            $c = intval($entero / 100);
            $r = $entero % 100;
            return $centenas[$c] . ($r ? ' ' . numeroALetras((float)$r) : '');
        }
        if ($entero < 1000000) {
            $m    = intval($entero / 1000);
            $r    = $entero % 1000;
            $miles = $m === 1 ? 'MIL' : numeroALetras((float)$m) . ' MIL';
            return $miles . ($r ? ' ' . numeroALetras((float)$r) : '');
        }
        return number_format($entero, 0, '.', ',');
    }
    $dec      = str_pad(round(($total - floor($total)) * 100), 2, '0', STR_PAD_LEFT);
    $sonTexto = numeroALetras($total) . ' CON ' . $dec . '/100';
    ?>
    <div class="bold">Son: <?= $sonTexto ?></div>

    <div class="line"></div>

    <div>No. Correlativo Orden Compra Exenta: ___________</div>
    <div>No. Correlativo Constancia de Reg. Exonerado: ___________</div>
    <div>No. Identificativo del Reg. De la Secretaria de estado de despacho SAG: ___________</div>

    <div class="line"></div>

    <div>Fecha limite de emisión: <?= $config['fecha_limite'] ? date('d/m/Y', strtotime($config['fecha_limite'])) : '' ?></div>
    <div>Rango autorizado: <?= htmlspecialchars($config['rango_desde'] ?? '') ?></div>
    <div>Hasta <?= htmlspecialchars($config['rango_hasta'] ?? '') ?></div>

    <div class="line"></div>

    <div class="center">Original: Cliente</div>
    <div class="center bold">Copia: Emisor Obligado Tributario</div>
    <?php if ((int)($venta['anulada'] ?? 0) === 0): ?>
    <div class="center bold">¡La factura es beneficio de todos, Exíjala!</div>
    <?php else: ?>
    <div class="center bold" style="color:#dc3545;">★ DOCUMENTO ANULADO — NO VÁLIDO ★</div>
    <?php endif; ?>

    <br>

    <div class="no-print" style="text-align:center; margin-top:20px;">
        <?php if ((int)($venta['anulada'] ?? 0) === 0): ?>
        <button onclick="window.print()"
                style="background:#F5A800;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:14px;cursor:pointer;margin-right:8px;">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <?php endif; ?>
        <button onclick="window.close()"
                style="background:#6c757d;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:14px;cursor:pointer;">
            Cerrar
        </button>
    </div>

</body>
</html>