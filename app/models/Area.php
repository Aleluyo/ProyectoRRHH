<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Area para RRHH_TEC
 * Admite: búsqueda, alta, edición, activación y manejo básico de jerarquías.
 */
class Area
{
    private const ALLOWED_FIELDS = [
        'id_empresa', 'id_area_padre', 'nombre_area', 'descripcion', 'activa'
    ];

    /**
     * Obtiene un área por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;
        $st = $pdo->prepare("SELECT * FROM areas WHERE id_area = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /**
     * Verifica si existe un nombre de área dentro de una empresa
     * (opcionalmente excluyendo un ID).
     */
    public static function nameExistsInEmpresa(int $idEmpresa, string $nombreArea, ?int $excludeId = null): bool
    {
        global $pdo;
        $sql = "SELECT 1 FROM areas WHERE id_empresa = ? AND nombre_area = ?";
        $params = [$idEmpresa, trim($nombreArea)];

        if ($excludeId) {
            $sql .= " AND id_area <> ?";
            $params[] = $excludeId;
        }

        $sql .= " LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return (bool)$st->fetchColumn();
    }

    /**
     * Lista de áreas, con paginado, búsqueda y filtro por empresa/activo.
     */
    public static function all(
        int $limit = 100,
        int $offset = 0,
        ?string $search = null,
        ?int $idEmpresa = null,
        ?bool $onlyActive = null
    ): array {
        global $pdo;

        $limit  = max(1, min($limit, 500));
        $offset = max(0, $offset);

        $where  = [];
        $params = [];

        if ($idEmpresa !== null) {
            $where[]   = 'a.id_empresa = :id_empresa';
            $params[':id_empresa'] = $idEmpresa;
        }

        if ($onlyActive !== null) {
            $where[]   = 'a.activa = :activa';
            $params[':activa'] = $onlyActive ? 1 : 0;
        }

        if ($search !== null && $search !== '') {
            $q = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($search)) . '%';
            $where[]   = '(a.nombre_area LIKE :q OR a.descripcion LIKE :q)';
            $params[':q'] = $q;
        }

        $sql = "SELECT a.*, e.nombre AS empresa_nombre, ap.nombre_area AS area_padre_nombre
                FROM areas a
                INNER JOIN empresas e ON a.id_empresa = e.id_empresa
                LEFT JOIN areas ap ON a.id_area_padre = ap.id_area";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY e.nombre, a.nombre_area
                  LIMIT :limit OFFSET :offset";

