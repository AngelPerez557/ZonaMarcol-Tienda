<?php

class TiendaController
{
    private ProductoModel     $productoModel;
    private CategoriaModel    $categoriaModel;
    private BannerModel       $bannerModel;
    private PedidoModel       $pedidoModel;
    private ClienteModel      $clienteModel;
    private ZonaModel         $zonaModel;
    private NotificacionModel $notifModel;
    private VentaModel        $ventaModel;
    private FavoritoModel     $favoritoModel;


    public function __construct()
    {
        $this->productoModel  = new ProductoModel();
        $this->categoriaModel = new CategoriaModel();
        $this->bannerModel    = new BannerModel();
        $this->pedidoModel    = new PedidoModel();
        $this->clienteModel   = new ClienteModel();
        $this->zonaModel      = new ZonaModel();
        $this->notifModel     = new NotificacionModel();
        $this->ventaModel     = new VentaModel();
        $this->favoritoModel  = new FavoritoModel();
    }

   public function index(): void
    {
        $pageTitle           = 'Inicio';
        $banners             = $this->bannerModel->findActivos();
        $productos           = $this->productoModel->findActivos();
        $categorias          = $this->categoriaModel->findAll();
        $descuentoModel      = new DescuentoModel();
        $descuentoActivo     = $descuentoModel->getActivo();
        $productosDestacados = array_slice($productos, 0, 8);

        // ── IDs de favoritos del cliente autenticado ──
        $favoritosIds = [];
        if (!empty($_SESSION['cliente'])) {
            $favs = $this->favoritoModel->findByCliente((int)$_SESSION['cliente']['id']);
            // $favs viene como arrays asociativos (FETCH_ASSOC), no objetos
            $favoritosIds = array_map(
                fn($f) => (int) (is_array($f) ? ($f['producto_id'] ?? 0) : ($f->producto_id ?? 0)),
                $favs
            );
        }

        $this->render('Inicio.php', compact(
            'pageTitle','banners','productosDestacados',
            'categorias','descuentoActivo','favoritosIds'
        ));
    }
    public function catalogo(string $catId = ''): void
    {
        $pageTitle   = 'Catálogo';
        $categoriaId = !empty($catId) ? (int)$catId : (int)($_GET['categoria'] ?? 0);
        $categorias  = $this->categoriaModel->findAll();
        $productos   = $this->productoModel->findActivos();
        $descuentoModel  = new DescuentoModel();
        $descuentoActivo = $descuentoModel->getActivo();

        if ($categoriaId > 0) {
            $productos = array_values(array_filter(
                $productos, fn($p) => (int)$p->categoria_id === $categoriaId
            ));
        }

        // ── IDs de favoritos del cliente autenticado ──
        $favoritosIds = [];
        if (!empty($_SESSION['cliente'])) {
            $favs = $this->favoritoModel->findByCliente((int)$_SESSION['cliente']['id']);
            // $favs viene como arrays asociativos (FETCH_ASSOC), no objetos
            $favoritosIds = array_map(
                fn($f) => (int) (is_array($f) ? ($f['producto_id'] ?? 0) : ($f->producto_id ?? 0)),
                $favs
            );
        }

        $this->render('Catalogo.php', compact(
            'pageTitle','productos','categorias','categoriaId','descuentoActivo','favoritosIds'
        ));
    }

    public function producto(string $id = ''): void
    {
        $descuentoModel  = new DescuentoModel();
        $descuentoActivo = $descuentoModel->getActivo();
        // Acepta /Tienda/producto/5 y /Tienda/producto/5-nombre-del-producto
        $idNum = (int) $id;
        if (!$idNum) {
            header('Location: ' . APP_URL . 'Tienda/catalogo'); exit();
        }
        $producto = $this->productoModel->findById($idNum);
        if (!$producto->Found || !$producto->activo) {
            header('Location: ' . APP_URL . 'Tienda/catalogo'); exit();
        }
        $variantes = $this->productoModel->findVariantes($idNum);
        $pageTitle = $producto->nombre;
        $this->render('Producto.php', compact('pageTitle','producto','variantes','descuentoActivo'));
    }

