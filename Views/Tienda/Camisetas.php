<?php
/**
 * Views/Tienda/Camisetas.php — Catálogo público de camisetas deportivas.
 * Tema oscuro. El cliente explora las equipaciones disponibles y filtra
 * por liga y versión. El pedido se coordina por WhatsApp (el configurador
 * completo con anticipo es una etapa posterior).
 */
$activas = array_filter($equipaciones, fn($e) => $e->isActivo());
?>
<div class="container py-5">

    <!-- ─── CABECERA ─────────────────────────────── -->
    <div class="text-center mb-4">
        <span style="display:inline-block;font-size:0.75rem;font-weight:700;letter-spacing:0.5px;
                     color:#F5A800;background:rgba(245,168,0,0.12);
                     border:1px solid rgba(245,168,0,0.28);border-radius:20px;padding:4px 14px;">
            <i class="fas fa-tshirt me-1"></i>CAMISETAS DEPORTIVAS
        </span>
        <h1 style="color:#fff;font-weight:800;margin:16px 0 8px;font-size:clamp(1.8rem,5vw,2.6rem);">
            Camisetas de tu <span style="color:#F5A800;">equipo</span>
        </h1>
        <p style="color:#8a8a8a;max-width:520px;margin:0 auto;">
            Ligas y selecciones, versión hombre, mujer e infantil.
            Elegí la tuya y la coordinamos por WhatsApp.
        </p>
    </div>

    <?php if (empty($activas)): ?>
        <div style="text-align:center;padding:60px 20px;color:#8a8a8a;">
            <i class="fas fa-tshirt fa-3x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
            Todavía no hay camisetas cargadas en el catálogo.
        </div>
    <?php else: ?>

    <!-- ─── FILTROS DE LIGA — tiles grandes con logo destacado ─── -->
    <div class="mb-4 cm-ligas-grid">
        <button type="button" class="cm-tile-liga on" data-liga="" title="Todas las ligas">
            <div class="cm-tile-logo">
                <i class="fas fa-globe" style="color:#F5A800;font-size:2rem;"></i>
            </div>
            <div class="cm-tile-nombre">Todas</div>
        </button>
        <?php foreach ($torneos as $t): ?>
        <button type="button" class="cm-tile-liga" data-liga="<?= htmlspecialchars($t->nombre) ?>"
                title="<?= htmlspecialchars($t->nombre) ?>">
            <div class="cm-tile-logo">
                <?php if (!empty($t->logo_path)): ?>
                <img src="<?= htmlspecialchars($t->getLogoUrl()) ?>"
                     alt="<?= htmlspecialchars($t->nombre) ?>"
                     onerror="this.parentElement.innerHTML='<i class=\'fas fa-trophy\' style=\'color:#F5A800;font-size:2rem;\'></i>';">
                <?php else: ?>
                <i class="fas fa-trophy" style="color:#F5A800;font-size:2rem;"></i>
                <?php endif; ?>
            </div>
            <div class="cm-tile-nombre"><?= htmlspecialchars($t->nombre) ?></div>
        </button>
        <?php endforeach; ?>
    </div>
    <div class="mb-4 d-flex flex-wrap gap-2 justify-content-center">
        <button type="button" class="cm-filtro cm-ver on" data-ver="">Todas</button>
        <button type="button" class="cm-filtro cm-ver" data-ver="hombre">Hombre</button>
        <button type="button" class="cm-filtro cm-ver" data-ver="mujer">Mujer</button>
        <button type="button" class="cm-filtro cm-ver" data-ver="infantil">Infantil</button>
    </div>

    <!-- ─── GRID DE CAMISETAS ────────────────────── -->
    <div class="row g-4" id="cmGrid">
        <?php foreach ($activas as $eq): ?>
        <div class="col-6 col-md-4 col-lg-3 cm-item"
             data-liga="<?= htmlspecialchars($eq->torneo_nombre ?? '') ?>"
             data-ver="<?= htmlspecialchars($eq->version ?? '') ?>">
            <div style="background:#222222;border:1px solid #333333;border-radius:14px;
                        overflow:hidden;height:100%;display:flex;flex-direction:column;">

                <div style="height:190px;background:#1a1a1a;background-image:url('<?= htmlspecialchars($eq->getImagenUrl()) ?>');
                            background-size:contain;background-position:center;background-repeat:no-repeat;"></div>

                <div style="padding:14px;display:flex;flex-direction:column;flex:1;">
                    <span style="font-size:0.7rem;font-weight:700;color:#F5A800;letter-spacing:0.4px;
                                 text-transform:uppercase;">
                        <?= htmlspecialchars($eq->torneo_nombre ?? 'Liga') ?>
                    </span>
                    <h6 style="color:#e6e6e6;font-weight:700;margin:4px 0 6px;line-height:1.3;">
                        <?= htmlspecialchars($eq->equipo_nombre ?? 'Equipo') ?>
                    </h6>
                    <div style="font-size:0.75rem;color:#8a8a8a;margin-bottom:12px;">
                        <?= htmlspecialchars($eq->tipo_nombre ?? '') ?>
                        · <?= htmlspecialchars($eq->getVersionLabel()) ?>
                        <?php if ($eq->temporada_nombre): ?>
                        · <?= htmlspecialchars($eq->temporada_nombre) ?>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top:auto;display:flex;align-items:center;
                                justify-content:space-between;border-top:1px solid #333333;padding-top:12px;">
                        <span style="color:#F5A800;font-weight:800;font-size:1.1rem;">
                            <?= htmlspecialchars($eq->getPrecioFormateado()) ?>
                        </span>
                        <a href="<?= APP_URL ?>Tienda/configurador/<?= (int) $eq->id ?>"
                           style="background:#F5A800;color:#1a1a1a;border-radius:8px;padding:6px 14px;
                                  font-size:0.8rem;font-weight:700;text-decoration:none;
                                  display:inline-flex;align-items:center;gap:4px;"
                           title="Configurar y pedir">
                            <i class="fas fa-cog"></i>Configurar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div id="cmVacio" style="display:none;text-align:center;padding:50px 20px;color:#8a8a8a;">
        No hay camisetas que coincidan con el filtro.
    </div>

    <?php endif; ?>
