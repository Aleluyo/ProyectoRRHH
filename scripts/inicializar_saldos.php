<?php
/**
 * Script para inicializar saldos de vacaciones de todos los empleados activos
 * 
 * Este script debe ejecutarse una vez para inicializar los saldos de todos los empleados
 * o puede ejecutarse anualmente para actualizar los saldos del nuevo año.
 * 
 * Uso: php scripts/inicializar_saldos.php [año]
 */

declare(strict_types=1);

// Incluir configuración
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/models/SaldosVacaciones.php';

// Obtener año desde argumentos o usar año actual
$anio = isset($argv[1]) ? (int)$argv[1] : (int)date('Y');

echo "==========================================\n";
echo "Inicialización de Saldos de Vacaciones\n";
echo "Año: $anio\n";
echo "==========================================\n\n";

try {
    // Ejecutar inicialización
    $resultados = SaldosVacaciones::inicializarSaldosTodos($anio);
    
    $exitosos = 0;
    $errores = 0;
    
    foreach ($resultados as $resultado) {
        if ($resultado['status'] === 'OK') {
            $exitosos++;
            echo "[✓] Empleado ID {$resultado['id_empleado']}: Saldo inicializado\n";
        } else {
            $errores++;
            echo "[✗] Empleado ID {$resultado['id_empleado']}: ERROR - {$resultado['mensaje']}\n";
        }
    }
    
    echo "\n==========================================\n";
    echo "Resumen:\n";
    echo "  Total procesados: " . count($resultados) . "\n";
    echo "  Exitosos: $exitosos\n";
    echo "  Errores: $errores\n";
    echo "==========================================\n";
    
    exit($errores > 0 ? 1 : 0);
    
} catch (\Throwable $e) {
    echo "\n[ERROR FATAL] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
