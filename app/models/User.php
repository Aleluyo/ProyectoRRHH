<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo User para RRHH_TEC
 * Admite: búsqueda, alta, edición, cambio de password y activación de usuarios.
 */
class User
{
    private const ALLOWED_FIELDS = [
        'username', 'first_name', 'last_name', 'role', 'area', 'puesto', 'ciudad', 'is_active'
    ];

    /**
     * Devuelve los datos de un usuario por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;
        $st = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /**
     * Devuelve el usuario por username (para login).
     */
    public static function findByUsername(string $username): ?array
    {
        global $pdo;
        $st = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $st->execute([trim($username)]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /**
     * Verifica si existe un username (opcionalmente excluyendo un ID).
     */
    public static function usernameExists(string $username, ?int $excludeId = null): bool
    {
        global $pdo;
        $sql = "SELECT 1 FROM users WHERE username = ?";
        $params = [$username];
        if ($excludeId) {
            $sql .= " AND id <> ?";
            $params[] = $excludeId;
        }
        $sql .= " LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return (bool)$st->fetchColumn();
    }

    /**
     * Lista de usuarios, con paginado y búsqueda básica.
     */
    public static function all(int $limit = 100, int $offset = 0, ?string $search = null): array
    {
        global $pdo;
        $limit = max(1, min($limit, 500));
        $offset = max(0, $offset);
        if ($search !== null && $search !== '') {
            $q = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($search)) . '%';
            $sql = "SELECT * FROM users
                    WHERE username LIKE :q OR first_name LIKE :q OR last_name LIKE :q
                       OR area LIKE :q OR puesto LIKE :q OR ciudad LIKE :q
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset";
            $st = $pdo->prepare($sql);
            $st->bindValue(':q', $q, \PDO::PARAM_STR);
            $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $st->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $st->execute();
        } else {
            $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $st = $pdo->prepare($sql);
            $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $st->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $st->execute();
        }
        return $st->fetchAll();
    }

    /**
     * Crea un usuario nuevo.
     */
    public static function create(array $data): int
    {
        global $pdo;
        $username   = trim((string)($data['username']   ?? ''));
        $first_name = trim((string)($data['first_name'] ?? ''));
        $last_name  = trim((string)($data['last_name']  ?? ''));
        $role       = trim((string)($data['role']       ?? ''));
        $area       = trim((string)($data['area']       ?? ''));
        $puesto     = trim((string)($data['puesto']     ?? ''));
        $ciudad     = trim((string)($data['ciudad']     ?? ''));
        $is_active  = (int)($data['is_active'] ?? 1);
        $password   = (string)($data['password'] ?? '');

        if ($username === '' || $first_name === '' || $last_name === '' || $password === '') {
            throw new \InvalidArgumentException('Faltan campos obligatorios.');
        }
        if (self::usernameExists($username)) {
            throw new \InvalidArgumentException('El nombre de usuario ya existe.');
        }
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => defined('PASSWORD_COST') ? PASSWORD_COST : 10]);

        $sql = "INSERT INTO users
                (username, password, first_name, last_name, role, area, puesto, ciudad, is_active)
                VALUES (?,?,?,?,?,?,?,?,?)";
        $st = $pdo->prepare($sql);
        $st->execute([$username, $hash, $first_name, $last_name, $role, $area, $puesto, $ciudad, $is_active]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Edita campos permitidos de usuario.
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID inválido.');
        }
        $fields = [];
        $params = [];
        foreach (self::ALLOWED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) continue;
            $fields[] = "$field = ?";
            $params[] = trim((string)$data[$field]);
        }
        if (empty($fields)) return;
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Cambia la contraseña del usuario.
     */
    public static function changePassword(int $id, string $newPassword): void
    {
        global $pdo;
        if ($id <= 0 || $newPassword === '') {
            throw new \InvalidArgumentException('Datos inválidos para cambiar contraseña.');
        }
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => defined('PASSWORD_COST') ? PASSWORD_COST : 10]);
        $st = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $st->execute([$hash, $id]);
    }

    /**
     * Activa/desactiva el usuario.
     */
    public static function setActive(int $id, bool $active): void
    {
        global $pdo;
        $st = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $st->execute([$active ? 1 : 0, $id]);
    }

    /**
     * Marca la fecha de último login.
     */
    public static function touchLastLogin(int $id): void
    {
        global $pdo;
        $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$id]);
    }

    /**
     * Nombre completo helper.
     */
    public static function fullName(array $userRow): string
    {
        $fn = trim((string)($userRow['first_name'] ?? ''));
        $ln = trim((string)($userRow['last_name'] ?? ''));
        return trim($fn . ' ' . $ln);
    }
}
