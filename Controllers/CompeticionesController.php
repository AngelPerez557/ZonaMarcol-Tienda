<?php

/**
 * CompeticionesController — CRUD del catálogo de Competiciones (parches
 * cosibles: LaLiga, Champions, Mundial…) del módulo Camisetas.
 *
 * Cada competición tiene un parche (imagen) y un precio_extra que se suma
 * al pedido cuando el cliente la elige. MVC estricto: persistencia en
 * CompeticionModel.
 *
 * Permiso requerido: camisetas.catalogo
 */
class CompeticionesController
{
    private CompeticionModel $competicionModel;

    public function __construct()
    {
        Auth::check();
        $this->competicionModel = new CompeticionModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — /Competiciones/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('camisetas.catalogo');

        $pageTitle     = 'Competiciones';
        $competiciones = $this->competicionModel->findAll();

        require_once VIEWS_PATH . 'Competiciones' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — /Competiciones/registry[/{id}]
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        Auth::require('camisetas.catalogo');

        $esEdicion   = !empty($id) && is_numeric($id);
        $pageTitle   = $esEdicion ? 'Editar Competición' : 'Nueva Competición';
        $competicion = $esEdicion
            ? $this->competicionModel->findById((int) $id)
            : new CompeticionEntity();

        if ($esEdicion && !$competicion->Found) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error',
                'text' => 'La competición no existe.',
            ];
            header('Location: ' . APP_URL . 'Competiciones/index');
            exit();
        }

        require_once VIEWS_PATH . 'Competiciones' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — /Competiciones/save (POST)
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Competiciones/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require('camisetas.catalogo');

        if (!Csrf::validate()) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error de seguridad',
                'text' => 'Token inválido. Intenta de nuevo.',
            ];
            header('Location: ' . APP_URL . 'Competiciones/index');
            exit();
        }

        $nombre      = htmlspecialchars(strip_tags(trim($_POST['nombre'] ?? '')));
        $precioExtra = max(0, (float) ($_POST['precio_extra'] ?? 0));

        $redirectForm = $esEdicion
            ? APP_URL . 'Competiciones/registry/' . $id
            : APP_URL . 'Competiciones/registry';

        if (empty($nombre)) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Campo requerido',
                'text' => 'El nombre de la competición es obligatorio.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        // ── Parche ────────────────────────────────────────────
        // Obligatorio al crear (parche_path es NOT NULL); opcional al editar.
        $parchePath = null;
        if (!empty($_FILES['parche']['name'])) {
            $parchePath = ImageOptimizer::process($_FILES['parche'], COMPETICION_PARCHE_DIR, 'parche_');
            if ($parchePath === null) {
                $_SESSION['alert'] = [
                    'icon' => 'error', 'title' => 'Error de imagen',
                    'text' => ImageOptimizer::$lastError ?? 'No se pudo procesar el parche.',
                ];
                header('Location: ' . $redirectForm);
                exit();
            }
        }
        if (!$esEdicion && $parchePath === null) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Parche requerido',
                'text' => 'Debes subir la imagen del parche.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        $data = [
            'nombre'       => $nombre,
            'precio_extra' => $precioExtra,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            if ($parchePath !== null) {
                $data['parche_path'] = $parchePath;
            }
            $ok      = $this->competicionModel->update($data);
            $mensaje = $ok ? 'Competición actualizada correctamente.' : 'Error al actualizar.';
        } else {
            $data['parche_path'] = $parchePath;
            $ok      = $this->competicionModel->insert($data) > 0;
            $mensaje = $ok ? 'Competición creada correctamente.' : 'Error al crear la competición.';
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $mensaje,
        ];
        header('Location: ' . APP_URL . 'Competiciones/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — /Competiciones/toggle (POST, JSON)
    // ─────────────────────────────────────────────
    public function toggle(): void
    {
        Auth::require('camisetas.catalogo');

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

        $ok = $this->competicionModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }
}
