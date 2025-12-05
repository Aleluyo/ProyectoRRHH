<?php
//app/public/views/empleados/list.php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

$area = htmlspecialchars($_SESSION['area'] ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// Por ahora, $empleados puede no venir definido, aseguramos que exista
$empleados = $empleados ?? [];
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>DETALLE EMPLEADO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-white text-vc-ink font-sans">
    <header class="p-4 border-b">
        <h1 class="text-2xl font-bold">Empleados</h1>
        <p class="text-sm text-gray-600">Altas, expedientes y consultas</p>
    </header>

    <main class="p-4">
        <a href="<?= url('index.php?controller=empleado&action=show') ?>"
            class="inline-flex items-center px-4 py-2 mb-4 text-sm font-semibold border rounded">
            Detalle empleado
        </a>

        <p>ESTA ES LA VISTA DE DETALLE EMPLEADO</p>
    </main>
</body>

</html>