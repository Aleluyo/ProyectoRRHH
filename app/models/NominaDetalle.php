<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo NominaDetalle
 * Renglones individuales de la nómina.
 */
class NominaDetalle
{
    /**
     * Obtener detalles de una nómina
     */
    public static function getByNomina(int $idNomina): array
    {
        global $pdo;
        $sql = "SELECT nd.*, c.clave, c.nombre as concepto_nombre, c.tipo
                FROM nomina_detalle nd
                INNER JOIN conceptos_nomina c ON c.id_concepto = nd.id_concepto
                WHERE nd.id_nomina = :id
                ORDER BY c.tipo, c.clave";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idNomina]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insertar un detalle
     */
    public static function create(int $idNomina, int $idConcepto, float $monto, string $observacion = ''): bool
    {
        global $pdo;
        $sql = "INSERT INTO nomina_detalle (id_nomina, id_concepto, monto, observacion)
                VALUES (:id_nom, :id_con, :monto, :obs)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id_nom' => $idNomina,
            ':id_con' => $idConcepto,
            ':monto' => $monto,
            ':obs' => $observacion
        ]);
    }

    /**
     * Borrar detalles previos (útil para recalcular)
     */
    public static function deleteByNomina(int $idNomina): bool
    {
        global $pdo;
        $sql = "DELETE FROM nomina_detalle WHERE id_nomina = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $idNomina]);
    }
}
