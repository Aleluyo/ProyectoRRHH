<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

// Redirige al dashboard si ya hay sesión activa
if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}

$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sleep(2); // pequeño delay anti-enumeración
    $res = AuthController::login($_POST['username'] ?? '', $_POST['password'] ?? '');
    if ($res['ok']) {
        $to = $_GET['redirect'] ?? url('index.php');
        header('Location: ' . $to);
        exit;
    } else {
        $err = implode("\n", $res['errors']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Iniciar sesión | RRHH</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap, Icons, SweetAlert -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      body {
        min-height: 100vh;
        background: linear-gradient(135deg, #e4e9f7 0%, #f6f8fa 60%, #a6b6d6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', Arial, sans-serif;
      }
      .login-card {
        background: rgba(255,255,255,0.93);
        border-radius: 18px;
        box-shadow: 0 10px 36px 0 #181c202a;
        backdrop-filter: blur(6px);
        border: 1px solid #e5e9f2;
        width: 100%;
        max-width: 350px;
        padding: 36px 30px 28px 30px;
        margin-top: 28px;
      }
      .login-logo {
        width: 70px;
        height: auto;
        margin-bottom: 14px;
        background: none;
        box-shadow: none;
        border-radius: 0;
        display: inline-block;
      }
      .form-control, .input-group-text {
        border-radius: 10px;
      }
      .btn-primary {
        background: linear-gradient(90deg, #647dee 0%, #7f53ac 100%);
        border: none;
        border-radius: 10px;
        transition: background 0.18s;
        font-weight: 600;
      }
      .btn-primary:active, .btn-primary:focus, .btn-primary:hover {
        background: linear-gradient(90deg, #7f53ac 0%, #647dee 100%);
      }
      .login-title {
        font-weight: bold;
        letter-spacing: .02em;
        color: #29345c;
      }
      .login-small {
        color: #7582a7;
        font-size: .93em;
        margin-bottom: 20px;
      }
    </style>
</head>
<body>
    <div>
      <div class="login-card shadow">
        <div class="text-center">
            <!-- LOGO RRHH, ajusta la ruta si lo necesitas -->
            <img src="<?= asset('img/galgo.png') ?>" alt="Logo RRHH" class="login-logo">
            <!-- O usa tu archivo subido:
            <img src="<?= asset('img/03ca6d8e-1b69-4eb6-ad98-f99e50289b57.png') ?>" alt="Logo RRHH" class="login-logo">
            -->
        </div>
        <h1 class="h4 text-center login-title mb-2">Acceso</h1>
        <div class="login-small text-center mb-3">
            Sistema de Recursos Humanos
        </div>
        <form method="POST" novalidate autocomplete="off">
            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                <input type="text" class="form-control" id="username" name="username" placeholder="Usuario" required autofocus>
            </div>
            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-1">Entrar</button>
        </form>
      </div>
    </div>

    <?php if (isset($_GET['expired'])): ?>
    <script>
        Swal.fire({icon:'info', title:'Sesión expirada', text:'Vuelve a iniciar sesión.', timer:2200, showConfirmButton:false});
    </script>
    <?php endif; ?>

    <?php if (isset($_GET['loggedout'])): ?>
    <script>
        Swal.fire({icon:'success', title:'Sesión cerrada', text:'Has salido correctamente.', timer:1800, showConfirmButton:false});
    </script>
    <?php endif; ?>

    <?php if ($err): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: <?= json_encode($err, JSON_UNESCAPED_UNICODE) ?>,
            confirmButtonText: 'Entendido'
        }).then(() => {
            const p = document.getElementById('password');
            if (p) { p.value = ''; p.focus(); }
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
