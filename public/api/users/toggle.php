<?php
declare(strict_types=1);
/**
 * API: Toggle estado
 */
$ROOT = dirname(__DIR__, 3);
require_once $ROOT . '/config/paths.php';
require_once $ROOT . '/app/middleware/Auth.php';
require_once $ROOT . '/app/middleware/Permissions.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

try {
    requireLogin();
    if (!isAdmin()) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }
    if (session_status() === PHP_SESSION_NONE) session_start();

    $csrfHeader = $_SERVER['HTTP_X_CSRF'] ?? null;
    $csrfField  = $_POST['csrf'] ?? null;
    if (!isset($_SESSION['csrf']) || (($csrfHeader ?? $csrfField) !== $_SESSION['csrf'])) {
        http_response_code(400); echo json_encode(['ok'=>false,'error'=>'CSRF inválido']); exit;
    }

    require_once $ROOT . '/app/controllers/UserController.php';

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }

    $user = UserController::find($id);
    if (!$user) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Usuario no encontrado']); exit; }

    $desired = isset($_POST['active']) ? (int)$_POST['active'] : (1 - (int)$user['is_active']);
    $desired = $desired ? 1 : 0;

    $pdo = (function(){ require PATH_CONFIG . '/db.php'; return db(); })();
    $st = $pdo->prepare("UPDATE users SET is_active=:a WHERE id=:id");
    $st->execute([':a'=>$desired, ':id'=>$id]);

    echo json_encode(['ok'=>true, 'id'=>$id, 'is_active'=>$desired], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'Server error']);
}
