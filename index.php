<?php
declare(strict_types=1);

$ROOT = __DIR__;
require_once $ROOT . '/config/paths.php';
require_once $ROOT . '/core/Session.php';
require_once $ROOT . '/core/Router.php';
require_once $ROOT . '/app/middleware/Auth.php';
require_once $ROOT . '/app/controllers/AuthController.php';

$CFG = require $ROOT . '/config/env.php';
Session::start($CFG['security']['session_name']);

$router = new Router();
require $ROOT . '/routes/web.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
