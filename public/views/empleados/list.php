<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../middleware/Auth.php';

requireLogin();
requireRole(1);

$search = $_GET['q'] ?? '';
$estado = $_GET['estado'] ?? '';
$idEmpresa = $_GET['id_empresa'] ?? '';
$idArea = $_GET['id_area'] ?? '';
$idPuesto = $_GET['id_puesto'] ?? '';

$empleados = $empleados ?? [];
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Empleados · Expedientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-white text-gray-900">
    <header class="p-4 border-b">
        <h1 class="text-2xl font-bold">Empleados</h1>
        <p class="text-sm text-gray-600">Altas, expedientes y consultas</p>
    </header>

    <main class="p-4 space-y-4">

        <div class="flex justify-between items-center">
            <form method="get" action="index.php" class="flex flex-wrap gap-2 items-end">
                <input type="hidden" name="controller" value="empleado">
                <input type="hidden" name="action" value="index">

                <div>
                    <label class="block text-xs font-semibold mb-1">Búsqueda</label>
                    <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                        class="border rounded px-2 py-1 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1">Estado</label>
                    <select name="estado" class="border rounded px-2 py-1 text-sm">
                        <option value="">-- Todos --</option>
                        <option value="ACTIVO" <?= $estado === 'ACTIVO' ? 'selected' : '' ?>>Activo</option>
                        <option value="BAJA" <?= $estado === 'BAJA' ? 'selected' : '' ?>>Baja</option>
                    </select>
                </div>

                <button type="submit" class="px-3 py-1 border rounded text-sm font-semibold">
                    Filtrar
                </button>
            </form>

            <a href="<?= url('index.php?controller=empleado&action=create') ?>"
                class="inline-flex items-center px-4 py-2 text-sm font-semibold border rounded">
                Nuevo empleado
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="text-left px-2 py-2">Nombre</th>
                        <th class="text-left px-2 py-2">Empresa</th>
                        <th class="text-left px-2 py-2">Área</th>
                        <th class="text-left px-2 py-2">Puesto</th>
                        <th class="text-left px-2 py-2">Estado</th>
                        <th class="text-left px-2 py-2">Fecha ingreso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($empleados)): ?>
                        <tr>
                            <td colspan="6" class="px-2 py-4 text-center text-gray-500">
                                No se encontraron empleados con los filtros actuales.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($empleados as $emp): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-2 py-2">
                                    <?= htmlspecialchars($emp['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-2 py-2">
                                    <?= htmlspecialchars($emp['empresa_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-2 py-2">
                                    <?= htmlspecialchars($emp['nombre_area'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-2 py-2">
                                    <?= htmlspecialchars($emp['nombre_puesto'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-2 py-2">
                                    <?= htmlspecialchars($emp['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-2 py-2">
                                    <?= htmlspecialchars($emp['fecha_ingreso'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>

</html>