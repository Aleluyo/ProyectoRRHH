<?php
// Asegurar que tenemos acceso a helpers si no se cargaron antes (aunque index.php público ya los carga)
require_once __DIR__ . '/../../../config/paths.php';
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Reportes | RRHH</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

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
          boxShadow: { soft: '0 10px 28px rgba(10,42,94,.08)' },
          backgroundImage: {
            gridglow: 'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
            ribbon: 'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'
          }
        }
      }
    }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600;700&family=DM+Sans:wght@300;400;500;700&family=Yellowtail&display=swap"
    rel="stylesheet">

  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
  <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
</head>

<body class="min-h-screen bg-white text-vc-ink font-sans relative">

  <!-- SVG Symbols (Reused from index.php) -->
  <svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden">
    <symbol id="i-chart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M3 3v18h18" />
      <rect x="7" y="13" width="3" height="5" />
      <rect x="12" y="9" width="3" height="9" />
      <rect x="17" y="5" width="3" height="13" />
    </symbol>
    <symbol id="i-download" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
        <polyline points="7 10 12 15 17 10" />
        <line x1="12" y1="15" x2="12" y2="3" />
    </symbol>
    <symbol id="i-filter" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
    </symbol>
  </svg>

  <div class="h-[1px] w-full bg-[image:linear-gradient(90deg,#ff78b5,#ffc9a9,#36d1cc)] opacity-70"></div>
  <div class="absolute inset-0 grid-bg opacity-15 pointer-events-none"></div>

  <header class="sticky top-0 z-30 border-b border-black/10 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 h-16 flex items-center">
      <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
        <img src="<?= asset('img/galgovc.png') ?>" alt="RRHH" class="h-9 w-auto">
        <div class="font-display text-lg tracking-widest uppercase text-vc-ink">RRHH</div>
      </a>
      <div class="ml-auto flex items-center gap-4">
        <a href="<?= url('index.php') ?>" class="text-sm font-medium text-vc-ink/70 hover:text-vc-ink transition">Inicio</a>
        <a href="<?= url('logout.php') ?>"
          class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">Cerrar
          sesión</a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="vice-title text-4xl text-vc-ink">Reportes y Exportaciones</h1>
            <p class="text-muted-ink mt-1">Generación y descarga de información del sistema.</p>
        </div>
        <div class="flex gap-2">
            <!-- Placeholder for global actions -->
        </div>
    </div>

    <!-- Filtros (Placeholder) -->
    <div class="bg-white border border-black/10 rounded-xl p-5 mb-8 shadow-soft">
        <div class="flex items-center gap-2 mb-4 text-vc-ink font-bold">
            <svg class="w-5 h-5"><use href="#i-filter"/></svg>
            <h2>Filtros Avanzados</h2>
        </div>
        <form class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-1">Módulo</label>
                <select class="w-full h-10 rounded-lg border border-black/10 bg-gray-50 px-3 text-sm focus:outline-none focus:border-vc-teal">
                    <option>Todos</option>
                    <option>Empleados</option>
                    <option>Nómina</option>
                    <option>Asistencias</option>
                    <option>Reclutamiento</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-1">Fecha Inicio</label>
                <input type="date" class="w-full h-10 rounded-lg border border-black/10 bg-gray-50 px-3 text-sm focus:outline-none focus:border-vc-teal">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-1">Fecha Fin</label>
                <input type="date" class="w-full h-10 rounded-lg border border-black/10 bg-gray-50 px-3 text-sm focus:outline-none focus:border-vc-teal">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="button" class="bg-vc-teal/10 text-vc-teal hover:bg-vc-teal/20 px-4 py-2 rounded-lg text-sm font-bold transition">
                    Aplicar Filtros
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Reportes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Reporte Card: Empleados -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-pink/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-pink/30 group-hover:bg-vc-pink transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-pink/10 rounded-lg text-vc-pink">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>
                <a href="<?= url('index.php?controller=reportes&action=empleados') ?>" class="text-gray-400 hover:text-vc-ink transition" title="Exportar CSV">
                    <svg class="w-5 h-5"><use href="#i-download"/></svg>
                </a>
            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Reporte de Empleados</h3>
            <p class="text-sm text-muted-ink mb-4">Listado general, altas, bajas y cambios de puesto.</p>
            <div class="flex gap-2 text-xs font-bold text-vc-ink/50">
                <span class="bg-gray-100 px-2 py-1 rounded">CSV</span>
                <span class="bg-gray-100 px-2 py-1 rounded">PDF</span>
            </div>
        </div>

        <!-- Reporte Card: Nómina -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-teal/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-teal/30 group-hover:bg-vc-teal transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-teal/10 rounded-lg text-vc-teal">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>
                <a href="<?= url('index.php?controller=reportes&action=nomina') ?>" class="text-gray-400 hover:text-vc-ink transition" title="Exportar CSV">
                    <svg class="w-5 h-5"><use href="#i-download"/></svg>
                </a>
            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Reporte de Nómina</h3>
            <p class="text-sm text-muted-ink mb-4">Resumen de pagos, deducciones y bonificaciones.</p>
            <div class="flex gap-2 text-xs font-bold text-vc-ink/50">
                <span class="bg-gray-100 px-2 py-1 rounded">CSV</span>
            </div>
        </div>

        <!-- Reporte Card: Asistencias -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-peach/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-peach/30 group-hover:bg-vc-peach transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-peach/10 rounded-lg text-vc-peach">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>
                <a href="<?= url('index.php?controller=reportes&action=asistencias') ?>" class="text-gray-400 hover:text-vc-ink transition" title="Exportar CSV">
                    <svg class="w-5 h-5"><use href="#i-download"/></svg>
                </a>
            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Reporte de Asistencias</h3>
            <p class="text-sm text-muted-ink mb-4">Registro de entradas, salidas, retardos y faltas.</p>
            <div class="flex gap-2 text-xs font-bold text-vc-ink/50">
                <span class="bg-gray-100 px-2 py-1 rounded">CSV</span>
                <span class="bg-gray-100 px-2 py-1 rounded">XLSX</span>
            </div>
        </div>

    </div>

    <h2 class="text-xl font-display text-vc-ink mt-8 mb-4">Otros Reportes</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Reporte Card: Reclutamiento -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-neon/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-neon/30 group-hover:bg-vc-neon transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-neon/10 rounded-lg text-vc-teal">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>
            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Reclutamiento</h3>
            <p class="text-sm text-muted-ink mb-4">Vacantes y Candidatos.</p>
            <div class="flex gap-2">
                <a href="<?= url('index.php?controller=reportes&action=vacantes') ?>" class="text-xs font-bold bg-gray-100 hover:bg-vc-neon/20 px-2 py-1 rounded transition">Vacantes CSV</a>
                <a href="<?= url('index.php?controller=reportes&action=candidatos') ?>" class="text-xs font-bold bg-gray-100 hover:bg-vc-neon/20 px-2 py-1 rounded transition">Candidatos CSV</a>
            </div>
        </div>

        <!-- Reporte Card: Organizacional -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-ink/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-ink/30 group-hover:bg-vc-ink transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-ink/10 rounded-lg text-vc-ink">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>
            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Estructura</h3>
            <p class="text-sm text-muted-ink mb-4">Áreas, Puestos y Ubicaciones.</p>
            <div class="flex flex-wrap gap-2">
                <a href="<?= url('index.php?controller=reportes&action=areas') ?>" class="text-xs font-bold bg-gray-100 hover:bg-vc-ink/10 px-2 py-1 rounded transition">Áreas</a>
                <a href="<?= url('index.php?controller=reportes&action=puestos') ?>" class="text-xs font-bold bg-gray-100 hover:bg-vc-ink/10 px-2 py-1 rounded transition">Puestos</a>
                <a href="<?= url('index.php?controller=reportes&action=ubicaciones') ?>" class="text-xs font-bold bg-gray-100 hover:bg-vc-ink/10 px-2 py-1 rounded transition">Ubicaciones</a>
            </div>
        </div>

        <!-- Reporte Card: Turnos -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-pink/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-pink/30 group-hover:bg-vc-pink transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-pink/10 rounded-lg text-vc-pink">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>
                <a href="<?= url('index.php?controller=reportes&action=turnos') ?>" class="text-gray-400 hover:text-vc-ink transition" title="Exportar CSV">
                    <svg class="w-5 h-5"><use href="#i-download"/></svg>
                </a>
            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Turnos</h3>
            <p class="text-sm text-muted-ink mb-4">Catálogo de turnos y horarios.</p>
            <div class="flex gap-2 text-xs font-bold text-vc-ink/50">
                <span class="bg-gray-100 px-2 py-1 rounded">CSV</span>
            </div>
        </div>

    </div>

  </main>
</body>
</html>
