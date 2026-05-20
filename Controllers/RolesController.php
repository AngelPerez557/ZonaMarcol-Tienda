<?php

class RolesController
{
    private RoleModel       $roleModel;
    private PermissionModel $permissionModel;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // ─────────────────────────────────────────────
    public function __construct()
    {
        Auth::check();
        $this->roleModel       = new RoleModel();
        $this->permissionModel = new PermissionModel();
    }

    // ─────────────────────────────────────────────
    // INDEX — Listado de roles
    // URL: /Roles/index
    // ─────────────────────────────────────────────
    public function index(): void
    {
        Auth::require('roles.ver');

        $pageTitle = 'Roles';
        $roles     = $this->roleModel->findAll();

        require_once VIEWS_PATH . 'Roles' . DS . 'index.php';
    }

    // ─────────────────────────────────────────────
    // REGISTRY — Crear o editar rol
    // URL: /Roles/registry      → crear
    // URL: /Roles/registry/{id} → editar + asignar permisos
    // ─────────────────────────────────────────────
    public function registry(string $id = ''): void
    {
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require($esEdicion ? 'roles.editar' : 'roles.crear');

        $pageTitle = $esEdicion ? 'Editar Rol' : 'Nuevo Rol';
        $rol       = $esEdicion
            ? $this->roleModel->getById((int) $id)
            : new RoleEntity();

        if ($esEdicion && !$rol->Found) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'El rol no existe.',
            ];
            header('Location: ' . APP_URL . 'Roles/index');
            exit();
        }

        // Todos los permisos agrupados por módulo
        $todosPermisos    = $this->permissionModel->findAll();
        $permisosAgrupados = [];
        foreach ($todosPermisos as $permiso) {
            $permisosAgrupados[$permiso->modulo][] = $permiso;
        }
        ksort($permisosAgrupados);

        // Permisos ya asignados al rol
        $permisosAsignados = $esEdicion
            ? array_map(
                fn($p) => $p->id,
                $this->roleModel->getPermissionsFullByRole((int) $id)
              )
            : [];

        require_once VIEWS_PATH . 'Roles' . DS . 'Registry.php';
    }

    // ─────────────────────────────────────────────
    // SAVE — Guardar rol (POST)
    // URL: /Roles/save
    // ─────────────────────────────────────────────
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Roles/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require($esEdicion ? 'roles.editar' : 'roles.crear');

        // Validar CSRF
        if (!Csrf::validate()) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error de seguridad',
                'text'  => 'Token inválido.',
            ];
            header('Location: ' . APP_URL . 'Roles/index');
            exit();
        }

        // Sanitizar
        $nombre      = htmlspecialchars(strip_tags(trim($_POST['nombre']      ?? '')));
        $slug        = strtolower(preg_replace('/[^a-z0-9\-]/', '', trim($_POST['slug'] ?? '')));
        $descripcion = htmlspecialchars(strip_tags(trim($_POST['descripcion'] ?? '')));
        $permisos    = $_POST['permisos'] ?? [];

        // Validaciones
        if (empty($nombre) || empty($slug)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campos requeridos',
                'text'  => 'El nombre y el slug son obligatorios.',
            ];
            $redirect = $esEdicion
                ? APP_URL . 'Roles/registry/' . $id
                : APP_URL . 'Roles/registry';
            header('Location: ' . $redirect);
            exit();
        }

        // Verificar slug duplicado
        if ($this->roleModel->slugExists($slug, $esEdicion ? $id : 0)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Slug duplicado',
                'text'  => 'Ya existe un rol con ese slug.',
            ];
            $redirect = $esEdicion
                ? APP_URL . 'Roles/registry/' . $id
                : APP_URL . 'Roles/registry';
            header('Location: ' . $redirect);
            exit();
        }

        $data = [
            'nombre'      => $nombre,
            'slug'        => $slug,
            'descripcion' => $descripcion ?: null,
        ];

        if ($esEdicion) {
            $data['id'] = $id;
            $ok = $this->roleModel->update($data);
        } else {
            $nuevoId = $this->roleModel->insert($data);
            $ok      = $nuevoId > 0;
            if ($ok) $id = $nuevoId;
        }

        // Sincronizar permisos
        if ($ok) {
            $permisosIds = array_map('intval', array_filter($permisos));
            $this->roleModel->syncPermissions($id, $permisosIds);
        }

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito'   : 'Error',
            'text'  => $ok
                ? ($esEdicion ? 'Rol actualizado correctamente.' : 'Rol creado correctamente.')
                : 'Error al guardar el rol.',
        ];

        header('Location: ' . APP_URL . 'Roles/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // DELETE — Eliminar rol (POST)
    // URL: /Roles/delete
    // ─────────────────────────────────────────────
    public function delete(): void
    {
        Auth::require('roles.eliminar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        if (!Csrf::validate()) {
            http_response_code(403);
            exit();
        }

        $id = (int) ($_POST['id'] ?? 0);

        // Verificar que no tenga usuarios asignados
        if ($this->roleModel->hasUsers($id)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'No se puede eliminar',
                'text'  => 'Este rol tiene usuarios asignados. Reasígnalos antes de eliminar.',
            ];
            header('Location: ' . APP_URL . 'Roles/index');
            exit();
        }

        $ok = $this->roleModel->delete($id);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok ? 'Rol eliminado correctamente.' : 'Error al eliminar el rol.',
        ];

        header('Location: ' . APP_URL . 'Roles/index');
        exit();
    }
}