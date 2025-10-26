<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// (opcional si no lo definiste en config.php)
if (!defined('APP_ENV')) {
    define('APP_ENV', 'local'); // cámbialo a 'production' en prod
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    // Si agregas DB_PORT en config.php, úsalo; si no, asume 3306
    $port = defined('DB_PORT') ? (int)DB_PORT : 3306;
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, $port, DB_NAME);

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_TIMEOUT            => 5,    // opcional
        ]);
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        return $pdo;
    } catch (PDOException $e) {
        if (APP_ENV === 'local') {
            http_response_code(500);
            die('DB connection error: ' . $e->getMessage());
        }
        error_log('DB connection error: ' . $e->getMessage());
        http_response_code(500);
        die('Error de conexión a la base de datos.');
    }
}

$pdo = db();
