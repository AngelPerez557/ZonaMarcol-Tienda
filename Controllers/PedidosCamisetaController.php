<?php

/**
 * PedidosCamisetaController — Bandeja de pedidos de camisetas que el
 * cliente arma desde el storefront (configurador) + comprobante de
 * transferencia.
 *
 * Flujo:
 *   1. Cliente confirma en `Tienda/configuradorSave` →
 *      pedido_camiseta (estado Pendiente_pago) + detalle + comprobante.
 *   2. Admin entra acá, ve la lista, abre el detalle, mira el comprobante.
 *   3. Si el pago es válido:
 *        - confirmarPago(): registra anticipo (monto declarado por el
 *          admin) y mueve a Confirmado.
 *   4. Avanza estado vía updateEstado() hacia En_proveedor → Recibido →
 *      Entregado o Cancelado.
 *
 * MVC estricto: SQL solo en PedidoCamisetaModel.
 * Permisos: camisetas.pedidos (gestionar la bandeja).
 */
class PedidosCamisetaController
{
    private PedidoCamisetaModel $pedidoModel;
    private NotificacionModel   $notifModel;

    /** Lista de transiciones legales, alineada al ENUM de la BD. */
    private const TRANSICIONES = [
        'Pendiente_pago' => ['Confirmado', 'Cancelado'],
        'Confirmado'     => ['En_proveedor', 'Cancelado'],
        'En_proveedor'   => ['Recibido', 'Cancelado'],
        'Recibido'       => ['Entregado', 'Cancelado'],
        'Entregado'      => [],
        'Cancelado'      => [],
    ];

    public function __construct()
    {
        Auth::check();
        $this->pedidoModel = new PedidoCamisetaModel();
        $this->notifModel  = new NotificacionModel();
    }

    // ─────────────────────────────────────────────
    // INDEX  /PedidosCamiseta/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('camisetas.pedidos');

        $pageTitle = 'Pedidos de Camisetas';
        $pedidos   = $this->pedidoModel->findAll();

        // Conteos por estado para los filtros.
        $estadosOrden = ['Pendiente_pago','Confirmado','En_proveedor','Recibido','Entregado','Cancelado'];
        $conteos      = array_fill_keys($estadosOrden, 0);
        foreach ($pedidos as $p) {
            if (isset($conteos[$p->estado])) {
                $conteos[$p->estado]++;
            }
        }

        require_once VIEWS_PATH . 'PedidosCamiseta' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // DETALLE  /PedidosCamiseta/detalle/{id}
    // ─────────────────────────────────────────────
    public function detalle(string $id = ''): void
    {
        Auth::require('camisetas.pedidos');

        if (!is_numeric($id) || (int) $id <= 0) {
            $this->redirectIndex('error', 'Error', 'Pedido inválido.');
        }

        $pedido = $this->pedidoModel->findById((int) $id);
        if (!$pedido->Found) {
            $this->redirectIndex('error', 'Error', 'El pedido no existe.');
        }

        $detalle      = $this->pedidoModel->findDetalle((int) $id);
        $transiciones = self::TRANSICIONES[$pedido->estado] ?? [];
        $pageTitle    = 'Pedido ' . $pedido->getCodigoFormateado();

        require_once VIEWS_PATH . 'PedidosCamiseta' . DS . 'Detalle.php';
    }

