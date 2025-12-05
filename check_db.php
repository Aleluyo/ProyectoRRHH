<?php
require_once __DIR__ . '/config/db.php';

function checkColumns($table) {
    global $pdo;
    echo "Columns for $table:\n";
    $stmt = $pdo->query("DESCRIBE $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    echo "\n";
}

checkColumns('vacantes');
checkColumns('candidatos');
