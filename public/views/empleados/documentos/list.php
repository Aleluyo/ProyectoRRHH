<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Documentos · RRHH</title>
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
                    boxShadow: { soft: '0 10px 28px rgba(10,42,94,.08)' }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
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
            <div class="ml-auto flex items-center gap-4">
                <a href="<?= url('index.php') ?>" class="text-sm font-medium text-vc-ink/70 hover:text-vc-ink transition">Inicio</a>
                <a href="<?= url('logout.php') ?>" class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">
        
        <div class="mb-5">
            <div class="text-sm text-vc-ink/50 flex items-center gap-2">
                <a href="<?= url('index.php') ?>" class="hover:text-vc-pink transition">Inicio</a>
                <span>/</span>
                <a href="<?= url('index.php?controller=empleado&action=index') ?>" class="hover:text-vc-pink transition">Empleados</a>
                <span>/</span>
                <span class="text-vc-ink">Documentos</span>
            </div>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mb-6 rounded-xl border p-4 <?= $_SESSION['tipo_mensaje'] === 'exito' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' ?>">
                <?= htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <div class="flex items-center justify-between mb-6">
            <h1 class="vice-title text-4xl text-vc-ink">Gestión de Documentos</h1>
        </div>

        <!-- Selector de Empleado -->
        <div class="mb-6 rounded-xl border border-black/10 bg-white/90 p-6 shadow-soft">
            <form method="GET" action="<?= url('index.php') ?>" class="flex items-end gap-4">
                <input type="hidden" name="controller" value="documento">
                <input type="hidden" name="action" value="listado">
                
                <div class="flex-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-2">Seleccionar Empleado</label>
                    <select name="id_empleado" class="w-full h-12 rounded-lg border border-black/10 bg-gray-50 px-4 text-sm focus:outline-none focus:border-vc-sand" required>
                        <option value="">-- Seleccione un empleado --</option>
                        <?php foreach ($empleados as $emp): ?>
                            <option value="<?= $emp['id_empleado'] ?>" <?= isset($idEmpleado) && $idEmpleado == $emp['id_empleado'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['nombre'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($emp['curp'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="h-12 px-6 bg-vc-sand text-vc-ink font-bold rounded-lg hover:bg-vc-sand/80 transition">
                    Ver Documentos
                </button>
            </form>
        </div>

        <?php if ($empleado): ?>
            <!-- Info del Empleado y Estadísticas -->
            <div class="mb-6 rounded-xl border border-black/10 bg-white/90 p-6 shadow-soft">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-vc-ink"><?= htmlspecialchars($empleado['nombre'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="text-sm text-vc-ink/60">CURP: <?= htmlspecialchars($empleado['curp'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <a href="<?= url('index.php?controller=documento&action=subir&id_empleado=' . $idEmpleado) ?>" 
                       class="px-4 py-2 bg-vc-teal text-white font-bold rounded-lg hover:bg-vc-teal/80 transition">
                        + Subir Documento
                    </a>
                </div>

                <?php if ($estadisticas): ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="text-2xl font-bold text-yellow-800"><?= $estadisticas['PENDIENTE'] ?></div>
                            <div class="text-sm text-yellow-700">Pendientes</div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="text-2xl font-bold text-green-800"><?= $estadisticas['VERIFICADO'] ?></div>
                            <div class="text-sm text-green-700">Verificados</div>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="text-2xl font-bold text-red-800"><?= $estadisticas['RECHAZADO'] ?></div>
                            <div class="text-sm text-red-700">Rechazados</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tabla de Documentos -->
            <?php if (empty($documentos)): ?>
                <div class="rounded-xl border border-black/10 bg-white/90 p-12 text-center shadow-soft">
                    <p class="text-vc-ink/50">No hay documentos registrados para este empleado</p>
                </div>
            <?php else: ?>
                <div class="rounded-xl border border-black/10 bg-white/90 overflow-hidden shadow-soft">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-black/5">
                            <tr>
                                <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-vc-ink/50">Tipo</th>
                                <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-vc-ink/50">Archivo</th>
                                <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-vc-ink/50">Vigencia</th>
                                <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-vc-ink/50">Estado</th>
                                <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-vc-ink/50">Subido</th>
                                <th class="text-center py-3 px-4 text-xs font-bold uppercase tracking-wider text-vc-ink/50">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5">
                            <?php foreach ($documentos as $doc): 
                                $estadoClass = match($doc['estado']) {
                                    'PENDIENTE' => 'bg-yellow-100 text-yellow-800',
                                    'VERIFICADO' => 'bg-green-100 text-green-800',
                                    'RECHAZADO' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 text-sm font-medium text-vc-ink">
                                        <?= htmlspecialchars(EmpleadoDocumento::tiposDocumento()[$doc['tipo_documento']] ?? $doc['tipo_documento'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm">
                                        <div class="font-medium text-vc-ink"><?= htmlspecialchars($doc['nombre_archivo'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-xs text-vc-ink/50">
                                            <?= strtoupper($doc['extension']) ?> · <?= number_format($doc['tamano_kb'], 0) ?> KB
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-vc-ink/70">
                                        <?= $doc['fecha_vigencia'] ? date('d/m/Y', strtotime($doc['fecha_vigencia'])) : 'N/A' ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-block px-2 py-1 text-xs font-bold rounded-full <?= $estadoClass ?>">
                                            <?= $doc['estado'] ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-vc-ink/70">
                                        <div><?= htmlspecialchars($doc['usuario_subida'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-xs"><?= date('d/m/Y H:i', strtotime($doc['fecha_subida'])) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Ver/Descargar -->
                                            <a href="<?= url('index.php?controller=documento&action=descargar&id=' . $doc['id_documento']) ?>" 
                                               target="_blank"
                                               class="text-vc-teal hover:text-vc-teal/70 transition" 
                                               title="Ver documento">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>

                                            <?php if ($doc['estado'] === 'PENDIENTE'): ?>
                                                <!-- Verificar -->
                                                <form method="POST" action="<?= url('index.php?controller=documento&action=verificar') ?>" class="inline">
                                                    <input type="hidden" name="id_documento" value="<?= $doc['id_documento'] ?>">
                                                    <button type="submit" class="text-green-600 hover:text-green-700 transition" title="Verificar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                </form>

                                                <!-- Rechazar -->
                                                <button onclick="rechazarDocumento(<?= $doc['id_documento'] ?>)" 
                                                        class="text-red-600 hover:text-red-700 transition" 
                                                        title="Rechazar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>

                                            <!-- Eliminar -->
                                            <button onclick="eliminarDocumento(<?= $doc['id_documento'] ?>)" 
                                                    class="text-gray-600 hover:text-gray-700 transition" 
                                                    title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </main>

    <script>
        function rechazarDocumento(idDocumento) {
            const motivo = prompt('Ingrese el motivo del rechazo:');
            if (motivo && motivo.trim() !== '') {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= url('index.php?controller=documento&action=rechazar') ?>';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id_documento';
                inputId.value = idDocumento;
                
                const inputObs = document.createElement('input');
                inputObs.type = 'hidden';
                inputObs.name = 'observaciones';
                inputObs.value = motivo;
                
                form.appendChild(inputId);
                form.appendChild(inputObs);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function eliminarDocumento(idDocumento) {
            if (confirm('¿Está seguro de eliminar este documento? Esta acción no se puede deshacer.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= url('index.php?controller=documento&action=eliminar') ?>';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id_documento';
                input.value = idDocumento;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
