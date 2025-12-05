<?php
require_once __DIR__ . '/config/db.php';

try {
    global $pdo;
    
    // Add activo column to vacantes if it doesn't exist
    $pdo->exec("ALTER TABLE vacantes ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Activo, 0=Eliminado'");
    echo "Updated vacantes table.\n";

    // Add activo column to candidatos if it doesn't exist
    $pdo->exec("ALTER TABLE candidatos ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Activo, 0=Eliminado'");
    echo "Updated candidatos table.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
