<?php
define('BASE_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();
$users = $db->query("SELECT * FROM usuario WHERE rol = 'admin'")->fetchAll();
echo "USUARIOS ADMIN:\n";
print_r($users);

$docentes = $db->query("SELECT * FROM docente WHERE nombre LIKE '%Admin%' OR matricula LIKE '%ADMIN%'")->fetchAll();
echo "\nDOCENTES ADMIN:\n";
print_r($docentes);
