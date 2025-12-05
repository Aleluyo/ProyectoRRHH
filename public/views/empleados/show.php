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

// Calcular antigüedad
$fechaIngreso = new DateTime($empleado['fecha_ingreso']);
$hoy = new DateTime();
$antiguedad = $hoy->diff($fechaIngreso);
$antiguedadTexto = $antiguedad->y . ' años, ' . $antiguedad->m . ' meses';

// Calcular edad si hay fecha de nacimiento
$edadTexto = '';
if (!empty($empleado['fecha_nacimiento'])) {
    $fechaNac = new DateTime($empleado['fecha_nacimiento']);
    $edad = $hoy->diff($fechaNac);
    $edadTexto = $edad->y . ' años';
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($empleado['nombre'], ENT_QUOTES, 'UTF-8') ?> · Expediente</title>
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
                <a href="<?= url('index.php?controller=empleado&action=listado') ?>"
                    class="text-muted-ink hover:text-vc-ink transition">Expedientes</a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span
                    class="font-medium text-vc-pink"><?= htmlspecialchars($empleado['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
            </nav>
        </div>

        <!-- Encabezado del expediente -->
        <section class="mb-6 rounded-xl border border-black/10 bg-white p-6 shadow-soft">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Avatar placeholder -->
                <div class="shrink-0">
                    <div
                        class="w-24 h-24 rounded-full bg-vc-teal/20 flex items-center justify-center text-3xl font-bold text-vc-teal">
                        <?= strtoupper(substr($empleado['nombre'], 0, 1)) ?>
                    </div>
                </div>

                <!-- Información principal -->
                <div class="flex-1">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-vc-ink">
                                <?= htmlspecialchars($empleado['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </h1>
                            <p class="text-sm text-muted-ink mt-1">
                                <?= htmlspecialchars($empleado['nombre_puesto'] ?? '', ENT_QUOTES, 'UTF-8') ?> ·
                                <?= htmlspecialchars($empleado['nombre_area'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="text-xs text-muted-ink mt-1">
                                <?= htmlspecialchars($empleado['empresa_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <?php if ($empleado['estado'] === 'ACTIVO'): ?>
                                <span
                                    class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    ● Activo
                                </span>
                            <?php else: ?>
                                <span
                                    class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    ● Baja
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-muted-ink">Fecha de ingreso:</span>
                            <p class="font-medium">
                                <?= htmlspecialchars($empleado['fecha_ingreso'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                        <div>
                            <span class="text-muted-ink">Antigüedad:</span>
                            <p class="font-medium"><?= $antiguedadTexto ?></p>
                        </div>
                        <div>
                            <span class="text-muted-ink">ID Empleado:</span>
                            <p class="font-medium">#<?= $empleado['id_empleado'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="mt-6 pt-6 border-t border-black/10 flex flex-wrap gap-3">
                <a href="<?= url('index.php?controller=empleado&action=edit&id=' . $empleado['id_empleado']) ?>"
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-vc-teal text-vc-ink text-sm font-medium hover:bg-vc-neon/80 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar datos
                </a>
                <a href="<?= url('index.php?controller=documento&action=subir&id_empleado=' . $empleado['id_empleado']) ?>"
                    class="inline-flex items-center px-4 py-2 rounded-lg border border-black/10 bg-white text-vc-ink text-sm font-medium hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Subir documento
                </a>
                <a href="<?= url('index.php?controller=movimiento&action=crear') ?>"
                    class="inline-flex items-center px-4 py-2 rounded-lg border border-black/10 bg-white text-vc-ink text-sm font-medium hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Registrar movimiento
                </a>
                <a href="<?= url('index.php?controller=movimiento&action=historial&id_empleado=' . $empleado['id_empleado']) ?>"
                    class="inline-flex items-center px-4 py-2 rounded-lg border border-black/10 bg-white text-vc-ink text-sm font-medium hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ver historial
                </a>
            </div>
        </section>

        <!-- Tabs de información -->
        <section>
            <div class="border-b border-black/10 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        class="tab-button active border-b-2 border-vc-pink py-4 px-1 text-sm font-medium text-vc-pink"
                        data-tab="personal">
                        Datos personales
                    </button>
                    <button
                        class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-muted-ink hover:text-vc-ink hover:border-gray-300"
                        data-tab="laboral">
                        Datos laborales
                    </button>
                    <button
                        class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-muted-ink hover:text-vc-ink hover:border-gray-300"
                        data-tab="bancaria">
                        Información bancaria
                    </button>
                    <button
                        class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-muted-ink hover:text-vc-ink hover:border-gray-300"
                        data-tab="contactos">
                        Contactos
                    </button>
                    <button
                        class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-muted-ink hover:text-vc-ink hover:border-gray-300"
                        data-tab="documentos">
                        Documentos
                    </button>
                    <button
                        class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-muted-ink hover:text-vc-ink hover:border-gray-300"
                        data-tab="historial">
                        Historial
                    </button>
                </nav>
            </div>

            <!-- Tab: Datos personales -->
            <div class="tab-content" id="tab-personal">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg border border-black/10 bg-white p-4">
                        <h3 class="font-semibold text-vc-ink mb-3">Identificación</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">CURP:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['curp'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">RFC:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['rfc'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">NSS:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['nss'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg border border-black/10 bg-white p-4">
                        <h3 class="font-semibold text-vc-ink mb-3">Información personal</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Fecha de nacimiento:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['fecha_nacimiento'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Edad:</dt>
                                <dd class="font-medium"><?= $edadTexto ?: 'N/A' ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Género:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['genero'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Estado civil:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['estado_civil'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg border border-black/10 bg-white p-4">
                        <h3 class="font-semibold text-vc-ink mb-3">Contacto</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Teléfono:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['telefono'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Correo:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['correo'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg border border-black/10 bg-white p-4">
                        <h3 class="font-semibold text-vc-ink mb-3">Dirección</h3>
                        <p class="text-sm">
                            <?= htmlspecialchars($empleado['direccion'] ?? 'No especificada', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tab: Datos laborales -->
            <div class="tab-content hidden" id="tab-laboral">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg border border-black/10 bg-white p-4">
                        <h3 class="font-semibold text-vc-ink mb-3">Organización</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Empresa:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['empresa_nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Área:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['nombre_area'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Puesto:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['nombre_puesto'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg border border-black/10 bg-white p-4">
                        <h3 class="font-semibold text-vc-ink mb-3">Ubicación y horario</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Ubicación:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['ubicacion_nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-ink">Turno:</dt>
                                <dd class="font-medium">
                                    <?= htmlspecialchars($empleado['turno_nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Tab: Información bancaria -->
            <div class="tab-content hidden" id="tab-bancaria">
                <?php if (!empty($cuentasBancarias)): ?>
                    <div class="space-y-3">
                        <?php foreach ($cuentasBancarias as $cuenta): ?>
                            <div class="rounded-lg border border-black/10 bg-white p-4 hover:shadow-md transition-shadow <?= $cuenta['activa'] ? '' : 'opacity-60' ?>">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="font-semibold text-vc-ink text-lg">
                                                <?= htmlspecialchars($cuenta['banco'], ENT_QUOTES, 'UTF-8') ?>
                                            </h4>
                                            <?php if ($cuenta['activa']): ?>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full border bg-green-100 text-green-800 border-green-200">
                                                    Activa
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full border bg-gray-100 text-gray-800 border-gray-200">
                                                    Inactiva
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="text-sm text-muted-ink space-y-1">
                                            <p>
                                                <span class="font-medium">CLABE:</span>
                                                <span class="font-mono"><?= htmlspecialchars($cuenta['clabe'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </p>
                                            <p>
                                                <span class="font-medium">Titular:</span>
                                                <?= htmlspecialchars($cuenta['titular'], ENT_QUOTES, 'UTF-8') ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col gap-2 ml-4">
                                        <button onclick="editarCuentaBancaria(<?= $cuenta['id_banco'] ?>)" 
                                                class="px-3 py-1 text-xs font-medium text-white bg-vc-teal rounded hover:bg-vc-teal/90 transition-colors text-center">
                                            Editar
                                        </button>
                                        
                                        <?php if ($cuenta['activa']): ?>
                                            <button onclick="desactivarCuentaBancaria(<?= $cuenta['id_banco'] ?>)" 
                                                    class="px-3 py-1 text-xs font-medium text-vc-ink bg-vc-peach rounded hover:bg-vc-peach/90 transition-colors text-center">
                                                Desactivar
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button onclick="eliminarCuentaBancaria(<?= $cuenta['id_banco'] ?>)" 
                                                class="px-3 py-1 text-xs font-medium text-white bg-vc-pink rounded hover:bg-vc-pink/90 transition-colors text-center">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4">
                        <button onclick="agregarCuentaBancaria()" 
                                class="inline-flex items-center px-4 py-2 bg-vc-pink text-white text-sm font-medium rounded-lg hover:bg-vc-pink/90 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Agregar cuenta bancaria
                        </button>
                    </div>
                <?php else: ?>
                    <div class="rounded-lg border border-black/10 bg-white p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-muted-ink mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <p class="text-sm text-muted-ink mb-4">No hay cuentas bancarias registradas para este empleado.</p>
                        <button onclick="agregarCuentaBancaria()" 
                                class="inline-flex items-center px-4 py-2 bg-vc-pink text-white text-sm font-medium rounded-lg hover:bg-vc-pink/90 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Agregar primera cuenta
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Contactos -->
            <div class="tab-content hidden" id="tab-contactos">
                <?php if (!empty($contactos)): ?>
                    <div class="space-y-3">
                        <?php foreach ($contactos as $contacto): ?>
                            <div class="rounded-lg border border-black/10 bg-white p-4 hover:shadow-md transition-shadow <?= $contacto['activo'] ? '' : 'opacity-60' ?>">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="font-semibold text-vc-ink text-lg">
                                                <?= htmlspecialchars($contacto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </h4>
                                            <?php
                                            $tipoClasses = [
                                                'EMERGENCIA' => 'bg-red-100 text-red-800 border-red-200',
                                                'PERSONAL' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'OTRO' => 'bg-gray-100 text-gray-800 border-gray-200'
                                            ];
                                            $tipoClass = $tipoClasses[$contacto['tipo']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                            ?>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full border <?= $tipoClass ?>">
                                                <?= htmlspecialchars($contacto['tipo'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <?php if (!$contacto['activo']): ?>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full border bg-gray-100 text-gray-600 border-gray-200">
                                                    Inactivo
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="text-sm text-muted-ink space-y-1">
                                            <p>
                                                <span class="font-medium">Teléfono:</span>
                                                <a href="tel:<?= htmlspecialchars($contacto['telefono'], ENT_QUOTES, 'UTF-8') ?>" class="text-vc-teal hover:underline">
                                                    <?= htmlspecialchars($contacto['telefono'], ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            </p>
                                            <?php if ($contacto['correo']): ?>
                                                <p>
                                                    <span class="font-medium">Correo:</span>
                                                    <a href="mailto:<?= htmlspecialchars($contacto['correo'], ENT_QUOTES, 'UTF-8') ?>" class="text-vc-teal hover:underline">
                                                        <?= htmlspecialchars($contacto['correo'], ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($contacto['parentesco']): ?>
                                                <p>
                                                    <span class="font-medium">Parentesco:</span>
                                                    <?= htmlspecialchars($contacto['parentesco'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col gap-2 ml-4">
                                        <button onclick="editarContacto(<?= $contacto['id_contacto'] ?>)" 
                                                class="px-3 py-1 text-xs font-medium text-white bg-vc-teal rounded hover:bg-vc-teal/90 transition-colors text-center">
                                            Editar
                                        </button>
                                        
                                        <?php if ($contacto['activo']): ?>
                                            <button onclick="desactivarContacto(<?= $contacto['id_contacto'] ?>)" 
                                                    class="px-3 py-1 text-xs font-medium text-vc-ink bg-vc-peach rounded hover:bg-vc-peach/90 transition-colors text-center">
                                                Desactivar
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button onclick="eliminarContacto(<?= $contacto['id_contacto'] ?>)" 
                                                class="px-3 py-1 text-xs font-medium text-white bg-vc-pink rounded hover:bg-vc-pink/90 transition-colors text-center">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4">
                        <button onclick="agregarContacto()" 
                                class="inline-flex items-center px-4 py-2 bg-vc-pink text-white text-sm font-medium rounded-lg hover:bg-vc-pink/90 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Agregar contacto
                        </button>
                    </div>
                <?php else: ?>
                    <div class="rounded-lg border border-black/10 bg-white p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-muted-ink mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-sm text-muted-ink mb-4">No hay contactos registrados para este empleado.</p>
                        <button onclick="agregarContacto()" 
                                class="inline-flex items-center px-4 py-2 bg-vc-pink text-white text-sm font-medium rounded-lg hover:bg-vc-pink/90 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Agregar primer contacto
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Documentos -->
            <div class="tab-content hidden" id="tab-documentos">
                <?php if (!empty($documentos)): ?>
                    <div class="space-y-3">
                        <?php foreach ($documentos as $doc): ?>
                            <div class="rounded-lg border border-black/10 bg-white p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="font-semibold text-vc-ink">
                                                <?= htmlspecialchars($doc['tipo_documento'], ENT_QUOTES, 'UTF-8') ?>
                                            </h4>
                                            <?php
                                            $estadoClasses = [
                                                'PENDIENTE' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'VERIFICADO' => 'bg-green-100 text-green-800 border-green-200',
                                                'RECHAZADO' => 'bg-red-100 text-red-800 border-red-200'
                                            ];
                                            $clase = $estadoClasses[$doc['estado']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                            ?>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full border <?= $clase ?>">
                                                <?= htmlspecialchars($doc['estado'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>
                                        
                                        <div class="text-sm text-muted-ink space-y-1">
                                            <p>
                                                <span class="font-medium">Archivo:</span>
                                                <?= htmlspecialchars($doc['nombre_archivo'], ENT_QUOTES, 'UTF-8') ?>
                                            </p>
                                            <p>
                                                <span class="font-medium">Subido:</span>
                                                <?= date('d/m/Y H:i', strtotime($doc['fecha_subida'])) ?>
                                            </p>
                                            <?php if ($doc['estado'] === 'VERIFICADO' && $doc['fecha_verificacion']): ?>
                                                <p>
                                                    <span class="font-medium">Verificado:</span>
                                                    <?= date('d/m/Y H:i', strtotime($doc['fecha_verificacion'])) ?>
                                                    por <?= htmlspecialchars($doc['usuario_verificacion'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($doc['observaciones']): ?>
                                                <p>
                                                    <span class="font-medium">Observaciones:</span>
                                                    <?= htmlspecialchars($doc['observaciones'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col gap-2 ml-4">
                                        <a href="<?= url('index.php?controller=documento&action=descargar&id=' . $doc['id_documento']) ?>" 
                                           class="px-3 py-1 text-xs font-medium text-white bg-vc-teal rounded hover:bg-vc-teal/90 transition-colors text-center">
                                            Ver
                                        </a>
                                        
                                        <?php if ($doc['estado'] === 'PENDIENTE'): ?>
                                            <a href="<?= url('index.php?controller=documento&action=verificar&id=' . $doc['id_documento']) ?>" 
                                               class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 transition-colors text-center"
                                               onclick="return confirm('¿Verificar este documento?')">
                                                Verificar
                                            </a>
                                            <a href="<?= url('index.php?controller=documento&action=rechazar&id=' . $doc['id_documento']) ?>" 
                                               class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition-colors text-center"
                                               onclick="return confirm('¿Rechazar este documento?')">
                                                Rechazar
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?= url('index.php?controller=documento&action=eliminar&id=' . $doc['id_documento']) ?>" 
                                           class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition-colors text-center"
                                           onclick="return confirm('¿Eliminar este documento permanentemente?')">
                                            Eliminar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="rounded-lg border border-black/10 bg-white p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-muted-ink mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm text-muted-ink mb-4">No hay documentos registrados para este empleado.</p>
                        <a href="<?= url('index.php?controller=documento&action=subir&id=' . $empleado['id_empleado']) ?>" 
                           class="inline-flex items-center px-4 py-2 bg-vc-pink text-white text-sm font-medium rounded-lg hover:bg-vc-pink/90 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Subir primer documento
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Historial -->
            <div class="tab-content hidden" id="tab-historial">
                <?php if (!empty($movimientos)): ?>
                    <div class="rounded-lg border border-black/10 bg-white p-6">
                        <div class="relative">
                            <!-- Línea vertical del timeline -->
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gradient-to-b from-vc-pink via-vc-peach to-vc-teal"></div>
                            
                            <div class="space-y-6">
                                <?php foreach ($movimientos as $mov): ?>
                                    <div class="relative pl-12">
                                        <!-- Punto en el timeline -->
                                        <?php
                                        $colorPunto = [
                                            'Baja' => 'bg-red-500 ring-red-200',
                                            'Cambio de Área' => 'bg-blue-500 ring-blue-200',
                                            'Cambio de Puesto' => 'bg-purple-500 ring-purple-200',
                                            'Cambio de Jefe Inmediato' => 'bg-yellow-500 ring-yellow-200',
                                            'Reingreso' => 'bg-green-500 ring-green-200'
                                        ];
                                        $colorClase = $colorPunto[$mov['tipo_movimiento']] ?? 'bg-gray-500 ring-gray-200';
                                        ?>
                                        <div class="absolute left-2.5 -translate-x-1/2 w-3 h-3 rounded-full <?= $colorClase ?> ring-4"></div>
                                        
                                        <div class="bg-white rounded-lg border border-black/10 p-4 hover:shadow-md transition-shadow">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex items-center gap-3">
                                                    <?php
                                                    $tipoClasses = [
                                                        'Baja' => 'bg-red-100 text-red-800 border-red-200',
                                                        'Cambio de Área' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                        'Cambio de Puesto' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                        'Cambio de Jefe Inmediato' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                        'Reingreso' => 'bg-green-100 text-green-800 border-green-200'
                                                    ];
                                                    $tipoCls = $tipoClasses[$mov['tipo_movimiento']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                                    ?>
                                                    <span class="px-3 py-1 text-xs font-semibold rounded-full border <?= $tipoCls ?>">
                                                        <?= htmlspecialchars($mov['tipo_movimiento'], ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                    <span class="text-xs text-muted-ink font-medium">
                                                        <?= date('d/m/Y', strtotime($mov['fecha_movimiento'])) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <?php if ($mov['motivo']): ?>
                                                <p class="text-sm text-vc-ink mb-2">
                                                    <span class="font-semibold">Motivo:</span>
                                                    <?= htmlspecialchars($mov['motivo'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($mov['valor_anterior'] || $mov['valor_nuevo']): ?>
                                                <div class="flex items-center gap-2 text-sm mb-2">
                                                    <?php if ($mov['valor_anterior']): ?>
                                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded border border-gray-300">
                                                            <?= htmlspecialchars($mov['valor_anterior'], ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                        <svg class="w-4 h-4 text-muted-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                        </svg>
                                                    <?php endif; ?>
                                                    <?php if ($mov['valor_nuevo']): ?>
                                                        <span class="px-2 py-1 bg-vc-teal/10 text-vc-teal font-medium rounded border border-vc-teal/30">
                                                            <?= htmlspecialchars($mov['valor_nuevo'], ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($mov['observaciones']): ?>
                                                <p class="text-xs text-muted-ink italic mb-2">
                                                    "<?= htmlspecialchars($mov['observaciones'], ENT_QUOTES, 'UTF-8') ?>"
                                                </p>
                                            <?php endif; ?>
                                            
                                            <div class="flex items-center gap-4 text-xs text-muted-ink mt-3 pt-3 border-t border-black/5">
                                                <span>
                                                    <span class="font-medium">Autorizado por:</span>
                                                    <?= htmlspecialchars($mov['usuario_registro'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                                <span>
                                                    <span class="font-medium">Registrado:</span>
                                                    <?= date('d/m/Y H:i', strtotime($mov['fecha_registro'])) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="rounded-lg border border-black/10 bg-white p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-muted-ink mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <p class="text-sm text-muted-ink mb-4">No hay movimientos registrados para este empleado.</p>
                        <a href="<?= url('index.php?controller=movimiento&action=crear&id=' . $empleado['id_empleado']) ?>" 
                           class="inline-flex items-center px-4 py-2 bg-vc-pink text-white text-sm font-medium rounded-lg hover:bg-vc-pink/90 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Registrar movimiento
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        // Función de inicialización de pestañas
        function initTabs() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            console.log('Inicializando pestañas...');
            console.log('Botones encontrados:', tabButtons.length);
            console.log('Contenidos encontrados:', tabContents.length);

            if (tabButtons.length === 0 || tabContents.length === 0) {
                console.error('No se encontraron pestañas o contenidos');
                return;
            }

            // Configurar eventos de clic
            tabButtons.forEach((button, index) => {
                button.addEventListener('click', function() {
                    const tabName = this.dataset.tab;
                    console.log('Click en pestaña:', tabName);

                    // Remover clases activas de todos los botones
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'border-vc-pink', 'text-vc-pink');
                        btn.classList.add('border-transparent', 'text-muted-ink');
                    });

                    // Ocultar todos los contenidos
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                        content.classList.remove('active');
                    });

                    // Activar el botón clickeado
                    this.classList.add('active', 'border-vc-pink', 'text-vc-pink');
                    this.classList.remove('border-transparent', 'text-muted-ink');

                    // Mostrar el contenido correspondiente
                    const targetTab = document.getElementById(`tab-${tabName}`);
                    if (targetTab) {
                        targetTab.classList.remove('hidden');
                        targetTab.classList.add('active');
                        console.log('Mostrando pestaña:', tabName);
                    } else {
                        console.error('No se encontró el contenedor:', `tab-${tabName}`);
                    }
                });
            });

            // Mostrar la primera pestaña por defecto
            const firstTab = document.getElementById('tab-personal');
            if (firstTab) {
                firstTab.classList.remove('hidden');
                firstTab.classList.add('active');
                console.log('Primera pestaña mostrada');
            }
        }

        // Inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTabs);
        } else {
            // DOM ya está listo, ejecutar inmediatamente
            initTabs();
        }

        //Implementacion de notificaciones flotantes 

        // Sistema de notificaciones flotantes
        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed top-24 left-1/2 transform -translate-x-1/2 z-50 space-y-3';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'transform transition-all duration-300 ease-in-out scale-0 opacity-0 w-full max-w-md bg-white shadow-2xl rounded-xl pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden';

            const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
            const iconColor = type === 'success' ? 'text-green-400' : 'text-red-400';
            const icon = type === 'success'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />';

            const bgColor = type === 'success' ? 'bg-green-50' : 'bg-red-50';
            toast.innerHTML = `<div class="${bgColor} p-6"><div class="flex items-center justify-center"><div class="flex-shrink-0"><svg class="h-10 w-10 ${iconColor}" fill="none" viewBox="0 0 24 24" stroke="currentColor">${icon}</svg></div><div class="ml-4 flex-1"><p class="text-lg font-semibold ${textColor}">${message}</p></div><div class="ml-4 flex-shrink-0"><button onclick="this.closest('div').parentElement.parentElement.remove()" class="inline-flex ${textColor} hover:opacity-75 focus:outline-none"><svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></button></div></div></div>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('scale-0', 'opacity-0');
                toast.classList.add('scale-100', 'opacity-100');
            }, 100);
            setTimeout(() => {
                toast.classList.add('scale-0', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 5000);

        }

        <?php if (isset($_SESSION['toast_message'])): ?>
            showToast('<?= addslashes($_SESSION['toast_message']) ?>', '<?= $_SESSION['toast_type'] ?? 'success' ?>');
            <?php
            unset($_SESSION['toast_message']);
            unset($_SESSION['toast_type']);
            ?>
        <?php endif; ?>

        // Funciones para gestión de cuentas bancarias
        function agregarCuentaBancaria() {
            mostrarModalBanco();
        }

        function mostrarModalBanco(datos = null) {
            const titulo = datos ? 'Editar Cuenta Bancaria' : 'Agregar Cuenta Bancaria';
            const idBanco = datos ? datos.id_banco : '';
            
            const html = `
                <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" id="modal-banco">
                    <div class="bg-white rounded-xl max-w-md w-full p-6">
                        <h3 class="text-xl font-semibold text-vc-ink mb-4">${titulo}</h3>
                        <form id="form-banco" class="space-y-4">
                            <input type="hidden" name="id_empleado" value="<?= $empleado['id_empleado'] ?>">
                            ${idBanco ? `<input type="hidden" name="id_banco" value="${idBanco}">` : ''}
                            <div>
                                <label class="block text-sm font-medium text-vc-ink mb-1">Banco *</label>
                                <input type="text" name="banco" value="${datos?.banco || ''}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vc-pink focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-vc-ink mb-1">CLABE (18 dígitos) *</label>
                                <input type="text" name="clabe" value="${datos?.clabe || ''}" required pattern="\\d{18}" maxlength="18" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vc-pink focus:border-transparent font-mono">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-vc-ink mb-1">Titular *</label>
                                <input type="text" name="titular" value="${datos?.titular || ''}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vc-pink focus:border-transparent">
                            </div>
                            ${datos ? `
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="activa" value="1" ${datos.activa ? 'checked' : ''} class="mr-2 rounded border-gray-300 text-vc-pink focus:ring-vc-pink">
                                    <span class="text-sm font-medium text-vc-ink">Cuenta activa</span>
                                </label>
                            </div>` : ''}
                            <div class="flex gap-2 pt-4">
                                <button type="submit" class="flex-1 px-4 py-2 bg-vc-pink text-white rounded-lg hover:bg-vc-pink/90 transition-colors">
                                    ${datos ? 'Actualizar' : 'Guardar'}
                                </button>
                                <button type="button" onclick="cerrarModal('modal-banco')" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
            document.getElementById('form-banco').addEventListener('submit', guardarCuentaBancaria);
        }

        async function editarCuentaBancaria(id) {
            try {
                const response = await fetch(`<?= url('') ?>api/banco.php?action=obtener&id=${id}`);
                
                // Verificar si la respuesta es exitosa
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Obtener el texto de la respuesta primero para debug
                const text = await response.text();
                console.log('Respuesta del servidor:', text);
                
                // Intentar parsear como JSON
                const result = JSON.parse(text);
                
                if (result.success) {
                    mostrarModalBanco(result.data);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error completo:', error);
                showToast('Error al cargar la cuenta bancaria: ' + error.message, 'error');
            }
        }

        async function desactivarCuentaBancaria(id) {
            if (!confirm('¿Desactivar esta cuenta bancaria? No se eliminará, solo se marcará como inactiva.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('id_banco', id);

                const response = await fetch('<?= url('') ?>api/banco.php?action=desactivar', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al desactivar la cuenta bancaria: ' + error.message, 'error');
            }
        }

        async function eliminarCuentaBancaria(id) {
            if (!confirm('¿Eliminar permanentemente esta cuenta bancaria? Esta acción no se puede deshacer.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('id_banco', id);

                const response = await fetch('<?= url('') ?>api/banco.php?action=eliminar', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al eliminar la cuenta bancaria: ' + error.message, 'error');
            }
        }

        async function guardarCuentaBancaria(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const idBanco = formData.get('id_banco');
            const action = idBanco ? 'editar' : 'crear';

            // Si no está marcado el checkbox, no lo envíes o envía 0
            if (idBanco && !formData.get('activa')) {
                formData.set('activa', '0');
            }

            try {
                const response = await fetch(`<?= url('') ?>api/banco.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    cerrarModal('modal-banco');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al guardar la cuenta bancaria: ' + error.message, 'error');
            }
        }

        // Funciones para gestión de contactos
        function agregarContacto() {
            mostrarModalContacto();
        }

        function mostrarModalContacto(datos = null) {
            const titulo = datos ? 'Editar Contacto' : 'Agregar Contacto';
            const idContacto = datos ? datos.id_contacto : '';
            
            const html = `
                <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" id="modal-contacto">
                    <div class="bg-white rounded-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
                        <h3 class="text-xl font-semibold text-vc-ink mb-4">${titulo}</h3>
                        <form id="form-contacto" class="space-y-4">
                            <input type="hidden" name="id_empleado" value="<?= $empleado['id_empleado'] ?>">
                            ${idContacto ? `<input type="hidden" name="id_contacto" value="${idContacto}">` : ''}
                            <div>
                                <label class="block text-sm font-medium text-vc-ink mb-1">Tipo *</label>
                                <select name="tipo" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vc-pink focus:border-transparent">
                                    <option value="EMERGENCIA" ${datos?.tipo === 'EMERGENCIA' ? 'selected' : ''}>Emergencia</option>
                                    <option value="PERSONAL" ${datos?.tipo === 'PERSONAL' ? 'selected' : ''}>Personal</option>
                                    <option value="OTRO" ${datos?.tipo === 'OTRO' ? 'selected' : ''}>Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-vc-ink mb-1">Nombre *</label>
                                <input type="text" name="nombre" value="${datos?.nombre || ''}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vc-pink focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-vc-ink mb-1">Teléfono *</label>
                                <input type="tel" name="telefono" value="${datos?.telefono || ''}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vc-pink focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-vc-ink mb-1">Correo</label>
                                <input type="email" name="correo" value="${datos?.correo || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vc-pink focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-vc-ink mb-1">Parentesco</label>
                                <input type="text" name="parentesco" value="${datos?.parentesco || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-vc-pink focus:border-transparent">
                            </div>
                            ${datos ? `
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="activo" value="1" ${datos.activo ? 'checked' : ''} class="mr-2 rounded border-gray-300 text-vc-pink focus:ring-vc-pink">
                                    <span class="text-sm font-medium text-vc-ink">Contacto activo</span>
                                </label>
                            </div>` : ''}
                            <div class="flex gap-2 pt-4">
                                <button type="submit" class="flex-1 px-4 py-2 bg-vc-pink text-white rounded-lg hover:bg-vc-pink/90 transition-colors">
                                    ${datos ? 'Actualizar' : 'Guardar'}
                                </button>
                                <button type="button" onclick="cerrarModal('modal-contacto')" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
            document.getElementById('form-contacto').addEventListener('submit', guardarContacto);
        }

        async function editarContacto(id) {
            try {
                const response = await fetch(`<?= url('') ?>api/contacto.php?action=obtener&id=${id}`);
                
                // Verificar si la respuesta es exitosa
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Obtener el texto de la respuesta primero para debug
                const text = await response.text();
                console.log('Respuesta del servidor:', text);
                
                // Intentar parsear como JSON
                const result = JSON.parse(text);
                
                if (result.success) {
                    mostrarModalContacto(result.data);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error completo:', error);
                showToast('Error al cargar el contacto: ' + error.message, 'error');
            }
        }

        async function desactivarContacto(id) {
            if (!confirm('¿Desactivar este contacto? No se eliminará, solo se marcará como inactivo.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('id_contacto', id);

                const response = await fetch('<?= url('') ?>api/contacto.php?action=desactivar', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al desactivar el contacto: ' + error.message, 'error');
            }
        }

        async function eliminarContacto(id) {
            if (!confirm('¿Eliminar permanentemente este contacto? Esta acción no se puede deshacer.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('id_contacto', id);

                const response = await fetch('<?= url('') ?>api/contacto.php?action=eliminar', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al eliminar el contacto: ' + error.message, 'error');
            }
        }

        async function guardarContacto(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const idContacto = formData.get('id_contacto');
            const action = idContacto ? 'editar' : 'crear';

            // Si no está marcado el checkbox, no lo envíes o envía 0
            if (idContacto && !formData.get('activo')) {
                formData.set('activo', '0');
            }

            try {
                const response = await fetch(`<?= url('') ?>api/contacto.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    cerrarModal('modal-contacto');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al guardar el contacto: ' + error.message, 'error');
            }
        }

        function cerrarModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.remove();
            }
        }
    </script>
</body>

</html>