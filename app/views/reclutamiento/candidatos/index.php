<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatos - RRHH</title>
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
</head>
<body class="bg-gray-50 text-vc-ink font-sans min-h-screen">

    <?php if (isset($_SESSION['swal'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <h1 class="text-4xl font-bold text-vc-ink tracking-tight">Banco de Candidatos</h1>
                <p class="text-gray-500 mt-1">Directorio de talento y prospectos.</p>
            </div>
            <div class="flex gap-3">
                 <a href="<?= url('index.php') ?>" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold hover:bg-gray-50 transition shadow-sm">
                    &larr; Volver
                </a>
                <a href="<?= url('index.php?controller=candidato&action=create') ?>" class="inline-flex items-center gap-2 bg-vc-teal text-white px-5 py-2.5 rounded-xl font-bold hover:bg-teal-500 transition shadow-lg shadow-vc-teal/30 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo Candidato
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex items-center gap-4">
            <div class="flex-1 relative">
                <form action="index.php" method="GET" class="flex w-full">
                    <input type="hidden" name="controller" value="candidato">
                    <input type="hidden" name="action" value="index">
                    
                    <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-vc-teal focus:ring-2 focus:ring-vc-teal/20 transition"
                           placeholder="Buscar por nombre, correo o teléfono...">
                </form>
            </div>
        </div>

        <!-- Candidates List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 tracking-wider">
                        <th class="p-4 font-semibold">Candidato</th>
                        <th class="p-4 font-semibold">Contacto</th>
                        <th class="p-4 font-semibold">Fuente</th>
                        <th class="p-4 font-semibold">CV</th>
                        <th class="p-4 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($candidatos)): ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400">
                                No se encontraron candidatos.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($candidatos as $c): ?>
                            <tr class="hover:bg-gray-50/50 transition group">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-vc-teal to-blue-400 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                            <?= strtoupper(substr($c['nombre'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-vc-ink"><?= htmlspecialchars($c['nombre']) ?></div>
                                            <div class="text-xs text-gray-400">ID: <?= $c['id_candidato'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="text-sm text-gray-600">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            <?= htmlspecialchars($c['correo']) ?>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            <?= htmlspecialchars($c['telefono']) ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-600 rounded">
                                        <?= htmlspecialchars($c['fuente'] ?? 'Directo') ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <?php if (!empty($c['cv'])): ?>
                                        <a href="<?= url('index.php?controller=candidato&action=verCV&id=' . $c['id_candidato']) ?>" 
                                           class="text-vc-teal hover:underline text-sm font-medium flex items-center gap-1"
                                           target="_blank">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            Ver CV
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-300 text-sm italic">Sin CV</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="<?= url('index.php?controller=candidato&action=edit&id=' . $c['id_candidato']) ?>" 
                                           class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <!-- Since we implemented logical delete, this uses the delete method which sets activo=0 -->
                                        <!-- No simple GET link for delete in controller, usually POST or confirmed GET -->
                                        <!-- Assuming controller::delete checks for ID in GET (legacy style) -->
                                        <a href="<?= url('index.php?controller=candidato&action=delete&id=' . $c['id_candidato']) ?>" 
                                           onclick="return confirm('¿Seguro que deseas eliminar este candidato?');"
                                           class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</body>
</html>
