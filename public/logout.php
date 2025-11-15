<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

// Cerrar sesión
AuthController::logout();

// Redirigir al login con flag para mostrar SweetAlert
redirect('login.php?loggedout=1');
