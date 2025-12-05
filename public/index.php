<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/controllers/EmpresaController.php';
require_once __DIR__ . '/../app/controllers/AreaController.php';
require_once __DIR__ . '/../app/controllers/PuestoController.php';
require_once __DIR__ . '/../app/controllers/UbicacionController.php';
require_once __DIR__ . '/../app/controllers/TurnoController.php';
// Controladores de Reclutamiento y Selección
require_once __DIR__ . '/../app/controllers/VacanteController.php';
require_once __DIR__ . '/../app/controllers/CandidatoController.php';
require_once __DIR__ . '/../app/controllers/PostulacionController.php';
require_once __DIR__ . '/../app/controllers/EntrevistaController.php';
//Controladores de Empleados
require_once __DIR__ . '/../app/controllers/EmpleadoController.php';
require_once __DIR__ . '/../app/controllers/MovimientoController.php';
require_once __DIR__ . '/../app/controllers/DocumentoController.php';

// Controladores de Asistencias
require_once __DIR__ . '/../app/controllers/AsistenciasController.php';
require_once __DIR__ . '/../app/controllers/PermisoController.php';
require_once __DIR__ . '/../app/controllers/ConfiguracionController.php';
require_once __DIR__ . '/../app/controllers/ReportesController.php';
require_once __DIR__ . '/../app/controllers/NominaController.php';

requireLogin();

// -----------------------------------------------------------------------------------------

// Obtiene el nombre del controlador y la acción desde los parámetros GET
$controllerName = $_GET['controller'] ?? null;
$actionName = $_GET['action'] ?? null;

// Si ambos parámetros están presentes, procesamos la solicitud
if ($controllerName !== null && $actionName !== null) {

  // Enrutamiento según el controlador solicitado
  switch ($controllerName) {
    case 'empresa':
      $controller = new EmpresaController();
      break;
    case 'area':
      $controller = new AreaController();
      break;
    case 'puesto':
      $controller = new PuestoController();
      break;
    case 'ubicacion':
      $controller = new UbicacionController();
      break;
    case 'turno':
      $controller = new TurnoController();
      break;
    // Reclutamiento y Selección
    case 'vacante':
      $controller = new VacanteController();
      break;
    case 'candidato':
      $controller = new CandidatoController();
      break;
    case 'postulacion':
      $controller = new PostulacionController();
      break;
    case 'entrevista':
      $controller = new EntrevistaController();
      break;
    //Empleados
    // Asistencias
    case 'asistencia':
      $controller = new AsistenciasController();
      break;
    // Empleados
    case 'empleado':
      $controller = new EmpleadoController();
      break;
    case 'movimiento':
      $controller = new MovimientoController();
      break;
    case 'documento':
      $controller = new DocumentoController();
      break;
    case 'configuracion':
      $controller = new ConfiguracionController();
      break;
    case 'reportes':
      $controller = new ReportesController();
      break;
    case 'nomina':
      $controller = new NominaController();
      break;
    case 'permiso':
      $controller = new PermisoController();
      break;
    default:
      http_response_code(404);
      echo "Controlador no encontrado";
      exit;
  }
  // Verifica que el método (acción) exista en el controlador
  if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    echo "Acción no encontrada";
    exit;
  }

  // Ejecuta la acción solicitada y finaliza el script sin renderizar la página de inicio
  $controller->{$actionName}();
  exit;
}
// -----------------------------------------------------------------------------------------



