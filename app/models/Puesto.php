<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Puesto para RRHH_TEC
 * CRUD de puestos del organigrama.
 */
class Puesto
{
    /**
     * Campos permitidos para UPDATE.
     */
    private const ALLOWED_FIELDS = [
        'id_area',
        'nombre_puesto',
        'nivel',
        'salario_base',
        'descripcion',
    ];

    /**
     * Valores válidos del enum nivel.
     */
    private const NIVELES_VALIDOS = [
        'OPERATIVO',
        'SUPERVISOR',
        'GERENCIAL',
        'DIRECTIVO',
    ];

    /**
     * Devuelve un puesto por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        $st = $pdo->prepare("SELECT * FROM puestos WHERE id_puesto = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de puestos con paginado, búsqueda y filtros opcionales.
     *
     * @param int         $limit   Número máximo de registros.
     * @param int         $offset  Desplazamiento.
     * @param string|null $search  Búsqueda por nombre/descripcion.
     * @param int|null    $idArea  Filtrar por área.
     * @param string|null $nivel   Filtrar por nivel (enum).
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?string $search = null,
        ?int $idArea = null,
        ?string $nivel = null
    ): array {
        global $pdo;

        $limit = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $q = '%' . trim($search) . '%';
            $where[] = '(p.nombre_puesto LIKE :q OR p.descripcion LIKE :q OR a.nombre_area LIKE :q OR e.nombre LIKE :q)';
            $params[':q'] = $q;
        }

        if ($idArea !== null && $idArea > 0) {
            $where[] = 'p.id_area = :id_area';
            $params[':id_area'] = $idArea;
        }

        if ($nivel !== null && trim($nivel) !== '') {
            $nivel = strtoupper(trim($nivel));
            if (in_array($nivel, self::NIVELES_VALIDOS, true)) {
                $where[] = 'nivel = :nivel';
                $params[':nivel'] = $nivel;
            }
        }

        $sql = "SELECT 
                p.*,
                a.nombre_area,
                e.nombre AS nombre_empresa
            FROM puestos p
            LEFT JOIN areas a      ON a.id_area = p.id_area
            LEFT JOIN empresas e   ON e.id_empresa = a.id_empresa";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY nombre_puesto ASC
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
     * Crea un puesto.
     *
     * @return int ID del puesto creado.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $idArea = (int) ($data['id_area'] ?? 0);
        $nombre = trim((string) ($data['nombre_puesto'] ?? ''));
        $nivel = strtoupper(trim((string) ($data['nivel'] ?? 'OPERATIVO')));
        $salarioBase = $data['salario_base'] ?? null;
        $descripcion = trim((string) ($data['descripcion'] ?? ''));

        if ($idArea <= 0) {
            throw new \InvalidArgumentException("El área es obligatoria.");
        }

        if ($nombre === '') {
            throw new \InvalidArgumentException("El nombre del puesto es obligatorio.");
        }

        if (!in_array($nivel, self::NIVELES_VALIDOS, true)) {
            throw new \InvalidArgumentException("Nivel de puesto inválido.");
        }

        if ($salarioBase !== null && $salarioBase !== '') {
            if (!is_numeric($salarioBase)) {
                throw new \InvalidArgumentException("El salario base debe ser numérico.");
            }
            $salarioBase = number_format((float) $salarioBase, 2, '.', '');
        } else {
            $salarioBase = null;
        }

        if (self::existsNombreEnArea($idArea, $nombre)) {
            throw new \InvalidArgumentException(
                "Ya existe un puesto con ese nombre en el área seleccionada."
            );
        }

        $sql = "INSERT INTO puestos (id_area, nombre_puesto, nivel, salario_base, descripcion)
                VALUES (?, ?, ?, ?, ?)";

        $st = $pdo->prepare($sql);
        $st->execute([
            $idArea,
            $nombre,
            $nivel,
            $salarioBase,
            $descripcion === '' ? null : $descripcion
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualiza un puesto.
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID inválido.");
        }

        $fields = [];
        $params = [];

        // Para validar unicidad (nombre + área)
        $idAreaParaValidar = null;
        $nombreParaValidar = null;

        foreach (self::ALLOWED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            switch ($field) {
                case 'id_area':
                    $value = (int) $value;
                    if ($value <= 0) {
                        throw new \InvalidArgumentException("El área es obligatoria.");
                    }
                    $idAreaParaValidar = $value;
                    break;

                case 'nombre_puesto':
                    $value = trim((string) $value);
                    if ($value === '') {
                        throw new \InvalidArgumentException("El nombre del puesto es obligatorio.");
                    }
                    $nombreParaValidar = $value;
                    break;

                case 'nivel':
                    $value = strtoupper(trim((string) $value));
                    if (!in_array($value, self::NIVELES_VALIDOS, true)) {
                        throw new \InvalidArgumentException("Nivel de puesto inválido.");
                    }
                    break;

                case 'salario_base':
                    if ($value === '' || $value === null) {
                        $value = null;
                    } else {
                        if (!is_numeric($value)) {
                            throw new \InvalidArgumentException("El salario base debe ser numérico.");
                        }
                        $value = number_format((float) $value, 2, '.', '');
                    }
                    break;

                case 'descripcion':
                    $value = trim((string) $value);
                    if ($value === '') {
                        $value = null;
                    }
                    break;
            }

            $fields[] = "$field = ?";
            $params[] = $value;
        }

        if (empty($fields)) {
            // No hay nada que actualizar
            return;
        }

        // Si no vino área o nombre en $data, se toma del puesto actual
        if ($idAreaParaValidar === null || $nombreParaValidar === null) {
            $actual = self::findById($id);
            if (!$actual) {
                throw new \RuntimeException("Puesto no encontrado.");
            }

            if ($idAreaParaValidar === null) {
                $idAreaParaValidar = (int) $actual['id_area'];
            }

            if ($nombreParaValidar === null) {
                $nombreParaValidar = trim((string) $actual['nombre_puesto']);
            }
        }

        // Validar que no exista OTRO puesto con el mismo nombre en el área
        if (self::existsNombreEnArea($idAreaParaValidar, $nombreParaValidar, $id)) {
            throw new \InvalidArgumentException(
                "Ya existe un puesto con ese nombre en el área seleccionada."
            );
        }

        $params[] = $id;

        $sql = "UPDATE puestos SET " . implode(', ', $fields) . " WHERE id_puesto = ?";
        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Elimina un puesto (DELETE físico).
     */
    public static function delete(int $id): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID inválido.");
        }

        $st = $pdo->prepare("DELETE FROM puestos WHERE id_puesto = ?");
        $st->execute([$id]);
    }

    /**
     * Verifica si ya existe un puesto con ese nombre en un área.
     *
     * @param int      $idArea     Área a validar
     * @param string   $nombre     Nombre del puesto (se hace TRIM)
     * @param int|null $excludeId  ID de puesto a excluir (para edición)
     */
    public static function existsNombreEnArea(int $idArea, string $nombre, ?int $excludeId = null): bool
    {
        global $pdo;

        $sql = "SELECT 1 FROM puestos 
                WHERE id_area = ? 
                AND TRIM(nombre_puesto) = TRIM(?)";

        $params = [$idArea, $nombre];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND id_puesto <> ?";
            $params[] = $excludeId;
        }

        $sql .= " LIMIT 1";

        $st = $pdo->prepare($sql);
        $st->execute($params);

        return (bool) $st->fetchColumn();
    }
}
