<?php
declare(strict_types=1);
/**
 * API: Obtener un usuario por ID
 * GET /public/api/users/get.php?id=123
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

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }

    require_once $ROOT . '/app/controllers/UserController.php';
    $u = UserController::find($id);
    if (!$u) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Usuario no encontrado']); exit; }

    echo json_encode(['ok'=>true, 'data'=>[
        'id'=>(int)$u['id'],
        'username'=>$u['username'],
        'first_name'=>$u['first_name'],
        'last_name'=>$u['last_name'],
        'role'=>$u['role'],
        'area'=>$u['area'],
        'puesto'=>$u['puesto'],
        'ciudad'=>$u['ciudad'],
        'is_active'=>(int)$u['is_active'],
        'last_login_at'=>$u['last_login_at'],
        'created_at'=>$u['created_at'],
        'updated_at'=>$u['updated_at'],
    ]], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Server error']);
}
