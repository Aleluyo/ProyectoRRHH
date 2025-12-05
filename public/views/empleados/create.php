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
    <title>Nuevo Empleado · Alta de Personal</title>
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
  <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
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

    <main class="mx-auto max-w-5xl px-4 sm:px-6 py-8 relative">
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
                <a href="<?= url('index.php?controller=empleado&action=altas') ?>"
                    class="text-muted-ink hover:text-vc-ink transition">Altas & Reingresos</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-medium text-vc-pink">Nuevo Empleado</span>
            </nav>
        </div>

        <!-- Título -->
        <div class="mb-6">
            <h1 class="vice-title text-[32px] leading-tight text-vc-ink">Alta de Nuevo Empleado</h1>
            <p class="mt-1 text-sm text-muted-ink">
                Completa la información del nuevo colaborador
            . Todos los campos son obligatorios.
            </p>
        </div>

        <!-- Formulario -->
        <form method="POST" action="<?= url('index.php?controller=empleado&action=store') ?>" class="space-y-6">
            
            <!-- Datos personales -->
            <section class="rounded-xl border border-black/10 bg-white p-6 shadow-soft">
                <h2 class="text-lg font-semibold text-vc-ink mb-4">Datos personales</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Nombre completo *</label>
                        <input type="text" name="nombre" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">CURP *</label>
                        <input type="text" name="curp" required maxlength="18"
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">RFC *</label>
                        <input type="text" name="rfc" required maxlength="13"
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">NSS *</label>
                        <input type="text" name="nss" required maxlength="15"
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Fecha de nacimiento *</label>
                        <input type="date" name="fecha_nacimiento" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Género *</label>
                        <select name="genero" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Seleccione...</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="OTRO">Otro</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Estado civil *</label>
                        <input type="text" name="estado_civil" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Teléfono *</label>
                        <input type="tel" name="telefono" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-vc-ink mb-1">Correo personal *</label>
                        <input type="email" name="correo" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-vc-ink mb-1">Dirección *</label>
                        <textarea name="direccion" rows="2" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"></textarea>
                    </div>
                </div>
            </section>

            <!-- Datos laborales -->
            <section class="rounded-xl border border-black/10 bg-white p-6 shadow-soft">
                <h2 class="text-lg font-semibold text-vc-ink mb-4">Datos laborales</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Empresa *</label>
                        <select name="id_empresa" id="id_empresa" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Seleccione...</option>
                            <?php foreach ($empresas as $empresa): ?>
                                    <option value="<?= $empresa['id_empresa'] ?>">
                                        <?= htmlspecialchars($empresa['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Área *</label>
                        <select name="id_area" id="id_area" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Seleccione...</option>
                            <?php foreach ($areas as $area): ?>
                                    <option value="<?= $area['id_area'] ?>">
                                        <?= htmlspecialchars($area['nombre_area'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Puesto *</label>
                        <select name="id_puesto" id="id_puesto" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Seleccione...</option>
                            <?php foreach ($puestos as $puesto): ?>
                                    <option value="<?= $puesto['id_puesto'] ?>">
                                        <?= htmlspecialchars($puesto['nombre_puesto'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Fecha de ingreso *</label>
                        <input type="date" name="fecha_ingreso" required value="<?= date('Y-m-d') ?>"
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Ubicación *</label>
                        <select name="id_ubicacion" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Seleccione...</option>
                            <?php foreach ($ubicaciones as $ubicacion): ?>
                                    <option value="<?= $ubicacion['id_ubicacion'] ?>">
                                        <?= htmlspecialchars($ubicacion['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-vc-ink mb-1">Turno *</label>
                        <select name="id_turno" required
                            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Seleccione...</option>
                            <?php foreach ($turnos as $turno): ?>
                                    <option value="<?= $turno['id_turno'] ?>">
                                        <?= htmlspecialchars($turno['nombre_turno'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Botones de acción -->
            <div class="flex gap-3 justify-end">
                <a href="<?= url('index.php?controller=empleado&action=altas') ?>"
                    class="px-4 py-2 rounded-lg border border-black/10 bg-white text-vc-ink text-sm font-medium hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-vc-teal text-vc-ink text-sm font-medium hover:bg-vc-neon/80 transition">
                    Registrar empleado
                </button>
            </div>
        </form>
    </main>
</body>

</html>