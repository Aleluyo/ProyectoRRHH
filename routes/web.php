<?php
declare(strict_types=1);

$router->get('/', fn() => redirect('/login'));
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'doLogin']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', function() {
  requireLogin();
  view('home/dashboard.php');
});
