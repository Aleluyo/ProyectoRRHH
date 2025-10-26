<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

class AuthController
{
    // Puedes definir estas constantes en config o aquí
    private const PASSWORD_COST      = 10;
    private const LOGIN_WINDOW_MIN   = 10;
    private const LOGIN_MAX_ATTEMPTS = 5;
    private const LOGIN_LOCK_MIN     = 10;

    public static function login(string $username, string $password): array
    {
        $username = trim($username);
        $password = trim($password);

        if ($username === '' || $password === '') {
            return ['ok' => false, 'errors' => ['Usuario y contraseña son obligatorios.']];
        }

        // Opcional: antifuerza bruta, solo si tienes la tabla login_attempts
        // self::assertNotLocked($username);

        $user = self::findUserByUsername($username);

        $valid = $user
            && $user['estado'] === 'ACTIVO'
            && password_verify($password, $user['contrasena']);

        if (!$valid) {
            // Constante para evitar timing attacks
            password_verify($password, password_hash('dummy', PASSWORD_BCRYPT));
            // self::registerAttempt($username, false); // Solo si tienes la tabla
            // self::logEvent($user['id_usuario'] ?? null, $username, 'LOGIN_FAILURE');
            return ['ok' => false, 'errors' => ['Credenciales inválidas o usuario inactivo.']];
        }

        // self::registerAttempt($username, true); // Solo si tienes la tabla
        // self::logEvent($user['id_usuario'], $username, 'LOGIN_SUCCESS'); // Solo si tienes la tabla

        // Password rehash si cambias el coste
        if (password_needs_rehash($user['contrasena'], PASSWORD_BCRYPT, ['cost' => self::PASSWORD_COST])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => self::PASSWORD_COST]);
            self::updatePassword($user['id_usuario'], $newHash);
        }

        self::touchLastLogin($user['id_usuario']);

        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);

        $_SESSION['user_id']  = (int)$user['id_usuario'];
        $_SESSION['username'] = (string)$user['username'];
        $_SESSION['rol']      = (int)$user['id_rol'];
        $_SESSION['correo']   = (string)$user['correo'];

        return ['ok' => true];
    }

    public static function logout(): void
    {
        // self::logEvent($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, 'LOGOUT');
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function currentUser(): ?array
    {
        if (!self::isLoggedIn()) return null;
        return [
            'id_usuario' => (int)$_SESSION['user_id'],
            'username'   => (string)$_SESSION['username'],
            'correo'     => (string)$_SESSION['correo'],
            'rol'        => (int)$_SESSION['rol'],
        ];
    }

    /* ================== DB ops internas ================== */

    private static function findUserByUsername(string $username): ?array
    {
        $pdo = db();
        $sql = "SELECT * FROM usuarios WHERE username = ? OR correo = ? LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute([trim($username), trim($username)]);
        $row = $st->fetch();
        return $row ?: null;
    }

    private static function updatePassword(int $userId, string $newHash): void
    {
        $pdo = db();
        $st = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?");
        $st->execute([$newHash, $userId]);
    }

    private static function touchLastLogin(int $userId): void
    {
        $pdo = db();
        $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?")->execute([$userId]);
    }

}
