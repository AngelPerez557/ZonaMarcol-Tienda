<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-key me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= array_sum(array_map('count', $permisosAgrupados)) ?> permisos en
                <?= count($permisosAgrupados) ?> módulos
            </small>
        </div>
        <a href="<?= APP_URL ?>Roles/index" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-user-shield me-1"></i>Ver Roles
        </a>
    </div>

    <!-- ─────────────────────────────────────────────
         PERMISOS AGRUPADOS POR MÓDULO
         ───────────────────────────────────────────── -->
    <?php if (empty($permisosAgrupados)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-key fa-3x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
        No hay permisos registrados.
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($permisosAgrupados as $modulo => $permisos): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center"
                     style="background:rgba(245,168,0,0.06);">
                    <span class="fw-semibold text-capitalize">
                        <i class="fas fa-cube me-2" style="color:#F5A800;"></i>
                        <?= htmlspecialchars($modulo) ?>
                    </span>
                    <span class="badge" style="background:#F5A800;">
                        <?= count($permisos) ?>
                    </span>
                </div>
                <div class="card-body py-2 px-3">
                    <?php foreach ($permisos as $permiso): ?>
                    <div class="d-flex align-items-start gap-2 py-1 border-bottom last-border-0">
                        <i class="fas fa-check-circle mt-1" style="color:#F5A800; font-size:0.75rem;"></i>
                        <div>
                            <div style="font-size:0.85rem; font-weight:500;">
                                <?= htmlspecialchars($permiso->nombre) ?>
                            </div>
                            <code style="font-size:0.75rem; color:#8C6300;">
                                <?= htmlspecialchars($permiso->slug) ?>
                            </code>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>