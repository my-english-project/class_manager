<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

echo "=== MIGRACIÓN DE SUPABASE A MYSQL LOCAL ===\n";

// 1. Conectar a MySQL Local usando PDO
try {
    $mysql = new PDO("mysql:host=localhost;dbname=uts;charset=utf8mb4", "root", "kjb2a0p", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✓ Conectado a MySQL local con éxito.\n";
} catch (PDOException $e) {
    die("✗ Error al conectar a MySQL local: " . $e->getMessage() . "\n");
}

// 2. Conectar a Supabase usando el adaptador existente
try {
    $supabase = Database::getConnection();
    echo "✓ Conectado al cliente HTTP de Supabase con éxito.\n";
} catch (Exception $e) {
    die("✗ Error al conectar a Supabase: " . $e->getMessage() . "\n");
}

// 3. Definir las tablas a migrar en orden de dependencia (padres primero, luego hijos)
$tables = [
    'docente' => 'id_docente',
    'materia' => 'id_materia',
    'grupo' => 'id_grupo',
    'alumno' => 'id_alumno',
    'usuario' => 'id_usuario',
    'grupo_alumno' => 'id_grupo_alumno',
    'sesion' => 'id_sesion',
    'asistencia' => 'id_asistencia',
    'examen_escrito' => 'id_examen_escrito',
    'examen_oral' => 'id_examen_oral',
    'actividad' => 'id_actividad',
    'calificacion_actividad' => 'id_calificacion_actividad'
];

// Desactivar restricciones de claves foráneas temporalmente para truncar de forma limpia
$mysql->exec("SET FOREIGN_KEY_CHECKS = 0;");
foreach (array_reverse(array_keys($tables)) as $table) {
    $mysql->exec("TRUNCATE TABLE `$table`;");
    echo "✓ Tabla local `$table` truncada.\n";
}
$mysql->exec("SET FOREIGN_KEY_CHECKS = 1;");

// 4. Extraer datos de Supabase e insertar en MySQL local
foreach ($tables as $table => $primaryKey) {
    echo "Migrando tabla `$table`...\n";
    try {
        // Ejecutar SELECT en Supabase via el endpoint RPC execute_sql
        $stmt = $supabase->prepare("SELECT * FROM \"$table\"");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        $count = count($rows);
        echo "  Encontrados $count registros en Supabase.\n";
        
        if ($count > 0) {
            // Preparar el INSERT en MySQL local
            $columns = array_keys($rows[0]);
            $colList = implode(', ', array_map(fn($c) => "`$c`", $columns));
            $placeholders = implode(', ', array_map(fn($c) => ":$c", $columns));
            
            $insertQuery = "INSERT INTO `$table` ($colList) VALUES ($placeholders)";
            $insertStmt = $mysql->prepare($insertQuery);
            
            $inserted = 0;
            foreach ($rows as $row) {
                // Limpiar tipos de datos para MySQL
                foreach ($row as $k => $v) {
                    if ($v === '') {
                        $row[$k] = null;
                    }
                }
                $insertStmt->execute($row);
                $inserted++;
            }
            echo "  ✓ $inserted registros insertados exitosamente en MySQL local.\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error en la tabla `$table`: " . $e->getMessage() . "\n";
    }
}

echo "\n=== MIGRACIÓN COMPLETADA CON ÉXITO ===\n";
