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
            <a href="<?= url('index.php') ?>" class="inline-flex items-center gap-2 bg-white border border-black/10 text-vc-ink px-4 py-2 rounded-lg font-bold hover:bg-gray-50 transition shadow-sm">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Generador de Reportes -->
    <div class="bg-white border border-black/10 rounded-xl p-6 mb-8 shadow-soft">
        <div class="flex items-center gap-2 mb-4 text-vc-ink font-bold border-b border-gray-100 pb-2">
            <svg class="w-5 h-5 text-vc-teal"><use href="#i-filter"/></svg>
            <h2 class="text-lg">Generar Reporte Personalizado</h2>
        </div>
        <form id="report-form" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-1">Tipo de Reporte</label>
                <select id="report-type" class="w-full h-11 rounded-lg border border-black/10 bg-gray-50 px-3 text-sm focus:outline-none focus:border-vc-teal focus:ring-1 focus:ring-vc-teal transition">
                    <option value="" disabled selected>Seleccionar...</option>
                    <option value="nomina">Nómina</option>
                    <option value="empleados">Empleados</option>
                    <option value="asistencias">Asistencias</option>
                    <option value="vacantes">Vacantes</option>
                    <option value="candidatos">Candidatos</option>
                    <option value="movimientos">Movimientos de Personal</option>
                    <option value="turnos">Turnos</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-1">Fecha Inicio</label>
                <input type="date" id="date-start" class="w-full h-11 rounded-lg border border-black/10 bg-gray-50 px-3 text-sm focus:outline-none focus:border-vc-teal transition">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-1">Fecha Fin</label>
                <input type="date" id="date-end" class="w-full h-11 rounded-lg border border-black/10 bg-gray-50 px-3 text-sm focus:outline-none focus:border-vc-teal transition">
            </div>
            <div>
                <button type="button" id="btn-download" class="w-full bg-vc-teal text-white hover:bg-teal-600 h-11 rounded-lg text-sm font-bold transition shadow-lg shadow-vc-teal/30 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5"><use href="#i-download"/></svg>
                    Descargar
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('btn-download').addEventListener('click', function() {
            const type = document.getElementById('report-type').value;
            const start = document.getElementById('date-start').value;
            const end = document.getElementById('date-end').value;

            if (!type) {
                alert('Por favor selecciona un tipo de reporte.');
                return;
            }

            // Construir URL
            let baseUrl = 'index.php?controller=reportes&action=' + type;
            
            if (start) baseUrl += '&fecha_inicio=' + start;
            if (end) baseUrl += '&fecha_fin=' + end;

            // Trigger download
            window.location.href = baseUrl;
        });
    </script>

    <!-- Lista de Reportes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Reporte Card: Empleados -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-pink/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-pink/30 group-hover:bg-vc-pink transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-pink/10 rounded-lg text-vc-pink">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>

            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Reporte de Empleados</h3>
            <p class="text-sm text-muted-ink mb-4">Listado general, altas, bajas y cambios de puesto.</p>
            <div class="flex gap-2">
                <a href="<?= url('index.php?controller=reportes&action=empleados') ?>" class="text-xs font-bold bg-vc-pink/10 text-vc-pink hover:bg-vc-pink hover:text-white px-3 py-2 rounded-lg transition flex items-center gap-1">
                    <svg class="w-4 h-4"><use href="#i-download"/></svg> CSV
                </a>
            </div>
        </div>

        <!-- Reporte Card: Nómina -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-teal/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-teal/30 group-hover:bg-vc-teal transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-teal/10 rounded-lg text-vc-teal">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>

            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Reporte de Nómina</h3>
            <p class="text-sm text-muted-ink mb-4">Resumen de pagos, deducciones y bonificaciones.</p>
            <div class="flex gap-2">
                <a href="<?= url('index.php?controller=reportes&action=nomina') ?>" class="text-xs font-bold bg-vc-teal/10 text-vc-teal hover:bg-vc-teal hover:text-white px-3 py-2 rounded-lg transition flex items-center gap-1">
                    <svg class="w-4 h-4"><use href="#i-download"/></svg> CSV
                </a>
            </div>
        </div>

        <!-- Reporte Card: Asistencias -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-peach/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-peach/30 group-hover:bg-vc-peach transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-peach/10 rounded-lg text-vc-peach">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>

            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Reporte de Asistencias</h3>
            <p class="text-sm text-muted-ink mb-4">Registro de entradas, salidas, retardos y faltas.</p>
            <div class="flex gap-2">
                <a href="<?= url('index.php?controller=reportes&action=asistencias') ?>" class="text-xs font-bold bg-vc-peach/10 text-vc-peach hover:bg-vc-peach hover:text-white px-3 py-2 rounded-lg transition flex items-center gap-1">
                    <svg class="w-4 h-4"><use href="#i-download"/></svg> CSV
                </a>
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
                <a href="<?= url('index.php?controller=reportes&action=vacantes') ?>" class="text-xs font-bold bg-vc-teal/10 text-vc-teal hover:bg-vc-teal hover:text-white px-3 py-2 rounded-lg transition flex items-center gap-1">
                    <svg class="w-4 h-4"><use href="#i-download"/></svg> Vacantes
                </a>
                <a href="<?= url('index.php?controller=reportes&action=candidatos') ?>" class="text-xs font-bold bg-vc-teal/10 text-vc-teal hover:bg-vc-teal hover:text-white px-3 py-2 rounded-lg transition flex items-center gap-1">
                    <svg class="w-4 h-4"><use href="#i-download"/></svg> Candidatos
                </a>
            </div>
        </div>



        <!-- Reporte Card: Turnos -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-vc-pink/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-vc-pink/30 group-hover:bg-vc-pink transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-vc-pink/10 rounded-lg text-vc-pink">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>

            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Turnos</h3>
            <p class="text-sm text-muted-ink mb-4">Catálogo de turnos y horarios.</p>
            <div class="flex gap-2">
                <a href="<?= url('index.php?controller=reportes&action=turnos') ?>" class="text-xs font-bold bg-vc-pink/10 text-vc-pink hover:bg-vc-pink hover:text-white px-3 py-2 rounded-lg transition flex items-center gap-1">
                    <svg class="w-4 h-4"><use href="#i-download"/></svg> CSV
                </a>
            </div>
        </div>

        <!-- Reporte Card: Movimientos -->
        <div class="group bg-white border border-black/10 rounded-xl p-5 hover:border-blue-500/50 transition shadow-sm hover:shadow-soft relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-500/30 group-hover:bg-blue-500 transition"></div>
            <div class="flex justify-between items-start mb-3">
                <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
                    <svg class="w-6 h-6"><use href="#i-chart"/></svg>
                </div>

            </div>
            <h3 class="font-display text-lg text-vc-ink mb-1">Movimientos de Personal</h3>
            <p class="text-sm text-muted-ink mb-4">Altas, bajas, cambios de área y puesto.</p>
            <div class="flex gap-2">
                <a href="<?= url('index.php?controller=reportes&action=movimientos') ?>" class="text-xs font-bold bg-blue-500/10 text-blue-500 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-lg transition flex items-center gap-1">
                    <svg class="w-4 h-4"><use href="#i-download"/></svg> CSV
                </a>
            </div>
        </div>

    </div>

  </main>
</body>
</html>