</div>

<style>
/* Filtros versión (Hombre/Mujer/Infantil) — pill chico */
.cm-filtro{
    font-size:0.8rem;color:#8a8a8a;background:#222222;border:1px solid #333333;
    border-radius:18px;padding:6px 16px;cursor:pointer;transition:all 0.2s;white-space:nowrap;
}
.cm-filtro:hover{border-color:#F5A800;color:#e6e6e6;}
.cm-filtro.on{background:#F5A800;color:#1a1a1a;border-color:#F5A800;font-weight:700;}

/* Tiles de liga — grid responsive con logo grande */
.cm-ligas-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(110px,1fr));
    gap:12px;
    max-width:980px;
    margin:0 auto;
}
.cm-tile-liga{
    background:#222222;border:1px solid #333333;border-radius:14px;
    padding:14px 8px;cursor:pointer;transition:all 0.2s;
    display:flex;flex-direction:column;align-items:center;gap:8px;
    color:#8a8a8a;
}
.cm-tile-liga:hover{
    border-color:#F5A800;color:#e6e6e6;transform:translateY(-2px);
    box-shadow:0 4px 14px rgba(245,168,0,0.15);
}
.cm-tile-logo{
    width:64px;height:64px;border-radius:12px;background:#fff;
    display:flex;align-items:center;justify-content:center;padding:6px;
}
.cm-tile-logo img{max-width:100%;max-height:100%;object-fit:contain;}
.cm-tile-nombre{
    font-size:0.78rem;font-weight:700;letter-spacing:0.3px;text-align:center;
    line-height:1.2;
}
.cm-tile-liga.on{
    background:#F5A800;border-color:#F5A800;color:#1a1a1a;
}
.cm-tile-liga.on .cm-tile-logo{background:#1a1a1a;}
.cm-tile-liga.on .cm-tile-logo i{color:#F5A800 !important;}
@media (max-width:480px){
    .cm-ligas-grid{grid-template-columns:repeat(auto-fit,minmax(90px,1fr));gap:8px;}
    .cm-tile-logo{width:52px;height:52px;}
    .cm-tile-nombre{font-size:0.7rem;}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let ligaSel = '', verSel = '';
    const items = document.querySelectorAll('.cm-item');
    const vacio = document.getElementById('cmVacio');

    function aplicar() {
        let visibles = 0;
        items.forEach(function (it) {
            const okLiga = (ligaSel === '' || it.dataset.liga === ligaSel);
            const okVer  = (verSel  === '' || it.dataset.ver  === verSel);
            const ver = okLiga && okVer;
            it.style.display = ver ? '' : 'none';
            if (ver) visibles++;
        });
        if (vacio) vacio.style.display = visibles === 0 ? 'block' : 'none';
    }

    document.querySelectorAll('.cm-tile-liga').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cm-tile-liga').forEach(b => b.classList.remove('on'));
            this.classList.add('on');
            ligaSel = this.dataset.liga;
            aplicar();
        });
    });
    document.querySelectorAll('.cm-ver').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cm-ver').forEach(b => b.classList.remove('on'));
            this.classList.add('on');
            verSel = this.dataset.ver;
            aplicar();
        });
    });
});
</script>
