<?php

/**
 * ServiciosController — CRUD del catálogo de servicios técnicos
 * (servicios_catalogo). Es la Etapa 1 del módulo Servicio Técnico.
 *
 * MVC estricto: toda la persistencia pasa por ServicioCatalogoModel.
 * Permiso requerido: servicio.catalogo
 */
class ServiciosController
{
    private ServicioCatalogoModel $servicioModel;

    public function __construct()
    {
        Auth::check();
        $this->servicioModel = new ServicioCatalogoModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — /Servicios/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('servicio.catalogo');

        $pageTitle = 'Catálogo de Servicios';
        $servicios = $this->servicioModel->findAll();

        require_once VIEWS_PATH . 'Servicios' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — /Servicios/registry[/{id}]
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        Auth::require('servicio.catalogo');

        $esEdicion = !empty($id) && is_numeric($id);
        $pageTitle = $esEdicion ? 'Editar Servicio' : 'Nuevo Servicio';
        $servicio  = $esEdicion
            ? $this->servicioModel->findById((int) $id)
            : new ServicioCatalogoEntity();

        if ($esEdicion && !$servicio->Found) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error',
                'text' => 'El servicio no existe.',
            ];
            header('Location: ' . APP_URL . 'Servicios/index');
            exit();
        }

        require_once VIEWS_PATH . 'Servicios' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — /Servicios/save (POST)
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Servicios/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require('servicio.catalogo');

        if (!Csrf::validate()) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error de seguridad',
                'text' => 'Token inválido. Intenta de nuevo.',
            ];
            header('Location: ' . APP_URL . 'Servicios/index');
            exit();
        }

        $nombre      = htmlspecialchars(strip_tags(trim($_POST['nombre'] ?? '')));
        $descripcion = htmlspecialchars(strip_tags(trim($_POST['descripcion'] ?? '')));
        $precio      = max(0, (float) ($_POST['precio'] ?? 0));
        $categoria   = $_POST['categoria'] ?? 'limpieza';

        // categoria debe pertenecer al ENUM de la tabla.
        if (!in_array($categoria, ['limpieza', 'reparacion', 'diagnostico', 'otro'], true)) {
            $categoria = 'limpieza';
        }

        $redirectForm = $esEdicion
            ? APP_URL . 'Servicios/registry/' . $id
            : APP_URL . 'Servicios/registry';

        if (empty($nombre)) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Campo requerido',
                'text' => 'El nombre del servicio es obligatorio.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        $data = [
            'nombre'      => $nombre,
            'descripcion' => $descripcion,
            'precio'      => $precio,
            'categoria'   => $categoria,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            $ok      = $this->servicioModel->update($data);
            $mensaje = $ok ? 'Servicio actualizado correctamente.' : 'Error al actualizar.';
        } else {
            $ok      = $this->servicioModel->insert($data) > 0;
            $mensaje = $ok ? 'Servicio creado correctamente.' : 'Error al crear el servicio.';
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $mensaje,
        ];
        header('Location: ' . APP_URL . 'Servicios/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — /Servicios/toggle (POST, JSON)
    // ─────────────────────────────────────────────
    public function toggle(): void
    {
        Auth::require('servicio.catalogo');

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

        $ok = $this->servicioModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }
}
