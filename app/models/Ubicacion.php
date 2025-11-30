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

        // ===  Validaciones de longitud ===
        // nombre -> VARCHAR(100)
        if (mb_strlen($nombre) > 100) {
            throw new \InvalidArgumentException("El nombre no debe superar los 100 caracteres.");
        }

        // dirección -> la tabla es TEXT; tú decides el límite lógico (ej. 500)
        if ($direccion !== '' && mb_strlen($direccion) > 500) {
            throw new \InvalidArgumentException("La dirección no debe superar los 500 caracteres.");
        }

        // ciudad / estado_region / pais -> VARCHAR(80)
        if ($ciudad !== '' && mb_strlen($ciudad) > 80) {
            throw new \InvalidArgumentException("La ciudad no debe superar los 80 caracteres.");
        }

        if ($estado !== '' && mb_strlen($estado) > 80) {
            throw new \InvalidArgumentException("El estado no debe superar los 80 caracteres.");
        }

        if ($pais !== '' && mb_strlen($pais) > 80) {
            throw new \InvalidArgumentException("El país no debe superar los 80 caracteres.");
        }

        // asegurar que activa sea 0 o 1
        $activa = in_array($activa, [0, 1], true) ? $activa : 1;

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
     * Verifica si ya existe una ubicación con un nombre "equivalente"
     * dentro de la misma empresa.
     *
     * Equivalente = mismo texto ignorando:
     * - mayúsculas/minúsculas
     * - cantidad de espacios (internos, inicio/fin)
     *
     * Ej: "Sede master" == "  SEDE    MASTER  "
     */
    public static function existsNombreEnEmpresa(int $idEmpresa, string $nombre, ?int $excludeId = null): bool
    {
        global $pdo;

        $nombreNormalizado = self::normalizarNombre($nombre);

        // Traemos todos los nombres de esa empresa (excluyendo opcionalmente un id)
        $sql = "SELECT id_ubicacion, nombre
                FROM ubicaciones
                WHERE id_empresa = :id_empresa";
        $params = [':id_empresa' => $idEmpresa];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND id_ubicacion <> :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $st = $pdo->prepare($sql);
        $st->execute($params);

        while ($row = $st->fetch(\PDO::FETCH_ASSOC)) {
            $existente = (string)($row['nombre'] ?? '');
            $existenteNormalizado = self::normalizarNombre($existente);

            if ($existenteNormalizado === $nombreNormalizado) {
                return true; // ya existe "algún" nombre equivalente
            }
        }

        return false;
    }


        /**
     * Normaliza el nombre para comparación:
     * - trim (inicio/fin)
     * - colapsa múltiples espacios internos a uno solo
     * - pasa a minúsculas para comparación case-insensitive
     */
    private static function normalizarNombre(string $nombre): string
    {
        // quitar espacios inicio/fin
        $nombre = trim($nombre);

        // reemplazar múltiples espacios (incluye tabs, etc.) por un solo espacio
        $nombre = preg_replace('/\s+/u', ' ', $nombre);

        // minúsculas para comparar sin importar mayúsculas/minúsculas
        return mb_strtolower($nombre, 'UTF-8');
    }


}
