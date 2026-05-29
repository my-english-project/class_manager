<?php
/**
 * MateriaController — Gestión de materias (asignaturas) para el Administrador.
 */
require_once BASE_PATH . '/config/database.php';

class MateriaController
{
    public function index(): array
    {
        $db = Database::getConnection();
        $rol = $_SESSION['usuario']['rol'] ?? 'docente';

        if ($rol !== 'admin') {
            header('Location: index.php?page=home');
            exit;
        }

        // List all active subjects
        $stmt = $db->query("
            SELECT * FROM materia 
            WHERE activo = 1 
            ORDER BY nombre ASC
        ");
        $materias = $stmt->fetchAll();

        return [
            'materias' => $materias
        ];
    }

    /**
     * Guardar o actualizar una materia.
     */
    public function save(): array
    {
        $db = Database::getConnection();
        if (($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
            return ['success' => false, 'message' => 'No tiene permisos para esta acción.'];
        }

        $idMateria    = (int)($_POST['id_materia'] ?? 0);
        $nombre       = trim($_POST['nombre'] ?? '');
        $siglas       = strtoupper(trim($_POST['siglas'] ?? ''));
        $cuatrimestre = (int)($_POST['cuatrimestre'] ?? 1);
        $descripcion  = trim($_POST['descripcion'] ?? '');

        if (empty($nombre) || empty($siglas)) {
            return ['success' => false, 'message' => 'Nombre y Siglas son campos obligatorios.'];
        }

        try {
            // Check for duplicate siglas
            if ($idMateria > 0) {
                $stmtCheck = $db->prepare("SELECT COUNT(*) FROM materia WHERE siglas = :siglas AND id_materia != :id AND activo = 1");
                $stmtCheck->execute([':siglas' => $siglas, ':id' => $idMateria]);
            } else {
                $stmtCheck = $db->prepare("SELECT COUNT(*) FROM materia WHERE siglas = :siglas AND activo = 1");
                $stmtCheck->execute([':siglas' => $siglas]);
            }

            if ($stmtCheck->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Ya existe otra materia con las mismas siglas.'];
            }

            if ($idMateria > 0) {
                // Update subject
                $stmt = $db->prepare("
                    UPDATE materia 
                    SET nombre = :nombre, siglas = :siglas, cuatrimestre = :cuat, descripcion = :desc 
                    WHERE id_materia = :id
                ");
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':siglas' => $siglas,
                    ':cuat' => $cuatrimestre,
                    ':desc' => $descripcion,
                    ':id' => $idMateria
                ]);
            } else {
                // Insert new subject
                $stmt = $db->prepare("
                    INSERT INTO materia (nombre, siglas, cuatrimestre, descripcion, activo) 
                    VALUES (:nombre, :siglas, :cuat, :desc, 1)
                ");
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':siglas' => $siglas,
                    ':cuat' => $cuatrimestre,
                    ':desc' => $descripcion
                ]);
            }

            return ['success' => true, 'message' => 'Materia guardada correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }

    /**
     * Da de baja (soft delete) una materia.
     */
    public function delete(): array
    {
        $db = Database::getConnection();
        if (($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
            return ['success' => false, 'message' => 'No tiene permisos para esta acción.'];
        }

        $idMateria = (int)($_POST['id_materia'] ?? 0);

        if ($idMateria <= 0) {
            return ['success' => false, 'message' => 'ID de materia no válido.'];
        }

        try {
            // Soft delete: set activo = 0
            $stmt = $db->prepare("UPDATE materia SET activo = 0 WHERE id_materia = :id");
            $stmt->execute([':id' => $idMateria]);

            return ['success' => true, 'message' => 'Materia dada de baja correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al dar de baja: ' . $e->getMessage()];
        }
    }
}
