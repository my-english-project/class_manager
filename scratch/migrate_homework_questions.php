<?php
/**
 * Migration Script — Add preguntas_respondidas to calificacion_actividad
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    // Check if column already exists
    $columns = $db->query("DESCRIBE calificacion_actividad")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('preguntas_respondidas', $columns)) {
        $db->exec("ALTER TABLE calificacion_actividad ADD COLUMN preguntas_respondidas TEXT DEFAULT NULL");
        echo "Column 'preguntas_respondidas' added to 'calificacion_actividad' successfully.\n";
    } else {
        echo "Column 'preguntas_respondidas' already exists.\n";
    }
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
