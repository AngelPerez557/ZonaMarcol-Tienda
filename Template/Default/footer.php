</div><!-- /.container-fluid -->
    </main><!-- /#mainContent -->

    <footer class="footer py-4 text-center">
        <p class="mb-0">
            &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash;
            Desarrollado por
            <a href="https://wa.me/50493429641"
               target="_blank"
               rel="noopener noreferrer"
               style="color:inherit; font-weight:600;">
                DeskCod
            </a>
        </p>
    </footer>

    <!-- ── 1. Variable global APP_URL ── -->
    <script>const APP_URL = '<?= APP_URL ?>';</script>

    <!-- ── Driver.js local ── -->
    <script src="<?= APP_URL ?>Content/Vendor/driverjs/driver.js.iife.js"></script>

    <!-- ── Variables globales del Tour ── -->
    <script>
    const AM_TOUR_COMPLETADO = <?= isset($_SESSION['user']['tour_completado'])
        ? ($_SESSION['user']['tour_completado'] ? 'true' : 'false')
        : 'true' ?>;
    const AM_APP_URL     = '<?= APP_URL ?>';
    const AM_USER_ID     = <?= Auth::id() ?? 0 ?>;
    const AM_CSRF        = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';
    const AM_USER_NOMBRE = '<?= htmlspecialchars(Auth::get('nombre') ?? 'Usuario') ?>';

    // Marca el tour como completado vía AJAX
    function amMarcarTour() {
        if (!AM_USER_ID || !AM_CSRF) return;
        const fd = new FormData();
        fd.append('csrf_token', AM_CSRF);
        fd.append('id', AM_USER_ID);
        fetch(AM_APP_URL + 'Usuarios/marcarTour', { method: 'POST', body: fd })
        .catch(() => {});
    }

    // Helper — obtener elemento por data-tour o id
    function amEl(id) {
        const el = document.querySelector('[data-tour="' + id + '"]')
                || document.getElementById(id);
        if (!el) return null;
        return el.hasAttribute('data-tour')
            ? '[data-tour="' + id + '"]'
            : '#' + el.id;
    }

    // Helper — filtrar steps sin elemento en DOM
    function amSteps(steps) {
        return steps.filter(s => {
            if (!s.element) return true;
            return document.querySelector(s.element) !== null;
        });
    }

    // Helper — instanciar Driver.js
    function amDriver(steps, onDestroy) {
        const fn = (window.driver && window.driver.js)
            ? window.driver.js.driver
            : window.driver;
        if (typeof fn !== 'function') return null;
        const t = fn({
            showProgress:     true,
            popoverClass:     'am-driver-popover',
            nextBtnText:      'Siguiente →',
            prevBtnText:      '← Atrás',
            doneBtnText:      '¡Entendido! ✓',
            allowClose:       true,
            onDestroyStarted: () => {
                amMarcarTour();
                if (onDestroy) onDestroy();
                t.destroy();
            },
            steps: amSteps(steps)
        });
        return t;
    }
    </script>

    <!-- ── Bootstrap bundle ── -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ── SweetAlert2 ── -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- ── Sidebar ── -->
    <script src="<?= APP_URL ?>Content/Dist/js/sidebar.js"></script>
    <!-- ── Dark mode toggle ── -->
    <script src="<?= APP_URL ?>Content/Dist/js/theme-switcher.js"></script>
    <!-- ── Tour guiado multi-página (Zona Marcol 2026) ── -->
    <script src="<?= APP_URL ?>Content/Dist/js/am-tour.js"></script>

    <!-- ── Scanner de código de barras con cámara ── -->
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="<?= APP_URL ?>Content/Dist/js/am-barcode-scanner.js"></script>

    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= APP_URL . htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ── Alertas flash ── -->
    <?php if (!empty($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon:              '<?= htmlspecialchars($flash['type']    ?? 'info',  ENT_QUOTES) ?>',
                title:             '<?= htmlspecialchars($flash['title']   ?? 'Aviso', ENT_QUOTES) ?>',
                text:              '<?= htmlspecialchars($flash['message'] ?? '',       ENT_QUOTES) ?>',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#F5A800',
                allowOutsideClick: false
            });
        });
        </script>
    <?php endif; ?>

    <!-- ── Alertas de sesión (sistema anterior) ── -->
    <?php if (!empty($_SESSION['alert'])): ?>
        <?php $alert = $_SESSION['alert']; unset($_SESSION['alert']); ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon:              '<?= htmlspecialchars($alert['icon']  ?? 'info',  ENT_QUOTES) ?>',
                title:             '<?= htmlspecialchars($alert['title'] ?? 'Aviso', ENT_QUOTES) ?>',
                text:              '<?= htmlspecialchars($alert['text']  ?? '',       ENT_QUOTES) ?>',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#F5A800',
                allowOutsideClick: false
            });
        });
        </script>
    <?php endif; ?>

</body>
</html>