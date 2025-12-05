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

$errors    = $errors    ?? [];
$candidato = $candidato ?? [];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar candidato · Reclutamiento y Selección</title>
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

  <!-- Estilos propios -->
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

  <!-- Contenido principal -->
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
        <a href="<?= url('index.php?controller=candidato&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Candidatos
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">
          Editar candidato #<?= htmlspecialchars((string)($candidato['id_candidato'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </span>
      </nav>
    </div>

    <!-- Título -->
    <section class="mb-6">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Editar candidato</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        Modifica los datos del candidato seleccionado.
      </p>
    </section>

    <!-- Mensajes de error generales -->
    <?php if (!empty($errors)): ?>
      <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <p class="font-semibold mb-1">Hay errores en el formulario:</p>
        <ul class="list-disc ml-5 space-y-0.5">
          <?php foreach ($errors as $msg): ?>
            <li><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Formulario -->
    <section class="relative rounded-xl border border-black/10 bg-white/90 p-5 shadow-soft">
      <form
        id="formCandidatoEdit"
        action="<?= url('index.php?controller=candidato&action=update&id=' . (int)($candidato['id_candidato'] ?? 0)) ?>"
        method="post"
        class="space-y-5"
      >
        <!-- Nombre completo -->
        <div>
          <label for="nombre" class="block text-sm font-medium text-vc-ink mb-1">
            Nombre completo
          </label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            value="<?= htmlspecialchars($candidato['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required
          />
          <p id="error-nombre" class="mt-1 text-xs text-rose-600">
            <?= isset($errors['nombre']) ? htmlspecialchars($errors['nombre'], ENT_QUOTES, 'UTF-8') : '' ?>
          </p>
        </div>

        <!-- Correo + Teléfono -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="correo" class="block text-sm font-medium text-vc-ink mb-1">
              Correo electrónico
            </label>
            <input
              type="email"
              id="correo"
              name="correo"
              class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              value="<?= htmlspecialchars($candidato['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              required
            />
            <p id="error-correo" class="mt-1 text-xs text-rose-600">
              <?= isset($errors['correo']) ? htmlspecialchars($errors['correo'], ENT_QUOTES, 'UTF-8') : '' ?>
            </p>
          </div>

          <div>
            <label for="telefono" class="block text-sm font-medium text-vc-ink mb-1">
              Teléfono
            </label>
            <input
              type="text"
              id="telefono"
              name="telefono"
              class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              value="<?= htmlspecialchars($candidato['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              required
            />
            <p id="error-telefono" class="mt-1 text-xs text-rose-600">
              <?= isset($errors['telefono']) ? htmlspecialchars($errors['telefono'], ENT_QUOTES, 'UTF-8') : '' ?>
            </p>
          </div>
        </div>

        <!-- Fuente -->
        <div>
          <label for="fuente" class="block text-sm font-medium text-vc-ink mb-1">
            Fuente <span class="text-xs text-muted-ink">(LinkedIn, Referido, Bolsa de trabajo…)</span>
          </label>
          <input
            type="text"
            id="fuente"
            name="fuente"
            class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            value="<?= htmlspecialchars($candidato['fuente'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          />
        </div>

        <!-- CV -->
        <div>
          <label for="cv" class="block text-sm font-medium text-vc-ink mb-1">
            CV <span class="text-xs text-muted-ink">(texto, resumen o link)</span>
          </label>
          <textarea
            id="cv"
            name="cv"
            rows="4"
            class="w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60 resize-y"
          ><?= htmlspecialchars($candidato['cv'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
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

  <!-- Validación en el navegador -->
  <script>
    (function () {
      const form      = document.getElementById('formCandidatoEdit');
      const nombreEl  = document.getElementById('nombre');
      const correoEl  = document.getElementById('correo');
      const telEl     = document.getElementById('telefono');

      const errNombre = document.getElementById('error-nombre');
      const errCorreo = document.getElementById('error-correo');
      const errTel    = document.getElementById('error-telefono');

      function clearErrors() {
        errNombre.textContent = '';
        errCorreo.textContent = '';
        errTel.textContent    = '';
      }

      form.addEventListener('submit', function (e) {
        clearErrors();
        let hasError = false;

        const nombre  = nombreEl.value.trim();
        const correo  = correoEl.value.trim();
        const telefono = telEl.value.trim();

        // Nombre: requerido, solo letras y espacios
        const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$/;
        if (!nombre) {
          errNombre.textContent = 'El nombre es obligatorio.';
          hasError = true;
        } else if (!nombreRegex.test(nombre)) {
          errNombre.textContent = 'El nombre solo puede contener letras y espacios.';
          hasError = true;
        }

        // Correo: requerido, formato sencillo de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!correo) {
          errCorreo.textContent = 'El correo electrónico es obligatorio.';
          hasError = true;
        } else if (!emailRegex.test(correo)) {
          errCorreo.textContent = 'El correo electrónico no tiene un formato válido.';
          hasError = true;
        }

        // Teléfono: requerido, solo números 8–15 dígitos
        const telRegex = /^[0-9]{8,15}$/;
        if (!telefono) {
          errTel.textContent = 'El teléfono es obligatorio.';
          hasError = true;
        } else if (!telRegex.test(telefono)) {
          errTel.textContent = 'El teléfono debe contener solo números (8 a 15 dígitos).';
          hasError = true;
        }

        if (hasError) {
          e.preventDefault();
        }
      });
    })();
  </script>
</body>
</html>