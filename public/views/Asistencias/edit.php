<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$area   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

$flashError = $_SESSION['flash_error'] ?? null;
$oldInput   = $_SESSION['old_input']   ?? [];
unset($_SESSION['flash_error'], $_SESSION['old_input']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Nueva asistencia · Entradas, salidas y faltas</title>
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
            sans: ['Josefin Sans','system-ui','sans-serif'],
            display: ['Yellowtail','system-ui','sans-serif'],
          },
          boxShadow: { soft:'0 18px 45px rgba(15,23,42,.12)' }
        }
      }
    }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600;700&family=Yellowtail&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
  <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">
    <?php if ($flashError): ?>
      <script>
        Swal.fire({
          icon: 'error',
          title: 'No se pudo guardar la asistencia',
          text: <?= json_encode($flashError, JSON_UNESCAPED_UNICODE) ?>,
          iconColor: '#ff78b5'
        });
      </script>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Inicio
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('index.php?controller=asistencia&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Asistencia
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">Nueva asistencia</span>
      </nav>
    </div>

    <!-- Título -->
    <section class="flex flex-col gap-2 mb-4">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Registrar asistencia</h1>
      <p class="text-sm sm:text-base text-muted-ink">
        Captura manual de entrada, salida o falta para un empleado.
      </p>
      <p class="text-xs text-muted-ink">
        (*) Campos obligatorios.
      </p>
    </section>

    <section class="mt-2">
      <div class="bg-white/95 rounded-xl border border-black/10 shadow-soft p-6 sm:p-8 relative z-10">
        <form method="POST" action="<?= url('index.php?controller=asistencia&action=store') ?>" class="space-y-6" id="formAsistencia">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-semibold text-vc-ink mb-1">
                Empleado <span class="text-red-500">*</span>
              </label>
              <select
                name="id_empleado"
                required
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
                <option value="">Selecciona…</option>
                <?php foreach ($empleados as $emp): ?>
                  <option value="<?= (int)$emp['id_empleado'] ?>"
                    <?= isset($oldInput['id_empleado']) && (int)$oldInput['id_empleado'] === (int)$emp['id_empleado'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-vc-ink mb-1">
                Fecha <span class="text-red-500">*</span>
              </label>
              <input
                type="date"
                name="fecha"
                required
                value="<?= htmlspecialchars($oldInput['fecha'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
            </div>
          </div>

          <fieldset class="border border-dashed border-black/15 rounded-lg p-4 sm:p-5">
            <legend class="px-2 text-xs font-semibold uppercase tracking-[0.16em] text-muted-ink">
              Horario del día
            </legend>

            <div class="mt-3 grid gap-4 sm:grid-cols-2" id="horasContainer">
              <div>
                <label class="block text-sm font-semibold text-vc-ink mb-1">
                  Hora de entrada
                </label>
                <input
                  type="time"
                  name="hora_entrada"
                  step="60"
                  value="<?= htmlspecialchars($oldInput['hora_entrada'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                  class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                >
              </div>
              <div>
                <label class="block text-sm font-semibold text-vc-ink mb-1">
                  Hora de salida
                </label>
                <input
                  type="time"
                  name="hora_salida"
                  step="60"
                  value="<?= htmlspecialchars($oldInput['hora_salida'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                  class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                >
              </div>
            </div>

            <div class="mt-4 flex items-center gap-2">
              <input
                type="checkbox"
                id="es_falta"
                name="es_falta"
                value="1"
                <?= isset($oldInput['es_falta']) && $oldInput['es_falta'] === '1' ? 'checked' : '' ?>
                class="h-4 w-4 rounded border-black/20 text-vc-teal focus:ring-vc-teal"
              >
              <label for="es_falta" class="text-sm text-muted-ink">
                Marcar el día como <strong>falta</strong> (ignora horas de entrada/salida).
              </label>
            </div>
          </fieldset>

          <div>
            <label class="block text-sm font-semibold text-vc-ink mb-1">
              Observaciones
            </label>
            <textarea
              name="observaciones"
              rows="3"
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            ><?= htmlspecialchars($oldInput['observaciones'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <a
              href="<?= url('index.php?controller=asistencia&action=index') ?>"
              class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm font-medium text-muted-ink hover:bg-slate-50 transition"
            >
              Cancelar
            </a>
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-5 py-2 text-sm font-semibold text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
            >
              Guardar asistencia
            </button>
          </div>
        </form>
      </div>
    </section>
  </main>

  <script>
    const chkFalta = document.getElementById('es_falta');
    const horasContainer = document.getElementById('horasContainer');

    function toggleHoras() {
      const disabled = chkFalta.checked;
      horasContainer.querySelectorAll('input[type="time"]').forEach(inp => {
        inp.disabled = disabled;
        if (disabled) inp.value = '';
      });
    }

    chkFalta.addEventListener('change', toggleHoras);
    toggleHoras();
  </script>
</body>
</html>
