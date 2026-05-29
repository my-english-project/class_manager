<?php
/**
 * GrupoController — CRUD for groups (RF-04/05/06/07).
 * 
 * Manages group creation, listing, editing, and active group selection.
 * Auto-generates the "ciclo" code based on year and period (RN-04).
 */

require_once BASE_PATH . '/config/database.php';

class GrupoController
{
    public function index(string $page = 'grupos'): array
    {
        $db = Database::getConnection();
        $docenteId = $_SESSION['docente']['id_docente'] ?? 0;

        $stmt = $db->prepare("
            SELECT g.*, 
                   (SELECT COUNT(*) FROM grupo_alumno ga WHERE ga.id_grupo = g.id_grupo) as total_alumnos
            FROM grupo g
            WHERE g.id_docente = :id AND g.activo = 1
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([':id' => $docenteId]);

        return [
            'grupos' => $stmt->fetchAll(),
            'grupoActivo' => $_SESSION['grupo_activo'] ?? null,
        ];
    }

    /**
     * Save a new group or update an existing one.
     */
    public function save(): array
    {
        $db = Database::getConnection();
        $docenteId = $_SESSION['docente']['id_docente'] ?? 0;

        $carrera      = trim($_POST['carrera'] ?? '');
        $siglas       = strtoupper(trim($_POST['siglas'] ?? ''));
        $cuatrimestre = (int)($_POST['cuatrimestre'] ?? 0);
        $grupo        = strtoupper(trim($_POST['grupo'] ?? ''));
        $periodo      = $_POST['periodo'] ?? '';
        $anio         = (int)($_POST['anio'] ?? date('Y'));
        $idGrupo      = (int)($_POST['id_grupo'] ?? 0);
        $materiaId    = (int)($_POST['id_materia'] ?? 0);
        $rol          = $_SESSION['usuario']['rol'] ?? 'docente';

        // Admin can override id_docente
        if ($rol === 'admin' && isset($_POST['id_docente'])) {
            $docenteId = (int)$_POST['id_docente'];
        }

        // Validation
        if (empty($carrera) || empty($siglas) || $cuatrimestre < 1 || $cuatrimestre > 10 || empty($grupo)) {
            return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
        }

        if (!in_array($periodo, ['ene-abr', 'may-ago', 'sep-dic'])) {
            return ['success' => false, 'message' => 'Periodo no válido.'];
        }

        // Auto-generate ciclo code (RN-04)
        $periodoNum = match ($periodo) {
            'ene-abr' => 1,
            'may-ago' => 2,
            'sep-dic' => 3,
        };
        $ciclo = substr((string)$anio, -2) . 'C' . $periodoNum;

        // Lookup or create materia by siglas if not provided
        if ($materiaId <= 0) {
            $stmtM = $db->prepare("SELECT id_materia FROM materia WHERE siglas = :siglas LIMIT 1");
            $stmtM->execute([':siglas' => $siglas]);
            $materiaId = $stmtM->fetchColumn();

            if (!$materiaId) {
                $stmtNewM = $db->prepare("INSERT INTO materia (nombre, siglas, descripcion) VALUES (:nombre, :siglas, :desc)");
                $stmtNewM->execute([
                    ':nombre' => 'Inglés — ' . $carrera,
                    ':siglas' => $siglas,
                    ':desc' => 'Materia generada automáticamente para la carrera ' . $carrera
                ]);
                $materiaId = (int)$db->lastInsertId();
            }
        }

        try {
            if ($idGrupo > 0) {
                // Update existing group
                if ($rol === 'admin') {
                    $stmt = $db->prepare("UPDATE grupo SET id_docente = :did, id_materia = :mid, carrera = :carrera, siglas = :siglas, cuatrimestre = :cuat, grupo = :grupo, periodo = :periodo, ciclo = :ciclo, anio = :anio WHERE id_grupo = :id");
                    $stmt->execute([
                        ':did' => $docenteId, ':mid' => $materiaId, ':carrera' => $carrera, ':siglas' => $siglas, ':cuat' => $cuatrimestre,
                        ':grupo' => $grupo, ':periodo' => $periodo, ':ciclo' => $ciclo,
                        ':anio' => $anio, ':id' => $idGrupo,
                    ]);
                } else {
                    $stmt = $db->prepare("UPDATE grupo SET id_materia = :mid, carrera = :carrera, siglas = :siglas, cuatrimestre = :cuat, grupo = :grupo, periodo = :periodo, ciclo = :ciclo, anio = :anio WHERE id_grupo = :id AND id_docente = :did");
                    $stmt->execute([
                        ':mid' => $materiaId, ':carrera' => $carrera, ':siglas' => $siglas, ':cuat' => $cuatrimestre,
                        ':grupo' => $grupo, ':periodo' => $periodo, ':ciclo' => $ciclo,
                        ':anio' => $anio, ':id' => $idGrupo, ':did' => $docenteId,
                    ]);
                }
            } else {
                // Insert new group
                $stmt = $db->prepare("INSERT INTO grupo (id_docente, id_materia, carrera, siglas, cuatrimestre, grupo, periodo, ciclo, anio) VALUES (:did, :mid, :carrera, :siglas, :cuat, :grupo, :periodo, :ciclo, :anio)");
                $stmt->execute([
                    ':did' => $docenteId, ':mid' => $materiaId, ':carrera' => $carrera, ':siglas' => $siglas,
                    ':cuat' => $cuatrimestre, ':grupo' => $grupo, ':periodo' => $periodo,
                    ':ciclo' => $ciclo, ':anio' => $anio,
                ]);
                $idGrupo = (int)$db->lastInsertId();
            }

            // Auto-select this group as active
            $stmtActive = $db->prepare("SELECT * FROM grupo WHERE id_grupo = :id");
            $stmtActive->execute([':id' => $idGrupo]);
            $_SESSION['grupo_activo'] = $stmtActive->fetch();

            return ['success' => true, 'message' => 'Grupo guardado correctamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }

    /**
     * Set a group as the active group for the session (RF-06).
     */
    public function setActive(): array
    {
        $db = Database::getConnection();
        $idGrupo = (int)($_POST['id_grupo'] ?? 0);

        if ($idGrupo <= 0) {
            return ['success' => false, 'message' => 'ID de grupo no válido.'];
        }

        $stmt = $db->prepare("SELECT * FROM grupo WHERE id_grupo = :id AND activo = 1");
        $stmt->execute([':id' => $idGrupo]);
        $grupo = $stmt->fetch();

        if (!$grupo) {
            return ['success' => false, 'message' => 'Grupo no encontrado.'];
        }

        $rol = $_SESSION['usuario']['rol'] ?? 'docente';
        $docenteId = $_SESSION['docente']['id_docente'] ?? 0;
        if ($rol === 'docente' && (int)$grupo['id_docente'] !== (int)$docenteId) {
            return ['success' => false, 'message' => 'No autorizado para este grupo.'];
        }

        $_SESSION['grupo_activo'] = $grupo;
        // If the cycle changes when selecting a group explicitly, update cycle as well
        if ($_SESSION['ciclo_activo'] !== $grupo['ciclo']) {
            $_SESSION['ciclo_activo'] = $grupo['ciclo'];
        }
        return ['success' => true, 'message' => 'Grupo activo: ' . $grupo['siglas'] . $grupo['cuatrimestre'] . $grupo['grupo']];
    }

    /**
     * Set active cycle (Mod 4).
     */
    public function setActiveCiclo(): array
    {
        $ciclo = trim($_POST['ciclo'] ?? '');
        if (empty($ciclo)) {
            return ['success' => false, 'message' => 'Ciclo no válido.'];
        }
        $_SESSION['ciclo_activo'] = $ciclo;
        $_SESSION['_ciclo_manual'] = true;
        if (isset($_SESSION['grupo_activo']) && $_SESSION['grupo_activo']['ciclo'] !== $ciclo) {
            unset($_SESSION['grupo_activo']);
        }
        return ['success' => true];
    }

    /**
     * Promote a group to a new cycle (Mod 4).
     */
    public function promote(): array
    {
        $db = Database::getConnection();
        $docenteId = $_SESSION['docente']['id_docente'] ?? 0;

        $idGrupoOld   = (int)($_POST['id_grupo_old'] ?? 0);
        $carrera      = trim($_POST['carrera'] ?? '');
        $siglas       = strtoupper(trim($_POST['siglas'] ?? ''));
        $cuatrimestre = (int)($_POST['cuatrimestre'] ?? 0);
        $grupo        = strtoupper(trim($_POST['grupo'] ?? ''));
        $periodo      = $_POST['periodo'] ?? '';
        $anio         = (int)($_POST['anio'] ?? date('Y'));
        $rol          = $_SESSION['usuario']['rol'] ?? 'docente';

        // Fetch original group to get its teacher
        $stmtOld = $db->prepare("SELECT id_docente FROM grupo WHERE id_grupo = :id");
        $stmtOld->execute([':id' => $idGrupoOld]);
        $oldGroup = $stmtOld->fetch();
        if (!$oldGroup) return ['success' => false, 'message' => 'Grupo original no encontrado.'];

        $targetDocenteId = $oldGroup['id_docente'];

        // Admin can override target teacher
        if ($rol === 'admin') {
            if (!isset($_POST['id_docente_new']) || empty($_POST['id_docente_new'])) {
                return ['success' => false, 'message' => 'Debes seleccionar un maestro asignado.'];
            }
            $targetDocenteId = (int)$_POST['id_docente_new'];
        }

        if ($idGrupoOld <= 0 || empty($carrera) || empty($siglas) || $cuatrimestre < 1 || $cuatrimestre > 15 || empty($grupo)) {
            return ['success' => false, 'message' => 'Datos inválidos para promover el grupo.'];
        }

        if (!in_array($periodo, ['ene-abr', 'may-ago', 'sep-dic'])) {
            return ['success' => false, 'message' => 'Periodo no válido.'];
        }

        $periodoNum = match ($periodo) {
            'ene-abr' => 1,
            'may-ago' => 2,
            'sep-dic' => 3,
        };
        $ciclo = substr((string)$anio, -2) . 'C' . $periodoNum;

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO grupo (id_docente, id_materia, carrera, siglas, cuatrimestre, grupo, periodo, ciclo, anio)
                VALUES (:did, NULL, :carrera, :siglas, :cuat, :grupo, :periodo, :ciclo, :anio)
            ");
            $stmt->execute([
                ':did' => $targetDocenteId, ':carrera' => $carrera, ':siglas' => $siglas,
                ':cuat' => $cuatrimestre, ':grupo' => $grupo, ':periodo' => $periodo,
                ':ciclo' => $ciclo, ':anio' => $anio,
            ]);
            $newGroupId = (int)$db->lastInsertId();

            $stmtCopy = $db->prepare("
                INSERT INTO grupo_alumno (id_grupo, id_alumno)
                SELECT :new_id, id_alumno FROM grupo_alumno WHERE id_grupo = :old_id
            ");
            $stmtCopy->execute([
                ':new_id' => $newGroupId,
                ':old_id' => $idGrupoOld
            ]);

            $db->commit();

            $_SESSION['ciclo_activo'] = $ciclo;
            $stmtActive = $db->prepare("SELECT * FROM grupo WHERE id_grupo = :id");
            $stmtActive->execute([':id' => $newGroupId]);
            $_SESSION['grupo_activo'] = $stmtActive->fetch();

            return ['success' => true, 'message' => 'Grupo promovido al ciclo ' . $ciclo];
        } catch (PDOException $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Error al promover: ' . $e->getMessage()];
        }
    }

    public function saveCiclo(): array
    {
        $db = Database::getConnection();
        if (($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
            return ['success' => false, 'message' => 'No tiene permisos para esta acción.'];
        }

        $idCiclo = (int)($_POST['id_ciclo'] ?? 0);
        $anio = (int)($_POST['anio'] ?? 0);
        $periodo = $_POST['periodo'] ?? '';
        $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

        if ($anio < 2020 || $anio > 2030 || !in_array($periodo, ['ene-abr', 'may-ago', 'sep-dic'])) {
            return ['success' => false, 'message' => 'Año o periodo no válido.'];
        }

        $periodoNum = match ($periodo) {
            'ene-abr' => 1,
            'may-ago' => 2,
            'sep-dic' => 3,
        };
        $codigo = substr((string)$anio, -2) . 'C' . $periodoNum;

        try {
            $db->beginTransaction();
            
            if ($idCiclo > 0) {
                // Check duplicate cycle code excluding current ID
                $stmtCheck = $db->prepare("SELECT COUNT(*) FROM ciclo WHERE codigo = :code AND id_ciclo != :id");
                $stmtCheck->execute([':code' => $codigo, ':id' => $idCiclo]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $db->rollBack();
                    return ['success' => false, 'message' => 'El periodo/ciclo ' . $codigo . ' ya está registrado.'];
                }

                if ($activo === 1) {
                    // Deactivate all other cycles
                    $db->exec("UPDATE ciclo SET activo = 0");
                }

                $stmt = $db->prepare("UPDATE ciclo SET codigo = :code, activo = :activo WHERE id_ciclo = :id");
                $stmt->execute([':code' => $codigo, ':activo' => $activo, ':id' => $idCiclo]);

                $db->commit();
                return ['success' => true, 'message' => 'Periodo/Ciclo ' . $codigo . ' actualizado correctamente.'];
            } else {
                // Check duplicate cycle code
                $stmtCheck = $db->prepare("SELECT COUNT(*) FROM ciclo WHERE codigo = :code");
                $stmtCheck->execute([':code' => $codigo]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $db->rollBack();
                    return ['success' => false, 'message' => 'El periodo/ciclo ' . $codigo . ' ya está registrado.'];
                }

                if ($activo === 1) {
                    // Deactivate all other cycles
                    $db->exec("UPDATE ciclo SET activo = 0");
                }

                $stmt = $db->prepare("INSERT INTO ciclo (codigo, activo) VALUES (:code, :activo)");
                $stmt->execute([':code' => $codigo, ':activo' => $activo]);

                $db->commit();
                return ['success' => true, 'message' => 'Periodo/Ciclo ' . $codigo . ' creado correctamente.'];
            }
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }
}
