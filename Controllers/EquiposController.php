<?php

/**
 * EquiposController — CRUD del catálogo de Equipos (clubes / selecciones)
 * del módulo Camisetas.
 *
 * Un equipo pertenece a un torneo (FK) y se asocia N:M con competiciones
 * (qué torneos/copas juega → qué parches puede llevar su camisa). La
 * relación N:M se persiste con EquipoModel::syncCompeticiones().
 *
 * Permiso requerido: camisetas.catalogo
 */
class EquiposController
{
    private EquipoModel       $equipoModel;
    private TorneoModel       $torneoModel;
    private CompeticionModel  $competicionModel;

    public function __construct()
    {
        Auth::check();
        $this->equipoModel      = new EquipoModel();
        $this->torneoModel      = new TorneoModel();
        $this->competicionModel = new CompeticionModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — /Equipos/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('camisetas.catalogo');

        $pageTitle = 'Equipos';
        $equipos   = $this->equipoModel->findAll();

        require_once VIEWS_PATH . 'Equipos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — /Equipos/registry[/{id}]
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        Auth::require('camisetas.catalogo');

        $esEdicion = !empty($id) && is_numeric($id);
        $pageTitle = $esEdicion ? 'Editar Equipo' : 'Nuevo Equipo';
        $equipo    = $esEdicion
            ? $this->equipoModel->findById((int) $id)
            : new EquipoEntity();

        if ($esEdicion && !$equipo->Found) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error',
                'text' => 'El equipo no existe.',
            ];
            header('Location: ' . APP_URL . 'Equipos/index');
            exit();
        }

        // Datos para los selectores del formulario.
        $torneos       = $this->torneoModel->findAll();
        $competiciones = $this->competicionModel->findAll();

        // IDs de competiciones ya asociadas al equipo (para marcar checkboxes).
        $competicionesEquipo = [];
        if ($esEdicion) {
            foreach ($this->equipoModel->findCompeticiones((int) $id) as $row) {
                $competicionesEquipo[] = (int) ($row['id'] ?? $row['competicion_id'] ?? 0);
            }
        }

        require_once VIEWS_PATH . 'Equipos' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — /Equipos/save (POST)
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Equipos/index');
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
            header('Location: ' . APP_URL . 'Equipos/index');
            exit();
        }

        $torneoId = (int) ($_POST['torneo_id'] ?? 0);
        $nombre   = htmlspecialchars(strip_tags(trim($_POST['nombre'] ?? '')));
        $orden    = (int) ($_POST['orden'] ?? 0);
        // Competiciones marcadas — se normalizan a enteros.
        $competicionIds = array_map('intval', $_POST['competiciones'] ?? []);

        $redirectForm = $esEdicion
            ? APP_URL . 'Equipos/registry/' . $id
            : APP_URL . 'Equipos/registry';

        if (empty($nombre) || $torneoId === 0) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Campos requeridos',
                'text' => 'El nombre y el torneo son obligatorios.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        // ── Escudo ────────────────────────────────────────────
        $escudoPath = null;
        if (!empty($_FILES['escudo']['name'])) {
            $escudoPath = ImageOptimizer::process($_FILES['escudo'], EQUIPO_LOGO_UPLOAD_DIR, 'escudo_');
            if ($escudoPath === null) {
                $_SESSION['alert'] = [
                    'icon' => 'error', 'title' => 'Error de imagen',
                    'text' => ImageOptimizer::$lastError ?? 'No se pudo procesar el escudo.',
                ];
                header('Location: ' . $redirectForm);
                exit();
            }
        }
        if (!$esEdicion && $escudoPath === null) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Escudo requerido',
                'text' => 'Debes subir el escudo del equipo.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        $data = [
            'torneo_id' => $torneoId,
            'nombre'    => $nombre,
            'orden'     => $orden,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            if ($escudoPath !== null) {
                $data['escudo_path'] = $escudoPath;
            }
            $ok        = $this->equipoModel->update($data);
            $equipoId  = $id;
            $mensaje   = $ok ? 'Equipo actualizado correctamente.' : 'Error al actualizar.';
        } else {
            $data['escudo_path'] = $escudoPath;
            $equipoId = $this->equipoModel->insert($data);
            $ok       = $equipoId > 0;
            $mensaje  = $ok ? 'Equipo creado correctamente.' : 'Error al crear el equipo.';
        }

        // Sincronizar la relación N:M equipo ↔ competiciones solo si el
        // equipo se guardó bien (el sync es transaccional dentro del Model).
        if ($ok) {
            $this->equipoModel->syncCompeticiones($equipoId, $competicionIds);
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $mensaje,
        ];
        header('Location: ' . APP_URL . 'Equipos/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — /Equipos/toggle (POST, JSON)
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

        $ok = $this->equipoModel->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }

    // ─────────────────────────────────────────────
    // DELETE — /Equipos/delete (POST)
    // FK equipaciones.equipo_id es ON DELETE CASCADE — al borrar un
    // equipo se borran también sus equipaciones.
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
        $ok = $this->equipoModel->delete($id);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok
                ? 'Equipo eliminado. Sus equipaciones asociadas también se eliminaron.'
                : 'Error al eliminar el equipo.',
        ];
        header('Location: ' . APP_URL . 'Equipos/index');
        exit();
    }
}
