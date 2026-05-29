<?php

/**
 * ReportesController.php — Vistas de reportes (ventas, pedidos, inventario)
 *
 * F-20 / F-26 — Antes este controller tenía SQL directo y un helper
 * privado callSP() que duplicaba lógica del BaseModel. Ahora toda la
 * BD pasa por ReporteModel — el Controller solo orquesta datos y vista.
 */
class ReportesController
{
    private ReporteModel         $reporteModel;
    private ReporteServicioModel $reporteServicioModel;

    public function __construct()
    {
        Auth::check();
        $this->reporteModel         = new ReporteModel();
        $this->reporteServicioModel = new ReporteServicioModel();
    }

    // ─────────────────────────────────────────────
    // VENTAS
    // URL: /Reportes/ventas
    // ─────────────────────────────────────────────
    public function ventas(): void
    {
        Auth::require('reportes.ver');

        $pageTitle       = 'Reporte de Ventas';
        $resumen         = $this->reporteModel->resumenVentas();
        $ventasPorDia    = $this->reporteModel->ventasPorDia();
        $ventasPorMes    = $this->reporteModel->ventasPorMes();
        $ventasPorMetodo = $this->reporteModel->ventasPorMetodo();
        $topProductos    = $this->reporteModel->topProductos();

        require_once VIEWS_PATH . 'Reportes' . DS . 'Ventas.php';
    }

    // ─────────────────────────────────────────────
    // PEDIDOS
    // URL: /Reportes/pedidos
    // ─────────────────────────────────────────────
    public function pedidos(): void
    {
        Auth::require('reportes.ver');

        $pageTitle        = 'Reporte de Pedidos';
        $resumen          = $this->reporteModel->resumenPedidos();
        $pedidosPorEstado = $this->reporteModel->pedidosPorEstado();
        $pedidosPorDia    = $this->reporteModel->pedidosPorDia();

        require_once VIEWS_PATH . 'Reportes' . DS . 'Pedidos.php';
    }

    // ─────────────────────────────────────────────
    // INVENTARIO
    // URL: /Reportes/inventario
    // ─────────────────────────────────────────────
    public function inventario(): void
    {
        Auth::require('reportes.ver');

        $pageTitle          = 'Reporte de Inventario';
        $limite             = (int) ($_GET['limite'] ?? 5);

        $resumen            = $this->reporteModel->resumenInventario();
        $stockBajo          = $this->reporteModel->stockBajo($limite);
        $variantesStockBajo = $this->reporteModel->variantesStockBajo($limite);

        require_once VIEWS_PATH . 'Reportes' . DS . 'Inventario.php';
    }

    // ─────────────────────────────────────────────
    // SERVICIO TÉCNICO
    // URL: /Reportes/servicio
    // ─────────────────────────────────────────────
    public function servicio(): void
    {
        Auth::require('reportes.ver');

        $pageTitle           = 'Reporte de Servicio Técnico';
        $resumen             = $this->reporteServicioModel->resumen();
        $ingresosPorMes      = $this->reporteServicioModel->ingresosPorMes(12);
        $ordenesPorEstado    = $this->reporteServicioModel->ordenesPorEstado();
        $garantiasVigentes   = $this->reporteServicioModel->garantiasVigentes(20);
        $totalGarantias      = $this->reporteServicioModel->contarGarantiasVigentes();
        $tecnicosProductivos = $this->reporteServicioModel->tecnicosProductivos(10);
        $tiempoPromedio      = $this->reporteServicioModel->tiempoPromedio();

        require_once VIEWS_PATH . 'Reportes' . DS . 'Servicio.php';
    }
}
