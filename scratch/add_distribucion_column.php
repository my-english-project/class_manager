<?php
/**
 * Class Manager — Add distribucion_preguntas column to examen_config
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    
    // Check if column already exists
    $stmt = $db->query("SHOW COLUMNS FROM examen_config LIKE 'distribucion_preguntas'");
    $column = $stmt->fetch();
    
    if (!$column) {
        $db->exec("ALTER TABLE examen_config ADD COLUMN distribucion_preguntas TEXT DEFAULT NULL");
        echo "Column 'distribucion_preguntas' added successfully to 'examen_config' table.\n";
    } else {
        echo "Column 'distribucion_preguntas' already exists.\n";
    }
} catch (Exception $e) {
    echo "Error running migration: " . $e->getMessage() . "\n";
}
