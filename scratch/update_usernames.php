<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "=== ACTUALIZANDO NOMBRES DE USUARIO EN LA BASE DE DATOS ===\n";
    
    // update usernames for student/docentes (any user that has @utsalamanca.edu.mx)
    // we use SUBSTRING_INDEX to extract the prefix before the @
    $db->exec("
        UPDATE usuario 
        SET username = SUBSTRING_INDEX(username, '@', 1) 
        WHERE username LIKE '%@utsalamanca.edu.mx%'
    ");
    
    $users = $db->query("SELECT id_usuario, username, rol FROM usuario")->fetchAll();
    echo "Nombres de usuario actuales:\n";
    foreach ($users as $u) {
        echo "  - ID {$u['id_usuario']}: {$u['username']} ({$u['rol']})\n";
    }
    
    echo "✓ Usuarios actualizados correctamente.\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
