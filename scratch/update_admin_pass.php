<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    $newPassword = 'uts2026';
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("UPDATE usuario SET password = :pass WHERE username = :user");
    $stmt->execute([
        ':pass' => $hash,
        ':user' => 'isaurouts@gmail.com'
    ]);
    
    echo "✓ Contraseña de isaurouts@gmail.com actualizada con éxito a: $newPassword\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
