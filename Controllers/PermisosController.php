<?php

class PermisosController
{
    private PermissionModel $permissionModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->permissionModel = new PermissionModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Listado de permisos agrupados por módulo
    // URL: /Permisos/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('roles.ver');

        $pageTitle = 'Permisos del Sistema';
        $permisos  = $this->permissionModel->findAll();

        // Agrupar por módulo
        $permisosAgrupados = [];
        foreach ($permisos as $permiso) {
            $permisosAgrupados[$permiso->modulo][] = $permiso;
        }
        ksort($permisosAgrupados);

        require_once VIEWS_PATH . 'Permisos' . DS . 'index.php';
    }
}