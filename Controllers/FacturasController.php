<?php

class FacturasController
{
    private VentaModel $ventaModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->ventaModel = new VentaModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Historial de facturas
    // URL: /Facturas/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('facturacion.ver');

        $pageTitle = 'Historial de Facturas';
        $ventas    = $this->ventaModel->findAll();
        $config    = $this->ventaModel->getFacturacionConfig();

        require_once VIEWS_PATH . 'Facturas' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // CONFIG — Configuración de facturación
    // URL: /Facturas/config
    // ─────────────────────────────────────────────
    public function config(): void
    {
        Auth::require('facturacion.configurar');

        $pageTitle = 'Configuración de Facturación';
        $config    = $this->ventaModel->getFacturacionConfig();

        require_once VIEWS_PATH . 'Facturas' . DS . 'Config.php';
    }

    // ─────────────────────────────────────────────
    // SAVE CONFIG — Guardar configuración (POST)
    // URL: /Facturas/saveConfig
    // ─────────────────────────────────────────────
    public function saveConfig(): void
    {
        Auth::require('facturacion.configurar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Facturas/config');
            exit();
        }

        if (!Csrf::validate()) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error de seguridad',
                'text'  => 'Token inválido.',
            ];
            header('Location: ' . APP_URL . 'Facturas/config');
            exit();
        }

        // Sanitizar
        $rtn             = htmlspecialchars(strip_tags(trim($_POST['rtn']              ?? '')));
        $cai             = htmlspecialchars(strip_tags(trim($_POST['cai']              ?? '')));
        $rangoDesde      = htmlspecialchars(strip_tags(trim($_POST['rango_desde']      ?? '')));
        $rangoHasta      = htmlspecialchars(strip_tags(trim($_POST['rango_hasta']      ?? '')));
        $fechaLimite     = htmlspecialchars(strip_tags(trim($_POST['fecha_limite']     ?? '')));
        $establecimiento = htmlspecialchars(strip_tags(trim($_POST['establecimiento']  ?? '')));
        $puntoEmision    = htmlspecialchars(strip_tags(trim($_POST['punto_emision']    ?? '')));
        $nombreFiscal    = htmlspecialchars(strip_tags(trim($_POST['nombre_fiscal']    ?? '')));
        $direccionFiscal = htmlspecialchars(strip_tags(trim($_POST['direccion_fiscal'] ?? '')));
        $correlativo     = (int) ($_POST['correlativo'] ?? 1);

        $ok = $this->ventaModel->updateFacturacionConfig([
            'rtn'              => $rtn             ?: null,
            'cai'              => $cai             ?: null,
            'rango_desde'      => $rangoDesde      ?: null,
            'rango_hasta'      => $rangoHasta      ?: null,
            'fecha_limite'     => $fechaLimite     ?: null,
            'establecimiento'  => $establecimiento ?: null,
            'punto_emision'    => $puntoEmision    ?: null,
            'nombre_fiscal'    => $nombreFiscal    ?: null,
            'direccion_fiscal' => $direccionFiscal ?: null,
            'correlativo'      => $correlativo,
        ]);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito'   : 'Error',
            'text'  => $ok
                ? 'Configuración de facturación actualizada correctamente.'
                : 'Error al guardar la configuración.',
        ];

        header('Location: ' . APP_URL . 'Facturas/config');
        exit();
    }
}