<?php
define('BASE_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();

try {
    // 1. Encontrar el usuario admin y su id_referencia
    $admin = $db->query("SELECT * FROM usuario WHERE rol = 'admin' LIMIT 1")->fetch();
    
    if ($admin && $admin['id_referencia']) {
        $idRef = $admin['id_referencia'];
        
        echo "Desvinculando admin (ID Usuario: {$admin['id_usuario']}) del docente (ID Docente: $idRef)...\n";
        
        // 2. Desvincular en la tabla usuario (set id_referencia = NULL o 0)
        // Nota: Si la columna no permite NULL, usaremos 0 o simplemente lo dejaremos huérfano en la lógica.
        // Intentaremos NULL primero.
        try {
            $db->runSql("UPDATE usuario SET id_referencia = NULL WHERE id_usuario = {$admin['id_usuario']}");
        } catch (Exception $e) {
            echo "No se pudo establecer NULL, intentando con 0...\n";
            $db->runSql("UPDATE usuario SET id_referencia = 0 WHERE id_usuario = {$admin['id_usuario']}");
        }
        
        // 3. Desactivar el registro ADMIN01 de la tabla docente en lugar de eliminarlo
        // (por seguridad y compatibilidad con el puente REST)
        echo "Desactivando registro ADMIN01 de la tabla docente...\n";
        $db->runSql("UPDATE docente SET activo = 0 WHERE id_docente = $idRef");
        
        echo "¡Operación completada con éxito!\n";
    } else {
        echo "No se encontró un admin vinculado o ya fue procesado.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
