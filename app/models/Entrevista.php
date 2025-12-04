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
        'entrevistador',
        'programada_para',
        'resultado',
        'notas',
    ];

    private const RESULTADOS_VALIDOS = ['PENDIENTE', 'APROBADO', 'RECHAZADO'];

    /**
     * Devuelve una entrevista por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de entrevista inválido.");
        }

        $st = $pdo->prepare("SELECT * FROM entrevistas WHERE id_entrevista = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista entrevistas de una postulación.
     */
    public static function byPostulacion(
        int $idPostulacion,
        int $limit = 500,
        int $offset = 0
    ): array {
        global $pdo;

        if ($idPostulacion <= 0) {
            throw new \InvalidArgumentException("ID de postulación inválido.");
        }

        $limit  = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $sql = "SELECT *
                FROM entrevistas
                WHERE id_postulacion = :idPostulacion
                ORDER BY programada_para DESC
                LIMIT :limit OFFSET :offset";

        $st = $pdo->prepare($sql);
        $st->bindValue(':idPostulacion', $idPostulacion, \PDO::PARAM_INT);
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $st->execute();
        return $st->fetchAll();
    }

    /**
     * Crea una entrevista.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $idPostulacion = self::normalizarId($data['id_postulacion'] ?? null, "postulación");
        $entrevistador = self::normalizarId($data['entrevistador'] ?? null, "entrevistador");

        $programadaRaw = (string)($data['programada_para'] ?? '');
        $programada    = self::normalizarFechaHora($programadaRaw, "fecha/hora programada");

        $resultadoRaw  = (string)($data['resultado'] ?? 'PENDIENTE');
        $resultado     = self::normalizarResultado($resultadoRaw);

        $notas         = trim((string)($data['notas'] ?? ''));

        $sql = "INSERT INTO entrevistas
                    (id_postulacion, entrevistador, programada_para, resultado, notas)
                VALUES (?, ?, ?, ?, ?)";

        $st = $pdo->prepare($sql);
        $st->execute([
            $idPostulacion,
            $entrevistador,
            $programada,
            $resultado,
            $notas !== '' ? $notas : null,
        ]);

        return (int)$pdo->lastInsertId();
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

                case 'entrevistador':
                    $value = self::normalizarId($value, "entrevistador");
                    break;

                case 'programada_para':
                    $value = self::normalizarFechaHora((string)$value, "fecha/hora programada");
                    break;

                case 'resultado':
                    $value = self::normalizarResultado((string)$value);
                    break;

                case 'notas':
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

        $sql = "UPDATE entrevistas SET " . implode(", ", $fields) . " WHERE id_entrevista = ?";
        $st  = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Elimina una entrevista.
     */
    public static function delete(int $id): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de entrevista inválido.");
        }

        $st = $pdo->prepare("DELETE FROM entrevistas WHERE id_entrevista = ?");
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

    /**
     * Normaliza fecha/hora (acepta 'Y-m-d H:i:s' o 'Y-m-d H:i').
     */
    private static function normalizarFechaHora(string $valor, string $labelCampo): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            throw new \InvalidArgumentException("La {$labelCampo} es obligatoria.");
        }

        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $valor)
           ?: \DateTime::createFromFormat('Y-m-d H:i', $valor);

        if (!$dt) {
            throw new \InvalidArgumentException("Formato inválido para {$labelCampo} (usa Y-m-d H:i o Y-m-d H:i:s).");
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