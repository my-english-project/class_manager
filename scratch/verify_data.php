<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

echo "=== DIAGNÓSTICO DE INTEGRIDAD BASE DE DATOS LOCAL ===\n";

try {
    $db = Database::getConnection();
    echo "✓ Conectado a MySQL local con éxito.\n\n";
    
    // 1. Contador de usuarios y estudiantes
    $users = $db->query("SELECT COUNT(*) as total FROM usuario")->fetch();
    $students = $db->query("SELECT COUNT(*) as total FROM alumno")->fetch();
    $teachers = $db->query("SELECT COUNT(*) as total FROM docente")->fetch();
    $groups = $db->query("SELECT COUNT(*) as total FROM grupo")->fetch();
    
    echo "Resumen de Entidades principales:\n";
    echo "  - Docentes: {$teachers['total']}\n";
    echo "  - Grupos: {$groups['total']}\n";
    echo "  - Alumnos: {$students['total']}\n";
    echo "  - Usuarios: {$users['total']}\n\n";
    
    // 2. Contador de preguntas y opciones
    $questions = $db->query("SELECT COUNT(*) as total FROM pregunta")->fetch();
    $options = $db->query("SELECT COUNT(*) as total FROM opcion")->fetch();
    $correct = $db->query("SELECT COUNT(*) as total FROM opcion WHERE es_correcta = 1")->fetch();
    
    echo "Banco de Preguntas:\n";
    echo "  - Preguntas importadas: {$questions['total']}/120\n";
    echo "  - Opciones de respuestas: {$options['total']}/360\n";
    echo "  - Claves correctas asociadas: {$correct['total']}/120\n\n";
    
    // 3. Simulación de generación de examen de 40 preguntas (5 de cada una de las 8 secciones)
    echo "Simulando generación de examen con 40 preguntas (5 por sección):\n";
    
    $sections = [
        [1, 'A'], [1, 'B'], [1, 'C'], [1, 'D'],
        [2, 'A'], [2, 'B'], [2, 'C'], [2, 'D']
    ];
    
    $examQuestions = [];
    foreach ($sections as $s) {
        $stmt = $db->prepare("
            SELECT p.id_pregunta, p.numero, p.texto, s.letra AS seccion, s.id_topico AS parte 
            FROM pregunta p 
            INNER JOIN seccion s ON p.id_seccion = s.id_seccion
            WHERE s.id_topico = :p AND s.letra = :s 
            ORDER BY RAND() 
            LIMIT 5
        ");
        $stmt->execute([':p' => $s[0], ':s' => $s[1]]);
        $secQuestions = $stmt->fetchAll();
        echo "  - Parte {$s[0]} Sección {$s[1]}: " . count($secQuestions) . " preguntas obtenidas.\n";
        
        foreach ($secQuestions as $q) {
            $examQuestions[] = $q;
        }
    }
    
    echo "\nTotal de preguntas del examen generado: " . count($examQuestions) . " (Debe ser 40).\n";
    
    if (count($examQuestions) === 40) {
        echo "✓ PRUEBA DE GENERACIÓN DE EXAMEN: EXITOSA!\n";
    } else {
        echo "✗ PRUEBA DE GENERACIÓN DE EXAMEN: FALLIDA!\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
