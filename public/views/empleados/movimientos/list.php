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

if (!isset($movimientos) || !is_array($movimientos)) {
    $movimientos = [];
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Movimientos de Empleados · RRHH</title>
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
    <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
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

    <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">
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
                <span class="font-medium text-vc-pink">Movimientos</span>
            </nav>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mb-6 p-4 rounded-lg <?= $_SESSION['tipo_mensaje'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                <p><?= htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
            <div>
                <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Movimientos</h1>
                <p class="mt-1 text-sm sm:text-base text-muted-ink">
                    Registro de bajas y cambios administrativos
                </p>
            </div>

            <div class="flex gap-3">
                <a href="?controller=movimiento&action=crear"
                    class="rounded-lg border border-vc-pink bg-vc-pink px-4 py-2 text-sm font-medium text-white hover:bg-vc-pink/90 transition">
                    + Nuevo Movimiento
                </a>
            </div>
        </section>

        <div class="mb-6 rounded-xl border border-black/10 bg-white/90 p-6 shadow-soft">
            <form method="GET" class="space-y-4">
                <input type="hidden" name="controller" value="movimiento" />
                <input type="hidden" name="action" value="listado" />

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Tipo de Movimiento</label>
                        <select name="tipo" class="w-full px-3 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm">
                            <option value="">Todos</option>
                            <?php foreach ($tiposMovimiento as $codigo => $nombre): ?>
                                <option value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= (isset($_GET['tipo']) && $_GET['tipo'] === $codigo) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Empleado</label>
                        <select name="id_empleado" class="w-full px-3 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm">
                            <option value="">Todos</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?= $emp['id_empleado'] ?>"
                                    <?= (isset($_GET['id_empleado']) && $_GET['id_empleado'] == $emp['id_empleado']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio"
                            value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            class="w-full px-3 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Fecha Fin</label>
                        <input type="date" name="fecha_fin"
                            value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            class="w-full px-3 py-2 border border-black/10 rounded-lg focus:ring-2 focus:ring-vc-teal focus:border-vc-teal text-sm" />
                    </div>
                </div>

                <div class="flex justify-between items-center pt-2">
                    <div class="text-sm text-muted-ink">
                        Total: <strong class="text-vc-ink"><?= $total ?></strong> movimientos
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="px-4 py-2 bg-vc-teal text-white rounded-lg hover:bg-opacity-90 transition text-sm">
                            Buscar
                        </button>
                        <a href="?controller=movimiento&action=listado" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <section class="overflow-x-auto rounded-xl border border-black/10 bg-white/90 shadow-soft">
            <?php if (count($movimientos) > 0): ?>
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100/80 text-xs uppercase tracking-wide text-muted-ink">
                        <tr>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-left">Empleado</th>
                            <th class="px-3 py-2 text-left">Tipo</th>
                            <th class="px-3 py-2 text-left">Motivo</th>
                            <th class="px-3 py-2 text-left">Cambio</th>
                            <th class="px-3 py-2 text-left">Autorizado Por</th>
                            <th class="px-3 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        <?php foreach ($movimientos as $mov): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-sm text-vc-ink font-medium">
                                        <?= date('d/m/Y', strtotime($mov['fecha_movimiento'])) ?>
                                    </div>
                                    <div class="text-xs text-muted-ink">
                                        <?= date('H:i', strtotime($mov['fecha_registro'])) ?>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-sm font-medium text-vc-ink">
                                        <?= htmlspecialchars($mov['nombre_empleado'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="text-xs text-muted-ink">
                                        <?= htmlspecialchars($mov['curp'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <?php
                                    $badgeClass = match ($mov['tipo_movimiento']) {
                                        'BAJA' => 'bg-red-100 text-red-800',
                                        'CAMBIO_AREA' => 'bg-blue-100 text-blue-800',
                                        'CAMBIO_PUESTO' => 'bg-purple-100 text-purple-800',
                                        'CAMBIO_JEFE' => 'bg-yellow-100 text-yellow-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    $tipoNombre = $tiposMovimiento[$mov['tipo_movimiento']] ?? $mov['tipo_movimiento'];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $badgeClass ?>">
                                        <?= htmlspecialchars($tipoNombre, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-sm text-vc-ink max-w-xs truncate">
                                        <?= htmlspecialchars($mov['motivo'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs">
                                        <div class="text-muted-ink">De: <?= htmlspecialchars($mov['valor_anterior'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-vc-ink font-medium">A: <?= htmlspecialchars($mov['valor_nuevo'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-sm text-vc-ink">
                                        <?= htmlspecialchars($mov['usuario_registro'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <a href="?controller=movimiento&action=ver&id=<?= $mov['id_movimiento'] ?>"
                                        class="inline-flex items-center px-3 py-1 bg-vc-teal/10 text-vc-teal rounded-lg hover:bg-vc-teal hover:text-white transition text-xs font-medium">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-12">
                    <p class="text-muted-ink text-lg">No se encontraron movimientos</p>
                    <a href="?controller=movimiento&action=crear" class="inline-block mt-4 px-6 py-2 bg-vc-pink text-white rounded-lg hover:bg-opacity-90 transition">
                        Registrar Primer Movimiento
                    </a>
                </div>
            <?php endif; ?>
        </section>

    </main>

</body>

</html>
