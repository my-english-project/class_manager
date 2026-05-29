<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

$tables = [
    'docente',
    'materia',
    'grupo',
    'alumno',
    'grupo_alumno',
    'sesion',
    'asistencia',
    'examen_escrito',
    'examen_oral',
    'actividad',
    'calificacion_actividad'
];

$db = Database::getConnection();

foreach ($tables as $table) {
    try {
        $stmt = $db->prepare("SELECT count(*) as total FROM \"$table\"");
        $stmt->execute();
        $row = $stmt->fetch();
        echo "Table $table: " . ($row['total'] ?? 0) . " rows\n";
    } catch (Exception $e) {
        echo "Table $table error: " . $e->getMessage() . "\n";
    }
}
