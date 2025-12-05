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
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Registrar Movimiento · RRHH</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

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
                    boxShadow: {
                        soft: '0 10px 28px rgba(10,42,94,.08)'
                    },
                    backgroundImage: {
                        gridglow: 'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
                        ribbon: 'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'
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

    <main class="mx-auto max-w-4xl px-4 sm:px-6 py-8 relative">
        <div class="mb-5">
            <nav class="flex items-center gap-3 text-sm">
                <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="<?= url('index.php?controller=empleado&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">Empleados</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="?controller=movimiento&action=listado" class="text-muted-ink hover:text-vc-ink transition">Movimientos</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-medium text-vc-pink">Nuevo</span>
            </nav>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mb-6 p-4 rounded-lg <?= $_SESSION['tipo_mensaje'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                <p><?= htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <div class="mb-6">
            <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Nuevo Movimiento</h1>
            <p class="mt-1 text-sm text-muted-ink">Registrar baja o cambio administrativo</p>
        </div>

        <div class="rounded-xl border border-black/10 bg-white/90 p-8 shadow-soft">
            <form method="POST" action="?controller=movimiento&action=guardar" id="formMovimiento">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-vc-ink mb-2">Empleado *</label>
                    <select name="id_empleado" id="id_empleado" required
                        class="w-full px-4 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm">
                        <option value="">Seleccione un empleado</option>
                        <?php foreach ($empleados as $emp): ?>
                            <option value="<?= $emp['id_empleado'] ?>"
                                data-curp="<?= htmlspecialchars($emp['curp'], ENT_QUOTES, 'UTF-8') ?>"
                                data-empresa="<?= htmlspecialchars($emp['empresa_nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>"
                                data-area="<?= htmlspecialchars($emp['nombre_area'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>"
                                data-puesto="<?= htmlspecialchars($emp['nombre_puesto'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>"
                                data-id-area="<?= $emp['id_area'] ?? '' ?>"
                                data-id-puesto="<?= $emp['id_puesto'] ?? '' ?>">
                                <?= htmlspecialchars($emp['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="infoEmpleado" class="mb-6 p-4 bg-slate-50 rounded-lg hidden">
                    <h3 class="text-sm font-medium text-vc-ink mb-2">Información Actual</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-ink">CURP:</span>
                            <span id="info-curp" class="ml-2 font-medium text-vc-ink"></span>
                        </div>
                        <div>
                            <span class="text-muted-ink">Empresa:</span>
                            <span id="info-empresa" class="ml-2 font-medium text-vc-ink"></span>
                        </div>
                        <div>
                            <span class="text-muted-ink">Área:</span>
                            <span id="info-area" class="ml-2 font-medium text-vc-ink"></span>
                        </div>
                        <div>
                            <span class="text-muted-ink">Puesto:</span>
                            <span id="info-puesto" class="ml-2 font-medium text-vc-ink"></span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-vc-ink mb-2">Tipo de Movimiento *</label>
                    <select name="tipo_movimiento" id="tipo_movimiento" required
                        class="w-full px-4 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm">
                        <option value="">Seleccione el tipo</option>
                        <?php foreach ($tiposMovimiento as $codigo => $nombre): ?>
                            <option value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-vc-ink mb-2">Fecha del Movimiento *</label>
                    <input type="date" name="fecha_movimiento" required
                        value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm" />
                </div>

                <div id="campo-valor-nuevo" class="mb-6 hidden">
                    <label id="label-valor-nuevo" class="block text-sm font-medium text-vc-ink mb-2">Nuevo Valor *</label>
                    <select name="valor_nuevo" id="valor_nuevo"
                        class="w-full px-4 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm">
                        <option value="">Seleccione...</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-vc-ink mb-2">Motivo *</label>
                    <input type="text" name="motivo" required
                        placeholder="Ej: Promoción, Renuncia voluntaria, Reestructuración..."
                        class="w-full px-4 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm" />
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-vc-ink mb-2">Observaciones</label>
                    <textarea name="observaciones" rows="4"
                        placeholder="Notas adicionales sobre el movimiento..."
                        class="w-full px-4 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="?controller=movimiento&action=listado"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-vc-pink text-white rounded-lg hover:bg-opacity-90 transition text-sm font-medium">
                        Registrar Movimiento
                    </button>
                </div>

            </form>
        </div>

    </main>

    <script>
        const areas = <?= json_encode($areas) ?>;
        const puestos = <?= json_encode($puestos) ?>;
        const empleados = <?= json_encode($empleados) ?>;

        const selectEmpleado = document.getElementById('id_empleado');
        const selectTipoMovimiento = document.getElementById('tipo_movimiento');
        const campoValorNuevo = document.getElementById('campo-valor-nuevo');
        const selectValorNuevo = document.getElementById('valor_nuevo');
        const labelValorNuevo = document.getElementById('label-valor-nuevo');
        const infoEmpleado = document.getElementById('infoEmpleado');

        selectEmpleado.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (this.value) {
                document.getElementById('info-curp').textContent = option.dataset.curp;
                document.getElementById('info-empresa').textContent = option.dataset.empresa;
                document.getElementById('info-area').textContent = option.dataset.area;
                document.getElementById('info-puesto').textContent = option.dataset.puesto;
                infoEmpleado.classList.remove('hidden');
            } else {
                infoEmpleado.classList.add('hidden');
            }
        });

        selectTipoMovimiento.addEventListener('change', function() {
            const tipo = this.value;
            const idEmpleado = selectEmpleado.value;

            selectValorNuevo.innerHTML = '<option value="">Seleccione...</option>';
            selectValorNuevo.removeAttribute('required');

            if (tipo === 'BAJA') {
                campoValorNuevo.classList.add('hidden');
            } else if (tipo === 'CAMBIO_AREA') {
                labelValorNuevo.textContent = 'Nueva Área *';
                selectValorNuevo.setAttribute('required', 'required');
                areas.forEach(area => {
                    const option = document.createElement('option');
                    option.value = area.id_area;
                    option.textContent = area.nombre_area;
                    selectValorNuevo.appendChild(option);
                });
                campoValorNuevo.classList.remove('hidden');
            } else if (tipo === 'CAMBIO_PUESTO') {
                labelValorNuevo.textContent = 'Nuevo Puesto *';
                selectValorNuevo.setAttribute('required', 'required');
                puestos.forEach(puesto => {
                    const option = document.createElement('option');
                    option.value = puesto.id_puesto;
                    option.textContent = puesto.nombre_puesto;
                    selectValorNuevo.appendChild(option);
                });
                campoValorNuevo.classList.remove('hidden');
            } else if (tipo === 'CAMBIO_JEFE') {
                labelValorNuevo.textContent = 'Nuevo Jefe Inmediato';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Sin jefe';
                selectValorNuevo.appendChild(option);
                empleados.forEach(emp => {
                    if (emp.id_empleado != idEmpleado) {
                        const option = document.createElement('option');
                        option.value = emp.id_empleado;
                        option.textContent = emp.nombre;
                        selectValorNuevo.appendChild(option);
                    }
                });
                campoValorNuevo.classList.remove('hidden');
            } else {
                campoValorNuevo.classList.add('hidden');
            }
        });
    </script>

</body>

</html>
