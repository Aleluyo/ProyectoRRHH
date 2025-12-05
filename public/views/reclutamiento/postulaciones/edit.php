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

// Errores y datos anteriores (si venimos de un submit fallido)
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// Si no hay datos "old", precargamos con los valores de la postulación
if (empty($old) && isset($postulacion)) {
    $old = [
        'id_vacante'        => $postulacion['id_vacante']   ?? '',
        'id_candidato'      => $postulacion['id_candidato'] ?? '',
        'estado'            => $postulacion['estado']       ?? 'POSTULADO',
        'fecha_postulacion' => isset($postulacion['aplicada_en'])
            ? substr((string)$postulacion['aplicada_en'], 0, 10)
            : '',
        'comentarios'       => $postulacion['comentarios'] ?? '',
    ];
}

// Helper para escapar
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Catálogo de etapas
$etapas = [
    'POSTULADO'  => 'Postulado',
    'SCREENING'  => 'Screening',
    'ENTREVISTA' => 'Entrevista',
    'PRUEBA'     => 'Prueba',
    'OFERTA'     => 'Oferta',
    'RECHAZADO'  => 'Rechazado',
    'CONTRATADO' => 'Contratado',
];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar postulación · Reclutamiento y Selección</title>
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
    <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
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

  <!-- Contenido principal -->
  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">
    <!-- Breadcrumb -->
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('views/reclutamiento/index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Reclutamiento y Selección</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('index.php?controller=postulacion&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">Postulaciones</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">Editar postulación #<?= (int)($postulacion['id_postulacion'] ?? 0) ?></span>
      </nav>
    </div>

    <!-- Título -->
    <section class="mb-6">
      <h1 class="vice-title text-[32px] leading-tight text-vc-ink">Editar postulación</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        Actualiza la etapa o comentarios de la postulación seleccionada.
      </p>
    </section>

    <!-- Mensajes de error -->
    <?php if (!empty($errors)): ?>
      <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-semibold mb-1">Revisa la información:</p>
        <ul class="list-disc list-inside space-y-0.5">
          <?php foreach ($errors as $msg): ?>
            <li><?= e((string)$msg) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Formulario -->
    <section class="bg-white/90 border border-black/10 rounded-2xl shadow-soft p-6 sm:p-8">
      <form id="formPostulacion" method="post"
            action="<?= url('index.php?controller=postulacion&action=update&id=' . (int)$postulacion['id_postulacion']) ?>"
            class="space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Vacante -->
          <div>
            <label for="id_vacante" class="block text-sm font-medium text-vc-ink mb-1">
              Vacante <span class="text-rose-500">*</span>
            </label>
            <select
              id="id_vacante"
              name="id_vacante"
              required
              class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
              <option value="">Selecciona una vacante…</option>
              <?php foreach ($vacantes as $vac): ?>
                <?php
                  $idVac = (int)($vac['id_vacante'] ?? 0);
                  $label = $vac['etiqueta'] ?? ($vac['empresa_area_puesto'] ?? ($vac['descripcion'] ?? ('Vacante #' . $idVac)));
                  $selected = ((string)$old['id_vacante'] === (string)$idVac) ? 'selected' : '';
                ?>
                <option value="<?= $idVac ?>" <?= $selected ?>>
                  <?= e((string)$label) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Candidato -->
          <div>
            <label for="id_candidato" class="block text-sm font-medium text-vc-ink mb-1">
              Candidato <span class="text-rose-500">*</span>
            </label>
            <select
              id="id_candidato"
              name="id_candidato"
              required
              class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
              <option value="">Selecciona un candidato…</option>
              <?php foreach ($candidatos as $cand): ?>
                <?php
                  $idCand = (int)($cand['id_candidato'] ?? 0);
                  $nombre = $cand['nombre'] ?? ($cand['candidato_nombre'] ?? ('Candidato #' . $idCand));
                  $selected = ((string)$old['id_candidato'] === (string)$idCand) ? 'selected' : '';
                ?>
                <option value="<?= $idCand ?>" <?= $selected ?>>
                  <?= e((string)$nombre) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Etapa -->
          <div>
            <label for="estado" class="block text-sm font-medium text-vc-ink mb-1">
              Etapa <span class="text-rose-500">*</span>
            </label>
            <select
              id="estado"
              name="estado"
              required
              class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
              <?php foreach ($etapas as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= (strtoupper((string)$old['estado']) === $value) ? 'selected' : '' ?>>
                  <?= e($label) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Fecha de postulación -->
          <div>
            <label for="fecha_postulacion" class="block text-sm font-medium text-vc-ink mb-1">
              Fecha de postulación <span class="text-rose-500">*</span>
            </label>
            <input
              id="fecha_postulacion"
              name="fecha_postulacion"
              type="date"
              required
              value="<?= e((string)$old['fecha_postulacion']) ?>"
              class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            />
          </div>
        </div>

        <!-- Comentarios -->
        <div>
          <label for="comentarios" class="block text-sm font-medium text-vc-ink mb-1">
            Comentarios
          </label>
          <textarea
            id="comentarios"
            name="comentarios"
            rows="4"
            class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
          ><?= e((string)$old['comentarios']) ?></textarea>
          <p class="mt-1 text-xs text-muted-ink">
            Campo opcional para notas sobre la postulación.
          </p>
        </div>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-3">
          <a
            href="<?= url('index.php?controller=postulacion&action=index') ?>"
            class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm text-muted-ink hover:bg-slate-50"
          >
            Cancelar
          </a>
          <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-5 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
          >
            Guardar cambios
          </button>
        </div>
      </form>
    </section>
  </main>

  <script>
    // Validación rápida en cliente: no permitir campos obligatorios vacíos
    document.getElementById('formPostulacion').addEventListener('submit', function (e) {
      const vacante  = document.getElementById('id_vacante');
      const cand     = document.getElementById('id_candidato');
      const estado   = document.getElementById('estado');
      const fecha    = document.getElementById('fecha_postulacion');

      let mensajes = [];

      if (!vacante.value) mensajes.push('Debes seleccionar una vacante.');
      if (!cand.value)    mensajes.push('Debes seleccionar un candidato.');
      if (!estado.value)  mensajes.push('Debes seleccionar una etapa.');
      if (!fecha.value)   mensajes.push('Debes indicar la fecha de postulación.');

      if (mensajes.length > 0) {
        e.preventDefault();
        alert(mensajes.join('\\n'));
      }
    });
  </script>
</body>
</html>