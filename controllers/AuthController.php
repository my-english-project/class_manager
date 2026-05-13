<?php
/**
 * AuthController — Handles login, registration, password update and logout.
 */
require_once BASE_PATH . '/config/database.php';

class AuthController
{
    public function login(): array
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            return ['success' => false, 'message' => 'Por favor, ingrese usuario y contraseña.'];
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM usuario WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $usuario = $stmt->fetch();

            if (!$usuario || !password_verify($password, $usuario['password'])) {
                return ['success' => false, 'message' => 'Usuario o contraseña incorrectos.'];
            }

            $_SESSION['logged_in'] = true;
            $_SESSION['usuario'] = $usuario;

            if ($usuario['rol'] === 'docente') {
                $stmtDoc = $db->prepare("SELECT * FROM docente WHERE id_docente = :id LIMIT 1");
                $stmtDoc->execute([':id' => $usuario['id_referencia']]);
                $_SESSION['docente'] = $stmtDoc->fetch();

                $stmtGrupo = $db->prepare(
                    "SELECT * FROM grupo WHERE id_docente = :id AND activo = 1 ORDER BY created_at DESC LIMIT 1"
                );
                $stmtGrupo->execute([':id' => $usuario['id_referencia']]);
                $grupo = $stmtGrupo->fetch();
                if ($grupo) {
                    $_SESSION['grupo_activo'] = $grupo;
                }
                
                return ['success' => true, 'redirect' => 'index.php?page=home'];
            } else {
                $stmtAlu = $db->prepare("SELECT * FROM alumno WHERE id_alumno = :id LIMIT 1");
                $stmtAlu->execute([':id' => $usuario['id_referencia']]);
                $_SESSION['alumno'] = $stmtAlu->fetch();

                return ['success' => true, 'redirect' => 'index.php?page=consulta'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    public function register(): array
    {
        $matricula = trim($_POST['matricula'] ?? '');
        if (!$matricula) {
            return ['success' => false, 'message' => 'La matrícula es requerida.'];
        }

        try {
            $db = Database::getConnection();
            $expectedUsername = $matricula . '@utsalamanca.edu.mx';
            
            $stmtUsr = $db->prepare("SELECT id_usuario FROM usuario WHERE username = :username LIMIT 1");
            $stmtUsr->execute([':username' => $expectedUsername]);
            if ($stmtUsr->fetch()) {
                return ['success' => false, 'message' => 'Este usuario ya está registrado.'];
            }

            $stmtDoc = $db->prepare("SELECT id_docente FROM docente WHERE matricula = :mat LIMIT 1");
            $stmtDoc->execute([':mat' => $matricula]);
            $docente = $stmtDoc->fetch();
            
            $rol = null;
            $idRef = null;

            if ($docente) {
                $rol = 'docente';
                $idRef = $docente['id_docente'];
            } else {
                $stmtAlu = $db->prepare("SELECT id_alumno FROM alumno WHERE matricula = :mat LIMIT 1");
                $stmtAlu->execute([':mat' => $matricula]);
                $alumno = $stmtAlu->fetch();
                if ($alumno) {
                    $rol = 'alumno';
                    $idRef = $alumno['id_alumno'];
                }
            }

            if (!$rol) {
                return ['success' => false, 'message' => 'Matrícula no encontrada en el sistema.'];
            }

            $passwordHash = password_hash($matricula, PASSWORD_DEFAULT);
            $stmtInsert = $db->prepare("INSERT INTO usuario (username, password, rol, id_referencia) VALUES (:username, :password, :rol, :idRef)");
            $stmtInsert->execute([
                ':username' => $expectedUsername,
                ':password' => $passwordHash,
                ':rol' => $rol,
                ':idRef' => $idRef
            ]);

            return ['success' => true, 'message' => 'Usuario registrado exitosamente. Tu contraseña temporal es tu misma matrícula.'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    public function updatePassword(): array
    {
        if (empty($_SESSION['logged_in']) || empty($_SESSION['usuario'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $newPassword = $_POST['new_password'] ?? '';
        if (strlen($newPassword) < 4) {
            return ['success' => false, 'message' => 'La contraseña debe ser mayor a 3 caracteres.'];
        }

        try {
            $db = Database::getConnection();
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuario SET password = :pass WHERE id_usuario = :id");
            $stmt->execute([
                ':pass' => $hash,
                ':id' => $_SESSION['usuario']['id_usuario']
            ]);
            
            // Auto update session for immediate effect if we verified password during login, but no need here
            return ['success' => true, 'message' => 'Contraseña actualizada correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    public function logout(): array
    {
        session_destroy();
        return ['success' => true, 'redirect' => 'index.php?page=login'];
    }
}
