<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
        session_start();
}

$area   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// $registros y $empleados vienen del controlador
$idEmpleado = isset($_GET['id_empleado']) ? (int)$_GET['id_empleado'] : 0;
$desde      = $_GET['desde'] ?? '';
$hasta      = $_GET['hasta'] ?? '';
$tipo       = $_GET['tipo'] ?? '';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Asistencia · Entradas, salidas y faltas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

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
                    },
                    boxShadow: { soft:'0 10px 28px rgba(10,42,94,.08)' }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <a href="<?= url('logout.php') ?>" class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">
                    Cerrar sesión
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">
        <?php if ($flashSuccess): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Listo',
                    text: <?= json_encode($flashSuccess, JSON_UNESCAPED_UNICODE) ?>,
                    iconColor: '#36d1cc'
                });
            </script>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Ocurrió un problema',
                    text: <?= json_encode($flashError, JSON_UNESCAPED_UNICODE) ?>,
                    iconColor: '#ff78b5'
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
                <span class="font-medium text-vc-pink">Asistencia</span>
            </nav>
        </div>

        <!-- Título + botón -->
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Asistencia</h1>
                <p class="mt-1 text-sm sm:text-base text-muted-ink">
                    Control de entradas, salidas y faltas.
                </p>
            </div>

            <a
                href="<?= url('index.php?controller=asistencia&action=create') ?>"
                class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-4 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
            >
                Registrar asistencia manual
            </a>
        </section>

        <!-- Filtros -->
        <section class="mt-6 mb-4">
            <form method="GET" action="<?= url('index.php') ?>" class="grid gap-4 sm:grid-cols-5 bg-white/90 rounded-xl border border-black/10 p-4 shadow-soft">
                <input type="hidden" name="controller" value="asistencia">
                <input type="hidden" name="action" value="index">

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-vc-ink mb-1">Empleado</label>
                    <select
                        name="id_empleado"
                        class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                    >
                        <option value="0">Todos</option>
                        <?php foreach ($empleados as $emp): ?>
                            <option value="<?= (int)$emp['id_empleado'] ?>"
                                <?= $idEmpleado === (int)$emp['id_empleado'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-vc-ink mb-1">Desde</label>
                    <input
                        type="date"
                        name="desde"
                        value="<?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?>"
                        class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-vc-ink mb-1">Hasta</label>
                    <input
                        type="date"
                        name="hasta"
                        value="<?= htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') ?>"
                        class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-vc-ink mb-1">Tipo</label>
                    <select
                        name="tipo"
                        class="w-full rounded-lg border border-black/10 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                    >
                        <option value="">Todos</option>
                        <?php foreach (['NORMAL','RETARDO','FALTA','JUSTIFICADO'] as $t): ?>
                            <option value="<?= $t ?>" <?= $tipo === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-5 flex justify-end gap-3">
                    <a
                        href="<?= url('index.php?controller=asistencia&action=index') ?>"
                        class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-xs font-medium text-muted-ink hover:bg-slate-50"
                    >
                        Limpiar
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-4 py-2 text-xs font-semibold text-vc-ink shadow-soft hover:bg-vc-neon/80"
                    >
                        Aplicar filtros
                    </button>
                </div>
            </form>
        </section>

        <!-- Tabla -->
        <section>
            <div class="overflow-x-auto rounded-xl border border-black/10 bg-white/90 shadow-soft">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100/80 text-xs uppercase tracking-wide text-muted-ink">
                        <tr>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-left">Empleado</th>
                            <th class="px-3 py-2 text-left">Entrada</th>
                            <th class="px-3 py-2 text-left">Salida</th>
                            <th class="px-3 py-2 text-left">Tipo</th>
                            <th class="px-3 py-2 text-left">Origen</th>
                            <th class="px-3 py-2 text-left">Observaciones</th>
                            <th class="px-3 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                    <?php if (empty($registros)): ?>
                        <tr>
                            <td colspan="8" class="px-3 py-4 text-center text-sm text-muted-ink">
                                No hay registros de asistencia para los filtros seleccionados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registros as $reg): ?>
                            <tr class="border-t border-slate-200 hover:bg-slate-50">
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <?= htmlspecialchars($reg['fecha'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <?= htmlspecialchars($reg['nombre_empleado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <?= htmlspecialchars($reg['hora_entrada'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <?= htmlspecialchars($reg['hora_salida'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <?= htmlspecialchars($reg['tipo'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <?= htmlspecialchars($reg['origen'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-3 py-2 text-xs max-w-xs truncate" title="<?= htmlspecialchars($reg['observaciones'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($reg['observaciones'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <a
                                        href="<?= url('index.php?controller=asistencia&action=edit&id=' . (int)$reg['id_asistencia']) ?>"
                                        class="inline-flex items-center justify-center rounded-md border border-black/10 bg-white px-2 py-1 text-xs hover:bg-vc-sand/60"
                                    >
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
