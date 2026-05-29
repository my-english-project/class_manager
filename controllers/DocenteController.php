<?php
/**
 * DocenteController — Gestión de maestros para el Administrador.
 */
require_once BASE_PATH . '/config/database.php';

class DocenteController
{
    public function index(): array
    {
        $db = Database::getConnection();
        $rol = $_SESSION['usuario']['rol'] ?? 'docente';

        if ($rol !== 'admin') {
            header('Location: index.php?page=home');
            exit;
        }

        // Listar todos los maestros activos
        $stmt = $db->query("
            SELECT d.*, u.username, u.rol as user_rol
            FROM docente d
            LEFT JOIN usuario u ON d.id_docente = u.id_referencia AND u.rol IN ('docente', 'admin')
            WHERE d.activo = 1
            ORDER BY d.nombre ASC
        ");
        $maestros = $stmt->fetchAll();

        return [
            'maestros' => $maestros
        ];
    }

    /**
     * Guarda o actualiza un maestro.
     */
    public function save(): array
    {
        $db = Database::getConnection();
        if (($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
            return ['success' => false, 'message' => 'No tiene permisos para esta acción.'];
        }

        $idDocente    = (int)($_POST['id_docente'] ?? 0);
        $nombre       = trim($_POST['nombre'] ?? '');
        $apellidoPat  = trim($_POST['apellido_pat'] ?? '');
        $apellidoMat  = trim($_POST['apellido_mat'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $matricula    = trim($_POST['matricula'] ?? '');
        $password     = $_POST['password'] ?? '';
        $rolUsuario   = $_POST['rol'] ?? 'docente';

        if (empty($nombre) || empty($email) || empty($matricula)) {
            return ['success' => false, 'message' => 'Nombre, Email y Matrícula son obligatorios.'];
        }

        try {
            if ($idDocente > 0) {
                // Actualizar docente
                $stmtD = $db->prepare("UPDATE docente SET nombre = :nombre, apellido_pat = :pat, apellido_mat = :mat, email = :email, matricula = :matri WHERE id_docente = :id");
                $stmtD->execute([
                    ':nombre' => $nombre, ':pat' => $apellidoPat, ':mat' => $apellidoMat,
                    ':email' => $email, ':matri' => $matricula, ':id' => $idDocente
                ]);
                
                // Actualizar usuario
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmtU = $db->prepare("UPDATE usuario SET password = :pass, rol = :rol WHERE id_referencia = :id AND (rol = 'docente' OR rol = 'admin')");
                    $stmtU->execute([':pass' => $hash, ':rol' => $rolUsuario, ':id' => $idDocente]);
                } else {
                    $stmtU = $db->prepare("UPDATE usuario SET rol = :rol WHERE id_referencia = :id AND (rol = 'docente' OR rol = 'admin')");
                    $stmtU->execute([':rol' => $rolUsuario, ':id' => $idDocente]);
                }
            } else {
                // Insertar nuevo docente
                $stmtD = $db->prepare("INSERT INTO docente (nombre, apellido_pat, apellido_mat, email, matricula, activo) VALUES (:nombre, :pat, :mat, :email, :matri, 1)");
                $stmtD->execute([
                    ':nombre' => $nombre, ':pat' => $apellidoPat, ':mat' => $apellidoMat,
                    ':email' => $email, ':matri' => $matricula
                ]);
                $idDocente = (int)$db->lastInsertId();

                // Crear usuario
                $pass = !empty($password) ? $password : $matricula; 
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmtU = $db->prepare("INSERT INTO usuario (username, password, rol, id_referencia) VALUES (:user, :pass, :rol, :id)");
                $stmtU->execute([
                    ':user' => $email, ':pass' => $hash, ':rol' => $rolUsuario, ':id' => $idDocente
                ]);
            }

            return ['success' => true, 'message' => 'Información del maestro guardada correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene el historial de grupos de un maestro.
     */
    public function history(): array
    {
        $db = Database::getConnection();
        $idDocente = (int)($_GET['id_docente'] ?? 0);

        if ($idDocente <= 0) {
            return ['success' => false, 'message' => 'ID de maestro no válido.'];
        }

        $stmt = $db->prepare("
            SELECT g.*, 
                   (SELECT COUNT(*) FROM grupo_alumno ga WHERE ga.id_grupo = g.id_grupo) as total_alumnos
            FROM grupo g
            WHERE g.id_docente = :id
            ORDER BY g.anio DESC, g.periodo DESC
        ");
        $stmt->execute([':id' => $idDocente]);
        $grupos = $stmt->fetchAll();

        return ['success' => true, 'grupos' => $grupos];
    }
}
