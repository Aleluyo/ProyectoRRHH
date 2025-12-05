<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

$area = htmlspecialchars($_SESSION['area'] ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// $vacante viene desde VacanteController::edit()
if (!isset($vacante) || !is_array($vacante)) {
  $vacante = [];
}

$errors = $errors ?? ($_SESSION['errors'] ?? []);
$old = $old ?? ($_SESSION['old_input'] ?? []);

// Helper para obtener valor viejo o de BD
function v_old_vac(string $key, array $old, array $vacante): string
{
  if (array_key_exists($key, $old)) {
    return (string) $old[$key];
  }
  return isset($vacante[$key]) ? (string) $vacante[$key] : '';
}

$idVacante = (int) ($vacante['id_vacante'] ?? 0);

// Valores actuales para inicializar JS
$oldEmpresa = v_old_vac('id_empresa', $old, $vacante);
$oldArea = v_old_vac('id_area', $old, $vacante);
$oldPuesto = v_old_vac('id_puesto', $old, $vacante);
$oldUbicacion = v_old_vac('id_ubicacion', $old, $vacante);
$oldSolicita = v_old_vac('solicitada_por', $old, $vacante);
$oldEstatus = v_old_vac('estatus', $old, $vacante);
$oldReq = v_old_vac('requisitos', $old, $vacante);
$oldFecha = v_old_vac('fecha_publicacion', $old, $vacante);

?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Editar vacante · Reclutamiento</title>
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
              pink: '#ff78b5', peach: '#ffc9a9', teal: '#36d1cc',
              sand: '#ffe9c7', ink: '#0a2a5e', neon: '#a7fffd'
            }
          },
          fontFamily: {
            display: ['Josefin Sans', 'system-ui', 'sans-serif'],
            sans: ['DM Sans', 'system-ui', 'sans-serif'],
            vice: ['Rage Italic', 'Yellowtail', 'cursive']
          },
          boxShadow: { soft: '0 10px 28px rgba(10,42,94,.08)' },
          backgroundImage: {
            gridglow: 'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
            ribbon: 'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'
          }
        }
      }
    }
  </script>

  <!-- Fuentes -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap"
    rel="stylesheet">

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
        <a href="<?= url('logout.php') ?>"
          class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <!-- Contenido -->
  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">
    <!-- Breadcrumb -->
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('views/reclutamiento/index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">
          Reclutamiento y Selección
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('index.php?controller=vacante&action=index') ?>"
          class="text-muted-ink hover:text-vc-ink transition">
          Vacantes
        </a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">Editar vacante #<?= $idVacante ?></span>
      </nav>
    </div>

    <!-- Título -->
    <section class="mb-6">
      <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Editar vacante</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        Modifica la información de la vacante seleccionada.
      </p>
    </section>

    <!-- Formulario -->
    <section class="rounded-xl border border-black/10 bg-white/90 p-5 shadow-soft">
      <?php if (!empty($errors)): ?>
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 border border-red-200">
          <p class="font-bold">Por favor corrige los siguientes errores:</p>
          <ul class="list-disc list-inside mt-1">
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= url('index.php?controller=vacante&action=update&id=' . $idVacante) ?>"
        class="space-y-6">

        <!-- Primera fila: Empresa / Área / Puesto -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Empresa -->
          <div>
            <label for="id_empresa" class="block text-sm font-medium text-vc-ink">Empresa</label>
            <select id="id_empresa" name="id_empresa"
              class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
              <option value="">Selecciona una empresa…</option>
              <?php foreach (($empresas ?? []) as $emp): ?>
                <?php
                $id = (string) $emp['id_empresa'];
                $txt = htmlspecialchars($emp['nombre'] ?? ('Empresa #' . $id), ENT_QUOTES, 'UTF-8');
                ?>
                <option value="<?= $id ?>" <?= $oldEmpresa === $id ? 'selected' : '' ?>>
                  <?= $txt ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Área -->
          <div>
            <label for="id_area" class="block text-sm font-medium text-vc-ink">Área</label>
            <select id="id_area" name="id_area"
              class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
              <option value="">Selecciona un área…</option>
            </select>
          </div>

          <!-- Puesto -->
          <div>
            <label for="id_puesto" class="block text-sm font-medium text-vc-ink">Puesto</label>
            <select id="id_puesto" name="id_puesto"
              class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
              <option value="">Selecciona un puesto…</option>
            </select>
          </div>
        </div>

        <!-- Segunda fila: Ubicación / Solicitada por / Estatus -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Ubicación -->
          <div>
            <label for="id_ubicacion" class="block text-sm font-medium text-vc-ink">Ubicación</label>
            <select id="id_ubicacion" name="id_ubicacion"
              class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
              <option value="">Selecciona una ubicación…</option>
            </select>
          </div>

          <!-- Solicitada por -->
          <div>
            <label for="solicitada_por" class="block text-sm font-medium text-vc-ink">Solicitada por (ID
              usuario)</label>
            <input type="number" name="solicitada_por" id="solicitada_por"
              value="<?= htmlspecialchars($oldSolicita, ENT_QUOTES, 'UTF-8') ?>"
              class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              required>
          </div>

          <!-- Estatus -->
          <div>
            <label for="estatus" class="block text-sm font-medium text-vc-ink">Estatus</label>
            <?php $estatusSel = strtoupper($oldEstatus); ?>
            <select name="estatus" id="estatus"
              class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              required>
              <option value="EN_APROBACION" <?= $estatusSel === 'EN_APROBACION' ? 'selected' : '' ?>>En aprobación</option>
              <option value="APROBADA" <?= $estatusSel === 'APROBADA' ? 'selected' : '' ?>>Aprobada</option>
              <option value="ABIERTA" <?= $estatusSel === 'ABIERTA' ? 'selected' : '' ?>>Abierta</option>
              <option value="EN_PROCESO" <?= $estatusSel === 'EN_PROCESO' ? 'selected' : '' ?>>En proceso</option>
              <option value="CERRADA" <?= $estatusSel === 'CERRADA' ? 'selected' : '' ?>>Cerrada</option>
            </select>
          </div>
        </div>

        <!-- Tercera fila: Fecha publicación (opcional) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label for="fecha_publicacion" class="block text-sm font-medium text-vc-ink">
              Fecha de publicación <span class="text-xs text-muted-ink">(opcional)</span>
            </label>
            <input type="date" name="fecha_publicacion" id="fecha_publicacion"
              value="<?= htmlspecialchars($oldFecha, ENT_QUOTES, 'UTF-8') ?>"
              class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
          </div>
        </div>

        <!-- Requisitos / Notas -->
        <div>
          <label for="requisitos" class="block text-sm font-medium text-vc-ink mb-1">Requisitos / Comentarios</label>
          <textarea name="requisitos" id="requisitos" rows="5"
            class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"><?= htmlspecialchars($oldReq, ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
          <a href="<?= url('index.php?controller=vacante&action=index') ?>"
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

  <!-- JS: selects dependientes (igual que en create.php) -->
  <script>
    // Datos en bruto desde PHP
    const EMPRESAS = <?= json_encode($empresas ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const AREAS = <?= json_encode($areas ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const PUESTOS = <?= json_encode($puestos ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const UBICACIONES = <?= json_encode($ubicaciones ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    const oldEmpresa = "<?= $oldEmpresa ?>";
    const oldArea = "<?= $oldArea ?>";
    const oldPuesto = "<?= $oldPuesto ?>";
    const oldUbicacion = "<?= $oldUbicacion ?>";

    const selEmpresa = document.getElementById('id_empresa');
    const selArea = document.getElementById('id_area');
    const selPuesto = document.getElementById('id_puesto');
    const selUbicacion = document.getElementById('id_ubicacion');

    function llenarAreas(idEmpresa, mantenerSeleccion) {
      selArea.innerHTML = '<option value="">Selecciona un área…</option>';

      AREAS.filter(a => String(a.id_empresa) === String(idEmpresa))
        .forEach(a => {
          const opt = document.createElement('option');
          opt.value = a.id_area;
          opt.textContent = a.nombre_area || ('Área #' + a.id_area);
          selArea.appendChild(opt);
        });

      if (mantenerSeleccion && oldArea) {
        selArea.value = oldArea;
      }
      if (!selArea.value && selArea.options.length > 1) {
        // Opcional: seleccionar el primero si no hay selección previa
        // selArea.selectedIndex = 1; 
      }
    }

    function llenarPuestos(idArea, mantenerSeleccion) {
      selPuesto.innerHTML = '<option value="">Selecciona un puesto…</option>';

      PUESTOS.filter(p => String(p.id_area) === String(idArea))
        .forEach(p => {
          const opt = document.createElement('option');
          opt.value = p.id_puesto;
          opt.textContent = p.nombre_puesto || ('Puesto #' + p.id_puesto);
          selPuesto.appendChild(opt);
        });

      if (mantenerSeleccion && oldPuesto) {
        selPuesto.value = oldPuesto;
      }
    }

    function llenarUbicaciones(idEmpresa, mantenerSeleccion) {
      selUbicacion.innerHTML = '<option value="">Selecciona una ubicación…</option>';

      UBICACIONES.filter(u => String(u.id_empresa) === String(idEmpresa))
        .forEach(u => {
          const opt = document.createElement('option');
          opt.value = u.id_ubicacion;
          opt.textContent = u.nombre || ('Ubicación #' + u.id_ubicacion);
          selUbicacion.appendChild(opt);
        });

      if (mantenerSeleccion && oldUbicacion) {
        selUbicacion.value = oldUbicacion;
      }
    }

    // Eventos
    selEmpresa.addEventListener('change', () => {
      const idEmp = selEmpresa.value || '';
      llenarAreas(idEmp, false);
      llenarPuestos('', false);
      llenarUbicaciones(idEmp, false);
    });

    selArea.addEventListener('change', () => {
      const idArea = selArea.value || '';
      llenarPuestos(idArea, false);
    });

    // Inicialización con valores viejos (si los hay)
    window.addEventListener('DOMContentLoaded', () => {
      if (oldEmpresa) {
        selEmpresa.value = oldEmpresa;
        llenarAreas(oldEmpresa, true);

        if (oldArea) {
          llenarPuestos(oldArea, true);
        }

        llenarUbicaciones(oldEmpresa, true);
      }
    });
  </script>
</body>

</html>