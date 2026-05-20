<?php

class PedidoEntity extends BaseEntity
{
    // ─────────────────────────────────────────────
    // PROPIEDADES — idénticas a columnas de la BD
    // ─────────────────────────────────────────────
    public ?int    $id              = null;
    public ?string $codigo          = null;
    public ?int    $cliente_id      = null;
    public ?string $wa_numero       = null;
    public ?string $tipo_entrega    = null;
    public ?string $direccion_envio = null;
    public ?int    $zona_id         = null;
    public ?string $estado          = 'Pendiente';
    public ?int    $pagado          = 0;
    public ?string $metodo_pago     = 'Transferencia'; 
    public ?float  $subtotal        = 0;
    public ?float  $costo_envio     = 0;
    public ?float  $total           = 0;
    public ?string $nota            = null;
    public ?string $created_at      = null;
    public ?string $updated_at      = null;

    // Campos adicionales del JOIN
    public ?string $cliente_nombre   = null;
    public ?string $cliente_telefono = null;

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    // Retorna el número de pedido formateado
    // Ej: #000123
    public function getCodigoFormateado(): string
    {
        return '#' . str_pad($this->id ?? 0, 6, '0', STR_PAD_LEFT);
    }

    // Retorna el total formateado
    public function getTotalFormateado(): string
    {
        return 'L. ' . number_format((float)$this->total, 2);
    }

    // Retorna true si es envío a domicilio
    public function esEnvio(): bool
    {
        return $this->tipo_entrega === 'Envio';
    }

    // Retorna true si es retiro en tienda
    public function esRetiro(): bool
    {
        return $this->tipo_entrega === 'Retiro';
    }

    // Retorna el badge CSS según el estado
    public function getBadgeEstado(): string
    {
        return match($this->estado) {
            'Pendiente'       => 'bg-warning text-dark',
            'Pagado'          => 'bg-success',    
            'En preparacion'  => 'bg-info text-dark',
            'Listo'           => 'bg-primary',
            'En camino'       => 'bg-purple text-white',
            'Entregado'       => 'bg-success',
            'Cancelado'       => 'bg-danger',
            default           => 'bg-secondary',
        };
    }

    // Retorna el ícono según el estado
    public function getIconoEstado(): string
    {
        return match($this->estado) {
            'Pendiente'       => 'fas fa-clock',
            'Pagado'          => 'fas fa-money-bill-wave',
            'En preparacion'  => 'fas fa-box-open',
            'Listo'           => 'fas fa-check-circle',
            'En camino'       => 'fas fa-truck',
            'Entregado'       => 'fas fa-check-double',
            'Cancelado'       => 'fas fa-times-circle',
            default           => 'fas fa-question-circle',
        };
    }

    // Retorna los estados siguientes posibles según el estado actual
    public function getEstadosSiguientes(): array
    {
        return match($this->estado) {
            'Pendiente'      => ['En preparacion'],
            'En preparacion' => ['Listo'],
            'Listo'          => $this->esEnvio() ? ['En camino'] : ['Entregado'],
            'En camino'      => ['Entregado'],
            'Pagado'         => ['En preparacion'],
            default          => [],
        };
    }

    // Retorna el mensaje de WhatsApp según el estado
    public function getMensajeWhatsApp(array $detalle = []): string
    {
        $nombre  = $this->cliente_nombre ?? 'Cliente';
        $codigo  = $this->getCodigoFormateado();
        $total   = $this->getTotalFormateado();

        $lineasProductos = '';
        foreach ($detalle as $item) {
            $lineasProductos .= "• {$item['nombre_producto']} x{$item['cantidad']} — L. " .
                                number_format((float)$item['subtotal'], 2) . "\n";
        }

        return match($this->estado) {
            'En preparacion' => "Hola {$nombre} 👋\nEstamos preparando tu pedido {$codigo} de Zona Marcol.\nTe avisaremos cuando esté listo. ⏳",
            'Listo'          => $this->esRetiro()
                ? "Hola {$nombre} 👋\nTu pedido {$codigo} está *listo para retirar* 🎉\n\n📦 Productos:\n{$lineasProductos}\n💰 Total: {$total}\n\n¡Te esperamos! 😊\nBarrio Abajo, Avenida La Libertad"
                : "Hola {$nombre} 👋\nTu pedido {$codigo} está listo y pronto saldrá a entrega. 📦",
            'En camino'      => "Hola {$nombre} 👋\nTu pedido {$codigo} ya va en camino 🛵\n\n💰 Total: {$total}\n\n¡Prepárate para recibirlo! 😊",
            'Entregado'      => "Hola {$nombre} 👋\nTu pedido {$codigo} fue entregado exitosamente ✅\n\n¡Gracias por tu compra en Zona Marcol! ❤️\nEsperamos verte pronto 💄",
            'Cancelado'      => "Hola {$nombre} 👋\nLamentamos informarte que tu pedido {$codigo} fue cancelado.\nPuedes contactarnos al 9987-3125 para más información.",
            default          => "Hola {$nombre}, actualización de tu pedido {$codigo} en Zona Marcol.",
        };
    }

    // Retorna el número de WhatsApp limpio para el enlace wa.me
    public function getWhatsAppUrl(array $detalle = []): string
    {
        $numero  = preg_replace('/[^0-9]/', '', $this->wa_numero ?? $this->cliente_telefono ?? '');
        $mensaje = $this->getMensajeWhatsApp($detalle);
        return "https://wa.me/504{$numero}?text=" . urlencode($mensaje);
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // ─────────────────────────────────────────────
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->tipo_entrega)) {
            $this->addError('El tipo de entrega es obligatorio.');
        }

        if ($this->esEnvio() && empty($this->direccion_envio)) {
            $this->addError('La dirección de envío es obligatoria para pedidos a domicilio.');
        }

        return !$this->hasErrors();
    }
}