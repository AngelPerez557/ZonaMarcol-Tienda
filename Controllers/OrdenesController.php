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
    private OrdenServicioModel      $ordenModel;
    private OrdenServicioItemModel  $itemModel;
    private OrdenServicioPagoModel  $pagoModel;
    private ServicioCatalogoModel   $catalogoModel;
    private ClienteModel            $clienteModel;
    private UserModel               $userModel;
    private CajaSesionModel         $cajaModel;
    private NotificacionModel       $notifModel;

    public function __construct()
    {
        Auth::check();
        $this->ordenModel    = new OrdenServicioModel();
        $this->itemModel     = new OrdenServicioItemModel();
        $this->pagoModel     = new OrdenServicioPagoModel();
        $this->catalogoModel = new ServicioCatalogoModel();
        $this->clienteModel  = new ClienteModel();
        $this->userModel     = new UserModel();
        $this->cajaModel     = new CajaSesionModel();
        $this->notifModel    = new NotificacionModel();
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
    // Carga: orden + items + historial + catálogo de servicios para el
    // dropdown de agregar item + transiciones legales del estado actual.
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

        $items        = $this->itemModel->findByOrden((int) $orden->id);
        $historial    = $this->ordenModel->findHistorial((int) $orden->id);
        $catalogo     = $this->catalogoModel->findActivos();
        $tecnicos     = $this->userModel->findAll();
        $transiciones = OrdenServicioModel::TRANSICIONES[$orden->estado] ?? [];
        $totalAprob   = $this->itemModel->sumarTotal((int) $orden->id, true);
        $pagos        = $this->pagoModel->findByOrden((int) $orden->id);
        $cajaAbierta  = $this->cajaModel->getSesionAbierta(Auth::id());
        $pageTitle    = 'Orden ' . $orden->codigo;

        require_once VIEWS_PATH . 'Ordenes' . DS . 'Detalle.php';
    }

    // ─────────────────────────────────────────────
    // AGREGAR ITEM  /Ordenes/agregarItem  (POST)
    // ─────────────────────────────────────────────
    public function agregarItem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Ordenes/index'); exit();
        }
        Auth::require('servicio.diagnosticar');
        $this->validarCsrfOAbort();

        $ordenId = (int) ($_POST['orden_id'] ?? 0);
        $orden   = $this->ordenModel->findById($ordenId);
        if (!$orden->Found || $orden->estaCerrada()) {
            $this->alertaYRedirect('error', 'Error',
                'La orden no existe o está cerrada.', 'Ordenes/index');
        }

        $tipo            = $_POST['tipo'] ?? '';
        $servicioCatId   = (int) ($_POST['servicio_catalogo_id'] ?? 0);
        $descripcion     = htmlspecialchars(strip_tags(trim($_POST['descripcion'] ?? '')));
        $cantidad        = max(1, (int) ($_POST['cantidad'] ?? 1));
        $precioUnit      = max(0, (float) ($_POST['precio_unitario'] ?? 0));
        $diasGarantia    = max(0, (int) ($_POST['dias_garantia'] ?? 30));

        // Si es servicio de catálogo: derivar nombre+precio del catálogo,
        // no confiar en lo que mandó el form.
        if ($tipo === 'servicio_catalogo') {
            if ($servicioCatId <= 0) {
                $this->alertaYRedirect('warning', 'Falta servicio',
                    'Elegí un servicio del catálogo.', 'Ordenes/detalle/' . $ordenId);
            }
            $sc = $this->catalogoModel->findById($servicioCatId);
            if (!$sc->Found || !$sc->isActivo()) {
                $this->alertaYRedirect('error', 'Servicio inválido',
                    'El servicio del catálogo no existe o está inactivo.',
                    'Ordenes/detalle/' . $ordenId);
            }
            $descripcion = $sc->nombre;
            $precioUnit  = (float) $sc->precio_base;
        } else {
            if ($descripcion === '') {
                $this->alertaYRedirect('warning', 'Falta descripción',
                    'Ingresá la descripción del ítem.',
                    'Ordenes/detalle/' . $ordenId);
            }
        }

        $itemId = $this->itemModel->insert([
            'orden_id'             => $ordenId,
            'tipo'                 => $tipo,
            'servicio_catalogo_id' => $tipo === 'servicio_catalogo' ? $servicioCatId : null,
            'descripcion'          => $descripcion,
            'cantidad'             => $cantidad,
            'precio_unitario'      => $precioUnit,
            'aprobado_cliente'     => 0,
            'dias_garantia'        => $diasGarantia,
            'agregado_por'         => Auth::id(),
        ]);

        if ($itemId <= 0) {
            $this->alertaYRedirect('error', 'Error',
                'No se pudo agregar el ítem.', 'Ordenes/detalle/' . $ordenId);
        }

        $this->ordenModel->recalcularTotal($ordenId);

        $_SESSION['alert'] = ['icon'=>'success','title'=>'Ítem agregado',
            'text'=>'El presupuesto se actualizó.'];
        header('Location: ' . APP_URL . 'Ordenes/detalle/' . $ordenId);
        exit();
    }

    // ─────────────────────────────────────────────
    // ELIMINAR ITEM  /Ordenes/eliminarItem  (POST)
    // ─────────────────────────────────────────────
    public function eliminarItem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Ordenes/index'); exit();
        }
        Auth::require('servicio.diagnosticar');
        $this->validarCsrfOAbort();

        $itemId = (int) ($_POST['item_id'] ?? 0);
        $item   = $this->itemModel->findById($itemId);
        if ($item->id === null) {
            $this->alertaYRedirect('error', 'Error',
                'El ítem no existe.', 'Ordenes/index');
        }

        $ordenId = (int) $item->orden_id;
        $orden   = $this->ordenModel->findById($ordenId);
        if ($orden->estaCerrada()) {
            $this->alertaYRedirect('warning', 'Orden cerrada',
                'No se pueden quitar ítems de una orden cerrada.',
                'Ordenes/detalle/' . $ordenId);
        }
        if ($item->isAprobado()) {
            $this->alertaYRedirect('warning', 'Ítem aprobado',
                'No se puede quitar un ítem ya aprobado por el cliente.',
                'Ordenes/detalle/' . $ordenId);
        }

        $this->itemModel->delete($itemId);
        $this->ordenModel->recalcularTotal($ordenId);

        $_SESSION['alert'] = ['icon'=>'success','title'=>'Ítem eliminado','text'=>''];
        header('Location: ' . APP_URL . 'Ordenes/detalle/' . $ordenId);
        exit();
    }

    // ─────────────────────────────────────────────
    // CAMBIAR ESTADO  /Ordenes/cambiarEstado  (POST)
    // ─────────────────────────────────────────────
    public function cambiarEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Ordenes/index'); exit();
        }
        $this->validarCsrfOAbort();

        $ordenId = (int) ($_POST['orden_id'] ?? 0);
        $nuevo   = trim($_POST['estado'] ?? '');
        $motivo  = htmlspecialchars(strip_tags(trim($_POST['motivo'] ?? '')));

        // Permiso fino según destino: Cancelado → servicio.aprobar.
        // Entregado → servicio.entregar. Resto → servicio.diagnosticar.
        $permiso = match ($nuevo) {
            'Cancelado' => 'servicio.aprobar',
            'Entregado' => 'servicio.entregar',
            default     => 'servicio.diagnosticar',
        };
        Auth::require($permiso);

        $orden = $this->ordenModel->findById($ordenId);
        if (!$orden->Found) {
            $this->alertaYRedirect('error', 'Error',
                'La orden no existe.', 'Ordenes/index');
        }

        $ok = $this->ordenModel->cambiarEstado($ordenId, $nuevo, $motivo ?: null, Auth::id());

        if ($ok) {
            $this->notifModel->insert('servicio',
                'Orden ' . $orden->codigo,
                'Estado: ' . $nuevo . ($motivo ? ' — ' . $motivo : ''),
                'Ordenes/detalle/' . $ordenId);
            $_SESSION['alert'] = ['icon'=>'success','title'=>'Estado actualizado',
                'text'=>'La orden ahora está en "' . $nuevo . '".'];
        } else {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Transición inválida',
                'text'=>'No se puede pasar de "' . $orden->estado . '" a "' . $nuevo . '".'];
        }
        header('Location: ' . APP_URL . 'Ordenes/detalle/' . $ordenId);
        exit();
    }

    // ─────────────────────────────────────────────
    // APROBAR ITEMS  /Ordenes/aprobarItems  (POST)
    // Marca como aprobados por el cliente los ids enviados (bulk).
    // ─────────────────────────────────────────────
    public function aprobarItems(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Ordenes/index'); exit();
        }
        Auth::require('servicio.aprobar');
        $this->validarCsrfOAbort();

        $ordenId = (int) ($_POST['orden_id'] ?? 0);
        $ids     = $_POST['item_ids'] ?? [];

        $orden = $this->ordenModel->findById($ordenId);
        if (!$orden->Found) {
            $this->alertaYRedirect('error', 'Error',
                'La orden no existe.', 'Ordenes/index');
        }

        if (!is_array($ids)) $ids = [];
        $aprobados = 0;
        foreach ($ids as $rawId) {
            $itemId = (int) $rawId;
            if ($itemId <= 0) continue;
            $item = $this->itemModel->findById($itemId);
            // Solo aceptamos items que pertenezcan a esta orden — evita
            // que mandando un id ajeno alguien apruebe un item de otra.
            if ($item->id !== null && (int) $item->orden_id === $ordenId) {
                $this->itemModel->marcarAprobado($itemId, true);
                $aprobados++;
            }
        }

        $_SESSION['alert'] = ['icon'=>'success','title'=>'Items aprobados',
            'text'=>$aprobados . ' ítem' . ($aprobados !== 1 ? 's' : '') . ' marcados como aprobados.'];
        header('Location: ' . APP_URL . 'Ordenes/detalle/' . $ordenId);
        exit();
    }

    // ─────────────────────────────────────────────
    // GUARDAR DIAGNÓSTICO  /Ordenes/guardarDiagnostico  (POST)
    // ─────────────────────────────────────────────
    public function guardarDiagnostico(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Ordenes/index'); exit();
        }
        Auth::require('servicio.diagnosticar');
        $this->validarCsrfOAbort();

        $ordenId    = (int) ($_POST['orden_id'] ?? 0);
        $diag       = htmlspecialchars(strip_tags(trim($_POST['diagnostico'] ?? '')));

        $orden = $this->ordenModel->findById($ordenId);
        if (!$orden->Found || $orden->estaCerrada()) {
            $this->alertaYRedirect('error', 'Error',
                'La orden no existe o está cerrada.', 'Ordenes/index');
        }

        $ok = $this->ordenModel->actualizarDiagnostico($ordenId, $diag);

        $_SESSION['alert'] = $ok
            ? ['icon'=>'success','title'=>'Diagnóstico guardado','text'=>'']
            : ['icon'=>'error','title'=>'Error','text'=>'No se pudo guardar.'];
        header('Location: ' . APP_URL . 'Ordenes/detalle/' . $ordenId);
        exit();
    }

    // ─────────────────────────────────────────────
    // ASIGNAR TÉCNICO  /Ordenes/asignarTecnico  (POST)
    // ─────────────────────────────────────────────
    public function asignarTecnico(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Ordenes/index'); exit();
        }
        Auth::require('servicio.recibir');
        $this->validarCsrfOAbort();

        $ordenId   = (int) ($_POST['orden_id'] ?? 0);
        $tecnicoId = (int) ($_POST['tecnico_id'] ?? 0);

        $orden = $this->ordenModel->findById($ordenId);
        if (!$orden->Found || $orden->estaCerrada()) {
            $this->alertaYRedirect('error', 'Error',
                'La orden no existe o está cerrada.', 'Ordenes/index');
        }

        $this->ordenModel->asignarTecnico($ordenId, $tecnicoId > 0 ? $tecnicoId : null);

        $_SESSION['alert'] = ['icon'=>'success','title'=>'Técnico actualizado','text'=>''];
        header('Location: ' . APP_URL . 'Ordenes/detalle/' . $ordenId);
        exit();
    }

    // ─────────────────────────────────────────────
    // ETAPA 4 — PAGOS
    // ─────────────────────────────────────────────

    /**
     * REGISTRAR PAGO  /Ordenes/registrarPago  (POST)
     * Cobra un pago contra una orden. Valida:
     *   - orden existe y no está cancelada
     *   - monto > 0 y <= saldo actual
     *   - tipo y método pertenecen al ENUM
     * Se vincula a la caja_sesion abierta del usuario (si existe).
     */
    public function registrarPago(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Ordenes/index'); exit();
        }
        Auth::require('servicio.entregar');
        $this->validarCsrfOAbort();

        $ordenId = (int) ($_POST['orden_id'] ?? 0);
        $tipo    = $_POST['tipo']   ?? '';
        $metodo  = $_POST['metodo'] ?? '';
        $monto   = round((float) ($_POST['monto'] ?? 0), 2);

        $orden = $this->ordenModel->findById($ordenId);
        if (!$orden->Found) {
            $this->alertaYRedirect('error', 'Error',
                'La orden no existe.', 'Ordenes/index');
        }
        if ($orden->estado === 'Cancelado') {
            $this->alertaYRedirect('warning', 'Orden cancelada',
                'No se registran pagos en órdenes canceladas.',
                'Ordenes/detalle/' . $ordenId);
        }
        if (!in_array($tipo, ['anticipo','saldo','abono'], true)) {
            $this->alertaYRedirect('warning', 'Tipo inválido',
                'Tipo de pago no reconocido.',
                'Ordenes/detalle/' . $ordenId);
        }
        if (!in_array($metodo, ['Efectivo','Tarjeta','Transferencia'], true)) {
            $this->alertaYRedirect('warning', 'Método inválido',
                'Método de pago no reconocido.',
                'Ordenes/detalle/' . $ordenId);
        }
        if ($monto <= 0) {
            $this->alertaYRedirect('warning', 'Monto inválido',
                'El monto debe ser mayor a cero.',
                'Ordenes/detalle/' . $ordenId);
        }
        if ($monto > (float) $orden->saldo + 0.009) {
            $this->alertaYRedirect('warning', 'Monto excede el saldo',
                'El monto (L. ' . number_format($monto, 2) . ') supera el saldo pendiente (L. '
                    . number_format((float) $orden->saldo, 2) . ').',
                'Ordenes/detalle/' . $ordenId);
        }

        // Vincular con caja abierta del usuario (si tiene una).
        $caja          = $this->cajaModel->getSesionAbierta(Auth::id());
        $cajaSesionId  = $caja['id'] ?? null;
        $reciboNumero  = $this->pagoModel->generarReciboNumero();

        $pagoId = $this->pagoModel->insert([
            'orden_id'       => $ordenId,
            'tipo'           => $tipo,
            'monto'          => $monto,
            'metodo'         => $metodo,
            'caja_sesion_id' => $cajaSesionId,
            'recibo_numero'  => $reciboNumero,
            'user_id'        => Auth::id(),
        ]);

        if ($pagoId <= 0) {
            $this->alertaYRedirect('error', 'Error',
                'No se pudo registrar el pago.',
                'Ordenes/detalle/' . $ordenId);
        }

        $this->ordenModel->recalcularPagado($ordenId);

        $this->notifModel->insert('servicio',
            'Pago registrado · ' . $orden->codigo,
            'L. ' . number_format($monto, 2) . ' (' . $tipo . ' · ' . $metodo . ')',
            'Ordenes/detalle/' . $ordenId);

        $_SESSION['alert'] = ['icon'=>'success','title'=>'Pago registrado',
            'text'=>'Recibo ' . $reciboNumero . ' generado.'];
        header('Location: ' . APP_URL . 'Ordenes/detalle/' . $ordenId);
        exit();
    }

    /**
     * ANULAR PAGO  /Ordenes/anularPago  (POST)
     * Permite revertir un pago cargado por error. Permiso: servicio.aprobar.
     */
    public function anularPago(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Ordenes/index'); exit();
        }
        Auth::require('servicio.aprobar');
        $this->validarCsrfOAbort();

        $pagoId = (int) ($_POST['pago_id'] ?? 0);
        $pago   = $this->pagoModel->findById($pagoId);
        if ($pago->id === null) {
            $this->alertaYRedirect('error', 'Error',
                'El pago no existe.', 'Ordenes/index');
        }

        $ordenId = (int) $pago->orden_id;
        $this->pagoModel->delete($pagoId);
        $this->ordenModel->recalcularPagado($ordenId);

        $_SESSION['alert'] = ['icon'=>'success','title'=>'Pago anulado',
            'text'=>'El recibo ' . ($pago->recibo_numero ?: '#' . $pagoId) . ' fue anulado.'];
        header('Location: ' . APP_URL . 'Ordenes/detalle/' . $ordenId);
        exit();
    }

    // ─────────────────────────────────────────────
    // Helpers internos
    // ─────────────────────────────────────────────

    private function validarCsrfOAbort(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error de seguridad',
                'text'=>'Token inválido.'];
            header('Location: ' . APP_URL . 'Ordenes/index');
            exit();
        }
    }

    private function alertaYRedirect(string $icon, string $title, string $text, string $ruta): void
    {
        $_SESSION['alert'] = ['icon'=>$icon, 'title'=>$title, 'text'=>$text];
        header('Location: ' . APP_URL . $ruta);
        exit();
    }
}
