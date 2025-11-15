<?php
declare(strict_types=1);
/**
 * API: Listado de usuarios (JSON) — DEPURACIÓN
 * GET /public/api/users/list.php?q=&role=&ciudad=&status=
 */
$ROOT = dirname(__DIR__, 3);
require_once $ROOT . '/config/paths.php';
require_once $ROOT . '/app/middleware/Auth.php';
require_once $ROOT . '/app/middleware/Permissions.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');   // no mezclar notices con el JSON
ini_set('log_errors', '1');

try {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['ok'=>false,'error'=>'No autorizado (isAdmin=false)']);
        exit;
    }

    require_once $ROOT . '/app/controllers/UserController.php';

    $q      = trim((string)($_GET['q'] ?? ''));
    $role   = isset($_GET['role'])   && $_GET['role']   !== '' ? (string)$_GET['role']   : null;
    $ciudad = isset($_GET['ciudad']) && $_GET['ciudad'] !== '' ? (string)$_GET['ciudad'] : null;
    $status = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status']    : null;

    $rows   = UserController::all($q ?: null, $role, $status, $ciudad);
    $roles  = UserController::roleOptions();

    echo json_encode([
        'ok'    => true,
        'count' => count($rows),
        'data'  => array_map(function($u) use ($roles){
            return [
                'id'            => (int)$u['id'],
                'username'      => (string)$u['username'],
                'first_name'    => (string)$u['first_name'],
                'last_name'     => (string)$u['last_name'],
                'name'          => trim($u['first_name'].' '.$u['last_name']),
                'role'          => (string)$u['role'],
                'role_label'    => $roles[$u['role']] ?? (string)$u['role'],
                'area'          => (string)$u['area'],
                'puesto'        => (string)$u['puesto'],
                'ciudad'        => (string)$u['ciudad'],
                'is_active'     => (int)$u['is_active'],
                'last_login_at' => $u['last_login_at'] ?: null,
                'created_at'    => $u['created_at'] ?? null,
                'updated_at'    => $u['updated_at'] ?? null,
            ];
        }, $rows),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    // <<< DEV-ONLY: devuelve mensaje y archivo/linea para encontrar el error >>>
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Server error',
        'debug' => [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            // 'trace' => $e->getTrace(), // descomenta si ocupas
        ],
    ], JSON_UNESCAPED_UNICODE);
}
