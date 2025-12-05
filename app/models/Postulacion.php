<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Postulacion para RRHH_TEC
 * Une candidatos con vacantes y maneja el estado del proceso.
 */
class Postulacion
{
    private const ALLOWED_FIELDS = [
        'id_vacante',
        'id_candidato',
        'estado',
        'comentarios',
    ];

    private const ESTADOS_VALIDOS = [
        'POSTULADO',
        'SCREENING',
        'ENTREVISTA',
        'PRUEBA',
        'OFERTA',
        'CONTRATADO',
        'RECHAZADO',
    ];

    /**
     * Devuelve una postulación por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de postulación inválido.");
        }

        $sql = "SELECT p.*, 
                       c.nombre AS candidato_nombre,
                       c.correo AS candidato_correo,
                       v.id_vacante,
                       a.nombre_area,
                       pu.nombre_puesto
                FROM postulaciones p
                LEFT JOIN candidatos c ON c.id_candidato = p.id_candidato
                LEFT JOIN vacantes   v  ON v.id_vacante   = p.id_vacante
                LEFT JOIN areas      a  ON a.id_area      = v.id_area
                LEFT JOIN puestos    pu ON pu.id_puesto   = v.id_puesto
                WHERE p.id_postulacion = ?
                LIMIT 1";

        $st = $pdo->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de postulaciones de una vacante, con paginado y filtro por estado.
     */
    public static function byVacante(
        int $idVacante,
        int $limit = 500,
        int $offset = 0,
        ?string $estado = null
    ): array {
        global $pdo;

        if ($idVacante <= 0) {
            throw new \InvalidArgumentException("ID de vacante inválido.");
        }

        $limit  = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where  = ['p.id_vacante = :idVacante'];
        $params = [':idVacante' => $idVacante];

        if ($estado !== null && trim($estado) !== '') {
            $estado = self::normalizarEstado($estado);
            $where[]           = 'p.estado = :estado';
            $params[':estado'] = $estado;
        }

        $sql = "SELECT p.*,
                       c.nombre AS candidato_nombre,
                       c.correo AS candidato_correo
                FROM postulaciones p
                LEFT JOIN candidatos c ON c.id_candidato = p.id_candidato
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.aplicada_en DESC
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
     * Lista de postulaciones para selects de Entrevistas.
     * Devuelve elementos tipo:
     * [
     *   ['id' => 1, 'label' => 'Vacante 1 · Recursos Humanos · Analista de RRHH · Pedro Martínez · ENTREVISTA'],
     *   ...
     * ]
     */
    public static function listaParaEntrevistas(): array
    {
        global $pdo;

        $sql = "
            SELECT 
                p.id_postulacion,
                v.id_vacante,
                c.nombre         AS candidato,
                a.nombre_area    AS area,
                pu.nombre_puesto AS puesto,
                p.estado         AS estado_postulacion
            FROM postulaciones p
            INNER JOIN vacantes   v  ON v.id_vacante   = p.id_vacante
            INNER JOIN candidatos c  ON c.id_candidato = p.id_candidato
            LEFT  JOIN areas      a  ON a.id_area      = v.id_area
            LEFT  JOIN puestos    pu ON pu.id_puesto   = v.id_puesto
            ORDER BY p.id_postulacion ASC
        ";

        $st   = $pdo->query($sql);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];

        foreach ($rows as $row) {
            $partes = [];

            // Vacante
            $partes[] = 'Vacante ' . $row['id_vacante'];

            // Área y puesto (si existen)
            if (!empty($row['area'])) {
                $partes[] = $row['area'];
            }
            if (!empty($row['puesto'])) {
                $partes[] = $row['puesto'];
            }

            // Candidato
            $partes[] = $row['candidato'];

            // Estado
            if (!empty($row['estado_postulacion'])) {
                $partes[] = strtoupper($row['estado_postulacion']);
            }

            $label = implode(' · ', $partes);

            $result[] = [
                'id'    => (int)$row['id_postulacion'],
                'label' => $label,
            ];
        }

        return $result;
    }

    /**
     * Crea una postulación.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $idVacante   = self::normalizarId($data['id_vacante'] ?? null, "vacante");
        $idCandidato = self::normalizarId($data['id_candidato'] ?? null, "candidato");

        $estadoRaw = (string)($data['estado'] ?? 'POSTULADO');
        $estado    = self::normalizarEstado($estadoRaw);

        $comentarios = trim((string)($data['comentarios'] ?? ''));

        $sql = "INSERT INTO postulaciones
                    (id_vacante, id_candidato, estado, comentarios, aplicada_en)
                VALUES (?, ?, ?, ?, NOW())";

        $st = $pdo->prepare($sql);
        $st->execute([
            $idVacante,
            $idCandidato,
            $estado,
            $comentarios !== '' ? $comentarios : null,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Actualiza campos de una postulación (incluyendo estado).
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de postulación inválido.");
        }

        $fields = [];
        $params = [];

        foreach (self::ALLOWED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            switch ($field) {
                case 'id_vacante':
                    $value = self::normalizarId($value, "vacante");
                    break;

                case 'id_candidato':
                    $value = self::normalizarId($value, "candidato");
                    break;

                case 'estado':
                    $value = self::normalizarEstado((string)$value);
                    break;

                case 'comentarios':
                    $value = trim((string)$value);
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

        $sql = "UPDATE postulaciones SET " . implode(", ", $fields) . " WHERE id_postulacion = ?";
        $st  = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Cambia el estado de una postulación (atajo).
     */
    public static function cambiarEstado(int $id, string $estado, ?string $comentarios = null): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de postulación inválido.");
        }

        $estado      = self::normalizarEstado($estado);
        $comentarios = $comentarios !== null ? trim($comentarios) : null;

        $sql = "UPDATE postulaciones
                SET estado = ?, comentarios = ?
                WHERE id_postulacion = ?";

        $st = $pdo->prepare($sql);
        $st->execute([
            $estado,
            $comentarios !== '' ? $comentarios : null,
            $id
        ]);
    }

    /**
     * Elimina una postulación.
     */
    public static function delete(int $id): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de postulación inválido.");
        }

        $st = $pdo->prepare("DELETE FROM postulaciones WHERE id_postulacion = ?");
        $st->execute([$id]);
    }

    /* ====================== Helpers internos ====================== */

    private static function normalizarId($valor, string $labelCampo): int
    {
        $id = (int)$valor;
        if ($id <= 0) {
            throw new \InvalidArgumentException("{$labelCampo} inválido.");
        }
        return $id;
    }

    private static function normalizarEstado(string $estado): string
    {
        $e = strtoupper(trim($estado));
        if (!in_array($e, self::ESTADOS_VALIDOS, true)) {
            throw new \InvalidArgumentException("Estado de postulación inválido: {$estado}");
        }
        return $e;
    }
}