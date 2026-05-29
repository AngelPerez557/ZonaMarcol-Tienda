<?php

/**
 * ClienteNotificador.php — Envía notificaciones por email al cliente final
 * cuando ocurren eventos relevantes (cambios de estado de orden, pago
 * confirmado, solicitud atendida/rechazada).
 *
 * Tres razones para tenerlo en un helper aparte:
 *   1. Evita que cada controller repita el JOIN cliente + render template
 *      + Mailer::send.
 *   2. Es el único lugar donde el "qué mensaje mandar para qué estado"
 *      vive — cambiar el texto no requiere tocar controllers.
 *   3. Política fail-soft centralizada: cualquier error se loguea y el
 *      flujo del controller sigue (el estado ya cambió en BD).
 *
 * Carga lazy del ClienteModel para no instanciar PDO si nadie llama.
 */
class ClienteNotificador
{
    /** Textos por estado de orden de servicio. */
    private const TEXTOS_ORDEN = [
        'Diagnostico' => [
            'titulo'  => 'Estamos diagnosticando tu equipo',
            'mensaje' => 'Nuestro técnico ya empezó a revisar tu equipo. Te avisaremos cuando tengamos el presupuesto listo.',
        ],
        'Esperando aprobacion' => [
            'titulo'  => 'Tu presupuesto está listo',
            'mensaje' => 'Ya cargamos el presupuesto de reparación. Ingresá a tu cuenta para revisarlo y aprobarlo.',
        ],
        'En reparacion' => [
            'titulo'  => 'Aprobaste el presupuesto',
            'mensaje' => 'Comenzamos la reparación de tu equipo. Te avisaremos cuando esté listo para retirar.',
        ],
        'Listo' => [
            'titulo'  => 'Tu equipo está listo para retirar',
            'mensaje' => 'Terminamos la reparación. Podés pasar a retirar tu equipo en el horario de atención. Recordá saldar el monto pendiente antes de la entrega.',
        ],
        'Entregado' => [
            'titulo'  => 'Equipo entregado',
            'mensaje' => 'Confirmamos la entrega de tu equipo. Gracias por confiar en nosotros. Si tenés algún problema, escribinos referenciando el código de orden.',
        ],
        'Cancelado' => [
            'titulo'  => 'Tu orden fue cancelada',
            'mensaje' => 'La orden fue cancelada. Si tenés dudas sobre el motivo, contactanos directamente.',
        ],
    ];

    // ─────────────────────────────────────────────
    // ÓRDENES DE SERVICIO
    // ─────────────────────────────────────────────

    /**
     * Notifica al cliente sobre el cambio de estado de su orden de servicio.
     * No notifica para estados internos sin valor de comunicación (Recibido —
     * el cliente ya estaba presente cuando se abrió la orden).
     */
    public static function notificarCambioEstadoOrden(int $clienteId, OrdenServicioEntity $orden): void
    {
        if (!isset(self::TEXTOS_ORDEN[$orden->estado])) {
            return;   // estado sin notificación (ej: Recibido)
        }

        $cliente = self::getCliente($clienteId);
        if (!$cliente || empty($cliente->email)) {
            return;   // sin email no podemos notificar
        }

        $txt = self::TEXTOS_ORDEN[$orden->estado];

        $detalleExtra = '';
        if ($orden->estado === 'Listo' && (float) $orden->saldo > 0.009) {
            $detalleExtra = '<div style="font-size:13px;color:#dc3545;margin-top:6px;">'
                . 'Saldo pendiente: <strong>L. ' . number_format((float) $orden->saldo, 2) . '</strong>'
                . '</div>';
        } elseif ($orden->estado === 'Cancelado' && !empty($orden->motivo_cancelacion)) {
            $detalleExtra = '<div style="font-size:13px;color:#888;margin-top:6px;">'
                . 'Motivo: ' . htmlspecialchars($orden->motivo_cancelacion) . '</div>';
        }

        $body = Mailer::renderTemplate('orden_estado', [
            'titulo'        => $txt['titulo'],
            'mensaje'       => $txt['mensaje'],
            'codigo'        => htmlspecialchars($orden->codigo ?? '—'),
            'estado'        => htmlspecialchars($orden->getEstadoLabel()),
            'cliente'       => htmlspecialchars($cliente->nombre ?? ''),
            'detalle_extra' => $detalleExtra,
        ]);

        Mailer::send(
            $cliente->email,
            $txt['titulo'] . ' · ' . ($orden->codigo ?? ''),
            $body,
            $cliente->nombre ?? null
        );
    }

