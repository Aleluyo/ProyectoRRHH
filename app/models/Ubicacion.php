<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Ubicacion para RRHH_TEC
 * CRUD de sedes/ubicaciones por empresa.
 */
class Ubicacion
{
    private const ALLOWED_FIELDS = [
        'id_empresa',
        'nombre',
        'direccion',
        'ciudad',
        'estado_region',
        'pais',
        'activa'
    ];

    /**
     * Devuelve una ubicación por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        $st = $pdo->prepare("SELECT * FROM ubicaciones WHERE id_ubicacion = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de ubicaciones con paginado, búsqueda y filtros.
     *
     * @param int         $limit      Límite de registros (1–1000)
     * @param int         $offset     Offset
     * @param string|null $search     Búsqueda por nombre/ciudad/estado/país
     * @param bool|null   $onlyActive true=solo activas, false=solo inactivas, null=todas
     * @param int|null    $idEmpresa  Filtrar por empresa (opcional)
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?string $search = null,
        ?bool $onlyActive = null,
        ?int $idEmpresa = null
    ): array {
        global $pdo;

        $limit  = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where  = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $q = '%' . trim($search) . '%';
            $where[]         = '(nombre LIKE :q OR ciudad LIKE :q OR estado_region LIKE :q OR pais LIKE :q)';
            $params[':q']    = $q;
        }

        if ($onlyActive !== null) {
            $where[]            = 'activa = :activa';
            $params[':activa']  = $onlyActive ? 1 : 0;
        }

        if ($idEmpresa !== null && $idEmpresa > 0) {
            $where[]                 = 'id_empresa = :id_empresa';
            $params[':id_empresa']   = $idEmpresa;
        }

        $sql = "SELECT * FROM ubicaciones";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY nombre ASC
                  LIMIT :limit OFFSET :offset";

        $st = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }

        $st->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $st->execute();
        return $st->fetchAll();
    }

    /**
     * Atajo para listar ubicaciones de una empresa concreta.
     */
    public static function allByEmpresa(
        int $idEmpresa,
        int $limit = 500,
        int $offset = 0,
        ?string $search = null,
        ?bool $onlyActive = null
    ): array {
        return self::all($limit, $offset, $search, $onlyActive, $idEmpresa);
    }

    /**
     * Crea una ubicación.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $idEmpresa = (int)($data['id_empresa'] ?? 0);
        $nombre    = trim((string)($data['nombre'] ?? ''));
        $direccion = trim((string)($data['direccion'] ?? ''));
        $ciudad    = trim((string)($data['ciudad'] ?? ''));
        $estado    = trim((string)($data['estado_region'] ?? ''));
        $pais      = trim((string)($data['pais'] ?? ''));
        $activa    = isset($data['activa']) ? (int)$data['activa'] : 1;

        if ($idEmpresa <= 0) {
            throw new \InvalidArgumentException("La empresa es obligatoria.");
        }

        if ($nombre === '') {
            throw new \InvalidArgumentException("El nombre de la sede es obligatorio.");
        }

        // Evitar nombres duplicados de sede dentro de la misma empresa
        if (self::existsNombreEnEmpresa($idEmpresa, $nombre)) {
            throw new \InvalidArgumentException("Ya existe una ubicación con ese nombre para esta empresa.");
        }

        $sql = "INSERT INTO ubicaciones
                    (id_empresa, nombre, direccion, ciudad, estado_region, pais, activa)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?)";

        $st = $pdo->prepare($sql);
        $st->execute([
            $idEmpresa,
            $nombre,
            $direccion,
            $ciudad,
            $estado,
            $pais,
            $activa
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Actualiza una ubicación.
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
            if (!array_key_exists($field, $data)) {
                continue;
            }

            // Trim solo para strings
            $value = $data[$field];
            if (is_string($value)) {
                $value = trim($value);
            }

            $fields[] = "$field = ?";
            $params[] = $value;
        }

        if (empty($fields)) {
            return;
        }

        $params[] = $id;

        $sql = "UPDATE ubicaciones
                SET " . implode(', ', $fields) . "
                WHERE id_ubicacion = ?";

        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Activar / Desactivar ubicación.
     */
    public static function setActive(int $id, bool $active): void
    {
        global $pdo;

        $st = $pdo->prepare("UPDATE ubicaciones SET activa = ? WHERE id_ubicacion = ?");
        $st->execute([$active ? 1 : 0, $id]);
    }

    /**
     * Verifica si ya existe una ubicación con ese nombre dentro de la misma empresa.
     */
    public static function existsNombreEnEmpresa(int $idEmpresa, string $nombre): bool
    {
        global $pdo;

        $st = $pdo->prepare(
            "SELECT 1
             FROM ubicaciones
             WHERE id_empresa = ? AND nombre = ?
             LIMIT 1"
        );
        $st->execute([
            $idEmpresa,
            trim($nombre)
        ]);

        return (bool)$st->fetchColumn();
    }
}
