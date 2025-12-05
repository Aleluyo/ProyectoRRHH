<?php
declare(strict_types=1);

// Script de prueba para verificar datos de empleados
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/models/Empleado.php';
require_once __DIR__ . '/app/models/EmpleadoDocumento.php';
require_once __DIR__ . '/app/models/Movimiento.php';

echo "=== PRUEBA DE DATOS DE EMPLEADOS ===\n\n";

// Obtener todos los empleados activos
$sql = "SELECT id_empleado, nombre, curp FROM empleados WHERE estado = 'Activo' ORDER BY id_empleado LIMIT 5";
$stmt = $pdo->query($sql);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Empleados encontrados: " . count($empleados) . "\n\n";

foreach ($empleados as $emp) {
    echo "----------------------------------------\n";
    echo "ID: {$emp['id_empleado']} - {$emp['nombre']}\n";
    echo "CURP: {$emp['curp']}\n\n";
    
    // Probar obtención de datos
    try {
        $empleado = Empleado::findById((int)$emp['id_empleado']);
        echo "✓ Empleado::findById() - OK\n";
        echo "  Datos: " . (is_array($empleado) ? "Array con " . count($empleado) . " campos" : "null") . "\n";
    } catch (Exception $e) {
        echo "✗ Empleado::findById() - ERROR: " . $e->getMessage() . "\n";
    }
    
    try {
        $documentos = EmpleadoDocumento::porEmpleado((int)$emp['id_empleado']);
        echo "✓ EmpleadoDocumento::porEmpleado() - OK\n";
        echo "  Documentos encontrados: " . count($documentos) . "\n";
        
        if (!empty($documentos)) {
            echo "  Campos del primer documento:\n";
            foreach (array_keys($documentos[0]) as $key) {
                echo "    - $key\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ EmpleadoDocumento::porEmpleado() - ERROR: " . $e->getMessage() . "\n";
    }
    
    try {
        $movimientos = Movimiento::historialEmpleado((int)$emp['id_empleado']);
        echo "✓ Movimiento::historialEmpleado() - OK\n";
        echo "  Movimientos encontrados: " . count($movimientos) . "\n";
        
        if (!empty($movimientos)) {
            echo "  Campos del primer movimiento:\n";
            foreach (array_keys($movimientos[0]) as $key) {
                echo "    - $key\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ Movimiento::historialEmpleado() - ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== FIN DE LA PRUEBA ===\n";
