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

// -----------------------------------------------------------------------------
// Obtención de datos
// -----------------------------------------------------------------------------
// Asegura que $turnos siempre sea un array (vacío si no viene definido)
if (!isset($turnos) || !is_array($turnos)) {
    $turnos = [];
}

// Valor del checkbox "Ver también inactivas"
$showInactive = isset($showInactive) ? (bool)$showInactive : false;

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Turnos · Administración organizacional</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Tailwind -->
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
          boxShadow: {
            soft:'0 10px 28px rgba(10,42,94,.08)'
          },
          backgroundImage: {
            gridglow:'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
            ribbon:'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'
          }
        }
      }
    }
  </script>

  <!-- Fuentes -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap" rel="stylesheet">

  <!-- Estilos Vice -->
  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
  <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
  <link rel="stylesheet" href="<?= asset('css/eliminatemsg.css') ?>">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen bg-white text-vc-ink font-sans relative">

  <!-- Línea superior + fondo -->
  <div class="h-[1px] w-full bg-[image:linear-gradient(90deg,#ff78b5,#ffc9a9,#36d1cc)] opacity-70"></div>
  <div class="absolute inset-0 grid-bg opacity-15 pointer-events-none"></div>

  <!-- Header -->
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

  <!-- Contenido principal -->
  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">
    <!-- Breadcrumb -->
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Inicio
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('views/organizacional/index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Administración Organizacional
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">Turnos</span>
      </nav>
    </div>

    <!-- Título + acciones -->
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Turnos</h1>
        <p class="mt-1 text-sm sm:text-base text-muted-ink">
          Catálogo de turnos laborales · Horarios, tolerancia y días de trabajo.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
        <!-- Barra de búsqueda -->
        <div class="relative">
          <input
            id="searchInput"
            type="text"
            class="w-full sm:w-72 rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            placeholder="Buscar por nombre o horario…"
          />
          <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-ink">Buscar</span>
        </div>

        <!-- Botón de agregar -->
        <a
          href="<?= url('index.php?controller=turno&action=create') ?>"
          class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-4 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
        >
          <span class="mr-2 text-lg leading-none">+</span>
          Agregar turno
        </a>
      </div>
    </section>

    <!-- Tabla -->
    <section class="mt-6">
      <div class="overflow-x-auto rounded-xl border border-black/10 bg-white/90 shadow-soft">
        <table class="min-w-full text-sm" id="tablaTurnos">
          <thead class="bg-slate-100/80 text-xs uppercase tracking-wide text-muted-ink">
            <tr>
              <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="id_turno">ID</th>
              <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="nombre_turno">Nombre</th>
              <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="hora_entrada">Hora entrada</th>
              <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="hora_salida">Hora salida</th>
              <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="tolerancia_minutos">Tolerancia (min)</th>
              <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="dias_laborales">Días laborales</th>
              <th class="px-3 py-2 text-center cursor-pointer select-none" data-sort="activo">Activo</th>
              <th class="px-3 py-2 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyTurnos" class="align-top bg-white">
            <!-- Filas generadas por JS -->
          </tbody>
        </table>
      </div>

      <!-- Mensaje sin resultados -->
      <p id="emptyMessage" class="mt-3 text-sm text-muted-ink hidden">
        No se encontraron turnos que coincidan con tu búsqueda.
      </p>

      <!-- Estado + paginación -->
      <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div id="tableStatus" class="text-sm text-muted-ink">
          Total: 0 · Página 0 / 0
        </div>
        <div class="flex gap-2 justify-end">
          <button
            id="btnPrev"
            type="button"
            class="rounded-lg border border-black/10 bg-white px-3 py-1.5 text-sm text-muted-ink disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50"
          >
            Anterior
          </button>
          <button
            id="btnNext"
            type="button"
            class="rounded-lg border border-black/10 bg-white px-3 py-1.5 text-sm text-muted-ink disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50"
          >
            Siguiente
          </button>
        </div>
      </div>
       <!-- Checkbox ver inactivas -->
        <div class="sm:ml-2 justify-start">
          <label class="inline-flex items-center gap-2 text-xs text-muted-ink">
            <input type="checkbox" id="chkShowInactive" class="rounded border-slate-300" <?= $showInactive ? 'checked' : '' ?>>
            <span>Ver también inactivos</span>
          </label>
        </div>
    </section>
  </main>

  <script>
    // Datos recibidos desde PHP
    const turnosData = <?= json_encode($turnos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?> || [];

    const rowsPerPage = 10;
    let currentPage = 1;
    let sortField = 'id_turno';
    let sortDir = 'asc';
    let filteredData = [...turnosData];

    const tbody = document.getElementById('tbodyTurnos');
    const searchInput = document.getElementById('searchInput');
    const emptyMessage = document.getElementById('emptyMessage');
    const tableStatus = document.getElementById('tableStatus');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const chkShowInactive = document.getElementById('chkShowInactive');
    const headerCells = document.querySelectorAll('th[data-sort]');

    if (chkShowInactive) {
      chkShowInactive.addEventListener('change', () => {
        const url = new URL(window.location.href);

        if (chkShowInactive.checked) {
          url.searchParams.set('showInactive', '1');
        } else {
          url.searchParams.delete('showInactive');
        }

        window.location.href = url.toString();
      });
    }

    const editBaseUrl   = "<?= url('index.php?controller=turno&action=edit') ?>";
    const toggleBaseUrl = "<?= url('index.php?controller=turno&action=toggle') ?>";

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function compareByField(a, b, field) {
      const va = (a[field] ?? '').toString().toLowerCase();
      const vb = (b[field] ?? '').toString().toLowerCase();

      const na = Number(va);
      const nb = Number(vb);
      const aIsNum = !Number.isNaN(na) && va.trim() !== '';
      const bIsNum = !Number.isNaN(nb) && vb.trim() !== '';

      if (aIsNum && bIsNum) {
        return na - nb;
      }
      return va.localeCompare(vb);
    }

    function formatDiasLabel(codigos) {
      if (!codigos) return '';
      const mapDias = {
        'L': 'Lunes',
        'M': 'Martes',
        'X': 'Miércoles',
        'J': 'Jueves',
        'V': 'Viernes',
        'S': 'Sábado',
        'D': 'Domingo'
      };
      const arr = codigos.split(',')
        .map(d => d.trim().toUpperCase())
        .filter(Boolean);

      const nombres = arr.map(d => mapDias[d] || d);
      return nombres.join(', ');
    }

    function renderTable() {
      if (!Array.isArray(filteredData)) {
        filteredData = [];
      }

      if (filteredData.length === 0) {
        tbody.innerHTML = '';
        emptyMessage.classList.remove('hidden');
        tableStatus.textContent = 'Total: 0 · Página 0 / 0';
        btnPrev.disabled = true;
        btnNext.disabled = true;
        return;
      }

      emptyMessage.classList.add('hidden');

      // Ordenar
      filteredData.sort((a, b) => {
        const res = compareByField(a, b, sortField);
        return sortDir === 'asc' ? res : -res;
      });

      const total = filteredData.length;
      const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
      if (currentPage > totalPages) currentPage = totalPages;

      const start = (currentPage - 1) * rowsPerPage;
      const pageItems = filteredData.slice(start, start + rowsPerPage);

      tbody.innerHTML = pageItems.map(tur => {
        const isActiva = String(tur.activo) === '1';
        const activaBadge = isActiva
          ? '<span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Activa</span>'
          : '<span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Inactiva</span>';
        const idTurno   = tur.id_turno;
        const nombre    = escapeHtml(tur.nombre_turno ?? '');
        const hEntrada  = escapeHtml(tur.hora_entrada ?? '');
        const hSalida   = escapeHtml(tur.hora_salida ?? '');
        const tolerancia = escapeHtml(tur.tolerancia_minutos ?? '');
        const diasCod   = String(tur.dias_laborales ?? '');
        const diasLabel = escapeHtml(formatDiasLabel(diasCod));

        return `
          <tr class="border-t border-slate-200 hover:bg-slate-50">
            <td class="px-3 py-2 whitespace-nowrap text-xs text-muted-ink">${idTurno}</td>
            <td class="px-3 py-2 whitespace-nowrap">${nombre}</td>
            <td class="px-3 py-2 whitespace-nowrap text-xs">${hEntrada}</td>
            <td class="px-3 py-2 whitespace-nowrap text-xs">${hSalida}</td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-center">${tolerancia}</td>
            <td class="px-3 py-2 max-w-xs truncate" title="${escapeHtml(diasCod)}">${diasLabel}</td>
            <td class="px-3 py-2 whitespace-nowrap text-center">${activaBadge}</td>
            <td class="px-3 py-2 whitespace-nowrap">
              <div class="flex gap-2 justify-center">
                <a
                  href="${editBaseUrl}&id=${idTurno}"
                  class="rounded-md border border-black/10 bg-white px-2 py-1 text-xs hover:bg-vc-sand/60"
                >
                  Editar
                </a>
                <button
                  type="button"
                  class="btn-delete rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs text-rose-700 hover:bg-rose-100"
                  data-id="${idTurno}"
                  data-nombre="${nombre}"
                  data-href="${toggleBaseUrl}&id=${tur.id_turno}&active=${isActiva ? 0 : 1}"
                  data-mode="${isActiva ? 'desactivar' : 'activar'}"
                >
                  ${isActiva ? 'Desactivar' : 'Activar'}
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');

      tableStatus.textContent = `Total: ${total} · Página ${currentPage} / ${totalPages}`;

      btnPrev.disabled = currentPage === 1;
      btnNext.disabled = currentPage === totalPages;
    }

    // Búsqueda en vivo
    searchInput.addEventListener('input', () => {
      const term = searchInput.value.trim().toLowerCase();

      if (term === '') {
        filteredData = [...turnosData];
      } else {
        filteredData = turnosData.filter(tur =>
          Object.values(tur).some(value =>
            String(value ?? '').toLowerCase().includes(term)
          )
        );
      }

      currentPage = 1;
      renderTable();
    });

    // Paginación
    btnPrev.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage -= 1;
        renderTable();
      }
    });

    btnNext.addEventListener('click', () => {
      const totalPages = Math.max(1, Math.ceil(filteredData.length / rowsPerPage));
      if (currentPage < totalPages) {
        currentPage += 1;
        renderTable();
      }
    });

    // Orden por encabezado
    headerCells.forEach(th => {
      th.addEventListener('click', () => {
        const field = th.dataset.sort;
        if (!field) return;

        if (sortField === field) {
          sortDir = sortDir === 'asc' ? 'desc' : 'asc';
        } else {
          sortField = field;
          sortDir = 'asc';
        }

        headerCells.forEach(cell => cell.classList.remove('text-vc-ink', 'font-semibold'));
        th.classList.add('text-vc-ink', 'font-semibold');

        renderTable();
      });
    });

   // SweetAlert para activar / desactivar turno
    document.addEventListener('click', event => {
      const btn = event.target.closest('.btn-delete');
      if (!btn) return;

      const nombre = btn.dataset.nombre || '';
      const href   = btn.dataset.href;
      const mode   = btn.dataset.mode || 'desactivar'; // 'activar' o 'desactivar'
      const isDeactivate = mode === 'desactivar';

      Swal.fire({
          title: isDeactivate ? '¿Desactivar turno?' : '¿Activar turno?',
          html: nombre 
            ? `${isDeactivate 
                ? 'Se desactivará' 
                : 'Se activará'} "<strong>${nombre}</strong>".`
            : isDeactivate
              ? 'Se desactivará el turno seleccionado.'
              : 'Se activará el turno seleccionado.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: isDeactivate ? 'Sí, desactivar' : 'Sí, activar',
          cancelButtonText: 'Cancelar',
          customClass: {
            popup: 'vc-delete',
            title: 'vc-delete-title',
            htmlContainer: 'vc-delete-text',
            confirmButton: 'vc-delete-confirm',
            cancelButton: 'vc-delete-cancel'
          },
          buttonsStyling: false                
        }).then(result => {
          if (result.isConfirmed && href) {
            window.location.href = href;
          }
        });
    });


    // Render inicial
    renderTable();
  </script>
</body>
</html>
