<?php
require_once __DIR__ . '/../../../config/paths.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Nóminas | RRHH</title>
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
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600;700&family=DM+Sans:wght@300;400;500;700&family=Yellowtail&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
  <link rel="icon" href="<?= asset('img/galgovc.ico') ?>" type="image/x-icon">
</head>
<body class="min-h-screen bg-white text-vc-ink font-sans relative">

  <svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden">
    <!-- SVG Symbols omitted for brevity, adding common ones needed -->
    <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
    </symbol>
    <symbol id="i-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
        <circle cx="12" cy="12" r="3"></circle>
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
        <a href="<?= url('logout.php') ?>" class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">Cerrar sesión</a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="vice-title text-4xl text-vc-ink">Nóminas</h1>
            <p class="text-muted-ink mt-1">Gestión de periodos de pago y generación de recibos.</p>
        </div>
        <div>
             <a href="<?= url('index.php?controller=nomina&action=create') ?>" class="inline-flex items-center gap-2 bg-vc-teal text-white px-4 py-2 rounded-lg font-bold hover:bg-teal-500 transition shadow-lg shadow-vc-teal/30">
                <svg class="w-5 h-5"><use href="#i-plus"/></svg>
                Generar Nueva Nómina
             </a>
        </div>
    </div>

    <!-- Feedback Flash -->
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-100 text-red-600 text-sm font-medium">
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-100 text-green-600 text-sm font-medium">
            <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <!-- Tabla de Periodos -->
    <div class="bg-white border border-black/10 rounded-xl overflow-hidden shadow-soft">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-black/5 bg-gray-50/50 text-xs uppercase tracking-wider text-vc-ink/50">
                        <th class="px-6 py-4 font-bold">Periodo</th>
                        <th class="px-6 py-4 font-bold">Tipo</th>
                        <th class="px-6 py-4 font-bold">Fechas</th>
                        <th class="px-6 py-4 font-bold">Estado</th>
                        <th class="px-6 py-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 text-sm">
                    <?php if (empty($periodos)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No hay periodos de nómina registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($periodos as $p): ?>
                            <tr class="hover:bg-gray-50/50 transition duration-150">
                                <td class="px-6 py-4 font-medium text-vc-ink">
                                    #<?= $p['id_periodo'] ?> 
                                    <span class="text-xs text-gray-400 ml-1"><?= htmlspecialchars($p['empresa_nombre'] ?? '') ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-blue-50 text-blue-700 text-xs font-bold">
                                        <?= $p['tipo'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 capitalize">
                                    <?php require_once __DIR__ . '/../../helpers/dates.php'; ?>
                                    <?= fecha_es($p['fecha_inicio']) ?> - <?= fecha_es($p['fecha_fin']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($p['estado'] === 'ABIERTO'): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-emerald-50 text-emerald-700 text-xs font-bold dot-indicator relative pl-4">
                                            <span class="absolute left-1.5 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            ABIERTO
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-500 text-xs font-bold">
                                            CERRADO
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= url('index.php?controller=nomina&action=show&id=' . $p['id_periodo']) ?>" class="text-vc-teal hover:text-teal-600 font-bold text-xs inline-flex items-center gap-1">
                                        VER DETALLE
                                        <svg class="w-4 h-4"><use href="#i-eye"/></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

  </main>
</body>
</html>
