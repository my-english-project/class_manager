<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/config/database.php';

try {
    $db = Database::getConnection();
    echo "Iniciando adecuación de usuarios...\n";

    // 1. Actualizar Administrador
    // La contraseña ADMIN01 hasheada (usando BCRYPT como AuthController)
    $passAdmin = password_hash('ADMIN01', PASSWORD_DEFAULT);
    $db->runSql("UPDATE usuario SET rol = 'admin', password = '$passAdmin' WHERE username = 'isaurouts@gmail.com'");
    echo "Administrador actualizado.\n";

    // 2. Insertar Maestros si no existen
    $maestros = [
        ['Isauro', 'Rios', '', 'jrios@utsalamanca.edu.mx', 'PROF01', 'JRIOS01'],
        ['Alejandro', 'Rios', '', 'irios@utsalamanca.edu.mx', 'PROF02', 'ARIOS01']
    ];

    foreach ($maestros as $m) {
        // Verificar si existe el docente
        $exists = $db->runSql("SELECT id_docente FROM docente WHERE email = '{$m[3]}'");
        if (empty($exists)) {
            $db->runSql("INSERT INTO docente (nombre, apellido_pat, apellido_mat, email, matricula, activo) 
                         VALUES ('{$m[0]}', '{$m[1]}', '{$m[2]}', '{$m[3]}', '{$m[5]}', 1)");
            $idRef = $db->lastInsertId();
            echo "Docente {$m[0]} {$m[1]} creado con ID $idRef.\n";
        } else {
            $idRef = $exists[0]['id_docente'];
            echo "Docente {$m[0]} {$m[1]} ya existe.\n";
        }

        // Crear/Actualizar usuario
        $pass = password_hash($m[4], PASSWORD_DEFAULT);
        $userExists = $db->runSql("SELECT id_usuario FROM usuario WHERE username = '{$m[3]}'");
        if (empty($userExists)) {
            $db->runSql("INSERT INTO usuario (username, password, rol, id_referencia) 
                         VALUES ('{$m[3]}', '$pass', 'docente', $idRef)");
            echo "Usuario para {$m[3]} creado.\n";
        } else {
            $db->runSql("UPDATE usuario SET password = '$pass', rol = 'docente', id_referencia = $idRef WHERE username = '{$m[3]}'");
            echo "Usuario para {$m[3]} actualizado.\n";
        }
    }

    // 3. Insertar Alumnos si no existen
    $alumnos_login = [
        ['612310471@utsalamanca.edu.mx', 'ALUM01', 1],  // Aline
        ['612310503@utsalamanca.edu.mx', 'ALUM02', 14]  // Emily
    ];

    foreach ($alumnos_login as $a) {
        $pass = password_hash($a[1], PASSWORD_DEFAULT);
        $userExists = $db->runSql("SELECT id_usuario FROM usuario WHERE username = '{$a[0]}'");
        if (empty($userExists)) {
            $db->runSql("INSERT INTO usuario (username, password, rol, id_referencia) 
                         VALUES ('{$a[0]}', '$pass', 'alumno', {$a[2]})");
            echo "Usuario para {$a[0]} creado.\n";
        } else {
            $db->runSql("UPDATE usuario SET password = '$pass', rol = 'alumno', id_referencia = {$a[2]} WHERE username = '{$a[0]}'");
            echo "Usuario para {$a[0]} actualizado.\n";
        }
    }

    echo "Adecuación completada con éxito.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
