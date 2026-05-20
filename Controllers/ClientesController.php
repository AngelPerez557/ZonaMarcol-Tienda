<?php

class ClientesController
{
    private ClienteModel $clienteModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->clienteModel = new ClienteModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Listado de clientes
    // URL: /Clientes/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('clientes.ver');

        $pageTitle = 'Clientes';
        $clientes  = $this->clienteModel->findAll();

        require_once VIEWS_PATH . 'Clientes' . DS . 'Index.php';
    }

    // ─────────────────────────────────────────────
    // TOGGLE — Activar / desactivar (POST — JSON)
    // URL: /Clientes/toggle
    // ─────────────────────────────────────────────
    public function toggle(): void
    {
        Auth::require('clientes.ver');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!Csrf::validate()) {
            http_response_code(403);
            exit();
        }

        $id     = (int) ($_POST['id']     ?? 0);
        $activo = (int) ($_POST['activo'] ?? 0);
        $ok     = $this->clienteModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }

    // ─────────────────────────────────────────────
    // SEARCH — Buscar clientes para la Caja (GET — JSON)
    // URL: /Clientes/search?q=nombre
    // ─────────────────────────────────────────────
    public function search(): void
    {
        Auth::require('clientes.ver');

        $query = htmlspecialchars(strip_tags(trim($_GET['q'] ?? '')));

        if (strlen($query) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit();
        }

        $resultados = $this->clienteModel->search($query);

        header('Content-Type: application/json');
        echo json_encode($resultados);
        exit();
    }

    // ─────────────────────────────────────────────
    // HISTORIAL — Ver compras de un cliente
    // URL: /Clientes/historial/{id}
    // ─────────────────────────────────────────────
    public function historial(string $id = ''): void
    {
        Auth::require('clientes.ver');

        if (empty($id) || !is_numeric($id)) {
            header('Location: ' . APP_URL . 'Clientes/index');
            exit();
        }

        $cliente  = $this->clienteModel->findById((int) $id);

        if (!$cliente->Found) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'El cliente no existe.',
            ];
            header('Location: ' . APP_URL . 'Clientes/index');
            exit();
        }

        // TODO: cuando VentaModel esté completo
        // $ventaModel = new VentaModel();
        // $ventas = $ventaModel->findByCliente((int) $id);
        $ventas    = [];
        $pageTitle = 'Historial de ' . $cliente->nombre;

        require_once VIEWS_PATH . 'Clientes' . DS . 'Historial.php';
    }
}