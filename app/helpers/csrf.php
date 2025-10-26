<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    // (opcional) fuerza SameSite=Lax por si acaso
    // session_set_cookie_params(['samesite' => 'Lax']);
    session_start();
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' .
           htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_validate(): bool {
    $sent = $_POST['_csrf'] ?? '';
    $sess = $_SESSION['_csrf_token'] ?? '';
    $ok = is_string($sent) && is_string($sess) && hash_equals($sess, $sent);
    // ❌ NO BORRAR EL TOKEN en modo visor/iframe:
    // unset($_SESSION['_csrf_token']);
    return $ok;
}
