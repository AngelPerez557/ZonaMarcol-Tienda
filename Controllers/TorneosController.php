<?php

/**
 * TorneosController — CRUD del catálogo de Torneos (ligas de clubes y
 * competiciones de selecciones) del módulo Camisetas.
 *
 * MVC estricto: ninguna consulta vive aquí; toda la persistencia pasa por
 * TorneoModel. El controlador solo orquesta: valida entrada, delega y
 * decide la respuesta (vista o redirect).
 *
 * Permiso requerido: camisetas.catalogo
 */
class TorneosController
{
    private TorneoModel $torneoModel;

    public function __construct()
    {
        // Sesión válida obligatoria antes de cualquier acción.
        Auth::check();
        $this->torneoModel = new TorneoModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Listado de torneos
    // URL: /Torneos/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('camisetas.catalogo');

        $pageTitle = 'Torneos';
        $torneos   = $this->torneoModel->findAll();

        require_once VIEWS_PATH . 'Torneos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — Crear o editar torneo
    // URL: /Torneos/registry      → crear
    // URL: /Torneos/registry/{id} → editar
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        Auth::require('camisetas.catalogo');

        $esEdicion = !empty($id) && is_numeric($id);
        $pageTitle = $esEdicion ? 'Editar Torneo' : 'Nuevo Torneo';
        $torneo    = $esEdicion
            ? $this->torneoModel->findById((int) $id)
            : new TorneoEntity();

        // findById devuelve una entidad vacía (Found = false) si no existe.
        if ($esEdicion && !$torneo->Found) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'El torneo no existe.',
            ];
            header('Location: ' . APP_URL . 'Torneos/index');
            exit();
        }

        require_once VIEWS_PATH . 'Torneos' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — Guardar torneo (POST)
    // URL: /Torneos/save
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Torneos/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require('camisetas.catalogo');

        // CSRF — el formulario debe traer el token de sesión.
        if (!Csrf::validate()) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error de seguridad',
                'text'  => 'Token inválido. Intenta de nuevo.',
            ];
            header('Location: ' . APP_URL . 'Torneos/index');
            exit();
        }

        // Sanitización de entradas de texto.
        $nombre = htmlspecialchars(strip_tags(trim($_POST['nombre'] ?? '')));
        $tipo   = $_POST['tipo'] ?? 'liga_club';
        $pais   = htmlspecialchars(strip_tags(trim($_POST['pais'] ?? ''))) ?: null;
        $orden  = (int) ($_POST['orden'] ?? 0);

        // El tipo debe pertenecer al ENUM de la tabla `torneos`.
        $tiposValidos = ['liga_club', 'seleccion', 'copa_continental', 'otro'];
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'liga_club';
        }

        // A dónde volver si la validación falla: al mismo formulario.
        $redirectForm = $esEdicion
            ? APP_URL . 'Torneos/registry/' . $id
            : APP_URL . 'Torneos/registry';

        if (empty($nombre)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campo requerido',
                'text'  => 'El nombre del torneo es obligatorio.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        // ── Logo ──────────────────────────────────────────────
        // Opcional en edición (si no se sube, se conserva el actual),
        // obligatorio al crear (la columna logo_path es NOT NULL).
        $logoPath = null;
        if (!empty($_FILES['logo']['name'])) {
            $logoPath = ImageOptimizer::process($_FILES['logo'], LIGA_LOGO_UPLOAD_DIR, 'liga_');
            if ($logoPath === null) {
                $_SESSION['alert'] = [
                    'icon'  => 'error',
                    'title' => 'Error de imagen',
                    'text'  => ImageOptimizer::$lastError ?? 'No se pudo procesar el logo.',
                ];
                header('Location: ' . $redirectForm);
                exit();
            }
        }
        if (!$esEdicion && $logoPath === null) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Logo requerido',
                'text'  => 'Debes subir el logo del torneo.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        $data = [
            'nombre' => $nombre,
            'tipo'   => $tipo,
            'pais'   => $pais,
            'orden'  => $orden,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            // Solo se envía logo_path si hubo imagen nueva; si no, el
            // TorneoModel pasa null y el SP conserva el logo existente.
            if ($logoPath !== null) {
                $data['logo_path'] = $logoPath;
            }
            $ok      = $this->torneoModel->update($data);
            $mensaje = $ok ? 'Torneo actualizado correctamente.' : 'Error al actualizar.';
        } else {
            $data['logo_path'] = $logoPath;
            $ok      = $this->torneoModel->insert($data) > 0;
            $mensaje = $ok ? 'Torneo creado correctamente.' : 'Error al crear el torneo.';
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $mensaje,
        ];
        header('Location: ' . APP_URL . 'Torneos/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — Activar / desactivar (POST, responde JSON)
    // URL: /Torneos/toggle
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

        $ok = $this->torneoModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }

    // ─────────────────────────────────────────────
    // DELETE — Eliminar torneo (POST)
    // URL: /Torneos/delete
    // OJO: la FK equipos.torneo_id es ON DELETE CASCADE — al borrar un
    // torneo se borran también sus equipos (y, en cadena, equipaciones).
    // ─────────────────────────────────────────────
    public function delete(): void
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

        $id = (int) ($_POST['id'] ?? 0);
        $ok = $this->torneoModel->delete($id);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok
                ? 'Torneo eliminado. Sus equipos asociados también se eliminaron.'
                : 'Error al eliminar el torneo.',
        ];
        header('Location: ' . APP_URL . 'Torneos/index');
        exit();
    }
}
