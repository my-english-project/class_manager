<?php
/**
 * CalificacionController — Grades management for all evaluation modules.
 * 
 * Handles WE, OE, PF, HW views + EX summary + SITO format.
 * Implements business rules RN-07 through RN-09 for weighted calculations.
 */

require_once BASE_PATH . '/config/database.php';

class CalificacionController
{
    public function index(string $page = ''): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        if (!$grupoActivo) {
            return ['alumnos' => [], 'grupoActivo' => null];
        }

        $db = Database::getConnection();
        $grupoId = $grupoActivo['id_grupo'];

        // Base student list
        $alumnos = $this->getStudents($db, $grupoId);

        $data = [
            'alumnos'     => $alumnos,
            'grupoActivo' => $grupoActivo,
        ];

        switch ($page) {
            case 'write_exam':
                $data['grades'] = $this->getExamGrades($db, $grupoId, 'examen_escrito');
                break;
            case 'oral_exam':
                $data['grades'] = $this->getExamGrades($db, $grupoId, 'examen_oral');
                break;
            case 'portfolio':
                $data['activities'] = $this->getActivities($db, $grupoId, 'portafolio');
                $data['activityGrades'] = $this->getActivityGrades($db, $grupoId, 'portafolio');
                break;
            case 'homework':
                $data['activities'] = $this->getActivities($db, $grupoId, 'tarea');
                $data['activityGrades'] = $this->getActivityGrades($db, $grupoId, 'tarea');
                break;
            case 'exam':
            case 'sito':
                $data['examSummary'] = $this->getExamSummary($db, $grupoId, $alumnos);
                break;
        }

