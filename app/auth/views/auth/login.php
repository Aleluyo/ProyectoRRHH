<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Login RRHH_TEC</title>
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="auth">
  <div class="login-box">
    <h2>Iniciar sesión</h2>
    <?php if (!empty($error)): ?>
      <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/ProyectoRRHH/login">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
      <label>Usuario</label>
      <input type="text" name="username" required>
      <label>Contraseña</label>
      <input type="password" name="password" required>
      <button type="submit">Entrar</button>
    </form>
  </div>
</body>
</html>
