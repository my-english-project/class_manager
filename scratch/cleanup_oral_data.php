<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "=== LIMPIEZA DE DATOS EXAMEN ORAL ===\n";

    // Quitar la relación con los textos en los exámenes orales sin borrar las calificaciones
    $db->exec("UPDATE examen_oral SET id_oral_text = NULL");
    
    // Ahora sí podemos vaciar las tablas de temas y textos
    $db->exec("DELETE FROM examen_oral_texto");
    $db->exec("DELETE FROM examen_oral_tema");
    echo "✓ Temas y textos de examen oral eliminados con éxito. Las calificaciones se conservaron.\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
