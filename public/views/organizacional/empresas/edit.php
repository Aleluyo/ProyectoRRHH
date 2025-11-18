<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

// $empresa debe venir desde EmpresaController::edit()
if (!isset($empresa) || !is_array($empresa)) {
    header('Location: ' . url('index.php?controller=empresa&action=index'));
    exit;
}

$area   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

$id            = (int)($empresa['id_empresa'] ?? 0);
$nombre        = htmlspecialchars($empresa['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$rfc           = htmlspecialchars($empresa['rfc'] ?? '', ENT_QUOTES, 'UTF-8');
$correo_contacto = htmlspecialchars($empresa['correo_contacto'] ?? '', ENT_QUOTES, 'UTF-8');
$telefonoRaw   = isset($empresa['telefono']) ? preg_replace('/\D+/', '', $empresa['telefono']) : '';
$telefono      = htmlspecialchars($telefonoRaw, ENT_QUOTES, 'UTF-8');
$direccionRaw  = $empresa['direccion'] ?? '';
$activa        = isset($empresa['activa']) ? (int)$empresa['activa'] : 1;

// Valores por defecto para los campos de dirección estandarizados
$calle            = '';
$numero_exterior  = '';
$numero_interior  = '';
$colonia          = '';
$municipio        = '';
$ciudadDir        = '';
$estado           = '';
$codigo_postal    = '';
$pais             = '';

// Intentar descomponer la dirección construida previamente
if (!empty($direccionRaw)) {
    $partes = array_map('trim', explode(',', $direccionRaw));

    // Ejemplo esperado:
    // 0: "Calle X #123 Int. 4"  (o sin Int.)
    // 1: "Col. Colonia"
    // 2: "Municipio"
    // 3: "Ciudad"
    // 4: "Estado"
    // 5: "C.P. 12345"
    // 6: "País"

    if (isset($partes[0]) && stripos($partes[0], 'Calle') === 0) {
        $callePart = trim(substr($partes[0], strlen('Calle')));
        $callePart = ltrim($callePart); // quitar espacio inicial

        // Regex para "NombreCalle #NumExt Int. NumInt" (Int. opcional)
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
    if (isset($partes[4])) $estado = $partes[4];

    if (isset($partes[5]) && stripos($partes[5], 'C.P.') === 0) {
        $codigo_postal = preg_replace('/^C\.P\.\s*/i', '', $partes[5]);
        $codigo_postal = trim($codigo_postal);
    }

    if (isset($partes[6])) $pais = $partes[6];
}

// Escapar para imprimir en HTML
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar empresa · Administración organizacional</title>
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
          class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink transition"
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
        <a href="<?= url('index.php?controller=empresa&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Empresas
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">
          Editar empresa
        </span>
      </nav>
    </div>

    <!-- Título -->
    <section class="flex flex-col gap-2 mb-4">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Editar empresa</h1>
      <p class="text-sm sm:text-base text-muted-ink">
        Actualiza la información de la empresa.
      </p>
      <p class="text-xs text-muted-ink">
        (*) Campos obligatorios.
      </p>
    </section>

    <!-- Formulario -->
    <section class="mt-2">
      <div class="bg-white/95 rounded-xl border border-black/10 shadow-soft p-6 sm:p-8 relative z-10">
        <form id="formEmpresa" method="POST" action="<?= url('index.php?controller=empresa&action=update&id=' . $id) ?>" class="space-y-6">
          <!-- Nombre -->
          <div>
            <label for="nombre" class="block text-sm font-semibold text-vc-ink mb-1">
              Nombre de la empresa <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="nombre"
              name="nombre"
              maxlength="120"
              required
              value="<?= e($nombre) ?>"
              class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
            >
          </div>

          <!-- RFC / Correo -->
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label for="rfc" class="block text-sm font-semibold text-vc-ink mb-1">
                RFC <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="rfc"
                name="rfc"
                maxlength="20"
                required
                value="<?= e($rfc) ?>"
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm uppercase tracking-[0.05em] focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
            </div>

            <div>
              <label for="correo_contacto" class="block text-sm font-semibold text-vc-ink mb-1">
                Correo de contacto <span class="text-red-500">*</span>
              </label>
              <input
                type="email"
                id="correo_contacto"
                name="correo_contacto"
                maxlength="120"
                required
                value="<?= e($correo_contacto) ?>"
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
            </div>
          </div>

          <!-- Teléfono -->
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label for="telefono" class="block text-sm font-semibold text-vc-ink mb-1">
                Teléfono <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="telefono"
                name="telefono"
                maxlength="20"
                required
                inputmode="numeric"
                pattern="[0-9]+"
                oninput="this.value = this.value.replace(/[^0-9]/g,'');"
                value="<?= e($telefono) ?>"
                class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm tracking-[0.12em] focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              >
            </div>

            <div class="flex items-end">
              <div>
                <span class="block text-sm font-semibold text-vc-ink mb-1">
                  Estado de la empresa <span class="text-red-500">*</span>
                </span>
                <div class="flex items-center gap-2">
                  <input
                    type="hidden"
                    name="activa"
                    value="0"
                  >
                  <input
                    type="checkbox"
                    id="activa"
                    name="activa"
                    value="1"
                    <?= $activa ? 'checked' : '' ?>
                    class="h-4 w-4 rounded border-black/20 text-vc-teal focus:ring-vc-teal"
                  >
                  <label for="activa" class="text-sm text-muted-ink">
                    Empresa activa
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Dirección estandarizada -->
          <fieldset class="border border-dashed border-black/15 rounded-lg p-4 sm:p-5">
            <legend class="px-2 text-xs font-semibold uppercase tracking-[0.16em] text-muted-ink">
              Dirección <span class="text-red-500">*</span>
            </legend>

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
                    value="<?= e($calle) ?>"
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
                    value="<?= e($numero_exterior) ?>"
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
                    value="<?= e($numero_interior) ?>"
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
                    value="<?= e($colonia) ?>"
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
                    value="<?= e($municipio) ?>"
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
                    value="<?= e($ciudadDir) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
                <div>
                  <label for="estado" class="block text-sm font-semibold text-vc-ink mb-1">
                    Estado <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="estado"
                    name="estado"
                    required
                    value="<?= e($estado) ?>"
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
                    value="<?= e($codigo_postal) ?>"
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
                    value="<?= e($pais) ?>"
                    class="block w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                  >
                </div>
              </div>
            </div>

            <!-- Campo oculto donde se concatenará la dirección -->
            <input type="hidden" id="direccion_full" name="direccion" value="<?= e($direccionRaw) ?>">

          </fieldset>

          <!-- Acciones -->
          <div class="flex justify-end gap-3 pt-2">
            <a
              href="<?= url('index.php?controller=empresa&action=index') ?>"
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
    // Antes de enviar el formulario, concatenamos la dirección en un solo campo
    const form = document.getElementById('formEmpresa');
    form.addEventListener('submit', function () {
      const calle           = document.getElementById('calle').value.trim();
      const numExt          = document.getElementById('numero_exterior').value.trim();
      const numInt          = document.getElementById('numero_interior').value.trim();
      const colonia         = document.getElementById('colonia').value.trim();
      const municipio       = document.getElementById('municipio').value.trim();
      const ciudad          = document.getElementById('ciudad').value.trim();
      const estado          = document.getElementById('estado').value.trim();
      const codigoPostal    = document.getElementById('codigo_postal').value.trim();
      const pais            = document.getElementById('pais').value.trim();

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
      if (estado)       partes.push(estado);
      if (codigoPostal) partes.push('C.P. ' + codigoPostal);
      if (pais)         partes.push(pais);

      document.getElementById('direccion_full').value = partes.join(', ');
    });
  </script>
</body>
</html>
