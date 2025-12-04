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

$errors = $errors ?? ($_SESSION['errors'] ?? []);
$old    = $old    ?? ($_SESSION['old_input'] ?? []);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Nueva entrevista · Reclutamiento</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

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
          boxShadow:{ soft:'0 10px 28px rgba(10,42,94,.08)' },
          backgroundImage:{
            gridglow:'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
            ribbon:'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'
          }
        }
      }
    }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
</head>
<body class="min-h-screen bg-white text-vc-ink font-sans relative">
  <div class="h-[1px] w-full bg-[image:linear-gradient(90deg,#ff78b5,#ffc9a9,#36d1cc)] opacity-70"></div>
  <div class="absolute inset-0 grid-bg opacity-15 pointer-events-none"></div>

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

  <main class="mx-auto max-w-4xl px-4 sm:px-6 py-8 relative">
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        <a href="<?= url('views/reclutamiento/index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Reclutamiento y Selección</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        <a href="<?= url('index.php?controller=entrevista&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">Entrevistas</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        <span class="font-medium text-vc-pink">Nueva entrevista</span>
      </nav>
    </div>

    <section class="mb-6">
      <h1 class="vice-title text-[32px] leading-tight text-vc-ink">Nueva entrevista</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        Agenda una entrevista para una postulación y registra los detalles básicos.
      </p>
    </section>

    <section class="rounded-xl border border-black/10 bg-white/90 p-5 shadow-soft">
      <form method="post" action="<?= url('index.php?controller=entrevista&action=store') ?>" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="id_postulacion" class="block text-sm font-medium text-vc-ink mb-1">ID Postulación</label>
            <input type="number" name="id_postulacion" id="id_postulacion"
              value="<?= htmlspecialchars((string)($old['id_postulacion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              required>
          </div>

          <div>
            <label for="fecha_programada" class="block text-sm font-medium text-vc-ink mb-1">Fecha y hora</label>
            <input type="datetime-local" name="fecha_programada" id="fecha_programada"
              value="<?= htmlspecialchars((string)($old['fecha_programada'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              required>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="tipo" class="block text-sm font-medium text-vc-ink mb-1">Tipo</label>
            <input type="text" name="tipo" id="tipo"
              placeholder="Ej. Telefónica, Presencial, Videollamada"
              value="<?= htmlspecialchars((string)($old['tipo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
          </div>

          <div>
            <label for="resultado" class="block text-sm font-medium text-vc-ink mb-1">Resultado</label>
            <?php $resOld = strtoupper((string)($old['resultado'] ?? 'PENDIENTE')); ?>
            <select name="resultado" id="resultado"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
              <option value="PENDIENTE" <?= $resOld === 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
              <option value="APROBADO"  <?= $resOld === 'APROBADO'  ? 'selected' : '' ?>>Aprobado</option>
              <option value="RECHAZADO" <?= $resOld === 'RECHAZADO' ? 'selected' : '' ?>>Rechazado</option>
            </select>
          </div>
        </div>

        <div>
          <label for="notas" class="block text-sm font-medium text-vc-ink mb-1">Notas</label>
          <textarea name="notas" id="notas" rows="4"
            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          ><?= htmlspecialchars((string)($old['notas'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row gap-3 sm:justify-end">
          <a href="<?= url('index.php?controller=entrevista&action=index') ?>"
             class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm text-muted-ink hover:bg-slate-50">
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
</body>
</html>