<?php
/**
 * AlumnoController — CRUD for students (RF-08/09/10/11).
 * 
 * Manages student enrollment in the active group.
 * Validates unique matricula per group (RN-11).
 */

require_once BASE_PATH . '/config/database.php';

class AlumnoController
{
    public function index(string $page = 'alumnos'): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;

        if (!$grupoActivo) {
            return ['alumnos' => [], 'grupoActivo' => null];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT a.id_alumno, a.matricula, a.nombre, a.apellido_pat, a.apellido_mat,
                   CONCAT(a.apellido_pat, ' ', COALESCE(a.apellido_mat, ''), ' ', a.nombre) as nombre_completo
            FROM alumno a
            INNER JOIN grupo_alumno ga ON a.id_alumno = ga.id_alumno
            WHERE ga.id_grupo = :gid
            ORDER BY a.apellido_pat, a.apellido_mat, a.nombre
        ");
        $stmt->execute([':gid' => $grupoActivo['id_grupo']]);

        return [
            'alumnos' => $stmt->fetchAll(),
            'grupoActivo' => $grupoActivo,
        ];
    }

    /**
     * Save a new student and enroll in the selected or active group.
     */
    public function save(): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        $idGrupo = (int)($_POST['id_grupo'] ?? ($grupoActivo['id_grupo'] ?? 0));

        if ($idGrupo <= 0) {
            return ['success' => false, 'message' => 'No hay grupo de destino seleccionado.'];
        }

        $idAlumno    = (int)($_POST['id_alumno'] ?? 0);
        $matricula   = trim($_POST['matricula'] ?? '');
        $nombre      = strtoupper(trim($_POST['nombre'] ?? ''));
        $apellidoPat = strtoupper(trim($_POST['apellido_pat'] ?? ''));
        $apellidoMat = strtoupper(trim($_POST['apellido_mat'] ?? ''));

        if (empty($matricula) || empty($nombre) || empty($apellidoPat)) {
            return ['success' => false, 'message' => 'Matrícula, nombre y apellido paterno son obligatorios.'];
        }

        $db = Database::getConnection();

