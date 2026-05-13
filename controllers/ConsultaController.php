<?php
/**
 * ConsultaController — Student consultation portal (CU-05, RF-03).
 * 
 * Allows students to search by matricula and view their
 * grades and attendance in read-only mode.
 */

require_once BASE_PATH . '/config/database.php';

class ConsultaController
{
    public function index(string $page = 'consulta'): array
    {
        return ['alumnoData' => null, 'searchPerformed' => false];
    }

    /**
     * Search for a student by matricula.
     */
    public function search(): array
    {
        $matricula = trim($_POST['matricula'] ?? '');

        if (empty($matricula)) {
            return ['success' => false, 'message' => 'Ingresa tu matrícula.'];
        }

        $db = Database::getConnection();

        // Find student
        $stmtAlumno = $db->prepare("SELECT * FROM alumno WHERE matricula = :mat AND activo = 1");
        $stmtAlumno->execute([':mat' => $matricula]);
        $alumno = $stmtAlumno->fetch();

        if (!$alumno) {
            return ['success' => false, 'message' => 'Matrícula no encontrada.'];
        }

        // Find their groups
        $stmtGrupos = $db->prepare("
            SELECT g.*, d.nombre as docente_nombre, d.apellido_pat as docente_apellido
            FROM grupo g
            INNER JOIN grupo_alumno ga ON g.id_grupo = ga.id_grupo
            INNER JOIN docente d ON g.id_docente = d.id_docente
            WHERE ga.id_alumno = :aid AND g.activo = 1
            ORDER BY g.created_at DESC
        ");
        $stmtGrupos->execute([':aid' => $alumno['id_alumno']]);
        $grupos = $stmtGrupos->fetchAll();

        // For each group, get grades summary
        $groupData = [];
        foreach ($grupos as $grupo) {
            $gid = $grupo['id_grupo'];
            $aid = $alumno['id_alumno'];

            // WE grades
            $stmtWE = $db->prepare("SELECT parcial, calificacion FROM examen_escrito WHERE id_grupo = :gid AND id_alumno = :aid");
            $stmtWE->execute([':gid' => $gid, ':aid' => $aid]);
            $weGrades = [];
            foreach ($stmtWE->fetchAll() as $row) { $weGrades[$row['parcial']] = $row['calificacion']; }

            // OE grades
            $stmtOE = $db->prepare("SELECT parcial, calificacion FROM examen_oral WHERE id_grupo = :gid AND id_alumno = :aid");
            $stmtOE->execute([':gid' => $gid, ':aid' => $aid]);
            $oeGrades = [];
            foreach ($stmtOE->fetchAll() as $row) { $oeGrades[$row['parcial']] = $row['calificacion']; }

            // Attendance
            $stmtAtt = $db->prepare("
                SELECT s.parcial, a.estado
                FROM asistencia a
                INNER JOIN sesion s ON a.id_sesion = s.id_sesion
                WHERE s.id_grupo = :gid AND a.id_alumno = :aid
            ");
            $stmtAtt->execute([':gid' => $gid, ':aid' => $aid]);
            $attData = $stmtAtt->fetchAll();

            $groupData[] = [
                'grupo'    => $grupo,
                'we'       => $weGrades,
                'oe'       => $oeGrades,
                'attendance' => $attData,
            ];
        }

        return [
            'success' => true,
            'alumno'  => [
                'nombre_completo' => trim($alumno['apellido_pat'] . ' ' . ($alumno['apellido_mat'] ?? '') . ' ' . $alumno['nombre']),
                'matricula'       => $alumno['matricula'],
                'iniciales'       => mb_substr($alumno['nombre'], 0, 1) . mb_substr($alumno['apellido_pat'], 0, 1),
            ],
            'grupos'  => $groupData,
        ];
    }
}
