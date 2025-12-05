<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

// Asegurar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$area   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// $ubicacion y $empresas vienen desde UbicacionController::edit()
if (!isset($ubicacion) || !is_array($ubicacion)) {
    $ubicacion = [];
}
if (!isset($empresas) || !is_array($empresas)) {
    $empresas = [];
}

// Flash + old_input (por si update falló)
$flashError   = $_SESSION['flash_error']   ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
$old          = $_SESSION['old_input']     ?? [];

// limpiar para que no se vuelvan a mostrar
unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['old_input']);

$idUbicacion = (int)($ubicacion['id_ubicacion'] ?? 0);
$nombreSede  = $ubicacion['nombre'] ?? '';
$idEmpresa   = (int)($ubicacion['id_empresa'] ?? 0);

// Bandera que viene del controlador: true si la empresa está inactiva y no se debe cambiar
$empresaSoloLectura = $empresaSoloLectura ?? false;

$direccionRaw = $ubicacion['direccion'] ?? '';

$ciudadCol   = $ubicacion['ciudad']        ?? '';
$estadoCol   = $ubicacion['estado_region'] ?? '';
$paisCol     = $ubicacion['pais']          ?? '';
$activa      = isset($ubicacion['activa']) ? (int)$ubicacion['activa'] : 1;

// Valores por defecto para los campos de dirección estandarizados
$calle           = '';
$numero_exterior = '';
$numero_interior = '';
$colonia         = '';
$municipio       = '';
$ciudadDir       = '';
$estadoDir       = '';
$codigo_postal   = '';
$paisDir         = '';

// Intentar descomponer la dirección construida previamente
if (!empty($direccionRaw)) {
    $partes = array_map('trim', explode(',', $direccionRaw));

    // Formato esperado:
    // 0: "Calle X #123 Int. 4"  (o sin Int.)
    // 1: "Col. Colonia"
    // 2: "Municipio"
    // 3: "Ciudad"
    // 4: "Estado/Región"
    // 5: "C.P. 12345"
    // 6: "País"

    if (isset($partes[0]) && stripos($partes[0], 'Calle') === 0) {
        $callePart = trim(substr($partes[0], strlen('Calle')));
        $callePart = ltrim($callePart); // quitar espacio inicial

        // "NombreCalle #NumExt Int. NumInt" (Int. opcional)
        if (preg_match('/^(.+?)\s*#([0-9]+)\s*(?:Int\.\s*(.+))?$/i', $callePart, $m)) {
            $calle = trim($m[1]);
            $numero_exterior = trim($m[2]);
            if (!empty($m[3])) {
                $numero_interior = trim($m[3]);
            }
        } else {
            $calle = $callePart;
        }
    }

    if (isset($partes[1]) && stripos($partes[1], 'Col.') === 0) {
        $colonia = trim(substr($partes[1], strlen('Col.')));
    }

    if (isset($partes[2])) $municipio = $partes[2];
    if (isset($partes[3])) $ciudadDir = $partes[3];
    if (isset($partes[4])) $estadoDir = $partes[4];

    if (isset($partes[5]) && stripos($partes[5], 'C.P.') === 0) {
        $codigo_postal = preg_replace('/^C\.P\.\s*/i', '', $partes[5]);
        $codigo_postal = trim($codigo_postal);
    }

    if (isset($partes[6])) $paisDir = $partes[6];
}

// Elegir valores finales para ciudad/estado/pais: primero columna, luego lo parseado
$ciudadFinal       = $ciudadCol   !== '' ? $ciudadCol   : $ciudadDir;
$estadoRegionFinal = $estadoCol   !== '' ? $estadoCol   : $estadoDir;
$paisFinal         = $paisCol     !== '' ? $paisCol     : $paisDir;

// Si venimos de un POST con errores, sobrescribir con old_input
if (!empty($old)) {
    $idEmpresa       = isset($old['id_empresa']) ? (int)$old['id_empresa'] : $idEmpresa;
    $nombreSede      = $old['nombre']        ?? $nombreSede;

    $calle           = $old['calle']         ?? $calle;
    $numero_exterior = $old['numero_exterior'] ?? $numero_exterior;
    $numero_interior = $old['numero_interior'] ?? $numero_interior;
    $colonia         = $old['colonia']       ?? $colonia;
    $municipio       = $old['municipio']     ?? $municipio;
    $ciudadFinal     = $old['ciudad']        ?? $ciudadFinal;
    $estadoRegionFinal = $old['estado_region'] ?? $estadoRegionFinal;
    $codigo_postal   = $old['codigo_postal'] ?? $codigo_postal;
    $paisFinal       = $old['pais']          ?? $paisFinal;

    // checkbox activa
    $activa = isset($old['activa']) ? 1 : 0;
}

