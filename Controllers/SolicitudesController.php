<?php

/**
 * SolicitudesController — Bandeja de solicitudes online de servicio técnico.
 *
 * Flujo del módulo:
 *   1. El cliente abre `Tienda/solicitarServicio` y manda el formulario.
 *   2. Llega acá como una fila en `solicitudes_servicio` (estado Pendiente)
 *      y dispara una notificación al admin.
 *   3. Un empleado entra a `Solicitudes/index`, ve la lista, y para cada
 *      pendiente decide:
 *        - Atender → crea una `orden_servicio` real con los datos
 *                    prellenados; la solicitud queda `Atendida` con FK a
 *                    la orden.
 *        - Rechazar → cambia el estado a `Rechazada` con motivo.
 *
 * MVC estricto: el SQL está en SolicitudServicioModel y OrdenServicioModel.
 * Permisos: servicio.solicitudes (ver y gestionar la bandeja).
 */
class SolicitudesController
{
    private SolicitudServicioModel $solicitudModel;
    private OrdenServicioModel     $ordenModel;
    private ClienteModel           $clienteModel;
    private UserModel              $userModel;
    private NotificacionModel      $notifModel;

    public function __construct()
    {
        Auth::check();
        $this->solicitudModel = new SolicitudServicioModel();
        $this->ordenModel     = new OrdenServicioModel();
        $this->clienteModel   = new ClienteModel();
        $this->userModel      = new UserModel();
        $this->notifModel     = new NotificacionModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Bandeja de solicitudes  /Solicitudes/index
    // El filtro por estado se aplica en JS sobre el mismo dataset.
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('servicio.solicitudes');

        $pageTitle    = 'Solicitudes de Servicio';
        $solicitudes  = $this->solicitudModel->findAll();
        $pendientes   = $this->solicitudModel->contarPendientes();

        require_once VIEWS_PATH . 'Solicitudes' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // ATENDER — Form pre-llenado para crear la orden  /Solicitudes/atender/{id}
    // ─────────────────────────────────────────────
    public function atender(string $id = ''): void
    {
        Auth::require('servicio.solicitudes');

        if (!is_numeric($id) || (int) $id <= 0) {
            $this->redirectIndexConAlerta('error', 'Error', 'Solicitud inválida.');
        }

        $solicitud = $this->solicitudModel->findById((int) $id);

        if (!$solicitud->Found) {
            $this->redirectIndexConAlerta('error', 'Error', 'La solicitud no existe.');
        }
        if (!$solicitud->isPendiente()) {
            $this->redirectIndexConAlerta(
                'warning', 'Ya procesada',
                'Esta solicitud ya fue ' . strtolower($solicitud->estado) . '.'
            );
        }

        $pageTitle = 'Atender solicitud #' . $solicitud->id;
        $usuarios  = $this->userModel->findAll();   // para elegir técnico

        require_once VIEWS_PATH . 'Solicitudes' . DS . 'Atender.php';
    }

    // ─────────────────────────────────────────────
    // ATENDER SAVE  /Solicitudes/atenderSave (POST)
    // Crea orden_servicio + marca la solicitud como Atendida.
    // Transacción lógica: si la orden falla, no se toca la solicitud.
    // ─────────────────────────────────────────────
    public function atenderSave(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Solicitudes/index');
            exit();
        }

        Auth::require('servicio.solicitudes');

        if (!Csrf::validate()) {
            $this->redirectIndexConAlerta('error', 'Error de seguridad', 'Token inválido.');
        }

        $solicitudId = (int) ($_POST['solicitud_id'] ?? 0);
        $solicitud   = $this->solicitudModel->findById($solicitudId);

        if (!$solicitud->Found || !$solicitud->isPendiente()) {
            $this->redirectIndexConAlerta(
                'error', 'Error',
                'La solicitud no existe o ya fue procesada.'
            );
        }

        // Sanitización de campos de la orden.
        $tecnicoId     = (int) ($_POST['tecnico_id'] ?? 0);
        $equipo        = htmlspecialchars(strip_tags(trim($_POST['equipo_descripcion'] ?? '')));
        $serial        = htmlspecialchars(strip_tags(trim($_POST['serial'] ?? '')));
        $accesorios    = htmlspecialchars(strip_tags(trim($_POST['accesorios_entregados'] ?? '')));
        $diagnostico   = htmlspecialchars(strip_tags(trim($_POST['diagnostico_inicial'] ?? '')));
        $observaciones = htmlspecialchars(strip_tags(trim($_POST['observaciones'] ?? '')));

        if (empty($equipo)) {
            $_SESSION['alert'] = [
                'icon' => 'warning', 'title' => 'Campos requeridos',
                'text' => 'La descripción del equipo es obligatoria.',
            ];
            header('Location: ' . APP_URL . 'Solicitudes/atender/' . $solicitudId);
            exit();
        }

        $ordenId = $this->ordenModel->insert([
            'codigo'                => $this->ordenModel->generarCodigo(),
            'cliente_id'            => (int) $solicitud->cliente_id,
            'user_recepcion_id'     => Auth::id(),
            'tecnico_id'            => $tecnicoId,
            'equipo_descripcion'    => $equipo,
            'serial'                => $serial,
            'accesorios_entregados' => $accesorios,
            'diagnostico_inicial'   => $diagnostico,
            'observaciones'         => $observaciones,
        ]);

        if ($ordenId <= 0) {
            $this->redirectIndexConAlerta(
                'error', 'Error',
                'No se pudo crear la orden de servicio.'
            );
        }

        // Vincula la solicitud con la orden recién creada.
        $this->solicitudModel->marcarAtendida($solicitudId, $ordenId);

        // Notifica al cliente vía bitácora interna.
        $this->notifModel->insert(
            'servicio',
            'Solicitud atendida',
            'Solicitud #' . $solicitudId . ' convertida en orden.',
            'Ordenes/detalle/' . $ordenId
        );

        // Email al cliente — fail-soft
        $ordenCreada = $this->ordenModel->findById($ordenId);
        ClienteNotificador::notificarSolicitudAtendida(
            (int) $solicitud->cliente_id,
            $solicitud,
            $ordenCreada->codigo ?? ('OS-' . $ordenId)
        );

        $_SESSION['alert'] = [
            'icon'  => 'success',
            'title' => 'Solicitud atendida',
            'text'  => 'Se creó la orden de servicio correctamente.',
        ];
        header('Location: ' . APP_URL . 'Ordenes/detalle/' . $ordenId);
        exit();
    }

