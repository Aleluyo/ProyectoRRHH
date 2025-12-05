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

$old    = $old    ?? [];
$errors = $errors ?? [];

function old_value(array $old, string $key): string {
    return htmlspecialchars($old[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Nuevo candidato · Reclutamiento y Selección</title>
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

  <main class="mx-auto max-w-5xl px-4 sm:px-6 py-8 relative">
    <!-- Breadcrumb -->
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Inicio
        </a>
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
        <span class="font-medium text-vc-pink">Nuevo candidato</span>
      </nav>
    </div>

    <!-- Título -->
    <section class="mb-7">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Nuevo candidato</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        Registra un nuevo candidato en el banco de CVs.
      </p>
    </section>

    <!-- Mensaje de error general -->
    <?php if (!empty($errors['general'])): ?>
      <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <section class="rounded-xl border border-black/10 bg-white/90 shadow-soft p-5 sm:p-6">
      <form
        action="<?= url('index.php?controller=candidato&action=create') ?>"
        method="post"
        class="space-y-5"
        novalidate
      >
        <!-- Nombre -->
        <div>
          <label for="nombre" class="block text-sm font-medium text-vc-ink">
            Nombre completo <span class="text-red-500">*</span>
          </label>
          <input
            id="nombre"
            name="nombre"
            type="text"
            required
            pattern="^[\p{L}\s]+$"
            title="Sólo letras y espacios."
            value="<?= old_value($old, 'nombre') ?>"
            class="mt-1 block w-full rounded-lg border <?= isset($errors['nombre']) ? 'border-red-400' : 'border-black/10' ?> bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          />
          <?php if (!empty($errors['nombre'])): ?>
            <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
        </div>

        <!-- Correo -->
        <div>
          <label for="correo" class="block text-sm font-medium text-vc-ink">
            Correo electrónico <span class="text-red-500">*</span>
          </label>
          <input
            id="correo"
            name="correo"
            type="email"
            required
            value="<?= old_value($old, 'correo') ?>"
            class="mt-1 block w-full rounded-lg border <?= isset($errors['correo']) ? 'border-red-400' : 'border-black/10' ?> bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          />
          <?php if (!empty($errors['correo'])): ?>
            <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['correo'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
        </div>

        <!-- Teléfono -->
        <div>
          <label for="telefono" class="block text-sm font-medium text-vc-ink">
            Teléfono <span class="text-red-500">*</span>
          </label>
          <input
            id="telefono"
            name="telefono"
            type="tel"
            required
            inputmode="numeric"
            pattern="^\d{8,15}$"
            minlength="8"
            maxlength="15"
            title="Sólo números, entre 8 y 15 dígitos."
            value="<?= old_value($old, 'telefono') ?>"
            class="mt-1 block w-full rounded-lg border <?= isset($errors['telefono']) ? 'border-red-400' : 'border-black/10' ?> bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          />
          <?php if (!empty($errors['telefono'])): ?>
            <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['telefono'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
        </div>

        <!-- Fuente -->
        <div>
          <label for="fuente" class="block text-sm font-medium text-vc-ink">
            Fuente <span class="text-xs text-muted-ink">(LinkedIn, Referido, Bolsa de trabajo…)</span>
          </label>
          <input
            id="fuente"
            name="fuente"
            type="text"
            value="<?= old_value($old, 'fuente') ?>"
            class="mt-1 block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          />
        </div>

        <!-- CV -->
        <div>
          <label for="cv" class="block text-sm font-medium text-vc-ink">
            CV <span class="text-xs text-muted-ink">(texto, resumen o link)</span>
          </label>
          <textarea
            id="cv"
            name="cv"
            rows="4"
            class="mt-1 block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          ><?= old_value($old, 'cv') ?></textarea>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3 pt-2">
          <a
            href="<?= url('index.php?controller=candidato&action=index') ?>"
            class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm text-vc-ink hover:bg-slate-50"
          >
            Cancelar
          </a>
          <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-5 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80"
          >
            Guardar candidato
          </button>
        </div>
      </form>
    </section>
  </main>

  <script>
    // Forzar sólo dígitos en el teléfono y limitar longitud
    const telInput = document.getElementById('telefono');
    if (telInput) {
      telInput.addEventListener('input', () => {
        telInput.value = telInput.value.replace(/\D/g, '').slice(0, 15);
      });
    }
  </script>
</body>
</html>