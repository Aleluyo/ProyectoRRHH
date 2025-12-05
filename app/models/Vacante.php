<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

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

    private const ESTATUS_VALIDOS = [
        'EN_APROBACION',
        'APROBADA',
        'ABIERTA',
        'EN_PROCESO',
        'CERRADA',
    ];

    /* =========================================================
     *  BÁSICOS
     * ======================================================= */

    public static function findById(int $id): ?array
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de vacante inválido.");
        }

        $sql = "
            SELECT
                v.*,
                a.id_empresa,
                a.nombre_area         AS area_nombre,
                e.nombre              AS empresa_nombre,
                p.nombre_puesto       AS puesto_nombre,
                u.nombre              AS ubicacion_nombre
            FROM vacantes v
            LEFT JOIN areas       a ON a.id_area      = v.id_area
            LEFT JOIN empresas    e ON e.id_empresa   = a.id_empresa
            LEFT JOIN puestos     p ON p.id_puesto    = v.id_puesto
            LEFT JOIN ubicaciones u ON u.id_ubicacion = v.id_ubicacion
            WHERE v.id_vacante = ?
            LIMIT 1
        ";

        $st = $pdo->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de vacantes con JOIN a empresa/área/puesto/ubicación.
     * $search busca por empresa, área, puesto, ubicación o estatus.
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?string $search = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): array {
        global $pdo;

        $limit = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $q = '%' . trim($search) . '%';
            $where[] = "(
                  e.nombre        LIKE :q
               OR a.nombre_area   LIKE :q
               OR p.nombre_puesto LIKE :q
               OR u.nombre        LIKE :q
               OR v.estatus       LIKE :q
            )";
            $params[':q'] = $q;
        }

        if ($fechaInicio) {
            $where[] = 'v.fecha_publicacion >= :fecha_inicio';
            $params[':fecha_inicio'] = $fechaInicio;
        }
        if ($fechaFin) {
            $where[] = 'v.fecha_publicacion <= :fecha_fin';
            $params[':fecha_fin'] = $fechaFin;
        }

        $sql = "
            SELECT
                v.*,
                a.nombre_area         AS area_nombre,
                e.nombre              AS empresa_nombre,
                p.nombre_puesto       AS puesto_nombre,
                u.nombre              AS ubicacion_nombre
            FROM vacantes v
            LEFT JOIN areas       a ON a.id_area      = v.id_area
            LEFT JOIN empresas    e ON e.id_empresa   = a.id_empresa
            LEFT JOIN puestos     p ON p.id_puesto    = v.id_puesto
            LEFT JOIN ubicaciones u ON u.id_ubicacion = v.id_ubicacion
        ";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // IMPORTANTE: sin comillas en IS NULL y sin coma al final
        $sql .= "
            ORDER BY
                v.fecha_publicacion IS NULL,
                v.fecha_publicacion DESC,
                v.id_vacante DESC
            LIMIT :limit OFFSET :offset
        ";

        $st = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $st->execute();
        return $st->fetchAll();
    }

    /* =========================================================
     *  CREATE / UPDATE / DELETE (iguales a como ya los tenías,
     *  sólo con validaciones básicas)
     * ======================================================= */

    public static function create(array $data): int
    {
        global $pdo;

        $idArea = self::normalizarId($data['id_area'] ?? null, 'área');
        $idPuesto = self::normalizarId($data['id_puesto'] ?? null, 'puesto');
        $idUbicacion = isset($data['id_ubicacion']) && $data['id_ubicacion'] !== ''
            ? self::normalizarId($data['id_ubicacion'], 'ubicación')
            : null;

        $solicitadaPor = isset($data['solicitada_por']) && $data['solicitada_por'] !== ''
            ? self::normalizarId($data['solicitada_por'], 'usuario solicitante')
            : null;

        $estatusRaw = (string) ($data['estatus'] ?? 'EN_APROBACION');
        $estatus = self::normalizarEstatus($estatusRaw);

        $requisitos = trim((string) ($data['requisitos'] ?? ''));
        $fechaPubRaw = trim((string) ($data['fecha_publicacion'] ?? ''));
        $fechaPub = $fechaPubRaw !== '' ? self::normalizarFecha($fechaPubRaw, 'fecha de publicación') : null;

        $sql = "
            INSERT INTO vacantes
                (id_area, id_puesto, id_ubicacion, solicitada_por, estatus, requisitos, fecha_publicacion, creada_en)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, NOW())
        ";

        $st = $pdo->prepare($sql);
        $st->execute([
            $idArea,
            $idPuesto,
            $idUbicacion,
            $solicitadaPor,
            $estatus,
            $requisitos !== '' ? $requisitos : null,
            $fechaPub,
        ]);

        return (int) $pdo->lastInsertId();
    }

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
                    $value = self::normalizarId($value, 'área');
                    break;

                case 'id_puesto':
                    $value = self::normalizarId($value, 'puesto');
                    break;

                case 'id_ubicacion':
                    $value = $value === '' || $value === null
                        ? null
                        : self::normalizarId($value, 'ubicación');
                    break;

                case 'solicitada_por':
                    $value = $value === '' || $value === null
                        ? null
                        : self::normalizarId($value, 'usuario solicitante');
                    break;

                case 'estatus':
                    $value = self::normalizarEstatus((string) $value);
                    break;

                case 'requisitos':
                    $value = trim((string) $value);
                    if ($value === '') {
                        $value = null;
                    }
                    break;

                case 'fecha_publicacion':
                    $value = trim((string) $value);
                    $value = $value === ''
                        ? null
                        : self::normalizarFecha($value, 'fecha de publicación');
                    break;
            }

            $fields[] = "$field = ?";
            $params[] = $value;
        }

        if (empty($fields)) {
            return;
        }

        $params[] = $id;

        $sql = "UPDATE vacantes SET " . implode(', ', $fields) . " WHERE id_vacante = ?";
        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    public static function delete(int $id): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de vacante inválido.");
        }

        $st = $pdo->prepare("DELETE FROM vacantes WHERE id_vacante = ?");
        $st->execute([$id]);
    }

    /* =========================================================
     *  HELPERS
     * ======================================================= */

    private static function normalizarId($valor, string $labelCampo): int
    {
        $id = (int) $valor;
        if ($id <= 0) {
            throw new \InvalidArgumentException("{$labelCampo} inválido.");
        }
        return $id;
    }

    private static function normalizarEstatus(string $estatus): string
    {
        $e = strtoupper(trim($estatus));
        if (!in_array($e, self::ESTATUS_VALIDOS, true)) {
            throw new \InvalidArgumentException("Estatus de vacante inválido: {$estatus}");
        }
        return $e;
    }

    /**
     * Acepta 'Y-m-d' (lo que usas en el formulario).
     */
    private static function normalizarFecha(string $valor, string $labelCampo): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            throw new \InvalidArgumentException("La {$labelCampo} es obligatoria.");
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $valor)
            ?: \DateTime::createFromFormat('Y-m-d H:i:s', $valor);

        if (!$dt) {
            throw new \InvalidArgumentException("Formato inválido para {$labelCampo} (usa AAAA-MM-DD).");
        }

        return $dt->format('Y-m-d');
    }
}