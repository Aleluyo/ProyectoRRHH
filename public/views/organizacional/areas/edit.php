<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

$sessionArea   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto        = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad        = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

/**
 * Variables esperadas desde el controlador:
 * - $area            (array con datos del área)
 * - $empresas        (lista de empresas para el combo)
 * - $areasPorEmpresa (areas activas agrupadas por empresa)
 * - $old             (opcional, datos previos si hubo error)
 */
if (!isset($area) || !is_array($area)) {
    $area = [];
}
if (!isset($empresas) || !is_array($empresas)) {
    $empresas = [];
}
if (!isset($areasPorEmpresa) || !is_array($areasPorEmpresa)) {
    $areasPorEmpresa = [];
}

/**
 * Valores antiguos del formulario (si hubo error)
 */
if (!isset($old) || !is_array($old)) {
    $old = $_SESSION['old_area'] ?? [];
}
unset($_SESSION['old_area']);

/**
 * Mensaje de error general (excepción del modelo)
 */
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);


$areaId            = (int)($area['id_area'] ?? 0);
$selectedEmpresaId = (string)($area['id_empresa'] ?? '');
$selectedPadreId   = (string)($old['id_area_padre'] ?? ($area['id_area_padre'] ?? ''));
$nombreAreaValue   = htmlspecialchars((string)($old['nombre_area'] ?? $area['nombre_area'] ?? ''), ENT_QUOTES, 'UTF-8');
$descripcionValue  = htmlspecialchars((string)($old['descripcion'] ?? $area['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8');
$activaValue       = (int)($old['activa'] ?? ($area['activa'] ?? 1));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar área · Administración organizacional</title>
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

  <!-- Estilos SweetAlert con paleta VC -->
  <style>
    .swal2-popup.vc-swal {
      border-radius: 1rem;
      border: none !important;
      box-shadow: 0 18px 45px rgba(15,23,42,.12);
      font-family: 'Josefin Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: #ffffff;
      color: #0a2a5e; /* vc.ink */
    }

    .swal2-title.vc-swal-title {
      font-size: 1rem;
      font-weight: 600;
      color: #0a2a5e; /* vc.ink */
    }

    .swal2-html-container.vc-swal-text {
      font-size: 0.875rem;
      color: #0a2a5e; /* vc.ink */
    }

    .swal2-confirm.vc-swal-confirm {
      border-radius: 0.75rem;
      padding: 0.5rem 1.5rem;
      background-color: #36d1cc !important; /* vc.teal */
      color: #0a2a5e !important;            /* vc.ink */
      font-weight: 600;
      box-shadow: 0 18px 45px rgba(15,23,42,.12);
      border: none !important;             
      outline: none !important;             
    }

    .swal2-confirm.vc-swal-confirm:hover {
      background-color: #a7fffd !important; /* vc.neon */
    }
  </style>

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
          <?= $puesto ?><?= $sessionArea ? ' &mdash; ' . $sessionArea : '' ?><?= $ciudad ? ' &mdash; ' . $ciudad : '' ?>
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
        <a href="<?= url('index.php?controller=area&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Áreas
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">
          Editar área
        </span>
      </nav>
    </div>

    <!-- Título -->
    <section class="flex flex-col gap-2 mb-4">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Editar área</h1>
      <p class="text-sm sm:text-base text-muted-ink">
        Actualiza los datos de esta área o departamento.
      </p>
      <p class="text-xs text-muted-ink">
        (*) Campos obligatorios.
      </p>
    </section>

    <!-- Formulario -->
    <section class="mt-2">
      <div class="bg-white/95 rounded-xl border border-black/10 shadow-soft p-6 sm:p-8 relative z-10">
        <form id="formArea" method="POST" action="<?= url('index.php?controller=area&action=update') ?>" class="space-y-6">
          <input type="hidden" name="id_area" value="<?= htmlspecialchars((string)$areaId, ENT_QUOTES, 'UTF-8') ?>">

          <!-- Empresa -->
          <div>
            <label for="id_empresa" class="block text-sm font-semibold text-vc-ink mb-1">
              Empresa <span class="text-red-500">*</span>
            </label>
            <select
              id="id_empresa"
              name="id_empresa"
              disabled
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
              <option value="">Selecciona una empresa…</option>
              <?php foreach ($empresas as $emp): ?>
                <?php
                  $idEmp   = (string)$emp['id_empresa'];
                  $nombreE = htmlspecialchars((string)$emp['nombre'], ENT_QUOTES, 'UTF-8');
                  $selected = $idEmp === $selectedEmpresaId ? 'selected' : '';
                ?>
                <option value="<?= htmlspecialchars($idEmp, ENT_QUOTES, 'UTF-8') ?>" <?= $selected ?>>
                  <?= $nombreE ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Nombre de área -->
          <div>
            <label for="nombre_area" class="block text-sm font-semibold text-vc-ink mb-1">
              Nombre del área <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="nombre_area"
              name="nombre_area"
              maxlength="100"
              required
              value="<?= $nombreAreaValue ?>"
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
          </div>

          <!-- Área padre + Estado -->
          <div class="grid gap-4 sm:grid-cols-2">
            <!-- Área padre (opcional) -->
            <div>
              <label for="id_area_padre" class="block text-sm font-semibold text-vc-ink mb-1">
                Área padre
              </label>
              <select
                id="id_area_padre"
                name="id_area_padre"
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
                <option value="">(Sin área padre)</option>
                <!-- Se llenará vía JS según empresa -->
              </select>
              <p class="mt-1 text-xs text-muted-ink">
                Opcional: selecciona un área superior para formar la jerarquía.
              </p>
            </div>

            <!-- Estado -->
            <div>
              <label class="block text-sm font-semibold text-vc-ink mb-1">
                Estado
              </label>
              <div class="flex items-center gap-2">
                <input
                  type="checkbox"
                  id="activa_check"
                  class="h-4 w-4 rounded border-black/20 text-vc-teal focus:ring-vc-teal/60"
                  <?= $activaValue === 1 ? 'checked' : '' ?>
                  onclick="document.getElementById('activa_value').value = this.checked ? 1 : 0;"
                >
                <span class="text-sm text-muted-ink">Área activa</span>
              </div>
              <input type="hidden" id="activa_value" name="activa" value="<?= $activaValue === 1 ? '1' : '0' ?>">
            </div>
          </div>

          <!-- Descripción -->
          <div>
            <label for="descripcion" class="block text-sm font-semibold text-vc-ink mb-1">
              Descripción
            </label>
            <textarea
              id="descripcion"
              name="descripcion"
              maxlength="200"
              rows="3"
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm resize-y focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              placeholder="Describe brevemente las funciones o alcance del área (opcional)."
            ><?= $descripcionValue ?></textarea>
          </div>

          <!-- Acciones -->
          <div class="flex justify-end gap-3 pt-2">
            <a
              href="<?= url('index.php?controller=area&action=index') ?>"
              class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm font-medium text-muted-ink hover:bg-slate-50 transition"
            >
              Cancelar
            </a>
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-5 py-2 text-sm font-semibold text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
            >
              Actualizar área
            </button>
          </div>
        </form>
      </div>
    </section>
  </main>

  <script>
    const areasPorEmpresa = <?= json_encode($areasPorEmpresa, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?> || {};
    const currentAreaId   = <?= (int)$areaId ?>;
    const selectedEmpresaId = "<?= htmlspecialchars($selectedEmpresaId, ENT_QUOTES, 'UTF-8') ?>";
    const selectedPadreId   = "<?= htmlspecialchars($selectedPadreId, ENT_QUOTES, 'UTF-8') ?>";

    function updateAreasPadre() {
      const areaPadreSelect = document.getElementById('id_area_padre');
      const empresaId       = selectedEmpresaId;

      areaPadreSelect.innerHTML = '<option value="">(Sin área padre)</option>';

      if (!empresaId || !areasPorEmpresa[empresaId]) return;

      areasPorEmpresa[empresaId].forEach(a => {
        // Evitar que un área sea padre de sí misma
        if (parseInt(a.id_area, 10) === currentAreaId) return;

        const opt = document.createElement('option');
        opt.value = a.id_area;
        opt.textContent = a.nombre_area;
        areaPadreSelect.appendChild(opt);
      });

      // Seleccionar el padre actual si existe
      if (selectedPadreId) {
        areaPadreSelect.value = selectedPadreId;
      }
    }

    updateAreasPadre();
  </script>

  <?php if (!empty($flashError)): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'No se pudo guardar el área',
      text: <?= json_encode($flashError, JSON_UNESCAPED_UNICODE) ?>,
      iconColor: '#ff78b5', // opcional, paleta VC
      background: '#ffffff',
      color: '#0a2a5e',
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#36d1cc',
      buttonsStyling: false,
      customClass: {
        popup: 'vc-swal',
        title: 'vc-swal-title',
        htmlContainer: 'vc-swal-text',
        confirmButton: 'vc-swal-confirm'
      }
    });
  </script>
  <?php endif; ?>

</body>
</html>
