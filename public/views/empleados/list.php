<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

$area = htmlspecialchars($_SESSION['area'] ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// Asegura que $empleados siempre sea un array
if (!isset($empleados) || !is_array($empleados)) {
    $empleados = [];
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Empleados · Expedientes</title>
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
                            pink: '#ff78b5', peach: '#ffc9a9', teal: '#36d1cc',
                            sand: '#ffe9c7', ink: '#0a2a5e', neon: '#a7fffd'
                        }
                    },
                    fontFamily: {
                        display: ['Josefin Sans', 'system-ui', 'sans-serif'],
                        sans: ['DM Sans', 'system-ui', 'sans-serif'],
                        vice: ['Rage Italic', 'Yellowtail', 'cursive']
                    },
                    boxShadow: {
                        soft: '0 10px 28px rgba(10,42,94,.08)'
                    },
                    backgroundImage: {
                        gridglow: 'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
                        ribbon: 'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'
                    }
                }
            }
        }
    </script>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap"
        rel="stylesheet">

    <!-- Estilos Vice -->
    <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
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
                <a href="<?= url('logout.php') ?>"
                    class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">
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
                <span class="font-medium text-vc-pink">Empleados</span>
            </nav>
        </div>

        <!-- Título + acciones -->
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Empleados</h1>
                <p class="mt-1 text-sm sm:text-base text-muted-ink">
                    Altas, expedientes y consultas · Gestión del personal.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                <!-- Barra de búsqueda -->
                <div class="relative">
                    <input id="searchInput" type="text"
                        class="w-full sm:w-72 rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                        placeholder="Buscar por nombre, CURP, RFC…" />
                    <span
                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-ink">Buscar</span>
                </div>
            </div>
        </section>

        <!-- Tabla -->
        <section class="mt-6">
            <div class="overflow-x-auto rounded-xl border border-black/10 bg-white/90 shadow-soft">
                <table class="min-w-full text-sm" id="tablaEmpleados">
                    <thead class="bg-slate-100/80 text-xs uppercase tracking-wide text-muted-ink">
                        <tr>
                            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="nombre">Nombre</th>
                            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="empresa_nombre">
                                Empresa</th>
                            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="nombre_area">Área</th>
                            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="nombre_puesto">Puesto
                            </th>
                            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="estado">Estado</th>
                            <th class="px-3 py-2 text-left cursor-pointer select-none" data-sort="fecha_ingreso">Fecha
                                ingreso</th>
                            <th class="px-3 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyEmpleados" class="align-top bg-white">
                        <!-- Filas generadas por JS -->
                    </tbody>
                </table>
            </div>

            <!-- Mensaje sin resultados -->
            <p id="emptyMessage" class="mt-3 text-sm text-muted-ink hidden">
                No se encontraron empleados que coincidan con tu búsqueda.
            </p>

            <!-- Estado + paginación -->
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
        // Datos recibidos desde PHP
        const empleadosData = <?= json_encode($empleados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?> || [];

        const rowsPerPage = 10;
        let currentPage = 1;
        let sortField = 'nombre';
        let sortDir = 'asc';
        let filteredData = [...empleadosData];

        const tbody = document.getElementById('tbodyEmpleados');
        const searchInput = document.getElementById('searchInput');
        const emptyMessage = document.getElementById('emptyMessage');
        const tableStatus = document.getElementById('tableStatus');
        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');
        const headerCells = document.querySelectorAll('th[data-sort]');

        const editBaseUrl = "<?= url('index.php?controller=empleado&action=edit') ?>";
        const showBaseUrl = "<?= url('index.php?controller=empleado&action=show') ?>";

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

            tbody.innerHTML = pageItems.map(emp => {
                const nombre = escapeHtml(emp.nombre);
                const empresa = escapeHtml(emp.empresa_nombre ?? '');
                const area = escapeHtml(emp.nombre_area ?? '');
                const puesto = escapeHtml(emp.nombre_puesto ?? '');
                const estado = escapeHtml(emp.estado ?? '');
                const fechaIngreso = escapeHtml(emp.fecha_ingreso ?? '');

                const estadoBadge = estado === 'ACTIVO'
                    ? '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>'
                    : '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Baja</span>';

                return `
          <tr class="border-t border-slate-200 hover:bg-slate-50">
            <td class="px-3 py-2 whitespace-nowrap font-medium">${nombre}</td>
            <td class="px-3 py-2 whitespace-nowrap text-xs">${empresa}</td>
            <td class="px-3 py-2 whitespace-nowrap text-xs">${area}</td>
            <td class="px-3 py-2 whitespace-nowrap text-xs">${puesto}</td>
            <td class="px-3 py-2 whitespace-nowrap">${estadoBadge}</td>
            <td class="px-3 py-2 whitespace-nowrap text-xs">${fechaIngreso}</td>
            <td class="px-3 py-2 whitespace-nowrap">
              <div class="flex gap-2 justify-center">
                <a
                  href="${showBaseUrl}&id=${emp.id_empleado}"
                  class="rounded-md border border-black/10 bg-white px-2 py-1 text-xs hover:bg-vc-teal/60"
                >
                  Ver
                </a>
                <a
                  href="${editBaseUrl}&id=${emp.id_empleado}"
                  class="rounded-md border border-black/10 bg-white px-2 py-1 text-xs hover:bg-vc-sand/60"
                >
                  Editar
                </a>
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
                filteredData = [...empleadosData];
            } else {
                filteredData = empleadosData.filter(emp =>
                    Object.values(emp).some(value =>
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

        // Render inicial
        renderTable();
    </script>
</body>

</html>