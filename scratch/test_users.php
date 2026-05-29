<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/config/database.php';

try {
    $db = Database::getConnection();
    $users = $db->runSql("SELECT * FROM usuario");
    echo "Usuarios actuales:\n" . json_encode($users, JSON_PRETTY_PRINT) . "\n";
    
    $docentes = $db->runSql("SELECT * FROM docente");
    echo "\nDocentes actuales:\n" . json_encode($docentes, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
