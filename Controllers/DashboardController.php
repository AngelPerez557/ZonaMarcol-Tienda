<?php

class DashboardController
{
    public function __construct()
    {
        Auth::check();
    }

    public function index(): void
    {
        $pageTitle = 'Dashboard';

        // ── Instanciar todos los modelos ──────────────
        $userModel       = new UserModel();
        $roleModel       = new RoleModel();
        $permissionModel = new PermissionModel();
        $productoModel   = new ProductoModel();
        $pedidoModel     = new PedidoModel();
        $clienteModel    = new ClienteModel();

        // ── Consultar datos ───────────────────────────
        $totalUsuarios = $userModel->count();
        $totalActivos  = $userModel->countActivos();
        $totalRoles    = $roleModel->count();
        $totalPermisos = $permissionModel->count();

        $totalProductos         = $productoModel->count();
        $totalProductosActivos  = $productoModel->count();
        $totalPedidosPendientes = $pedidoModel->countByEstado('Pendiente');
        $totalPedidosHoy        = $pedidoModel->countHoy();
        $totalClientes          = $clienteModel->count();

        $usuario = Auth::user();

        require_once VIEWS_PATH . 'Dashboard' . DS . 'index.php';
    }
}