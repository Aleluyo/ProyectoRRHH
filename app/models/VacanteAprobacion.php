<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo VacanteAprobacion para RRHH_TEC
 * Maneja el flujo de aprobaciones por nivel de una vacante.
 */
class VacanteAprobacion
{
    private const ALLOWED_FIELDS = [
        'decision',
        'comentario',
    ];

    private const DECISIONES_VALIDAS = ['PENDIENTE', 'APROBADO', 'RECHAZADO'];

    /**
     * Devuelve un registro de aprobación por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de aprobación inválido.");
        }

        $st = $pdo->prepare("SELECT * FROM vacante_aprobaciones WHERE id_aprobacion = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista aprobaciones de una vacante, ordenadas por nivel.
     */
    public static function byVacante(int $idVacante): array
    {
        global $pdo;

        if ($idVacante <= 0) {
            throw new \InvalidArgumentException("ID de vacante inválido.");
        }

        $st = $pdo->prepare(
            "SELECT * 
             FROM vacante_aprobaciones
             WHERE id_vacante = ?
             ORDER BY nivel ASC, id_aprobacion ASC"
        );
        $st->execute([$idVacante]);
        return $st->fetchAll();
    }

    /**
     * Crea un paso de aprobación para una vacante.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $idVacante  = self::normalizarId($data['id_vacante'] ?? null, "vacante");
        $aprobador  = self::normalizarId($data['aprobador'] ?? null, "aprobador");
        $nivelRaw   = $data['nivel'] ?? 1;
        $nivel      = (int)$nivelRaw;

        if ($nivel <= 0) {
            throw new \InvalidArgumentException("El nivel de aprobación debe ser un entero positivo.");
        }

        $decisionRaw = (string)($data['decision'] ?? 'PENDIENTE');
        $decision    = self::normalizarDecision($decisionRaw);

        $comentario  = trim((string)($data['comentario'] ?? ''));

        $sql = "INSERT INTO vacante_aprobaciones
                    (id_vacante, aprobador, nivel, decision, comentario)
                VALUES (?, ?, ?, ?, ?)";

        $st = $pdo->prepare($sql);
        $st->execute([
            $idVacante,
            $aprobador,
            $nivel,
            $decision,
            $comentario !== '' ? $comentario : null,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Actualiza la decisión de un registro de aprobación.
     */
    public static function updateDecision(int $id, string $decision, ?string $comentario = null): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de aprobación inválido.");
        }

        $decision  = self::normalizarDecision($decision);
        $comentario = $comentario !== null ? trim($comentario) : null;

        $sql = "UPDATE vacante_aprobaciones
                SET decision = ?, comentario = ?, decidido_en = NOW()
                WHERE id_aprobacion = ?";

        $st = $pdo->prepare($sql);
        $st->execute([
            $decision,
            $comentario !== '' ? $comentario : null,
            $id
        ]);
    }

    /**
     * Elimina un registro de aprobación.
     */
    public static function delete(int $id): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de aprobación inválido.");
        }

        $st = $pdo->prepare("DELETE FROM vacante_aprobaciones WHERE id_aprobacion = ?");
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

    private static function normalizarDecision(string $decision): string
    {
        $d = strtoupper(trim($decision));
        if (!in_array($d, self::DECISIONES_VALIDAS, true)) {
            throw new \InvalidArgumentException("Decisión inválida: {$decision}");
        }
        return $d;
    }
}