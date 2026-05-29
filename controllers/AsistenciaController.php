<?php
/**
 * AsistenciaController — Attendance management (RF-18/19/20/21/22).
 * 
 * Handles reading/saving attendance states and calculating the
 * "Ser" grade based on attendance percentage (RN-07).
 */

require_once BASE_PATH . '/config/database.php';

class AsistenciaController
{
    public function index(string $page = 'attendance'): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        if (!$grupoActivo) {
            return ['sesiones' => [], 'alumnos' => [], 'asistencias' => [], 'grupoActivo' => null];
        }

        $db = Database::getConnection();
        $grupoId = $grupoActivo['id_grupo'];

        // All sessions for this group
        $stmtSesiones = $db->prepare("
            SELECT * FROM sesion WHERE id_grupo = :gid ORDER BY fecha ASC
        ");
        $stmtSesiones->execute([':gid' => $grupoId]);
        $sesiones = $stmtSesiones->fetchAll();

        // All students in this group
        $stmtAlumnos = $db->prepare("
            SELECT a.id_alumno, a.matricula, a.nombre, a.apellido_pat, a.apellido_mat,
                   CONCAT(a.apellido_pat, ' ', COALESCE(a.apellido_mat, ''), ' ', a.nombre) as nombre_completo
            FROM alumno a
            INNER JOIN grupo_alumno ga ON a.id_alumno = ga.id_alumno
            WHERE ga.id_grupo = :gid
            ORDER BY a.apellido_pat, a.apellido_mat, a.nombre
        ");
        $stmtAlumnos->execute([':gid' => $grupoId]);
        $alumnos = $stmtAlumnos->fetchAll();

        // All attendance records for this group
        $stmtAsistencias = $db->prepare("
            SELECT a.id_alumno, a.id_sesion, a.estado
            FROM asistencia a
            INNER JOIN sesion s ON a.id_sesion = s.id_sesion
            WHERE s.id_grupo = :gid
        ");
        $stmtAsistencias->execute([':gid' => $grupoId]);
        $rawAsistencias = $stmtAsistencias->fetchAll();

        // Index by alumno_id -> sesion_id -> estado
        $asistencias = [];
        foreach ($rawAsistencias as $row) {
            $asistencias[$row['id_alumno']][$row['id_sesion']] = $row['estado'];
        }

        return [
            'sesiones'    => $sesiones,
            'alumnos'     => $alumnos,
            'asistencias' => $asistencias,
            'grupoActivo' => $grupoActivo,
        ];
    }

    /**
     * Initialize attendance for a session — sets all students to "asistencia" (RF-20).
     */
    public function initAttendance(): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        if (!$grupoActivo) {
            return ['success' => false, 'message' => 'No hay grupo activo.'];
        }

        $idSesion = (int)($_POST['id_sesion'] ?? 0);
        if ($idSesion <= 0) {
            return ['success' => false, 'message' => 'Sesión no válida.'];
        }

        $db = Database::getConnection();
        $grupoId = $grupoActivo['id_grupo'];

        // Get all students in this group
        $stmtAlumnos = $db->prepare("
            SELECT a.id_alumno FROM alumno a
            INNER JOIN grupo_alumno ga ON a.id_alumno = ga.id_alumno
            WHERE ga.id_grupo = :gid
        ");
        $stmtAlumnos->execute([':gid' => $grupoId]);
        $alumnos = $stmtAlumnos->fetchAll();

        $stmtInsert = $db->prepare("
            INSERT INTO asistencia (id_sesion, id_alumno, estado)
            VALUES (:sid, :aid, 'asistencia')
            ON DUPLICATE KEY UPDATE estado = 'asistencia'
        ");

        foreach ($alumnos as $alumno) {
            $stmtInsert->execute([':sid' => $idSesion, ':aid' => $alumno['id_alumno']]);
        }

        return ['success' => true, 'message' => 'Asistencia inicializada para todos los alumnos.'];
    }

    /**
     * Save individual attendance state changes (AJAX).
     */
    public function save(): array
    {
        $idSesion = (int)($_POST['id_sesion'] ?? 0);
        $idAlumno = (int)($_POST['id_alumno'] ?? 0);
        $estado   = $_POST['estado'] ?? '';

        $validStates = ['asistencia', 'retardo', 'falta', 'justificado'];

        if ($idSesion <= 0 || $idAlumno <= 0 || !in_array($estado, $validStates)) {
            return ['success' => false, 'message' => 'Datos no válidos.'];
        }

        $db = Database::getConnection();

        try {
            $stmt = $db->prepare("
                INSERT INTO asistencia (id_sesion, id_alumno, estado)
                VALUES (:sid, :aid, :estado)
                ON DUPLICATE KEY UPDATE estado = VALUES(estado), updated_at = NOW()
            ");
            $stmt->execute([
                ':sid' => $idSesion, ':aid' => $idAlumno,
                ':estado' => $estado,
            ]);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }
}