        $st = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $st->execute();
        return $st->fetchAll();
    }

    /**
     * Crea un área nueva.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $idEmpresa    = (int)($data['id_empresa']    ?? 0);
        $idAreaPadre  = $data['id_area_padre'] ?? null;
        $nombreArea   = trim((string)($data['nombre_area'] ?? ''));
        $descripcion  = trim((string)($data['descripcion'] ?? ''));
        $activa       = isset($data['activa']) ? (int)$data['activa'] : 1;

        if ($idEmpresa <= 0 || $nombreArea === '') {
            throw new \InvalidArgumentException('Faltan campos obligatorios (empresa y nombre de área).');
        }

        // Normalizar id_area_padre
        $idAreaPadre = $idAreaPadre !== null && $idAreaPadre !== ''
            ? (int)$idAreaPadre
            : null;

        // (Opcional) validar que el área padre exista
        if ($idAreaPadre !== null && !self::findById($idAreaPadre)) {
            throw new \InvalidArgumentException('El área padre no existe.');
        }

        // Validar nombre único dentro de la empresa
        if (self::nameExistsInEmpresa($idEmpresa, $nombreArea)) {
            throw new \InvalidArgumentException('Ya existe un área con ese nombre en la misma empresa.');
        }

        $sql = "INSERT INTO areas
                (id_empresa, id_area_padre, nombre_area, descripcion, activa)
                VALUES (?,?,?,?,?)";
        $st = $pdo->prepare($sql);
        $st->execute([
            $idEmpresa,
            $idAreaPadre,
            $nombreArea,
            $descripcion,
            $activa
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Actualiza campos permitidos de un área.
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException('ID de área inválido.');
        }

        // Si viene nombre_empresa o id_empresa, se valida la unicidad:
        if (isset($data['id_empresa']) || isset($data['nombre_area'])) {
            $idEmpresa   = isset($data['id_empresa']) ? (int)$data['id_empresa'] : null;
            $nombreArea  = isset($data['nombre_area']) ? trim((string)$data['nombre_area']) : null;

            if ($idEmpresa !== null && $nombreArea !== null &&
                self::nameExistsInEmpresa($idEmpresa, $nombreArea, $id)) {
                throw new \InvalidArgumentException('Ya existe un área con ese nombre en la misma empresa.');
            }
        }

        $fields = [];
        $params = [];

        foreach (self::ALLOWED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) continue;

            if ($field === 'id_area_padre') {
                $val = $data[$field];
                $val = $val !== null && $val !== '' ? (int)$val : null;

                // Evitar que un área sea padre de sí misma
                if ($val === $id) {
                    throw new \InvalidArgumentException('Un área no puede ser padre de sí misma.');
                }
                $fields[] = "$field = ?";
                $params[] = $val;
                continue;
            }

            if ($field === 'activa') {
                $fields[] = "$field = ?";
                $params[] = (int)$data[$field];
                continue;
            }

            $fields[] = "$field = ?";
            $params[] = is_string($data[$field]) ? trim((string)$data[$field]) : $data[$field];
        }

        if (empty($fields)) return;

        $params[] = $id;
        $sql = "UPDATE areas SET " . implode(', ', $fields) . " WHERE id_area = ?";
        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Activa/desactiva un área.
     */
    public static function setActive(int $id, bool $active): void
    {
        global $pdo;
        $st = $pdo->prepare("UPDATE areas SET activa = ? WHERE id_area = ?");
        $st->execute([$active ? 1 : 0, $id]);
    }

    /**
     * Áreas de una empresa (para combos, organigrama, etc.).
     */
    public static function getByEmpresa(int $idEmpresa, bool $onlyActive = true): array
    {
        global $pdo;
        $sql = "SELECT * FROM areas WHERE id_empresa = ?";

        $params = [$idEmpresa];

        if ($onlyActive) {
            $sql .= " AND activa = 1";
        }

        $sql .= " ORDER BY nombre_area";

        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /**
     * Áreas raíz (sin padre) de una empresa.
     */
    public static function getRootByEmpresa(int $idEmpresa, bool $onlyActive = true): array
    {
        global $pdo;
        $sql = "SELECT * FROM areas
                WHERE id_empresa = ? AND id_area_padre IS NULL";

        $params = [$idEmpresa];

        if ($onlyActive) {
            $sql .= " AND activa = 1";
        }

        $sql .= " ORDER BY nombre_area";

        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /**
     * Hijos directos de un área (para árbol jerárquico).
     */
    public static function getChildren(int $idArea, bool $onlyActive = true): array
    {
        global $pdo;
        $sql = "SELECT * FROM areas WHERE id_area_padre = ?";

        $params = [$idArea];

        if ($onlyActive) {
            $sql .= " AND activa = 1";
        }

        $sql .= " ORDER BY nombre_area";

        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

     /**
     * Lista de áreas con el nombre de su empresa.
     */
    public static function allWithEmpresa(int $limit = 1000, int $offset = 0): array
    {
        global $pdo;

        $limit  = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $sql = "SELECT
                    a.*,
                    e.nombre AS nombre_empresa
                FROM areas a
                LEFT JOIN empresas e ON e.id_empresa = a.id_empresa
                ORDER BY e.nombre ASC, a.nombre_area ASC
                LIMIT :limit OFFSET :offset";

        $st = $pdo->prepare($sql);
        $st->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll();
    }
}
