<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/config/database.php';

try {
    $db = Database::getConnection();
    $alumnos = $db->runSql("SELECT * FROM alumno WHERE matricula IN ('612310471', '612310503')");
    echo "Alumnos encontrados:\n" . json_encode($alumnos, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
