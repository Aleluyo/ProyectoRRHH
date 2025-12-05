<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

$area_session = htmlspecialchars($_SESSION['area'] ?? '', ENT_QUOTES, 'UTF-8');
$puesto_session = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Altas & Reingresos · Empleados</title>
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
                        sans: ['DM Sans', 'system-ui', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 10px 28px rgba(10,42,94,.08)'
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
</head>

<body class="min-h-screen bg-white text-vc-ink font-sans relative">

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
                    <?= $puesto_session ?><?= $area_session ? ' &mdash; ' . $area_session : '' ?>
                </span>
                <a href="<?= url('logout.php') ?>"
                    class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">
                    Cerrar sesión
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">
        <!-- Breadcrumb -->
        <div class="mb-5">
            <nav class="flex items-center gap-3 text-sm">
                <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="<?= url('index.php?controller=empleado&action=index') ?>"
                    class="text-muted-ink hover:text-vc-ink transition">Empleados</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-medium text-vc-pink">Altas & Reingresos</span>
            </nav>
        </div>

        <!-- Título -->
        <section class="mb-8">
            <h1 class="vice-title text-[42px] leading-tight text-vc-ink">Altas & Reingresos</h1>
            <p class="mt-2 text-base text-muted-ink">
                Gestión de nuevos ingresos y reingresos de personal
            </p>
        </section>

        <!-- Acceso rápido al formulario -->
        <section class="mb-8">
            <a href="<?= url('index.php?controller=empleado&action=create') ?>"
                class="group block rounded-2xl border-2 border-vc-teal/30 bg-gradient-to-br from-vc-teal/10 to-vc-neon/10 p-8 shadow-soft hover:shadow-xl hover:border-vc-teal transition-all duration-300">
                <div class="flex items-center gap-6">
                    <div
                        class="shrink-0 w-20 h-20 rounded-2xl bg-vc-teal/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-10 h-10 text-vc-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-vc-ink mb-1">Registrar nuevo empleado</h2>
                        <p class="text-muted-ink">
                            Acceso rápido al formulario de alta de personal. Captura datos personales, laborales y
                            documentación.
                        </p>
                    </div>
                    <div class="shrink-0">
                        <svg class="w-8 h-8 text-vc-teal group-hover:translate-x-2 transition-transform duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                </div>
            </a>
        </section>

        <!-- Últimos ingresos y reingresos -->
        <section>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-vc-ink">Últimos ingresos</h2>
                    <p class="text-sm text-muted-ink mt-1">Registro de los 10 empleados más recientes</p>
                </div>
                <div class="flex gap-2">
                    <button
                        class="px-4 py-2 rounded-lg border border-black/10 bg-white text-sm text-vc-ink hover:bg-slate-50 transition">
                        Filtrar
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Tarjeta de empleado reciente - Ejemplo -->
                <div
                    class="rounded-xl border border-black/10 bg-white p-5 shadow-soft hover:shadow-lg transition-shadow">
                    <div class="flex items-start gap-4">
                        <div
                            class="shrink-0 w-14 h-14 rounded-full bg-vc-pink/20 flex items-center justify-center text-xl font-bold text-vc-pink">
                            JD
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-vc-ink truncate">Juan Pérez Domínguez</h3>
                            <p class="text-sm text-muted-ink">Analista de Sistemas</p>
                            <p class="text-xs text-muted-ink mt-1">Tecnología · Desarrollo</p>
                            <div class="mt-3 flex items-center gap-2">
                                <span
                                    class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ● Alta
                                </span>
                                <span class="text-xs text-muted-ink">Hace 2 días</span>
                            </div>
                        </div>
                        <a href="#" class="shrink-0 p-2 rounded-lg hover:bg-slate-100 transition">
                            <svg class="w-5 h-5 text-muted-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Tarjeta de reingreso - Ejemplo -->
                <div
                    class="rounded-xl border border-black/10 bg-white p-5 shadow-soft hover:shadow-lg transition-shadow">
                    <div class="flex items-start gap-4">
                        <div
                            class="shrink-0 w-14 h-14 rounded-full bg-vc-teal/20 flex items-center justify-center text-xl font-bold text-vc-teal">
                            MG
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-vc-ink truncate">María García López</h3>
                            <p class="text-sm text-muted-ink">Coordinadora de Ventas</p>
                            <p class="text-xs text-muted-ink mt-1">Comercial · Ventas</p>
                            <div class="mt-3 flex items-center gap-2">
                                <span
                                    class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    ↻ Reingreso
                                </span>
                                <span class="text-xs text-muted-ink">Hace 5 días</span>
                            </div>
                        </div>
                        <a href="#" class="shrink-0 p-2 rounded-lg hover:bg-slate-100 transition">
                            <svg class="w-5 h-5 text-muted-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Mensaje cuando no hay datos -->
                <div
                    class="md:col-span-2 rounded-xl border-2 border-dashed border-black/10 bg-slate-50/50 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-muted-ink/40 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-muted-ink font-medium">No hay registros recientes de altas o reingresos</p>
                    <p class="text-sm text-muted-ink mt-2">Los nuevos ingresos aparecerán aquí automáticamente</p>
                </div>
            </div>

            <!-- Botón ver todos -->
            <div class="mt-6 text-center">
                <a href="<?= url('index.php?controller=empleado&action=listado&estado=ACTIVO') ?>"
                    class="inline-flex items-center px-6 py-3 rounded-lg border border-black/10 bg-white text-sm font-medium text-vc-ink hover:bg-slate-50 transition">
                    Ver todos los empleados activos
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </section>
    </main>
</body>

</html>