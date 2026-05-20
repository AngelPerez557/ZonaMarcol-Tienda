<?php

class ZonasController
{
    private ZonaModel $model;

    public function __construct()
    {
        Auth::check();
        $this->model = new ZonaModel();
    }

    public function index(): void
    {
        Auth::require('zonas.ver');
        $pageTitle = 'Zonas de Envío';
        $zonas     = $this->model->findAll();
        require_once VIEWS_PATH . 'Zonas' . DS . 'index.php';
    }

    public function registry(string $id = ''): void
    {
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require('zonas.gestionar');

        $pageTitle = $esEdicion ? 'Editar Zona' : 'Nueva Zona';
        $zona      = $esEdicion ? $this->model->findById((int) $id) : null;

        if ($esEdicion && !$zona) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Zona no encontrada.'];
            header('Location: ' . APP_URL . 'Zonas/index');
            exit();
        }

        require_once VIEWS_PATH . 'Zonas' . DS . 'Registry.php';
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Zonas/index');
            exit();
        }

        Auth::require('zonas.gestionar');

        if (!Csrf::validate()) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error de seguridad','text'=>'Token inválido.'];
            header('Location: ' . APP_URL . 'Zonas/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;
        $nombre    = htmlspecialchars(strip_tags(trim($_POST['nombre'] ?? '')));
        $costo     = (float) ($_POST['costo'] ?? 0);

        if (empty($nombre)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Requerido','text'=>'El nombre es obligatorio.'];
            header('Location: ' . APP_URL . ($esEdicion ? 'Zonas/registry/' . $id : 'Zonas/registry'));
            exit();
        }

        if ($esEdicion) {
            $ok = $this->model->update(['id'=>$id,'nombre'=>$nombre,'costo'=>$costo]);
        } else {
            $ok = $this->model->insert(['nombre'=>$nombre,'costo'=>$costo]) > 0;
        }

        $_SESSION['alert'] = ['icon'=>$ok?'success':'error','title'=>$ok?'Éxito':'Error',
            'text'=>$ok?'Zona guardada.':'Error al guardar.'];
        header('Location: ' . APP_URL . 'Zonas/index');
        exit();
    }

    public function toggle(): void
    {
        Auth::require('zonas.gestionar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        Csrf::validateOrFail();
        $ok = $this->model->toggleActivo((int)($_POST['id']??0), (int)($_POST['activo']??0));
        header('Content-Type: application/json');
        echo json_encode(['success'=>$ok]);
        exit();
    }

    public function delete(): void
    {
        Auth::require('zonas.gestionar');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        Csrf::validateOrFail();
        $ok = $this->model->delete((int)($_POST['id']??0));
        $_SESSION['alert'] = ['icon'=>$ok?'success':'error','title'=>$ok?'Eliminado':'Error',
            'text'=>$ok?'Zona eliminada.':'Error al eliminar.'];
        header('Location: ' . APP_URL . 'Zonas/index');
        exit();
    }
}