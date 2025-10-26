<?php
declare(strict_types=1);
/**
 * API: Crear/Actualizar usuario
 * POST /public/api/users/save.php
 * Campos:
 *  - id (opcional para update)
 *  - username, password(opcional en update), first_name, last_name, role, area, puesto, ciudad, is_active(0|1)
 */
$ROOT = dirname(__DIR__, 3);
require_once $ROOT . '/config/paths.php';
require_once $ROOT . '/app/middleware/Auth.php';
require_once $ROOT . '/app/middleware/Permissions.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

try {
    requireLogin();
    if (!isAdmin()) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }

    if (session_status() === PHP_SESSION_NONE) session_start();
    $csrfHeader = $_SERVER['HTTP_X_CSRF'] ?? null;
    if (!isset($_SESSION['csrf']) || $csrfHeader !== $_SESSION['csrf']) {
        http_response_code(400); echo json_encode(['ok'=>false,'error'=>'CSRF inválido']); exit;
    }

    require_once $ROOT . '/app/controllers/UserController.php';

    // Recoger datos (JSON o form-data)
    $raw = file_get_contents('php://input');
    $data = [];
    if ($raw && ($obj = json_decode($raw, true)) && is_array($obj)) {
        $data = $obj;
    } else {
        $data = $_POST; // por si lo mandas como formdata
    }

    $id = isset($data['id']) && $data['id'] !== '' ? (int)$data['id'] : null;

    $payload = [
        'username'   => $data['username']   ?? '',
        'password'   => $data['password']   ?? '',
        'first_name' => $data['first_name'] ?? '',
        'last_name'  => $data['last_name']  ?? '',
        'role'       => $data['role']       ?? '',
        'area'       => $data['area']       ?? '',
        'puesto'     => $data['puesto']     ?? '',
        'ciudad'     => $data['ciudad']     ?? '',
        'is_active'  => isset($data['is_active']) && (string)$data['is_active'] !== '0' ? '1' : '0',
    ];

    if ($id) {
        UserController::update($id, $payload);
        echo json_encode(['ok'=>true, 'id'=>$id, 'message'=>'Usuario actualizado'], JSON_UNESCAPED_UNICODE);
    } else {
        $newId = UserController::create($payload);
        echo json_encode(['ok'=>true, 'id'=>$newId, 'message'=>'Usuario creado'], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'Server error']);
}