    public function carrito(): void
    {
        $pageTitle = 'Carrito';
        $zonas     = $this->zonaModel->findActivas();
        $this->render('Carrito.php', compact('pageTitle','zonas'));
    }

    // ─────────────────────────────────────────────
    // VERIFICAR STOCK — endpoint AJAX
    // Verifica stock antes de agregar al carrito
    // URL: /Tienda/verificarStock  (POST — JSON)
    // ─────────────────────────────────────────────
    public function verificarStock(): void
    {
        header('Content-Type: application/json');

        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $varianteId = (int) ($_POST['variante_id'] ?? 0);
        $cantidad   = (int) ($_POST['cantidad']    ?? 1);

        if (!$productoId) {
            echo json_encode(['disponible' => false, 'mensaje' => 'Producto inválido']);
            exit();
        }

        if ($varianteId > 0) {
            // Verificar stock de variante
            $variante = $this->productoModel->findVarianteById($varianteId);
            if (!$variante || !$variante->activo || $variante->stock < $cantidad) {
                echo json_encode([
                    'disponible' => false,
                    'mensaje'    => 'Esta opción no está disponible en la cantidad solicitada.',
                    'stock'      => $variante ? (int)$variante->stock : 0,
                ]);
                exit();
            }
        } else {
            // Verificar stock de producto simple
            $producto = $this->productoModel->findById($productoId);
            if (!$producto->Found || !$producto->activo || $producto->stock < $cantidad) {
                echo json_encode([
                    'disponible' => false,
                    'mensaje'    => 'Este producto no está disponible en la cantidad solicitada.',
                    'stock'      => $producto->Found ? (int)$producto->stock : 0,
                ]);
                exit();
            }
        }

        echo json_encode(['disponible' => true]);
        exit();
    }

