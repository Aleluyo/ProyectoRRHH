<?php
// Probar API de contacto directamente
$_GET['action'] = 'obtener';
$_GET['id'] = '1';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Simular sesión de usuario
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['rol'] = 1;

ob_start();
include __DIR__ . '/public/api/contacto.php';
$output = ob_get_clean();

echo "=== RESPUESTA DE LA API ===\n";
echo $output;
echo "\n\n=== DECODIFICADO ===\n";
$json = json_decode($output, true);
if ($json) {
    print_r($json);
} else {
    echo "ERROR: La respuesta no es JSON válido\n";
    echo "Error JSON: " . json_last_error_msg() . "\n";
}
