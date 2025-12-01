<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$areaSesion   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puestoSesion = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudadSesion = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// -----------------------------------------------------------------------------
// Datos recibidos desde el controlador
// -----------------------------------------------------------------------------

// $puesto debe venir desde PuestoController::edit()
if (!isset($puesto) || !is_array($puesto)) {
    header('Location: ' . url('index.php?controller=puesto&action=index'));
    exit;
}

$puestoData = $puesto;

// Lista de áreas (empresa / área) desde el controlador
$areasLista = (isset($areas) && is_array($areas)) ? $areas : [];

// Niveles (enum)
$niveles = isset($niveles) && is_array($niveles)
    ? $niveles
    : ['OPERATIVO', 'SUPERVISOR', 'GERENCIAL', 'DIRECTIVO'];

// -----------------------------------------------------------------------------
// Flash messages y old_input
// -----------------------------------------------------------------------------
$flashError   = $_SESSION['flash_error']   ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
$old          = $_SESSION['old_input']     ?? [];

// limpiamos para que no se queden pegados
unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['old_input']);

// -----------------------------------------------------------------------------
// Valores base del puesto (BD)
// -----------------------------------------------------------------------------
$idPuestoBase      = (int)($puestoData['id_puesto'] ?? 0);
$idAreaBase        = (int)($puestoData['id_area']   ?? 0);
$nombrePuestoBase  = (string)($puestoData['nombre_puesto'] ?? '');
$nivelBase         = strtoupper((string)($puestoData['nivel'] ?? 'OPERATIVO'));
$salarioBaseBase   = (string)($puestoData['salario_base'] ?? '');
$descripcionBase   = (string)($puestoData['descripcion'] ?? '');

// -----------------------------------------------------------------------------
// Valores finales a mostrar: si hay old_input (hubo error), se usan;
// si no, se usan los de la BD.
// -----------------------------------------------------------------------------
$idPuesto = $idPuestoBase;

$idAreaSeleccionada = isset($old['id_area'])
    ? (int)$old['id_area']
    : $idAreaBase;

$nombrePuesto = htmlspecialchars(
    $old['nombre_puesto'] ?? $nombrePuestoBase,
    ENT_QUOTES,
    'UTF-8'
);

$nivelSeleccionado = strtoupper(
    (string)($old['nivel'] ?? $nivelBase)
);

$salarioBaseStr = (string)($old['salario_base'] ?? $salarioBaseBase);
$salarioBaseVal = htmlspecialchars($salarioBaseStr, ENT_QUOTES, 'UTF-8');

$descripcion = htmlspecialchars(
    $old['descripcion'] ?? $descripcionBase,
    ENT_QUOTES,
    'UTF-8'
);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar puesto · Administración organizacional</title>
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
          Editar puesto
        </span>
      </nav>
    </div>

    <?php if ($flashError): ?>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          Swal.fire({
            icon: 'error',
            title: 'No se pudo actualizar el puesto',
            text: <?= json_encode($flashError, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
            iconColor: '#ff78b5', // vc.pink
            background: '#ffffff',
            color: '#0a2a5e',     // vc.ink
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
        });
      </script>
    <?php endif; ?>

    <!-- Título -->
    <section class="flex flex-col gap-2 mb-4">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">
        Editar puesto
      </h1>
      <p class="text-sm sm:text-base text-muted-ink">
        Actualiza la información del puesto, su empresa/área, nivel jerárquico y salario base referencial.
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
          action="<?= url('index.php?controller=puesto&action=update&id=' . $idPuesto) ?>"
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
                  $idAreaOpt = (int)($a['id_area'] ?? 0);
                  if ($idAreaOpt <= 0) continue;

                  $nombreArea    = htmlspecialchars((string)($a['nombre_area']    ?? ('Área ' . $idAreaOpt)), ENT_QUOTES, 'UTF-8');
                  $nombreEmpresa = htmlspecialchars((string)($a['nombre_empresa'] ?? ''), ENT_QUOTES, 'UTF-8');

                  $label    = ($nombreEmpresa ? $nombreEmpresa . ' · ' : '') . $nombreArea;
                  $selected = ($idAreaOpt === $idAreaSeleccionada) ? 'selected' : '';
                ?>
                <option value="<?= htmlspecialchars((string)$idAreaOpt, ENT_QUOTES, 'UTF-8') ?>" <?= $selected ?>>
                  <?= $label ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (empty($areasLista)): ?>
              <p class="mt-1 text-xs text-red-500">
                No hay áreas registradas. Debes crear al menos una área (asignada a una empresa) antes de editar puestos.
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
              value="<?= $nombrePuesto ?>"
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
                  <?php
                    $nivVal   = strtoupper((string)$nivel);
                    $selected = ($nivVal === $nivelSeleccionado) ? 'selected' : '';
                  ?>
                  <option
                    value="<?= htmlspecialchars($nivVal, ENT_QUOTES, 'UTF-8') ?>"
                    <?= $selected ?>
                  >
                    <?= htmlspecialchars($nivVal, ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <p class="mt-1 text-xs text-muted-ink">
                Ajusta la posición del puesto dentro de la jerarquía organizacional.
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
                  inputmode="decimal"
                  pattern="^[0-9]+(\.[0-9]{0,2})?$"
                  oninput="
                    this.value = this.value
                      .replace(/[^0-9.]/g,'')       // solo dígitos y punto
                      .replace(/(\..*?)\..*/g,'$1') // deja solo un punto
                  "
                  class="block w-full rounded-lg border border-black/10 bg-white pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  placeholder="Ej. 15000 o 15000.50"
                  value="<?= $salarioBaseVal ?>"
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
            ><?= $descripcion ?></textarea>
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
              Guardar cambios
            </button>
          </div>
        </form>
      </div>
    </section>
  </main>
</body>
</html>
