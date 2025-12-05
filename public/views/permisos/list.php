<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
require_once __DIR__ . '/../../../app/models/Empresa.php';
require_once __DIR__ . '/../../../app/models/Empleado.php';

requireLogin();

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
$old          = $_SESSION['old_input']     ?? [];
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['old_input']);

$area   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// Valores por defecto para evitar warnings si el controlador no pas├│ datos
if (!isset($politicas) || !is_array($politicas)) {
  $politicas = [];
}
if (!isset($solicitudes) || !is_array($solicitudes)) {
  $solicitudes = [];
}
if (!isset($pendientes) || !is_array($pendientes)) {
  $pendientes = [];
}
// Carga defensiva de cat├ílogos si se accede directo a la vista
if (!isset($empresas) || !is_array($empresas)) {
  $empresas = Empresa::getActivasParaCombo();
}
if (!isset($empleados) || !is_array($empleados)) {
  $empleados = Empleado::all(500, 0, null, 'ACTIVO');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Permisos y Vacaciones - RRHH</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
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
        <span class="hidden sm:inline-block truncate max-w-[200px]">
          <?= $puesto ?><?= $area ? ' ΓÇö ' . $area : '' ?><?= $ciudad ? ' ΓÇö ' . $ciudad : '' ?>
        </span>
        <a href="<?= url('logout.php') ?>" class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative space-y-8">
    <div class="mb-2 flex items-center gap-3 text-sm">
      <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
      <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
      <span class="font-medium text-vc-pink">Permisos & Vacaciones</span>
    </div>

    <section class="text-center">
      <h1 class="vice-title text-[40px] leading-tight text-vc-ink">Permisos & Vacaciones</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">Crea políticas, recibe solicitudes y aprueba en niveles.</p>
    </section>

    <?php if ($flashSuccess): ?>
      <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm shadow-soft">
        <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
      <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm shadow-soft">
        <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="rounded-xl border border-black/5 bg-white/80 shadow-soft">
        <div class="flex items-center justify-between px-4 py-3 border-b border-black/5">
          <div>
            <h2 class="font-display text-lg text-vc-ink">Políticas de vacaciones</h2>
            <p class="text-sm text-muted-ink">Definir días base, incrementos y tope.</p>
          </div>
        </div>
        <div class="p-4 space-y-3">
          <form class="grid grid-cols-1 md:grid-cols-2 gap-3" method="POST" action="<?= url('index.php?controller=permiso&action=guardarPolitica') ?>">
            <input type="hidden" name="controller" value="permiso">
            <input type="hidden" name="action" value="guardarPolitica">
            <div>
              <label class="text-sm text-muted-ink">Empresa</label>
              <select name="id_empresa" class="w-full rounded-lg border border-black/10 px-3 py-2" required>
                <option value="">Selecciona</option>
                <?php foreach ($empresas as $emp): ?>
                  <option value="<?= (int)$emp['id_empresa'] ?>"><?= htmlspecialchars($emp['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="text-sm text-muted-ink">Días inicio</label>
              <input type="number" name="dias_inicio" min="1" class="w-full rounded-lg border border-black/10 px-3 py-2" required>
            </div>
            <div>
              <label class="text-sm text-muted-ink">Incremento anual</label>
              <input type="number" name="incremento_anual" min="0" class="w-full rounded-lg border border-black/10 px-3 py-2" required>
            </div>
            <div>
              <label class="text-sm text-muted-ink">Días máximo</label>
              <input type="number" name="dias_max" min="1" class="w-full rounded-lg border border-black/10 px-3 py-2" required>
            </div>
            <div>
              <label class="text-sm text-muted-ink">Inicio de ciclo (opcional)</label>
              <input type="date" name="periodo_anual_inicio" class="w-full rounded-lg border border-black/10 px-3 py-2">
            </div>
            <div class="flex items-center gap-2 pt-6">
              <input type="checkbox" name="activa" value="1" class="rounded border-black/20" checked>
              <span class="text-sm">Activa</span>
            </div>
            <div class="md:col-span-2">
              <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-vc-teal px-4 py-2 text-sm font-semibold text-vc-ink shadow-soft hover:translate-y-[1px] transition">
                Guardar política
              </button>
            </div>
          </form>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="text-xs uppercase text-muted-ink">
                <tr>
                  <th class="text-left py-2">Empresa</th>
                  <th class="text-left py-2">Base</th>
                  <th class="text-left py-2">+Año</th>
                  <th class="text-left py-2">Máx</th>
                  <th class="text-left py-2">Ciclo</th>
                  <th class="text-left py-2">Activa</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-black/5">
                <?php foreach ($politicas as $p): ?>
                  <tr>
                    <td class="py-2 pr-3"><?= htmlspecialchars($p['empresa_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="py-2"><?= (int)$p['dias_inicio'] ?></td>
                    <td class="py-2">+<?= (int)$p['incremento_anual'] ?></td>
                    <td class="py-2"><?= (int)$p['dias_max'] ?></td>
                    <td class="py-2"><?= htmlspecialchars((string)$p['periodo_anual_inicio'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="py-2"><?= ((int)$p['activa'] === 1) ? 'Sí' : 'No' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-black/5 bg-white/80 shadow-soft">
        <div class="flex items-center justify-between px-4 py-3 border-b border-black/5">
          <div>
            <h2 class="font-display text-lg text-vc-ink">Crear solicitud</h2>
            <p class="text-sm text-muted-ink">Vacaciones, permisos o incapacidades.</p>
          </div>
        </div>
        <form class="p-4 space-y-3" method="POST" action="<?= url('index.php?controller=permiso&action=guardarSolicitud') ?>">
          <input type="hidden" name="controller" value="permiso">
          <input type="hidden" name="action" value="guardarSolicitud">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="text-sm text-muted-ink">Empleado</label>
              <select name="id_empleado" id="select-empleado" class="w-full rounded-lg border border-black/10 px-3 py-2" required>
                <option value="">Selecciona</option>
                <?php foreach ($empleados as $emp): ?>
                  <option value="<?= (int)$emp['id_empleado'] ?>"><?= htmlspecialchars($emp['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="text-sm text-muted-ink">Tipo</label>
              <select name="tipo" id="select-tipo" class="w-full rounded-lg border border-black/10 px-3 py-2" required>
                <option value="VACACIONES">Vacaciones</option>
                <option value="PERMISO">Permiso</option>
                <option value="INCAPACIDAD">Incapacidad</option>
                <option value="OTRO">Otro</option>
              </select>
            </div>
            <div>
              <label class="text-sm text-muted-ink">Fecha inicio</label>
              <input type="date" name="fecha_inicio" class="w-full rounded-lg border border-black/10 px-3 py-2" required>
            </div>
            <div>
              <label class="text-sm text-muted-ink">Fecha fin</label>
              <input type="date" name="fecha_fin" class="w-full rounded-lg border border-black/10 px-3 py-2" required>
            </div>
            <div id="vacaciones-info" class="hidden md:col-span-2 rounded-lg bg-vc-teal/10 border border-vc-teal/20 px-3 py-2">
              <div class="flex items-center justify-between">
                <span class="text-sm text-vc-ink">Días disponibles:</span>
                <span id="dias-disponibles" class="text-lg font-semibold text-vc-teal">--</span>
              </div>
            </div>
            <div>
              <label class="text-sm text-muted-ink">Días (opcional)</label>
              <input type="number" step="0.5" name="dias" id="input-dias" class="w-full rounded-lg border border-black/10 px-3 py-2" placeholder="Calcula automático si se deja vacío">
              <p id="warning-dias" class="hidden text-xs text-rose-600 mt-1">⚠️ Supera los días disponibles</p>
            </div>
            <div class="md:col-span-2">
              <label class="text-sm text-muted-ink">Motivo</label>
              <textarea name="motivo" rows="3" class="w-full rounded-lg border border-black/10 px-3 py-2" placeholder="Describe el motivo"></textarea>
            </div>
          </div>
          <div class="flex justify-end">
            <button type="submit" id="btn-enviar" class="inline-flex items-center gap-2 rounded-lg bg-vc-pink px-4 py-2 text-sm font-semibold text-vc-ink shadow-soft hover:translate-y-[1px] transition">
              Enviar solicitud
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Sección de saldo actual del usuario (si está disponible) -->
    <?php if ($saldoActual): ?>
    <div class="rounded-xl border border-black/5 bg-gradient-to-br from-vc-teal/5 to-vc-pink/5 shadow-soft p-4">
      <h3 class="font-display text-lg text-vc-ink mb-2">Mis Días de Vacaciones <?= date('Y') ?></h3>
      <div class="grid grid-cols-3 gap-4 text-center">
        <div>
          <p class="text-2xl font-bold text-vc-teal"><?= number_format((float)$saldoActual['dias_asignados'], 1) ?></p>
          <p class="text-xs text-muted-ink">Asignados</p>
        </div>
        <div>
          <p class="text-2xl font-bold text-vc-pink"><?= number_format((float)$saldoActual['dias_tomados'], 1) ?></p>
          <p class="text-xs text-muted-ink">Tomados</p>
        </div>
        <div>
          <p class="text-2xl font-bold text-vc-ink"><?= number_format((float)$saldoActual['dias_disponibles'], 1) ?></p>
          <p class="text-xs text-muted-ink">Disponibles</p>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="rounded-xl border border-black/5 bg-white/80 shadow-soft">
      <div class="flex items-center justify-between px-4 py-3 border-b border-black/5">
        <div>
          <h2 class="font-display text-lg text-vc-ink">Solicitudes recientes</h2>
          <p class="text-sm text-muted-ink">Incluye el resumen de aprobaciones por nivel.</p>
        </div>
      </div>
      <div class="p-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase text-muted-ink">
            <tr>
              <th class="text-left py-2">Empleado</th>
              <th class="text-left py-2">Tipo</th>
              <th class="text-left py-2">Inicio</th>
              <th class="text-left py-2">Fin</th>
              <th class="text-left py-2">D├¡as</th>
              <th class="text-left py-2">Estado</th>
              <th class="text-left py-2">Aprobaciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black/5">
            <?php foreach ($solicitudes as $s): ?>
              <tr>
                <td class="py-2 pr-3"><?= htmlspecialchars($s['empleado_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="py-2"><?= htmlspecialchars($s['tipo'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="py-2"><?= htmlspecialchars($s['fecha_inicio'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="py-2"><?= htmlspecialchars($s['fecha_fin'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="py-2"><?= htmlspecialchars((string)$s['dias'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="py-2 font-semibold <?= $s['estado'] === 'APROBADO' ? 'text-emerald-700' : ($s['estado'] === 'RECHAZADO' ? 'text-rose-700' : 'text-amber-700') ?>"><?= htmlspecialchars($s['estado'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="py-2 text-xs text-muted-ink max-w-[220px]"><?= htmlspecialchars((string)$s['aprobaciones'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="rounded-xl border border-black/5 bg-white/80 shadow-soft">
      <div class="flex items-center justify-between px-4 py-3 border-b border-black/5">
        <div>
          <h2 class="font-display text-lg text-vc-ink">Aprobaciones pendientes</h2>
          <p class="text-sm text-muted-ink">Solo las asignadas a tu usuario.</p>
        </div>
      </div>
      <div class="p-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase text-muted-ink">
            <tr>
              <th class="text-left py-2">Empleado</th>
              <th class="text-left py-2">Tipo</th>
              <th class="text-left py-2">Rango</th>
              <th class="text-left py-2">Acción</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black/5">
            <?php foreach ($pendientes as $p): ?>
              <tr>
                <td class="py-2 pr-3"><?= htmlspecialchars($p['empleado_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="py-2"><?= htmlspecialchars($p['tipo'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="py-2"><?= htmlspecialchars($p['fecha_inicio'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($p['fecha_fin'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="py-2">
                  <form class="flex items-center gap-2" method="POST" action="<?= url('index.php?controller=permiso&action=decidir') ?>">
                    <input type="hidden" name="id_aprobacion" value="<?= (int)$p['id_aprobacion'] ?>">
                    <input type="hidden" name="controller" value="permiso">
                    <input type="hidden" name="action" value="decidir">
                    <select name="decision" class="rounded-lg border border-black/10 px-2 py-1 text-sm">
                      <option value="APROBADO">Aprobar</option>
                      <option value="RECHAZADO">Rechazar</option>
                    </select>
                    <input type="text" name="comentario" placeholder="Comentario" class="rounded-lg border border-black/10 px-2 py-1 text-sm">
                    <button type="submit" class="rounded-lg bg-vc-teal px-3 py-1 text-xs font-semibold text-vc-ink shadow-soft">Aplicar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($pendientes)): ?>
              <tr><td class="py-2 text-muted-ink" colspan="4">Sin pendientes.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const selectEmpleado = document.getElementById('select-empleado');
      const selectTipo = document.getElementById('select-tipo');
      const vacacionesInfo = document.getElementById('vacaciones-info');
      const diasDisponibles = document.getElementById('dias-disponibles');
      const inputDias = document.getElementById('input-dias');
      const warningDias = document.getElementById('warning-dias');
      const btnEnviar = document.getElementById('btn-enviar');
      
      let saldoActual = null;

      // Cargar saldo cuando se selecciona un empleado
      selectEmpleado.addEventListener('change', function() {
        const idEmpleado = this.value;
        if (idEmpleado && selectTipo.value === 'VACACIONES') {
          cargarSaldo(idEmpleado);
        } else {
          ocultarInfo();
        }
      });

      // Mostrar/ocultar info según tipo
      selectTipo.addEventListener('change', function() {
        if (this.value === 'VACACIONES' && selectEmpleado.value) {
          cargarSaldo(selectEmpleado.value);
        } else {
          ocultarInfo();
        }
      });

      // Validar días solicitados
      inputDias.addEventListener('input', function() {
        validarDias();
      });

      function cargarSaldo(idEmpleado) {
        fetch(`index.php?controller=permiso&action=obtenerSaldo&id_empleado=${idEmpleado}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              saldoActual = data;
              diasDisponibles.textContent = data.dias_disponibles.toFixed(1);
              vacacionesInfo.classList.remove('hidden');
              validarDias();
            } else {
              console.error('Error al cargar saldo:', data.error);
              ocultarInfo();
            }
          })
          .catch(error => {
            console.error('Error:', error);
            ocultarInfo();
          });
      }

      function ocultarInfo() {
        vacacionesInfo.classList.add('hidden');
        warningDias.classList.add('hidden');
        saldoActual = null;
      }

      function validarDias() {
        if (!saldoActual || selectTipo.value !== 'VACACIONES') {
          warningDias.classList.add('hidden');
          return;
        }

        const diasSolicitados = parseFloat(inputDias.value);
        if (isNaN(diasSolicitados) || diasSolicitados <= 0) {
          warningDias.classList.add('hidden');
          return;
        }

        if (diasSolicitados > saldoActual.dias_disponibles) {
          warningDias.classList.remove('hidden');
        } else {
          warningDias.classList.add('hidden');
        }
      }
    });
  </script>
</body>
</html>

