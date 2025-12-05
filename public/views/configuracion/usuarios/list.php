<?php
// views/configuracion/usuarios/list.php
// Variables: $users (array)
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-lg font-bold text-vc-ink">Gestión de Usuarios</h2>
        <p class="text-sm text-gray-500">Administra el acceso al sistema</p>
    </div>
    <button onclick="document.getElementById('modal-create-user').showModal()" 
            class="bg-vc-ink text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-vc-ink/90 transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nuevo Usuario
    </button>
</div>

<!-- Tabla -->
<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead>
            <tr class="border-b border-gray-200 text-gray-500">
                <th class="py-3 px-2 font-medium">Usuario</th>
                <th class="py-3 px-2 font-medium">Correo</th>
                <th class="py-3 px-2 font-medium">Rol</th>
                <th class="py-3 px-2 font-medium">Estado</th>
                <th class="py-3 px-2 font-medium text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($users as $u): ?>
                <tr class="group hover:bg-gray-50 transition">
                    <td class="py-3 px-2 font-medium text-vc-ink">
                        <?= htmlspecialchars($u['username']) ?>
                    </td>
                    <td class="py-3 px-2 text-gray-600">
                        <?= htmlspecialchars($u['correo']) ?>
                    </td>
                    <td class="py-3 px-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            <?= htmlspecialchars($u['nombre_rol'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td class="py-3 px-2">
                        <?php if ($u['estado'] === 'ACTIVO'): ?>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> <?= htmlspecialchars($u['estado']) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-2 text-right flex items-center justify-end gap-2">
                        <!-- Toggle Active -->
                        <?php $isActive = ($u['estado'] === 'ACTIVO'); ?>
                        <a href="<?= url('index.php?controller=configuracion&action=toggleUsuario&id=' . $u['id_usuario'] . '&active=' . ($isActive ? 0 : 1)) ?>" 
                           class="text-gray-400 hover:text-vc-ink transition" 
                           title="<?= $isActive ? 'Desactivar' : 'Activar' ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php if($isActive): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                <?php endif; ?>
                            </svg>
                        </a>
                        
                        <!-- Reset Password -->
                        <form action="<?= url('index.php?controller=configuracion&action=resetPassword&id=' . $u['id_usuario']) ?>" method="POST" 
                              onsubmit="return confirm('¿Estás seguro de regenerar la contraseña para <?= $u['username'] ?>?')"
                              class="inline">
                            <button type="submit" class="text-gray-400 hover:text-vc-pink transition" title="Regenerar Contraseña">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Create User -->
<dialog id="modal-create-user" class="rounded-xl shadow-2xl p-0 w-full max-w-md backdrop:bg-black/30">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold text-lg text-vc-ink">Nuevo Usuario</h3>
        <form method="dialog"><button class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></form>
    </div>
    <form action="<?= url('index.php?controller=configuracion&action=storeUsuario') ?>" method="POST" class="p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Usuario</label>
            <input type="text" name="username" required class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-2 focus:ring-vc-pink focus:border-vc-pink outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
            <input type="email" name="correo" required class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-2 focus:ring-vc-pink focus:border-vc-pink outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
            <select name="role" class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-2 focus:ring-vc-pink focus:border-vc-pink outline-none transition">
                <option value="2">Usuario Estándar</option>
                <option value="1">Administrador</option>
            </select>
        </div>
        
        <div class="bg-blue-50 p-3 rounded-lg text-sm text-blue-800 flex gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>La contraseña se generará automáticamente y se mostrará al guardar.</p>
        </div>

        <div class="pt-4 flex justify-end gap-3">
            <form method="dialog"><button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">Cancelar</button></form>
            <button type="submit" class="bg-vc-ink text-white px-4 py-2 rounded-lg hover:bg-vc-ink/90 transition shadow-lg shadow-vc-ink/20">Crear Usuario</button>
        </div>
    </form>
</dialog>
