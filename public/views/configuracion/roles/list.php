<?php
// views/configuracion/roles/list.php
// Variables: $roles (array)
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-lg font-bold text-vc-ink">Roles y Permisos</h2>
        <p class="text-sm text-gray-500">Define qué pueden hacer los usuarios</p>
    </div>
    <!-- Botón placeholder para futuro -->
    <button class="bg-gray-100 text-gray-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed flex items-center gap-2" title="Próximamente">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nuevo Rol
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($roles as $rol): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-vc-ink text-lg"><?= htmlspecialchars($rol['nombre_rol']) ?></h3>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($rol['descripcion']) ?></p>
                </div>
                <span class="bg-vc-teal/10 text-vc-teal px-2 py-1 rounded text-xs font-bold uppercase tracking-wider">
                    ID: <?= $rol['id_rol'] ?>
                </span>
            </div>
            
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Permisos Asignados</h4>
                <?php if (!empty($rol['permisos'])): ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($rol['permisos'] as $permiso): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100" title="<?= htmlspecialchars($permiso['descripcion']) ?>">
                                <?= htmlspecialchars($permiso['clave']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-400 italic">Sin permisos asignados.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
