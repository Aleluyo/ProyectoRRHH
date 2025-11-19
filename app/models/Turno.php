<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Turno para RRHH_TEC
 * CRUD completo + validaciones básicas.
 */
class Turno
{
    private const ALLOWED_FIELDS = [
        'nombre_turno',
        'hora_entrada',
        'hora_salida',
        'tolerancia_minutos',
        'dias_laborales',
    ];

    /** Días válidos para el SET de dias_laborales */
    private const DIAS_VALIDOS = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];

    /**
     * Devuelve un turno por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        $st = $pdo->prepare("SELECT * FROM turnos WHERE id_turno = ? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de turnos con paginado y búsqueda por nombre.
     */
    public static function all(int $limit = 500, int $offset = 0, ?string $search = null): array
    {
        global $pdo;

        $limit  = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where  = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $q = '%' . trim($search) . '%';
            $where[]        = '(nombre_turno LIKE :q)';
            $params[':q']   = $q;
        }

        $sql = "SELECT * FROM turnos";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY nombre_turno ASC
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
     * Crea un turno.
     */
    public static function create(array $data): int
    {
        global $pdo;

        $nombreTurno       = trim((string)($data['nombre_turno'] ?? ''));
        $horaEntradaRaw    = trim((string)($data['hora_entrada'] ?? ''));
        $horaSalidaRaw     = trim((string)($data['hora_salida'] ?? ''));
        $toleranciaRaw     = $data['tolerancia_minutos'] ?? 10;
        $diasLaboralesRaw  = $data['dias_laborales'] ?? 'L,M,X,J,V';

        if ($nombreTurno === '') {
            throw new \InvalidArgumentException("El nombre del turno es obligatorio.");
        }

        $horaEntrada = self::normalizarHora($horaEntradaRaw, "hora de entrada");
        $horaSalida  = self::normalizarHora($horaSalidaRaw, "hora de salida");

        // Validación simple: la hora de salida debe ser distinta a la de entrada.
        if ($horaEntrada === $horaSalida) {
            throw new \InvalidArgumentException("La hora de entrada y salida no pueden ser iguales.");
        }

        $toleranciaMin = self::normalizarTolerancia($toleranciaRaw);
        $diasLaborales = self::normalizarDiasLaborales($diasLaboralesRaw);

        $sql = "INSERT INTO turnos 
                    (nombre_turno, hora_entrada, hora_salida, tolerancia_minutos, dias_laborales)
                VALUES (?, ?, ?, ?, ?)";

        $st = $pdo->prepare($sql);
        $st->execute([
            $nombreTurno,
            $horaEntrada,
            $horaSalida,
            $toleranciaMin,
            $diasLaborales
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Actualiza un turno.
     */
    public static function update(int $id, array $data): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de turno inválido.");
        }

        $fields = [];
        $params = [];

        foreach (self::ALLOWED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            switch ($field) {
                case 'nombre_turno':
                    $value = trim((string)$value);
                    if ($value === '') {
                        throw new \InvalidArgumentException("El nombre del turno es obligatorio.");
                    }
                    break;

                case 'hora_entrada':
                    $value = self::normalizarHora((string)$value, "hora de entrada");
                    break;

                case 'hora_salida':
                    $value = self::normalizarHora((string)$value, "hora de salida");
                    break;

                case 'tolerancia_minutos':
                    $value = self::normalizarTolerancia($value);
                    break;

                case 'dias_laborales':
                    $value = self::normalizarDiasLaborales($value);
                    break;
            }

            $fields[] = "$field = ?";
            $params[] = $value;
        }

        if (empty($fields)) {
            return; // Nada que actualizar
        }

        $params[] = $id;

        $sql = "UPDATE turnos SET " . implode(", ", $fields) . " WHERE id_turno = ?";
        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    /**
     * Elimina un turno (DELETE).
     * 
     */
    public static function delete(int $id): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de turno inválido.");
        }

        $st = $pdo->prepare("DELETE FROM turnos WHERE id_turno = ?");
        $st->execute([$id]);
    }

    /* ====================== Helpers internos ====================== */

    /**
     * Normaliza una hora (acepta formatos H:i o H:i:s) y devuelve H:i:s
     */
    private static function normalizarHora(string $hora, string $labelCampo): string
    {
        if ($hora === '') {
            throw new \InvalidArgumentException("La {$labelCampo} es obligatoria.");
        }

        $horaObj = \DateTime::createFromFormat('H:i:s', $hora)
                ?: \DateTime::createFromFormat('H:i', $hora);

        if (!$horaObj) {
            throw new \InvalidArgumentException("Formato inválido para {$labelCampo} (usa HH:MM o HH:MM:SS).");
        }

        return $horaObj->format('H:i:s');
    }

    /**
     * Normaliza tolerancia en minutos (int >= 0).
     */
    private static function normalizarTolerancia($valor): int
    {
        if ($valor === '' || $valor === null) {
            $valor = 10;
        }

        $min = (int)$valor;

        if ($min < 0) {
            throw new \InvalidArgumentException("La tolerancia en minutos no puede ser negativa.");
        }

        // Puedes limitar a cierto máximo, p.ej: 240 min
        if ($min > 1440) {
            throw new \InvalidArgumentException("La tolerancia en minutos es demasiado alta.");
        }

        return $min;
    }

    /**
     * Normaliza los días laborales a un string compatible con el SET:
     * - Acepta string "L,M,X" o array ['L','M','X']
     * - Devuelve ej: "L,M,X,J,V"
     */
    private static function normalizarDiasLaborales($valor): string
    {
        $dias = [];

        if (is_array($valor)) {
            $dias = $valor;
        } else {
            $valor = trim((string)$valor);
            if ($valor === '') {
                // Por defecto lunes a viernes
                $dias = ['L', 'M', 'X', 'J', 'V'];
            } else {
                $dias = explode(',', $valor);
            }
        }

        $limpios = [];

        foreach ($dias as $d) {
            $d = strtoupper(trim((string)$d));
            if ($d === '') {
                continue;
            }
            if (!in_array($d, self::DIAS_VALIDOS, true)) {
                throw new \InvalidArgumentException("Día laboral inválido: {$d}");
            }
            if (!in_array($d, $limpios, true)) {
                $limpios[] = $d;
            }
        }

        if (empty($limpios)) {
            throw new \InvalidArgumentException("Debes seleccionar al menos un día laboral.");
        }

        // Orden estándar L,M,X,J,V,S,D
        $ordenados = [];
        foreach (self::DIAS_VALIDOS as $diaValido) {
            if (in_array($diaValido, $limpios, true)) {
                $ordenados[] = $diaValido;
            }
        }

        return implode(',', $ordenados);
    }
}
