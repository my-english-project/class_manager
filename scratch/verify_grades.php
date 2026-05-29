<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';

$db = Database::getConnection();

echo "=== COUNT EXAMEN ESCRITO POR ALUMNO EN GRUPO 1 ===\n";
$stmt = $db->query("
    SELECT a.matricula, a.nombre, a.apellido_pat, COUNT(ee.id_examen_escrito) as count_write
    FROM alumno a
    INNER JOIN grupo_alumno ga ON a.id_alumno = ga.id_alumno
    LEFT JOIN examen_escrito ee ON a.id_alumno = ee.id_alumno AND ee.id_grupo = 1
    WHERE ga.id_grupo = 1
    GROUP BY a.id_alumno
    ORDER BY a.apellido_pat, a.apellido_mat, a.nombre
");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "{$row['matricula']} - {$row['apellido_pat']} {$row['nombre']}: {$row['count_write']} exams\n";
}

echo "\n=== ACTIVIDADES CREADAS EN GRUPOS ===\n";
$stmt2 = $db->query("
    SELECT id_grupo, tipo, parcial, nombre
    FROM actividad
    ORDER BY id_grupo, tipo, parcial
");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
