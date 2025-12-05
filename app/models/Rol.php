<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

class Rol
{
    /**
     * Obtener todos los roles
     */
    public static function all(): array
    {
        $pdo = db();
        $st = $pdo->query("SELECT * FROM roles ORDER BY id_rol ASC");
        return $st->fetchAll();
    }

    /**
     * Obtener permisos de un rol
     */
    public static function getPermissions(int $roleId): array
    {
        $pdo = db();
        $sql = "SELECT p.* 
                FROM permisos_sistema p
                JOIN rol_permiso rp ON p.id_permiso = rp.id_permiso
                WHERE rp.id_rol = ?";
        $st = $pdo->prepare($sql);
        $st->execute([$roleId]);
        return $st->fetchAll();
    }
}
