<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Empresa para RRHH_TEC
 * Admite CRUD completo y validaciones básicas.
 */
class Empresa
{
    private const ALLOWED_FIELDS = [
        'nombre',
        'rfc',
        'correo_contacto',
        'telefono',
        'direccion',
        'activa'
    ];

    /**
     * Devuelve una empresa por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        $st = $pdo->prepare("SELECT * FROM empresas WHERE id_empresa = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de empresas con paginado y búsqueda.
     */
    public static function all(int $limit = 500, int $offset = 0, ?string $search = null, ?bool $onlyActive = null): array
    {
        global $pdo;

        $limit  = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where  = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $q = '%' . trim($search) . '%';
            $where[]   = '(nombre LIKE :q OR rfc LIKE :q OR correo_contacto LIKE :q)';
            $params[':q'] = $q;
        }

        if ($onlyActive !== null) {
            $where[] = 'activa = :activa';
            $params[':activa'] = $onlyActive ? 1 : 0;
        }

        $sql = "SELECT * FROM empresas";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY nombre ASC
                  LIMIT :limit OFFSET :offset";

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
     * Crea una empresa.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $nombre   = trim((string)($data['nombre'] ?? ''));
        $rfc      = trim((string)($data['rfc'] ?? ''));
        $correo   = trim((string)($data['correo_contacto'] ?? ''));
        $telefono = trim((string)($data['telefono'] ?? ''));
        $direccion = trim((string)($data['direccion'] ?? ''));
        $activa   = isset($data['activa']) ? (int)$data['activa'] : 1;

        if ($nombre === '') {
            throw new \InvalidArgumentException("El nombre es obligatorio.");
        }

        // Validación opcional: evitar nombres duplicados
        if (self::existsNombre($nombre)) {
            throw new \InvalidArgumentException("Ya existe una empresa con ese nombre.");
        }

        $sql = "INSERT INTO empresas (nombre, rfc, correo_contacto, telefono, direccion, activa)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $st = $pdo->prepare($sql);
        $st->execute([$nombre, $rfc, $correo, $telefono, $direccion, $activa]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Actualiza una empresa.
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID inválido.");
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

        $sql = "UPDATE empresas SET " . implode(", ", $fields) . " WHERE id_empresa = ?";
        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Activar / Desactivar empresa.
     */
    public static function setActive(int $id, bool $active): void
    {
        global $pdo;

        $st = $pdo->prepare("UPDATE empresas SET activa = ? WHERE id_empresa = ?");
        $st->execute([$active ? 1 : 0, $id]);
    }

    /**
     * Verifica si ya existe una empresa con ese nombre.
     */
    public static function existsNombre(string $nombre): bool
    {
        global $pdo;

        $st = $pdo->prepare("SELECT 1 FROM empresas WHERE nombre = ? LIMIT 1");
        $st->execute([trim($nombre)]);
        return (bool)$st->fetchColumn();
    }
}
