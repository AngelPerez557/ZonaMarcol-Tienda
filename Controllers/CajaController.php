<?php

class CajaController
{
    private ProductoModel    $productoModel;
    private VarianteModel    $varianteModel;
    private VentaModel       $ventaModel;
    private CajaSesionModel  $sesionModel;

    public function __construct()
    {
        Auth::check();
        $this->productoModel = new ProductoModel();
        $this->varianteModel = new VarianteModel();
        $this->ventaModel    = new VentaModel();
        $this->sesionModel   = new CajaSesionModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Vista principal de caja
    // URL: /Caja/index
    // Requiere caja abierta — si no, redirige a apertura
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('ventas.crear');

        // Verificar que tenga caja abierta
        $sesion = $this->sesionModel->getSesionAbierta(Auth::id());
        if (!$sesion) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Caja cerrada',
                'text'  => 'Debes abrir la caja antes de registrar ventas.',
            ];
            header('Location: ' . APP_URL . 'Caja/apertura');
            exit();
        }

        $pageTitle       = 'Caja / Punto de Venta';
        $productos       = $this->productoModel->findActivos();
        $categoriaModel  = new CategoriaModel();
        $categorias      = $categoriaModel->findActivas();
        $descuentoModel  = new DescuentoModel();
        $descuentoActivo = $descuentoModel->getActivo();

        require_once VIEWS_PATH . 'Caja' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // APERTURA — Formulario para abrir caja
    // URL: /Caja/apertura
    // ─────────────────────────────────────────────
    public function apertura(): void
    {
        Auth::require('ventas.crear');

        // Si ya tiene caja abierta redirige a index
        $sesion = $this->sesionModel->getSesionAbierta(Auth::id());
        if ($sesion) {
            header('Location: ' . APP_URL . 'Caja/index');
            exit();
        }

        $pageTitle = 'Apertura de Caja';
        require_once VIEWS_PATH . 'Caja' . DS . 'Apertura.php';
    }

    // ─────────────────────────────────────────────
    // ABRIR — Procesa el formulario de apertura (POST JSON)
    // URL: /Caja/abrir
    // ─────────────────────────────────────────────
    public function abrir(): void
    {
        header('Content-Type: application/json');
        Auth::require('ventas.crear');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        if (!Csrf::validate()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token inválido.']);
            exit();
        }

        $montoApertura = (float) ($_POST['monto_apertura'] ?? 0);
        $nota          = htmlspecialchars(strip_tags(trim($_POST['nota'] ?? '')));

        if ($montoApertura < 0) {
            echo json_encode(['success' => false, 'message' => 'El monto de apertura no puede ser negativo.']);
            exit();
        }

        $id = $this->sesionModel->abrir(Auth::id(), $montoApertura, $nota);

        if ($id === -1) {
            echo json_encode(['success' => false, 'message' => 'Ya tienes una caja abierta.']);
            exit();
        }

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Error al abrir la caja.']);
            exit();
        }

        echo json_encode(['success' => true, 'message' => 'Caja abierta correctamente.', 'sesion_id' => $id]);
        exit();
    }

    // ─────────────────────────────────────────────
    // CIERRE — Vista de cierre de caja
    // URL: /Caja/cierre
    // ─────────────────────────────────────────────
    public function cierre(): void
    {
        Auth::require('ventas.crear');

        $sesion = $this->sesionModel->getSesionAbierta(Auth::id());
        if (!$sesion) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Sin caja abierta',
                'text'  => 'No tienes ninguna caja abierta para cerrar.',
            ];
            header('Location: ' . APP_URL . 'Caja/apertura');
            exit();
        }

        // Calcular totales del turno
        $totales   = $this->sesionModel->calcularTotales($sesion['id'], Auth::id(), $sesion['abierta_at']);
        $pageTitle = 'Cierre de Caja';

        require_once VIEWS_PATH . 'Caja' . DS . 'Cierre.php';
    }

    // ─────────────────────────────────────────────
    // CERRAR — Procesa el cierre (POST JSON)
    // URL: /Caja/cerrar
    // ─────────────────────────────────────────────
    public function cerrar(): void
    {
        header('Content-Type: application/json');
        Auth::require('ventas.crear');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        if (!Csrf::validate()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token inválido.']);
            exit();
        }

        $sesion = $this->sesionModel->getSesionAbierta(Auth::id());
        if (!$sesion) {
            echo json_encode(['success' => false, 'message' => 'No tienes ninguna caja abierta.']);
            exit();
        }

        $montoCierre = (float) ($_POST['monto_cierre'] ?? 0);
        $notaCierre  = htmlspecialchars(strip_tags(trim($_POST['nota_cierre'] ?? '')));

        // Recalcular totales del sistema al momento del cierre
        $totales = $this->sesionModel->calcularTotales(
            $sesion['id'],
            Auth::id(),
            $sesion['abierta_at']
        );

        $totalEfectivo     = (float) ($totales['total_efectivo']      ?? 0);
        $totalTarjeta      = (float) ($totales['total_tarjeta']       ?? 0);
        $totalTransferencia= (float) ($totales['total_transferencia'] ?? 0);
        $totalAnuladas     = (float) ($totales['total_anuladas']      ?? 0);
        $totalVentas       = (float) ($totales['total_ventas']        ?? 0);

        // El sistema espera: fondo inicial + efectivo cobrado
        $montoSistema = (float) $sesion['monto_apertura'] + $totalEfectivo;

        $ok = $this->sesionModel->cerrar([
            'sesion_id'          => $sesion['id'],
            'user_id'            => Auth::id(),
            'monto_cierre'       => $montoCierre,
            'monto_sistema'      => $montoSistema,
            'total_ventas'       => $totalVentas,
            'total_efectivo'     => $totalEfectivo,
            'total_tarjeta'      => $totalTarjeta,
            'total_transferencia'=> $totalTransferencia,
            'total_anuladas'     => $totalAnuladas,
            'nota_cierre'        => $notaCierre,
        ]);

        if ($ok) {
            echo json_encode([
                'success'    => true,
                'message'    => 'Caja cerrada correctamente.',
                'sesion_id'  => $sesion['id'],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cerrar la caja.']);
        }
        exit();
    }

    // ─────────────────────────────────────────────
    // HISTORIAL — Lista de cierres
    // URL: /Caja/historial
    // ─────────────────────────────────────────────
    public function historial(): void
    {
        Auth::require('ventas.ver');

        $pageTitle = 'Historial de Caja';
        $sesiones  = $this->sesionModel->historial();

        require_once VIEWS_PATH . 'Caja' . DS . 'Historial.php';
    }

    // ─────────────────────────────────────────────
    // RESUMEN — Vista imprimible de un cierre
    // URL: /Caja/resumen/{id}
    // ─────────────────────────────────────────────
    public function resumen(string $id = ''): void
    {
        Auth::require('ventas.ver');

        if (empty($id) || !is_numeric($id)) {
            header('Location: ' . APP_URL . 'Caja/historial');
            exit();
        }

        $sesion = $this->sesionModel->findById((int) $id);

        if (!$sesion) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Sesión no encontrada.'];
            header('Location: ' . APP_URL . 'Caja/historial');
            exit();
        }

        require_once VIEWS_PATH . 'Caja' . DS . 'Resumen.php';
    }

    // ─────────────────────────────────────────────
    // BUSCAR, BARRAS, COBRAR, RECIBO — sin cambios
    // ─────────────────────────────────────────────

    public function buscar(): void
    {
        Auth::require('ventas.crear');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit(); }
        $query = htmlspecialchars(strip_tags(trim($_GET['q'] ?? '')));
        if (strlen($query) < 1) { header('Content-Type: application/json'); echo json_encode([]); exit(); }
        $productos = $this->productoModel->findByNombre($query);
        $resultado = [];
        foreach ($productos as $p) {
            $item = [
                'id'              => $p->id,
                'nombre'          => $p->nombre,
                'precio_base'     => $p->precio_base,
                'tiene_variantes' => $p->tieneVariantes(),
                'stock'           => $p->stock,
                'image_url'       => $p->getImageUrl(),
                'categoria'       => $p->categoria_nombre,
                'categoria_id'    => $p->categoria_id,
                'variantes'       => [],
            ];
            if ($p->tieneVariantes()) {
                $variantes = $this->varianteModel->findByProducto($p->id);
                foreach ($variantes as $v) {
                    if (!$v->isActivo()) continue;
                    $item['variantes'][] = ['id'=>$v->id,'nombre'=>$v->nombre,'precio'=>$v->getPrecioEfectivo(),'stock'=>$v->stock];
                }
            }
            $resultado[] = $item;
        }
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit();
    }

    public function barras(): void
    {
        Auth::require('ventas.crear');
        $codigo    = htmlspecialchars(strip_tags(trim($_GET['codigo'] ?? '')));
        if (empty($codigo)) { header('Content-Type: application/json'); echo json_encode(['found'=>false]); exit(); }
        $resultado = $this->productoModel->findByBarras($codigo) ?: $this->productoModel->findSimpleByBarras($codigo);
        header('Content-Type: application/json');
        echo json_encode($resultado ? ['found'=>true,'producto'=>$resultado] : ['found'=>false]);
        exit();
    }

    public function cobrar(): void
    {
        Auth::require('ventas.crear');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!Csrf::validate()) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Token inválido.']);
            exit();
        }

        // Verificar caja abierta antes de cobrar
        $sesion = $this->sesionModel->getSesionAbierta(Auth::id());
        if (!$sesion) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Debes abrir la caja antes de registrar ventas.']);
            exit();
        }

        $clienteId     = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
        $metodoPago    = htmlspecialchars(strip_tags(trim($_POST['metodo_pago'] ?? 'Efectivo')));
        $montoRecibido = !empty($_POST['monto_recibido']) ? (float)$_POST['monto_recibido'] : null;
        $nota          = htmlspecialchars(strip_tags(trim($_POST['nota'] ?? '')));

        if (!in_array($metodoPago, ['Efectivo','Tarjeta','Transferencia'], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Método de pago inválido.']);
            exit();
        }

        $items = json_decode($_POST['items'] ?? '[]', true);
        if (empty($items)) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>'El carrito está vacío.']); exit(); }

        $subtotal     = array_reduce($items, fn($s,$i) => $s + (float)$i['precio'] * (int)$i['cantidad'], 0);
        $descuentoPct = (float)($_POST['descuento_pct'] ?? 0);
        $descMonto    = $descuentoPct > 0 ? round($subtotal * $descuentoPct / 100, 2) : 0;
        $total        = round($subtotal - $descMonto, 2);
        $cambio       = $metodoPago === 'Efectivo' && $montoRecibido ? round($montoRecibido - $total, 2) : null;

        if ($metodoPago === 'Efectivo' && $montoRecibido !== null && $montoRecibido < $total) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'El monto recibido es insuficiente.']);
            exit();
        }

        try {
            $this->ventaModel->beginTransactionPublic();
            $ventaId = $this->ventaModel->insert([
                'cliente_id'=>$clienteId,'user_id'=>Auth::id(),'metodo_pago'=>$metodoPago,
                'subtotal'=>$subtotal,'descuento'=>0,'total'=>$total,
                'monto_recibido'=>$montoRecibido,'cambio'=>$cambio,'nota'=>$nota ?: null,
            ]);
            if (!$ventaId) throw new \RuntimeException('Error al crear la venta.');

            foreach ($items as $item) {
                $productoId = (int)$item['producto_id'];
                $varianteId = !empty($item['variante_id']) ? (int)$item['variante_id'] : null;
                $cantidad   = (int)$item['cantidad'];
                $precio     = (float)$item['precio'];
                $nombre     = htmlspecialchars(strip_tags($item['nombre'] ?? ''));
                $this->ventaModel->insertDetalle([
                    'venta_id'=>$ventaId,'producto_id'=>$productoId,'variante_id'=>$varianteId,
                    'nombre_producto'=>$nombre,'precio_unit'=>$precio,'cantidad'=>$cantidad,
                    'subtotal'=>round($precio * $cantidad, 2),
                ]);
                $ok = $varianteId
                    ? $this->varianteModel->descontarStock($varianteId, $cantidad)
                    : $this->productoModel->descontarStock($productoId, $cantidad);
                if (!$ok) throw new \RuntimeException("Stock insuficiente para: {$nombre}");
            }
            $this->ventaModel->commitPublic();
            header('Content-Type: application/json');
            echo json_encode(['success'=>true,'venta_id'=>$ventaId,'total'=>$total,'cambio'=>$cambio,'message'=>'Venta registrada.']);
        } catch (\RuntimeException $e) {
            $this->ventaModel->rollbackPublic();
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
        }
        exit();
    }

    public function recibo(string $id = ''): void
    {
        Auth::require('ventas.ver');
        if (empty($id) || !is_numeric($id)) { header('Location: '.APP_URL.'Caja/index'); exit(); }
        $venta   = $this->ventaModel->findById((int)$id);
        $detalle = $this->ventaModel->findDetalle((int)$id);
        $config  = $this->ventaModel->getFacturacionConfig();
        require_once VIEWS_PATH . 'Caja' . DS . 'Recibo.php';
    }
}