    // ─────────────────────────────────────────────
    // CONFIRMAR PAGO  /PedidosCamiseta/confirmarPago (POST)
    // Registra el anticipo y mueve a Confirmado si el comprobante
    // resulta válido. El monto lo decide el admin (puede ser parcial).
    // ─────────────────────────────────────────────
    public function confirmarPago(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'PedidosCamiseta/index');
            exit();
        }

        Auth::require('camisetas.pedidos');

        if (!Csrf::validate()) {
            $this->redirectIndex('error', 'Error de seguridad', 'Token inválido.');
        }

        $id    = (int) ($_POST['id'] ?? 0);
        $monto = (float) ($_POST['monto'] ?? 0);

        $pedido = $this->pedidoModel->findById($id);
        if (!$pedido->Found) {
            $this->redirectIndex('error', 'Error', 'El pedido no existe.');
        }
        if ($pedido->estado !== 'Pendiente_pago') {
            $this->redirectIndex(
                'warning', 'Estado inválido',
                'El pedido ya no está pendiente de pago.'
            );
        }
        if ($monto <= 0 || $monto > (float) $pedido->total) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Monto inválido',
                'text' => 'Ingresá un monto mayor a 0 y menor o igual al total del pedido.',
            ];
            header('Location: ' . APP_URL . 'PedidosCamiseta/detalle/' . $id);
            exit();
        }

        $okAnt = $this->pedidoModel->registrarAnticipo($id, $monto);
        $okEst = $this->pedidoModel->updateEstado($id, 'Confirmado');

        if ($okAnt && $okEst) {
            $this->notifModel->insert(
                'camiseta',
                'Pago confirmado: ' . $pedido->codigo,
                'Anticipo registrado L. ' . number_format($monto, 2),
                'PedidosCamiseta/detalle/' . $id
            );

            // Email al cliente — fail-soft, no rompe el flujo
            $pedidoActualizado = $this->pedidoModel->findById($id);
            ClienteNotificador::notificarPagoConfirmadoCamiseta(
                (int) $pedido->cliente_id,
                $pedidoActualizado,
                $monto
            );
            $_SESSION['alert'] = [
                'icon' => 'success', 'title' => 'Pago confirmado',
                'text' => 'Anticipo registrado y pedido movido a Confirmado.',
            ];
        } else {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error',
                'text' => 'No se pudo confirmar el pago. Revisar log.',
            ];
        }
        header('Location: ' . APP_URL . 'PedidosCamiseta/detalle/' . $id);
        exit();
    }

    // ─────────────────────────────────────────────
    // CAMBIAR ESTADO  /PedidosCamiseta/cambiarEstado (POST)
    // Solo se aceptan transiciones legales (whitelist).
    // ─────────────────────────────────────────────
    public function cambiarEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'PedidosCamiseta/index');
            exit();
        }

        Auth::require('camisetas.pedidos');

        if (!Csrf::validate()) {
            $this->redirectIndex('error', 'Error de seguridad', 'Token inválido.');
        }

        $id    = (int) ($_POST['id'] ?? 0);
        $nuevo = trim($_POST['estado'] ?? '');

        $pedido = $this->pedidoModel->findById($id);
        if (!$pedido->Found) {
            $this->redirectIndex('error', 'Error', 'El pedido no existe.');
        }

        $legales = self::TRANSICIONES[$pedido->estado] ?? [];
        if (!in_array($nuevo, $legales, true)) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Transición inválida',
                'text' => 'No se puede pasar de ' . $pedido->estado . ' a ' . $nuevo . '.',
            ];
            header('Location: ' . APP_URL . 'PedidosCamiseta/detalle/' . $id);
            exit();
        }

        $ok = $this->pedidoModel->updateEstado($id, $nuevo);

        if ($ok) {
            $this->notifModel->insert(
                'camiseta',
                'Pedido ' . $pedido->codigo,
                'Estado: ' . str_replace('_', ' ', $nuevo),
                'PedidosCamiseta/detalle/' . $id
            );
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Estado actualizado' : 'Error',
            'text'  => $ok
                ? 'El pedido se movió a "' . str_replace('_', ' ', $nuevo) . '".'
                : 'No se pudo actualizar el estado.',
        ];
        header('Location: ' . APP_URL . 'PedidosCamiseta/detalle/' . $id);
        exit();
    }

    // ─────────────────────────────────────────────
    // VER COMPROBANTE  /PedidosCamiseta/verComprobante/{id}
    // Stream del archivo evitando exponer su path real al cliente.
    // ─────────────────────────────────────────────
    public function verComprobante(string $id = ''): void
    {
        Auth::require('camisetas.pedidos');

        if (!is_numeric($id) || (int) $id <= 0) {
            http_response_code(404);
            exit();
        }

        $pedido = $this->pedidoModel->findById((int) $id);
        if (!$pedido->Found || empty($pedido->comprobante_path)) {
            http_response_code(404);
            exit();
        }

        $file = COMPROBANTE_CAMISETA_DIR . basename($pedido->comprobante_path);
        if (!is_readable($file)) {
            http_response_code(404);
            exit();
        }

        // Tipo de contenido derivado de la extensión.
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            default       => 'application/octet-stream',
        };

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: private, max-age=0');
        readfile($file);
        exit();
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    private function redirectIndex(string $icon, string $title, string $text): void
    {
        $_SESSION['alert'] = ['icon' => $icon, 'title' => $title, 'text' => $text];
        header('Location: ' . APP_URL . 'PedidosCamiseta/index');
        exit();
    }
}
