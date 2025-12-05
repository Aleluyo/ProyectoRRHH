<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/Empleado.php';

/**
 * Modelo para gestión de saldos de vacaciones
 */
class SaldosVacaciones
{
    /**
     * Obtiene el saldo actual de un empleado para un año específico
     */
    public static function obtenerSaldoActual(int $idEmpleado, int $anio): ?array
    {
        global $pdo;

        $sql = "SELECT sv.*, 
                       (sv.dias_asignados - sv.dias_tomados) as dias_disponibles
                FROM saldos_vacaciones sv
                WHERE sv.id_empleado = :id_empleado AND sv.anio = :anio
                LIMIT 1";
        
        $st = $pdo->prepare($sql);
        $st->execute([
            ':id_empleado' => $idEmpleado,
            ':anio' => $anio
        ]);

        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Calcula los días correspondientes según política y antigüedad del empleado
     */
    public static function calcularDiasCorrespondientes(int $idEmpleado, int $anio): float
    {
        global $pdo;

        // Obtener empleado y su empresa a través de puesto/área
        $empleado = Empleado::findById($idEmpleado);
        if (!$empleado) {
            throw new \InvalidArgumentException('Empleado no encontrado.');
        }

        $idEmpresa = (int)$empleado['id_empresa'];
        $fechaIngreso = $empleado['fecha_ingreso'];

        // Obtener política de vacaciones de la empresa
        $sqlPolitica = "SELECT * FROM politicas_vacaciones 
                       WHERE id_empresa = :id_empresa AND activa = 1
                       ORDER BY id_politica DESC LIMIT 1";
        $st = $pdo->prepare($sqlPolitica);
        $st->execute([':id_empresa' => $idEmpresa]);
        $politica = $st->fetch(\PDO::FETCH_ASSOC);

        if (!$politica) {
            // Si no hay política, retornar días base por defecto
            return 6.0;
        }

        $diasInicio = (int)$politica['dias_inicio'];
        $incrementoAnual = (int)$politica['incremento_anual'];
        $diasMax = (int)$politica['dias_max'];

        // Calcular años de antigüedad al inicio del año especificado
        $fechaIngresoTs = strtotime($fechaIngreso);
        $inicioAnioTs = strtotime("$anio-01-01");
        
        $diffSegundos = $inicioAnioTs - $fechaIngresoTs;
        $aniosAntiguedad = max(0, floor($diffSegundos / (365.25 * 24 * 3600)));

        // Calcular días: inicio + (antigüedad * incremento), con tope máximo
        $diasCalculados = $diasInicio + ($aniosAntiguedad * $incrementoAnual);
        $diasFinales = min($diasCalculados, $diasMax);

        return (float)$diasFinales;
    }

    /**
     * Inicializa el saldo para un empleado en un año específico
     */
    public static function inicializarSaldo(int $idEmpleado, int $anio): int
    {
        global $pdo;

        // Verificar si ya existe
        $existe = self::obtenerSaldoActual($idEmpleado, $anio);
        if ($existe) {
            return (int)$existe['id_saldo'];
        }

        // Calcular días correspondientes
        $diasAsignados = self::calcularDiasCorrespondientes($idEmpleado, $anio);

        $sql = "INSERT INTO saldos_vacaciones 
                (id_empleado, anio, dias_asignados, dias_tomados)
                VALUES (?, ?, ?, 0.00)";
        
        $st = $pdo->prepare($sql);
        $st->execute([$idEmpleado, $anio, $diasAsignados]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Actualiza los días tomados (incrementa)
     */
    public static function actualizarDiasTomados(int $idEmpleado, float $dias, int $anio): void
    {
        global $pdo;

        // Asegurar que existe el saldo
        self::inicializarSaldo($idEmpleado, $anio);

        $sql = "UPDATE saldos_vacaciones 
                SET dias_tomados = dias_tomados + :dias
                WHERE id_empleado = :id_empleado AND anio = :anio";
        
        $st = $pdo->prepare($sql);
        $st->execute([
            ':dias' => $dias,
            ':id_empleado' => $idEmpleado,
            ':anio' => $anio
        ]);
    }

    /**
     * Obtiene el historial de saldos de un empleado
     */
    public static function obtenerHistorialSaldos(int $idEmpleado, int $limit = 10): array
    {
        global $pdo;

        $sql = "SELECT sv.*, 
                       (sv.dias_asignados - sv.dias_tomados) as dias_disponibles
                FROM saldos_vacaciones sv
                WHERE sv.id_empleado = :id_empleado
                ORDER BY sv.anio DESC
                LIMIT :limit";
        
        $st = $pdo->prepare($sql);
        $st->bindValue(':id_empleado', $idEmpleado, \PDO::PARAM_INT);
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Inicializa saldos para todos los empleados activos de un año
     */
    public static function inicializarSaldosTodos(int $anio): array
    {
        global $pdo;

        $sql = "SELECT id_empleado FROM empleados WHERE estado = 'ACTIVO'";
        $st = $pdo->query($sql);
        $empleados = $st->fetchAll(\PDO::FETCH_ASSOC);

        $procesados = [];
        foreach ($empleados as $emp) {
            $idEmpleado = (int)$emp['id_empleado'];
            try {
                self::inicializarSaldo($idEmpleado, $anio);
                $procesados[] = [
                    'id_empleado' => $idEmpleado,
                    'status' => 'OK'
                ];
            } catch (\Throwable $e) {
                $procesados[] = [
                    'id_empleado' => $idEmpleado,
                    'status' => 'ERROR',
                    'mensaje' => $e->getMessage()
                ];
            }
        }

        return $procesados;
    }

    /**
     * Valida si un empleado tiene suficientes días disponibles
     */
    public static function validarDiasDisponibles(int $idEmpleado, float $diasSolicitados, int $anio): bool
    {
        // Inicializar saldo si no existe
        self::inicializarSaldo($idEmpleado, $anio);

        $saldo = self::obtenerSaldoActual($idEmpleado, $anio);
        if (!$saldo) {
            return false;
        }

        $diasDisponibles = (float)$saldo['dias_disponibles'];
        return $diasDisponibles >= $diasSolicitados;
    }

    /**
     * Obtiene días disponibles para un empleado en un año
     */
    public static function obtenerDiasDisponibles(int $idEmpleado, int $anio): float
    {
        // Inicializar saldo si no existe
        self::inicializarSaldo($idEmpleado, $anio);

        $saldo = self::obtenerSaldoActual($idEmpleado, $anio);
        if (!$saldo) {
            return 0.0;
        }

        return (float)$saldo['dias_disponibles'];
    }
}
