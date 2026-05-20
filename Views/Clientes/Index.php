<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-users me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted"><?= count($clientes) ?> cliente<?= count($clientes) !== 1 ? 's' : '' ?> registrado<?= count($clientes) !== 1 ? 's' : '' ?></small>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalQR">
            <i class="fas fa-qrcode me-2"></i>Mostrar QR de registro
        </button>
    </div>

    <!-- Modal QR -->
    <div class="modal fade" id="modalQR" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header border-0">
                    <h5 class="modal-title w-100 fw-bold"><i class="fas fa-qrcode me-2" style="color:#F5A800;"></i>Registro de Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-4">
                    <p class="text-muted mb-3">Muestra este código al cliente para que se registre desde su celular.</p>
                    <div id="qrRegistro" style="display:inline-block;padding:12px;border:4px solid #F5A800;border-radius:12px;"></div>
                    <div class="mt-3"><small class="text-muted"><i class="fas fa-link me-1"></i><?= APP_URL ?>Tienda/registro</small></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="buscarCliente" placeholder="Buscar por nombre, correo o teléfono...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 d-flex align-items-center justify-content-end gap-2">
                    <small class="text-muted" id="contadorVisible"><?= count($clientes) ?></small>
                    <select class="form-select form-select-sm" id="porPagina" style="width:auto;">
                        <option value="15">15</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card" id="tablaClientes">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">#</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientes)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-2x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
                            No hay clientes registrados.
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($clientes as $i => $cliente): ?>
                        <tr class="cliente-row"
                            data-nombre="<?= strtolower(htmlspecialchars($cliente->nombre)) ?>"
                            data-email="<?= strtolower(htmlspecialchars($cliente->email ?? '')) ?>"
                            data-telefono="<?= htmlspecialchars($cliente->telefono ?? '') ?>"
                            data-activo="<?= $cliente->activo ?>">
                            <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;background:rgba(245,168,0,0.12);flex-shrink:0;">
                                        <i class="fas fa-user" style="color:#F5A800;font-size:0.85rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($cliente->nombre) ?></div>
                                        <small class="text-muted">Desde <?= date('d/m/Y', strtotime($cliente->created_at)) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">
                                <?= $cliente->email ? '<a href="mailto:'.htmlspecialchars($cliente->email).'" class="text-muted">'.htmlspecialchars($cliente->email).'</a>' : '<em>Sin correo</em>' ?>
                            </td>
                            <td class="text-muted">
                                <?= $cliente->telefono ? '<a href="https://wa.me/'.preg_replace('/[^0-9]/','',  $cliente->telefono).'" target="_blank" class="text-muted"><i class="fab fa-whatsapp me-1 text-success"></i>'.htmlspecialchars($cliente->telefono).'</a>' : '<em>Sin teléfono</em>' ?>
                            </td>
                            <td class="text-muted" style="max-width:180px;">
                                <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;">
                                    <?= $cliente->direccion ? htmlspecialchars($cliente->direccion) : '<em>Sin dirección</em>' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if (Auth::can('clientes.editar')): ?>
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-activo" type="checkbox" role="switch"
                                           id="toggle-<?= $cliente->id ?>"
                                           data-id="<?= $cliente->id ?>"
                                           data-url="<?= APP_URL ?>Clientes/toggle"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $cliente->isActivo() ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="toggle-<?= $cliente->id ?>"></label>
                                </div>
                                <?php else: ?>
                                <span class="badge <?= $cliente->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $cliente->isActivo() ? 'Activo' : 'Inactivo' ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (Auth::can('clientes.editar')): ?>
                                <a href="<?= APP_URL ?>Clientes/registry/<?= $cliente->id ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
        <small class="text-muted" id="infoPagina"></small>
        <nav><ul class="pagination pagination-sm mb-0" id="navPagina"></ul></nav>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    new QRCode(document.getElementById('qrRegistro'), {
        text:'<?= APP_URL ?>Tienda/registro', width:220, height:220,
        colorDark:'#F5A800', colorLight:'#ffffff', correctLevel:QRCode.CorrectLevel.H
    });

    const filas     = [...document.querySelectorAll('.cliente-row')];
    const contador  = document.getElementById('contadorVisible');
    const infoPag   = document.getElementById('infoPagina');
    const navPag    = document.getElementById('navPagina');
    const porPagSel = document.getElementById('porPagina');
    const buscar    = document.getElementById('buscarCliente');
    const filtroEst = document.getElementById('filtroEstado');

    let porPagina = 25, pagActual = 1;
    let filtradas = filas.map((_, i) => i);

    function aplicarFiltros() {
        const txt    = buscar.value.toLowerCase();
        const estado = filtroEst.value;
        filtradas = [];
        filas.forEach((fila, i) => {
            const ok = (!txt    || fila.dataset.nombre.includes(txt) || fila.dataset.email.includes(txt) || fila.dataset.telefono.includes(txt)) &&
                       (!estado || fila.dataset.activo === estado);
            if (ok) filtradas.push(i);
        });
        pagActual = 1; render();
    }

    buscar.addEventListener('input',        aplicarFiltros);
    filtroEst.addEventListener('change',    aplicarFiltros);
    porPagSel.addEventListener('change', () => { porPagina = parseInt(porPagSel.value); pagActual = 1; render(); });

    function render() {
        const total   = filtradas.length;
        const paginas = Math.max(1, Math.ceil(total / porPagina));
        if (pagActual > paginas) pagActual = paginas;
        const inicio = (pagActual-1)*porPagina, fin = Math.min(inicio+porPagina, total);
        const vis    = new Set(filtradas.slice(inicio, fin));
        filas.forEach((el, i) => { el.style.display = vis.has(i) ? '' : 'none'; });
        contador.textContent = `${total}`;
        infoPag.textContent  = total > 0 ? `Página ${pagActual} de ${paginas} — ${inicio+1}–${fin} de ${total}` : 'Sin resultados';
        renderNav(paginas);
    }

    function renderNav(paginas) {
        navPag.innerHTML = '';
        if (paginas <= 1) return;
        const btn = (lbl, page, dis, act) => {
            const li = document.createElement('li'); li.className = `page-item${dis?' disabled':''}${act?' active':''}`;
            const a  = document.createElement('a');  a.className = 'page-link'; a.href = '#'; a.innerHTML = lbl;
            if (!dis && !act) a.addEventListener('click', e => { e.preventDefault(); pagActual = page; render(); document.getElementById('tablaClientes')?.scrollIntoView({behavior:'smooth',block:'start'}); });
            li.appendChild(a); return li;
        };
        navPag.appendChild(btn('&laquo;', pagActual-1, pagActual===1, false));
        let nums = paginas <= 7 ? Array.from({length:paginas},(_,i)=>i+1) : [1];
        if (paginas > 7) { if (pagActual>3) nums.push('…'); for (let i=Math.max(2,pagActual-1);i<=Math.min(paginas-1,pagActual+1);i++) nums.push(i); if (pagActual<paginas-2) nums.push('…'); nums.push(paginas); }
        nums.forEach(n => { if (n==='…') { const li=document.createElement('li'); li.className='page-item disabled'; li.innerHTML='<a class="page-link">…</a>'; navPag.appendChild(li); } else navPag.appendChild(btn(n,n,false,n===pagActual)); });
        navPag.appendChild(btn('&raquo;', pagActual+1, pagActual===paginas, false));
    }

    // Toggle activo
    document.querySelectorAll('input.toggle-activo').forEach(toggle => {
        toggle.addEventListener('change', function(e) {
            e.stopPropagation();
            const id=this.dataset.id, url=this.dataset.url, csrf=this.dataset.csrf, activo=this.checked?1:0, self=this;
            fetch(url,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`id=${id}&activo=${activo}&csrf_token=${csrf}`})
            .then(r=>r.json()).then(data=>{
                if(data.success){ this.closest('.cliente-row').dataset.activo=activo; Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2000}).fire({icon:'success',title:activo?'Cliente activado':'Cliente desactivado'}); }
                else self.checked=!self.checked;
            }).catch(()=>{ self.checked=!self.checked; });
        });
    });

    render();
});
</script>