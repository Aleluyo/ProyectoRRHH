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

if (!isset($movimiento) || !$movimiento) {
    header('Location: ?controller=movimiento&action=listado');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Detalle de Movimiento · RRHH</title>
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

    <main class="mx-auto max-w-4xl px-4 sm:px-6 py-8 relative">
        <div class="mb-5">
            <nav class="flex items-center gap-3 text-sm">
                <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <a href="<?= url('index.php?controller=empleado&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">Empleados</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <a href="?controller=movimiento&action=listado" class="text-muted-ink hover:text-vc-ink transition">Movimientos</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="font-medium text-vc-pink">Detalle</span>
            </nav>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mb-6 p-4 rounded-lg <?= $_SESSION['tipo_mensaje'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                <p><?= htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <div class="mb-6">
            <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Detalle de Movimiento</h1>
            <p class="mt-1 text-sm text-muted-ink">ID: <?= $movimiento['id_movimiento'] ?></p>
        </div>

        <div class="rounded-xl border border-black/10 bg-white/90 p-8 shadow-soft">
            <div class="mb-6 text-center">
                <?php
                $badgeClass = match ($movimiento['tipo_movimiento']) {
                    'BAJA' => 'bg-red-100 text-red-800 border-red-200',
                    'CAMBIO_AREA' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'CAMBIO_PUESTO' => 'bg-purple-100 text-purple-800 border-purple-200',
                    'CAMBIO_JEFE' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    default => 'bg-gray-100 text-gray-800 border-gray-200'
                };
                $tipoNombre = Movimiento::tiposMovimiento()[$movimiento['tipo_movimiento']] ?? $movimiento['tipo_movimiento'];
                ?>
                <span class="inline-block px-4 py-2 text-lg font-semibold rounded-lg border-2 <?= $badgeClass ?>">
                    <?= htmlspecialchars($tipoNombre, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-muted-ink mb-2">Empleado</h3>
                    <p class="text-lg font-medium text-vc-ink">
                        <?= htmlspecialchars($movimiento['nombre_empleado'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="text-sm text-muted-ink"><?= htmlspecialchars($movimiento['curp'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-muted-ink mb-2">Fecha del Movimiento</h3>
                    <p class="text-lg font-medium text-vc-ink">
                        <?= date('d/m/Y', strtotime($movimiento['fecha_movimiento'])) ?>
                    </p>
                    <p class="text-sm text-muted-ink">Registrado: <?= date('d/m/Y H:i', strtotime($movimiento['fecha_registro'])) ?></p>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-sm font-medium text-muted-ink mb-2">Motivo</h3>
                    <p class="text-base text-vc-ink"><?= htmlspecialchars($movimiento['motivo'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <?php if (!empty($movimiento['observaciones'])): ?>
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-medium text-muted-ink mb-2">Observaciones</h3>
                        <p class="text-base text-vc-ink bg-slate-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($movimiento['observaciones'], ENT_QUOTES, 'UTF-8')) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($movimiento['valor_anterior']) || !empty($movimiento['valor_nuevo'])): ?>
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-medium text-muted-ink mb-3">Cambio Realizado</h3>
                        <div class="flex items-center justify-center space-x-6">
                            <div class="text-center flex-1 bg-slate-50 p-4 rounded-lg">
                                <p class="text-xs text-muted-ink mb-1">Valor Anterior</p>
                                <p class="text-base font-medium text-vc-ink">
                                    <?= htmlspecialchars($movimiento['valor_anterior'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                            <div class="text-2xl text-vc-teal">→</div>
                            <div class="text-center flex-1 bg-vc-teal bg-opacity-10 p-4 rounded-lg border-2 border-vc-teal">
                                <p class="text-xs text-muted-ink mb-1">Valor Nuevo</p>
                                <p class="text-base font-medium text-vc-ink">
                                    <?= htmlspecialchars($movimiento['valor_nuevo'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <h3 class="text-sm font-medium text-muted-ink mb-2">Autorizado Por</h3>
                    <p class="text-base font-medium text-vc-ink">
                        <?= htmlspecialchars($movimiento['usuario_registro'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-muted-ink mb-2">Estado Actual del Empleado</h3>
                    <?php
                    $estadoBadge = $movimiento['estado_empleado'] === 'ACTIVO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    ?>
                    <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full <?= $estadoBadge ?>">
                        <?= htmlspecialchars($movimiento['estado_empleado'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center mt-6">
            <a href="?controller=movimiento&action=historial&id_empleado=<?= $movimiento['id_empleado'] ?>"
                class="px-4 py-2 bg-vc-teal text-white rounded-lg hover:bg-opacity-90 transition text-sm">
                Ver Historial del Empleado
            </a>
            <div class="flex space-x-3">
                <a href="?controller=movimiento&action=listado"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
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
