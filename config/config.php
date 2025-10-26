<?php
declare(strict_types=1);

require_once __DIR__ . '/paths.php';

/* Errores (dev) */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/* Zona horaria */
date_default_timezone_set('America/Tijuana');

/* Sesión segura */
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    );

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('AAHNSESSID');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax' // en prod puedes cambiar a 'Strict'
    ]);

    session_start();
}

/* BD */
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'rrhh_tec');
define('DB_USER', 'root');
define('DB_PASS', '');

/* Seguridad login */
define('PASSWORD_COST', 10);
define('LOGIN_WINDOW_MINUTES', 10);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCK_MINUTES', 15);
