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

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Nuevo turno · Administración organizacional</title>
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
            sans: ['Josefin Sans','system-ui','-apple-system','BlinkMacSystemFont','Segoe UI','sans-serif'],
            display: ['Yellowtail','system-ui','-apple-system','BlinkMacSystemFont','Segoe UI','sans-serif'],
          },
          boxShadow: {
            soft: '0 18px 45px rgba(15,23,42,.12)'
          }
        }
      }
    };
  </script>

  <!-- Fuentes -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600;700&family=Yellowtail&display=swap" rel="stylesheet">

  <!-- Estilos Vice -->
  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <a
          href="<?= url('logout.php') ?>"
          class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink"
        >
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">

    <!-- Breadcrumb -->
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Inicio
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('views/organizacional/index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Administración Organizacional
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('index.php?controller=turno&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Turnos
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">
          Nuevo turno
        </span>
      </nav>
    </div>

    <!-- Título -->
    <section class="flex flex-col gap-2 mb-4">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Nuevo turno</h1>
      <p class="text-sm sm:text-base text-muted-ink">
        Define un turno laboral con su horario, tolerancia y días de trabajo.
      </p>
      <p class="text-xs text-muted-ink">
        (*) Campos obligatorios.
      </p>
    </section>

    <!-- Formulario -->
    <section class="mt-2">
      <div class="bg-white/95 rounded-xl border border-black/10 shadow-soft p-6 sm:p-8 relative z-10">
        <form id="formTurno" method="POST" action="<?= url('index.php?controller=turno&action=store') ?>" class="space-y-6">
          <!-- Nombre turno -->
          <div>
            <label for="nombre_turno" class="block text-sm font-semibold text-vc-ink mb-1">
              Nombre del turno <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="nombre_turno"
              name="nombre_turno"
              maxlength="60"
              required
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
          </div>

          <!-- Horas -->
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label for="hora_entrada" class="block text-sm font-semibold text-vc-ink mb-1">
                Hora de entrada <span class="text-red-500">*</span>
              </label>
              <input
                type="time"
                id="hora_entrada"
                name="hora_entrada"
                required
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
            </div>

            <div>
              <label for="hora_salida" class="block text-sm font-semibold text-vc-ink mb-1">
                Hora de salida <span class="text-red-500">*</span>
              </label>
              <input
                type="time"
                id="hora_salida"
                name="hora_salida"
                required
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
            </div>
          </div>

          <!-- Tolerancia -->
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label for="tolerancia_minutos" class="block text-sm font-semibold text-vc-ink mb-1">
                Tolerancia (minutos) <span class="text-red-500">*</span>
              </label>
              <input
                type="number"
                id="tolerancia_minutos"
                name="tolerancia_minutos"
                min="0"
                max="1440"
                value="10"
                required
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
              <p class="mt-1 text-xs text-muted-ink">
                Minutos permitidos de retraso sin considerarse falta.
              </p>
            </div>
          </div>

          <!-- Días laborales -->
          <fieldset class="border border-dashed border-black/15 rounded-lg p-4 sm:p-5">
            <legend class="px-2 text-xs font-semibold uppercase tracking-[0.16em] text-muted-ink">
              Días laborales <span class="text-red-500">*</span>
            </legend>

            <p class="mt-2 text-xs text-muted-ink">
              Selecciona los días en que aplica este turno.
            </p>

            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm">
              <label class="inline-flex items-center gap-2">
                <input
                  type="checkbox"
                  name="dias_laborales[]"
                  value="L"
                  class="rounded border-black/20 text-vc-teal focus:ring-vc-teal"
                  checked
                >
                <span>Lunes (L)</span>
              </label>

              <label class="inline-flex items-center gap-2">
                <input
                  type="checkbox"
                  name="dias_laborales[]"
                  value="M"
                  class="rounded border-black/20 text-vc-teal focus:ring-vc-teal"
                  checked
                >
                <span>Martes (M)</span>
              </label>

              <label class="inline-flex items-center gap-2">
                <input
                  type="checkbox"
                  name="dias_laborales[]"
                  value="X"
                  class="rounded border-black/20 text-vc-teal focus:ring-vc-teal"
                  checked
                >
                <span>Miércoles (X)</span>
              </label>

              <label class="inline-flex items-center gap-2">
                <input
                  type="checkbox"
                  name="dias_laborales[]"
                  value="J"
                  class="rounded border-black/20 text-vc-teal focus:ring-vc-teal"
                  checked
                >
                <span>Jueves (J)</span>
              </label>

              <label class="inline-flex items-center gap-2">
                <input
                  type="checkbox"
                  name="dias_laborales[]"
                  value="V"
                  class="rounded border-black/20 text-vc-teal focus:ring-vc-teal"
                  checked
                >
                <span>Viernes (V)</span>
              </label>

              <label class="inline-flex items-center gap-2">
                <input
                  type="checkbox"
                  name="dias_laborales[]"
                  value="S"
                  class="rounded border-black/20 text-vc-teal focus:ring-vc-teal"
                >
                <span>Sábado (S)</span>
              </label>

              <label class="inline-flex items-center gap-2">
                <input
                  type="checkbox"
                  name="dias_laborales[]"
                  value="D"
                  class="rounded border-black/20 text-vc-teal focus:ring-vc-teal"
                >
                <span>Domingo (D)</span>
              </label>
            </div>
          </fieldset>

          <!-- Acciones -->
          <div class="flex justify-end gap-3 pt-2">
            <a
              href="<?= url('index.php?controller=turno&action=index') ?>"
              class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm font-medium text-muted-ink hover:bg-slate-50 transition"
            >
              Cancelar
            </a>
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-5 py-2 text-sm font-semibold text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
            >
              Guardar turno
            </button>
          </div>
        </form>
      </div>
    </section>
  </main>

  <script>
    const formTurno = document.getElementById('formTurno');

    formTurno.addEventListener('submit', function (e) {
      const hEntrada = document.getElementById('hora_entrada').value;
      const hSalida  = document.getElementById('hora_salida').value;
      const checks   = document.querySelectorAll('input[name="dias_laborales[]"]:checked');

      // Validación simple: entrada y salida no pueden ser iguales
      if (hEntrada && hSalida && hEntrada === hSalida) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning',
          title: 'Revisa el horario',
          text: 'La hora de entrada y la hora de salida no pueden ser iguales.',
          confirmButtonColor: '#36d1cc'
        });
        return;
      }

      // Debe haber al menos un día laboral
      if (checks.length === 0) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning',
          title: 'Selecciona días laborales',
          text: 'Debes seleccionar al menos un día laboral.',
          confirmButtonColor: '#36d1cc'
        });
        return;
      }
    });
  </script>
</body>
</html>
