<?php

class CategoriasController
{
    private CategoriaModel $categoriaModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->categoriaModel = new CategoriaModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Listado de categorías
    // URL: /Categorias/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('categorias.ver');

        $pageTitle  = 'Categorías';
        $categorias = $this->categoriaModel->findAll();

        require_once VIEWS_PATH . 'Categorias' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — Crear o editar categoría
    // URL: /Categorias/registry      → crear
    // URL: /Categorias/registry/{id} → editar
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require($esEdicion ? 'categorias.editar' : 'categorias.crear');

        $pageTitle = $esEdicion ? 'Editar Categoría' : 'Nueva Categoría';
        $categoria = $esEdicion
            ? $this->categoriaModel->findById((int) $id)
            : new CategoriaEntity();

        if ($esEdicion && !$categoria->Found) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'La categoría no existe.',
            ];
            header('Location: ' . APP_URL . 'Categorias/index');
            exit();
        }

        require_once VIEWS_PATH . 'Categorias' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — Guardar categoría (POST)
    // URL: /Categorias/save
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Categorias/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require($esEdicion ? 'categorias.editar' : 'categorias.crear');

        // Validar CSRF
        if (!Csrf::validate()) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error de seguridad',
                'text'  => 'Token inválido. Intenta de nuevo.',
            ];
            header('Location: ' . APP_URL . 'Categorias/index');
            exit();
        }

        $nombre      = htmlspecialchars(strip_tags(trim($_POST['nombre']      ?? '')));
        $descripcion = htmlspecialchars(strip_tags(trim($_POST['descripcion'] ?? '')));

        if (empty($nombre)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campo requerido',
                'text'  => 'El nombre de la categoría es obligatorio.',
            ];
            $redirect = $esEdicion
                ? APP_URL . 'Categorias/registry/' . $id
                : APP_URL . 'Categorias/registry';
            header('Location: ' . $redirect);
            exit();
        }

        $data = [
            'nombre'      => $nombre,
            'descripcion' => $descripcion ?: null,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            $ok         = $this->categoriaModel->update($data);
            $mensaje    = $ok ? 'Categoría actualizada correctamente.' : 'Error al actualizar.';
        } else {
            $nuevoId = $this->categoriaModel->insert($data);
            $ok      = $nuevoId > 0;
            $mensaje = $ok ? 'Categoría creada correctamente.' : 'Error al crear la categoría.';
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $mensaje,
        ];

        header('Location: ' . APP_URL . 'Categorias/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — Activar / desactivar (POST)
    // URL: /Categorias/toggle
    // ─────────────────────────────────────────────
    public function toggle(): void
    {
        Auth::require('categorias.editar');

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

        // Verificar que no tenga productos activos antes de desactivar
        if ($activo === 0 && $this->categoriaModel->hasProductos($id)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'No puedes desactivar una categoría con productos activos.',
            ]);
            exit();
        }

        $ok = $this->categoriaModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }

    // ─────────────────────────────────────────────
    // DELETE — Eliminar categoría (POST)
    // URL: /Categorias/delete
    // ─────────────────────────────────────────────
    public function delete(): void
    {
        Auth::require('categorias.eliminar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!Csrf::validate()) {
            http_response_code(403);
            exit();
        }

        $id = (int) ($_POST['id'] ?? 0);

        // Verificar que no tenga productos activos
        if ($this->categoriaModel->hasProductos($id)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'No permitido',
                'text'  => 'No puedes eliminar una categoría con productos activos.',
            ];
            header('Location: ' . APP_URL . 'Categorias/index');
            exit();
        }

        $ok = $this->categoriaModel->delete($id);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok ? 'Categoría eliminada correctamente.' : 'Error al eliminar.',
        ];

        header('Location: ' . APP_URL . 'Categorias/index');
        exit();
    }
}