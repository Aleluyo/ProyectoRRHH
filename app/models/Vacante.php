<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Vacante para RRHH_TEC
 * Maneja la requisición y administración de vacantes.
 */
class Vacante
{
    private const ALLOWED_FIELDS = [
        'id_area',
        'id_puesto',
        'id_ubicacion',
        'solicitada_por',
        'estatus',
        'requisitos',
        'fecha_publicacion',
    ];

    /**
     * Devuelve una vacante por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de vacante inválido.");
        }

        $st = $pdo->prepare("SELECT * FROM vacantes WHERE id_vacante = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de vacantes con paginado, búsqueda por texto y filtro por estatus.
     * $search busca en requisitos (puedes ampliar a más campos si quieres).
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?string $search = null,
        ?string $estatus = null
    ): array {
        global $pdo;

        $limit  = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where  = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $q = '%' . trim($search) . '%';
            $where[]      = '(requisitos LIKE :q)';
            $params[':q'] = $q;
        }

        if ($estatus !== null && trim($estatus) !== '') {
            $where[]           = 'estatus = :estatus';
            $params[':estatus'] = trim($estatus);
        }

        $sql = "SELECT * FROM vacantes";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY creada_en DESC
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
     * Crea una vacante (requisición).
     */
    public static function create(array $data): int
    {
        global $pdo;

        $idArea        = self::normalizarId($data['id_area'] ?? null, "área");
        $idPuesto      = self::normalizarId($data['id_puesto'] ?? null, "puesto");
        $idUbicacion   = self::normalizarId($data['id_ubicacion'] ?? null, "ubicación");
        $solicitadaPor = self::normalizarId($data['solicitada_por'] ?? null, "usuario solicitante");

        $estatus = trim((string)($data['estatus'] ?? ''));
        if ($estatus === '') {
            throw new \InvalidArgumentException("El estatus de la vacante es obligatorio.");
        }

        $requisitos        = trim((string)($data['requisitos'] ?? ''));
        $fechaPublicacion  = self::normalizarFecha($data['fecha_publicacion'] ?? null);

        $sql = "INSERT INTO vacantes 
                    (id_area, id_puesto, id_ubicacion, solicitada_por,
                     estatus, requisitos, fecha_publicacion)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $st = $pdo->prepare($sql);
        $st->execute([
            $idArea,
            $idPuesto,
            $idUbicacion,
            $solicitadaPor,
            $estatus,
            $requisitos !== '' ? $requisitos : null,
            $fechaPublicacion,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Actualiza campos de una vacante.
     * Solo actualiza los campos definidos en ALLOWED_FIELDS.
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de vacante inválido.");
        }

        $fields = [];
        $params = [];

        foreach (self::ALLOWED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            switch ($field) {
                case 'id_area':
                    $value = self::normalizarId($value, "área");
                    break;

                case 'id_puesto':
                    $value = self::normalizarId($value, "puesto");
                    break;

                case 'id_ubicacion':
                    $value = self::normalizarId($value, "ubicación");
                    break;

                case 'solicitada_por':
                    $value = self::normalizarId($value, "usuario solicitante");
                    break;

                case 'estatus':
                    $value = trim((string)$value);
                    if ($value === '') {
                        throw new \InvalidArgumentException("El estatus de la vacante es obligatorio.");
                    }
                    break;

                case 'requisitos':
                    $value = trim((string)$value);
                    if ($value === '') {
                        $value = null;
                    }
                    break;

                case 'fecha_publicacion':
                    $value = self::normalizarFecha($value);
                    break;
            }

            $fields[] = "$field = ?";
            $params[] = $value;
        }

        if (empty($fields)) {
            return; // Nada que actualizar
        }

        $params[] = $id;

        $sql = "UPDATE vacantes SET " . implode(", ", $fields) . " WHERE id_vacante = ?";
        $st  = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Cambia solo el estatus de la vacante.
     */
    public static function updateStatus(int $id, string $estatus): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de vacante inválido.");
        }

        $estatus = trim($estatus);
        if ($estatus === '') {
            throw new \InvalidArgumentException("El estatus de la vacante es obligatorio.");
        }

        $st = $pdo->prepare("UPDATE vacantes SET estatus = ? WHERE id_vacante = ?");
        $st->execute([$estatus, $id]);
    }

    /**
     * Elimina una vacante.
     * OJO: valida solo por ID, no revisa dependencias (postulaciones, etc.).
     */
    public static function delete(int $id): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de vacante inválido.");
        }

        $st = $pdo->prepare("DELETE FROM vacantes WHERE id_vacante = ?");
        $st->execute([$id]);
    }

    /* ====================== Helpers internos ====================== */

    private static function normalizarId($valor, string $labelCampo): int
    {
        $id = (int)$valor;
        if ($id <= 0) {
            throw new \InvalidArgumentException("{$labelCampo} inválida.");
        }
        return $id;
    }

    /**
     * Normaliza una fecha (Y-m-d). Acepta null o string.
     */
    private static function normalizarFecha($valor): ?string
    {
        if ($valor === null || trim((string)$valor) === '') {
            return null;
        }

        $valor = trim((string)$valor);

        $fecha = \DateTime::createFromFormat('Y-m-d', $valor)
              ?: \DateTime::createFromFormat('d/m/Y', $valor);

        if (!$fecha) {
            throw new \InvalidArgumentException("Formato de fecha inválido, usa Y-m-d o d/m/Y.");
        }

        return $fecha->format('Y-m-d');
    }
}