$area = htmlspecialchars($_SESSION['area'] ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

$modules = [
  ['title' => 'Empleados', 'sub' => 'Altas, expedientes y consultas', 'icon' => 'i-users', 'href' => url('index.php?controller=empleado&action=index'), 'tag' => 'pink'],
  // NUEVO módulo principal de Reclutamiento
  ['title' => 'Reclutamiento y Selección', 'sub' => 'Vacantes, candidatos y entrevistas', 'icon' => 'i-users', 'href' => url('views/reclutamiento/index.php'), 'tag' => 'teal'],
  ['title' => 'Nómina', 'sub' => 'Recibos, cálculos y reportes', 'icon' => 'i-cash', 'href' => url('index.php?controller=nomina&action=index'), 'tag' => 'teal'],
  ['title' => 'Asistencias', 'sub' => 'Entradas, salidas y faltas', 'icon' => 'i-cal-check', 'href' => url('index.php?controller=asistencia&action=index'), 'tag' => 'peach'],
  ['title' => 'Permisos & Vacaciones', 'sub' => 'Gestión de ausencias', 'icon' => 'i-cal-plus', 'href' => url('views/permisos/list.php'), 'tag' => 'sand'],

  ['title' => 'Empresas', 'sub' => 'Catálogo de áreas, puestos, ubicaciones y más', 'icon' => 'i-building', 'href' => url('views/organizacional/index.php'), 'tag' => 'pink'],
  ['title' => 'Reportes', 'sub' => 'Indicadores y estadísticas', 'icon' => 'i-chart', 'href' => url('index.php?controller=reportes&action=index'), 'tag' => 'peach'],
  ['title' => 'Configuración', 'sub' => 'Parámetros del sistema', 'icon' => 'i-gear', 'href' => url('index.php?controller=configuracion&action=index'), 'tag' => 'sand'],
];
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>IF7A - RRHH</title>
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

  <svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden">
    <symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M16 11a4 4 0 1 0-4-4" />
      <path d="M22 21a7 7 0 0 0-14 0" />
      <path d="M8 11a4 4 0 1 0-4-4" />
      <path d="M14 21a7 7 0 0 0-12 0" />
    </symbol>
    <symbol id="i-cash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <rect x="2" y="5" width="20" height="14" rx="2" />
      <circle cx="12" cy="12" r="3" />
    </symbol>
    <symbol id="i-cal-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <rect x="3" y="4" width="18" height="18" rx="2" />
      <path d="M16 2v4M8 2v4M3 10h18" />
      <path d="M9 15l2 2 4-4" />
    </symbol>
    <symbol id="i-cal-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <rect x="3" y="4" width="18" height="18" rx="2" />
      <path d="M16 2v4M8 2v4M3 10h18" />
      <path d="M12 14v6M9 17h6" />
    </symbol>
    <symbol id="i-badge" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <rect x="4" y="3" width="16" height="18" rx="2" />
      <path d="M8 7h8M8 11h8M8 15h5" />
    </symbol>
    <symbol id="i-building" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <rect x="3" y="3" width="18" height="18" rx="2" />
      <path d="M7 7h3v3H7zM14 7h3v3h-3zM7 14h3v3H7zM14 14h3v3h-3z" />
    </symbol>
    <symbol id="i-chart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M3 3v18h18" />
      <rect x="7" y="13" width="3" height="5" />
      <rect x="12" y="9" width="3" height="9" />
      <rect x="17" y="5" width="3" height="13" />
    </symbol>
    <symbol id="i-gear" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <circle cx="12" cy="12" r="3" />
      <path
        d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 1-2 0 1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-1.82.33l-.06.06A2 2 0 1 1 6.94 3.29l.06.06A1.65 1.65 0 0 0 8.6 4.6a1.65 1.65 0 0 0 1-.6 1.65 1.65 0 0 0 1 .6 1.65 1.65 0 0 0 1.82-.33l.06-.06A2 2 0 1 1 20.71 6.94l-.06.06A1.65 1.65 0 0 0 19.4 8.6a1.65 1.65 0 0 0 .6 1 1.65 1.65 0 0 1 0 2 1.65 1.65 0 0 0-.6 1z" />
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
      <div class="ml-auto">
        <a href="<?= url('logout.php') ?>"
          class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">Cerrar
          sesión</a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8">
    <section class="text-center mb-7">
      <h2 class="vice-title text-[40px] leading-tight text-vc-ink">¡Te damos la bienvenida!</h2>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        <?= $puesto ?><?= $area ? ' &mdash; ' . $area : '' ?><?= $ciudad ? ' &mdash; ' . $ciudad : '' ?>
      </p>
    </section>
    <section aria-label="Módulos del sistema" class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <?php foreach ($modules as $m):
        $tag = $m['tag'] ?? 'teal';
        $accent = [
          'pink' => 'bg-vc-pink/30 group-hover:bg-vc-pink/50',
          'teal' => 'bg-vc-teal/30 group-hover:bg-vc-teal/50',
          'peach' => 'bg-vc-peach/40 group-hover:bg-vc-peach/60',
          'sand' => 'bg-vc-sand/50 group-hover:bg-vc-sand/70',
        ][$tag] ?? 'bg-vc-teal/30 group-hover:bg-vc-teal/50';
        ?>
        <a href="<?= $m['href'] ?>"
          class="group relative rounded-xl border border-black/10 bg-white p-5 flex items-center gap-4 hover:border-vc-teal/30 transition shadow-soft">
          <span class="absolute left-0 top-0 h-full w-1.5 rounded-l-xl <?= $accent ?>"></span>
          <div class="shrink-0 rounded-lg border border-black/10 bg-white/80 p-3 text-vc-ink">
            <svg class="h-6 w-6">
              <use href="#<?= $m['icon'] ?>" />
            </svg>
          </div>
          <div class="min-w-0">
            <h3 class="font-display text-lg text-vc-ink truncate">
              <?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?>
            </h3>
            <p class="text-sm truncate text-muted-ink"><?= htmlspecialchars($m['sub'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div class="ml-auto opacity-0 group-hover:opacity-100 transition text-vc-ink/70">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M9 6l6 6-6 6" />
            </svg>
          </div>
        </a>
      <?php endforeach; ?>
    </section>
  </main>
</body>

</html>