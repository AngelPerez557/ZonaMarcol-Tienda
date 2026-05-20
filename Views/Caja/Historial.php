<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-book me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted"><?= count($sesiones) ?> sesión<?= count($sesiones) !== 1 ? 'es' : '' ?></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" id="porPagina" style="width:auto;">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
            </select>
            <a href="<?= APP_URL ?>Caja/index" class="btn btn-primary btn-sm">
                <i class="fas fa-cash-register me-1"></i>Ir a Caja
            </a>
        </div>
    </div>

    <div class="card" id="tablaHistorial">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">#</th>
                            <th>Cajero</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th class="text-end">Fondo inicial</th>
                            <th class="text-end">Total ventas</th>
                            <th class="text-end">Diferencia</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sesiones)): ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-book fa-2x mb-3 d-block" style="opacity:0.3;color:#F5A800;"></i>
                            No hay sesiones registradas.
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($sesiones as $s): ?>
                        <tr class="sesion-row">
                            <td class="ps-4 fw-bold" style="color:#F5A800;">#<?= str_pad($s['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><div class="fw-semibold"><?= htmlspecialchars($s['cajero_nombre']) ?></div></td>
                            <td class="text-muted" style="font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($s['abierta_at'])) ?></td>
                            <td class="text-muted" style="font-size:0.85rem;"><?= $s['cerrada_at'] ? date('d/m/Y H:i', strtotime($s['cerrada_at'])) : '—' ?></td>
                            <td class="text-end">L. <?= number_format((float)$s['monto_apertura'], 2) ?></td>
                            <td class="text-end fw-bold" style="color:#28a745;">
                                <?= $s['total_ventas'] !== null ? 'L. '.number_format((float)$s['total_ventas'],2) : '—' ?>
                            </td>
                            <td class="text-end fw-bold">
                                <?php if ($s['diferencia'] !== null): $dif=(float)$s['diferencia']; ?>
                                <span class="<?= abs($dif)<0.01?'text-success':($dif>0?'text-info':'text-danger') ?>">
                                    <?= $dif>=0?'+':'' ?>L. <?= number_format($dif,2) ?>
                                </span>
                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $s['estado']==='abierta'?'bg-success':'bg-secondary' ?>">
                                    <?= $s['estado']==='abierta'?'Abierta':'Cerrada' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <?php if ($s['estado']==='cerrada'): ?>
                                    <a href="<?= APP_URL ?>Caja/resumen/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver resumen"><i class="fas fa-eye"></i></a>
                                    <a href="<?= APP_URL ?>Caja/resumen/<?= $s['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimir"><i class="fas fa-print"></i></a>
                                    <?php else: ?>
                                    <a href="<?= APP_URL ?>Caja/cierre" class="btn btn-sm btn-danger"><i class="fas fa-store-slash me-1"></i>Cerrar</a>
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

    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
        <small class="text-muted" id="infoPagina"></small>
        <nav><ul class="pagination pagination-sm mb-0" id="navPagina"></ul></nav>
    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filas=[ ...document.querySelectorAll('.sesion-row')];
    const infoPag=document.getElementById('infoPagina');
    const navPag=document.getElementById('navPagina');
    const porPagSel=document.getElementById('porPagina');
    let porPagina=20, pagActual=1, filtradas=filas.map((_,i)=>i);

    porPagSel.addEventListener('change',()=>{ porPagina=parseInt(porPagSel.value); pagActual=1; render(); });

    function render(){
        const total=filtradas.length, paginas=Math.max(1,Math.ceil(total/porPagina));
        if(pagActual>paginas) pagActual=paginas;
        const inicio=(pagActual-1)*porPagina, fin=Math.min(inicio+porPagina,total);
        const vis=new Set(filtradas.slice(inicio,fin));
        filas.forEach((el,i)=>{ el.style.display=vis.has(i)?'':'none'; });
        infoPag.textContent=total>0?`Página ${pagActual} de ${paginas} — ${inicio+1}–${fin} de ${total}`:'Sin resultados';
        renderNav(paginas);
    }

    function renderNav(paginas){
        navPag.innerHTML=''; if(paginas<=1) return;
        const btn=(lbl,page,dis,act)=>{
            const li=document.createElement('li'); li.className=`page-item${dis?' disabled':''}${act?' active':''}`;
            const a=document.createElement('a'); a.className='page-link'; a.href='#'; a.innerHTML=lbl;
            if(!dis&&!act) a.addEventListener('click',e=>{ e.preventDefault(); pagActual=page; render(); document.getElementById('tablaHistorial')?.scrollIntoView({behavior:'smooth',block:'start'}); });
            li.appendChild(a); return li;
        };
        navPag.appendChild(btn('&laquo;',pagActual-1,pagActual===1,false));
        let nums=paginas<=7?Array.from({length:paginas},(_,i)=>i+1):[1];
        if(paginas>7){ if(pagActual>3)nums.push('…'); for(let i=Math.max(2,pagActual-1);i<=Math.min(paginas-1,pagActual+1);i++)nums.push(i); if(pagActual<paginas-2)nums.push('…'); nums.push(paginas); }
        nums.forEach(n=>{ if(n==='…'){const li=document.createElement('li');li.className='page-item disabled';li.innerHTML='<a class="page-link">…</a>';navPag.appendChild(li);}else navPag.appendChild(btn(n,n,false,n===pagActual)); });
        navPag.appendChild(btn('&raquo;',pagActual+1,pagActual===paginas,false));
    }
    render();
});
</script>