<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo ConceptoNomina
 * Catálogo de Percepciones y Deducciones.
 */
class ConceptoNomina
{
    /**
     * Obtener todos los conceptos
     */
    public static function all(): array
    {
        global $pdo;
        $sql = "SELECT * FROM conceptos_nomina ORDER BY tipo, clave";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener conceptos por tipo (PERCEPCION / DEDUCCION)
     */
    public static function getByTipo(string $tipo): array
    {
        global $pdo;
        $sql = "SELECT * FROM conceptos_nomina WHERE tipo = :tipo ORDER BY clave";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':tipo' => $tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