    // ─────────────────────────────────────────────
    // CHECKOUT — crea pedido + venta + factura
    // ─────────────────────────────────────────────
    public function checkout(): void
    {
        $this->requireCliente();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Tienda/carrito'); exit();
        }
        if (!isset($_POST['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            header('Location: ' . APP_URL . 'Tienda/carrito'); exit();
        }

        $clienteId   = !empty($_SESSION['cliente']['id']) ? (int)$_SESSION['cliente']['id'] : null;
        $waNumero    = htmlspecialchars(strip_tags(trim($_POST['wa_numero']       ?? '')));
        $tipoEntrega = htmlspecialchars(strip_tags(trim($_POST['tipo_entrega']    ?? 'Retiro')));
        $direccion   = htmlspecialchars(strip_tags(trim($_POST['direccion_envio'] ?? '')));
        $zonaId      = !empty($_POST['zona_id']) ? (int)$_POST['zona_id'] : null;
        $nota        = htmlspecialchars(strip_tags(trim($_POST['nota']            ?? '')));
        $items       = json_decode($_POST['items'] ?? '[]', true);

        if (empty($items)) {
            header('Location: ' . APP_URL . 'Tienda/carrito'); exit();
        }

        // ── Validar stock de todos los items ──────
        foreach ($items as $item) {
            $pid = (int)($item['id']         ?? 0);
            $vid = (int)($item['varianteId'] ?? 0);
            $qty = (int)($item['cantidad']   ?? 1);

            if ($vid > 0) {
                $variante = $this->productoModel->findVarianteById($vid);
                if (!$variante || $variante->stock < $qty) {
                    $_SESSION['alert'] = [
                        'icon'  => 'warning',
                        'title' => 'Sin stock',
                        'text'  => "El producto \"{$item['nombre']}\" no tiene suficiente stock.",
                    ];
                    header('Location: ' . APP_URL . 'Tienda/carrito'); exit();
                }
            } else {
                $producto = $this->productoModel->findById($pid);
                if (!$producto->Found || $producto->stock < $qty) {
                    $_SESSION['alert'] = [
                        'icon'  => 'warning',
                        'title' => 'Sin stock',
                        'text'  => "El producto \"{$item['nombre']}\" no tiene suficiente stock.",
                    ];
                    header('Location: ' . APP_URL . 'Tienda/carrito'); exit();
                }
            }
        }

        // ── Calcular totales ──────────────────────
        $subtotal   = array_reduce(
            $items, fn($sum, $i) => $sum + (float)$i['precio'] * (int)$i['cantidad'], 0
        );
        $costoEnvio = 0;

        if ($tipoEntrega === 'Envio' && $zonaId) {
            $zona       = $this->zonaModel->findById($zonaId);
            $costoEnvio = $zona ? (float)($zona['costo'] ?? 0) : 0;
        }

        $total  = $subtotal + $costoEnvio;
        $codigo = $this->pedidoModel->generarCodigo();

        // ── Crear pedido ──────────────────────────
        $pedidoId = $this->pedidoModel->insert([
            'codigo'          => $codigo,
            'cliente_id'      => $clienteId,
            'wa_numero'       => $waNumero    ?: null,
            'tipo_entrega'    => $tipoEntrega,
            'metodo_pago'     => in_array($_POST['metodo_pago'] ?? '', ['Transferencia','Efectivo'])
                                    ? $_POST['metodo_pago'] : 'Transferencia',
            'direccion_envio' => $tipoEntrega === 'Envio' ? $direccion : null,
            'zona_id'         => $zonaId,
            'subtotal'        => $subtotal,
            'costo_envio'     => $costoEnvio,
            'total'           => $total,
            'nota'            => $nota ?: null,
        ]);

        if ($pedidoId <= 0) {
            header('Location: ' . APP_URL . 'Tienda/carrito?error=1'); exit();
        }

        // ── Insertar detalle del pedido ───────────
        foreach ($items as $item) {
            $this->pedidoModel->insertDetalle([
                'pedido_id'       => $pedidoId,
                'producto_id'     => $item['id'],
                'variante_id'     => $item['varianteId'] ?? null,
                'nombre_producto' => $item['nombre'],
                'precio_unit'     => $item['precio'],
                'cantidad'        => $item['cantidad'],
                'subtotal'        => $item['precio'] * $item['cantidad'],
            ]);
        }

        // ── Descontar stock de cada producto ──────
        foreach ($items as $item) {
            $vid = (int)($item['varianteId'] ?? 0);
            $qty = (int)($item['cantidad']   ?? 1);
            if ($vid > 0) {
                $this->productoModel->updateVarianteStock($vid, $qty);
            } else {
                $this->productoModel->updateStock((int)$item['id'], $qty);
            }
        }

        // ── Notificación al panel ─────────────────
        $clienteNombre = $_SESSION['cliente']['nombre'] ?? 'Cliente';
        $this->notifModel->nuevoPedido($codigo, $clienteNombre, $total);

        // ── Guardar datos para vista de éxito ─────
        // La venta se registra en caja cuando el admin confirma el pago
        $_SESSION['pedido_exitoso'] = [
            'pedido_id' => $pedidoId,
            'venta_id'  => 0,
        ];

        header('Location: ' . APP_URL . 'Tienda/pedidoExitoso');
        exit();
    }

    public function pedidoExitoso(): void
    {
        $datos = $_SESSION['pedido_exitoso'] ?? null;
        if (!$datos) {
            header('Location: ' . APP_URL . 'Tienda'); exit();
        }
        unset($_SESSION['pedido_exitoso']);

        $pedidoId = is_array($datos) ? (int)$datos['pedido_id'] : (int)$datos;
        $ventaId  = is_array($datos) ? (int)($datos['venta_id'] ?? 0) : 0;

        $pedido        = $this->pedidoModel->findById($pedidoId);
        $detalle       = $this->pedidoModel->findDetalle($pedidoId);
        $factConfig    = $this->ventaModel->getFacturacionConfig();
        $pageTitle     = 'Pedido confirmado';

        $this->render('PedidoExitoso.php', compact(
            'pageTitle','pedido','detalle','ventaId','factConfig'
        ));
    }

    public function misPedidos(): void
    {
        $this->requireCliente();
        $pageTitle = 'Mis Pedidos';
        $pedidos   = $this->pedidoModel->findByCliente(
            (int)$_SESSION['cliente']['id']
        );
        $this->render('MisPedidos.php', compact('pageTitle','pedidos'));
    }

    public function registro(): void
    {
        if (!empty($_SESSION['cliente'])) {
            header('Location: ' . APP_URL . 'Tienda/index'); exit();
        }
        $pageTitle = 'Crear cuenta';
        $error     = $_GET['error'] ?? null;
        $this->render('Registro.php', compact('pageTitle','error'));
    }

    public function guardarRegistro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Tienda/registro'); exit();
        }
        if (!isset($_POST['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            header('Location: ' . APP_URL . 'Tienda/registro?error=csrf'); exit();
        }

        $nombre    = htmlspecialchars(strip_tags(trim($_POST['nombre']   ?? '')));
        $email     = htmlspecialchars(strip_tags(trim($_POST['email']    ?? '')));
        $telefono  = htmlspecialchars(strip_tags(trim($_POST['telefono'] ?? '')));
        $password  = trim($_POST['password']  ?? '');
        $password2 = trim($_POST['password2'] ?? '');

        if (empty($nombre) || empty($email) || empty($password)) {
            header('Location: ' . APP_URL . 'Tienda/registro?error=campos'); exit();
        }
        if ($password !== $password2) {
            header('Location: ' . APP_URL . 'Tienda/registro?error=password'); exit();
        }
        if ($this->clienteModel->emailExists($email)) {
            header('Location: ' . APP_URL . 'Tienda/registro?error=email'); exit();
        }

        $id = $this->clienteModel->insert([
            'nombre'    => $nombre,
            'email'     => $email,
            'telefono'  => $telefono ?: null,
            'direccion' => null,
            'password'  => password_hash($password, PASSWORD_BCRYPT),
        ]);

        if ($id > 0) {
            $cliente = $this->clienteModel->findById($id);
            $_SESSION['cliente'] = [
                'id'       => $cliente->id,
                'nombre'   => $cliente->nombre,
                'email'    => $cliente->email,
                'telefono' => $cliente->telefono,
            ];
            header('Location: ' . APP_URL . 'Tienda/index');
        } else {
            header('Location: ' . APP_URL . 'Tienda/registro?error=server');
        }
        exit();
    }

    public function login(): void
    {
        if (!empty($_SESSION['cliente'])) {
            header('Location: ' . APP_URL . 'Tienda/index'); exit();
        }
        $pageTitle = 'Iniciar sesión';
        $error     = $_GET['error'] ?? null;
        $this->render('Login.php', compact('pageTitle','error'));
    }

    public function procesarLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Tienda/login'); exit();
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::check($ip)) {
            $minutos = RateLimiter::minutosRestantes($ip);
            header('Location: ' . APP_URL . 'Tienda/login?blocked=1&min=' . $minutos);
            exit();
        }

