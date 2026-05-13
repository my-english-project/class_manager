<?php
/**
 * SesionController — CRUD for class sessions (RF-12/13/14).
 * 
 * Manages date registration for the active group.
 * Prevents duplicate dates per group (RF-13).
 */

require_once BASE_PATH . '/config/database.php';

class SesionController
{
    public function index(string $page = 'sesiones'): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        if (!$grupoActivo) {
            return ['sesiones' => [], 'grupoActivo' => null];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM sesion
            WHERE id_grupo = :gid
            ORDER BY fecha ASC
        ");
        $stmt->execute([':gid' => $grupoActivo['id_grupo']]);

        return [
            'sesiones' => $stmt->fetchAll(),
            'grupoActivo' => $grupoActivo,
        ];
    }

    /**
     * Register a new class date.
     */
    public function save(): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        if (!$grupoActivo) {
            return ['success' => false, 'message' => 'No hay grupo activo.'];
        }

        $idSesion = (int)($_POST['id_sesion'] ?? 0);
        $fecha   = $_POST['fecha'] ?? '';
        $parcial = (int)($_POST['parcial'] ?? 1);
        $tema    = $_POST['tema'] ?? null;

        if (empty($fecha) || $parcial < 1 || $parcial > 3) {
            return ['success' => false, 'message' => 'Fecha y parcial son obligatorios.'];
        }

        $db = Database::getConnection();
        $grupoId = $grupoActivo['id_grupo'];

        try {
            if ($idSesion > 0) {
                // Update
                $stmt = $db->prepare("UPDATE sesion SET fecha = :fecha, parcial = :parcial, tema = :tema WHERE id_sesion = :sid");
                $stmt->execute([':fecha' => $fecha, ':parcial' => $parcial, ':tema' => $tema, ':sid' => $idSesion]);
                return ['success' => true, 'message' => 'Sesión modificada correctamente.'];
            }

            $stmt = $db->prepare("
                INSERT INTO sesion (id_grupo, fecha, parcial, tema)
                VALUES (:gid, :fecha, :parcial, :tema)
            ");
            $stmt->execute([':gid' => $grupoId, ':fecha' => $fecha, ':parcial' => $parcial, ':tema' => $tema]);
            $newId = $db->lastInsertId();

            // Auto-register attendance for all students in group
            $stmtAlumnos = $db->prepare("
                SELECT ga.id_alumno FROM grupo_alumno ga WHERE ga.id_grupo = :gid
            ");
            $stmtAlumnos->execute([':gid' => $grupoId]);
            $alumnos = $stmtAlumnos->fetchAll();

            $stmtInsert = $db->prepare("
                INSERT INTO asistencia (id_sesion, id_alumno, estado)
                VALUES (:sid, :aid, 'asistencia')
                ON DUPLICATE KEY UPDATE estado = 'asistencia'
            ");
            foreach ($alumnos as $alumno) {
                $stmtInsert->execute([':sid' => $newId, ':aid' => $alumno['id_alumno']]);
            }

            return ['success' => true, 'message' => 'Sesión registrada correctamente.'];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Ya existe una sesión para esta fecha en este grupo.'];
            }
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a session (RF-14). Only if no attendance recorded.
     */
    public function delete(): array
    {
        $idSesion = (int)($_POST['id_sesion'] ?? 0);
        if ($idSesion <= 0) {
            return ['success' => false, 'message' => 'ID de sesión no válido.'];
        }

        $db = Database::getConnection();

        try {
            $db->prepare("DELETE FROM asistencia WHERE id_sesion = :sid")->execute([':sid' => $idSesion]);
            $db->prepare("DELETE FROM sesion WHERE id_sesion = :sid")->execute([':sid' => $idSesion]);
            return ['success' => true, 'message' => 'Sesión eliminada.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }
}
