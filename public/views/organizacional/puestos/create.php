<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

$areaSesion   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puestoSesion = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudadSesion = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// Lista de áreas recibidas desde el controlador (Empresa / Área)
$areasLista = (isset($areas) && is_array($areas)) ? $areas : [];

// Niveles de puesto (enum)
$niveles = ['OPERATIVO', 'SUPERVISOR', 'GERENCIAL', 'DIRECTIVO'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Nuevo puesto · Administración organizacional</title>
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
          <?= $puestoSesion ?><?= $areaSesion ? ' &mdash; ' . $areaSesion : '' ?><?= $ciudadSesion ? ' &mdash; ' . $ciudadSesion : '' ?>
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
        <a href="<?= url('index.php?controller=puesto&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Puestos
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">
          Nuevo puesto
        </span>
      </nav>
    </div>

    <!-- Título -->
    <section class="flex flex-col gap-2 mb-4">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Nuevo puesto</h1>
      <p class="text-sm sm:text-base text-muted-ink">
        Registra un puesto asociándolo a su empresa y área, definiendo nivel jerárquico y salario base referencial.
      </p>
      <p class="text-xs text-muted-ink">
        (*) Campos obligatorios.
      </p>
    </section>

    <!-- Formulario -->
    <section class="mt-2">
      <div class="bg-white/95 rounded-xl border border-black/10 shadow-soft p-6 sm:p-8 relative z-10">
        <form
          id="formPuesto"
          method="POST"
          action="<?= url('index.php?controller=puesto&action=store') ?>"
          class="space-y-6"
        >

          <!-- Empresa / Área -->
          <div>
            <label for="id_area" class="block text-sm font-semibold text-vc-ink mb-1">
              Empresa / Área <span class="text-red-500">*</span>
            </label>
            <select
              id="id_area"
              name="id_area"
              required
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              <?= empty($areasLista) ? 'disabled' : '' ?>
            >
              <option value="">Selecciona empresa / área…</option>
              <?php foreach ($areasLista as $a): ?>
                <?php
                  $idArea = (int)($a['id_area'] ?? 0);
                  if ($idArea <= 0) continue;

                  $nombreArea    = htmlspecialchars((string)($a['nombre_area']    ?? ('Área ' . $idArea)), ENT_QUOTES, 'UTF-8');
                  $nombreEmpresa = htmlspecialchars((string)($a['nombre_empresa'] ?? ''), ENT_QUOTES, 'UTF-8');

                  $label = ($nombreEmpresa ? $nombreEmpresa . ' · ' : '') . $nombreArea;
                ?>
                <option value="<?= htmlspecialchars((string)$idArea, ENT_QUOTES, 'UTF-8') ?>">
                  <?= $label ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (empty($areasLista)): ?>
              <p class="mt-1 text-xs text-red-500">
                No hay áreas registradas. Debes crear al menos una área (asignada a una empresa) antes de registrar puestos.
              </p>
            <?php else: ?>
              <p class="mt-1 text-xs text-muted-ink">
                El valor guardado será el área, pero se muestra la empresa para mayor contexto.
              </p>
            <?php endif; ?>
          </div>

          <!-- Nombre del puesto -->
          <div>
            <label for="nombre_puesto" class="block text-sm font-semibold text-vc-ink mb-1">
              Nombre del puesto <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="nombre_puesto"
              name="nombre_puesto"
              maxlength="100"
              required
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              placeholder="Ej. Auxiliar contable, Supervisor de producción…"
            >
          </div>

          <!-- Nivel jerárquico / Salario base -->
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label for="nivel" class="block text-sm font-semibold text-vc-ink mb-1">
                Nivel jerárquico <span class="text-red-500">*</span>
              </label>
              <select
                id="nivel"
                name="nivel"
                required
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
                <?php foreach ($niveles as $nivel): ?>
                  <option value="<?= htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <p class="mt-1 text-xs text-muted-ink">
                Define la posición del puesto dentro de la jerarquía organizacional.
              </p>
            </div>

            <div>
              <label for="salario_base" class="block text-sm font-semibold text-vc-ink mb-1">
                Salario base referencial
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-xs text-muted-ink">$</span>
                <input
                  type="text"
                  id="salario_base"
                  name="salario_base"
                  maxlength="12"
                  inputmode="numeric"
                  pattern="[0-9]+"
                  oninput="this.value = this.value.replace(/[^0-9]/g,'');"
                  class="block w-full rounded-lg border border-black/10 bg-white pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  placeholder="Solo números (ej. 15000)"
                >
              </div>
              <p class="mt-1 text-xs text-muted-ink">
                Solo se permiten números. El formato exacto se normaliza en el sistema (ej. 15000.00).
              </p>
            </div>
          </div>

          <!-- Descripción -->
          <div>
            <label for="descripcion" class="block text-sm font-semibold text-vc-ink mb-1">
              Descripción breve
            </label>
            <textarea
              id="descripcion"
              name="descripcion"
              maxlength="200"
              rows="3"
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              placeholder="Resumen del propósito del puesto, principales responsabilidades, etc."
            ></textarea>
            <p class="mt-1 text-xs text-muted-ink">
              Hasta 200 caracteres.
            </p>
          </div>

          <!-- Acciones -->
          <div class="flex justify-end gap-3 pt-2">
            <a
              href="<?= url('index.php?controller=puesto&action=index') ?>"
              class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm font-medium text-muted-ink hover:bg-slate-50 transition"
            >
              Cancelar
            </a>
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-5 py-2 text-sm font-semibold text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
              <?= empty($areasLista) ? 'disabled' : '' ?>
            >
              Guardar puesto
            </button>
          </div>
        </form>
      </div>
    </section>
  </main>
</body>
</html>
