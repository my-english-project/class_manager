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
                $stmtConf = $db->prepare("SELECT parcial, generado, preguntas_t1, preguntas_t2, duracion, distribucion_preguntas FROM examen_config WHERE id_grupo = :gid AND ciclo = :ciclo");
                $stmtConf->execute([':gid' => $grupoId, ':ciclo' => $_SESSION['ciclo_activo']]);
                $configs = $stmtConf->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
                
                // For each config, decode or construct the default distribution JSON
                foreach ($configs as $parcialKey => &$cfg) {
                    if (empty($cfg['distribucion_preguntas'])) {
                        $cfg['distribucion_preguntas'] = json_encode([
                            "1" => (int)$cfg['preguntas_t1'],
                            "2" => (int)$cfg['preguntas_t2']
                        ]);
                    }
                }
                unset($cfg); // break the reference
                
                $data['examConfigs'] = $configs;
                
                // Fetch topics
                $data['topics'] = $db->query("SELECT * FROM topico ORDER BY id_topico")->fetchAll();

                // Fetch other groups of this teacher for publishing options
                $stmtGrupos = $db->prepare("SELECT id_grupo, siglas, cuatrimestre, grupo FROM grupo WHERE id_docente = :did AND ciclo = :ciclo AND activo = 1 ORDER BY siglas, cuatrimestre, grupo");
                $stmtGrupos->execute([':did' => $_SESSION['docente']['id_docente'] ?? 0, ':ciclo' => $_SESSION['ciclo_activo']]);
                $data['docenteGrupos'] = $stmtGrupos->fetchAll();
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
    private function getStudents($db, int $grupoId): array
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
    private function getExamGrades($db, int $grupoId, string $table): array
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
    private function getActivities($db, int $grupoId, string $tipo): array
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
    private function getActivityGrades($db, int $grupoId, string $tipo): array
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
    private function getExamSummary($db, int $grupoId, array $alumnos): array
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
    private function calculateAttendanceGrades($db, int $grupoId): array
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
                    ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion), updated_at = NOW()
                ");
                $stmt->execute([
                    ':gid' => $grupoId, ':aid' => $alumnoId,
                    ':p' => $parcial, ':cal' => $valor,
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
    private function saveActivityGrades($db, array $grades): array
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
                    ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion), updated_at = NOW()
                ");
                $stmt->execute([
                    ':actid' => $actId, ':aid' => $alumnoId,
                    ':cal' => $valor,
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

    /**
     * Submit and auto-grade the written exam (AJAX).
     */
    public function submitWrittenExam(): array
    {
        if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['alumno', 'docente', 'admin'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $isTestRun = in_array($_SESSION['usuario']['rol'], ['docente', 'admin']);
        $alumnoId = $isTestRun ? 0 : (int)($_SESSION['alumno']['id_alumno'] ?? 0);
        $grupoId = (int)($_POST['id_grupo'] ?? 0);
        $parcial = (int)($_POST['parcial'] ?? 0);
        $answers = $_POST['answers'] ?? []; // Array of question_id => selected_option_id

        if (!$isTestRun && $alumnoId <= 0) {
            return ['success' => false, 'message' => 'Datos de examen no válidos (Alumno no identificado).'];
        }
        if ($grupoId <= 0 || $parcial < 1 || $parcial > 3) {
            return ['success' => false, 'message' => 'Datos de examen no válidos.'];
        }

        $db = Database::getConnection();

        try {
            if (!$isTestRun) {
                // Check if student already has a grade for this exam
                $stmtCheck = $db->prepare("SELECT id_examen_escrito FROM examen_escrito WHERE id_grupo = :gid AND id_alumno = :aid AND parcial = :p");
                $stmtCheck->execute([':gid' => $grupoId, ':aid' => $alumnoId, ':p' => $parcial]);
                if ($stmtCheck->fetch()) {
                    return ['success' => false, 'message' => 'Ya has presentado este examen y tu calificación ya fue guardada.'];
                }
            }

            // Retrieve the correct option for all questions to compare
            $correctCount = 0;
            
            $stmtConf = $db->prepare("SELECT preguntas_t1, preguntas_t2 FROM examen_config WHERE id_grupo = :gid AND parcial = :p");
            $stmtConf->execute([':gid' => $grupoId, ':p' => $parcial]);
            $config = $stmtConf->fetch();
            $totalQuestions = $config ? ((int)$config['preguntas_t1'] + (int)$config['preguntas_t2']) : 40;

            if (!empty($answers)) {
                $questionIds = array_map('intval', array_keys($answers));
                
                // Fetch correct option ids for these questions
                $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
                $stmtCorrect = $db->prepare("
                    SELECT id_pregunta, id_opcion 
                    FROM opcion 
                    WHERE id_pregunta IN ($placeholders) AND es_correcta = 1
                ");
                $stmtCorrect->execute($questionIds);
                $correctAnswers = $stmtCorrect->fetchAll(PDO::FETCH_KEY_PAIR); // returns [id_pregunta => id_opcion]

                foreach ($answers as $qId => $optId) {
                    $qId = (int)$qId;
                    $optId = (int)$optId;
                    if (isset($correctAnswers[$qId]) && (int)$correctAnswers[$qId] === $optId) {
                        $correctCount++;
                    }
                }
            }

            // Calculate grade out of 10.0
            $grade = round(($correctCount / $totalQuestions) * 10, 2);

            if (!$isTestRun) {
                // Insert score in the database
                $stmtSave = $db->prepare("
                    INSERT INTO examen_escrito (id_grupo, id_alumno, parcial, calificacion, fecha_presentacion)
                    VALUES (:gid, :aid, :p, :cal, NOW())
                    ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion), fecha_presentacion = VALUES(fecha_presentacion), updated_at = NOW()
                ");
                $stmtSave->execute([
                    ':gid' => $grupoId,
                    ':aid' => $alumnoId,
                    ':p' => $parcial,
                    ':cal' => $grade
                ]);
            }

            return [
                'success' => true,
                'message' => $isTestRun ? 'Examen calificado con éxito (Prueba de Docente).' : 'Examen guardado y calificado con éxito.',
                'score' => $correctCount,
                'total' => $totalQuestions,
                'grade' => $grade
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al guardar examen: ' . $e->getMessage()];
        }
    }

    /**
     * Enable/Disable (Generate) the online written exam for a group and parcial (AJAX).
     */
    public function toggleWrittenExam(): array
    {
        if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['docente', 'admin'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $grupoId = (int)($_POST['id_grupo'] ?? 0);
        $parcial = (int)($_POST['parcial'] ?? 0);
        $generado = (int)($_POST['generado'] ?? 0);
        $duracion = (int)($_POST['duracion'] ?? 50);
        $targetGroups = $_POST['target_groups'] ?? [$grupoId];
        $ciclo = $_SESSION['ciclo_activo'] ?? '';
        
        // Dynamic distribution
        $distribucion = $_POST['distribucion'] ?? []; // Map of topic_id => count
        // Default to empty array if not passed, but we sanitize values
        $sanitizedDist = [];
        foreach ($distribucion as $tid => $val) {
            $sanitizedDist[(int)$tid] = max(0, (int)$val);
        }
        $distribucionJson = json_encode($sanitizedDist);
        
        // Backward compatibility columns preguntas_t1 and preguntas_t2
        $preguntasT1 = (int)($sanitizedDist[1] ?? $_POST['preguntas_t1'] ?? 20);
        $preguntasT2 = (int)($sanitizedDist[2] ?? $_POST['preguntas_t2'] ?? 20);

        if (empty($targetGroups) || $parcial < 1 || $parcial > 3 || empty($ciclo)) {
            return ['success' => false, 'message' => 'Parámetros no válidos.'];
        }

        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO examen_config (id_grupo, parcial, ciclo, generado, preguntas_t1, preguntas_t2, duracion, distribucion_preguntas)
                VALUES (:gid, :p, :ciclo, :gen, :t1, :t2, :dur, :dist)
                ON DUPLICATE KEY UPDATE generado = VALUES(generado), preguntas_t1 = VALUES(preguntas_t1), preguntas_t2 = VALUES(preguntas_t2), duracion = VALUES(duracion), distribucion_preguntas = VALUES(distribucion_preguntas)
            ");

            foreach ($targetGroups as $gId) {
                $stmt->execute([
                    ':gid' => (int)$gId,
                    ':p' => $parcial,
                    ':ciclo' => $ciclo,
                    ':gen' => $generado,
                    ':t1' => $preguntasT1,
                    ':t2' => $preguntasT2,
                    ':dur' => $duracion,
                    ':dist' => $distribucionJson
                ]);
            }

            $db->commit();

            $msg = $generado ? 'Examen habilitado con éxito para el Parcial ' . $parcial : 'Examen deshabilitado para el Parcial ' . $parcial;
            return ['success' => true, 'message' => $msg];
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Error al configurar examen: ' . $e->getMessage()];
        }
    }
}
