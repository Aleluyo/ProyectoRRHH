<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Dashboard RRHH_TEC</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
  <header class="topbar">
    <div>Bienvenido <?= htmlspecialchars(Session::get('user')['username'] ?? '') ?></div>
    <nav><a href="<?= base_url('logout') ?>">Cerrar sesión</a></nav>
  </header>

  <main class="container">
    <h1>Dashboard</h1>
    <p>Sesión iniciada correctamente.</p>
  </main>
</body>
</html>