        $email    = htmlspecialchars(strip_tags(trim($_POST['email']    ?? '')));
        $password = trim($_POST['password'] ?? '');
        $cliente  = $this->clienteModel->findByEmail($email);

        if (!$cliente->Found || !password_verify($password, $cliente->password ?? '')) {
            RateLimiter::registrarFallo($ip);
            header('Location: ' . APP_URL . 'Tienda/login?error=credenciales'); exit();
        }
        if (!$cliente->isActivo()) {
            header('Location: ' . APP_URL . 'Tienda/login?error=inactivo'); exit();
        }

        RateLimiter::limpiar($ip);
        $_SESSION['cliente'] = [
            'id'       => $cliente->id,
            'nombre'   => $cliente->nombre,
            'email'    => $cliente->email,
            'telefono' => $cliente->telefono,
        ];

        $redirect = $_SESSION['redirect_tienda'] ?? APP_URL . 'Tienda/index';
        unset($_SESSION['redirect_tienda']);
        header('Location: ' . $redirect);
        exit();
    }

    public function logout(): void
    {
        // F-09 — Logout requiere CSRF token (via ?csrf=... en GET o POST).
        // Sin token, no se cierra sesión (previene logout CSRF).
        $token = $_GET['csrf'] ?? $_POST['csrf_token'] ?? '';
        if (!Csrf::validate($token)) {
            header('Location: ' . APP_URL . 'Tienda/index');
            exit();
        }

        unset($_SESSION['cliente']);
        header('Location: ' . APP_URL . 'Tienda/index');
        exit();
    }

    private function requireCliente(): void
    {
        if (empty($_SESSION['cliente'])) {
            $_SESSION['redirect_tienda'] = APP_URL . ($_GET['url'] ?? '');
            header('Location: ' . APP_URL . 'Tienda/login');
            exit();
        }
    }// ─────────────────────────────────────────────
