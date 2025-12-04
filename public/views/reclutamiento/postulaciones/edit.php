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

// $postulacion viene desde PostulacionController::edit()
if (!isset($postulacion) || !is_array($postulacion)) {
    $postulacion = [];
}

$errors = $errors ?? ($_SESSION['errors'] ?? []);
$old    = $old    ?? ($_SESSION['old_input'] ?? []);

function v_old_post(string $key, array $old, array $postulacion): string {
    if (array_key_exists($key, $old)) return (string)$old[$key];
    return isset($postulacion[$key]) ? (string)$postulacion[$key] : '';
}

$idPostulacion = (int)($postulacion['id_postulacion'] ?? 0);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar postulación · Reclutamiento</title>
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
          boxShadow: { soft:'0 10px 28px rgba(10,42,94,.08)' },
          backgroundImage: {
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
        <a href="<?= url('index.php?controller=postulacion&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">Postulaciones</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        <span class="font-medium text-vc-pink">Editar postulación #<?= $idPostulacion ?></span>
      </nav>
    </div>

    <section class="mb-6">
      <h1 class="vice-title text-[32px] leading-tight text-vc-ink">Editar postulación</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        Actualiza la etapa o comentarios de la postulación seleccionada.
      </p>
    </section>

    <section class="rounded-xl border border-black/10 bg-white/90 p-5 shadow-soft">
      <form method="post" action="<?= url('index.php?controller=postulacion&action=update&id=' . $idPostulacion) ?>" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="id_vacante" class="block text-sm font-medium text-vc-ink mb-1">ID Vacante</label>
            <input type="number" name="id_vacante" id="id_vacante"
              value="<?= htmlspecialchars(v_old_post('id_vacante', $old, $postulacion), ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              required>
          </div>

          <div>
            <label for="id_candidato" class="block text-sm font-medium text-vc-ink mb-1">ID Candidato</label>
            <input type="number" name="id_candidato" id="id_candidato"
              value="<?= htmlspecialchars(v_old_post('id_candidato', $old, $postulacion), ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              required>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="etapa" class="block text-sm font-medium text-vc-ink mb-1">Etapa</label>
            <?php $etapaSel = strtoupper(v_old_post('etapa', $old, $postulacion)); ?>
            <select name="etapa" id="etapa"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
              <option value="SCREENING"  <?= $etapaSel === 'SCREENING'  ? 'selected' : '' ?>>Screening</option>
              <option value="ENTREVISTA" <?= $etapaSel === 'ENTREVISTA' ? 'selected' : '' ?>>Entrevista</option>
              <option value="PRUEBA"     <?= $etapaSel === 'PRUEBA'     ? 'selected' : '' ?>>Prueba</option>
              <option value="OFERTA"     <?= $etapaSel === 'OFERTA'     ? 'selected' : '' ?>>Oferta</option>
              <option value="CONTRATADO" <?= $etapaSel === 'CONTRATADO' ? 'selected' : '' ?>>Contratado</option>
              <option value="DESCARTADO" <?= $etapaSel === 'DESCARTADO' ? 'selected' : '' ?>>Descartado</option>
            </select>
          </div>

          <div>
            <label for="fecha_postulacion" class="block text-sm font-medium text-vc-ink mb-1">Fecha de postulación</label>
            <input type="date" name="fecha_postulacion" id="fecha_postulacion"
              value="<?= htmlspecialchars(v_old_post('fecha_postulacion', $old, $postulacion), ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
          </div>
        </div>

        <div>
          <label for="comentarios" class="block text-sm font-medium text-vc-ink mb-1">Comentarios</label>
          <textarea name="comentarios" id="comentarios" rows="4"
            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          ><?= htmlspecialchars(v_old_post('comentarios', $old, $postulacion), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row gap-3 sm:justify-end">
          <a href="<?= url('index.php?controller=postulacion&action=index') ?>"
             class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm text-muted-ink hover:bg-slate-50">
            Cancelar
          </a>
          <button type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-4 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition">
            Guardar cambios
          </button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>