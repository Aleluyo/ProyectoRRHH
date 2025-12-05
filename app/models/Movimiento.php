<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Movimiento
 * Maneja los registros de bajas y cambios administrativos de empleados
 */
class Movimiento
{
    /**
     * Lista todos los movimientos con información del empleado
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?string $tipoMovimiento = null,
        ?int $idEmpleado = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): array {
        global $pdo;

        $limit = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where = [];
        $params = [];

        // Filtro por tipo de movimiento
        if ($tipoMovimiento !== null && $tipoMovimiento !== '') {
            $where[] = 'm.tipo_movimiento = :tipo_movimiento';
            $params[':tipo_movimiento'] = $tipoMovimiento;
        }

        // Filtro por empleado
        if ($idEmpleado !== null) {
            $where[] = 'm.id_empleado = :id_empleado';
            $params[':id_empleado'] = $idEmpleado;
        }

        // Filtro por rango de fechas
        if ($fechaInicio !== null && $fechaInicio !== '') {
            $where[] = 'm.fecha_movimiento >= :fecha_inicio';
            $params[':fecha_inicio'] = $fechaInicio;
        }

        if ($fechaFin !== null && $fechaFin !== '') {
            $where[] = 'm.fecha_movimiento <= :fecha_fin';
            $params[':fecha_fin'] = $fechaFin;
        }

        $whereSQL = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT 
                m.id_movimiento,
                m.id_empleado,
                m.tipo_movimiento,
                m.fecha_movimiento,
                m.motivo,
                m.observaciones,
                m.valor_anterior,
                m.valor_nuevo,
                m.autorizado_por,
                m.fecha_registro,
                e.nombre AS nombre_empleado,
                e.curp,
                e.estado AS estado_empleado,
                emp.nombre AS empresa_actual,
                a.nombre_area AS area_actual,
                p.nombre_puesto AS puesto_actual,
                u.username AS usuario_registro
            FROM movimientos m
            INNER JOIN empleados e ON m.id_empleado = e.id_empleado
            LEFT JOIN puestos p ON e.id_puesto = p.id_puesto
            LEFT JOIN areas a ON p.id_area = a.id_area
            LEFT JOIN empresas emp ON a.id_empresa = emp.id_empresa
            LEFT JOIN usuarios u ON m.autorizado_por = u.id_usuario
            {$whereSQL}
            ORDER BY m.fecha_movimiento DESC, m.fecha_registro DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de movimientos (para paginación)
     */
    public static function count(
        ?string $tipoMovimiento = null,
        ?int $idEmpleado = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): int {
        global $pdo;

        $where = [];
        $params = [];

        if ($tipoMovimiento !== null && $tipoMovimiento !== '') {
            $where[] = 'm.tipo_movimiento = :tipo_movimiento';
            $params[':tipo_movimiento'] = $tipoMovimiento;
        }

        if ($idEmpleado !== null) {
            $where[] = 'm.id_empleado = :id_empleado';
            $params[':id_empleado'] = $idEmpleado;
        }

        if ($fechaInicio !== null && $fechaInicio !== '') {
            $where[] = 'm.fecha_movimiento >= :fecha_inicio';
            $params[':fecha_inicio'] = $fechaInicio;
        }

        if ($fechaFin !== null && $fechaFin !== '') {
            $where[] = 'm.fecha_movimiento <= :fecha_fin';
            $params[':fecha_fin'] = $fechaFin;
        }

        $whereSQL = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) FROM movimientos m {$whereSQL}";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene un movimiento por ID
     */
    public static function find(int $id): ?array
    {
        global $pdo;

        $sql = "
            SELECT 
                m.*,
                e.nombre AS nombre_empleado,
                e.curp,
                e.estado AS estado_empleado,
                emp.nombre AS empresa_actual,
                a.nombre_area AS area_actual,
                p.nombre_puesto AS puesto_actual,
                u.username AS usuario_registro
            FROM movimientos m
            INNER JOIN empleados e ON m.id_empleado = e.id_empleado
            LEFT JOIN puestos p ON e.id_puesto = p.id_puesto
            LEFT JOIN areas a ON p.id_area = a.id_area
            LEFT JOIN empresas emp ON a.id_empresa = emp.id_empresa
            LEFT JOIN usuarios u ON m.autorizado_por = u.id_usuario
            WHERE m.id_movimiento = :id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Obtiene el historial de movimientos de un empleado
     */
    public static function historialEmpleado(int $idEmpleado): array
    {
        global $pdo;

        $sql = "
            SELECT 
                m.*,
                u.username AS usuario_registro
            FROM movimientos m
            LEFT JOIN usuarios u ON m.autorizado_por = u.id_usuario
            WHERE m.id_empleado = :id_empleado
            ORDER BY m.fecha_movimiento DESC, m.fecha_registro DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_empleado' => $idEmpleado]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un nuevo movimiento
     */
    public static function create(array $data): int
    {
        global $pdo;

        $sql = "
            INSERT INTO movimientos (
                id_empleado,
                tipo_movimiento,
                fecha_movimiento,
                motivo,
                observaciones,
                valor_anterior,
                valor_nuevo,
                autorizado_por,
                fecha_registro
            ) VALUES (
                :id_empleado,
                :tipo_movimiento,
                :fecha_movimiento,
                :motivo,
                :observaciones,
                :valor_anterior,
                :valor_nuevo,
                :autorizado_por,
                NOW()
            )
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_empleado' => $data['id_empleado'],
            ':tipo_movimiento' => $data['tipo_movimiento'],
            ':fecha_movimiento' => $data['fecha_movimiento'],
            ':motivo' => $data['motivo'] ?? null,
            ':observaciones' => $data['observaciones'] ?? null,
            ':valor_anterior' => $data['valor_anterior'] ?? null,
            ':valor_nuevo' => $data['valor_nuevo'] ?? null,
            ':autorizado_por' => $data['autorizado_por']
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Registra una baja de empleado
     */
    public static function registrarBaja(
        int $idEmpleado,
        string $fechaBaja,
        string $motivo,
        int $autorizadoPor,
        ?string $observaciones = null
    ): int {
        global $pdo;

        $pdo->beginTransaction();

        try {
            // Obtener datos actuales del empleado
            $empleado = Empleado::findById($idEmpleado);
            if (!$empleado) {
                throw new Exception("Empleado no encontrado");
            }

            // Registrar el movimiento
            $idMovimiento = self::create([
                'id_empleado' => $idEmpleado,
                'tipo_movimiento' => 'BAJA',
                'fecha_movimiento' => $fechaBaja,
                'motivo' => $motivo,
                'observaciones' => $observaciones,
                'valor_anterior' => 'ACTIVO',
                'valor_nuevo' => 'BAJA',
                'autorizado_por' => $autorizadoPor
            ]);

            // Actualizar estado del empleado
            $sql = "UPDATE empleados SET estado = 'BAJA', fecha_baja = :fecha_baja WHERE id_empleado = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':fecha_baja' => $fechaBaja,
                ':id' => $idEmpleado
            ]);

            $pdo->commit();
            return $idMovimiento;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Registra un cambio de área
     */
    public static function cambiarArea(
        int $idEmpleado,
        int $nuevaArea,
        string $fechaCambio,
        string $motivo,
        ?string $observaciones,
        int $autorizadoPor
    ): int {
        global $pdo;

        $pdo->beginTransaction();

        try {
            // Obtener área actual
            $empleado = Empleado::findById($idEmpleado);
            if (!$empleado) {
                throw new Exception("Empleado no encontrado");
            }

            $areaAnterior = $empleado['nombre_area'] ?? 'Sin área';

            // Obtener nombre de la nueva área
            $stmt = $pdo->prepare("SELECT nombre_area FROM areas WHERE id_area = :id");
            $stmt->execute([':id' => $nuevaArea]);
            $areaNueva = $stmt->fetchColumn();

            // Registrar el movimiento
            $idMovimiento = self::create([
                'id_empleado' => $idEmpleado,
                'tipo_movimiento' => 'CAMBIO_AREA',
                'fecha_movimiento' => $fechaCambio,
                'motivo' => $motivo,
                'observaciones' => $observaciones,
                'valor_anterior' => $areaAnterior,
                'valor_nuevo' => $areaNueva,
                'autorizado_por' => $autorizadoPor
            ]);

            // Actualizar área del empleado
            $sql = "UPDATE empleados SET id_area = :id_area WHERE id_empleado = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_area' => $nuevaArea,
                ':id' => $idEmpleado
            ]);

            $pdo->commit();
            return $idMovimiento;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Registra un cambio de puesto
     */
    public static function cambiarPuesto(
        int $idEmpleado,
        int $nuevoPuesto,
        string $fechaCambio,
        string $motivo,
        ?string $observaciones,
        int $autorizadoPor
    ): int {
        global $pdo;

        $pdo->beginTransaction();

        try {
            // Obtener puesto actual
            $empleado = Empleado::findById($idEmpleado);
            if (!$empleado) {
                throw new Exception("Empleado no encontrado");
            }

            $puestoAnterior = $empleado['nombre_puesto'] ?? 'Sin puesto';

            // Obtener nombre del nuevo puesto
            $stmt = $pdo->prepare("SELECT nombre_puesto FROM puestos WHERE id_puesto = :id");
            $stmt->execute([':id' => $nuevoPuesto]);
            $puestoNuevo = $stmt->fetchColumn();

            // Registrar el movimiento
            $idMovimiento = self::create([
                'id_empleado' => $idEmpleado,
                'tipo_movimiento' => 'CAMBIO_PUESTO',
                'fecha_movimiento' => $fechaCambio,
                'motivo' => $motivo,
                'observaciones' => $observaciones,
                'valor_anterior' => $puestoAnterior,
                'valor_nuevo' => $puestoNuevo,
                'autorizado_por' => $autorizadoPor
            ]);

            // Actualizar puesto del empleado
            $sql = "UPDATE empleados SET id_puesto = :id_puesto WHERE id_empleado = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_puesto' => $nuevoPuesto,
                ':id' => $idEmpleado
            ]);

            $pdo->commit();
            return $idMovimiento;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Registra un cambio de jefe inmediato
     */
    public static function cambiarJefeInmediato(
        int $idEmpleado,
        ?int $nuevoJefe,
        string $fechaCambio,
        string $motivo,
        ?string $observaciones,
        int $autorizadoPor
    ): int {
        global $pdo;

        $pdo->beginTransaction();

        try {
            // Obtener jefe actual
            $empleado = Empleado::findById($idEmpleado);
            if (!$empleado) {
                throw new Exception("Empleado no encontrado");
            }

            $jefeAnterior = $empleado['jefe_inmediato'] ?? 'Sin jefe';

            // Obtener nombre del nuevo jefe
            $jefeNuevo = 'Sin jefe';
            if ($nuevoJefe !== null) {
                $stmt = $pdo->prepare("SELECT nombre FROM empleados WHERE id_empleado = :id");
                $stmt->execute([':id' => $nuevoJefe]);
                $jefeNuevo = $stmt->fetchColumn() ?: 'Sin jefe';
            }

            // Registrar el movimiento
            $idMovimiento = self::create([
                'id_empleado' => $idEmpleado,
                'tipo_movimiento' => 'CAMBIO_JEFE',
                'fecha_movimiento' => $fechaCambio,
                'motivo' => $motivo,
                'observaciones' => $observaciones,
                'valor_anterior' => $jefeAnterior,
                'valor_nuevo' => $jefeNuevo,
                'autorizado_por' => $autorizadoPor
            ]);

            // Actualizar jefe del empleado
            $sql = "UPDATE empleados SET id_jefe = :id_jefe WHERE id_empleado = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_jefe' => $nuevoJefe,
                ':id' => $idEmpleado
            ]);

            $pdo->commit();
            return $idMovimiento;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Tipos de movimientos disponibles
     */
    public static function tiposMovimiento(): array
    {
        return [
            'BAJA' => 'Baja de empleado',
            'CAMBIO_AREA' => 'Cambio de área',
            'CAMBIO_PUESTO' => 'Cambio de puesto',
            'CAMBIO_JEFE' => 'Cambio de jefe inmediato',
            'CAMBIO_POSICION' => 'Cambio de posición'
        ];
    }
}
