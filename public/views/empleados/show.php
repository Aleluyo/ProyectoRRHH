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
                <button
                    class="inline-flex items-center px-4 py-2 rounded-lg border border-black/10 bg-white text-vc-ink text-sm font-medium hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Subir documento
                </button>
                <button
                    class="inline-flex items-center px-4 py-2 rounded-lg border border-black/10 bg-white text-vc-ink text-sm font-medium hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Registrar movimiento
                </button>
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
            <div class="tab-content active" id="tab-personal">
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
                <div class="rounded-lg border border-black/10 bg-white p-4">
                    <p class="text-sm text-muted-ink">Información bancaria disponible próximamente.</p>
                </div>
            </div>

            <!-- Tab: Contactos -->
            <div class="tab-content hidden" id="tab-contactos">
                <div class="rounded-lg border border-black/10 bg-white p-4">
                    <p class="text-sm text-muted-ink">Contactos de emergencia disponibles próximamente.</p>
                </div>
            </div>

            <!-- Tab: Documentos -->
            <div class="tab-content hidden" id="tab-documentos">
                <div class="rounded-lg border border-black/10 bg-white p-4">
                    <p class="text-sm text-muted-ink">Documentos del expediente disponibles próximamente.</p>
                </div>
            </div>

            <!-- Tab: Historial -->
            <div class="tab-content hidden" id="tab-historial">
                <div class="rounded-lg border border-black/10 bg-white p-4">
                    <p class="text-sm text-muted-ink">Historial de movimientos disponible próximamente.</p>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Tab switching
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabName = button.dataset.tab;

                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'border-vc-pink', 'text-vc-pink');
                    btn.classList.add('border-transparent', 'text-muted-ink');
                });
                tabContents.forEach(content => content.classList.add('hidden'));

                // Add active class to clicked button and corresponding content
                button.classList.add('active', 'border-vc-pink', 'text-vc-pink');
                button.classList.remove('border-transparent', 'text-muted-ink');
                document.getElementById(`tab-${tabName}`).classList.remove('hidden');
            });
        });

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
    </script>
</body>

</html>