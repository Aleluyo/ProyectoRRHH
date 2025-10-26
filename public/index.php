<?php
declare(strict_types=1);

/**
 * Dashboard RRHH — 2 columnas por fila (2–2–2–2), estilo Frutiger-Aero
 * Requisitos:
 *  - config/config.php: sesión/constantes
 *  - config/paths.php: helpers url(string), asset(string)
 *  - app/middleware/Auth.php: requireLogin()
 *  - config/db.php: función db(): PDO
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../config/db.php';

requireLogin();

$name   = htmlspecialchars(trim($_SESSION['username'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$rolId  = (int)($_SESSION['rol'] ?? 2);
$rolStr = $rolId === 1 ? 'Administrador' : 'Usuario';
$area   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

/**
 * Notificaciones: cotizaciones por vencer (<= 180 días)
 * Si la tabla no existe, se ignora sin romper la vista.
 */
$notifCount = 0;
try {
  $pdo = db();
  // Ajusta nombres de tabla/campos a tu esquema real
  $stmt = $pdo->query("
    SELECT COUNT(*) AS c
    FROM quotes q
    WHERE q.status IN ('ENVIADA','ACEPTADA','PENDIENTE')
      AND q.expiration_date IS NOT NULL
      AND DATEDIFF(q.expiration_date, CURRENT_DATE()) BETWEEN 0 AND 180
  ");
  $row = $stmt->fetch();
  $notifCount = (int)($row['c'] ?? 0);
} catch (Throwable $e) {
  $notifCount = 0;
}

/**
 * Definición de módulos (8 items exactos -> 2 por fila, 4 filas)
 */
$modules = [
  ['title' => 'Empleados',             'sub' => 'Altas, expedientes y consultas',  'icon' => 'bi-people',          'href' => url('views/empleados/list.php')],
  ['title' => 'Nómina',                'sub' => 'Recibos, cálculos y reportes',    'icon' => 'bi-cash-coin',       'href' => url('views/nomina/list.php')],
  ['title' => 'Asistencia',            'sub' => 'Entradas, salidas y faltas',      'icon' => 'bi-calendar-check',  'href' => url('views/asistencia/list.php')],
  ['title' => 'Permisos & Vacaciones', 'sub' => 'Gestión de ausencias',            'icon' => 'bi-calendar-plus',   'href' => url('views/permisos/list.php')],
  ['title' => 'Usuarios',              'sub' => 'Control y roles de acceso',       'icon' => 'bi-person-badge',    'href' => url('views/usuarios/list.php')],
  ['title' => 'Empresas',              'sub' => 'Catálogo y sucursales',           'icon' => 'bi-building',        'href' => url('views/empresas/list.php')],
  ['title' => 'Reportes',              'sub' => 'Indicadores y estadísticas',      'icon' => 'bi-bar-chart-line',  'href' => url('views/reportes/list.php')],
  ['title' => 'Configuración',         'sub' => 'Parámetros del sistema',          'icon' => 'bi-gear',            'href' => url('views/configuracion/index.php')],
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel RRHH</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS de terceros -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Fuentes -->
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,700&display=swap">

  <style>
    :root{
      --aero-cyan: #74e8e5;
      --aero-blue: #84b3ff;
      --aero-ink:  #1595c8;
      --aero-green:#66d1a6;
      --glass-bg:  rgba(255,255,255,0.9);
      --glass-brd: #bff7ee99;
      --text-ink:  #217a9b;
      --text-sec:  #178d72cc;
      --shadow-1:  0 6px 34px 0 #53efd640;
      --shadow-2:  0 16px 38px 0 #6fffe1;
      --ring:      0 0 0 3px #1595c855, 0 6px 34px 0 #53efd640;
    }
    @media (prefers-color-scheme: dark){
      :root{
        --glass-bg: rgba(20,24,28,0.8);
        --glass-brd: #0debd555;
        --text-ink: #a6e6ff;
        --text-sec: #8bd3b8;
      }
    }

    body{
      min-height: 100vh;
      font-family: 'Nunito','Segoe UI',Arial,sans-serif;
      letter-spacing: .01em;
      background: radial-gradient(ellipse at 60% 20%, #e0f3ff 0%, #a3e5c2 68%, #74b6ef 100%);
      overflow-x: hidden;
      position: relative;
    }
    @media (prefers-color-scheme: dark){
      body{
        background: radial-gradient(ellipse at 60% 20%, #0d2532 0%, #0a362f 68%, #0b2440 100%);
      }
    }

    /* Burbujas decorativas Aero */
    .bubble{
      position:absolute; border-radius:50%; opacity:.23; pointer-events:none;
      background: radial-gradient(circle at 40% 40%, #74b6ef80, #ffffff0c 75%);
      filter: blur(2px); animation: float 10s ease-in-out infinite;
      z-index:0;
    }
    .b1{ width:190px; height:190px; left:8vw; top:11vh; }
    .b2{ width:90px;  height:90px;  right:11vw; top:15vh; animation-delay:2s; }
    .b3{ width:140px; height:140px; left:30vw; bottom:6vh; animation-delay:3.6s; }
    .b4{ width:120px; height:120px; right:7vw; bottom:8vh; animation-delay:1.2s; }
    @keyframes float{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-20px)} }
    @media (prefers-reduced-motion: reduce){ .bubble{ animation:none } }

    /* Navbar vidrio */
    .navbar-aero{
      background: rgba(255,255,255,0.75);
      backdrop-filter: blur(9px);
      border-bottom: 2px solid #82e9ee22;
      box-shadow: 0 4px 28px 0 #38b6ff18;
      position: sticky; top: 0; z-index: 10;
    }
    @media (prefers-color-scheme: dark){
      .navbar-aero{ background: rgba(16,22,26,0.65) }
    }
    .brand-name{
      color: var(--aero-ink);
      font-weight: 900; letter-spacing: 1.6px;
      text-shadow: 0 1px 0 #fff, 0 2px 4px #80e1d288;
    }
    .brand-logo{
      height:48px; width:auto; filter: drop-shadow(0 2px 12px #53efd6b0);
    }

    /* Botón de salir y campana */
    .btn-gradient{
      background: linear-gradient(90deg, var(--aero-cyan) 0%, var(--aero-blue) 100%);
      border: none;
      box-shadow: 0 2px 10px #84b3ff66;
    }
    .btn-gradient:hover{ filter: brightness(1.03) }
    .bell{
      position:relative; font-size:1.35rem; color:#24c6dc; cursor:pointer;
      filter: drop-shadow(0 2px 6px #74e6ff70);
    }
    .bell:hover{ color: var(--aero-ink) }
    .badge-dot{
      position:absolute; top:-2px; right:-2px; min-width:20px; height:20px;
      border-radius:10px; font-size:.72rem; background:#e53935; color:#fff;
      display:flex; align-items:center; justify-content:center;
      box-shadow: 0 0 0 2px #fff;
    }

    /* Cards de módulos */
    .card-link{ text-decoration:none; outline:none }
    .card-link:focus-visible .card-aero{ box-shadow: var(--ring) }
    .card-aero{
      min-height: 140px; display:flex; align-items:center; gap:18px;
      background: var(--glass-bg); border-radius: 22px; border: 2px solid var(--glass-brd);
      box-shadow: var(--shadow-1);
      padding: 24px 20px; position:relative; overflow:hidden; transition: all .15s ease;
      z-index:1;
    }
    .card-aero::before{
      content:""; position:absolute; top:-60px; right:-70px; width:120px; height:120px;
      background: linear-gradient(135deg, var(--aero-cyan) 0%, var(--aero-blue) 100%);
      opacity:.13; border-radius:50%; z-index:0;
    }
    .card-aero:hover{
      transform: translateY(-3px) scale(1.02);
      box-shadow: var(--shadow-2);
      border-color: #19bae7cc;
      background: rgba(255,255,255,0.97);
    }
    @media (prefers-color-scheme: dark){
      .card-aero:hover{ background: rgba(22,26,30,0.9) }
    }
    .card-icon{
      font-size:2rem; color:#24c6dc; filter: drop-shadow(0 2px 6px #74e6ff70); z-index:1; transition: color .2s;
    }
    .card-aero:hover .card-icon{ color: var(--aero-ink) }
    .card-title{
      margin:0 0 .25rem 0; font-weight:900; color: var(--text-ink);
      letter-spacing:.4px; text-shadow: 0 1px 0 #fff, 0 2px 8px #afe3ff3c; z-index:1;
    }
    .card-sub{ margin:0; color: var(--text-sec); font-size:.98rem; z-index:1 }

    /* 2 columnas por fila en >= md; 1 col en < md */
    .grid-2-2 .col-item{ width:100% }
    @media (min-width: 768px){
      .grid-2-2{ display:flex; flex-wrap:wrap; gap:1.5rem }
      .grid-2-2 .col-item{
        flex: 0 0 calc(50% - .75rem); /* 2 columnas fijas */
      }
    }

    .welcome{ color:#1899c5; text-shadow:0 2px 12px #53efd633; letter-spacing:1px; }
  </style>
</head>
<body>
  <!-- Burbujas -->
  <div class="bubble b1"></div><div class="bubble b2"></div><div class="bubble b3"></div><div class="bubble b4"></div>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-aero">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('index.php') ?>">
        <img src="<?= asset('img/galgo.png') ?>" alt="RRHH" class="brand-logo" width="48" height="48" decoding="async" fetchpriority="high">
        <span class="brand-name">RRHH</span>
      </a>

      <div class="ms-auto d-flex align-items-center gap-3">
        <!-- Campana de notificaciones -->
        <a href="<?= url('views/quotes/alerts.php') ?>" class="position-relative" aria-label="Notificaciones de cotizaciones por vencer">
          <i class="bi bi-bell bell"></i>
          <?php if ($notifCount > 0): ?>
            <span class="badge-dot"><?= $notifCount ?></span>
          <?php endif; ?>
        </a>
        <span class="small text-secondary"><?= $name ?> (<?= $rolStr ?>)</span>
        <a href="<?= url('logout.php') ?>" class="btn btn-sm btn-gradient fw-semibold">Cerrar sesión</a>
      </div>
    </div>
  </nav>

  <main class="container py-5" style="position:relative; z-index:1;">
    <!-- Bienvenida -->
    <header class="text-center mb-5">
      <h2 class="fw-bold welcome">Bienvenido, <?= $name ?>.</h2>
      <p class="mb-0 text-secondary" style="font-size:1.05rem;">
        <?= $puesto ?><?= $area ? ' &mdash; ' . $area : '' ?><?= $ciudad ? ' &mdash; ' . $ciudad : '' ?>
      </p>
    </header>

    <!-- Grid 2–2–2–2 -->
    <section class="grid-2-2" aria-label="Módulos del sistema">
      <?php foreach ($modules as $m): ?>
        <div class="col-item">
          <a class="card-link" href="<?= $m['href'] ?>" aria-label="<?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="card-aero">
              <i class="bi <?= $m['icon'] ?> card-icon" aria-hidden="true"></i>
              <div>
                <h5 class="card-title"><?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?></h5>
                <p class="card-sub"><?= htmlspecialchars($m['sub'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
