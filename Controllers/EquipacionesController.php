<?php

/**
 * EquipacionesController — CRUD del catálogo de Equipaciones (la camisa
 * concreta: equipo + temporada + tipo + versión) del módulo Camisetas.
 *
 * Llave única en BD: (equipo_id, temporada_id, tipo_equipacion_id, version).
 * Un alta con combinación repetida la rechaza la base de datos.
 *
 * Permiso requerido: camisetas.catalogo
 */
class EquipacionesController
{
    private EquipacionModel       $equipacionModel;
    private EquipoModel           $equipoModel;
    private TemporadaModel        $temporadaModel;
    private CamisetaCatalogoModel $catalogoModel;

    public function __construct()
    {
        Auth::check();
        $this->equipacionModel = new EquipacionModel();
        $this->equipoModel     = new EquipoModel();
        $this->temporadaModel  = new TemporadaModel();
        $this->catalogoModel   = new CamisetaCatalogoModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — /Equipaciones/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('camisetas.catalogo');

        $pageTitle    = 'Equipaciones';
        $equipaciones = $this->equipacionModel->findAll();

        require_once VIEWS_PATH . 'Equipaciones' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — /Equipaciones/registry[/{id}]
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        Auth::require('camisetas.catalogo');

        $esEdicion  = !empty($id) && is_numeric($id);
        $pageTitle  = $esEdicion ? 'Editar Equipación' : 'Nueva Equipación';
        $equipacion = $esEdicion
            ? $this->equipacionModel->findById((int) $id)
            : new EquipacionEntity();

        if ($esEdicion && !$equipacion->Found) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error',
                'text' => 'La equipación no existe.',
            ];
            header('Location: ' . APP_URL . 'Equipaciones/index');
            exit();
        }

        // Catálogos para los selectores del formulario.
        $equipos    = $this->equipoModel->findAll();
        $temporadas = $this->temporadaModel->findAll();
        $tipos      = $this->catalogoModel->tiposEquipacionActivos();

        require_once VIEWS_PATH . 'Equipaciones' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — /Equipaciones/save (POST)
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Equipaciones/index');
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
            header('Location: ' . APP_URL . 'Equipaciones/index');
            exit();
        }

        $equipoId     = (int) ($_POST['equipo_id']          ?? 0);
        $temporadaId  = (int) ($_POST['temporada_id']       ?? 0);
        $tipoId       = (int) ($_POST['tipo_equipacion_id'] ?? 0);
        $version      = $_POST['version'] ?? '';
        $precioBase   = (float) ($_POST['precio_base'] ?? 0);

        $redirectForm = $esEdicion
            ? APP_URL . 'Equipaciones/registry/' . $id
            : APP_URL . 'Equipaciones/registry';

        // La versión debe pertenecer al ENUM de la tabla `equipaciones`.
        if (!in_array($version, ['hombre', 'mujer', 'infantil'], true)) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Versión no válida',
                'text' => 'Selecciona hombre, mujer o infantil.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        if ($equipoId === 0 || $temporadaId === 0 || $tipoId === 0) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Campos requeridos',
                'text' => 'Equipo, temporada y tipo de equipación son obligatorios.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        if ($precioBase <= 0) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Precio inválido',
                'text' => 'El precio base debe ser mayor a cero.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        // ── Imagen de la camisa ───────────────────────────────
        $imagenPath = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imagenPath = ImageOptimizer::process($_FILES['imagen'], EQUIPACION_IMAGE_UPLOAD_DIR, 'eqp_');
            if ($imagenPath === null) {
                $_SESSION['alert'] = [
                    'icon' => 'error', 'title' => 'Error de imagen',
                    'text' => ImageOptimizer::$lastError ?? 'No se pudo procesar la imagen.',
                ];
                header('Location: ' . $redirectForm);
                exit();
            }
        }
        if (!$esEdicion && $imagenPath === null) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Imagen requerida',
                'text' => 'Debes subir la imagen de la camisa.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        $data = [
            'equipo_id'          => $equipoId,
            'temporada_id'       => $temporadaId,
            'tipo_equipacion_id' => $tipoId,
            'version'            => $version,
            'precio_base'        => $precioBase,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            if ($imagenPath !== null) {
                $data['imagen_path'] = $imagenPath;
            }
            $ok      = $this->equipacionModel->update($data);
            $mensaje = $ok ? 'Equipación actualizada correctamente.' : 'Error al actualizar.';
        } else {
            $data['imagen_path'] = $imagenPath;
            $ok      = $this->equipacionModel->insert($data) > 0;
            $mensaje = $ok
                ? 'Equipación creada correctamente.'
                : 'Error al crear. Verifica que esa combinación equipo/temporada/tipo/versión no exista ya.';
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $mensaje,
        ];
        header('Location: ' . APP_URL . 'Equipaciones/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — /Equipaciones/toggle (POST, JSON)
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

        $ok = $this->equipacionModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }
}
