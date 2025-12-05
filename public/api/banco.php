<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/middleware/Auth.php';
require_once __DIR__ . '/../../app/models/EmpleadoBanco.php';

requireLogin();
requireRole(1);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        case 'crear':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $data = [
                'id_empleado' => (int)($_POST['id_empleado'] ?? 0),
                'banco' => trim($_POST['banco'] ?? ''),
                'clabe' => trim($_POST['clabe'] ?? ''),
                'titular' => trim($_POST['titular'] ?? ''),
                'activa' => 1
            ];

            // Validaciones adicionales
            if (empty($data['banco'])) {
                throw new Exception('El nombre del banco es obligatorio');
            }

            if (!preg_match('/^\d{18}$/', $data['clabe'])) {
                throw new Exception('La CLABE debe tener exactamente 18 dígitos');
            }

            if (empty($data['titular'])) {
                throw new Exception('El nombre del titular es obligatorio');
            }

            // Verificar si la CLABE ya existe
            if (EmpleadoBanco::clabeExiste($data['clabe'])) {
                throw new Exception('La CLABE ya está registrada en el sistema');
            }

            $id = EmpleadoBanco::create($data);

            echo json_encode([
                'success' => true,
                'message' => 'Cuenta bancaria agregada exitosamente',
                'id' => $id
            ]);
            break;

        case 'editar':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $idBanco = (int)($_POST['id_banco'] ?? 0);
            if ($idBanco <= 0) {
                throw new Exception('ID de cuenta inválido');
            }

            $cuenta = EmpleadoBanco::findById($idBanco);
            if (!$cuenta) {
                throw new Exception('Cuenta bancaria no encontrada');
            }

            $data = [
                'banco' => trim($_POST['banco'] ?? ''),
                'clabe' => trim($_POST['clabe'] ?? ''),
                'titular' => trim($_POST['titular'] ?? ''),
                'activa' => (int)($_POST['activa'] ?? 1)
            ];

            // Validaciones
            if (empty($data['banco'])) {
                throw new Exception('El nombre del banco es obligatorio');
            }

            if (!preg_match('/^\d{18}$/', $data['clabe'])) {
                throw new Exception('La CLABE debe tener exactamente 18 dígitos');
            }

            if (empty($data['titular'])) {
                throw new Exception('El nombre del titular es obligatorio');
            }

            // Verificar si la CLABE ya existe (excepto la actual)
            if (EmpleadoBanco::clabeExiste($data['clabe'], $idBanco)) {
                throw new Exception('La CLABE ya está registrada en otra cuenta');
            }

            EmpleadoBanco::update($idBanco, $data);

            echo json_encode([
                'success' => true,
                'message' => 'Cuenta bancaria actualizada exitosamente'
            ]);
            break;

        case 'obtener':
            if ($method !== 'GET') {
                throw new Exception('Método no permitido');
            }

            $idBanco = (int)($_GET['id'] ?? 0);
            if ($idBanco <= 0) {
                throw new Exception('ID de cuenta inválido');
            }

            $cuenta = EmpleadoBanco::findById($idBanco);
            if (!$cuenta) {
                throw new Exception('Cuenta bancaria no encontrada');
            }

            echo json_encode([
                'success' => true,
                'data' => $cuenta
            ]);
            break;

        case 'desactivar':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $idBanco = (int)($_POST['id_banco'] ?? 0);
            if ($idBanco <= 0) {
                throw new Exception('ID de cuenta inválido');
            }

            $cuenta = EmpleadoBanco::findById($idBanco);
            if (!$cuenta) {
                throw new Exception('Cuenta bancaria no encontrada');
            }

            EmpleadoBanco::desactivar($idBanco);

            echo json_encode([
                'success' => true,
                'message' => 'Cuenta bancaria desactivada exitosamente'
            ]);
            break;

        case 'eliminar':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $idBanco = (int)($_POST['id_banco'] ?? 0);
            if ($idBanco <= 0) {
                throw new Exception('ID de cuenta inválido');
            }

            $cuenta = EmpleadoBanco::findById($idBanco);
            if (!$cuenta) {
                throw new Exception('Cuenta bancaria no encontrada');
            }

            EmpleadoBanco::delete($idBanco);

            echo json_encode([
                'success' => true,
                'message' => 'Cuenta bancaria eliminada exitosamente'
            ]);
            break;

        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