    // ─────────────────────────────────────────────
    // RECHAZAR  /Solicitudes/rechazar (POST)
    // ─────────────────────────────────────────────
    public function rechazar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Solicitudes/index');
            exit();
        }

        Auth::require('servicio.solicitudes');

        if (!Csrf::validate()) {
            $this->redirectIndexConAlerta('error', 'Error de seguridad', 'Token inválido.');
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $motivo = htmlspecialchars(strip_tags(trim($_POST['motivo'] ?? '')));

        if ($id <= 0 || empty($motivo)) {
            $this->redirectIndexConAlerta(
                'warning', 'Falta el motivo',
                'Hay que indicar por qué se rechaza la solicitud.'
            );
        }

        $solicitud = $this->solicitudModel->findById($id);
        if (!$solicitud->Found || !$solicitud->isPendiente()) {
            $this->redirectIndexConAlerta(
                'error', 'Error',
                'La solicitud no existe o ya fue procesada.'
            );
        }

        $ok = $this->solicitudModel->rechazar($id, $motivo);

        if ($ok) {
            // Email al cliente — fail-soft. Usamos la solicitud refrescada
            // para que el motivo de rechazo viaje al template.
            $solicitudActualizada = $this->solicitudModel->findById($id);
            ClienteNotificador::notificarSolicitudRechazada(
                (int) $solicitud->cliente_id,
                $solicitudActualizada
            );
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Solicitud rechazada' : 'Error',
            'text'  => $ok
                ? 'La solicitud fue marcada como rechazada.'
                : 'No se pudo rechazar la solicitud.',
        ];
        header('Location: ' . APP_URL . 'Solicitudes/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // Helpers internos
    // ─────────────────────────────────────────────

    private function redirectIndexConAlerta(string $icon, string $title, string $text): void
    {
        $_SESSION['alert'] = ['icon' => $icon, 'title' => $title, 'text' => $text];
        header('Location: ' . APP_URL . 'Solicitudes/index');
        exit();
    }
}
