<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';

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
    <title>Empleados · Gestión de Personal</title>
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
    <link
        href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
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

    <!-- Contenido principal -->
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
                <span class="font-medium text-vc-pink">Empleados</span>
            </nav>
        </div>

        <!-- Título -->
        <section class="text-center mb-8">
            <h1 class="vice-title text-[40px] leading-tight text-vc-ink">Gestión de Empleados</h1>
            <p class="mt-2 text-sm sm:text-base text-muted-ink">
                Selecciona el módulo que deseas gestionar
            </p>
        </section>

        <!-- Tarjetas del menú -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto">

            <!-- 1. Expedientes -->
            <a href="<?= url('index.php?controller=empleado&action=listado') ?>"
                class="group relative rounded-xl border border-black/10 bg-white p-6 hover:border-vc-pink/30 transition shadow-soft hover:shadow-lg">
                <span
                    class="absolute left-0 top-0 h-full w-1.5 rounded-l-xl bg-vc-pink/30 group-hover:bg-vc-pink/50"></span>

                <div class="flex items-start gap-4">
                    <div class="shrink-0 rounded-lg border border-black/10 bg-vc-pink/10 p-3 text-vc-pink">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-display text-xl text-vc-ink mb-1">Expedientes</h3>
                        <p class="text-sm text-muted-ink">
                            Búsqueda y gestión de empleados
                        </p>
                        <p class="mt-2 text-xs text-muted-ink">
                            Consulta y edición de expedientes de empleados. Acceso a datos personales, contactos,
                            documentos e historial.
                        </p>
                    </div>

                    <div class="opacity-0 group-hover:opacity-100 transition text-vc-pink">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </div>
                </div>
            </a>

            <!-- 2. Altas & Reingresos -->
            <a href="<?= url('index.php?controller=empleado&action=altas') ?>"
                class="group relative rounded-xl border border-black/10 bg-white p-6 hover:border-vc-teal/30 transition shadow-soft hover:shadow-lg">
                <span
                    class="absolute left-0 top-0 h-full w-1.5 rounded-l-xl bg-vc-teal/30 group-hover:bg-vc-teal/50"></span>

                <div class="flex items-start gap-4">
                    <div class="shrink-0 rounded-lg border border-black/10 bg-vc-teal/10 p-3 text-vc-teal">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-display text-xl text-vc-ink mb-1">Altas & Reingresos</h3>
                        <p class="text-sm text-muted-ink">
                            Ingresos de nuevos colaboradores
                        </p>
                        <p class="mt-2 text-xs text-muted-ink">
                            Registrar nuevos ingresos y reingresos de empleados. Formulario de alta con datos
                            personales, puesto y ubicación.
                        </p>
                    </div>

                    <div class="opacity-0 group-hover:opacity-100 transition text-vc-teal">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </div>
                </div>
            </a>

            <!-- 3. Movimientos -->
            <a href="<?= url('index.php?controller=empleado&action=movimientos') ?>"
                class="group relative rounded-xl border border-black/10 bg-white p-6 hover:border-vc-peach/30 transition shadow-soft hover:shadow-lg">
                <span
                    class="absolute left-0 top-0 h-full w-1.5 rounded-l-xl bg-vc-peach/40 group-hover:bg-vc-peach/60"></span>

                <div class="flex items-start gap-4">
                    <div class="shrink-0 rounded-lg border border-black/10 bg-vc-peach/10 p-3 text-vc-peach">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-display text-xl text-vc-ink mb-1">Movimientos</h3>
                        <p class="text-sm text-muted-ink">
                            Bajas y cambios administrativos
                        </p>
                        <p class="mt-2 text-xs text-muted-ink">
                            Registrar bajas y cambios administrativos: cambio de puesto, área, jefe inmediato o
                            ubicación.
                        </p>
                    </div>

                    <div class="opacity-0 group-hover:opacity-100 transition text-vc-peach">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </div>
                </div>
            </a>

            <!-- 4. Documentos -->
            <a href="<?= url('index.php?controller=empleado&action=documentos') ?>"
                class="group relative rounded-xl border border-black/10 bg-white p-6 hover:border-vc-sand/30 transition shadow-soft hover:shadow-lg">
                <span
                    class="absolute left-0 top-0 h-full w-1.5 rounded-l-xl bg-vc-sand/50 group-hover:bg-vc-sand/70"></span>

                <div class="flex items-start gap-4">
                    <div class="shrink-0 rounded-lg border border-black/10 bg-vc-sand/10 p-3 text-vc-sand">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-display text-xl text-vc-ink mb-1">Documentos</h3>
                        <p class="text-sm text-muted-ink">
                            Carga y validación de expedientes
                        </p>
                        <p class="mt-2 text-xs text-muted-ink">
                            Carga y revisión de documentos del expediente. Verificación de INE, CURP, contratos y otros
                            documentos.
                        </p>
                    </div>

                    <div class="opacity-0 group-hover:opacity-100 transition" style="color: #d4a574;">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </div>
                </div>
            </a>

        </section>
    </main>
</body>

</html>