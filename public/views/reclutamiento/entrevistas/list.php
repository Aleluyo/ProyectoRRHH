<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

$area   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// Asegura que $entrevistas siempre sea un array
if (!isset($entrevistas) || !is_array($entrevistas)) {
    $entrevistas = [];
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Entrevistas · Reclutamiento y Selección</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            vc: {
              pink:'#ff78b5', peach:'#ffc9a9', teal:'#36d1cc',
              sand:'#ffe9c7', ink:'#0a2a5e', neon:'#a7fffd'
            }
          },
          fontFamily: {
            display:['Josefin Sans','system-ui','sans-serif'],
            sans:['DM Sans','system-ui','sans-serif'],
            vice:['Rage Italic','Yellowtail','cursive']
          },
          boxShadow:{ soft:'0 10px 28px rgba(10,42,94,.08)' },
          backgroundImage:{
            gridglow:'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
            ribbon:'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'
          }
        }
      }
    }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/eliminatemsg.css') ?>">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen bg-white text-vc-ink font-sans relative">
  <div class="h-[1px] w-full bg-[image:linear-gradient(90deg,#ff78b5,#ffc9a9,#36d1cc)] opacity-70"></div>
  <div class="absolute inset-0 grid-bg opacity-15 pointer-events-none"></div>

  <header class="sticky top-0 z-30 border-b border-black/10 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 h-16 flex items-center">
      <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
        <img src="<?= asset('img/galgovc.png') ?>" alt="RRHH" class="h-9 w-auto">
        <div class="font-display text-lg tracking-widest uppercase text-vc-ink">RRHH</div>
      </a>
      <div class="ml-auto flex items-center gap-3 text-sm text-muted-ink">
        <span class="hidden sm:inline-block truncate max-w-[220px]">
          <?= $puesto ?><?= $area ? ' &mdash; ' . $area : '' ?><?= $ciudad ? ' &mdash; ' . $ciudad : '' ?>
        </span>
        <a href="<?= url('logout.php') ?>" class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        <a href="<?= url('views/reclutamiento/index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Reclutamiento y Selección</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        <span class="font-medium text-vc-pink">Entrevistas</span>
      </nav>
    </div>

    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Entrevistas</h1>
        <p class="mt-1 text-sm sm:text-base text-muted-ink">
          Agenda y resultados de entrevistas para las postulaciones activas.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
        <div class="relative">
          <input id="searchInput" type="text"
            class="w-full sm:w-72 rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            placeholder="Buscar por postulación, fecha, resultado…">
          <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-ink">Buscar</span>
        </div>
        <a href="<?= url('index.php?controller=entrevista&action=create') ?>"
           class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-4 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition">
          <span class="mr-2 text-lg leading-none">+</span>
          Nueva entrevista
        </a>
      </div>
    </section>

    <section class="mt-6">
      <div class="overflow-x-auto rounded-xl border border-black/10 bg-white/90 shadow-soft">
        <table class="min-w-full text-sm" id="tablaEntrevistas">
          <thead class="bg-slate-100/80 text-xs uppercase tracking-wide text-muted-ink">
          <tr>
            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="id_entrevista">ID</th>
            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="id_postulacion">Postulación</th>
            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="fecha_programada">Fecha</th>
            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="tipo">Tipo</th>
            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="resultado">Resultado</th>
            <th class="px-3 py-2 text-center">Acciones</th>
          </tr>
          </thead>
          <tbody id="tbodyEntrevistas" class="align-top bg-white"></tbody>
        </table>
      </div>

      <p id="emptyMessage" class="mt-3 text-sm text-muted-ink hidden">
        No se encontraron entrevistas que coincidan con tu búsqueda.
      </p>

      <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div id="tableStatus" class="text-sm text-muted-ink">
          Total: 0 · Página 0 / 0
        </div>
        <div class="flex gap-2 justify-end">
          <button id="btnPrev" type="button"
                  class="rounded-lg border border-black/10 bg-white px-3 py-1.5 text-sm text-muted-ink disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50">
            Anterior
          </button>
          <button id="btnNext" type="button"
                  class="rounded-lg border border-black/10 bg-white px-3 py-1.5 text-sm text-muted-ink disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50">
            Siguiente
          </button>
        </div>
      </div>
    </section>
  </main>

  <script>
    const entrevistasData = <?= json_encode($entrevistas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?> || [];

    const rowsPerPage = 10;
    let currentPage = 1;
    let sortField = 'id_entrevista';
    let sortDir = 'asc';
    let filteredData = [...entrevistasData];

    const tbody = document.getElementById('tbodyEntrevistas');
    const searchInput = document.getElementById('searchInput');
    const emptyMessage = document.getElementById('emptyMessage');
    const tableStatus = document.getElementById('tableStatus');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const headerCells = document.querySelectorAll('th[data-sort]');

    const editBaseUrl   = "<?= url('index.php?controller=entrevista&action=edit') ?>";
    const deleteBaseUrl = "<?= url('index.php?controller=entrevista&action=delete') ?>";

    function escapeHtml(v){
      return String(v ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
        .replace(/'/g,'&#039;');
    }

    function compareByField(a,b,f){
      const va=(a[f]??'').toString().toLowerCase();
      const vb=(b[f]??'').toString().toLowerCase();
      const na=Number(va), nb=Number(vb);
      const aNum=!Number.isNaN(na)&&va.trim()!=='';
      const bNum=!Number.isNaN(nb)&&vb.trim()!=='';
      if(aNum&&bNum) return na-nb;
      return va.localeCompare(vb);
    }

    function resultadoBadge(res){
      const r=(res||'').toString().toUpperCase();
      let cls='bg-slate-100 text-slate-700';
      if(r==='PENDIENTE') cls='bg-amber-100 text-amber-800';
      else if(r==='APROBADO') cls='bg-emerald-100 text-emerald-800';
      else if(r==='RECHAZADO') cls='bg-rose-100 text-rose-800';
      return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium ${cls}">${escapeHtml(r)}</span>`;
    }

    function renderTable(){
      if(!Array.isArray(filteredData)) filteredData=[];
      if(filteredData.length===0){
        tbody.innerHTML='';
        emptyMessage.classList.remove('hidden');
        tableStatus.textContent='Total: 0 · Página 0 / 0';
        btnPrev.disabled=true; btnNext.disabled=true;
        return;
      }

      emptyMessage.classList.add('hidden');

      filteredData.sort((a,b)=>{
        const r=compareByField(a,b,sortField);
        return sortDir==='asc'?r:-r;
      });

      const total=filteredData.length;
      const totalPages=Math.max(1,Math.ceil(total/rowsPerPage));
      if(currentPage>totalPages) currentPage=totalPages;

      const start=(currentPage-1)*rowsPerPage;
      const pageItems=filteredData.slice(start,start+rowsPerPage);

      tbody.innerHTML=pageItems.map(e=>{
        const id=e.id_entrevista;
        const post=escapeHtml(e.id_postulacion ?? '');
        const fecha=escapeHtml(e.fecha_programada ?? '');
        const tipo=escapeHtml(e.tipo ?? '');
        const res=e.resultado ?? '';

        return `<tr class="border-t border-slate-200 hover:bg-slate-50">
          <td class="px-3 py-2 whitespace-nowrap text-xs text-muted-ink">${id}</td>
          <td class="px-3 py-2 whitespace-nowrap text-xs">${post}</td>
          <td class="px-3 py-2 whitespace-nowrap text-xs">${fecha}</td>
          <td class="px-3 py-2 whitespace-nowrap text-xs">${tipo}</td>
          <td class="px-3 py-2 whitespace-nowrap text-xs">${resultadoBadge(res)}</td>
          <td class="px-3 py-2 whitespace-nowrap">
            <div class="flex gap-2 justify-center">
              <a href="${editBaseUrl}&id=${id}"
                 class="rounded-md border border-black/10 bg-white px-2 py-1 text-xs hover:bg-vc-sand/60">
                Editar
              </a>
              <button type="button"
                class="btn-delete rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs text-rose-700 hover:bg-rose-100"
                data-label="Entrevista #${id}" data-href="${deleteBaseUrl}&id=${id}">
                Eliminar
              </button>
            </div>
          </td>
        </tr>`;
      }).join('');

      tableStatus.textContent=`Total: ${total} · Página ${currentPage} / ${totalPages}`;
      btnPrev.disabled=currentPage===1;
      btnNext.disabled=currentPage===totalPages;
    }

    searchInput.addEventListener('input',()=>{
      const term=searchInput.value.trim().toLowerCase();
      if(term==='') filteredData=[...entrevistasData];
      else{
        filteredData=entrevistasData.filter(e=>
          Object.values(e).some(v=>String(v??'').toLowerCase().includes(term))
        );
      }
      currentPage=1;
      renderTable();
    });

    btnPrev.addEventListener('click',()=>{ if(currentPage>1){currentPage--;renderTable();}});
    btnNext.addEventListener('click',()=>{
      const totalPages=Math.max(1,Math.ceil(filteredData.length/rowsPerPage));
      if(currentPage<totalPages){currentPage++;renderTable();}
    });

    headerCells.forEach(th=>{
      th.addEventListener('click',()=>{
        const f=th.dataset.sort;
        if(!f) return;
        if(sortField===f) sortDir=(sortDir==='asc'?'desc':'asc');
        else{sortField=f;sortDir='asc';}
        headerCells.forEach(c=>c.classList.remove('text-vc-ink','font-semibold'));
        th.classList.add('text-vc-ink','font-semibold');
        renderTable();
      });
    });

    document.addEventListener('click',e=>{
      const btn=e.target.closest('.btn-delete');
      if(!btn) return;
      const label=btn.dataset.label || 'la entrevista seleccionada';
      const href=btn.dataset.href;
      Swal.fire({
        title:'¿Eliminar entrevista?',
        html:`Se eliminará <strong>${escapeHtml(label)}</strong>.`,
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Sí, eliminar',
        cancelButtonText:'Cancelar',
        customClass:{
          popup:'vc-delete',
          title:'vc-delete-title',
          htmlContainer:'vc-delete-text',
          confirmButton:'vc-delete-confirm',
          cancelButton:'vc-delete-cancel'
        },
        buttonsStyling:false
      }).then(r=>{
        if(r.isConfirmed && href) window.location.href=href;
      });
    });

    renderTable();
  </script>
</body>
</html>