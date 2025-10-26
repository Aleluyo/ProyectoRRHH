<?php
declare(strict_types=1);

/**
 * Detecta BASE_URL y define helpers/constantes de rutas.
 * Funciona en XAMPP (carpeta dentro de htdocs) y en VirtualHost apuntando a /public.
 *
 * Constantes expuestas:
 *  - PROJECT_ROOT  => /ruta/completa/al/proyecto (sin /public)
 *  - PATH_PUBLIC   => PROJECT_ROOT . '/public'
 *  - PATH_APP      => PROJECT_ROOT . '/app'
 *  - PATH_CONFIG   => PROJECT_ROOT . '/config'
 *  - PATH_ASSETS   => PATH_PUBLIC  . '/assets'
 *  - PATH_ROOT     => alias de PROJECT_ROOT (compatibilidad con código existente)
 *  - BASE_URL      => prefijo público (p.ej. "/AAHN/public" o "/")
 *  - APP_URL       => alias de BASE_URL
 *
 * Helpers:
 *  - url($path)    => BASE_URL + $path (si BASE_URL = '/', regresa $path tal cual)
 *  - asset($path)  => url('assets/' . $path)
 *  - redirect($path, $code=302) => redirección relativa a BASE_URL o absoluta (http/https)
 *  - current_url() => URL actual completa
 *  - is_active($startsWithPath) => si la URL actual empieza con esa ruta (útil para menús)
 */

/* ===== Polyfills mínimos (para PHP < 8) ===== */
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        if ($needle === '') return true;
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

/* ===== Raíz del proyecto y paths base ===== */
if (!defined('PROJECT_ROOT')) {
    // .../AAHN/config/paths.php -> PROJECT_ROOT = .../AAHN
    define('PROJECT_ROOT', dirname(__DIR__));
}

$publicPath = PROJECT_ROOT . '/public';

/* ===== Cálculo de BASE_URL ===== */
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$docRoot = rtrim(str_replace('\\', '/', $docRoot), '/');

$baseUrl = '';

if ($docRoot !== '' && is_dir($publicPath)) {
    $publicReal = realpath($publicPath);
    $docReal    = realpath($docRoot);

    if ($publicReal !== false && $docReal !== false) {
        $publicReal = rtrim(str_replace('\\', '/', $publicReal), '/');
        $docReal    = rtrim(str_replace('\\', '/', $docReal), '/');

        if (strpos($publicReal, $docReal) === 0) {
            // Prefijo público = diferencia entre /document_root y /public real
            $baseUrl = substr($publicReal, strlen($docReal));
            if ($baseUrl === '') $baseUrl = '/';
            if ($baseUrl[0] !== '/') $baseUrl = '/' . $baseUrl;
        }
    }
}

/* Fallback: deducir desde SCRIPT_NAME si lo anterior no fue posible */
if ($baseUrl === '') {
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $pos = strpos($script, '/public/');
    $baseUrl = ($pos !== false) ? substr($script, 0, $pos + 7) : '/';
}

/* Normaliza: sin slash final (excepto root) */
$baseUrl = rtrim($baseUrl, '/');
if ($baseUrl === '') $baseUrl = '/';

define('BASE_URL', $baseUrl);   // ej: /AAHN/public o /
define('APP_URL',  BASE_URL);   // alias

/* ===== Paths de filesystem útiles ===== */
define('PATH_PUBLIC', $publicPath);
define('PATH_APP',    PROJECT_ROOT . '/app');
define('PATH_CONFIG', PROJECT_ROOT . '/config');
define('PATH_ASSETS', PATH_PUBLIC . '/assets');

/* ===== Alias de compatibilidad =====
 * Algunos scripts esperan PATH_ROOT; lo igualamos a PROJECT_ROOT para evitar errores.
 */
if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', PROJECT_ROOT);
}

/* ===== Helpers de URL ===== */

/** Construye una URL relativa a BASE_URL. */
function url(string $path = ''): string {
    $path = '/' . ltrim($path, '/');
    return BASE_URL === '/' ? $path : BASE_URL . $path;
}

/** Construye una URL a /assets dentro de /public. */
function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Redirige a una ruta relativa a BASE_URL o a una URL absoluta (http/https).
 * @param string $path Ruta relativa (index.php, views/..., etc.) o URL absoluta
 * @param int    $code Código HTTP (302 por default)
 * @return never
 */
function redirect(string $path, int $code = 302): never {
    $isAbsolute = preg_match('#^https?://#i', $path) === 1;
    $to = $isAbsolute ? $path : url($path);
    header("Location: {$to}", true, $code);
    exit;
}

/** Devuelve la URL actual (incluye query string). */
function current_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri    = $_SERVER['REQUEST_URI'] ?? '/';
    return "{$scheme}://{$host}{$uri}";
}

/**
 * Marca activa una ruta para menús (true si la URL actual empieza con esa ruta).
 * Útil en navbars: is_active('views/quotes/') o is_active('views/legacy/')
 */
function is_active(string $startsWithPath): bool {
    $req = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $prefix = url($startsWithPath);
    return strpos($req, $prefix) === 0;
}