// MI PERFIL — ver y editar datos del cliente
// URL: /Tienda/miPerfil
// ─────────────────────────────────────────────
public function miPerfil(): void
{
    $this->requireCliente();
    $pageTitle     = 'Mi Perfil';
    $cliente       = $this->clienteModel->findById((int)$_SESSION['cliente']['id']);
    $error         = $_GET['error']         ?? null;
    $ok            = $_GET['ok']            ?? null;
    $errorPassword = $_GET['errorPassword'] ?? null;
    $okPassword    = $_GET['okPassword']    ?? null;
    $this->render('Miperfil.php', compact(
        'pageTitle','cliente','error','ok','errorPassword','okPassword'
    ));
}

// ─────────────────────────────────────────────
// GUARDAR PERFIL — actualiza datos del cliente
// URL: /Tienda/guardarPerfil  (POST)
// ─────────────────────────────────────────────
public function guardarPerfil(): void
{
    $this->requireCliente();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . APP_URL . 'Tienda/miPerfil'); exit();
    }
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('Location: ' . APP_URL . 'Tienda/miPerfil'); exit();
    }

    $clienteId = (int)$_SESSION['cliente']['id'];
    $nombre    = htmlspecialchars(strip_tags(trim($_POST['nombre']    ?? '')));
    $email     = htmlspecialchars(strip_tags(trim($_POST['email']     ?? '')));
    $telefono  = htmlspecialchars(strip_tags(trim($_POST['telefono']  ?? '')));
    $direccion = htmlspecialchars(strip_tags(trim($_POST['direccion'] ?? '')));

    if (empty($nombre) || (empty($email) && empty($telefono))) {
        header('Location: ' . APP_URL . 'Tienda/miPerfil?error=campos'); exit();
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . APP_URL . 'Tienda/miPerfil?error=email'); exit();
    }

    if (!empty($email) && $this->clienteModel->emailExistsForUpdate($email, $clienteId)) {
        header('Location: ' . APP_URL . 'Tienda/miPerfil?error=duplicado'); exit();
    }

    $ok = $this->clienteModel->update([
        'id'        => $clienteId,
        'nombre'    => $nombre,
        'email'     => $email    ?: null,
        'telefono'  => $telefono ?: null,
        'direccion' => $direccion ?: null,
    ]);

    if ($ok) {
        // Actualizar sesión
        $_SESSION['cliente']['nombre']   = $nombre;
        $_SESSION['cliente']['email']    = $email;
        $_SESSION['cliente']['telefono'] = $telefono;
        header('Location: ' . APP_URL . 'Tienda/miPerfil?ok=1'); exit();
    }

    header('Location: ' . APP_URL . 'Tienda/miPerfil?error=servidor'); exit();
}

