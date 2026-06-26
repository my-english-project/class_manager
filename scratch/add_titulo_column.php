<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "=== MIGRACIÓN ADICIÓN COLUMNA TITULO ===\n";

    $stmt = $db->query("SHOW COLUMNS FROM examen_oral_texto LIKE 'titulo'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE examen_oral_texto ADD COLUMN titulo VARCHAR(120) DEFAULT NULL");
        echo "✓ Columna 'titulo' agregada exitosamente a la tabla 'examen_oral_texto'.\n";
    } else {
        echo "✓ Columna 'titulo' ya existe en la tabla 'examen_oral_texto'.\n";
    }

    echo "=== MIGRACIÓN COMPLETADA CON ÉXITO ===\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
