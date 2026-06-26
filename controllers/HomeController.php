<?php
/**
 * HomeController — Dashboard data for the Home (HM) view.
 * 
 * Provides stats: total groups, students, avg attendance,
 * pass/fail distribution, and a grades summary table (RF-15/16/17).
 */

require_once BASE_PATH . '/config/database.php';

class HomeController
{
    public function index(string $page = 'home'): array
    {
        $db = Database::getConnection();
        $docenteId = $_SESSION['docente']['id_docente'] ?? 0;
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;

        $rol = $_SESSION['usuario']['rol'] ?? 'docente';

        if ($rol === 'alumno') {
            $alumnoId = $_SESSION['alumno']['id_alumno'] ?? 0;
            
            $cicloActivo = $_SESSION['ciclo_activo'] ?? '';
            
            // Fetch enrolled groups/materias for the current cycle
            $stmtGrupos = $db->prepare("
                SELECT g.*, m.nombre as materia_nombre, m.siglas as materia_siglas, d.nombre as docente_nombre, d.apellido_pat as docente_apellido
                FROM grupo g
                INNER JOIN grupo_alumno ga ON g.id_grupo = ga.id_grupo
                INNER JOIN docente d ON g.id_docente = d.id_docente
                LEFT JOIN materia m ON g.id_materia = m.id_materia
                WHERE ga.id_alumno = :aid AND g.activo = 1 AND g.ciclo = :ciclo
                ORDER BY g.created_at DESC
            ");
            $stmtGrupos->execute([':aid' => $alumnoId, ':ciclo' => $cicloActivo]);
            $enrolledGroups = $stmtGrupos->fetchAll();
            
            // Gather all detailed grades and attendance per group and parcial
            $studentData = [];
            foreach ($enrolledGroups as $g) {
                $gid = $g['id_grupo'];
                
                $parcialData = [];
                for ($p = 1; $p <= 3; $p++) {
                    // 1. Examen Escrito
                    $stmtWE = $db->prepare("SELECT calificacion FROM examen_escrito WHERE id_grupo = :gid AND id_alumno = :aid AND parcial = :p");
                    $stmtWE->execute([':gid' => $gid, ':aid' => $alumnoId, ':p' => $p]);
                    $we = $stmtWE->fetchColumn();
                    
                    // 2. Examen Oral
                    $stmtOE = $db->prepare("SELECT calificacion, id_oral_text FROM examen_oral WHERE id_grupo = :gid AND id_alumno = :aid AND parcial = :p");
                    $stmtOE->execute([':gid' => $gid, ':aid' => $alumnoId, ':p' => $p]);
                    $oeRow = $stmtOE->fetch();
                    $oe = $oeRow ? $oeRow['calificacion'] : null;
                    $idOralText = $oeRow ? $oeRow['id_oral_text'] : null;
                    
                    // 3. Portafolio Average
                    $stmtPF = $db->prepare("
                        SELECT AVG(ca.calificacion) as avg_pf 
                        FROM calificacion_actividad ca 
                        INNER JOIN actividad a ON ca.id_actividad = a.id_actividad 
                        WHERE a.id_grupo = :gid AND ca.id_alumno = :aid AND a.tipo = 'portafolio' AND a.parcial = :p
                    ");
                    $stmtPF->execute([':gid' => $gid, ':aid' => $alumnoId, ':p' => $p]);
                    $pf = $stmtPF->fetchColumn();
                    
                    // 4. Tareas Average
                    $stmtHW = $db->prepare("
                        SELECT AVG(ca.calificacion) as avg_hw 
                        FROM calificacion_actividad ca 
                        INNER JOIN actividad a ON ca.id_actividad = a.id_actividad 
                        WHERE a.id_grupo = :gid AND ca.id_alumno = :aid AND a.tipo = 'tarea' AND a.parcial = :p
                    ");
                    $stmtHW->execute([':gid' => $gid, ':aid' => $alumnoId, ':p' => $p]);
                    $hw = $stmtHW->fetchColumn();
                    
                    // 5. Asistencias percentage and totals
                    $stmtAtt = $db->prepare("
                        SELECT 
                            SUM(CASE WHEN a.estado = 'asistencia' THEN 1.0 WHEN a.estado = 'justificado' THEN 1.0 WHEN a.estado = 'retardo' THEN 0.5 ELSE 0 END) as present,
                            COUNT(*) as total
                        FROM asistencia a
                        INNER JOIN sesion s ON a.id_sesion = s.id_sesion
                        WHERE s.id_grupo = :gid AND a.id_alumno = :aid AND s.parcial = :p
                    ");
                    $stmtAtt->execute([':gid' => $gid, ':aid' => $alumnoId, ':p' => $p]);
                    $att = $stmtAtt->fetch();

                    // 6. Examen Escrito Generado/Habilitado por el Docente
                    $stmtConf = $db->prepare("SELECT generado FROM examen_config WHERE id_grupo = :gid AND parcial = :p AND ciclo = :ciclo");
                    $stmtConf->execute([':gid' => $gid, ':p' => $p, ':ciclo' => $g['ciclo']]);
                    $isGenerated = (int)$stmtConf->fetchColumn();
                    
                    $parcialData[$p] = [
                        'we' => $we !== false && $we !== null ? (float)$we : null,
                        'oe' => $oe !== false && $oe !== null ? (float)$oe : null,
                        'id_oral_text' => $idOralText,
                        'pf' => $pf !== null ? round((float)$pf, 2) : null,
                        'hw' => $hw !== null ? round((float)$hw, 2) : null,
                        'att_present' => $att ? (float)$att['present'] : 0,
                        'att_total' => $att ? (int)$att['total'] : 0,
                        'exam_generated' => $isGenerated,
                    ];
                }
                
                $studentData[] = [
                    'grupo' => $g,
                    'parciales' => $parcialData
                ];
            }
            
            return [
                'userRol' => 'alumno',
                'alumno' => $_SESSION['alumno'],
                'studentData' => $studentData
            ];
        }

        // Total groups - Admin sees all, Teacher sees only theirs
        $sqlGroups = "SELECT COUNT(*) as total FROM grupo WHERE activo = 1";
        if ($rol !== 'admin') {
            $sqlGroups .= " AND id_docente = :id";
        }
        $stmtGroups = $db->prepare($sqlGroups);
        if ($rol !== 'admin') {
            $stmtGroups->execute([':id' => $docenteId]);
        } else {
            $stmtGroups->execute();
        }
        $totalGroups = $stmtGroups->fetch()['total'];

        // Total students across groups
        $sqlStudents = "
            SELECT COUNT(DISTINCT ga.id_alumno) as total
            FROM grupo_alumno ga
            INNER JOIN grupo g ON ga.id_grupo = g.id_grupo
            WHERE g.activo = 1
        ";
        if ($rol !== 'admin') {
            $sqlStudents .= " AND g.id_docente = :id";
        }
        $stmtStudents = $db->prepare($sqlStudents);
        if ($rol !== 'admin') {
            $stmtStudents->execute([':id' => $docenteId]);
        } else {
            $stmtStudents->execute();
        }
        $totalStudents = $stmtStudents->fetch()['total'];

        // Average attendance percentage for active group
        $avgAttendance = 0;
        $passCount = 0;
        $failCount = 0;
        $alumnos = [];

        $cicloActivo = $_SESSION['ciclo_activo'] ?? '';
        
        // Fetch distinct cycles from the master cycle table
        $stmtCiclos = $db->query("SELECT codigo FROM ciclo ORDER BY codigo DESC");
        $ciclosDisponibles = $stmtCiclos->fetchAll(PDO::FETCH_COLUMN);

        // --- SMART CYCLE SELECTION ---
        // 1. If we have an active group, sync cycle
        if ($grupoActivo && isset($grupoActivo['ciclo']) && $cicloActivo !== $grupoActivo['ciclo']) {
            $cicloActivo = $grupoActivo['ciclo'];
            $_SESSION['ciclo_activo'] = $cicloActivo;
        }

        // 2. The active cycle is determined by the current date (or manual selection) and does not auto-fallback.
        $manuallySelected = $_SESSION['_ciclo_manual'] ?? false;
        // -----------------------------
        
        // Ensure the active cycle is in the list for the UI
        if (!in_array($cicloActivo, $ciclosDisponibles) && !empty($cicloActivo)) {
            array_unshift($ciclosDisponibles, $cicloActivo);
        }

        // Fetch groups to display in dashboard matching the active cycle
        $sqlGrupos = "
            SELECT g.*, 
                (SELECT COUNT(*) FROM grupo_alumno ga WHERE ga.id_grupo = g.id_grupo) as total_alumnos 
            FROM grupo g 
            WHERE g.ciclo = :ciclo AND g.activo = 1 
        ";
        $paramsGrupos = [':ciclo' => $cicloActivo];
        if ($rol !== 'admin') {
            $sqlGrupos .= " AND g.id_docente = :id";
            $paramsGrupos[':id'] = $docenteId;
        }
        $sqlGrupos .= " ORDER BY g.siglas ASC, g.cuatrimestre ASC, g.grupo ASC";
        
        $stmtGrupos = $db->prepare($sqlGrupos);
        $stmtGrupos->execute($paramsGrupos);
        $grupos = $stmtGrupos->fetchAll();

        if (!$grupoActivo && count($grupos) > 0) {
            $grupoActivo = $grupos[0];
            $_SESSION['grupo_activo'] = $grupoActivo;
        }

        // Average attendance
        $sqlAtt = "
            SELECT 
                COUNT(CASE WHEN a.estado = 'asistencia' THEN 1 END) as present,
                COUNT(CASE WHEN a.estado = 'justificado' THEN 1 END) as justified,
                COUNT(CASE WHEN a.estado = 'retardo' THEN 1 END) as late,
                COUNT(*) as total
            FROM asistencia a
            INNER JOIN sesion s ON a.id_sesion = s.id_sesion
            INNER JOIN grupo g ON s.id_grupo = g.id_grupo
            WHERE g.ciclo = :ciclo AND g.activo = 1
        ";
        $paramsAtt = [':ciclo' => $cicloActivo];
        if ($rol !== 'admin') {
            $sqlAtt .= " AND g.id_docente = :did";
            $paramsAtt[':did'] = $docenteId;
        }
        
        $stmtAtt = $db->prepare($sqlAtt);
        $stmtAtt->execute($paramsAtt);
        $attData = $stmtAtt->fetch();

        if ($attData['total'] > 0) {
            $avgAttendance = round(
                (($attData['present'] + $attData['justified'] + ($attData['late'] * 0.5)) / $attData['total']) * 100,
                1
            );
        }

        // Get student grades summary across ALL active groups
        foreach ($grupos as $g) {
            $alumnosGrupo = $this->getStudentGradesSummary($db, $g['id_grupo']);
            foreach ($alumnosGrupo as $alumno) {
                if (isset($alumno['promedio_final']) && $alumno['promedio_final'] !== null) {
                    if ($alumno['promedio_final'] >= 7.00) {
                        $passCount++;
                    } else {
                        $failCount++;
                    }
                }
            }
        }

        // Fetch all teachers if admin (for group assignment)
        $docentes = [];
        if ($rol === 'admin') {
            $stmtD = $db->query("SELECT id_docente, nombre, apellido_pat FROM docente WHERE activo = 1 ORDER BY nombre ASC");
            $docentes = $stmtD->fetchAll();
        }

        return [
            'totalGroups'   => $totalGroups,
            'totalStudents' => $totalStudents,
            'avgAttendance' => $avgAttendance,
            'passCount'     => $passCount,
            'failCount'     => $failCount,
            'grupos'        => $grupos,
            'grupoActivo'   => $grupoActivo,
            'cicloActivo'   => $cicloActivo,
            'ciclosDisponibles' => $ciclosDisponibles,
            'docentes'      => $docentes,
            'userRol'       => $rol
        ];
    }

    /**
     * Get summary of student grades (all 3 parciales + final average).
     */
    private function getStudentGradesSummary($db, int $grupoId): array
    {
        // 1. Fetch all students for the group
        $stmt = $db->prepare("
            SELECT a.id_alumno, a.matricula, a.nombre, a.apellido_pat, a.apellido_mat
            FROM alumno a
            INNER JOIN grupo_alumno ga ON a.id_alumno = ga.id_alumno
            WHERE ga.id_grupo = :gid
            ORDER BY a.apellido_pat, a.apellido_mat, a.nombre
        ");
        $stmt->execute([':gid' => $grupoId]);
        $students = $stmt->fetchAll();

        if (empty($students)) return [];

        // 2. Fetch all grades in bulk for this group
        $stmtWE = $db->prepare("SELECT id_alumno, parcial, calificacion FROM examen_escrito WHERE id_grupo = :gid");
        $stmtWE->execute([':gid' => $grupoId]);
        $weRows = $stmtWE->fetchAll();

        $stmtOE = $db->prepare("SELECT id_alumno, parcial, calificacion FROM examen_oral WHERE id_grupo = :gid");
        $stmtOE->execute([':gid' => $grupoId]);
        $oeRows = $stmtOE->fetchAll();

        // 3. Map grades to arrays for easy access: [alumno_id][parcial] = calificacion
        $weMap = [];
        foreach ($weRows as $row) {
            $weMap[$row['id_alumno']][$row['parcial']] = (float)$row['calificacion'];
        }

        $oeMap = [];
        foreach ($oeRows as $row) {
            $oeMap[$row['id_alumno']][$row['parcial']] = (float)$row['calificacion'];
        }

        // 4. Process students in memory
        foreach ($students as &$student) {
            $aid = $student['id_alumno'];
            $student['nombre_completo'] = trim(
                $student['apellido_pat'] . ' ' . ($student['apellido_mat'] ?? '') . ' ' . $student['nombre']
            );

            $parciales = [];
            for ($p = 1; $p <= 3; $p++) {
                $we = $weMap[$aid][$p] ?? null;
                $oe = $oeMap[$aid][$p] ?? null;
                
                $student["we_p{$p}"] = $we;
                $student["oe_p{$p}"] = $oe;

                if ($we !== null || $oe !== null) {
                    $weVal = $we ?? 0;
                    $oeVal = $oe ?? 0;
                    // Approximation of parcial grade for dashboard
                    $parciales[$p] = ($weVal * 0.30) + ($oeVal * 0.36) + ($oeVal * 0.24) + (10 * 0.10);
                }
            }

            $student['promedio_final'] = count($parciales) > 0
                ? round(array_sum($parciales) / count($parciales), 2)
                : null;
        }

        return $students;
    }
}