        try {
            if ($idAlumno > 0) {
                // Update existing student
                $stmtUpdate = $db->prepare("
                    UPDATE alumno SET matricula = :mat, nombre = :nom, apellido_pat = :pat, apellido_mat = :mat2
                    WHERE id_alumno = :id
                ");
                $stmtUpdate->execute([
                    ':mat' => $matricula, ':nom' => $nombre,
                    ':pat' => $apellidoPat, ':mat2' => $apellidoMat ?: null,
                    ':id'  => $idAlumno
                ]);
                
                // If group is changed or if we need to update/verify group association
                // In edit mode we can also verify if they are associated with the selected group, or insert it if not
                $stmtEnrolled = $db->prepare(
                    "SELECT id_grupo_alumno FROM grupo_alumno WHERE id_grupo = :gid AND id_alumno = :aid"
                );
                $stmtEnrolled->execute([':gid' => $idGrupo, ':aid' => $idAlumno]);
                if (!$stmtEnrolled->fetch()) {
                    // Update/change their enrollment: delete from old groups of the same cycle? Or just insert new association
                    // Let's insert new association to allow enrollment
                    $stmtEnroll = $db->prepare(
                        "INSERT INTO grupo_alumno (id_grupo, id_alumno) VALUES (:gid, :aid)"
                    );
                    $stmtEnroll->execute([':gid' => $idGrupo, ':aid' => $idAlumno]);
                }

                return ['success' => true, 'message' => 'Datos del alumno actualizados.'];
            }
            // Check if student already exists by matricula
            $stmtCheck = $db->prepare("SELECT id_alumno FROM alumno WHERE matricula = :mat");
            $stmtCheck->execute([':mat' => $matricula]);
            $existing = $stmtCheck->fetch();

            if ($existing) {
                $alumnoId = $existing['id_alumno'];

                // Check if already enrolled in this group (RN-11)
                $stmtEnrolled = $db->prepare(
                    "SELECT id_grupo_alumno FROM grupo_alumno WHERE id_grupo = :gid AND id_alumno = :aid"
                );
                $stmtEnrolled->execute([':gid' => $idGrupo, ':aid' => $alumnoId]);

                if ($stmtEnrolled->fetch()) {
                    return ['success' => false, 'message' => 'Este alumno ya está inscrito en este grupo.'];
                }
            } else {
                // Create new student record
                $stmtInsert = $db->prepare("
                    INSERT INTO alumno (matricula, nombre, apellido_pat, apellido_mat)
                    VALUES (:mat, :nom, :pat, :mat2)
                ");
                $stmtInsert->execute([
                    ':mat' => $matricula, ':nom' => $nombre,
                    ':pat' => $apellidoPat, ':mat2' => $apellidoMat ?: null,
                ]);
                $alumnoId = (int)$db->lastInsertId();
            }

            // Enroll in group
            $stmtEnroll = $db->prepare(
                "INSERT INTO grupo_alumno (id_grupo, id_alumno) VALUES (:gid, :aid)"
            );
            $stmtEnroll->execute([':gid' => $idGrupo, ':aid' => $alumnoId]);

            return ['success' => true, 'message' => 'Alumno registrado correctamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }

    /**
     * Remove a student from the selected or active group (RF-10).
     * Only allowed if no grades are recorded.
     */
    public function delete(): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        $grupoId = (int)($_POST['id_grupo'] ?? ($grupoActivo['id_grupo'] ?? 0));
        
        if ($grupoId <= 0) {
            return ['success' => false, 'message' => 'Grupo de origen no válido o ausente.'];
        }

        $alumnoId = (int)($_POST['id_alumno'] ?? 0);
        if ($alumnoId <= 0) {
            return ['success' => false, 'message' => 'ID de alumno no válido.'];
        }

        $db = Database::getConnection();

        // Check for existing grades (RN-12)
        $stmtCheck = $db->prepare("
            SELECT COUNT(*) as total FROM (
                SELECT id_examen_escrito FROM examen_escrito WHERE id_grupo = :gid AND id_alumno = :aid
                UNION ALL
                SELECT id_examen_oral FROM examen_oral WHERE id_grupo = :gid2 AND id_alumno = :aid2
            ) sub
        ");
        $stmtCheck->execute([':gid' => $grupoId, ':aid' => $alumnoId, ':gid2' => $grupoId, ':aid2' => $alumnoId]);

        if ($stmtCheck->fetch()['total'] > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: el alumno tiene calificaciones registradas.'];
        }

        try {
            // Delete attendance records first
            $db->prepare("
                DELETE FROM asistencia
                USING sesion
                WHERE asistencia.id_sesion = sesion.id_sesion
                AND sesion.id_grupo = :gid AND asistencia.id_alumno = :aid
            ")->execute([':gid' => $grupoId, ':aid' => $alumnoId]);

            // Remove from group
            $db->prepare(
                "DELETE FROM grupo_alumno WHERE id_grupo = :gid AND id_alumno = :aid"
            )->execute([':gid' => $grupoId, ':aid' => $alumnoId]);

            return ['success' => true, 'message' => 'Alumno removido del grupo.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    /**
     * Reset student's password to their matricula (hashed).
     */
    public function resetPassword(): array
    {
        if (($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $idAlumno = (int)($_POST['id_alumno'] ?? 0);
        if ($idAlumno <= 0) {
            return ['success' => false, 'message' => 'ID de alumno no válido.'];
        }

        $db = Database::getConnection();

        try {
            // Get student matricula
            $stmt = $db->prepare("SELECT matricula FROM alumno WHERE id_alumno = :aid LIMIT 1");
            $stmt->execute([':aid' => $idAlumno]);
            $alumno = $stmt->fetch();

            if (!$alumno) {
                return ['success' => false, 'message' => 'Alumno no encontrado.'];
            }

            $matricula = $alumno['matricula'];
            $passwordHash = password_hash($matricula, PASSWORD_DEFAULT);

            // Check if user already exists
            $stmtUser = $db->prepare("SELECT id_usuario FROM usuario WHERE id_referencia = :aid AND rol = 'alumno' LIMIT 1");
            $stmtUser->execute([':aid' => $idAlumno]);
            $existingUser = $stmtUser->fetch();

            if ($existingUser) {
                // Update existing user password and make sure username is the matricula
                $stmtUpdate = $db->prepare("UPDATE usuario SET password = :pass, username = :username WHERE id_usuario = :uid");
                $stmtUpdate->execute([
                    ':pass' => $passwordHash,
                    ':username' => $matricula,
                    ':uid' => $existingUser['id_usuario']
                ]);
            } else {
                // Create new user record
                $stmtInsert = $db->prepare("INSERT INTO usuario (username, password, rol, id_referencia) VALUES (:username, :pass, 'alumno', :aid)");
                $stmtInsert->execute([
                    ':username' => $matricula,
                    ':pass' => $passwordHash,
                    ':aid' => $idAlumno
                ]);
            }

            return ['success' => true, 'message' => 'Contraseña restablecida con éxito a la matrícula del alumno.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al restablecer contraseña: ' . $e->getMessage()];
        }
    }
}