    // ─────────────────────────────────────────────
    // PEDIDOS CAMISETA
    // ─────────────────────────────────────────────

    /**
     * Notifica al cliente que su comprobante fue validado y el pedido
     * pasó a Confirmado.
     */
    public static function notificarPagoConfirmadoCamiseta(
        int $clienteId,
        PedidoCamisetaEntity $pedido,
        float $montoConfirmado
    ): void {
        $cliente = self::getCliente($clienteId);
        if (!$cliente || empty($cliente->email)) {
            return;
        }

        $body = Mailer::renderTemplate('orden_estado', [
            'titulo'  => 'Recibimos tu pago',
            'mensaje' => 'Validamos tu comprobante de transferencia. Tu pedido pasó a Confirmado y empezamos el proceso con el proveedor.',
            'codigo'  => htmlspecialchars($pedido->codigo ?? '—'),
            'estado'  => htmlspecialchars($pedido->getEstadoLabel()),
            'cliente' => htmlspecialchars($cliente->nombre ?? ''),
            'detalle_extra' => '<div style="font-size:13px;color:#28a745;margin-top:6px;">'
                . 'Monto registrado: <strong>L. ' . number_format($montoConfirmado, 2) . '</strong></div>',
        ]);

        Mailer::send(
            $cliente->email,
            'Pago confirmado · ' . ($pedido->codigo ?? ''),
            $body,
            $cliente->nombre ?? null
        );
    }

    // ─────────────────────────────────────────────
    // SOLICITUDES
    // ─────────────────────────────────────────────

    public static function notificarSolicitudAtendida(
        int $clienteId,
        SolicitudServicioEntity $solicitud,
        string $codigoOrden
    ): void {
        $cliente = self::getCliente($clienteId);
        if (!$cliente || empty($cliente->email)) {
            return;
        }

        $body = Mailer::renderTemplate('orden_estado', [
            'titulo'  => 'Atendimos tu solicitud',
            'mensaje' => 'Convertimos tu solicitud en una orden de servicio. Te contactaremos para coordinar la recepción del equipo.',
            'codigo'  => htmlspecialchars($codigoOrden),
            'estado'  => 'Recibida',
            'cliente' => htmlspecialchars($cliente->nombre ?? ''),
            'detalle_extra' => '',
        ]);

        Mailer::send(
            $cliente->email,
            'Solicitud atendida · ' . $codigoOrden,
            $body,
            $cliente->nombre ?? null
        );
    }

    public static function notificarSolicitudRechazada(
        int $clienteId,
        SolicitudServicioEntity $solicitud
    ): void {
        $cliente = self::getCliente($clienteId);
        if (!$cliente || empty($cliente->email)) {
            return;
        }

        $body = Mailer::renderTemplate('orden_estado', [
            'titulo'  => 'Tu solicitud no procede',
            'mensaje' => 'Lamentamos no poder atender tu solicitud en este momento.',
            'codigo'  => 'Solicitud #' . (int) $solicitud->id,
            'estado'  => 'Rechazada',
            'cliente' => htmlspecialchars($cliente->nombre ?? ''),
            'detalle_extra' => $solicitud->motivo_rechazo
                ? '<div style="font-size:13px;color:#888;margin-top:6px;">'
                  . 'Motivo: ' . htmlspecialchars($solicitud->motivo_rechazo) . '</div>'
                : '',
        ]);

        Mailer::send(
            $cliente->email,
            'Solicitud rechazada',
            $body,
            $cliente->nombre ?? null
        );
    }

    // ─────────────────────────────────────────────
    // Helpers internos
    // ─────────────────────────────────────────────

    /**
     * Lookup del cliente con manejo de errores silencioso. Devuelve la
     * entidad si existe y tiene Found=true; null si no.
     */
    private static function getCliente(int $clienteId): ?ClienteEntity
    {
        if ($clienteId <= 0) return null;
        try {
            $model = new ClienteModel();
            $cli   = $model->findById($clienteId);
            return $cli->Found ? $cli : null;
        } catch (\Throwable $e) {
            error_log('[ClienteNotificador::getCliente] ' . $e->getMessage());
            return null;
        }
    }
}
