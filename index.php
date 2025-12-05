<?php
// Front controller shim: redirect to /public/index.php when mod_rewrite is not active.
$target = __DIR__ . '/public/index.php';
if (file_exists($target)) {
    require $target;
    exit;
}
http_response_code(404);
echo 'public/index.php no encontrado';
