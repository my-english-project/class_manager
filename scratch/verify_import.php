<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';

$db = Database::getConnection();

echo "=== SESIONES CREADAS POR GRUPO Y PARCIAL ===\n";
$stmt = $db->query("
    SELECT id_grupo, parcial, COUNT(*) as cnt
    FROM sesion
    GROUP BY id_grupo, parcial
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "=== RESUMEN DE ASISTENCIAS POR ESTADO ===\n";
$stmt2 = $db->query("
    SELECT estado, COUNT(*) as cnt
    FROM asistencia
    GROUP BY estado
");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
