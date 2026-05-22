<?php

/**
 * OrdenesController — Órdenes de servicio técnico (Etapa 2).
 *
 * Cubre la recepción de equipos, el listado y el detalle. Las transiciones
 * de estado, ítems de presupuesto y pagos se agregan en etapas posteriores.
 *
 * MVC estricto: la persistencia vive en OrdenServicioModel.
 * Permisos: servicio.ver (consultar) · servicio.recibir (crear/editar).
 */
class OrdenesController
{
    private OrdenServicioModel $ordenModel;
    private ClienteModel       $clienteModel;
    private UserModel          $userModel;

    public function __construct()
    {
        Auth::check();
        $this->ordenModel   = new OrdenServicioModel();
        $this->clienteModel = new ClienteModel();
        $this->userModel    = new UserModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Listado de órdenes  /Ordenes/index
    // El filtro por estado es del lado del cliente (JS), por eso
    // se cargan todas las órdenes de una vez.
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('servicio.ver');

        $pageTitle = 'Órdenes de Servicio';
        $ordenes   = $this->ordenModel->findAll();
        $conteos   = $this->ordenModel->contarPorEstado();

        require_once VIEWS_PATH . 'Ordenes' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — Recepción / edición  /Ordenes/registry[/{id}]
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        Auth::require('servicio.recibir');

        $esEdicion = !empty($id) && is_numeric($id);
        $pageTitle = $esEdicion ? 'Editar Orden' : 'Nueva Orden de Servicio';
        $orden     = $esEdicion
            ? $this->ordenModel->findById((int) $id)
            : new OrdenServicioEntity();

        if ($esEdicion && !$orden->Found) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error',
                'text' => 'La orden no existe.',
            ];
            header('Location: ' . APP_URL . 'Ordenes/index');
            exit();
        }

        // Una orden entregada o cancelada ya no se edita.
        if ($esEdicion && $orden->estaCerrada()) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'No editable',
                'text' => 'Una orden entregada o cancelada no se puede modificar.',
            ];
            header('Location: ' . APP_URL . 'Ordenes/detalle/' . $id);
            exit();
        }

        // Catálogos para los selectores.
        $clientes = $this->clienteModel->findAll();
        $usuarios = $this->userModel->findAll();

        require_once VIEWS_PATH . 'Ordenes' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE  /Ordenes/save (POST)
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Ordenes/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require('servicio.recibir');

        if (!Csrf::validate()) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error de seguridad',
                'text' => 'Token inválido. Intenta de nuevo.',
            ];
            header('Location: ' . APP_URL . 'Ordenes/index');
            exit();
        }

        $clienteId  = (int) ($_POST['cliente_id'] ?? 0);
        $tecnicoId  = (int) ($_POST['tecnico_id'] ?? 0);
        $equipo     = htmlspecialchars(strip_tags(trim($_POST['equipo_descripcion'] ?? '')));
        $serial     = htmlspecialchars(strip_tags(trim($_POST['serial'] ?? '')));
        $accesorios = htmlspecialchars(strip_tags(trim($_POST['accesorios_entregados'] ?? '')));
        $diagnostico = htmlspecialchars(strip_tags(trim($_POST['diagnostico_inicial'] ?? '')));
        $observaciones = htmlspecialchars(strip_tags(trim($_POST['observaciones'] ?? '')));

        $redirectForm = $esEdicion
            ? APP_URL . 'Ordenes/registry/' . $id
            : APP_URL . 'Ordenes/registry';

        if ($clienteId === 0 || empty($equipo)) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Campos requeridos',
                'text' => 'El cliente y la descripción del equipo son obligatorios.',
            ];
            header('Location: ' . $redirectForm);
            exit();
        }

        $data = [
            'cliente_id'            => $clienteId,
            'tecnico_id'            => $tecnicoId,
            'equipo_descripcion'    => $equipo,
            'serial'                => $serial,
            'accesorios_entregados' => $accesorios,
            'diagnostico_inicial'   => $diagnostico,
            'observaciones'         => $observaciones,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            $ok        = $this->ordenModel->update($data);
            $ordenId   = $id;
            $mensaje   = $ok ? 'Orden actualizada correctamente.' : 'Error al actualizar la orden.';
        } else {
            // Datos que solo se fijan al crear: código único y quién recibe.
            $data['codigo']            = $this->ordenModel->generarCodigo();
            $data['user_recepcion_id'] = Auth::id();
            $ordenId = $this->ordenModel->insert($data);
            $ok      = $ordenId > 0;
            $mensaje = $ok ? 'Orden de servicio creada correctamente.' : 'Error al crear la orden.';
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito' : 'Error',
            'text'  => $mensaje,
        ];

        header('Location: ' . APP_URL . ($ok ? 'Ordenes/detalle/' . $ordenId : 'Ordenes/index'));
        exit();
    }

    // ─────────────────────────────────────────────
    // DETALLE — Ficha de una orden  /Ordenes/detalle/{id}
    // ─────────────────────────────────────────────
    public function detalle(string $id = ''): void
    {
        Auth::require('servicio.ver');

        $orden = $this->ordenModel->findById((int) $id);
        if (!$orden->Found) {
            $_SESSION['alert'] = [
                'icon' => 'error', 'title' => 'Error',
                'text' => 'La orden no existe.',
            ];
            header('Location: ' . APP_URL . 'Ordenes/index');
            exit();
        }

        $pageTitle = 'Orden ' . $orden->codigo;

        require_once VIEWS_PATH . 'Ordenes' . DS . 'Detalle.php';
    }
}
