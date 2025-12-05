<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

class Usuario
{
    /**
     * Lista de usuarios
     */
    public static function all(int $limit = 100, int $offset = 0, ?string $search = null): array
    {
        $pdo = db();
        $limit = max(1, min($limit, 500));
        $offset = max(0, $offset);

        $sql = "SELECT u.*, r.nombre_rol 
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id_rol";
        
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= " WHERE u.username LIKE :q OR u.correo LIKE :q";
            $params[':q'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY u.creado_en DESC LIMIT :limit OFFSET :offset";

        $st = $pdo->prepare($sql);
        
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);
        
        $st->execute();
        return $st->fetchAll();
    }

    /**
     * Crear usuario
     */
    public static function create(array $data): int
    {
        $pdo = db();

        $username = trim($data['username'] ?? '');
        $correo   = trim($data['correo'] ?? '');
        $password = $data['password'] ?? '';
        $id_rol   = (int)($data['id_rol'] ?? 2); // 2 = Usuario por defecto
        $estado   = 'ACTIVO';

        if ($username === '' || $correo === '' || $password === '') {
            throw new \InvalidArgumentException("Faltan datos obligatorios.");
        }

        // Verificar duplicados
        $st = $pdo->prepare("SELECT 1 FROM usuarios WHERE username = ? OR correo = ?");
        $st->execute([$username, $correo]);
        if ($st->fetchColumn()) {
            throw new \InvalidArgumentException("El usuario o correo ya existe.");
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

        $sql = "INSERT INTO usuarios (username, correo, contrasena, id_rol, estado) VALUES (?, ?, ?, ?, ?)";
        $st = $pdo->prepare($sql);
        $st->execute([$username, $correo, $hash, $id_rol, $estado]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Cambiar contraseña
     */
    public static function changePassword(int $id, string $newPassword): void
    {
        $pdo = db();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        $st = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?");
        $st->execute([$hash, $id]);
    }

    /**
     * Activar/Desactivar
     */
    public static function setEstado(int $id, string $estado): void
    {
        $pdo = db();
        $st = $pdo->prepare("UPDATE usuarios SET estado = ? WHERE id_usuario = ?");
        $st->execute([$estado, $id]);
    }

    /**
     * Generar contraseña aleatoria
     */
    public static function generatePassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*';
        $password = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        return $password;
    }
}