// Helper para escapar
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Para el hidden de dirección: si hay old_input, usamos la última enviada
$direccionForm = $old['direccion'] ?? $direccionRaw;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar ubicación · Administración organizacional</title>
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

    <?php if ($flashError): ?>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          Swal.fire({
            icon: 'error',
            title: 'No se pudo actualizar la ubicación',
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
        <a href="<?= url('index.php?controller=ubicacion&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Ubicaciones
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">
          Editar ubicación
        </span>
      </nav>
    </div>

    <!-- Título -->
    <section class="flex flex-col gap-2 mb-4">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Editar ubicación</h1>
      <p class="text-sm sm:text-base text-muted-ink">
        Actualiza los datos de la sede o sucursal seleccionada.
      </p>
      <p class="text-xs text-muted-ink">
        (*) Campos obligatorios.
      </p>
    </section>

    <!-- Formulario -->
    <section class="mt-2">
      <div class="bg-white/95 rounded-xl border border-black/10 shadow-soft p-6 sm:p-8 relative z-10">
        <form
          id="formUbicacion"
          method="POST"
          action="<?= url('index.php?controller=ubicacion&action=update&id=' . $idUbicacion) ?>"
          class="space-y-6"
        >
          <!-- Empresa -->
          <div>
            <label for="id_empresa" class="block text-sm font-semibold text-vc-ink mb-1">
              Empresa <span class="text-red-500">*</span>
            </label>
            <select
              id="id_empresa"
              name="id_empresa"
              required
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              <?= $empresaSoloLectura ? 'disabled' : '' ?>
            >
              <option value="">Seleccione una empresa…</option>
              <?php foreach ($empresas as $emp): ?>
                <?php
                  $idEmp       = (int)$emp['id_empresa'];
                  $nombreEmp   = e((string)$emp['nombre']);
                  $activaEmp   = isset($emp['activa']) ? (int)$emp['activa'] : 1;
                  $textoOpcion = $nombreEmp . ($activaEmp === 0 ? ' (inactiva)' : '');
                ?>
                <option
                  value="<?= $idEmp ?>"
                  <?= $idEmp === $idEmpresa ? 'selected' : '' ?>
                >
                  <?= $textoOpcion ?>
                </option>
              <?php endforeach; ?>
            </select>

            <?php if ($empresaSoloLectura): ?>
              <!-- IMPORTANTE: al estar disabled, el select no se envía; mantenemos el id por hidden -->
              <input type="hidden" name="id_empresa" value="<?= $idEmpresa ?>">
              <p class="mt-1 text-xs text-red-500">
                La empresa está desactivada; se mantiene solo para históricos y no puede cambiarse desde aquí.
              </p>
            <?php endif; ?>
          </div>

          <!-- Nombre sede -->
          <div>
            <label for="nombre" class="block text-sm font-semibold text-vc-ink mb-1">
              Nombre de la sede <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="nombre"
              name="nombre"
              maxlength="100"
              required
              value="<?= e((string)$nombreSede) ?>"
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
            <p class="mt-1 text-xs text-muted-ink">
              Ejemplo: "Oficinas corporativas CDMX", "Planta Monterrey", "Sucursal Centro".
            </p>
          </div>

          <!-- Dirección estandarizada -->
          <fieldset class="border border-dashed border-black/15 rounded-lg p-4 sm:p-5">
            <legend class="px-2 text-xs font-semibold uppercase tracking-[0.16em] text-muted-ink">
              Dirección de la sede <span class="text-red-500">*</span>
            </legend>

            <p class="mt-1 mb-3 text-xs text-muted-ink">
              Dirección actual guardada:
              <span class="font-medium">
                <?= e((string)$direccionRaw) ?>
              </span>
            </p>

            <div class="mt-3 grid gap-4">
              <!-- Calle / No. exterior / No. interior -->
              <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                  <label for="calle" class="block text-sm font-semibold text-vc-ink mb-1">
                    Calle <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="calle"
                    name="calle"
                    required
                    value="<?= e((string)$calle) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
                <div>
                  <label for="numero_exterior" class="block text-sm font-semibold text-vc-ink mb-1">
                    Número exterior <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="numero_exterior"
                    name="numero_exterior"
                    required
                    inputmode="numeric"
                    pattern="[0-9]+"
                    oninput="this.value = this.value.replace(/[^0-9]/g,'');"
                    value="<?= e((string)$numero_exterior) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
              </div>

              <div class="grid gap-4 sm:grid-cols-3">
                <div>
                  <label for="numero_interior" class="block text-sm font-semibold text-vc-ink mb-1">
                    Número interior
                  </label>
                  <input
                    type="text"
                    id="numero_interior"
                    name="numero_interior"
                    value="<?= e((string)$numero_interior) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
                <div class="sm:col-span-2">
                  <label for="colonia" class="block text-sm font-semibold text-vc-ink mb-1">
                    Colonia <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="colonia"
                    name="colonia"
                    required
                    value="<?= e((string)$colonia) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
              </div>

              <div class="grid gap-4 sm:grid-cols-3">
                <div>
                  <label for="municipio" class="block text-sm font-semibold text-vc-ink mb-1">
                    Municipio <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="municipio"
                    name="municipio"
                    required
                    value="<?= e((string)$municipio) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
                <div>
                  <label for="ciudad" class="block text-sm font-semibold text-vc-ink mb-1">
                    Ciudad <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="ciudad"
                    name="ciudad"
                    required
                    maxlength="80"
                    value="<?= e((string)$ciudadFinal) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
                <div>
                  <label for="estado_region" class="block text-sm font-semibold text-vc-ink mb-1">
                    Estado <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="estado_region"
                    name="estado_region"
                    required
                    maxlength="80"
                    value="<?= e((string)$estadoRegionFinal) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
              </div>

              <div class="grid gap-4 sm:grid-cols-3">
                <div>
                  <label for="codigo_postal" class="block text-sm font-semibold text-vc-ink mb-1">
                    Código postal <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="codigo_postal"
                    name="codigo_postal"
                    required
                    maxlength="10"
                    inputmode="numeric"
                    pattern="[0-9]+"
                    oninput="this.value = this.value.replace(/[^0-9]/g,'');"
                    value="<?= e((string)$codigo_postal) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm tracking-[0.16em] focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
                <div class="sm:col-span-2">
                  <label for="pais" class="block text-sm font-semibold text-vc-ink mb-1">
                    País <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="pais"
                    name="pais"
                    required
                    maxlength="80"
                    value="<?= e((string)$paisFinal) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
              </div>
            </div>

            <!-- Campo oculto donde se concatenará la nueva dirección -->
            <input
              type="hidden"
              id="direccion_full"
              name="direccion"
              value="<?= e((string)$direccionForm) ?>"
            >
          </fieldset>

          <!-- Activa -->
          <div class="flex items-center gap-2">
            <input
              type="checkbox"
              id="activa"
              name="activa"
              value="1"
              class="rounded border-black/20 text-vc-teal focus:ring-vc-teal"
              <?= $activa === 1 ? 'checked' : '' ?>
            >
            <label for="activa" class="text-sm text-vc-ink">
              Ubicación activa
            </label>
          </div>

          <!-- Acciones -->
          <div class="flex justify-end gap-3 pt-2">
            <a
              href="<?= url('index.php?controller=ubicacion&action=index') ?>"
              class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm font-medium text-muted-ink hover:bg-slate-50 transition"
            >
              Cancelar
            </a>
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-5 py-2 text-sm font-semibold text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
            >
              Guardar cambios
            </button>
          </div>
        </form>
      </div>
    </section>
  </main>

  <script>
    // Antes de enviar el formulario, se concatena la dirección en un solo campo
    const form = document.getElementById('formUbicacion');
    form.addEventListener('submit', function () {
      const calle        = document.getElementById('calle').value.trim();
      const numExt       = document.getElementById('numero_exterior').value.trim();
      const numInt       = document.getElementById('numero_interior').value.trim();
      const colonia      = document.getElementById('colonia').value.trim();
      const municipio    = document.getElementById('municipio').value.trim();
      const ciudad       = document.getElementById('ciudad').value.trim();
      const estadoRegion = document.getElementById('estado_region').value.trim();
      const cp           = document.getElementById('codigo_postal').value.trim();
      const pais         = document.getElementById('pais').value.trim();

      const partes = [];

      if (calle) {
        let linea = 'Calle ' + calle;
        if (numExt) linea += ' #' + numExt;
        if (numInt) linea += ' Int. ' + numInt;
        partes.push(linea);
      }

      if (colonia)      partes.push('Col. ' + colonia);
      if (municipio)    partes.push(municipio);
      if (ciudad)       partes.push(ciudad);
      if (estadoRegion) partes.push(estadoRegion);
      if (cp)           partes.push('C.P. ' + cp);
      if (pais)         partes.push(pais);

      document.getElementById('direccion_full').value = partes.join(', ');
    });
  </script>
</body>
</html>
