<div class="container py-5">

    <h3 class="fw-bold mb-4">
        <i class="fas fa-box me-2" style="color:#F5A800;"></i>Mis Pedidos
    </h3>

    <?php if (empty($pedidos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-box-open fa-3x mb-3 d-block" style="opacity:0.3;"></i>
        <p>Aún no has hecho ningún pedido.</p>
        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa px-4 py-2 d-inline-block">
            Ver catálogo
        </a>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($pedidos as $pedido): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-bold" style="color:#F5A800; font-size:1.1rem;">
                                <?= htmlspecialchars($pedido->codigo) ?>
                            </div>
                            <small class="text-muted">
                                <?= date('d/m/Y H:i', strtotime($pedido->created_at)) ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge <?= $pedido->getBadgeEstado() ?>">
                                <i class="<?= $pedido->getIconoEstado() ?> me-1"></i>
                                <?= $pedido->estado ?>
                            </span>
                            <div class="fw-bold mt-1" style="color:#F5A800;">
                                <?= $pedido->getTotalFormateado() ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <span class="badge bg-light text-dark border">
                            <i class="fas <?= $pedido->esEnvio() ? 'fa-truck' : 'fa-store' ?> me-1"></i>
                            <?= $pedido->esEnvio() ? 'Envío a domicilio' : 'Retiro en tienda' ?>
                        </span>

                        <?php if ($pedido->pagado): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>Pagado
                        </span>
                        <?php endif; ?>

                        <!-- Siempre escribe al número de Ana, no al del cliente -->
                        <a href="https://wa.me/<?= WA_NUMBER ?>?text=<?= urlencode('Hola Ana 👋, quiero consultar el estado de mi pedido #' . ($pedido->codigo ?? '') . '. Gracias.') ?>"
                           target="_blank"
                           class="badge bg-success text-decoration-none">
                            <i class="fab fa-whatsapp me-1"></i>Consultar estado
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>