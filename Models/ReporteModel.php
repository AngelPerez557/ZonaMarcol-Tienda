<?php

/**
 * ReporteModel.php — Reportes agregados (ventas, pedidos, inventario)
 *
 * F-20 — Antes vivía como SQL directo + helper callSP() privado en
 * ReportesController. Movido a Model para respetar MVC estricto:
 *   - Controller NO toca BD
 *   - Todas las consultas pasan por SPs vía BaseModel
 *
 * Esta clase no representa una tabla concreta; agrupa SPs que
 * cruzan varias tablas para generar reportes. Por eso $table queda
 * vacío y los SPs se invocan por nombre completo (no usan $spPrefix).
 */
class ReporteModel extends BaseModel
{
    // ReporteModel no mapea a una tabla concreta — agrupa SPs de varias tablas
    protected string $table = '';

    // ─────────────────────────────────────────────
    // VENTAS
    // ─────────────────────────────────────────────

    // Tarjetas de KPI del reporte de ventas (totales, promedio, etc.)
    public function resumenVentas(): ?array
    {
        return $this->callSPSingle('sp_reportes_resumenVentas');
    }

    // Serie de ventas agrupada por día — para gráfica de tendencia
    public function ventasPorDia(): array
    {
        return $this->callSP('sp_reportes_ventasPorDia');
    }

    // Serie de ventas agrupada por mes — para comparativo histórico
    public function ventasPorMes(): array
    {
        return $this->callSP('sp_reportes_ventasPorMes');
    }

    // Distribución de ventas por método de pago — para gráfica de dona
    public function ventasPorMetodo(): array
    {
        return $this->callSP('sp_reportes_ventasPorMetodo');
    }

    // Top productos más vendidos — para tabla del reporte
    public function topProductos(): array
    {
        return $this->callSP('sp_reportes_topProductos');
    }

    // ─────────────────────────────────────────────
    // PEDIDOS
    // ─────────────────────────────────────────────

    public function resumenPedidos(): ?array
    {
        return $this->callSPSingle('sp_reportes_resumenPedidos');
    }

    public function pedidosPorEstado(): array
    {
        return $this->callSP('sp_reportes_pedidosPorEstado');
    }

    public function pedidosPorDia(): array
    {
        return $this->callSP('sp_reportes_pedidosPorDia');
    }

    // ─────────────────────────────────────────────
    // INVENTARIO
    // ─────────────────────────────────────────────

    public function resumenInventario(): ?array
    {
        return $this->callSPSingle('sp_reportes_resumenInventario');
    }

    // Productos con stock por debajo del umbral $limite
    public function stockBajo(int $limite = 5): array
    {
        return $this->callSP('sp_reportes_stockBajo', [$limite]);
    }

    // Variantes con stock por debajo del umbral $limite
    public function variantesStockBajo(int $limite = 5): array
    {
        return $this->callSP('sp_reportes_variantesStockBajo', [$limite]);
    }

    // Reporte v2 — Catálogo completo con stock, precio, valor inventario y estado
    // por producto. Usado por la exportación completa de inventario.
    public function inventarioCompleto(): array
    {
        return $this->callSP('sp_reportes_inventarioCompleto');
    }

    // Reporte v2 — Totalizado por categoría (cantidad, stock total, valor).
    public function inventarioPorCategoria(): array
    {
        return $this->callSP('sp_reportes_inventarioPorCategoria');
    }
}
