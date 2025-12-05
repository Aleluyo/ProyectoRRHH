<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo PeriodoNomina
 * Maneja los periodos de pago (Semanal, Quincenal, Mensual).
 */
class PeriodoNomina
{
    /**
     * Obtener todos los periodos ordenados por fecha inicio descendente/
     */
    public static function all(): array
    {
        global $pdo;
        $sql = "SELECT pn.*, e.nombre as empresa_nombre 
                FROM periodos_nomina pn
                INNER JOIN empresas e ON e.id_empresa = pn.id_empresa
                ORDER BY pn.fecha_inicio DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar un periodo por ID
     */
    public static function findById(int $id): ?array
    {
        global $pdo;
        $sql = "SELECT pn.*, e.nombre as empresa_nombre 
                FROM periodos_nomina pn
                INNER JOIN empresas e ON e.id_empresa = pn.id_empresa
                WHERE pn.id_periodo = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crear un nuevo periodo
     */
    public static function create(array $data): bool
    {
        global $pdo;
        
        // Validaciones básicas
        if (strtotime($data['fecha_fin']) < strtotime($data['fecha_inicio'])) {
            return false; 
        }

        $sql = "INSERT INTO periodos_nomina (id_empresa, tipo, fecha_inicio, fecha_fin, estado) 
                VALUES (:id_empresa, :tipo, :fecha_inicio, :fecha_fin, 'ABIERTO')";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id_empresa' => $data['id_empresa'],
            ':tipo' => $data['tipo'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin']
        ]);
    }

    /**
     * Cerrar un periodo
     */
    public static function close(int $id): bool
    {
        global $pdo;
        $sql = "UPDATE periodos_nomina SET estado = 'CERRADO' WHERE id_periodo = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
