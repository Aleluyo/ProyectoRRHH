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

if (!isset($empleado) || !$empleado) {
    header('Location: ?controller=empleado&action=listado');
    exit;
}

if (!isset($movimientos) || !is_array($movimientos)) {
    $movimientos = [];
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Historial de Movimientos · <?= htmlspecialchars($empleado['nombre'], ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{vc:{pink:'#ff78b5',peach:'#ffc9a9',teal:'#36d1cc',sand:'#ffe9c7',ink:'#0a2a5e',neon:'#a7fffd'}},fontFamily:{display:['Josefin Sans','system-ui','sans-serif'],sans:['DM Sans','system-ui','sans-serif'],vice:['Rage Italic','Yellowtail','cursive']},boxShadow:{soft:'0 10px 28px rgba(10,42,94,.08)'},backgroundImage:{gridglow:'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',ribbon:'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'}}}}</script>
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
                <a href="<?= url('logout.php') ?>" class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 sm:px-6 py-8 relative">
        <div class="mb-5">
            <nav class="flex items-center gap-3 text-sm">
                <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <a href="<?= url('index.php?controller=empleado&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">Empleados</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="font-medium text-vc-pink">Historial</span>
            </nav>
        </div>

        <div class="mb-6">
            <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Historial de Movimientos</h1>
            <p class="mt-1 text-sm text-muted-ink"><?= htmlspecialchars($empleado['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="rounded-xl border border-black/10 bg-white/90 p-6 shadow-soft mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-muted-ink">CURP:</span>
                    <p class="font-medium text-vc-ink"><?= htmlspecialchars($empleado['curp'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div>
                    <span class="text-muted-ink">Estado:</span>
                    <?php
                    $estadoBadge = $empleado['estado'] === 'ACTIVO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    ?>
                    <p class="mt-1">
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?= $estadoBadge ?>">
                            <?= htmlspecialchars($empleado['estado'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </p>
                </div>
                <div>
                    <span class="text-muted-ink">Empresa:</span>
                    <p class="font-medium text-vc-ink"><?= htmlspecialchars($empleado['empresa_nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div>
                    <span class="text-muted-ink">Área:</span>
                    <p class="font-medium text-vc-ink"><?= htmlspecialchars($empleado['nombre_area'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-black/10 bg-white/90 p-8 shadow-soft">
            <h2 class="text-xl font-display font-bold text-vc-ink mb-6">
                Línea de Tiempo
                <span class="text-sm font-normal text-muted-ink ml-2">(<?= count($movimientos) ?> movimientos)</span>
            </h2>

            <?php if (count($movimientos) > 0): ?>
                <div class="relative">
                    <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                    <div class="space-y-6">
                        <?php foreach ($movimientos as $mov): ?>
                            <div class="relative pl-20">
                                <?php
                                $dotColor = match ($mov['tipo_movimiento']) {
                                    'BAJA' => 'bg-red-500',
                                    'CAMBIO_AREA' => 'bg-blue-500',
                                    'CAMBIO_PUESTO' => 'bg-purple-500',
                                    'CAMBIO_JEFE' => 'bg-yellow-500',
                                    default => 'bg-gray-500'
                                };
                                ?>
                                <div class="absolute left-6 top-3 w-5 h-5 rounded-full border-4 border-white <?= $dotColor ?> z-10"></div>

                                <div class="absolute left-0 top-1 text-right" style="width: 60px;">
                                    <div class="text-xs font-medium text-vc-ink">
                                        <?= date('d/m/Y', strtotime($mov['fecha_movimiento'])) ?>
                                    </div>
                                    <div class="text-xs text-muted-ink">
                                        <?= date('H:i', strtotime($mov['fecha_registro'])) ?>
                                    </div>
                                </div>

                                <div class="bg-slate-50 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-start justify-between mb-2">
                                        <?php
                                        $badgeClass = match ($mov['tipo_movimiento']) {
                                            'BAJA' => 'bg-red-100 text-red-800',
                                            'CAMBIO_AREA' => 'bg-blue-100 text-blue-800',
                                            'CAMBIO_PUESTO' => 'bg-purple-100 text-purple-800',
                                            'CAMBIO_JEFE' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        $tipoNombre = Movimiento::tiposMovimiento()[$mov['tipo_movimiento']] ?? $mov['tipo_movimiento'];
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $badgeClass ?>">
                                            <?= htmlspecialchars($tipoNombre, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <a href="?controller=movimiento&action=ver&id=<?= $mov['id_movimiento'] ?>"
                                            class="text-xs text-vc-teal hover:underline">
                                            Ver detalle →
                                        </a>
                                    </div>

                                    <p class="text-sm font-medium text-vc-ink mb-2">
                                        <?= htmlspecialchars($mov['motivo'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <?php if (!empty($mov['valor_anterior']) || !empty($mov['valor_nuevo'])): ?>
                                        <div class="flex items-center space-x-2 text-xs text-muted-ink">
                                            <span class="bg-white px-2 py-1 rounded">
                                                <?= htmlspecialchars($mov['valor_anterior'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <span>→</span>
                                            <span class="bg-vc-teal bg-opacity-20 px-2 py-1 rounded font-medium">
                                                <?= htmlspecialchars($mov['valor_nuevo'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($mov['observaciones'])): ?>
                                        <p class="text-xs text-muted-ink mt-2 italic">
                                            <?= htmlspecialchars($mov['observaciones'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="text-xs text-muted-ink mt-2">
                                        Autorizado por: <?= htmlspecialchars($mov['usuario_registro'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <p class="text-muted-ink text-lg">Este empleado no tiene movimientos registrados</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-6 flex justify-between">
            <a href="<?= url('index.php?controller=empleado&action=show&id=' . $empleado['id_empleado']) ?>"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                Volver al Expediente
            </a>
            <div class="flex space-x-3">
                <a href="<?= url('index.php?controller=movimiento&action=listado') ?>"
                    class="px-4 py-2 bg-vc-teal text-white rounded-lg hover:bg-opacity-90 transition text-sm">
                    Todos los Movimientos
                </a>
                <button onclick="window.print()"
                    class="px-4 py-2 bg-vc-pink text-white rounded-lg hover:bg-opacity-90 transition text-sm">
                    Imprimir
                </button>
            </div>
        </div>
    </main>
</body>
</html>
