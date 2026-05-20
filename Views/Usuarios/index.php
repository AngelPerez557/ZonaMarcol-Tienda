<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-users-cog me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($usuarios) ?> usuario<?= count($usuarios) !== 1 ? 's' : '' ?> registrado<?= count($usuarios) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <?php if (Auth::can('usuarios.crear')): ?>
        <a href="<?= APP_URL ?>Usuarios/registry" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>

    <!-- ─────────────────────────────────────────────
         FILTROS
         ───────────────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0"
                               id="buscarUsuario"
                               placeholder="Buscar por nombre o correo...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" id="filtroRol">
                        <option value="">Todos los roles</option>
                        <?php
                        $roles = array_unique(array_map(fn($u) => $u->rol_nombre, $usuarios));
                        sort($roles);
                        foreach ($roles as $rol):
                        ?>
                        <option value="<?= htmlspecialchars($rol) ?>">
                            <?= htmlspecialchars($rol) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 text-end">
                    <small class="text-muted" id="contadorVisible">
                        Mostrando <?= count($usuarios) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         TABLA
         ───────────────────────────────────────────── -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">Usuario</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No hay usuarios registrados.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr class="usuario-row"
                            data-nombre="<?= strtolower(htmlspecialchars($usuario->nombre)) ?>"
                            data-email="<?= strtolower(htmlspecialchars($usuario->email ?? '')) ?>"
                            data-rol="<?= htmlspecialchars($usuario->rol_nombre ?? '') ?>"
                            data-activo="<?= $usuario->activo ?>">

                            <!-- Avatar + nombre -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="
                                        width:40px; height:40px; flex-shrink:0;
                                        border-radius:50%;
                                        background-image: url('<?= $usuario->getFotoUrl() ?>');
                                        background-size: cover;
                                        background-position: center;
                                        background-color: rgba(245,168,0,0.12);
                                        border: 2px solid rgba(245,168,0,0.3);">
                                    </div>
                                    <div>
                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($usuario->nombre) ?>
                                            <?php if ($usuario->id === Auth::id()): ?>
                                            <span class="badge ms-1" style="background:#F5A800; font-size:0.65rem;">Tú</span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            Desde <?= date('d/m/Y', strtotime($usuario->created_at)) ?>
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td class="text-muted">
                                <?= htmlspecialchars($usuario->email) ?>
                            </td>

                            <td class="text-muted">
                                <?= $usuario->telefono
                                    ? htmlspecialchars($usuario->telefono)
                                    : '<em>Sin teléfono</em>' ?>
                            </td>

                            <td>
                                <span class="badge" style="background:rgba(245,168,0,0.15); color:#8C6300;">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    <?= htmlspecialchars($usuario->getNombreRol()) ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <?php if (Auth::can('usuarios.editar') && $usuario->id !== Auth::id()): ?>
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-activo"
                                           type="checkbox"
                                           role="switch"
                                           id="toggle-<?= $usuario->id ?>"
                                           data-id="<?= $usuario->id ?>"
                                           data-url="<?= APP_URL ?>Usuarios/toggle"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $usuario->isActivo() ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="toggle-<?= $usuario->id ?>"></label>
                                </div>
                                <?php else: ?>
                                <span class="badge <?= $usuario->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $usuario->isActivo() ? 'Activo' : 'Inactivo' ?>
                                </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <?php if (Auth::can('usuarios.editar')): ?>
                                    <a href="<?= APP_URL ?>Usuarios/registry/<?= $usuario->id ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>

                                    <?php if (Auth::can('usuarios.eliminar') && $usuario->id !== Auth::id()): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="<?= $usuario->id ?>"
                                            data-nombre="<?= htmlspecialchars($usuario->nombre) ?>"
                                            data-url="<?= APP_URL ?>Usuarios/delete"
                                            data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const buscar    = document.getElementById('buscarUsuario');
    const filtroRol = document.getElementById('filtroRol');
    const filtroEst = document.getElementById('filtroEstado');
    const contador  = document.getElementById('contadorVisible');
    const filas     = document.querySelectorAll('.usuario-row');

    function filtrar() {
        const texto  = buscar.value.toLowerCase();
        const rol    = filtroRol.value;
        const estado = filtroEst.value;
        let visible  = 0;

        filas.forEach(fila => {
            const nombre = fila.dataset.nombre || '';
            const email  = fila.dataset.email  || '';
            const rolFil = fila.dataset.rol    || '';
            const activo = fila.dataset.activo;

            const okTexto  = nombre.includes(texto) || email.includes(texto);
            const okRol    = !rol    || rolFil === rol;
            const okEstado = !estado || activo === estado;

            if (okTexto && okRol && okEstado) {
                fila.style.display = '';
                visible++;
            } else {
                fila.style.display = 'none';
            }
        });

        contador.textContent = `Mostrando ${visible}`;
    }

    buscar.addEventListener('input', filtrar);
    filtroRol.addEventListener('change', filtrar);
    filtroEst.addEventListener('change', filtrar);

    // Toggle activo
    document.querySelectorAll('input.toggle-activo[type="checkbox"]').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();
            const id     = this.dataset.id;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;
            const activo = this.checked ? 1 : 0;
            const self   = this;
            const fila   = this.closest('.usuario-row');

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    fila.dataset.activo = activo;
                    Swal.mixin({
                        toast: true, position: 'top-end',
                        showConfirmButton: false, timer: 2000
                    }).fire({
                        icon: 'success',
                        title: activo ? 'Usuario activado' : 'Usuario desactivado'
                    });
                } else {
                    self.checked = !self.checked;
                    Swal.fire({
                        icon: 'warning', title: 'No permitido',
                        text: data.message ?? 'No se pudo cambiar el estado.',
                        confirmButtonColor: '#F5A800'
                    });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // Eliminar
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const nombre = this.dataset.nombre;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar usuario?',
                text: `"${nombre}" será eliminado permanentemente.`,
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="id" value="${id}">
                        <input type="hidden" name="csrf_token" value="${csrf}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

});
</script>