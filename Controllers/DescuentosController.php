<?php

class DescuentosController
{
    private DescuentoModel  $descuentoModel;
    private CategoriaModel  $categoriaModel;

    public function __construct()
    {
        Auth::check();
        $this->descuentoModel = new DescuentoModel();
        $this->categoriaModel = new CategoriaModel();
    }

    // URL: /Descuentos/index
    public function index(): void
    {
        Auth::require('productos.ver');
        $pageTitle  = 'Descuentos';
        $descuentos = $this->descuentoModel->findAll();
        require_once VIEWS_PATH . 'Descuentos' . DS . 'index.php';
    }

    // URL: /Descuentos/registry | /Descuentos/registry/{id}
    public function registry(string $id = ''): void
    {
        Auth::require('productos.editar');
        $esEdicion  = !empty($id) && is_numeric($id);
        $pageTitle  = $esEdicion ? 'Editar Descuento' : 'Nuevo Descuento';
        $descuento  = $esEdicion
            ? $this->descuentoModel->findById((int) $id)
            : null;
        $categorias = $this->categoriaModel->findActivas();
        require_once VIEWS_PATH . 'Descuentos' . DS . 'Registry.php';
    }

    // URL: /Descuentos/save (POST)
    public function save(): void
    {
        Auth::require('productos.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Descuentos/index'); exit();
        }
        if (!Csrf::validate()) {
            header('Location: ' . APP_URL . 'Descuentos/index'); exit();
        }

        $id          = (int) ($_POST['id'] ?? 0);
        $esEdicion   = $id > 0;
        $nombre      = htmlspecialchars(strip_tags(trim($_POST['nombre']      ?? '')));
        $porcentaje  = (float) ($_POST['porcentaje']  ?? 0);
        $aplicaA     = in_array($_POST['aplica_a'] ?? '', ['todo','categoria'])
                        ? $_POST['aplica_a'] : 'todo';
        $categoriaId = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
        $fechaInicio = $_POST['fecha_inicio'] ?? '';
        $fechaFin    = $_POST['fecha_fin']    ?? '';
        $activo      = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || $porcentaje <= 0 || empty($fechaInicio) || empty($fechaFin)) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Campos requeridos',
                'text' => 'Completa todos los campos obligatorios.',
            ];
            header('Location: ' . APP_URL . ($esEdicion ? "Descuentos/registry/$id" : 'Descuentos/registry'));
            exit();
        }

        $data = compact('nombre','porcentaje','aplicaA','categoriaId','fechaInicio','fechaFin','activo');
        $data['aplica_a']     = $aplicaA;
        $data['categoria_id'] = $categoriaId;
        $data['fecha_inicio'] = $fechaInicio;
        $data['fecha_fin']    = $fechaFin;

        if ($esEdicion) {
            $data['id'] = $id;
            $ok = $this->descuentoModel->update($data);
        } else {
            $ok = $this->descuentoModel->insert($data) > 0;
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $ok ? 'Descuento guardado correctamente.' : 'Error al guardar.',
        ];
        header('Location: ' . APP_URL . 'Descuentos/index');
        exit();
    }

    // URL: /Descuentos/delete (POST)
    public function delete(): void
    {
        Auth::require('productos.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); exit();
        }
        if (!Csrf::validate()) {
            http_response_code(403); exit();
        }

        $ok = $this->descuentoModel->delete((int)($_POST['id'] ?? 0));

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok ? 'Descuento eliminado.' : 'Error al eliminar.',
        ];
        header('Location: ' . APP_URL . 'Descuentos/index');
        exit();
    }

    // URL: /Descuentos/getActivo (GET — JSON) — usado por caja y tienda
    public function getActivo(): void
    {
        header('Content-Type: application/json');
        $descuento = $this->descuentoModel->getActivo();
        echo json_encode($descuento ?? ['activo' => false]);
        exit();
    }
}