<?php

class BannersController
{
    private BannerModel $model;

    public function __construct()
    {
        Auth::check();
        $this->model = new BannerModel();
    }

    public function index(): void
    {
        Auth::require('banners.ver');
        $pageTitle = 'Banners';
        $banners   = $this->model->findAll();
        require_once VIEWS_PATH . 'Banners' . DS . 'index.php';
    }

    public function registry(string $id = ''): void
    {
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require('banners.gestionar');

        $pageTitle = $esEdicion ? 'Editar Banner' : 'Nuevo Banner';
        $banner    = $esEdicion ? $this->model->findById((int) $id) : null;

        if ($esEdicion && !$banner) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Banner no encontrado.'];
            header('Location: ' . APP_URL . 'Banners/index');
            exit();
        }

        require_once VIEWS_PATH . 'Banners' . DS . 'Registry.php';
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Banners/index');
            exit();
        }

        Auth::require('banners.gestionar');

        if (!Csrf::validate()) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error de seguridad','text'=>'Token inválido.'];
            header('Location: ' . APP_URL . 'Banners/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;
        $titulo    = htmlspecialchars(strip_tags(trim($_POST['titulo'] ?? '')));
        $enlace    = htmlspecialchars(strip_tags(trim($_POST['enlace'] ?? '')));
        $orden     = (int) ($_POST['orden'] ?? 0);

        $imageUrl = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imageUrl = $this->subirImagen($_FILES['imagen']);
            if (!$imageUrl) {
                $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'Solo JPG, PNG o WEBP. Máx. 10MB.'];
                header('Location: ' . APP_URL . ($esEdicion ? 'Banners/registry/' . $id : 'Banners/registry'));
                exit();
            }
        }

        if ($esEdicion) {
            $ok = $this->model->update(['id'=>$id,'titulo'=>$titulo,'imagen_url'=>$imageUrl,'enlace'=>$enlace,'orden'=>$orden]);
        } else {
            if (!$imageUrl) {
                $_SESSION['alert'] = ['icon'=>'warning','title'=>'Imagen requerida','text'=>'Sube una imagen para el banner.'];
                header('Location: ' . APP_URL . 'Banners/registry');
                exit();
            }
            $ok = $this->model->insert(['titulo'=>$titulo,'imagen_url'=>$imageUrl,'enlace'=>$enlace,'orden'=>$orden]) > 0;
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito'   : 'Error',
            'text'  => $ok ? 'Banner guardado.' : 'Error al guardar.',
        ];
        header('Location: ' . APP_URL . 'Banners/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — Activa/desactiva banner (POST — JSON)
    // ─────────────────────────────────────────────
    public function toggle(): void
    {
        // Header JSON PRIMERO
        header('Content-Type: application/json');

        if (!Auth::can('banners.gestionar')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sin permiso.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false]);
            exit();
        }

        if (!Csrf::validate()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token inválido.']);
            exit();
        }

        $ok = $this->model->toggleActivo((int)($_POST['id'] ?? 0), (int)($_POST['activo'] ?? 0));
        echo json_encode(['success' => $ok]);
        exit();
    }

    // ─────────────────────────────────────────────
    // DELETE — Elimina banner (POST)
    // ─────────────────────────────────────────────
    public function delete(): void
    {
        if (!Auth::can('banners.gestionar')) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Sin permiso','text'=>'No tienes permiso para eliminar banners.'];
            header('Location: ' . APP_URL . 'Banners/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Banners/index');
            exit();
        }

        if (!Csrf::validate()) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Token inválido','text'=>'Error de seguridad.'];
            header('Location: ' . APP_URL . 'Banners/index');
            exit();
        }

        $ok = $this->model->delete((int)($_POST['id'] ?? 0));

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok ? 'Banner eliminado.' : 'Error al eliminar.',
        ];
        header('Location: ' . APP_URL . 'Banners/index');
        exit();
    }

    private function subirImagen(array $file): ?string
    {
        $destino = BANNER_IMAGE_UPLOAD_DIR;
        if (!is_dir($destino)) mkdir($destino, 0755, true);
        return ImageOptimizer::process($file, $destino, 'banner_');
    }
}