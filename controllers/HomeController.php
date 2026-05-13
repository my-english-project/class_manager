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

        // Total groups for this teacher
        $stmtGroups = $db->prepare("SELECT COUNT(*) as total FROM grupo WHERE id_docente = :id AND activo = 1");
        $stmtGroups->execute([':id' => $docenteId]);
        $totalGroups = $stmtGroups->fetch()['total'];

        // Total students across all teacher's groups
        $stmtStudents = $db->prepare("
            SELECT COUNT(DISTINCT ga.id_alumno) as total
            FROM grupo_alumno ga
            INNER JOIN grupo g ON ga.id_grupo = g.id_grupo
            WHERE g.id_docente = :id AND g.activo = 1
        ");
        $stmtStudents->execute([':id' => $docenteId]);
        $totalStudents = $stmtStudents->fetch()['total'];

        // Average attendance percentage for active group
        $avgAttendance = 0;
        $passCount = 0;
        $failCount = 0;
        $alumnos = [];

        $cicloActivo = $_SESSION['ciclo_activo'] ?? '';
        
        // Fetch distinct cycles for the widget
        $stmtCiclos = $db->prepare("SELECT DISTINCT ciclo FROM grupo WHERE id_docente = :id ORDER BY ciclo DESC");
        $stmtCiclos->execute([':id' => $docenteId]);
        $ciclosDisponibles = $stmtCiclos->fetchAll(PDO::FETCH_COLUMN);
        
        // Ensure the active cycle is in the list, even if it has no groups
        if (!in_array($cicloActivo, $ciclosDisponibles) && !empty($cicloActivo)) {
            array_unshift($ciclosDisponibles, $cicloActivo);
        }

        // Fetch groups to display in dashboard matching the active cycle
        $stmtGrupos = $db->prepare("
            SELECT g.*, 
                (SELECT COUNT(*) FROM grupo_alumno ga WHERE ga.id_grupo = g.id_grupo) as total_alumnos 
            FROM grupo g 
            WHERE g.id_docente = :id AND g.ciclo = :ciclo AND g.activo = 1 
            ORDER BY g.siglas ASC, g.cuatrimestre ASC, g.grupo ASC
        ");
        $stmtGrupos->execute([':id' => $docenteId, ':ciclo' => $cicloActivo]);
        $grupos = $stmtGrupos->fetchAll();

        if (!$grupoActivo && count($grupos) > 0) {
            $grupoActivo = $grupos[0];
            $_SESSION['grupo_activo'] = $grupoActivo;
        }

        // Average attendance across ALL active groups of this teacher
        $stmtAtt = $db->prepare("
            SELECT 
                COUNT(CASE WHEN a.estado = 'asistencia' THEN 1 END) as present,
                COUNT(CASE WHEN a.estado = 'justificado' THEN 1 END) as justified,
                COUNT(CASE WHEN a.estado = 'retardo' THEN 1 END) as late,
                COUNT(*) as total
            FROM asistencia a
            INNER JOIN sesion s ON a.id_sesion = s.id_sesion
            INNER JOIN grupo g ON s.id_grupo = g.id_grupo
            WHERE g.id_docente = :did AND g.ciclo = :ciclo AND g.activo = 1
        ");
        $stmtAtt->execute([':did' => $docenteId, ':ciclo' => $cicloActivo]);
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
        ];
    }

    /**
     * Get summary of student grades (all 3 parciales + final average).
     */
    private function getStudentGradesSummary(PDO $db, int $grupoId): array
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
