<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');
define('CONF_PATH', BASE_PATH . '/config');
define('CORE_PATH', BASE_PATH . '/core');
define('VIEW_PATH', APP_PATH . '/views');
define('PUB_PATH',  BASE_PATH . '/public');

$CFG = require CONF_PATH . '/env.php'; // ← importante cargar aquí

function view(string $path, array $data = []): void {
    extract($data, EXTR_OVERWRITE);
    require VIEW_PATH . '/' . ltrim($path, '/');
}

function base_url(string $path = ''): string {
    global $CFG;
    return rtrim($CFG['app']['base_url'], '/') . '/' . ltrim($path, '/');
}

function asset(string $rel): string {
    // http://localhost/ProyectoRRHH/public/assets/...
    return base_url('public/assets/' . ltrim($rel, '/'));
}

function redirect(string $to): void {
    // Acepta “/login” o “login”
    $url = str_starts_with($to, 'http')
        ? $to
        : base_url(ltrim($to, '/'));
    header('Location: ' . $url);
    exit;
}
