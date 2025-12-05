<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Nomina
 * Maneja los totales de nómina por empleado.
 */
class Nomina
{
    /**
     * Obtener nómina de un periodo específico
     */
    public static function getByPeriodo(int $idPeriodo): array
    {
        global $pdo;
        $sql = "SELECT ne.*, e.nombre as empleado_nombre, e.rfc, p.nombre_puesto, a.nombre_area
                FROM nomina_empleado ne
                INNER JOIN empleados e ON e.id_empleado = ne.id_empleado
                LEFT JOIN puestos p ON p.id_puesto = e.id_puesto
                LEFT JOIN areas a ON a.id_area = p.id_area
                WHERE ne.id_periodo = :id_periodo
                ORDER BY e.nombre";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_periodo' => $idPeriodo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar nomina por ID
     */
    public static function findById(int $idNomina): ?array
    {
        global $pdo;
        $sql = "SELECT ne.*, e.nombre as empleado_nombre, e.rfc, e.curp, e.nss, e.fecha_ingreso,
                       p.nombre_puesto, a.nombre_area, pn.fecha_inicio, pn.fecha_fin, pn.tipo as periodo_tipo
                FROM nomina_empleado ne
                INNER JOIN empleados e ON e.id_empleado = ne.id_empleado
                INNER JOIN periodos_nomina pn ON pn.id_periodo = ne.id_periodo
                LEFT JOIN puestos p ON p.id_puesto = e.id_puesto
                LEFT JOIN areas a ON a.id_area = p.id_area
                WHERE ne.id_nomina = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idNomina]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Verificar si existe nómina para un empleado en un periodo
     */
    public static function exists(int $idEmpleado, int $idPeriodo): bool
    {
        global $pdo;
        $sql = "SELECT COUNT(*) FROM nomina_empleado WHERE id_empleado = :emp AND id_periodo = :per";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':emp' => $idEmpleado, ':per' => $idPeriodo]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Crear registro maestro de nómina (totales en 0 inicialmente)
     */
    public static function create(int $idEmpleado, int $idPeriodo): int
    {
        global $pdo;
        $sql = "INSERT INTO nomina_empleado (id_empleado, id_periodo, total_percepciones, total_deducciones, total_neto)
                VALUES (:emp, :per, 0, 0, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':emp' => $idEmpleado, ':per' => $idPeriodo]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualizar totales de la nómina
     */
    public static function updateTotals(int $idNomina, float $percepciones, float $deducciones): bool
    {
        global $pdo;
        $neto = $percepciones - $deducciones; // Deducciones se guardan como positivo en columnas de totales usualmente y se restan, o verificar lógica
        // SQL insert showed positive values for both totals, so P - D = Neto.
        
        $sql = "UPDATE nomina_empleado 
                SET total_percepciones = :p, total_deducciones = :d, total_neto = :n 
                WHERE id_nomina = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':p' => $percepciones, ':d' => $deducciones, ':n' => $neto, ':id' => $idNomina]);
    }
    /**
     * Obtener todas las nóminas con detalles para reportes
     */
    public static function getAllExtended(int $limit = 10000, int $offset = 0): array
    {
        global $pdo;
        $sql = "SELECT ne.*, 
                       e.nombre as empleado_nombre, e.rfc, e.nss,
                       p.nombre_puesto, a.nombre_area,
                       pn.fecha_inicio, pn.fecha_fin, pn.tipo as periodo_tipo, pn.id_empresa
                FROM nomina_empleado ne
                INNER JOIN empleados e ON e.id_empleado = ne.id_empleado
                INNER JOIN periodos_nomina pn ON pn.id_periodo = ne.id_periodo
                LEFT JOIN puestos p ON p.id_puesto = e.id_puesto
                LEFT JOIN areas a ON a.id_area = p.id_area
                ORDER BY pn.fecha_inicio DESC, e.nombre ASC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