        return $data;
    }

    /**
     * Get ordered student list for the active group.
     */
    private function getStudents(PDO $db, int $grupoId): array
    {
        $stmt = $db->prepare("
            SELECT a.id_alumno, a.matricula, a.nombre, a.apellido_pat, a.apellido_mat,
                   CONCAT(a.apellido_pat, ' ', COALESCE(a.apellido_mat, ''), ' ', a.nombre) as nombre_completo
            FROM alumno a
            INNER JOIN grupo_alumno ga ON a.id_alumno = ga.id_alumno
            WHERE ga.id_grupo = :gid
            ORDER BY a.apellido_pat, a.apellido_mat, a.nombre
        ");
        $stmt->execute([':gid' => $grupoId]);
        return $stmt->fetchAll();
    }

    /**
     * Get exam grades (WE or OE) indexed by alumno_id -> parcial -> calificacion.
     */
    private function getExamGrades(PDO $db, int $grupoId, string $table): array
    {
        $stmt = $db->prepare("
            SELECT id_alumno, parcial, calificacion FROM {$table}
            WHERE id_grupo = :gid
        ");
        $stmt->execute([':gid' => $grupoId]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['id_alumno']][$row['parcial']] = $row['calificacion'];
        }
        return $indexed;
    }

    /**
     * Get activity definitions for a group and type.
     */
    private function getActivities(PDO $db, int $grupoId, string $tipo): array
    {
        $stmt = $db->prepare("
            SELECT * FROM actividad
            WHERE id_grupo = :gid AND tipo = :tipo
            ORDER BY parcial, orden, nombre
        ");
        $stmt->execute([':gid' => $grupoId, ':tipo' => $tipo]);
        return $stmt->fetchAll();
    }

    /**
     * Get activity grades indexed by alumno_id -> actividad_id -> calificacion.
     */
    private function getActivityGrades(PDO $db, int $grupoId, string $tipo): array
    {
        $stmt = $db->prepare("
            SELECT ca.id_alumno, ca.id_actividad, ca.calificacion
            FROM calificacion_actividad ca
            INNER JOIN actividad act ON ca.id_actividad = act.id_actividad
            WHERE act.id_grupo = :gid AND act.tipo = :tipo
        ");
        $stmt->execute([':gid' => $grupoId, ':tipo' => $tipo]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['id_alumno']][$row['id_actividad']] = $row['calificacion'];
        }
        return $indexed;
    }

    /**
     * Build the exam summary with weighted calculations (RN-07/08/09).
     * Returns per-student: WE, OE, PF, AT per parcial + final grade.
     */
    private function getExamSummary(PDO $db, int $grupoId, array $alumnos): array
    {
        $weGrades = $this->getExamGrades($db, $grupoId, 'examen_escrito');
        $oeGrades = $this->getExamGrades($db, $grupoId, 'examen_oral');

        // Portfolio averages
        $pfActivities = $this->getActivities($db, $grupoId, 'portafolio');
        $pfGrades = $this->getActivityGrades($db, $grupoId, 'portafolio');

        // Homework averages
        $hwActivities = $this->getActivities($db, $grupoId, 'tarea');
        $hwGrades = $this->getActivityGrades($db, $grupoId, 'tarea');

        // Attendance (Ser) grades
        $attGrades = $this->calculateAttendanceGrades($db, $grupoId);

        $summary = [];

        foreach ($alumnos as $alumno) {
            $aid = $alumno['id_alumno'];
            $studentData = [
                'id_alumno'       => $aid,
                'matricula'       => $alumno['matricula'],
                'nombre_completo' => $alumno['nombre_completo'],
            ];

            $parciales = [];

            for ($p = 1; $p <= 3; $p++) {
                $we = isset($weGrades[$aid][$p]) ? (float)$weGrades[$aid][$p] : null;
                $oe = isset($oeGrades[$aid][$p]) ? (float)$oeGrades[$aid][$p] : null;

                // Calculate PF avg for this parcial
                $pfSum = 0;
                $pfCount = 0;
                foreach ($pfActivities as $act) {
                    if ($act['parcial'] == $p && isset($pfGrades[$aid][$act['id_actividad']])) {
                        $pfSum += (float)$pfGrades[$aid][$act['id_actividad']];
                        $pfCount++;
                    }
                }
                $pfAvg = $pfCount > 0 ? $pfSum / $pfCount : null;

                // Calculate HW avg for this parcial
                $hwSum = 0;
                $hwCount = 0;
                foreach ($hwActivities as $act) {
                    if ($act['parcial'] == $p && isset($hwGrades[$aid][$act['id_actividad']])) {
                        $hwSum += (float)$hwGrades[$aid][$act['id_actividad']];
                        $hwCount++;
                    }
                }
                $hwAvg = $hwCount > 0 ? $hwSum / $hwCount : null;

                // Combine PF and HW for the portfolio component
                $portfolioTotal = null;
                if ($pfAvg !== null || $hwAvg !== null) {
                    $portfolioTotal = (($pfAvg ?? 0) + ($hwAvg ?? 0)) / max(1, ($pfAvg !== null ? 1 : 0) + ($hwAvg !== null ? 1 : 0));
                }

                // AT for this parcial
                $at = $attGrades[$aid][$p] ?? null;

                $studentData["at_p{$p}"] = $at;
                $studentData["we_p{$p}"] = $we;
                $studentData["oe_p{$p}"] = $oe;
                $studentData["hw_p{$p}"] = $hwAvg;
                $studentData["pf_p{$p}"] = $pfAvg;

                // Calculate weighted parcial grade (RN-09 Updated)
                // Parcial = WE × 0.30 + OE × 0.36 + HW × 0.12 + PF × 0.12 + AT × 0.10
                // We default missing values to 0 except AT which defaults to 10 if no sessions? 
                // Actually if a component is null, it typically counts as 0 unless there are no activities.
                
                if ($we !== null || $oe !== null || $hwAvg !== null || $pfAvg !== null) {
                    $weVal = $we ?? 0;
                    $oeVal = $oe ?? 0;
                    $hwVal = $hwAvg ?? 0;
                    $pfVal = $pfAvg ?? 0;
                    $atVal = $at ?? 10;

                    $parcialGrade = ($weVal * 0.30) + ($oeVal * 0.36) + ($hwVal * 0.12) + ($pfVal * 0.12) + ($atVal * 0.10);
                    $parcialGrade = round($parcialGrade, 2);

                    $studentData["parcial_p{$p}"] = $parcialGrade;
                    $parciales[] = $parcialGrade;
                } else {
                    $studentData["parcial_p{$p}"] = null;
                }
            }

            // Final grade = average of available parciales (RN-09)
            $studentData['final'] = count($parciales) > 0
                ? round(array_sum($parciales) / count($parciales), 2)
                : null;

            // SITO format: Ser, Saber, Hacer as separate totals
            $studentData['sito_ser'] = isset($attGrades[$aid]) ? round(array_sum($attGrades[$aid]) / count($attGrades[$aid]), 1) : null;

            $saberSum = 0;
            $saberCount = 0;
            $hacerSum = 0;
            $hacerCount = 0;
            for ($p = 1; $p <= 3; $p++) {
                if (isset($weGrades[$aid][$p])) {
                    $saberSum += (float)$weGrades[$aid][$p];
                    $saberCount++;
                }
                $oeVal = isset($oeGrades[$aid][$p]) ? (float)$oeGrades[$aid][$p] : null;
                $pfVal = $studentData["pf_p{$p}"];
                if ($oeVal !== null || $pfVal !== null) {
                    $hacer = (($oeVal ?? 0) * 0.60 + ($pfVal ?? 0) * 0.40);
                    $hacerSum += $hacer;
                    $hacerCount++;
                }
            }
            $studentData['sito_saber'] = $saberCount > 0 ? round($saberSum / $saberCount, 1) : null;
            $studentData['sito_hacer'] = $hacerCount > 0 ? round($hacerSum / $hacerCount, 1) : null;

            $summary[] = $studentData;
        }

        return $summary;
    }

    /**
     * Calculate attendance "Ser" grade per student per parcial (RN-07).
     * Ser = (asistencias + 0.5*retardos + justificados) / total_sesiones × 10
     */
    private function calculateAttendanceGrades(PDO $db, int $grupoId): array
    {
        $stmt = $db->prepare("
            SELECT a.id_alumno, s.parcial, a.estado
            FROM asistencia a
            INNER JOIN sesion s ON a.id_sesion = s.id_sesion
            WHERE s.id_grupo = :gid
        ");
        $stmt->execute([':gid' => $grupoId]);
        $rows = $stmt->fetchAll();

        // Count per alumno per parcial
        $counts = [];
        foreach ($rows as $row) {
            $key = $row['id_alumno'] . '_' . $row['parcial'];
            if (!isset($counts[$key])) {
                $counts[$key] = ['present' => 0, 'late' => 0, 'absent' => 0, 'justified' => 0, 'total' => 0];
            }
            $counts[$key][$row['estado'] === 'asistencia' ? 'present' :
                ($row['estado'] === 'retardo' ? 'late' :
                ($row['estado'] === 'justificado' ? 'justified' : 'absent'))]++;
            $counts[$key]['total']++;
        }

        $grades = [];
        foreach ($counts as $key => $data) {
            [$alumnoId, $parcial] = explode('_', $key);
            if ($data['total'] > 0) {
                $score = ($data['present'] + ($data['late'] * 0.5) + $data['justified']) / $data['total'] * 10;
                $grades[(int)$alumnoId][(int)$parcial] = round($score, 2);
            }
        }

        return $grades;
    }

    /* ─── AJAX Handlers ─────────────────────────────────────── */

    /**
     * Save grades for WE/OE (AJAX).
     */
    public function saveGrades(): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        if (!$grupoActivo) {
            return ['success' => false, 'message' => 'No hay grupo activo.'];
        }

        $db = Database::getConnection();
        $grupoId = $grupoActivo['id_grupo'];
        $type    = $_POST['type'] ?? '';
        $grades  = $_POST['grades'] ?? [];

        $tableMap = [
            'we' => 'examen_escrito',
            'oe' => 'examen_oral',
        ];

        if (!isset($tableMap[$type])) {
            // Handle activity grades (PF/HW)
            return $this->saveActivityGrades($db, $grades);
        }

        $table = $tableMap[$type];
        $idCol = 'id_' . $table;

        try {
            foreach ($grades as $grade) {
                $alumnoId = (int)($grade['id_alumno'] ?? 0);
                $parcial  = (int)($grade['parcial'] ?? 0);
                $valor    = $grade['calificacion'];

                if ($alumnoId <= 0 || $parcial < 1 || $parcial > 3) {
                    continue;
                }

                // Validate range (RN-02)
                if ($valor !== '' && $valor !== null) {
                    $valor = (float)$valor;
                    if ($valor < 0 || $valor > 10) {
                        continue;
                    }
                    $valor = round($valor, 2);
                } else {
                    $valor = null;
                }

                $stmt = $db->prepare("
                    INSERT INTO {$table} (id_grupo, id_alumno, parcial, calificacion)
                    VALUES (:gid, :aid, :p, :cal)
                    ON CONFLICT (id_grupo, id_alumno, parcial) 
                    DO UPDATE SET calificacion = EXCLUDED.calificacion, updated_at = NOW()
                ");
                $stmt->execute([
                    ':gid' => $grupoId, ':aid' => $alumnoId,
                    ':p' => $parcial, ':cal' => $valor, ':cal2' => $valor,
                ]);
            }

            return ['success' => true, 'message' => 'Calificaciones guardadas correctamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }

    /**
     * Save activity grades (PF/HW).
     */
    private function saveActivityGrades(PDO $db, array $grades): array
    {
        try {
            foreach ($grades as $grade) {
                $actId    = (int)($grade['id_actividad'] ?? 0);
                $alumnoId = (int)($grade['id_alumno'] ?? 0);
                $valor    = $grade['calificacion'];

                if ($actId <= 0 || $alumnoId <= 0) {
                    continue;
                }

                if ($valor !== '' && $valor !== null) {
                    $valor = min(10, max(0, round((float)$valor, 2)));
                } else {
                    $valor = null;
                }

                $stmt = $db->prepare("
                    INSERT INTO calificacion_actividad (id_actividad, id_alumno, calificacion)
                    VALUES (:actid, :aid, :cal)
                    ON CONFLICT (id_actividad, id_alumno)
                    DO UPDATE SET calificacion = EXCLUDED.calificacion, updated_at = NOW()
                ");
                $stmt->execute([
                    ':actid' => $actId, ':aid' => $alumnoId,
                    ':cal' => $valor, ':cal2' => $valor,
                ]);
            }

            return ['success' => true, 'message' => 'Calificaciones de actividad guardadas.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Create a new activity (PF or HW) for the active group.
     */
    public function saveActivity(): array
    {
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        if (!$grupoActivo) {
            return ['success' => false, 'message' => 'No hay grupo activo.'];
        }

        $db = Database::getConnection();
        $grupoId = $grupoActivo['id_grupo'];
        $nombre  = trim($_POST['nombre'] ?? '');
        $tipo    = $_POST['tipo'] ?? '';
        $parcial = (int)($_POST['parcial'] ?? 1);

        $idActividad = (int)($_POST['id_actividad'] ?? 0);

        if (empty($nombre) || !in_array($tipo, ['portafolio', 'tarea'])) {
            return ['success' => false, 'message' => 'Nombre y tipo de actividad son obligatorios.'];
        }

        try {
            if ($idActividad > 0) {
                // Update
                $stmt = $db->prepare("UPDATE actividad SET nombre = :nombre, parcial = :parcial WHERE id_actividad = :aid");
                $stmt->execute([':nombre' => $nombre, ':parcial' => $parcial, ':aid' => $idActividad]);
                return ['success' => true, 'message' => 'Actividad modificada correctamente.'];
            }

            // Get next order number
            $stmtOrder = $db->prepare("
                SELECT COALESCE(MAX(orden), 0) + 1 as next_orden
                FROM actividad WHERE id_grupo = :gid AND tipo = :tipo AND parcial = :p
            ");
            $stmtOrder->execute([':gid' => $grupoId, ':tipo' => $tipo, ':p' => $parcial]);
            $nextOrden = $stmtOrder->fetch()['next_orden'];

            $stmt = $db->prepare("
                INSERT INTO actividad (id_grupo, tipo, parcial, nombre, orden)
                VALUES (:gid, :tipo, :p, :nombre, :orden)
            ");
            $stmt->execute([
                ':gid' => $grupoId, ':tipo' => $tipo, ':p' => $parcial,
                ':nombre' => $nombre, ':orden' => $nextOrden,
            ]);

            return ['success' => true, 'message' => 'Actividad creada correctamente.', 'id' => $db->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete an activity (RF-31). Only if no grades recorded.
     */
    public function deleteActivity(): array
    {
        $idActividad = (int)($_POST['id_actividad'] ?? 0);
        if ($idActividad <= 0) {
            return ['success' => false, 'message' => 'ID no válido.'];
        }

        $db = Database::getConnection();

        $stmtCheck = $db->prepare("SELECT COUNT(*) as total FROM calificacion_actividad WHERE id_actividad = :id");
        $stmtCheck->execute([':id' => $idActividad]);

        if ($stmtCheck->fetch()['total'] > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: existen calificaciones registradas.'];
        }

        try {
            $db->prepare("DELETE FROM actividad WHERE id_actividad = :id")->execute([':id' => $idActividad]);
            return ['success' => true, 'message' => 'Actividad eliminada.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