// ─────────────────────────────────────────────
// CAMBIAR PASSWORD — actualiza contraseña cliente
// URL: /Tienda/cambiarPassword  (POST)
// ─────────────────────────────────────────────
public function cambiarPassword(): void
{
    $this->requireCliente();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . APP_URL . 'Tienda/miPerfil'); exit();
    }
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('Location: ' . APP_URL . 'Tienda/miPerfil'); exit();
    }

    $clienteId   = (int)$_SESSION['cliente']['id'];
    $actual      = trim($_POST['password_actual']    ?? '');
    $nueva       = trim($_POST['password_nueva']     ?? '');
    $confirmar   = trim($_POST['password_confirmar'] ?? '');

    $cliente = $this->clienteModel->findById($clienteId);

    if (!password_verify($actual, $cliente->password ?? '')) {
        header('Location: ' . APP_URL . 'Tienda/miPerfil?errorPassword=actual'); exit();
    }
    if ($nueva !== $confirmar) {
        header('Location: ' . APP_URL . 'Tienda/miPerfil?errorPassword=coincide'); exit();
    }
    if (strlen($nueva) < 6) {
        header('Location: ' . APP_URL . 'Tienda/miPerfil?errorPassword=corta'); exit();
    }

    $ok = $this->clienteModel->updatePassword(
        $clienteId,
        password_hash($nueva, PASSWORD_BCRYPT)
    );

    if ($ok) {
        header('Location: ' . APP_URL . 'Tienda/miPerfil?okPassword=1'); exit();
    }

    header('Location: ' . APP_URL . 'Tienda/miPerfil?errorPassword=servidor'); exit();
}

    private function render(string $vista, array $vars = []): void
    {
        extract($vars);
        $urlActual = strtolower(trim($_GET['url'] ?? '', '/'));

        ob_start();
        require VIEWS_PATH . 'Tienda' . DS . $vista;
        $content = ob_get_clean();

        ob_start();
        require ROOT . 'Template' . DS . 'Tienda' . DS . 'index.php';
        $template = ob_get_clean();

        $output = str_replace('{JBODY}',    $content, $template);
        $output = str_replace('{JSCRIPTS}', '',        $output);
        echo $output;
    }

    public function toggleFavorito(): void
    {
        header('Content-Type: application/json');

        if (empty($_SESSION['cliente'])) {
            echo json_encode(['error' => 'no_auth']);
            exit();
        }

        $productoId = (int)($_POST['producto_id'] ?? 0);
        if (!$productoId) {
            echo json_encode(['error' => 'invalid']);
            exit();
        }

        $clienteId = (int)$_SESSION['cliente']['id'];
        $liked     = $this->favoritoModel->toggle($clienteId, $productoId);
        echo json_encode(['liked' => $liked]);
        exit();
    }
    public function misFavoritos(): void
    {
        $this->requireCliente();
        $pageTitle = 'Mis Favoritos';
        $favoritos = $this->favoritoModel->findByCliente(
            (int)$_SESSION['cliente']['id']
        );
        $this->render('MisFavoritos.php', compact('pageTitle', 'favoritos'));
    }
    // ─────────────────────────────────────────────
    // CONFIRMAR PAGO — (POST — JSON)
    // URL: /Pedidos/confirmarPago
    // Crea la venta en caja cuando el pago es verificado
    // ─────────────────────────────────────────────
    public function confirmarPago(): void
    {
        Auth::require('pedidos.gestionar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); exit();
        }
        if (!Csrf::validate()) {
            http_response_code(403); exit();
        }

        $pedidoId = (int)($_POST['pedido_id'] ?? 0);
        if (!$pedidoId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pedido inválido.']);
            exit();
        }

        $pedido = $this->pedidoModel->findById($pedidoId);
        if (!$pedido->Found) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado.']);
            exit();
        }

        if ($pedido->pagado) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Este pedido ya fue pagado.']);
            exit();
        }

        $detalle   = $this->pedidoModel->findDetalle($pedidoId);
        $ventaModel = new VentaModel();

        // Crear venta en caja
        $ventaId = $ventaModel->insert([
            'cliente_id'     => $pedido->cliente_id,
            'user_id'        => Auth::id(),
            'metodo_pago'    => $pedido->metodo_pago ?? 'Transferencia',
            'subtotal'       => $pedido->subtotal,
            'descuento'      => 0,
            'total'          => $pedido->total,
            'monto_recibido' => $pedido->total,
            'cambio'         => 0,
            'nota'           => "Pedido tienda en línea #{$pedido->codigo}",
        ]);

        if ($ventaId > 0) {
            // Insertar detalle de venta
            foreach ($detalle as $item) {
                $ventaModel->insertDetalle([
                    'venta_id'        => $ventaId,
                    'producto_id'     => $item['producto_id'],
                    'variante_id'     => $item['variante_id'] ?? null,
                    'nombre_producto' => $item['nombre_producto'],
                    'precio_unit'     => $item['precio_unit'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['subtotal'],
                ]);
            }

            // Marcar pedido como pagado y cambiar estado
            $this->pedidoModel->marcarPagado($pedidoId, Auth::id());

            header('Content-Type: application/json');
            echo json_encode([
                'success'  => true,
                'message'  => 'Pago confirmado. Venta registrada en caja.',
                'venta_id' => $ventaId,
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al registrar la venta.']);
        }
        exit();
    }

    
}