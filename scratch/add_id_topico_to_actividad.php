<?php
/**
 * Class Manager — Add id_topico column to activity table
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';

try {
    $db = Database::getConnection();
    
    // Check if column already exists
    $stmt = $db->query("SHOW COLUMNS FROM actividad LIKE 'id_topico'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // Add column and foreign key constraint
        $db->exec("ALTER TABLE actividad ADD COLUMN id_topico INT DEFAULT NULL");
        $db->exec("ALTER TABLE actividad ADD CONSTRAINT fk_actividad_topico FOREIGN KEY (id_topico) REFERENCES topico(id_topico) ON DELETE SET NULL ON UPDATE CASCADE");
        echo "✓ Column 'id_topico' and foreign key constraint successfully added to 'actividad' table.\n";
    } else {
        echo "ℹ Column 'id_topico' already exists in 'actividad' table.\n";
    }
} catch (Exception $e) {
    echo "✗ Error during migration: " . $e->getMessage() . "\n";
}
