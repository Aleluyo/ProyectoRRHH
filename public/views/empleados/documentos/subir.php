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
    <title>Subir Documento · RRHH</title>
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

    <main class="mx-auto max-w-4xl px-4 sm:px-6 py-8 relative">
        
        <div class="mb-5">
            <div class="text-sm text-vc-ink/50 flex items-center gap-2">
                <a href="<?= url('index.php') ?>" class="hover:text-vc-pink transition">Inicio</a>
                <span>/</span>
                <a href="<?= url('index.php?controller=empleado&action=index') ?>" class="hover:text-vc-pink transition">Empleados</a>
                <span>/</span>
                <a href="<?= url('index.php?controller=documento&action=listado') ?>" class="hover:text-vc-pink transition">Documentos</a>
                <span>/</span>
                <span class="text-vc-ink">Subir</span>
            </div>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mb-6 rounded-xl border p-4 <?= $_SESSION['tipo_mensaje'] === 'exito' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' ?>">
                <?= htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <h1 class="vice-title text-4xl text-vc-ink mb-2">Subir Documento</h1>
        <p class="text-vc-ink/60 mb-6">Empleado: <strong><?= htmlspecialchars($empleado['nombre'], ENT_QUOTES, 'UTF-8') ?></strong></p>

        <!-- Formulario de Subida -->
        <div class="rounded-xl border border-black/10 bg-white/90 p-8 shadow-soft">
            <form method="POST" action="<?= url('index.php?controller=documento&action=guardar') ?>" enctype="multipart/form-data" id="formSubirDoc">
                <input type="hidden" name="id_empleado" value="<?= $empleado['id_empleado'] ?>">

                <div class="space-y-6">
                    
                    <!-- Tipo de Documento -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-2">
                            Tipo de Documento <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_documento" required class="w-full h-12 rounded-lg border border-black/10 bg-gray-50 px-4 text-sm focus:outline-none focus:border-vc-sand">
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($tiposDocumento as $key => $label): ?>
                                <option value="<?= $key ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Fecha de Vigencia -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-2">
                            Fecha de Vigencia <span class="text-vc-ink/30">(Opcional)</span>
                        </label>
                        <input type="date" name="fecha_vigencia" class="w-full h-12 rounded-lg border border-black/10 bg-gray-50 px-4 text-sm focus:outline-none focus:border-vc-sand">
                        <p class="text-xs text-vc-ink/50 mt-1">Aplicable para documentos con vencimiento (INE, certificados, etc.)</p>
                    </div>

                    <!-- Archivo -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-2">
                            Archivo <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="file" 
                                name="archivo" 
                                id="archivo" 
                                accept=".<?= implode(',. ', $extensionesPermitidas) ?>"
                                required 
                                class="w-full h-12 rounded-lg border border-black/10 bg-gray-50 px-4 text-sm focus:outline-none focus:border-vc-sand file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-vc-sand file:text-vc-ink hover:file:bg-vc-sand/80">
                        </div>
                        <p class="text-xs text-vc-ink/50 mt-1">
                            Extensiones permitidas: <strong><?= strtoupper(implode(', ', $extensionesPermitidas)) ?></strong>
                            <br>
                            Tamaño máximo: <strong><?= $tamanoMaximoMB ?> MB</strong>
                        </p>
                        <div id="archivoInfo" class="mt-2 text-sm text-vc-ink/70"></div>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-vc-ink/50 mb-2">
                            Observaciones <span class="text-vc-ink/30">(Opcional)</span>
                        </label>
                        <textarea name="observaciones" rows="3" class="w-full rounded-lg border border-black/10 bg-gray-50 px-4 py-3 text-sm focus:outline-none focus:border-vc-sand" placeholder="Notas adicionales sobre el documento..."></textarea>
                    </div>

                    <!-- Información -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-bold mb-1">Importante:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>El documento quedará en estado <strong>PENDIENTE</strong> hasta su verificación</li>
                                    <li>Asegúrese de que el archivo sea legible y de buena calidad</li>
                                    <li>Los documentos rechazados deberán ser cargados nuevamente</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Botones -->
                <div class="mt-8 flex gap-3">
                    <button type="submit" class="flex-1 h-12 bg-vc-sand text-vc-ink font-bold rounded-lg hover:bg-vc-sand/80 transition">
                        Subir Documento
                    </button>
                    <a href="<?= url('index.php?controller=documento&action=listado&id_empleado=' . $empleado['id_empleado']) ?>" 
                       class="h-12 px-6 flex items-center justify-center border border-black/10 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

    </main>

    <script>
        // Mostrar información del archivo seleccionado
        document.getElementById('archivo').addEventListener('change', function(e) {
            const archivo = e.target.files[0];
            const infoDiv = document.getElementById('archivoInfo');
            
            if (archivo) {
                const tamanoMB = (archivo.size / 1024 / 1024).toFixed(2);
                const extension = archivo.name.split('.').pop().toLowerCase();
                
                // Validar extensión
                const extensionesPermitidas = <?= json_encode($extensionesPermitidas) ?>;
                if (!extensionesPermitidas.includes(extension)) {
                    infoDiv.innerHTML = '<span class="text-red-600">⚠️ Extensión no permitida</span>';
                    e.target.value = '';
                    return;
                }
                
                // Validar tamaño
                const maxMB = <?= $tamanoMaximoMB ?>;
                if (parseFloat(tamanoMB) > maxMB) {
                    infoDiv.innerHTML = '<span class="text-red-600">⚠️ El archivo excede el tamaño máximo de ' + maxMB + ' MB</span>';
                    e.target.value = '';
                    return;
                }
                
                infoDiv.innerHTML = '<span class="text-green-600">✓ ' + archivo.name + ' (' + tamanoMB + ' MB)</span>';
            } else {
                infoDiv.innerHTML = '';
            }
        });

        // Validación antes de enviar
        document.getElementById('formSubirDoc').addEventListener('submit', function(e) {
            const archivo = document.getElementById('archivo').files[0];
            if (!archivo) {
                e.preventDefault();
                alert('Debe seleccionar un archivo');
                return;
            }
        });
    </script>
</body>
</html>
