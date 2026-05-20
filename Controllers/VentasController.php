<?php

class VentasController
{
    private VentaModel $ventaModel;

    public function __construct()
    {
        Auth::check();
        $this->ventaModel = new VentaModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Historial de ventas
    // URL: /Ventas/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('ventas.ver');

        $pageTitle = 'Historial de Ventas';
        $ventas    = $this->ventaModel->findAll();
        $totalHoy  = $this->ventaModel->totalHoy();
        $countHoy  = $this->ventaModel->countHoy();

        require_once VIEWS_PATH . 'Ventas' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // DETALLE — Ver detalle de una venta
    // URL: /Ventas/detalle/{id}
    // ─────────────────────────────────────────────
    public function detalle(string $id = ''): void
    {
        Auth::require('ventas.ver');

        if (empty($id) || !is_numeric($id)) {
            header('Location: ' . APP_URL . 'Ventas/index');
            exit();
        }

        $venta   = $this->ventaModel->findById((int) $id);
        $detalle = $this->ventaModel->findDetalle((int) $id);
        $config  = $this->ventaModel->getFacturacionConfig();

        if (!$venta) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'La venta no existe.'];
            header('Location: ' . APP_URL . 'Ventas/index');
            exit();
        }

        $pageTitle = 'Detalle de Venta #' . str_pad($id, 8, '0', STR_PAD_LEFT);

        require_once VIEWS_PATH . 'Ventas' . DS . 'Detalle.php';
    }

    // ─────────────────────────────────────────────
    // ANULAR — (POST — JSON)
    // URL: /Ventas/anular
    // No elimina el registro — ley fiscal Honduras
    // ─────────────────────────────────────────────
    public function anular(): void
    {
        header('Content-Type: application/json');

        if (!Auth::can('ventas.eliminar')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sin permiso para anular ventas.']);
            exit();
        }

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

        $id     = (int) ($_POST['id'] ?? 0);
        $motivo = htmlspecialchars(strip_tags(trim($_POST['motivo'] ?? '')));

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Venta inválida.']);
            exit();
        }

        if (empty($motivo)) {
            echo json_encode(['success' => false, 'message' => 'El motivo de anulación es obligatorio.']);
            exit();
        }

        // Verificar que la venta existe y no está ya anulada
        $venta = $this->ventaModel->findById($id);
        if (!$venta) {
            echo json_encode(['success' => false, 'message' => 'La venta no existe.']);
            exit();
        }

        if ((int) $venta['anulada'] === 1) {
            echo json_encode(['success' => false, 'message' => 'Esta venta ya fue anulada.']);
            exit();
        }

        $ok = $this->ventaModel->anular($id, $motivo, Auth::id());

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Venta anulada correctamente.' : 'Error al anular la venta.',
        ]);
        exit();
    }
}