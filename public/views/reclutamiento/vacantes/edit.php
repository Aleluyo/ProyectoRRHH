<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

$areaSesion = htmlspecialchars($_SESSION['area'] ?? '', ENT_QUOTES, 'UTF-8');
$puestoSesion = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudadSesion = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

$errors = $errors ?? [];
$old = $old ?? [];
$vacante = $vacante ?? []; // Datos de la vacante a editar

// ID de la vacante (para el action del form)
$idVacante = (int) ($vacante['id_vacante'] ?? 0);

// Pre-llenado: Preferencia a OLD (si hubo error en POST), sino a la BD ($vacante)
$valEmpresa = (string) ($old['id_empresa'] ?? $vacante['id_empresa'] ?? '');
$valArea = (string) ($old['id_area'] ?? $vacante['id_area'] ?? '');
$valPuesto = (string) ($old['id_puesto'] ?? $vacante['id_puesto'] ?? '');
$valUbicacion = (string) ($old['id_ubicacion'] ?? $vacante['id_ubicacion'] ?? '');
$valSolicita = (string) ($old['solicitada_por'] ?? $vacante['solicitada_por'] ?? '');
$valEstatus = (string) ($old['estatus'] ?? $vacante['estatus'] ?? 'EN_APROBACION');
$valFecha = (string) ($old['fecha_publicacion'] ?? $vacante['fecha_publicacion'] ?? '');
$valReq = (string) ($old['requisitos'] ?? $vacante['requisitos'] ?? '');

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Editar vacante · Reclutamiento y Selección</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Tailwind -->
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

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap"
        rel="stylesheet">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
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
                    <?= $puestoSesion ?><?= $areaSesion ? ' &mdash; ' . $areaSesion : '' ?><?= $ciudadSesion ? ' &mdash; ' . $ciudadSesion : '' ?>
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
                <a href="<?= url('views/reclutamiento/index.php') ?>"
                    class="text-muted-ink hover:text-vc-ink transition">
                    Reclutamiento y Selección
                </a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="<?= url('index.php?controller=vacante&action=index') ?>"
                    class="text-muted-ink hover:text-vc-ink transition">
                    Vacantes
                </a>
                <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-medium text-vc-pink">Editar vacante</span>
            </nav>
        </div>

        <!-- Título -->
        <section class="mb-6">
            <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Editar vacante #<?= $idVacante ?></h1>
            <p class="mt-1 text-sm sm:text-base text-muted-ink">
                Modifica los datos de la requisición.
            </p>
        </section>

        <!-- Formulario -->
        <section>
            <form method="POST" action="<?= url('index.php?controller=vacante&action=update&id=' . $idVacante) ?>"
                class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Empresa -->
                    <div>
                        <label for="id_empresa" class="block text-sm font-medium text-vc-ink">Empresa</label>
                        <select id="id_empresa" name="id_empresa"
                            class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Selecciona una empresa…</option>
                            <?php foreach (($empresas ?? []) as $emp): ?>
                                <?php
                                $id = (string) $emp['id_empresa'];
                                $txt = htmlspecialchars($emp['nombre'] ?? ('Empresa #' . $id), ENT_QUOTES, 'UTF-8');
                                ?>
                                <option value="<?= $id ?>" <?= $valEmpresa === $id ? 'selected' : '' ?>>
                                    <?= $txt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['id_empresa'])): ?>
                            <p class="mt-1 text-xs text-red-600">
                                <?= htmlspecialchars($errors['id_empresa'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Área -->
                    <div>
                        <label for="id_area" class="block text-sm font-medium text-vc-ink">Área</label>
                        <select id="id_area" name="id_area"
                            class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Selecciona un área…</option>
                            <!-- Opciones se llenan con JS -->
                        </select>
                        <?php if (!empty($errors['id_area'])): ?>
                            <p class="mt-1 text-xs text-red-600">
                                <?= htmlspecialchars($errors['id_area'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Puesto -->
                    <div>
                        <label for="id_puesto" class="block text-sm font-medium text-vc-ink">Puesto</label>
                        <select id="id_puesto" name="id_puesto"
                            class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Selecciona un puesto…</option>
                            <!-- Opciones se llenan con JS -->
                        </select>
                        <?php if (!empty($errors['id_puesto'])): ?>
                            <p class="mt-1 text-xs text-red-600">
                                <?= htmlspecialchars($errors['id_puesto'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Segunda fila: ubicación + solicitante + estatus -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Ubicación -->
                    <div>
                        <label for="id_ubicacion" class="block text-sm font-medium text-vc-ink">Ubicación</label>
                        <select id="id_ubicacion" name="id_ubicacion"
                            class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <option value="">Selecciona una ubicación…</option>
                            <!-- Opciones se llenan con JS -->
                        </select>
                        <?php if (!empty($errors['id_ubicacion'])): ?>
                            <p class="mt-1 text-xs text-red-600">
                                <?= htmlspecialchars($errors['id_ubicacion'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Solicitada por -->
                    <div>
                        <label for="solicitada_por" class="block text-sm font-medium text-vc-ink">
                            Solicitada por (ID usuario)
                        </label>
                        <input type="number" id="solicitada_por" name="solicitada_por" min="1"
                            class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                            value="<?= htmlspecialchars($valSolicita, ENT_QUOTES, 'UTF-8') ?>" />
                        <?php if (!empty($errors['solicitada_por'])): ?>
                            <p class="mt-1 text-xs text-red-600">
                                <?= htmlspecialchars($errors['solicitada_por'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Estatus -->
                    <div>
                        <label for="estatus" class="block text-sm font-medium text-vc-ink">Estatus</label>
                        <select id="estatus" name="estatus"
                            class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
                            <?php
                            $opcionesEstatus = [
                                'EN_APROBACION' => 'En aprobación',
                                'APROBADA' => 'Aprobada',
                                'ABIERTA' => 'Abierta',
                                'EN_PROCESO' => 'En proceso',
                                'CERRADA' => 'Cerrada',
                            ];
                            foreach ($opcionesEstatus as $val => $label):
                                ?>
                                <option value="<?= $val ?>" <?= strtoupper($valEstatus) === $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['estatus'])): ?>
                            <p class="mt-1 text-xs text-red-600">
                                <?= htmlspecialchars($errors['estatus'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fecha + requisitos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="fecha_publicacion" class="block text-sm font-medium text-vc-ink">
                            Fecha de publicación (opcional)
                        </label>
                        <input type="date" id="fecha_publicacion" name="fecha_publicacion"
                            class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                            value="<?= htmlspecialchars($valFecha, ENT_QUOTES, 'UTF-8') ?>" />
                        <?php if (!empty($errors['fecha_publicacion'])): ?>
                            <p class="mt-1 text-xs text-red-600">
                                <?= htmlspecialchars($errors['fecha_publicacion'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="requisitos" class="block text-sm font-medium text-vc-ink">
                        Requisitos / Comentarios
                    </label>
                    <textarea id="requisitos" name="requisitos" rows="5"
                        class="mt-1 block w-full rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"><?= htmlspecialchars($valReq, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (!empty($errors['requisitos'])): ?>
                        <p class="mt-1 text-xs text-red-600">
                            <?= htmlspecialchars($errors['requisitos'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="<?= url('index.php?controller=vacante&action=index') ?>"
                        class="inline-flex items-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm hover:bg-slate-50">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-vc-teal px-4 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition">
                        Actualizar vacante
                    </button>
                </div>
            </form>
        </section>
    </main>

    <!-- JS: selects dependientes (opción A) -->
    <script>
        // Datos en bruto desde PHP
        const EMPRESAS = <?= json_encode($empresas ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const AREAS = <?= json_encode($areas ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const PUESTOS = <?= json_encode($puestos ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const UBICACIONES = <?= json_encode($ubicaciones ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        const valEmpresa = "<?= $valEmpresa ?>";
        const valArea = "<?= $valArea ?>";
        const valPuesto = "<?= $valPuesto ?>";
        const valUbicacion = "<?= $valUbicacion ?>";

        const selEmpresa = document.getElementById('id_empresa');
        const selArea = document.getElementById('id_area');
        const selPuesto = document.getElementById('id_puesto');
        const selUbicacion = document.getElementById('id_ubicacion');

        function llenarAreas(idEmpresa, mantenerSeleccion) {
            selArea.innerHTML = '<option value="">Selecciona un área…</option>';

            AREAS.filter(a => String(a.id_empresa) === String(idEmpresa))
                .forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a.id_area;
                    opt.textContent = a.nombre_area || ('Área #' + a.id_area);
                    selArea.appendChild(opt);
                });

            if (mantenerSeleccion && valArea) {
                selArea.value = valArea;
            }
            if (!selArea.value) {
                selArea.selectedIndex = 0;
            }
        }

        function llenarPuestos(idArea, mantenerSeleccion) {
            selPuesto.innerHTML = '<option value="">Selecciona un puesto…</option>';

            PUESTOS.filter(p => String(p.id_area) === String(idArea))
                .forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id_puesto;
                    opt.textContent = p.nombre_puesto || ('Puesto #' + p.id_puesto);
                    selPuesto.appendChild(opt);
                });

            if (mantenerSeleccion && valPuesto) {
                selPuesto.value = valPuesto;
            }
            if (!selPuesto.value) {
                selPuesto.selectedIndex = 0;
            }
        }

        function llenarUbicaciones(idEmpresa, mantenerSeleccion) {
            selUbicacion.innerHTML = '<option value="">Selecciona una ubicación…</option>';

            UBICACIONES.filter(u => String(u.id_empresa) === String(idEmpresa))
                .forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id_ubicacion;
                    opt.textContent = u.nombre || ('Ubicación #' + u.id_ubicacion);
                    selUbicacion.appendChild(opt);
                });

            if (mantenerSeleccion && valUbicacion) {
                selUbicacion.value = valUbicacion;
            }
            if (!selUbicacion.value) {
                selUbicacion.selectedIndex = 0;
            }
        }

        // Eventos
        selEmpresa.addEventListener('change', () => {
            const idEmp = selEmpresa.value || '';
            llenarAreas(idEmp, false);
            llenarPuestos('', false);
            llenarUbicaciones(idEmp, false);
        });

        selArea.addEventListener('change', () => {
            const idArea = selArea.value || '';
            llenarPuestos(idArea, false);
        });

        // Inicialización con valores
        window.addEventListener('DOMContentLoaded', () => {
            if (valEmpresa) {
                // Asegurar que el select de empresa tenga el valor
                selEmpresa.value = valEmpresa;
                // Llenar dependientes
                llenarAreas(valEmpresa, true);

                if (valArea) {
                    llenarPuestos(valArea, true);
                }

                llenarUbicaciones(valEmpresa, true);
            }
        });
    </script>
</body>

</html>