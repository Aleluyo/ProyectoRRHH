<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacantes - RRHH</title>
    <!-- Tailwind CSS (via CDN) -->
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
                    }
                }
            }
        }
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 text-vc-ink font-sans min-h-screen">

    <?php if (isset($_SESSION['swal'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: "<?= htmlspecialchars($_SESSION['swal']['title']) ?>",
                    text: "<?= htmlspecialchars($_SESSION['swal']['text']) ?>",
                    icon: "<?= htmlspecialchars($_SESSION['swal']['icon']) ?>",
                    confirmButtonColor: '#36d1cc'
                });
            });
        </script>
        <?php unset($_SESSION['swal']); ?>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto p-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-4xl font-bold text-vc-ink tracking-tight">Gestión de Vacantes</h1>
                <p class="text-gray-500 mt-1">Administra las posiciones abiertas y el seguimiento de candidatos.</p>
            </div>
            <div class="flex gap-3">
                 <a href="<?= url('index.php') ?>" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold hover:bg-gray-50 transition shadow-sm">
                    &larr; Volver
                </a>
                <a href="<?= url('index.php?controller=vacante&action=create') ?>" class="inline-flex items-center gap-2 bg-vc-pink text-white px-5 py-2.5 rounded-xl font-bold hover:bg-pink-500 transition shadow-lg shadow-vc-pink/30 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nueva Vacante
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex items-center gap-4">
            <div class="flex-1 relative">
                <form action="index.php" method="GET" class="flex w-full">
                    <input type="hidden" name="controller" value="vacante">
                    <input type="hidden" name="action" value="index">
                    
                    <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-vc-pink focus:ring-2 focus:ring-vc-pink/20 transition"
                           placeholder="Buscar por puesto, área o ubicación...">
                </form>
            </div>
        </div>

        <!-- Vacancies List -->
        <div class="grid grid-cols-1 gap-4">
            <?php if (empty($vacantes)): ?>
                <div class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-200">
                    <p class="text-gray-400 text-lg">No se encontraron vacantes activas.</p>
                    <a href="<?= url('index.php?controller=vacante&action=create') ?>" class="text-vc-pink font-bold hover:underline mt-2 inline-block">Crear la primera</a>
                </div>
            <?php else: ?>
                <?php foreach ($vacantes as $v): ?>
                    <?php 
                        // Status Badge Color
                        $statusColor = match($v['estatus']) {
                            'ABIERTA' => 'bg-green-100 text-green-700 border-green-200',
                            'CERRADA' => 'bg-red-50 text-red-700 border-red-100',
                            'EN_APROBACION' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                            default => 'bg-gray-100 text-gray-600 border-gray-200'
                        };
                    ?>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition group flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        
                        <!-- Left Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-xl font-bold text-vc-ink group-hover:text-vc-pink transition">
                                    <?= htmlspecialchars($v['puesto_nombre'] ?? 'Sin puesto') ?>
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $statusColor ?>">
                                    <?= htmlspecialchars($v['estatus']) ?>
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap text-sm text-gray-500 gap-x-6 gap-y-1 mt-2">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    <?= htmlspecialchars($v['area_nombre'] ?? '-') ?>
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <?= htmlspecialchars($v['ubicacion_nombre'] ?? '-') ?>
                                </span>
                                <span class="flex items-center gap-1" title="Fecha Publicación">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <?= date('d M Y', strtotime($v['fecha_publicacion'] ?? $v['creada_en'])) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3">
                            <a href="<?= url('index.php?controller=tablero&action=index&id_vacante=' . $v['id_vacante']) ?>" 
                               class="inline-flex items-center gap-2 bg-vc-teal/10 text-vc-teal px-4 py-2 rounded-lg font-bold hover:bg-vc-teal hover:text-white transition group-hover:bg-vc-teal group-hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                Ver Tablero
                            </a>
                            
                            <!-- Dropdown-like Actions -->
                            <div class="flex gap-2">
                                <a href="<?= url('index.php?controller=vacante&action=edit&id=' . $v['id_vacante']) ?>" 
                                   class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <a href="<?= url('index.php?controller=vacante&action=delete&id=' . $v['id_vacante']) ?>" 
                                   onclick="return confirm('¿Seguro que deseas eliminar esta vacante?');"
                                   class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
