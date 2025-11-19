<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
require_once __DIR__ . '/../../../config/db.php'; //QUITAR

requireLogin();
requireRole(1);

$area   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

/** Submódulos administrativos del área de Empresas */
$submodules = [
  ['title' => 'Empresas', 'sub' => 'Razón social, RFC y contacto',  'icon' => 'bi-building',          'href' => url('index.php?controller=empresa&action=index')],
  ['title' => 'Áreas',                'sub' => 'Organización interna',          'icon' => 'bi-diagram-3',         'href' => url('index.php?controller=area&action=index')],
  ['title' => 'Puestos',              'sub' => 'Cargos y descripciones',        'icon' => 'bi-person-lines-fill', 'href' => url('index.php?controller=puesto&action=index')],
  ['title' => 'Ubicaciones',          'sub' => 'Sucursales y sedes',            'icon' => 'bi-geo-alt',           'href' => url('index.php?controller=ubicacion&action=index')],
  ['title' => 'Turnos',               'sub' => 'Horarios laborales',            'icon' => 'bi-clock',             'href' => url('views/organizacional/turnos/list.php')],
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Administración organizacional - RRHH</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

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
          boxShadow: { soft:'0 10px 28px rgba(10,42,94,.08)' },
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

  <!-- Bootstrap Icons para los bi-* -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

</head>
<body class="min-h-screen bg-white text-vc-ink font-sans relative">

  <!-- Línea de color y fondo tipo grid -->
  <div class="h-[1px] w-full bg-[image:linear-gradient(90deg,#ff78b5,#ffc9a9,#36d1cc)] opacity-70"></div>
  <div class="absolute inset-0 grid-bg opacity-15 pointer-events-none"></div>

  <!-- Header reutilizado -->
  <header class="sticky top-0 z-30 border-b border-black/10 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 h-16 flex items-center">
      <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
        <img src="<?= asset('img/galgovc.png') ?>" alt="RRHH" class="h-9 w-auto">
        <div class="font-display text-lg tracking-widest uppercase text-vc-ink">RRHH</div>
      </a>
      <div class="ml-auto flex items-center gap-3 text-sm text-muted-ink">
        <span class="hidden sm:inline-block truncate max-w-[200px]">
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
      <span class="font-medium text-vc-pink">Administración Organizacional</span>
    </nav>
  </div>
    <!-- Encabezado del módulo -->
    <section class="text-center mb-7">
      <h1 class="vice-title text-[40px] leading-tight text-vc-ink">Administración Organizacional</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        Bienvenido, ¿Qué desea realizar? 
      </p>
    </section>

    <!-- Tarjetas de submódulos -->
    <section aria-label="Submódulos de Estructura Organizacional" class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <?php foreach ($submodules as $m):
        $tag = $m['tag'] ?? 'pink';
        $accent = [
          'pink'  => 'bg-vc-pink/30 group-hover:bg-vc-pink/50',
          'teal'  => 'bg-vc-teal/30 group-hover:bg-vc-teal/50',
          'peach' => 'bg-vc-peach/40 group-hover:bg-vc-peach/60',
          'sand'  => 'bg-vc-sand/50 group-hover:bg-vc-sand/70',
        ][$tag] ?? 'bg-vc-teal/30 group-hover:bg-vc-teal/50';
      ?>
        <a href="<?= $m['href'] ?>" class="group relative rounded-xl border border-black/5 bg-white/80 px-4 py-4 flex items-center gap-4 hover:border-vc-teal/30 transition shadow-soft">
          <span class="absolute left-0 top-0 h-full w-1.5 rounded-l-xl <?= $accent ?>"></span>

          <div class="shrink-0 rounded-lg border border-black/10 bg-white/80 p-3 text-vc-ink flex items-center justify-center">
            <!-- Icono Bootstrap -->
            <i class="bi <?= htmlspecialchars($m['icon'], ENT_QUOTES, 'UTF-8') ?> text-xl"></i>
          </div>

          <div class="min-w-0">
            <h3 class="font-display text-lg text-vc-ink truncate">
              <?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?>
            </h3>
            <p class="text-sm truncate text-muted-ink">
              <?= htmlspecialchars($m['sub'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>

          <div class="ml-auto opacity-0 group-hover:opacity-100 transition text-vc-ink/70">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M9 6l6 6-6 6"/>
            </svg>
          </div>
        </a>
      <?php endforeach; ?>
    </section>
  </main>
</body>
</html>