<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Entrevista para RRHH_TEC
 * Maneja las entrevistas de las postulaciones.
 */
class Entrevista
{
    private const ALLOWED_FIELDS = [
        'id_postulacion',
        'programada_para',
        'resultado',
        'notas',
    ];

    private const RESULTADOS_VALIDOS = ['PENDIENTE', 'APROBADO', 'RECHAZADO', 'CANCELADA'];

    /**
     * Devuelve una entrevista por ID (con info básica de la postulación).
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de entrevista inválido.");
        }

        $sql = "
            SELECT
                e.*,
                p.id_postulacion,
                v.id_vacante,
                a.nombre_area,
                pu.nombre_puesto,
                c.nombre AS candidato_nombre,
                CONCAT(
                    'Vacante ', v.id_vacante, ' · ',
                    COALESCE(a.nombre_area, 'Sin área'), ' · ',
                    COALESCE(pu.nombre_puesto, 'Sin puesto'), ' · ',
                    COALESCE(c.nombre, 'Sin candidato')
                ) AS postulacion_resumen
            FROM entrevistas e
            LEFT JOIN postulaciones p ON p.id_postulacion = e.id_postulacion
            LEFT JOIN vacantes v      ON v.id_vacante    = p.id_vacante
            LEFT  JOIN areas   a       ON a.id_area       = v.id_area
            LEFT  JOIN puestos pu      ON pu.id_puesto    = v.id_puesto
            LEFT  JOIN candidatos c    ON c.id_candidato  = p.id_candidato
            WHERE e.id_entrevista = ?
            LIMIT 1
        ";

        $st = $pdo->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de entrevistas (para la pantalla principal).
     * Incluye: resumen de la postulación y orden por ID ASC.
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?string $search = null
    ): array {
        global $pdo;

        $limit = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        // Borrado lógico: filtrar 'CANCELADA'
        $where = ['e.resultado != :resCancelada'];
        $params = [':resCancelada' => 'CANCELADA'];

        if ($search !== null && trim($search) !== '') {
            $search = '%' . trim($search) . '%';
            $where[] = '(c.nombre LIKE :q OR a.nombre_area LIKE :q OR pu.nombre_puesto LIKE :q)';
            $params[':q'] = $search;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "
            SELECT
                e.*,
                p.id_postulacion,
                v.id_vacante,
                a.nombre_area,
                pu.nombre_puesto,
                c.nombre AS candidato_nombre,
                CONCAT(
                    'Vacante ', v.id_vacante, ' · ',
                    COALESCE(a.nombre_area, 'Sin área'), ' · ',
                    COALESCE(pu.nombre_puesto, 'Sin puesto'), ' · ',
                    COALESCE(c.nombre, 'Sin candidato')
                ) AS postulacion_resumen
            FROM entrevistas e
            LEFT JOIN postulaciones p ON p.id_postulacion = e.id_postulacion
            LEFT JOIN vacantes v      ON v.id_vacante    = p.id_vacante
            LEFT  JOIN areas   a       ON a.id_area       = v.id_area
            LEFT  JOIN puestos pu      ON pu.id_puesto    = v.id_puesto
            LEFT  JOIN candidatos c    ON c.id_candidato  = p.id_candidato
            {$whereSql}
            ORDER BY e.id_entrevista ASC
            LIMIT :limit OFFSET :offset
        ";

        $st = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }

        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $st->execute();
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Crea una entrevista.
     * El entrevistador se toma del usuario logueado si existe, o 1 por defecto.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $idPostulacion = self::normalizarId($data['id_postulacion'] ?? null, "postulación");
        $programadaRaw = (string) ($data['programada_para'] ?? '');
        $programada = self::normalizarFechaHora($programadaRaw, "fecha/hora programada");

        $resultadoRaw = (string) ($data['resultado'] ?? 'PENDIENTE');
        $resultado = self::normalizarResultado($resultadoRaw);

        $notas = trim((string) ($data['notas'] ?? ''));

        // Entrevistador: usuario actual o 1 por defecto
        $entrevistador = isset($_SESSION['id_usuario'])
            ? (int) $_SESSION['id_usuario']
            : 1;

        if ($entrevistador <= 0) {
            $entrevistador = 1;
        }

        $sql = "
            INSERT INTO entrevistas
                (id_postulacion, entrevistador, programada_para, resultado, notas)
            VALUES (?, ?, ?, ?, ?)
        ";

        $st = $pdo->prepare($sql);
        $st->execute([
            $idPostulacion,
            $entrevistador,
            $programada,
            $resultado,
            $notas !== '' ? $notas : null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualiza campos de una entrevista.
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de entrevista inválido.");
        }

        $fields = [];
        $params = [];

        foreach (self::ALLOWED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            switch ($field) {
                case 'id_postulacion':
                    $value = self::normalizarId($value, "postulación");
                    break;

                case 'programada_para':
                    $value = self::normalizarFechaHora((string) $value, "fecha/hora programada");
                    break;

                case 'resultado':
                    $value = self::normalizarResultado((string) $value);
                    break;

                case 'notas':
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
            return; // Nada que actualizar
        }

        $params[] = $id;

        $sql = "UPDATE entrevistas SET " . implode(", ", $fields) . " WHERE id_entrevista = ?";
        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Elimina una entrevista (lógico).
     */
    public static function delete(int $id): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de entrevista inválido.");
        }

        // Borrado físico
        $st = $pdo->prepare("DELETE FROM entrevistas WHERE id_entrevista = ?");
        $st->execute([$id]);
    }

    /* ====================== Helpers internos ====================== */

    private static function normalizarId($valor, string $labelCampo): int
    {
        $id = (int) $valor;
        if ($id <= 0) {
            throw new \InvalidArgumentException("{$labelCampo} inválido.");
        }
        return $id;
    }

    /**
     * Normaliza fecha/hora:
     * - Y-m-d H:i:s
     * - Y-m-d H:i
     * - Y-m-d\TH:i (formato de input datetime-local)
     */
    private static function normalizarFechaHora(string $valor, string $labelCampo): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            throw new \InvalidArgumentException("La {$labelCampo} es obligatoria.");
        }

        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $valor)
            ?: \DateTime::createFromFormat('Y-m-d H:i', $valor)
            ?: \DateTime::createFromFormat('Y-m-d\TH:i', $valor);

        if (!$dt) {
            throw new \InvalidArgumentException("Formato inválido para {$labelCampo}.");
        }

        return $dt->format('Y-m-d H:i:s');
    }

    private static function normalizarResultado(string $resultado): string
    {
        $r = strtoupper(trim($resultado));
        if (!in_array($r, self::RESULTADOS_VALIDOS, true)) {
            throw new \InvalidArgumentException("Resultado de entrevista inválido: {$resultado}");
        }
        return $r;
    }
}