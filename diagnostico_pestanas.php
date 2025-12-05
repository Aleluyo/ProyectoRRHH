<?php
declare(strict_types=1);

// Script de diagnóstico para el problema de las pestañas
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/models/Empleado.php';
require_once __DIR__ . '/app/models/EmpleadoDocumento.php';
require_once __DIR__ . '/app/models/Movimiento.php';

// Obtener ID del empleado de la URL o usar 1 por defecto
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

echo "<!DOCTYPE html>";
echo "<html><head><title>Diagnóstico - Empleado $id</title>";
echo "<style>body{font-family:monospace;padding:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>";
echo "</head><body>";

echo "<h1>Diagnóstico del Empleado ID: $id</h1>";
echo "<p><a href='?id=" . ($id-1) . "'>← Anterior</a> | <a href='?id=" . ($id+1) . "'>Siguiente →</a></p>";

echo "<hr>";

// 1. Verificar empleado
echo "<h2>1. Verificar datos del empleado</h2>";
try {
    $empleado = Empleado::findById($id);
    if ($empleado) {
        echo "<p class='success'>✓ Empleado encontrado</p>";
        echo "<pre>";
        echo "Nombre: " . htmlspecialchars($empleado['nombre']) . "\n";
        echo "CURP: " . htmlspecialchars($empleado['curp'] ?? 'N/A') . "\n";
        echo "Estado: " . htmlspecialchars($empleado['estado'] ?? 'N/A') . "\n";
        echo "Campos totales: " . count($empleado) . "\n";
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ Empleado NO encontrado</p>";
        echo "<p><a href='index.php?controller=empleado&action=listado'>Ver listado de empleados</a></p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ ERROR al obtener empleado: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    exit;
}

// 2. Verificar documentos
echo "<h2>2. Verificar documentos</h2>";
try {
    $documentos = EmpleadoDocumento::porEmpleado($id);
    echo "<p class='success'>✓ Query de documentos ejecutado correctamente</p>";
    echo "<p>Documentos encontrados: <strong>" . count($documentos) . "</strong></p>";
    
    if (!empty($documentos)) {
        echo "<p class='warning'>Campos disponibles en documentos:</p>";
        echo "<pre>";
        print_r(array_keys($documentos[0]));
        echo "</pre>";
        
        echo "<p>Primer documento:</p>";
        echo "<pre>";
        foreach ($documentos[0] as $key => $value) {
            echo "$key: " . htmlspecialchars(var_export($value, true)) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='warning'>⚠ No hay documentos para este empleado</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ ERROR al obtener documentos: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// 3. Verificar movimientos
echo "<h2>3. Verificar movimientos</h2>";
try {
    $movimientos = Movimiento::historialEmpleado($id);
    echo "<p class='success'>✓ Query de movimientos ejecutado correctamente</p>";
    echo "<p>Movimientos encontrados: <strong>" . count($movimientos) . "</strong></p>";
    
    if (!empty($movimientos)) {
        echo "<p class='warning'>Campos disponibles en movimientos:</p>";
        echo "<pre>";
        print_r(array_keys($movimientos[0]));
        echo "</pre>";
        
        echo "<p>Primer movimiento:</p>";
        echo "<pre>";
        foreach ($movimientos[0] as $key => $value) {
            echo "$key: " . htmlspecialchars(var_export($value, true)) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='warning'>⚠ No hay movimientos para este empleado</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ ERROR al obtener movimientos: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// 4. Simular renderizado de pestañas
echo "<h2>4. Simulación de renderizado de pestañas</h2>";

// Pestaña Documentos
echo "<h3>Pestaña Documentos (simulación)</h3>";
if (!empty($documentos)) {
    echo "<p class='success'>✓ Se renderizaría foreach con " . count($documentos) . " documento(s)</p>";
    echo "<p>Campos críticos verificados:</p>";
    echo "<ul>";
    foreach ($documentos as $idx => $doc) {
        echo "<li>Documento " . ($idx + 1) . ":<ul>";
        echo "<li>tipo_documento: " . (isset($doc['tipo_documento']) ? '✓' : '✗') . "</li>";
        echo "<li>estado: " . (isset($doc['estado']) ? '✓' : '✗') . "</li>";
        echo "<li>nombre_archivo: " . (isset($doc['nombre_archivo']) ? '✓' : '✗') . "</li>";
        echo "<li>fecha_subida: " . (isset($doc['fecha_subida']) ? '✓' : '✗') . "</li>";
        echo "<li>usuario_verificacion: " . (isset($doc['usuario_verificacion']) ? '✓' : '✗') . "</li>";
        echo "</ul></li>";
    }
    echo "</ul>";
} else {
    echo "<p class='success'>✓ Se renderizaría mensaje 'No hay documentos'</p>";
}

// Pestaña Historial
echo "<h3>Pestaña Historial (simulación)</h3>";
if (!empty($movimientos)) {
    echo "<p class='success'>✓ Se renderizaría foreach con " . count($movimientos) . " movimiento(s)</p>";
    echo "<p>Campos críticos verificados:</p>";
    echo "<ul>";
    foreach ($movimientos as $idx => $mov) {
        echo "<li>Movimiento " . ($idx + 1) . ":<ul>";
        echo "<li>tipo_movimiento: " . (isset($mov['tipo_movimiento']) ? '✓' : '✗') . "</li>";
        echo "<li>fecha_movimiento: " . (isset($mov['fecha_movimiento']) ? '✓' : '✗') . "</li>";
        echo "<li>motivo: " . (isset($mov['motivo']) ? '✓' : '✗') . "</li>";
        echo "<li>usuario_registro: " . (isset($mov['usuario_registro']) ? '✓' : '✗') . "</li>";
        echo "<li>fecha_registro: " . (isset($mov['fecha_registro']) ? '✓' : '✗') . "</li>";
        echo "</ul></li>";
    }
    echo "</ul>";
} else {
    echo "<p class='success'>✓ Se renderizaría mensaje 'No hay movimientos'</p>";
}

echo "<hr>";
echo "<h2>Conclusión</h2>";
echo "<p><strong>Si todos los checks anteriores son ✓, entonces el problema está en el JavaScript del navegador.</strong></p>";
echo "<p>Para verificar en el navegador:</p>";
echo "<ol>";
echo "<li>Abre el expediente del empleado: <a href='index.php?controller=empleado&action=show&id=$id' target='_blank'>Ver expediente</a></li>";
echo "<li>Presiona F12 para abrir las herramientas de desarrollador</li>";
echo "<li>Ve a la pestaña 'Console'</li>";
echo "<li>Busca errores en rojo</li>";
echo "<li>Intenta hacer clic en las pestañas y observa los logs</li>";
echo "</ol>";

echo "</body></html>";
