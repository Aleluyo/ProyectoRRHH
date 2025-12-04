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

// $candidato viene desde CandidatoController::edit()
if (!isset($candidato) || !is_array($candidato)) {
    $candidato = [];
}

$errors = $errors ?? ($_SESSION['errors'] ?? []);
$old    = $old    ?? ($_SESSION['old_input'] ?? []);

function v_old_cand(string $key, array $old, array $candidato): string {
    if (array_key_exists($key, $old)) {
        return (string)$old[$key];
    }
    return isset($candidato[$key]) ? (string)$candidato[$key] : '';
}

$idCandidato = (int)($candidato['id_candidato'] ?? 0);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar candidato · Reclutamiento</title>
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

  <!-- Estilos -->
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
        <a href="<?= url('logout.php') ?>" class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <!-- Contenido -->
  <main class="mx-auto max-w-4xl px-4 sm:px-6 py-8 relative">
    <!-- Breadcrumb -->
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('views/reclutamiento/index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Reclutamiento y Selección
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('index.php?controller=candidato&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Candidatos
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">Editar candidato #<?= $idCandidato ?></span>
      </nav>
    </div>

    <!-- Título -->
    <section class="mb-6">
      <h1 class="vice-title text-[32px] leading-tight text-vc-ink">Editar candidato</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        Modifica los datos del candidato seleccionado.
      </p>
    </section>

    <!-- Formulario -->
    <section class="rounded-xl border border-black/10 bg-white/90 p-5 shadow-soft">
      <form method="post" action="<?= url('index.php?controller=candidato&action=update&id=' . $idCandidato) ?>" class="space-y-4">
        <!-- nombre -->
        <div>
          <label for="nombre" class="block text-sm font-medium text-vc-ink mb-1">Nombre completo</label>
          <input
            type="text"
            name="nombre"
            id="nombre"
            value="<?= htmlspecialchars(v_old_cand('nombre', $old, $candidato), ENT_QUOTES, 'UTF-8') ?>"
            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            required
          >
          <?php if (!empty($errors['nombre'])): ?>
            <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- correo -->
          <div>
            <label for="correo" class="block text-sm font-medium text-vc-ink mb-1">Correo electrónico</label>
            <input
              type="email"
              name="correo"
              id="correo"
              value="<?= htmlspecialchars(v_old_cand('correo', $old, $candidato), ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
            <?php if (!empty($errors['correo'])): ?>
              <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['correo'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>

          <!-- telefono -->
          <div>
            <label for="telefono" class="block text-sm font-medium text-vc-ink mb-1">Teléfono</label>
            <input
              type="text"
              name="telefono"
              id="telefono"
              value="<?= htmlspecialchars(v_old_cand('telefono', $old, $candidato), ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
            <?php if (!empty($errors['telefono'])): ?>
              <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['telefono'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>
        </div>

        <!-- fuente -->
        <div>
          <label for="fuente" class="block text-sm font-medium text-vc-ink mb-1">Fuente</label>
          <input
            type="text"
            name="fuente"
            id="fuente"
            value="<?= htmlspecialchars(v_old_cand('fuente', $old, $candidato), ENT_QUOTES, 'UTF-8') ?>"
            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          >
        </div>

        <!-- cv -->
        <div>
          <label for="cv" class="block text-sm font-medium text-vc-ink mb-1">
            CV <span class="text-xs text-muted-ink">(texto, resumen o link)</span>
          </label>
          <textarea
            name="cv"
            id="cv"
            rows="4"
            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          ><?= htmlspecialchars(v_old_cand('cv', $old, $candidato), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- Botones -->
        <div class="mt-4 flex flex-col sm:flex-row gap-3 sm:justify-end">
          <a
            href="<?= url('index.php?controller=candidato&action=index') ?>"
            class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm text-muted-ink hover:bg-slate-50"
          >
            Cancelar
          </a>
          <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-4 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
          >
            Guardar cambios
          </button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>