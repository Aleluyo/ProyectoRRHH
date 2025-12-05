<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Asistencia
 * Control de entradas, salidas y faltas en la tabla asistencia_registros
 */
class Asistencia
{
    private const ORIGENES = ['MANUAL', 'RELOJ', 'IMPORTACION'];
    private const TIPOS    = ['NORMAL', 'RETARDO', 'FALTA', 'JUSTIFICADO'];

    public static function findById(int $id): ?array
    {
        global $pdo;

        $st = $pdo->prepare("SELECT * FROM asistencia_registros WHERE id_asistencia = ? LIMIT 1");
        $st->execute([$id]);

        $row = $st->fetch();
        return $row ?: null;
    }

    public static function findByEmpleadoFecha(int $idEmpleado, string $fecha): ?array
    {
        self::validarFecha($fecha);
        self::assertEmpleadoExiste($idEmpleado);

        global $pdo;

        $st = $pdo->prepare("
            SELECT *
            FROM asistencia_registros
            WHERE id_empleado = ?
              AND fecha = ?
            LIMIT 1
        ");
        $st->execute([$idEmpleado, $fecha]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Listado con filtros básicos
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?int $idEmpleado = null,
        ?string $fechaDesde = null,
        ?string $fechaHasta = null,
        ?string $tipo = null
    ): array {
        global $pdo;

        $limit  = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where  = [];
        $params = [];

        if ($idEmpleado !== null && $idEmpleado > 0) {
            $where[] = 'ar.id_empleado = :id_empleado';
            $params[':id_empleado'] = $idEmpleado;
        }

        if ($fechaDesde !== null && trim($fechaDesde) !== '') {
            self::validarFecha($fechaDesde);
            $where[] = 'ar.fecha >= :desde';
            $params[':desde'] = $fechaDesde;
        }

        if ($fechaHasta !== null && trim($fechaHasta) !== '') {
            self::validarFecha($fechaHasta);
            $where[] = 'ar.fecha <= :hasta';
            $params[':hasta'] = $fechaHasta;
        }

        if ($tipo !== null && trim($tipo) !== '') {
            $tipo = strtoupper(trim($tipo));
            if (!in_array($tipo, self::TIPOS, true)) {
                throw new \InvalidArgumentException("Tipo de asistencia inválido.");
            }
            $where[] = 'ar.tipo = :tipo';
            $params[':tipo'] = $tipo;
        }

        $sql = "
            SELECT ar.*, e.nombre AS nombre_empleado
            FROM asistencia_registros ar
            INNER JOIN empleados e ON e.id_empleado = ar.id_empleado
        ";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= "
            ORDER BY ar.fecha DESC, e.nombre ASC
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
     * Registrar entrada
     */
    public static function registrarEntrada(
        int $idEmpleado,
        string $fecha,
        string $horaEntrada,
        string $origen = 'RELOJ',
        ?string $observaciones = null
    ): int {
        self::validarFecha($fecha);
        self::validarHora($horaEntrada);
        self::validarOrigen($origen);
        self::assertEmpleadoExiste($idEmpleado);

        global $pdo;

        $pdo->beginTransaction();
        try {
            $registro = self::findByEmpleadoFecha($idEmpleado, $fecha);
            $tipo = self::calcularTipoEntrada($idEmpleado, $horaEntrada);

            if ($registro) {
                if ($registro['hora_entrada'] !== null) {
                    throw new \RuntimeException("Ya existe una hora de entrada para este día.");
                }

                $st = $pdo->prepare("
                    UPDATE asistencia_registros
                    SET hora_entrada = ?, tipo = ?, origen = ?, observaciones = ?
                    WHERE id_asistencia = ?
                ");
                $st->execute([
                    $horaEntrada,
                    $tipo,
                    $origen,
                    $observaciones ?? $registro['observaciones'],
                    (int)$registro['id_asistencia']
                ]);

                $id = (int)$registro['id_asistencia'];
            } else {
                $st = $pdo->prepare("
                    INSERT INTO asistencia_registros
                        (id_empleado, fecha, hora_entrada, tipo, origen, observaciones)
                    VALUES
                        (?, ?, ?, ?, ?, ?)
                ");
                $st->execute([
                    $idEmpleado,
                    $fecha,
                    $horaEntrada,
                    $tipo,
                    $origen,
                    $observaciones
                ]);

                $id = (int)$pdo->lastInsertId();
            }

            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Registrar salida
     */
    public static function registrarSalida(
        int $idEmpleado,
        string $fecha,
        string $horaSalida,
        string $origen = 'RELOJ',
        ?string $observaciones = null
    ): void {
        self::validarFecha($fecha);
        self::validarHora($horaSalida);
        self::validarOrigen($origen);
        self::assertEmpleadoExiste($idEmpleado);

        global $pdo;

        $registro = self::findByEmpleadoFecha($idEmpleado, $fecha);
        if (!$registro) {
            throw new \RuntimeException("No existe registro de asistencia para ese día.");
        }

        if ($registro['hora_salida'] !== null) {
            throw new \RuntimeException("Ya existe una hora de salida para este día.");
        }

        $st = $pdo->prepare("
            UPDATE asistencia_registros
            SET hora_salida = ?, origen = ?, observaciones = ?
            WHERE id_asistencia = ?
        ");
        $st->execute([
            $horaSalida,
            $origen,
            $observaciones ?? $registro['observaciones'],
            (int)$registro['id_asistencia']
        ]);
    }

    /**
     * Marcar falta
     */
    public static function marcarFalta(
        int $idEmpleado,
        string $fecha,
        string $origen = 'MANUAL',
        ?string $observaciones = null
    ): int {
        self::validarFecha($fecha);
        self::validarOrigen($origen);
        self::assertEmpleadoExiste($idEmpleado);

        global $pdo;

        $pdo->beginTransaction();
        try {
            $registro = self::findByEmpleadoFecha($idEmpleado, $fecha);

            if ($registro) {
                $st = $pdo->prepare("
                    UPDATE asistencia_registros
                    SET tipo = 'FALTA', origen = ?, observaciones = ?
                    WHERE id_asistencia = ?
                ");
                $st->execute([
                    $origen,
                    $observaciones ?? $registro['observaciones'],
                    (int)$registro['id_asistencia']
                ]);

                $id = (int)$registro['id_asistencia'];
            } else {
                $st = $pdo->prepare("
                    INSERT INTO asistencia_registros
                        (id_empleado, fecha, tipo, origen, observaciones)
                    VALUES
                        (?, ?, 'FALTA', ?, ?)
                ");
                $st->execute([
                    $idEmpleado,
                    $fecha,
                    $origen,
                    $observaciones
                ]);

                $id = (int)$pdo->lastInsertId();
            }

            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Actualización manual (horas, tipo calculado, observaciones)
     */
    public static function actualizarManual(int $idAsistencia, array $data): void
    {
        global $pdo;

        $registro = self::findById($idAsistencia);
        if (!$registro) {
            throw new \RuntimeException("Registro de asistencia no encontrado.");
        }

        $horaEntrada = $data['hora_entrada'] ?? null;
        $horaSalida  = $data['hora_salida'] ?? null;
        $observ      = trim((string)($data['observaciones'] ?? $registro['observaciones'] ?? ''));
        $origen      = strtoupper(trim((string)($data['origen'] ?? $registro['origen'] ?? 'MANUAL')));

        if ($horaEntrada !== null && $horaEntrada !== '') {
            self::validarHora($horaEntrada);
        } else {
            $horaEntrada = null;
        }

        if ($horaSalida !== null && $horaSalida !== '') {
            self::validarHora($horaSalida);
        } else {
            $horaSalida = null;
        }

        self::validarOrigen($origen);

        // Recalcular tipo por hora de entrada
        $tipo = $registro['tipo'];
        if ($horaEntrada !== null) {
            $tipo = self::calcularTipoEntrada((int)$registro['id_empleado'], $horaEntrada);
        }

        $st = $pdo->prepare("
            UPDATE asistencia_registros
            SET hora_entrada = :he,
                hora_salida  = :hs,
                tipo         = :tipo,
                origen       = :origen,
                observaciones = :obs
            WHERE id_asistencia = :id
        ");
        $st->execute([
            ':he'    => $horaEntrada,
            ':hs'    => $horaSalida,
            ':tipo'  => $tipo,
            ':origen'=> $origen,
            ':obs'   => $observ,
            ':id'    => $idAsistencia,
        ]);
    }

    /* ======= helpers privados ======= */

    private static function calcularTipoEntrada(int $idEmpleado, string $horaEntrada): string
    {
        global $pdo;

        $st = $pdo->prepare("
            SELECT t.hora_entrada, t.tolerancia_minutos
            FROM empleados e
            INNER JOIN turnos t ON t.id_turno = e.id_turno
            WHERE e.id_empleado = ?
            LIMIT 1
        ");
        $st->execute([$idEmpleado]);
        $turno = $st->fetch(\PDO::FETCH_ASSOC);

        if (!$turno) {
            return 'NORMAL';
        }

        $horaPlan = \DateTime::createFromFormat('H:i:s', $turno['hora_entrada']);
        $horaReal = \DateTime::createFromFormat('H:i:s', $horaEntrada);

        if (!$horaPlan || !$horaReal) {
            return 'NORMAL';
        }

        if ($horaReal <= $horaPlan) {
            return 'NORMAL';
        }

        $diffMin = (int) floor(($horaReal->getTimestamp() - $horaPlan->getTimestamp()) / 60);
        $tol     = (int) $turno['tolerancia_minutos'];

        return $diffMin > $tol ? 'RETARDO' : 'NORMAL';
    }

    private static function validarFecha(string $fecha): void
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dt || $dt->format('Y-m-d') !== $fecha) {
            throw new \InvalidArgumentException("Fecha inválida (usa formato YYYY-MM-DD).");
        }
    }

    private static function validarHora(string $hora): void
    {
        $dt = \DateTime::createFromFormat('H:i:s', $hora);
        if (!$dt || $dt->format('H:i:s') !== $hora) {
            throw new \InvalidArgumentException("Hora inválida (usa formato HH:MM o HH:MM:SS).");
        }
    }

    private static function validarOrigen(string $origen): void
    {
        $origen = strtoupper(trim($origen));
        if (!in_array($origen, self::ORIGENES, true)) {
            throw new \InvalidArgumentException("Origen inválido de asistencia.");
        }
    }

    private static function assertEmpleadoExiste(int $idEmpleado): void
    {
        global $pdo;

        $st = $pdo->prepare("
            SELECT 1
            FROM empleados
            WHERE id_empleado = ?
              AND estado = 'ACTIVO'
            LIMIT 1
        ");
        $st->execute([$idEmpleado]);

        if (!$st->fetchColumn()) {
            throw new \InvalidArgumentException("Empleado no encontrado o inactivo.");
        }
    }
}
