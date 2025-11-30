<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}

$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sleep(1);
    $res = AuthController::login($_POST['username'] ?? '', $_POST['password'] ?? '');
    if ($res['ok']) {
        $to = $_GET['redirect'] ?? url('index.php');
        header('Location: ' . $to);
        exit;
    } else {
        $err = implode("\n", $res['errors']);
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Iniciar sesión | RRHH</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            vc: {
              pink:  '#ff78b5',
              peach: '#ffc9a9',
              teal:  '#36d1cc',
              sand:  '#ffe9c7',
              ink:   '#0a2a5e',
              neon:  '#a7fffd'
            }
          },
          fontFamily: {
            display: ['Josefin Sans','system-ui','sans-serif'],
            sans:    ['DM Sans','system-ui','sans-serif'],
            vice:    ['Rage Italic','Yellowtail','cursive']
          },
          boxShadow: {
            soft: '0 12px 36px rgba(10,42,94,.10)'
          },
          backgroundImage: {
            miamiLight: 'linear-gradient(180deg, #fff 0%, rgba(255,233,199,.55) 40%, rgba(167,255,253,.25) 100%)',
            gridglow:   'radial-gradient(circle at 1px 1px, rgba(0,0,0,.08) 1px, transparent 1px)'
          }
        }
      }
    }
  </script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600;700&family=DM+Sans:wght@300;400;500;700&family=Yellowtail&display=swap" rel="stylesheet">

  <!-- Rage Italic (coloca tus archivos en /fonts/) -->
  <style>
    @font-face{
      font-family:'Rage Italic';
      src: url('/fonts/rage-italic.woff2') format('woff2'),
           url('/fonts/rage-italic.woff')  format('woff');
      font-weight:400; font-style:italic; font-display:swap;
    }
  </style>

  <style>
    :root{ --muted: rgba(10,42,94,.70); --ring: 0 0 0 3px rgba(54,209,204,.25); }
    .glass { background: rgba(255,255,255,.92); border: 1px solid rgba(0,0,0,.08); backdrop-filter: blur(8px); }
    .focus-ring { outline: none; box-shadow: var(--ring); }
    .spinner-vc{ width:16px;height:16px;border-radius:50%;border:2px solid rgba(10,42,94,.18);border-top-color:#36d1cc;animation:spin .8s linear infinite;display:inline-block;vertical-align:middle }
    @keyframes spin{ to{ transform: rotate(360deg);} }

    /* Título estilo “Vice City” */
    .vice-title{
      font-family: 'Rage Italic','Yellowtail',cursive;
      letter-spacing:.02em; line-height:.95;
      text-shadow: 0 1px 0 rgba(255,255,255,.85), 0 6px 18px rgba(10,42,94,.10);
      -webkit-text-stroke: 1px rgba(10,42,94,.35); text-stroke: 1px rgba(10,42,94,.35);
    }
    .dark .vice-title{
      -webkit-text-stroke: 0 transparent; text-stroke: 0 transparent;
      text-shadow: 0 0 2px #fff, 0 0 8px #a7fffd, 0 0 18px #a7fffd, 0 0 28px #36d1cc;
    }

    /* --- P3P Loader Styles --- */
    .loading-wrapper {
        position: fixed;
        inset: 0; /* Full screen overlay */
        background-color: rgba(255,255,255,0.92); /* White overlay */
        backdrop-filter: blur(8px);
        z-index: 9999;
        display: none; /* Oculto por defecto */
        
        /* Align content to bottom-right */
        align-items: flex-end;
        justify-content: flex-end;
        padding: 40px;
        
        /* Animation */
        opacity: 0;
        animation: fadeIn 0.4s ease-out forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .loading-content {
        display: flex;
        flex-direction: row-reverse;
        align-items: center;
        gap: 12px;
    }

    .loading-text {
        font-family: 'Josefin Sans', sans-serif;
        font-size: 1.2rem;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #0a2a5e; /* vc-ink */
        font-weight: 700;
        text-shadow: 0 2px 10px rgba(255,255,255,0.8);
    }

    .loading-dots { display: inline-flex; min-width: 1.5em; }
    .dot { width: 0.35em; text-align: center; }
    .dot-1 { animation: dot1 1.6s linear infinite; }
    .dot-2 { animation: dot2 1.6s linear infinite; }
    .dot-3 { animation: dot3 1.6s linear infinite; }

    @keyframes dot1 { 0%, 75% { opacity: 1; } 75.01%, 100% { opacity: 0; } }
    @keyframes dot2 { 0%, 24.99% { opacity: 0; } 25%, 75% { opacity: 1; } 75.01%, 100% { opacity: 0; } }
    @keyframes dot3 { 0%, 49.99% { opacity: 0; } 50%, 75% { opacity: 1; } 75.01%, 100% { opacity: 0; } }

    .card-pivot {
        width: 70px; /* Ajustado un poco para no ser gigante */
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        perspective: 800px;
    }

    .card-spin {
        width: 70px;
        height: 100px;
        transform-origin: center center;
        transform-style: preserve-3d;
        animation: spinY 1.1s linear infinite;
    }

    @keyframes spinY {
        0% { transform: rotateY(0deg); }
        50% { transform: rotateY(180deg); }
        100% { transform: rotateY(360deg); }
    }

    .card-diamond {
        width: 100%;
        height: 100%;
        position: relative;
        /* Gradiente Teal a Ink */
        background: linear-gradient(180deg, #36d1cc, #0a2a5e);
        box-shadow:
            0 0 10px rgba(10,42,94, 0.5),
            0 0 18px rgba(54,209,204, 0.5);
        clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    }
  </style>

  <!-- SweetAlert2 (ya lo usabas) -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen bg-miamiLight text-vc-ink font-sans relative">
  <!-- Fondo sutil -->
  <div class="absolute inset-0 bg-gridglow bg-[length:24px_24px] opacity-40 pointer-events-none"></div>

  <main class="relative min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
      <!-- CARD CUADRADO -->
      <div class="glass rounded-none shadow-soft overflow-hidden">
        <!-- Barra superior pastel -->
        <div class="h-1 w-full bg-gradient-to-r from-vc-pink via-vc-peach to-vc-teal"></div>

        <div class="p-7">
          <div class="text-center">
            <img src="<?= asset('img/galgovc.png') ?>" alt="Logo RRHH" class="mx-auto h-16 w-auto mb-3">
          </div>

          <!-- Título con Rage -->
          <h1 class="vice-title text-4xl text-center text-vc-ink">RRHH Access</h1>
          <p class="text-center text-xs mt-1 tracking-wide uppercase" style="color:var(--muted)">Acceso restringido • Personal autorizado</p>

          <form id="loginForm" method="POST" novalidate autocomplete="off" class="mt-5 space-y-4">
            <!-- Usuario -->
            <div>
              <label for="username" class="sr-only">Usuario</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-[color:var(--muted)]">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z"/><path d="M3 22a9 9 0 0 1 18 0"/>
                  </svg>
                </span>
                <input id="username" name="username" type="text" required autofocus
                  placeholder="Usuario"
                  class="w-full h-11 rounded-xl bg-white/90 border border-black/10 pl-10 pr-3 focus-ring" />
              </div>
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="sr-only">Contraseña</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-[color:var(--muted)]">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 1 1 8 0v4"/>
                  </svg>
                </span>
                <input id="password" name="password" type="password" required
                  placeholder="Contraseña"
                  class="w-full h-11 rounded-xl bg-white/90 border border-black/10 pl-10 pr-3 focus-ring" />
              </div>
            </div>

            <!-- Sin enlaces auxiliares -->
            <div class="h-1"></div>

            <button id="btnSubmit" type="submit"
              class="w-full h-11 rounded-md border border-black/10 bg-white text-vc-ink hover:bg-vc-teal/10 transition">
              <span class="btn-text">Entrar</span>
              <span class="btn-wait hidden"><span class="spinner-vc mr-2"></span>Validando…</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <!-- P3P Loader HTML -->
  <div id="p3-loader" class="loading-wrapper">
      <div class="loading-content">
          <div class="card-pivot">
              <div class="card-spin">
                  <div class="card-diamond"></div>
              </div>
          </div>
          <div class="loading-text">
              CARGANDO
              <span class="loading-dots">
                  <span class="dot dot-1">.</span>
                  <span class="dot dot-2">.</span>
                  <span class="dot dot-3">.</span>
              </span>
          </div>
      </div>
  </div>

  <?php if (isset($_GET['expired'])): ?>
  <script>
    Swal.fire({icon:'info', title:'Sesión expirada', text:'Vuelve a iniciar sesión.', timer:2200, showConfirmButton:false});
  </script>
  <?php endif; ?>

  <?php if (isset($_GET['loggedout'])): ?>
  <script>
    Swal.fire({icon:'success', title:'Sesión cerrada', text:'Has salido correctamente.', timer:1800, showConfirmButton:false});
  </script>
  <?php endif; ?>

  <?php if ($err): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: <?= json_encode($err, JSON_UNESCAPED_UNICODE) ?>,
      confirmButtonText: 'Entendido'
    }).then(()=>{ const p=document.getElementById('password'); if(p){ p.value=''; p.focus(); }});
  </script>
  <?php endif; ?>

  <script>
    // Spinner durante POST (Modificado para P3P)
    (function(){
      const f=document.getElementById('loginForm');
      const b=document.getElementById('btnSubmit');
      const l=document.getElementById('p3-loader');

      if(!f) return;
      
      f.addEventListener('submit',()=>{ 
          if(b) b.disabled=true; 
          // Mostrar loader P3P
          if(l) l.style.display = 'flex';
      });
    })();
  </script>
</body>
</html>
