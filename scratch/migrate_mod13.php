<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "=== MIGRACIÓN MODIFICACIÓN 13 - BASE DE DATOS LOCAL ===\n";

    // 1. LEER PREGUNTAS Y OPCIONES EXISTENTES ANTES DE TRUNCAR
    echo "1. Leyendo banco de preguntas existente...\n";
    $oldPreguntas = $db->query("SELECT * FROM pregunta ORDER BY id_pregunta ASC")->fetchAll();
    $oldOpciones = $db->query("SELECT * FROM opcion ORDER BY id_opcion ASC")->fetchAll();

    $opcionesPorPregunta = [];
    foreach ($oldOpciones as $opt) {
        $opcionesPorPregunta[$opt['id_pregunta']][] = $opt;
    }

    echo "   - Leídas " . count($oldPreguntas) . " preguntas y " . count($oldOpciones) . " opciones.\n";

    // 2. ELIMINAR TABLAS CON FOREIGN KEYS
    echo "2. Eliminando tablas anteriores...\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tablesToDrop = [
        'asistencia', 'sesion', 'grupo_alumno', 'examen_escrito', 'examen_oral', 
        'calificacion_actividad', 'actividad', 'examen_config', 'opcion', 'pregunta', 
        'grupo', 'alumno', 'docente', 'materia', 'seccion', 'topico', 'ciclo'
    ];
    foreach ($tablesToDrop as $table) {
        $db->exec("DROP TABLE IF EXISTS {$table}");
    }
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "   - Tablas eliminadas con éxito.\n";

    // 3. CREAR NUEVAS TABLAS
    echo "3. Creando nuevas tablas...\n";
    
    // Tabla: ciclo
    $db->exec("
    CREATE TABLE ciclo (
      id_ciclo INT AUTO_INCREMENT PRIMARY KEY,
      codigo VARCHAR(10) NOT NULL UNIQUE,
      activo TINYINT(1) NOT NULL DEFAULT 1,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: docente
    $db->exec("
    CREATE TABLE docente (
      id_docente INT AUTO_INCREMENT PRIMARY KEY,
      matricula VARCHAR(20) NOT NULL UNIQUE,
      nombre VARCHAR(80) NOT NULL,
      apellido_pat VARCHAR(60) NOT NULL,
      apellido_mat VARCHAR(60) DEFAULT NULL,
      email VARCHAR(120) DEFAULT NULL UNIQUE,
      activo TINYINT(1) NOT NULL DEFAULT 1,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: materia
    $db->exec("
    CREATE TABLE materia (
      id_materia INT AUTO_INCREMENT PRIMARY KEY,
      nombre VARCHAR(120) NOT NULL,
      siglas VARCHAR(20) NOT NULL UNIQUE,
      descripcion TEXT DEFAULT NULL,
      activo TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: grupo
    $db->exec("
    CREATE TABLE grupo (
      id_grupo INT AUTO_INCREMENT PRIMARY KEY,
      id_docente INT NOT NULL,
      id_materia INT DEFAULT NULL,
      carrera VARCHAR(120) NOT NULL,
      siglas VARCHAR(20) NOT NULL,
      cuatrimestre TINYINT NOT NULL,
      grupo VARCHAR(10) NOT NULL,
      periodo ENUM('ene-abr','may-ago','sep-dic') NOT NULL,
      ciclo VARCHAR(10) NOT NULL,
      anio SMALLINT NOT NULL,
      activo TINYINT(1) NOT NULL DEFAULT 1,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (id_docente) REFERENCES docente(id_docente) ON DELETE RESTRICT ON UPDATE CASCADE,
      FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (ciclo) REFERENCES ciclo(codigo) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: alumno
    $db->exec("
    CREATE TABLE alumno (
      id_alumno INT AUTO_INCREMENT PRIMARY KEY,
      matricula VARCHAR(20) NOT NULL UNIQUE,
      nombre VARCHAR(80) NOT NULL,
      apellido_pat VARCHAR(60) NOT NULL,
      apellido_mat VARCHAR(60) DEFAULT NULL,
      email VARCHAR(120) DEFAULT NULL,
      activo TINYINT(1) NOT NULL DEFAULT 1,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: grupo_alumno
    $db->exec("
    CREATE TABLE grupo_alumno (
      id_grupo_alumno INT AUTO_INCREMENT PRIMARY KEY,
      id_grupo INT NOT NULL,
      id_alumno INT NOT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY uq_grupo_alumno (id_grupo, id_alumno),
      FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo) ON DELETE CASCADE,
      FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: topico
    $db->exec("
    CREATE TABLE topico (
      id_topico INT AUTO_INCREMENT PRIMARY KEY,
      nombre VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: seccion
    $db->exec("
    CREATE TABLE seccion (
      id_seccion INT AUTO_INCREMENT PRIMARY KEY,
      id_topico INT NOT NULL,
      nombre VARCHAR(100) NOT NULL, -- 'Multiple choice', 'Word ordering', 'Error identification', 'Theoretical'
      letra CHAR(1) NOT NULL, -- 'A', 'B', 'C', 'D'
      descripcion TEXT DEFAULT NULL,
      UNIQUE KEY uq_topico_letra (id_topico, letra),
      FOREIGN KEY (id_topico) REFERENCES topico(id_topico) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: pregunta
    $db->exec("
    CREATE TABLE pregunta (
      id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
      id_seccion INT NOT NULL,
      numero INT NOT NULL UNIQUE,
      texto TEXT NOT NULL,
      FOREIGN KEY (id_seccion) REFERENCES seccion(id_seccion) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: opcion
    $db->exec("
    CREATE TABLE opcion (
      id_opcion INT AUTO_INCREMENT PRIMARY KEY,
      id_pregunta INT NOT NULL,
      letra CHAR(1) NOT NULL,
      texto TEXT NOT NULL,
      es_correcta TINYINT(1) NOT NULL DEFAULT 0,
      FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: examen_escrito
    $db->exec("
    CREATE TABLE examen_escrito (
      id_examen_escrito INT AUTO_INCREMENT PRIMARY KEY,
      id_grupo INT NOT NULL,
      id_alumno INT NOT NULL,
      parcial TINYINT NOT NULL,
      calificacion DECIMAL(4,2) DEFAULT NULL,
      fecha_presentacion DATETIME DEFAULT NULL,
      updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY uq_we (id_grupo, id_alumno, parcial),
      FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo) ON DELETE CASCADE,
      FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: examen_oral
    $db->exec("
    CREATE TABLE examen_oral (
      id_examen_oral INT AUTO_INCREMENT PRIMARY KEY,
      id_grupo INT NOT NULL,
      id_alumno INT NOT NULL,
      parcial TINYINT NOT NULL,
      calificacion DECIMAL(4,2) DEFAULT NULL,
      updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY uq_oe (id_grupo, id_alumno, parcial),
      FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo) ON DELETE CASCADE,
      FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: examen_config
    $db->exec("
    CREATE TABLE examen_config (
      id_config INT AUTO_INCREMENT PRIMARY KEY,
      id_grupo INT NOT NULL,
      parcial TINYINT NOT NULL,
      ciclo VARCHAR(10) NOT NULL,
      preguntas_t1 INT NOT NULL DEFAULT 20,
      preguntas_t2 INT NOT NULL DEFAULT 20,
      generado TINYINT(1) NOT NULL DEFAULT 0,
      UNIQUE KEY uq_group_parcial_cycle (id_grupo, parcial, ciclo),
      FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: sesion
    $db->exec("
    CREATE TABLE sesion (
      id_sesion INT AUTO_INCREMENT PRIMARY KEY,
      id_grupo INT NOT NULL,
      fecha DATE NOT NULL,
      parcial TINYINT NOT NULL,
      tema VARCHAR(120) DEFAULT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY uq_sesion_grupo_fecha (id_grupo, fecha),
      FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: asistencia
    $db->exec("
    CREATE TABLE asistencia (
      id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
      id_sesion INT NOT NULL,
      id_alumno INT NOT NULL,
      estado ENUM('asistencia','retardo','falta','justificado') NOT NULL DEFAULT 'falta',
      updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY uq_asistencia (id_sesion, id_alumno),
      FOREIGN KEY (id_sesion) REFERENCES sesion(id_sesion) ON DELETE CASCADE,
      FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: actividad
    $db->exec("
    CREATE TABLE actividad (
      id_actividad INT AUTO_INCREMENT PRIMARY KEY,
      id_grupo INT NOT NULL,
      tipo ENUM('portafolio','tarea') NOT NULL,
      parcial TINYINT     NOT NULL,
      nombre       VARCHAR(80) NOT NULL,
      orden        TINYINT     DEFAULT NULL,
      created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Tabla: calificacion_actividad
    $db->exec("
    CREATE TABLE calificacion_actividad (
      id_calificacion_actividad INT AUTO_INCREMENT PRIMARY KEY,
      id_actividad              INT          NOT NULL,
      id_alumno                 INT          NOT NULL,
      calificacion              DECIMAL(4,2) DEFAULT NULL,
      updated_at                DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY uq_cal_act (id_actividad, id_alumno),
      FOREIGN KEY (id_actividad) REFERENCES actividad(id_actividad) ON DELETE CASCADE,
      FOREIGN KEY (id_alumno)    REFERENCES alumno(id_alumno) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "   - Tablas del esquema creadas exitosamente.\n";

    // 4. SEEDING DATOS BÁSICOS
    echo "4. Insertando Ciclos, Maestros, Materias, Grupos...\n";
    
    // Ciclo
    $db->exec("INSERT INTO ciclo (codigo, activo) VALUES ('26C1', 1), ('26C2', 1)");

    // Docentes
    $stmtDoc = $db->prepare("INSERT INTO docente (matricula, nombre, apellido_pat) VALUES (:mat, :nom, :pat)");
    
    $stmtDoc->execute([':mat' => 'jrios', ':nom' => 'JOSE ISAURO', ':pat' => 'RIOS']);
    $idIsauro = $db->lastInsertId();
    
    $stmtDoc->execute([':mat' => 'arios', ':nom' => 'ALEJANDRO', ':pat' => 'RIOS']);
    $idAlejandro = $db->lastInsertId();

    // Actualizar usuarios en la tabla usuario
    $db->exec("DELETE FROM usuario WHERE username IN ('jrios', 'arios')");
    $stmtUsr = $db->prepare("INSERT INTO usuario (username, password, rol, id_referencia) VALUES (:user, :pass, 'docente', :idRef)");
    $hashedPwd = password_hash('1234', PASSWORD_DEFAULT);
    
    $stmtUsr->execute([':user' => 'jrios', ':pass' => $hashedPwd, ':idRef' => $idIsauro]);
    $stmtUsr->execute([':user' => 'arios', ':pass' => $hashedPwd, ':idRef' => $idAlejandro]);

    // Materias
    $db->exec("INSERT INTO materia (nombre, siglas, descripcion) VALUES ('Inglés VII', 'IER', 'Inglés Nivel VII'), ('Inglés VIII', 'ITEA', 'Inglés Nivel VIII')");
    
    // Grupos
    $db->exec("
    INSERT INTO grupo (id_docente, id_materia, carrera, siglas, cuatrimestre, grupo, periodo, ciclo, anio) VALUES
      ({$idIsauro}, 1, 'Ingeniería en Energías Renovables', 'IER', 8, 'A', 'ene-abr', '26C1', 2026),
      ({$idIsauro}, 2, 'Ingeniería en Tecnología Ambiental', 'ITEA', 8, 'A', 'ene-abr', '26C1', 2026)
    ");
    $idG_IER = 1;
    $idG_ITEA = 2;

    echo "   - Ciclos, maestros y materias creados.\n";

    // 5. IMPORTAR ALUMNOS DESDE EL JSON
    echo "5. Importando alumnos desde JSON...\n";
    $jsonAlumnos = file_get_contents(__DIR__ . '/alumnos.json');
    $alumnos = json_decode($jsonAlumnos, true);
    
    $stmtAlu = $db->prepare("INSERT INTO alumno (matricula, nombre, apellido_pat, apellido_mat) VALUES (:mat, :nom, :pat, :mat2)");
    $stmtGA = $db->prepare("INSERT INTO grupo_alumno (id_grupo, id_alumno) VALUES (:gid, :aid)");

    foreach ($alumnos as $a) {
        // Tratar de insertar el alumno
        try {
            $stmtAlu->execute([
                ':mat' => $a['matricula'],
                ':nom' => $a['nombre'],
                ':pat' => $a['apellido_pat'],
                ':mat2' => $a['apellido_mat']
            ]);
            $aid = $db->lastInsertId();
        } catch (Exception $e) {
            // Ya existía
            $stmtFind = $db->prepare("SELECT id_alumno FROM alumno WHERE matricula = :mat");
            $stmtFind->execute([':mat' => $a['matricula']]);
            $aid = $stmtFind->fetchColumn();
        }

        // Inscribir en el grupo
        $gid = (strpos($a['carrera'], 'Energías') !== false) ? $idG_IER : $idG_ITEA;
        try {
            $stmtGA->execute([':gid' => $gid, ':aid' => $aid]);
        } catch (Exception $e) {
            // Ya inscrito
        }
    }
    echo "   - " . count($alumnos) . " alumnos importados e inscritos.\n";

    // 6. CREAR TÓPICOS Y SECCIONES
    echo "6. Estructurando Tópicos y Secciones...\n";
    
    // Tópicos
    $db->exec("INSERT INTO topico (nombre) VALUES ('Part 1'), ('Part 2')");
    $idT_P1 = 1;
    $idT_P2 = 2;

    // Secciones
    $stmtSec = $db->prepare("INSERT INTO seccion (id_topico, nombre, letra, descripcion) VALUES (:tid, :name, :let, :desc)");
    
    // Part 1
    $stmtSec->execute([':tid' => $idT_P1, ':name' => 'Multiple choice', ':let' => 'A', ':desc' => 'Choose the most appropriate modal verb based on the degree of certainty and context provided.']);
    $sec_P1_A = $db->lastInsertId();
    $stmtSec->execute([':tid' => $idT_P1, ':name' => 'Word ordering', ':let' => 'B', ':desc' => 'Select the option that represents the correct grammatical sequence to form a logical sentence.']);
    $sec_P1_B = $db->lastInsertId();
    $stmtSec->execute([':tid' => $idT_P1, ':name' => 'Error identification', ':let' => 'C', ':desc' => 'Select the underlined option that contains a grammatical error or a logical contradiction.']);
    $sec_P1_C = $db->lastInsertId();
    $stmtSec->execute([':tid' => $idT_P1, ':name' => 'Theoretical', ':let' => 'D', ':desc' => 'Select the correct choice that corresponds to standard grammatical rules and semantic interpretations.']);
    $sec_P1_D = $db->lastInsertId();

    // Part 2
    $stmtSec->execute([':tid' => $idT_P2, ':name' => 'Multiple choice', ':let' => 'A', ':desc' => 'Choose the most appropriate modal verb based on the degree of certainty and context provided.']);
    $sec_P2_A = $db->lastInsertId();
    $stmtSec->execute([':tid' => $idT_P2, ':name' => 'Word ordering', ':let' => 'B', ':desc' => 'Select the option that represents the correct grammatical sequence to form a logical sentence.']);
    $sec_P2_B = $db->lastInsertId();
    $stmtSec->execute([':tid' => $idT_P2, ':name' => 'Error identification', ':let' => 'C', ':desc' => 'Select the underlined option that contains a grammatical error or a logical contradiction.']);
    $sec_P2_C = $db->lastInsertId();
    $stmtSec->execute([':tid' => $idT_P2, ':name' => 'Theoretical', ':let' => 'D', ':desc' => 'Select the correct choice that corresponds to standard grammatical rules and semantic interpretations.']);
    $sec_P2_D = $db->lastInsertId();

    echo "   - Tópicos y Secciones creados.\n";

    // 7. RESTURCTURAR PREGUNTAS Y OPCIONES
    echo "7. Migrando y enlazando banco de preguntas...\n";
    $stmtQ = $db->prepare("INSERT INTO pregunta (id_pregunta, id_seccion, numero, texto) VALUES (:pid, :sid, :num, :txt)");
    $stmtO = $db->prepare("INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (:pid, :let, :txt, :corr)");

    foreach ($oldPreguntas as $q) {
        // Encontrar la sección correspondiente
        $parte = $q['parte'];
        $letra = $q['seccion'];

        if ($parte == 1) {
            $sid = match ($letra) {
                'A' => $sec_P1_A,
                'B' => $sec_P1_B,
                'C' => $sec_P1_C,
                'D' => $sec_P1_D,
            };
        } else {
            $sid = match ($letra) {
                'A' => $sec_P2_A,
                'B' => $sec_P2_B,
                'C' => $sec_P2_C,
                'D' => $sec_P2_D,
            };
        }

        $stmtQ->execute([
            ':pid' => $q['id_pregunta'],
            ':sid' => $sid,
            ':num' => $q['numero'],
            ':txt' => $q['texto']
        ]);

        // Insertar opciones
        if (isset($opcionesPorPregunta[$q['id_pregunta']])) {
            foreach ($opcionesPorPregunta[$q['id_pregunta']] as $opt) {
                $stmtO->execute([
                    ':pid' => $q['id_pregunta'],
                    ':let' => $opt['letra'],
                    ':txt' => $opt['texto'],
                    ':corr' => $opt['es_correcta']
                ]);
            }
        }
    }
    echo "   - Cuestionario reestructurado con éxito.\n";
    echo "✓ MIGRACIÓN M13 COMPLETADA CON ÉXITO.\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
