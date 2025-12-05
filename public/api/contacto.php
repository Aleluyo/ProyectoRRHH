<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/middleware/Auth.php';
require_once __DIR__ . '/../../app/models/EmpleadoContacto.php';

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
                'tipo' => trim($_POST['tipo'] ?? 'EMERGENCIA'),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'correo' => trim($_POST['correo'] ?? ''),
                'parentesco' => trim($_POST['parentesco'] ?? ''),
                'activo' => 1
            ];

            // Validaciones
            if (empty($data['nombre'])) {
                throw new Exception('El nombre del contacto es obligatorio');
            }

            if (empty($data['telefono'])) {
                throw new Exception('El teléfono es obligatorio');
            }

            $tiposValidos = ['EMERGENCIA', 'PERSONAL', 'OTRO'];
            if (!in_array($data['tipo'], $tiposValidos)) {
                throw new Exception('Tipo de contacto no válido');
            }

            // Validar email si se proporciona
            if (!empty($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El correo electrónico no es válido');
            }

            $id = EmpleadoContacto::create($data);

            echo json_encode([
                'success' => true,
                'message' => 'Contacto agregado exitosamente',
                'id' => $id
            ]);
            break;

        case 'editar':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $idContacto = (int)($_POST['id_contacto'] ?? 0);
            if ($idContacto <= 0) {
                throw new Exception('ID de contacto inválido');
            }

            $contacto = EmpleadoContacto::findById($idContacto);
            if (!$contacto) {
                throw new Exception('Contacto no encontrado');
            }

            $data = [
                'tipo' => trim($_POST['tipo'] ?? 'EMERGENCIA'),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'correo' => trim($_POST['correo'] ?? ''),
                'parentesco' => trim($_POST['parentesco'] ?? ''),
                'activo' => (int)($_POST['activo'] ?? 1)
            ];

            // Validaciones
            if (empty($data['nombre'])) {
                throw new Exception('El nombre del contacto es obligatorio');
            }

            if (empty($data['telefono'])) {
                throw new Exception('El teléfono es obligatorio');
            }

            $tiposValidos = ['EMERGENCIA', 'PERSONAL', 'OTRO'];
            if (!in_array($data['tipo'], $tiposValidos)) {
                throw new Exception('Tipo de contacto no válido');
            }

            // Validar email si se proporciona
            if (!empty($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El correo electrónico no es válido');
            }

            EmpleadoContacto::update($idContacto, $data);

            echo json_encode([
                'success' => true,
                'message' => 'Contacto actualizado exitosamente'
            ]);
            break;

        case 'obtener':
            if ($method !== 'GET') {
                throw new Exception('Método no permitido');
            }

            $idContacto = (int)($_GET['id'] ?? 0);
            if ($idContacto <= 0) {
                throw new Exception('ID de contacto inválido');
            }

            $contacto = EmpleadoContacto::findById($idContacto);
            if (!$contacto) {
                throw new Exception('Contacto no encontrado');
            }

            echo json_encode([
                'success' => true,
                'data' => $contacto
            ]);
            break;

        case 'desactivar':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $idContacto = (int)($_POST['id_contacto'] ?? 0);
            if ($idContacto <= 0) {
                throw new Exception('ID de contacto inválido');
            }

            $contacto = EmpleadoContacto::findById($idContacto);
            if (!$contacto) {
                throw new Exception('Contacto no encontrado');
            }

            EmpleadoContacto::desactivar($idContacto);

            echo json_encode([
                'success' => true,
                'message' => 'Contacto desactivado exitosamente'
            ]);
            break;

        case 'eliminar':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $idContacto = (int)($_POST['id_contacto'] ?? 0);
            if ($idContacto <= 0) {
                throw new Exception('ID de contacto inválido');
            }

            $contacto = EmpleadoContacto::findById($idContacto);
            if (!$contacto) {
                throw new Exception('Contacto no encontrado');
            }

            EmpleadoContacto::delete($idContacto);

            echo json_encode([
                'success' => true,
                'message' => 'Contacto eliminado exitosamente'
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
