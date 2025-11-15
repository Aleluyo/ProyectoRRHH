<?php
declare(strict_types=1);

// Helpers requeridos: url(), redirect(), PATH_PUBLIC
require_once __DIR__ . '/../../config/paths.php';    // Define url(), redirect(), PATH_PUBLIC
require_once __DIR__ . '/../../config/config.php';   // Si tienes constantes de app/roles

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const SESSION_IDLE_MINUTES     = 60;   // Tiempo de inactividad (min) para cerrar sesión
const SESSION_ABSOLUTE_MINUTES = 480;  // Tiempo máximo absoluto de sesión (min)

function enforceSessionTimeouts(): void
{
    $now = time();

    $_SESSION['session_started_at'] = $_SESSION['session_started_at'] ?? $now;
    $_SESSION['last_activity_at']   = $_SESSION['last_activity_at']   ?? $now;

    $idleLimit     = SESSION_IDLE_MINUTES * 60;
    $absoluteLimit = SESSION_ABSOLUTE_MINUTES * 60;

    $idleElapsed     = $now - (int)$_SESSION['last_activity_at'];
    $absoluteElapsed = $now - (int)$_SESSION['session_started_at'];

    if ($idleElapsed > $idleLimit || $absoluteElapsed > $absoluteLimit) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        redirect('login.php?expired=1');
        exit;
    }

    $_SESSION['last_activity_at'] = $now;
    // Re-genera ID cada 15 min para seguridad
    if (!isset($_SESSION['last_regen']) || ($now - (int)$_SESSION['last_regen']) > 900) {
        session_regenerate_id(true);
        $_SESSION['last_regen'] = $now;
    }
}

/**
 * Redirige a login si el usuario no está autenticado.
 * Llama internamente a enforceSessionTimeouts().
 */
function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        $req = $_SERVER['REQUEST_URI'] ?? url('index.php');
        redirect('login.php?redirect=' . urlencode($req));
        exit;
    }
    enforceSessionTimeouts();
}

/**
 * Valida que el usuario tenga el rol requerido.
 * Por default, tus roles en sesión son enteros (1=admin, 2=usuario, etc.)
 *
 * @param int $requiredRole
 */
function requireRole(int $requiredRole): void
{
    requireLogin();
    $userRole = $_SESSION['rol'] ?? 2; // Por defecto 2 (usuario normal)
    if ($userRole !== $requiredRole) {
        http_response_code(403);
        require PATH_PUBLIC . '/403.php';
        exit;
    }
}

