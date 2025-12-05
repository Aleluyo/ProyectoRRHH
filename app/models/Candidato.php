<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Candidato para RRHH_TEC
 * Maneja el banco de candidatos y sus datos de contacto/CV.
 */
class Candidato
{
    private const ALLOWED_FIELDS = [
        'nombre',
        'correo',
        'telefono',
        'cv',
        'fuente',
    ];

    /**
     * Devuelve un candidato por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de candidato inválido.");
        }

        $st = $pdo->prepare("SELECT * FROM candidatos WHERE id_candidato = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de candidatos con paginado, búsqueda y filtro por fuente.
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?string $search = null,
        ?string $fuente = null
    ): array {
        global $pdo;

        $limit = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $q = '%' . trim($search) . '%';
            $where[] = '(nombre LIKE :q OR correo LIKE :q OR telefono LIKE :q)';
            $params[':q'] = $q;
        }

        // Logical Delete Filter
        $where[] = "activo = 1";

        if ($fuente !== null && trim($fuente) !== '') {
            $where[] = 'fuente = :fuente';
            $params[':fuente'] = trim($fuente);
        }

        $sql = "SELECT * FROM candidatos";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY id_candidato DESC
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
     * Crea un candidato.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $nombre = trim((string) ($data['nombre'] ?? ''));
        $correo = self::normalizarCorreo($data['correo'] ?? null);
        $telefono = self::normalizarTelefono($data['telefono'] ?? null);
        $cv = trim((string) ($data['cv'] ?? ''));
        $fuente = trim((string) ($data['fuente'] ?? ''));

        if ($nombre === '') {
            throw new \InvalidArgumentException("El nombre del candidato es obligatorio.");
        }

        $sql = "INSERT INTO candidatos
                    (nombre, correo, telefono, cv, fuente)
                VALUES (?, ?, ?, ?, ?)";

        $st = $pdo->prepare($sql);
        $st->execute([
            $nombre,
            $correo,
            $telefono,
            $cv !== '' ? $cv : null,
            $fuente !== '' ? $fuente : null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualiza un candidato.
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de candidato inválido.");
        }

        $fields = [];
        $params = [];

        foreach (self::ALLOWED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            switch ($field) {
                case 'nombre':
                    $value = trim((string) $value);
                    if ($value === '') {
                        throw new \InvalidArgumentException("El nombre del candidato es obligatorio.");
                    }
                    break;

                case 'correo':
                    $value = self::normalizarCorreo($value);
                    break;

                case 'telefono':
                    $value = self::normalizarTelefono($value);
                    break;

                case 'cv':
                case 'fuente':
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

        $sql = "UPDATE candidatos SET " . implode(", ", $fields) . " WHERE id_candidato = ?";
        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Elimina un candidato.
     * OJO: no valida si tiene postulaciones asociadas.
     */


    /**
     * Verifica si ya existe un candidato con ese correo.
     */
    public static function existsByCorreo(string $correo, ?int $excludeId = null): bool
    {
        global $pdo;

        $correo = self::normalizarCorreo($correo);

        $sql = "SELECT COUNT(*) FROM candidatos WHERE correo = ?";
        $params = [$correo];

        if ($excludeId !== null) {
            $sql .= " AND id_candidato <> ?";
            $params[] = $excludeId;
        }

        $st = $pdo->prepare($sql);
        $st->execute($params);

        return (bool) $st->fetchColumn();
    }

    /* ====================== Helpers internos ====================== */

    private static function normalizarCorreo($valor): ?string
    {
        $valor = $valor === null ? '' : trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Correo electrónico inválido.");
        }

        return $valor;
    }

    private static function normalizarTelefono($valor): ?string
    {
        $valor = $valor === null ? '' : trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        // Validación simple: longitud mínima/máxima
        if (strlen($valor) < 5 || strlen($valor) > 25) {
            throw new \InvalidArgumentException("Teléfono inválido (longitud incorrecta).");
        }

        return $valor;
    }

    /**
     * Eliminado lógico (activo = 0).
     */
    public static function delete(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE candidatos SET activo = 0 WHERE id_candidato = ?");
        return $stmt->execute([$id]);
    }
}