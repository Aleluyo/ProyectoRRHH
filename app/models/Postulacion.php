<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Postulacion
 * Une candidatos con vacantes y maneja el estado del proceso.
 */
class Postulacion
{
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
     * Lista de postulaciones con joins para mostrar información legible
     * (empresa, área, puesto, candidato).
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?string $search = null
    ): array {
        global $pdo;

        $limit  = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where  = ['1 = 1'];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $search = '%' . trim($search) . '%';
            $where[] = '(c.nombre LIKE :q
                      OR a.nombre_area LIKE :q
                      OR pu.nombre_puesto LIKE :q
                      OR e.nombre LIKE :q
                      OR p.estado LIKE :q)';
            $params[':q'] = $search;
        }

        $sql = "
            SELECT
                p.id_postulacion,
                p.id_vacante,
                p.id_candidato,
                p.estado,
                p.comentarios,
                p.aplicada_en,

                v.id_area,
                v.id_puesto,

                a.nombre_area,
                e.nombre       AS empresa_nombre,
                pu.nombre_puesto,

                c.nombre       AS candidato_nombre
            FROM postulaciones p
            INNER JOIN vacantes   v  ON v.id_vacante  = p.id_vacante
            INNER JOIN areas      a  ON a.id_area     = v.id_area
            INNER JOIN empresas   e  ON e.id_empresa  = a.id_empresa
            INNER JOIN puestos    pu ON pu.id_puesto  = v.id_puesto
            INNER JOIN candidatos c  ON c.id_candidato = p.id_candidato
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.aplicada_en DESC, p.id_postulacion DESC
            LIMIT :limit OFFSET :offset
        ";

        $st = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, \PDO::PARAM_STR);
        }

        $st->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $st->execute();
        return $st->fetchAll();
    }

    /**
     * Devuelve una postulación (con joins) por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de postulación inválido.");
        }

        $sql = "
            SELECT
                p.*,
                v.id_area,
                v.id_puesto,
                a.nombre_area,
                e.nombre       AS empresa_nombre,
                pu.nombre_puesto,
                c.nombre       AS candidato_nombre
            FROM postulaciones p
            INNER JOIN vacantes   v  ON v.id_vacante   = p.id_vacante
            INNER JOIN areas      a  ON a.id_area      = v.id_area
            INNER JOIN empresas   e  ON e.id_empresa   = a.id_empresa
            INNER JOIN puestos    pu ON pu.id_puesto   = v.id_puesto
            INNER JOIN candidatos c  ON c.id_candidato = p.id_candidato
            WHERE p.id_postulacion = ?
            LIMIT 1
        ";

        $st = $pdo->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Crea una postulación.
     * $data debe traer: id_vacante, id_candidato, estado, comentarios, fecha_aplicacion (Y-m-d).
     */
    public static function create(array $data): int
    {
        global $pdo;

        $idVacante   = self::normalizarId($data['id_vacante']   ?? null, "vacante");
        $idCandidato = self::normalizarId($data['id_candidato'] ?? null, "candidato");

        $estadoRaw = (string)($data['estado'] ?? 'POSTULADO');
        $estado    = self::normalizarEstado($estadoRaw);

        $comentarios = trim((string)($data['comentarios'] ?? ''));

        $fechaRaw   = trim((string)($data['fecha_aplicacion'] ?? ''));
        $aplicadaEn = self::normalizarFecha($fechaRaw);

        $sql = "
            INSERT INTO postulaciones
                (id_vacante, id_candidato, estado, comentarios, aplicada_en)
            VALUES (?, ?, ?, ?, ?)
        ";

        $st = $pdo->prepare($sql);
        $st->execute([
            $idVacante,
            $idCandidato,
            $estado,
            $comentarios !== '' ? $comentarios : null,
            $aplicadaEn,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Actualiza campos de una postulación.
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de postulación inválido.");
        }

        $fields = [];
        $params = [];

        if (array_key_exists('id_vacante', $data)) {
            $fields[] = 'id_vacante = ?';
            $params[] = self::normalizarId($data['id_vacante'], "vacante");
        }

        if (array_key_exists('id_candidato', $data)) {
            $fields[] = 'id_candidato = ?';
            $params[] = self::normalizarId($data['id_candidato'], "candidato");
        }

        if (array_key_exists('estado', $data)) {
            $fields[] = 'estado = ?';
            $params[] = self::normalizarEstado((string)$data['estado']);
        }

        if (array_key_exists('comentarios', $data)) {
            $comentarios = trim((string)$data['comentarios']);
            $fields[]    = 'comentarios = ?';
            $params[]    = $comentarios !== '' ? $comentarios : null;
        }

        if (array_key_exists('fecha_aplicacion', $data)) {
            $fields[] = 'aplicada_en = ?';
            $params[] = self::normalizarFecha((string)$data['fecha_aplicacion']);
        }

        if (empty($fields)) {
            return; // Nada que actualizar
        }

        $params[] = $id;

        $sql = "UPDATE postulaciones SET " . implode(', ', $fields) . " WHERE id_postulacion = ?";
        $st  = $pdo->prepare($sql);
        $st->execute($params);
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

    /**
     * Recibe una fecha (Y-m-d o Y-m-d H:i:s) o cadena vacía.
     * Si viene vacía, usa la fecha/hora actual.
     */
    private static function normalizarFecha(string $valor): string
    {
        $valor = trim($valor);

        if ($valor === '') {
            return date('Y-m-d H:i:s');
        }

        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $valor)
           ?: \DateTime::createFromFormat('Y-m-d', $valor);

        if (!$dt) {
            throw new \InvalidArgumentException("Fecha de postulación inválida.");
        }

        return $dt->format('Y-m-d H:i:s');
    }
}