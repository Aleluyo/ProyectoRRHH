<?php
declare(strict_types=1);

require_once CONF_PATH . '/db.php';
require_once APP_PATH  . '/models/User.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Csrf.php';
require_once CONF_PATH . '/paths.php';

final class AuthController {

  public static function showLogin(): void {
    requireGuest();
    view('auth/login.php', ['csrf' => Csrf::token()]);
  }

  public static function doLogin(): void {
    requireGuest();
    if (!Csrf::verify($_POST['_csrf'] ?? null)) {
      echo "Token CSRF inválido"; return;
    }
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (!$user || !$pass) {
      view('auth/login.php', ['error' => 'Campos requeridos', 'csrf' => Csrf::token()]);
      return;
    }

    $pdo = db();
    $u = User::findByUsername($pdo, $user);
    if (!$u || !password_verify($pass, $u->hash)) {
      view('auth/login.php', ['error' => 'Credenciales inválidas', 'csrf' => Csrf::token()]);
      return;
    }
    if ($u->estado !== 'ACTIVO') {
      view('auth/login.php', ['error' => 'Usuario inactivo', 'csrf' => Csrf::token()]);
      return;
    }

    Session::set('user', ['id' => $u->id, 'username' => $u->username, 'rol' => $u->idRol]);
    $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id")
        ->execute([':id' => $u->id]);
    redirect('/dashboard');
  }

  public static function logout(): void {
    Session::destroy();
    redirect('/login');
  }
}
