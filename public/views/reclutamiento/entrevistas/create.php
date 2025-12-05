<?php
declare(strict_types=1);

// Se asume que el controlador ya cargó la configuración, sesión y Auth.
// require_once ... (redundante)

$area = htmlspecialchars($_SESSION['area'] ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// Asegura que $postulaciones siempre exista como array
if (!isset($postulaciones) || !is_array($postulaciones)) {
  $postulaciones = [];
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Nueva entrevista · Reclutamiento y Selección</title>
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

  <!-- Estilos propios -->
  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
  <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
</head>

<body class="min-h-screen bg-white text-vc-ink font-sans relative">

  <!-- Línea superior -->
  <div class="h-[1px] w-full bg-[image:linear-gradient(90deg,#ff78b5,#ffc9a9,#36d1cc)] opacity-70"></div>
  <div class="absolute inset-0 grid-bg opacity-15 pointer-events-none"></div>

  <!-- Header -->
  <header class="sticky top-0 z-30 border-b border-black/10 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 h-16 flex items-center">
      <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
        <img src="<?= asset('img/galgovc.png') ?>" alt="RRHH" class="h-9 w-auto">
        <div class="font-display text-lg tracking-widest uppercase text-vc-ink">RRHH</div>
      </a>
      <div class="ml-auto flex items-center gap-3 text-sm text-slate-500">
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
        <a href="<?= url('index.php') ?>" class="text-slate-500 hover:text-vc-ink transition">
          Inicio
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('views/reclutamiento/index.php') ?>" class="text-slate-500 hover:text-vc-ink transition">
          Reclutamiento y Selección
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('index.php?controller=entrevista&action=index') ?>"
          class="text-slate-500 hover:text-vc-ink transition">
          Entrevistas
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">Nueva entrevista</span>
      </nav>
    </div>

    <!-- Título -->
    <section class="mb-6">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Nueva entrevista</h1>
      <p class="mt-1 text-sm sm:text-base text-slate-500">
        Agenda una entrevista para una postulación y registra los detalles básicos.
      </p>
    </section>

    <!-- Formulario -->
    <section>
      <form id="formEntrevista" action="<?= url('index.php?controller=entrevista&action=store') ?>" method="post"
        class="space-y-6 bg-white/90 border border-black/5 rounded-2xl shadow-soft p-5 sm:p-6">

        <!-- Primera fila: Postulación / Fecha y hora -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="id_postulacion" class="block text-sm font-medium text-slate-700 mb-1">
              Postulación
            </label>
            <select name="id_postulacion" id="id_postulacion"
              class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
              <option value="">Selecciona una postulación...</option>
              <?php foreach ($postulaciones as $p): ?>
                <option value="<?= htmlspecialchars((string) $p['id'], ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars((string) $p['label'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="programada_para" class="block text-sm font-medium text-slate-700 mb-1">
              Fecha y hora
            </label>
            <input type="datetime-local" name="programada_para" id="programada_para"
              class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60" />
          </div>
        </div>

        <!-- Segunda fila: Tipo / Resultado -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="tipo" class="block text-sm font-medium text-slate-700 mb-1">
              Tipo de entrevista
            </label>
            <input type="text" name="tipo" id="tipo" placeholder="Ej. Telefónica, presencial, videollamada"
              class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60" />
          </div>

          <div>
            <label for="resultado" class="block text-sm font-medium text-slate-700 mb-1">
              Resultado
            </label>
            <select name="resultado" id="resultado"
              class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
              <option value="">Selecciona un resultado…</option>
              <option value="PENDIENTE">Pendiente</option>
              <option value="APROBADO">Aprobado</option>
              <option value="RECHAZADO">Rechazado</option>
            </select>
          </div>
        </div>

        <!-- Notas -->
        <div>
          <label for="notas" class="block text-sm font-medium text-slate-700 mb-1">
            Notas <span class="text-xs text-slate-400">(opcional)</span>
          </label>
          <textarea name="notas" id="notas" rows="4"
            class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"></textarea>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3 pt-2">
          <a href="<?= url('index.php?controller=entrevista&action=index') ?>"
            class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
            Cancelar
          </a>
          <button type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-4 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition">
            Guardar entrevista
          </button>
        </div>
      </form>
    </section>
  </main>

  <!-- Validación JS -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('formEntrevista');
      if (!form) return;

      form.addEventListener('submit', function (e) {
        const errores = [];

        const idPostulacion = (document.getElementById('id_postulacion')?.value || '').trim();
        const fechaHora = (document.getElementById('programada_para')?.value || '').trim();
        const tipo = (document.getElementById('tipo')?.value || '').trim();
        const resultado = (document.getElementById('resultado')?.value || '').trim();
        // notas es opcional

        if (!idPostulacion) {
          errores.push('Selecciona una postulación.');
        }
        if (!fechaHora) {
          errores.push('Ingresa la fecha y hora de la entrevista.');
        }
        if (!tipo) {
          errores.push('Indica el tipo de entrevista.');
        }
        if (!resultado) {
          errores.push('Selecciona el resultado (aunque sea Pendiente).');
        }

        if (errores.length > 0) {
          e.preventDefault();
          alert(errores.join('\n'));
        }
      });
    });
  </script>
</body>

</html>