<?php

/**
 * CatalogosController — Página única que gestiona los catálogos simples
 * del módulo Camisetas: Temporadas, Tipos de equipación y las 3 tablas
 * de Tallas (hombre/mujer/infantil).
 *
 * Decisión de diseño: una sola vista en vez de un CRUD por catálogo —
 * son entidades chicas (apenas nombre + orden/años) y abrir un módulo
 * completo por cada una es overkill.
 *
 * Los handlers POST se llaman `save` y `toggle` a propósito: están en la
 * whitelist `$metodosJson` de index.php, así no se carga la plantilla
 * antes del header() y se evita "headers already sent".
 *
 * Permiso: camisetas.catalogo (admin lo tiene por seed).
 */
class CatalogosController
{
    private TemporadaModel        $temporadaModel;
    private CamisetaCatalogoModel $catalogo;

    public function __construct()
    {
        Auth::check();
        $this->temporadaModel = new TemporadaModel();
        $this->catalogo       = new CamisetaCatalogoModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — /Catalogos/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('camisetas.catalogo');

        $pageTitle      = 'Catálogos de Camisetas';
        $temporadas     = $this->temporadaModel->findAll();
        $tipos          = $this->catalogo->tiposFindAll();
        $tallasHombre   = $this->catalogo->tallasFindAll('hombre');
        $tallasMujer    = $this->catalogo->tallasFindAll('mujer');
        $tallasInfantil = $this->catalogo->tallasFindAll('infantil');

        require_once VIEWS_PATH . 'Catalogos' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — POST único que dispatch por $_POST['cat']
    // cat = temporada | tipo | talla
    // Si cat = talla, $_POST['subtipo'] = hombre|mujer|infantil
    // ─────────────────────────────────────────────
    public function save(): void
    {
        Auth::require('camisetas.catalogo');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Catalogos/index');
            exit();
        }
        if (!Csrf::validate()) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error de seguridad',
                'text' => 'Token inválido. Intenta de nuevo.',
            ];
            header('Location: ' . APP_URL . 'Catalogos/index');
            exit();
        }

        $cat       = $_POST['cat'] ?? '';
        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;
        $nombre    = htmlspecialchars(strip_tags(trim($_POST['nombre'] ?? '')));

        if (empty($nombre)) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Campo requerido',
                'text' => 'El nombre es obligatorio.',
            ];
            header('Location: ' . APP_URL . 'Catalogos/index');
            exit();
        }

        $ok      = false;
        $titulo  = 'Registro';

        switch ($cat) {
            case 'temporada':
                $data = [
                    'nombre'      => $nombre,
                    'anio_inicio' => (int) ($_POST['anio_inicio'] ?? 0),
                    'anio_fin'    => (int) ($_POST['anio_fin']    ?? 0),
                ];
                if ($esEdicion) {
                    $data['id'] = $id;
                    $ok = $this->temporadaModel->update($data);
                } else {
                    $ok = $this->temporadaModel->insert($data) > 0;
                }
                $titulo = 'Temporada';
                break;

            case 'tipo':
                $data = [
                    'nombre' => $nombre,
                    'orden'  => (int) ($_POST['orden'] ?? 0),
                ];
                if ($esEdicion) {
                    $data['id'] = $id;
                    $ok = $this->catalogo->tipoUpdate($data);
                } else {
                    $ok = $this->catalogo->tipoInsert($data) > 0;
                }
                $titulo = 'Tipo de equipación';
                break;

            case 'talla':
                $subtipo = $_POST['subtipo'] ?? '';
                if (!in_array($subtipo, ['hombre', 'mujer', 'infantil'], true)) {
                    $_SESSION['alert'] = [
                        'icon' => 'error', 'title' => 'Error',
                        'text' => 'Tipo de talla no válido.',
                    ];
                    header('Location: ' . APP_URL . 'Catalogos/index');
                    exit();
                }
                $data = [
                    'nombre' => $nombre,
                    'orden'  => (int) ($_POST['orden'] ?? 0),
                ];
                if ($esEdicion) {
                    $data['id'] = $id;
                    $ok = $this->catalogo->tallaUpdate($subtipo, $data);
                } else {
                    $ok = $this->catalogo->tallaInsert($subtipo, $data) > 0;
                }
                $titulo = 'Talla ' . ucfirst($subtipo);
                break;

            default:
                $_SESSION['alert'] = [
                    'icon' => 'error', 'title' => 'Error',
                    'text' => 'Catálogo no válido.',
                ];
                header('Location: ' . APP_URL . 'Catalogos/index');
                exit();
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $ok
                ? "{$titulo} guardada correctamente."
                : "Error al guardar {$titulo}.",
        ];
        header('Location: ' . APP_URL . 'Catalogos/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // TOGGLE — POST JSON. Mismo dispatch por cat.
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

        $cat    = $_POST['cat'] ?? '';
        $id     = (int) ($_POST['id'] ?? 0);
        $activo = (int) ($_POST['activo'] ?? 0);
        $ok     = false;

        switch ($cat) {
            case 'temporada':
                $ok = $this->temporadaModel->toggleActivo($id, $activo);
                break;
            case 'tipo':
                $ok = $this->catalogo->tipoToggle($id, $activo);
                break;
            case 'talla':
                $subtipo = $_POST['subtipo'] ?? '';
                if (in_array($subtipo, ['hombre', 'mujer', 'infantil'], true)) {
                    $ok = $this->catalogo->tallaToggle($subtipo, $id, $activo);
                }
                break;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }
}
