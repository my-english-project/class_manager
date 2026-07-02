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
        $db = Database::getConnection();
        $grupoActivo = $_SESSION['grupo_activo'] ?? null;
        
        if ($page === 'take_homework') {
            $actId = (int)($_GET['id_actividad'] ?? 0);
            $stmtAct = $db->prepare("SELECT * FROM actividad WHERE id_actividad = :aid LIMIT 1");
            $stmtAct->execute([':aid' => $actId]);
            $act = $stmtAct->fetch();
            
            $questions = [];
            if ($act) {
                if (!empty($act['distribucion_preguntas'])) {
                    $dist = json_decode($act['distribucion_preguntas'], true);
                    if (is_array($dist)) {
                        foreach ($dist as $tid => $qty) {
                            $stmtSections = $db->prepare("SELECT id_seccion FROM seccion WHERE id_topico = :tid ORDER BY letra");
                            $stmtSections->execute([':tid' => (int)$tid]);
                            $sections = $stmtSections->fetchAll();

                            if (empty($sections)) continue;

                            $limitTopic = (int)$qty;
                            $sectionsCount = count($sections);
                            $limitPerSection = (int)ceil($limitTopic / max(1, $sectionsCount));

                            $alumnoId = (int)($_SESSION['alumno']['id_alumno'] ?? 0);
                            
                            // Fetch historically answered question IDs for this topic by this student
                            $stmtHistory = $db->prepare("
                                SELECT ca.preguntas_respondidas 
                                FROM calificacion_actividad ca
                                INNER JOIN actividad a ON ca.id_actividad = a.id_actividad
                                WHERE ca.id_alumno = :alu AND a.tipo = 'tarea' AND a.id_topico = :tid AND ca.preguntas_respondidas IS NOT NULL
                            ");
                            $stmtHistory->execute([':alu' => $alumnoId, ':tid' => (int)$tid]);
                            $historyRecords = $stmtHistory->fetchAll(PDO::FETCH_COLUMN);
                            
                            $excludedQuestionIds = [];
                            foreach ($historyRecords as $record) {
                                $arr = json_decode($record, true);
                                if (is_array($arr)) {
                                    foreach ($arr as $qid) {
                                        $excludedQuestionIds[] = (int)$qid;
                                    }
                                }
                            }
                            $excludedQuestionIds = array_unique($excludedQuestionIds);

                            $questionsTopic = [];
                            foreach ($sections as $sec) {
                                $notInSql = '';
                                if (!empty($excludedQuestionIds)) {
                                    $notInSql = "AND p.id_pregunta NOT IN (" . implode(',', $excludedQuestionIds) . ")";
                                }

                                $stmt = $db->prepare("
                                    SELECT p.*, s.nombre AS seccion_nombre, s.letra AS seccion_letra, s.id_topico
                                    FROM pregunta p
                                    INNER JOIN seccion s ON p.id_seccion = s.id_seccion
                                    WHERE s.id_seccion = :sid $notInSql
                                    ORDER BY RAND()
                                    LIMIT :lim
                                ");
                                $stmt->bindValue(':sid', $sec['id_seccion'], PDO::PARAM_INT);
                                $stmt->bindValue(':lim', $limitPerSection, PDO::PARAM_INT);
                                $stmt->execute();
                                $fetchedQs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Fallback: if there are not enough remaining questions to satisfy the limit, reuse some from the topic
                                if (count($fetchedQs) < $limitPerSection) {
                                    $missing = $limitPerSection - count($fetchedQs);
                                    $excludePlaceholders = '';
                                    if (!empty($fetchedQs)) {
                                        $alreadyFetched = array_column($fetchedQs, 'id_pregunta');
                                        $excludePlaceholders = "AND p.id_pregunta NOT IN (" . implode(',', $alreadyFetched) . ")";
                                    }
                                    
                                    $stmtFallback = $db->prepare("
                                        SELECT p.*, s.nombre AS seccion_nombre, s.letra AS seccion_letra, s.id_topico
                                        FROM pregunta p
                                        INNER JOIN seccion s ON p.id_seccion = s.id_seccion
                                        WHERE s.id_seccion = :sid $excludePlaceholders
                                        ORDER BY RAND()
                                        LIMIT :lim
                                    ");
                                    $stmtFallback->bindValue(':sid', $sec['id_seccion'], PDO::PARAM_INT);
                                    $stmtFallback->bindValue(':lim', $missing, PDO::PARAM_INT);
                                    $stmtFallback->execute();
                                    $fetchedQs = array_merge($fetchedQs, $stmtFallback->fetchAll(PDO::FETCH_ASSOC));
                                }

                                $questionsTopic = array_merge($questionsTopic, $fetchedQs);
                            }
                            shuffle($questionsTopic);
                            $questionsTopic = array_slice($questionsTopic, 0, $limitTopic);
                            $questions = array_merge($questions, $questionsTopic);
                        }
                        shuffle($questions);
                    }
                } elseif ($act['id_topico']) {
                    $stmtQ = $db->prepare("
                        SELECT p.*, s.nombre AS seccion_nombre, s.letra AS seccion_letra, s.id_topico
                        FROM pregunta p
                        INNER JOIN seccion s ON p.id_seccion = s.id_seccion
                        WHERE s.id_topico = :tid
                        ORDER BY p.id_pregunta
                    ");
                    $stmtQ->execute([':tid' => $act['id_topico']]);
                    $questions = $stmtQ->fetchAll();
                }

                foreach ($questions as &$q) {
                    $stmtO = $db->prepare("SELECT * FROM opcion WHERE id_pregunta = :qid ORDER BY letra");
                    $stmtO->execute([':qid' => $q['id_pregunta']]);
                    $q['opciones'] = $stmtO->fetchAll();
                }
                unset($q);
            }
            
            return [
                'actividad' => $act ?: null,
                'questions' => $questions
            ];
        }

        if ($page === 'take_oral_exam') {
            $alumnoId = $_SESSION['alumno']['id_alumno'] ?? 0;
            $parcial = (int)($_GET['parcial'] ?? 0);
            $grupoIdVal = (int)($_GET['id_grupo'] ?? 0);
            
            if ($grupoIdVal <= 0 && $grupoActivo) {
                $grupoIdVal = $grupoActivo['id_grupo'];
            }
            
            $stmtVar = $db->prepare("
                SELECT txt.id_oral_text, txt.texto, txt.titulo, t.nombre AS topic_name, g.siglas, g.cuatrimestre, g.grupo
                FROM examen_oral eo
                INNER JOIN examen_oral_texto txt ON eo.id_oral_text = txt.id_oral_text
                INNER JOIN examen_oral_tema t ON txt.id_oral_topic = t.id_oral_topic
                INNER JOIN grupo g ON eo.id_grupo = g.id_grupo
                WHERE eo.id_grupo = :gid AND eo.id_alumno = :aid AND eo.parcial = :p
            ");
            $stmtVar->execute([':gid' => $grupoIdVal, ':aid' => $alumnoId, ':p' => $parcial]);
            $assigned_text = $stmtVar->fetch();
            
            return [
                'assigned_text' => $assigned_text ?: null,
                'parcial' => $parcial
            ];
        }

        if (!$grupoActivo) {
            return ['alumnos' => [], 'grupoActivo' => null];
        }

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
                
                // Fetch topics with question counts
                $data['topics'] = $db->query("
                    SELECT t.id_topico, t.nombre, COUNT(p.id_pregunta) AS total_preguntas
                    FROM topico t
                    LEFT JOIN seccion s ON t.id_topico = s.id_topico
                    LEFT JOIN pregunta p ON s.id_seccion = p.id_seccion
                    GROUP BY t.id_topico, t.nombre
                    ORDER BY t.id_topico ASC
                ")->fetchAll();

                // Fetch other groups of this teacher for publishing options
                $stmtGrupos = $db->prepare("SELECT id_grupo, siglas, cuatrimestre, grupo FROM grupo WHERE id_docente = :did AND ciclo = :ciclo AND activo = 1 ORDER BY siglas, cuatrimestre, grupo");
                $stmtGrupos->execute([':did' => $_SESSION['docente']['id_docente'] ?? 0, ':ciclo' => $_SESSION['ciclo_activo']]);
                $data['docenteGrupos'] = $stmtGrupos->fetchAll();
                break;
            case 'oral_exam':
                $data['grades'] = $this->getExamGrades($db, $grupoId, 'examen_oral');
                
                // Fetch assigned texts details
                $stmtAssigned = $db->prepare("
                    SELECT eo.id_alumno, eo.parcial, eo.id_oral_text, t.nombre AS topic_name, txt.texto AS text_content, txt.titulo AS text_title
                    FROM examen_oral eo
                    LEFT JOIN examen_oral_texto txt ON eo.id_oral_text = txt.id_oral_text
                    LEFT JOIN examen_oral_tema t ON txt.id_oral_topic = t.id_oral_topic
                    WHERE eo.id_grupo = :gid
                ");
                $stmtAssigned->execute([':gid' => $grupoId]);
                $assignedList = $stmtAssigned->fetchAll();
                $assigned = [];
                foreach ($assignedList as $row) {
                    $assigned[$row['id_alumno']][$row['parcial']] = [
                        'id_oral_text' => $row['id_oral_text'],
                        'topic_name' => $row['topic_name'],
                        'text_content' => $row['text_content'],
                        'text_title' => $row['text_title']
                    ];
                }
                $data['assigned'] = $assigned;
                
                // Fetch topics
                $data['topics'] = $db->query("SELECT * FROM examen_oral_tema ORDER BY id_oral_topic DESC")->fetchAll();
                break;

            case 'oral_exam_review':
                // Teacher evaluates a specific student's oral exam
                $idAlumno = (int)($_GET['id_alumno'] ?? 0);
                $parcial = (int)($_GET['parcial'] ?? 0);
                
                $stmtStudent = $db->prepare("SELECT id_alumno, matricula, CONCAT(apellido_pat, ' ', COALESCE(apellido_mat, ''), ' ', nombre) AS nombre_completo FROM alumno WHERE id_alumno = :aid");
                $stmtStudent->execute([':aid' => $idAlumno]);
                $data['student'] = $stmtStudent->fetch();
                $data['parcial'] = $parcial;
                
                $stmtVar = $db->prepare("
                    SELECT txt.id_oral_text, txt.texto, txt.titulo, t.nombre AS topic_name
                    FROM examen_oral eo
                    INNER JOIN examen_oral_texto txt ON eo.id_oral_text = txt.id_oral_text
                    INNER JOIN examen_oral_tema t ON txt.id_oral_topic = t.id_oral_topic
                    WHERE eo.id_grupo = :gid AND eo.id_alumno = :aid AND eo.parcial = :p
                ");
                $stmtVar->execute([':gid' => $grupoId, ':aid' => $idAlumno, ':p' => $parcial]);
                $data['assigned_text'] = $stmtVar->fetch();
                break;

            case 'take_oral_exam':
                // Student view of their assigned oral exam
                $alumnoId = $_SESSION['alumno']['id_alumno'] ?? 0;
                $parcial = (int)($_GET['parcial'] ?? 0);
                $grupoIdVal = (int)($_GET['id_grupo'] ?? 0);
                
                if ($grupoIdVal <= 0 && $grupoActivo) {
                    $grupoIdVal = $grupoActivo['id_grupo'];
                }
                
                $stmtVar = $db->prepare("
                    SELECT txt.id_oral_text, txt.texto, txt.titulo, t.nombre AS topic_name, g.siglas, g.cuatrimestre, g.grupo
                    FROM examen_oral eo
                    INNER JOIN examen_oral_texto txt ON eo.id_oral_text = txt.id_oral_text
                    INNER JOIN examen_oral_tema t ON txt.id_oral_topic = t.id_oral_topic
                    INNER JOIN grupo g ON eo.id_grupo = g.id_grupo
                    WHERE eo.id_grupo = :gid AND eo.id_alumno = :aid AND eo.parcial = :p
                ");
                $stmtVar->execute([':gid' => $grupoIdVal, ':aid' => $alumnoId, ':p' => $parcial]);
                $data['assigned_text'] = $stmtVar->fetch();
                $data['parcial'] = $parcial;
                break;
            case 'portfolio':
                $data['activities'] = $this->getActivities($db, $grupoId, 'portafolio');
                $data['activityGrades'] = $this->getActivityGrades($db, $grupoId, 'portafolio');
                break;
            case 'homework':
                $data['activities'] = $this->getActivities($db, $grupoId, 'tarea');
                $data['activityGrades'] = $this->getActivityGrades($db, $grupoId, 'tarea');
                $data['topics'] = $db->query("
                    SELECT t.id_topico, t.nombre, COUNT(p.id_pregunta) AS total_preguntas
                    FROM topico t
                    LEFT JOIN seccion s ON t.id_topico = s.id_topico
                    LEFT JOIN pregunta p ON s.id_seccion = p.id_seccion
                    GROUP BY t.id_topico, t.nombre
                    ORDER BY t.id_topico DESC
                ")->fetchAll();
                
                $stmtGrupos = $db->prepare("SELECT id_grupo, siglas, cuatrimestre, grupo FROM grupo WHERE id_docente = :did AND ciclo = :ciclo AND activo = 1 ORDER BY siglas, cuatrimestre, grupo");
                $stmtGrupos->execute([':did' => $_SESSION['docente']['id_docente'] ?? 0, ':ciclo' => $_SESSION['ciclo_activo']]);
                $data['docenteGrupos'] = $stmtGrupos->fetchAll();
                break;
            case 'quiz_lab':
                $data['topics'] = $db->query("
                    SELECT t.id_topico, t.nombre, COUNT(p.id_pregunta) AS total_preguntas
                    FROM topico t
                    LEFT JOIN seccion s ON t.id_topico = s.id_topico
                    LEFT JOIN pregunta p ON s.id_seccion = p.id_seccion
                    GROUP BY t.id_topico, t.nombre
                    ORDER BY t.id_topico DESC
                ")->fetchAll();
                $data['activities'] = $db->query("SELECT * FROM actividad WHERE id_grupo = {$grupoId} AND tipo = 'tarea' ORDER BY parcial, orden")->fetchAll();
                $data['oral_topics'] = $db->query("SELECT * FROM examen_oral_tema ORDER BY id_oral_topic DESC")->fetchAll();
                
                $stmtGrupos = $db->prepare("SELECT id_grupo, siglas, cuatrimestre, grupo FROM grupo WHERE id_docente = :did AND ciclo = :ciclo AND activo = 1 ORDER BY siglas, cuatrimestre, grupo");
                $stmtGrupos->execute([':did' => $_SESSION['docente']['id_docente'] ?? 0, ':ciclo' => $_SESSION['ciclo_activo']]);
                $data['docenteGrupos'] = $stmtGrupos->fetchAll();
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
        
        $distribucion = $_POST['distribucion'] ?? [];
        $sanitizedDist = [];
        $primaryTopicId = null;
        foreach ($distribucion as $tid => $qty) {
            $qtyVal = (int)$qty;
            if ($qtyVal > 0) {
                $sanitizedDist[(int)$tid] = $qtyVal;
                if ($primaryTopicId === null) {
                    $primaryTopicId = (int)$tid;
                }
            }
        }
        $distJson = !empty($sanitizedDist) ? json_encode($sanitizedDist) : null;
        $targetGroups = $_POST['target_groups'] ?? [$grupoId];

        if (empty($nombre) || !in_array($tipo, ['portafolio', 'tarea'])) {
            return ['success' => false, 'message' => 'Nombre y tipo de actividad son obligatorios.'];
        }

        try {
            if ($idActividad > 0) {
                // Update
                $stmt = $db->prepare("
                    UPDATE actividad 
                    SET nombre = :nombre, parcial = :parcial, id_topico = :tid, distribucion_preguntas = :dist 
                    WHERE id_actividad = :aid
                ");
                $stmt->execute([
                    ':nombre' => $nombre, 
                    ':parcial' => $parcial, 
                    ':tid' => $primaryTopicId,
                    ':dist' => $distJson,
                    ':aid' => $idActividad
                ]);
                return ['success' => true, 'message' => 'Actividad modificada correctamente.'];
            }

            // Insert for each target group
            $db->beginTransaction();
            
            $stmtOrder = $db->prepare("
                SELECT COALESCE(MAX(orden), 0) + 1 as next_orden
                FROM actividad WHERE id_grupo = :gid AND tipo = :tipo AND parcial = :p
            ");
            
            $stmtInsert = $db->prepare("
                INSERT INTO actividad (id_grupo, tipo, parcial, nombre, orden, id_topico, distribucion_preguntas)
                VALUES (:gid, :tipo, :p, :nombre, :orden, :tid, :dist)
            ");

            foreach ($targetGroups as $gId) {
                $stmtOrder->execute([':gid' => (int)$gId, ':tipo' => $tipo, ':p' => $parcial]);
                $nextOrden = $stmtOrder->fetch()['next_orden'];
                
                $stmtInsert->execute([
                    ':gid' => (int)$gId,
                    ':tipo' => $tipo,
                    ':p' => $parcial,
                    ':nombre' => $nombre,
                    ':orden' => $nextOrden,
                    ':tid' => $primaryTopicId,
                    ':dist' => $distJson
                ]);
            }

            $db->commit();
            return ['success' => true, 'message' => 'Actividad creada correctamente.'];
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

    /**
     * Save a new oral exam topic and parse its markdown paragraphs (AJAX).
     */
    public function saveOralTopic(): array
    {
        if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['docente', 'admin'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $markdownText = trim($_POST['markdown_text'] ?? '');

        if (empty($nombre) || empty($markdownText)) {
            return ['success' => false, 'message' => 'El nombre del tema y el contenido son obligatorios.'];
        }

        // Split blocks by blank lines (double newlines)
        $blocks = preg_split('/\r?\n\s*\r?\n/', $markdownText);
        $blocks = array_filter(array_map('trim', $blocks));

        $variants = [];
        foreach ($blocks as $block) {
            $lines = explode("\n", $block);
            $lines = array_filter(array_map('trim', $lines));
            
            if (empty($lines)) continue;
            
            // First line is the title (e.g. "1. Volcanoes")
            $title = array_shift($lines);
            
            // Remaining lines joined by space is the body text
            $body = implode(" ", $lines);
            $body = trim($body);
            
            if (!empty($body)) {
                $variants[] = [
                    'title' => $title,
                    'text' => $body
                ];
            }
        }

        if (empty($variants)) {
            return ['success' => false, 'message' => 'No se encontraron textos válidos en el contenido.'];
        }

        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            // Insert topic
            $stmtTopic = $db->prepare("INSERT INTO examen_oral_tema (nombre, markdown_text) VALUES (:nom, :md)");
            $stmtTopic->execute([':nom' => $nombre, ':md' => $markdownText]);
            $topicId = $db->lastInsertId();

            // Insert paragraph variants
            $stmtText = $db->prepare("INSERT INTO examen_oral_texto (id_oral_topic, parrafo_num, texto, titulo) VALUES (:tid, :pnum, :txt, :title)");
            $pnum = 1;
            foreach ($variants as $var) {
                $stmtText->execute([
                    ':tid' => $topicId,
                    ':pnum' => $pnum++,
                    ':txt' => $var['text'],
                    ':title' => $var['title']
                ]);
            }

            $db->commit();
            return ['success' => true, 'message' => 'Tema de examen oral guardado con éxito con ' . count($variants) . ' variantes.'];
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Error al guardar tema: ' . $e->getMessage()];
        }
    }

    /**
     * Randomly and balancedly assign oral exam paragraphs to a group of students (AJAX).
     */
    public function assignOralExam(): array
    {
        if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['docente', 'admin'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $grupoId = (int)($_POST['id_grupo'] ?? 0);
        $parcial = (int)($_POST['parcial'] ?? 0);
        $topicId = (int)($_POST['id_oral_topic'] ?? 0);

        if ($grupoId <= 0 || $parcial < 1 || $parcial > 3 || $topicId <= 0) {
            return ['success' => false, 'message' => 'Parámetros de asignación no válidos.'];
        }

        $db = Database::getConnection();

        try {
            // Fetch all student IDs in the group (ordered by name)
            $students = $this->getStudents($db, $grupoId);
            if (empty($students)) {
                return ['success' => false, 'message' => 'El grupo seleccionado no tiene alumnos inscritos.'];
            }

            // Fetch paragraph variants in numerical order
            $stmtTexts = $db->prepare("SELECT id_oral_text FROM examen_oral_texto WHERE id_oral_topic = :tid ORDER BY parrafo_num ASC");
            $stmtTexts->execute([':tid' => $topicId]);
            $variants = $stmtTexts->fetchAll(PDO::FETCH_COLUMN);

            if (empty($variants)) {
                return ['success' => false, 'message' => 'El tema seleccionado no tiene textos configurados.'];
            }

            $db->beginTransaction();

            // Assign variants sequentially in numerical order
            $numVariants = count($variants);
            $variantIndex = 0;

            // Assign one variant to each student
            $stmtAssign = $db->prepare("
                INSERT INTO examen_oral (id_grupo, id_alumno, parcial, id_oral_text)
                VALUES (:gid, :aid, :p, :oid)
                ON DUPLICATE KEY UPDATE id_oral_text = VALUES(id_oral_text)
            ");

            foreach ($students as $student) {
                $assignedTextId = $variants[$variantIndex % $numVariants];
                $variantIndex++;

                $stmtAssign->execute([
                    ':gid' => $grupoId,
                    ':aid' => $student['id_alumno'],
                    ':p' => $parcial,
                    ':oid' => $assignedTextId
                ]);
            }

            $db->commit();
            return ['success' => true, 'message' => 'Examen oral asignado con éxito a todos los alumnos del grupo en orden numérico.'];
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Error al asignar examen: ' . $e->getMessage()];
        }
    }

    public function saveOralGrade(): array
    {
        if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['docente', 'admin'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $grupoId = (int)($_POST['id_grupo'] ?? 0);
        $alumnoId = (int)($_POST['id_alumno'] ?? 0);
        $parcial = (int)($_POST['parcial'] ?? 0);
        $grade = $_POST['calificacion'];

        if ($grupoId <= 0 || $alumnoId <= 0 || $parcial < 1 || $parcial > 3) {
            return ['success' => false, 'message' => 'Datos de calificación no válidos.'];
        }

        if ($grade !== '' && $grade !== null) {
            $grade = min(10.0, max(0.0, round((float)$grade, 2)));
        } else {
            $grade = null;
        }

        $db = Database::getConnection();

        try {
            $stmt = $db->prepare("
                INSERT INTO examen_oral (id_grupo, id_alumno, parcial, calificacion)
                VALUES (:gid, :aid, :p, :cal)
                ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion), updated_at = NOW()
            ");
            $stmt->execute([
                ':gid' => $grupoId,
                ':aid' => $alumnoId,
                ':p' => $parcial,
                ':cal' => $grade
            ]);

            return ['success' => true, 'message' => 'Calificación de examen oral guardada correctamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al guardar calificación: ' . $e->getMessage()];
        }
    }

    /**
     * Retrieve all questions of a topic in JSON format (AJAX).
     */
    public function getTopicQuestions(): array
    {
        if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['docente', 'admin'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $idTopico = (int)($_GET['id_topico'] ?? 0);
        if ($idTopico <= 0) {
            return ['success' => false, 'message' => 'ID de tópico no válido.', 'questions' => []];
        }

        $db = Database::getConnection();
        $stmtQ = $db->prepare("
            SELECT p.id_pregunta, p.texto, s.nombre AS seccion_nombre, s.letra AS seccion_letra
            FROM pregunta p
            INNER JOIN seccion s ON p.id_seccion = s.id_seccion
            WHERE s.id_topico = :tid
            ORDER BY p.id_pregunta
        ");
        $stmtQ->execute([':tid' => $idTopico]);
        $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($questions as &$q) {
            $stmtO = $db->prepare("SELECT letra, texto, es_correcta FROM opcion WHERE id_pregunta = :qid ORDER BY letra");
            $stmtO->execute([':qid' => $q['id_pregunta']]);
            $q['opciones'] = $stmtO->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($q);

        return [
            'success' => true,
            'questions' => $questions
        ];
    }

    /**
     * Save a new topic and process its bulk parsed questions (AJAX).
     */
    public function saveQuizLabTopic(): array
    {
        if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['docente', 'admin'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $topicoId = (int)($_POST['id_topico'] ?? 0);
        $nuevoTopicoNombre = trim($_POST['nuevo_topico_nombre'] ?? '');
        $tipoEjercicio = trim($_POST['tipo_ejercicio'] ?? 'Multiple choice');
        $questionsText = trim($_POST['questions_text'] ?? '');

        if ($topicoId === 0 && empty($nuevoTopicoNombre)) {
            return ['success' => false, 'message' => 'Debes seleccionar un tema existente o ingresar el nombre de uno nuevo.'];
        }
        if (empty($questionsText)) {
            return ['success' => false, 'message' => 'El contenido de las preguntas es obligatorio.'];
        }

        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            if ($topicoId === 0) {
                // Check if topic name already exists
                $stmtCheck = $db->prepare("SELECT id_topico FROM topico WHERE nombre = :nom LIMIT 1");
                $stmtCheck->execute([':nom' => $nuevoTopicoNombre]);
                $existing = $stmtCheck->fetch();
                if ($existing) {
                    $topicoId = (int)$existing['id_topico'];
                } else {
                    $stmtIns = $db->prepare("INSERT INTO topico (nombre) VALUES (:nom)");
                    $stmtIns->execute([':nom' => $nuevoTopicoNombre]);
                    $topicoId = (int)$db->lastInsertId();
                }
            }

            // Create or select the section for this type under the topic
            $stmtSecs = $db->prepare("SELECT id_seccion, letra FROM seccion WHERE id_topico = :tid ORDER BY letra DESC");
            $stmtSecs->execute([':tid' => $topicoId]);
            $existingSecs = $stmtSecs->fetchAll();

            $idSeccion = 0;
            foreach ($existingSecs as $es) {
                $stmtSecName = $db->prepare("SELECT nombre FROM seccion WHERE id_seccion = :sid");
                $stmtSecName->execute([':sid' => $es['id_seccion']]);
                $secName = $stmtSecName->fetchColumn();
                if (strtolower(trim($secName)) === strtolower($tipoEjercicio)) {
                    $idSeccion = (int)$es['id_seccion'];
                    break;
                }
            }

            if ($idSeccion === 0) {
                $nextLetter = 'A';
                if (!empty($existingSecs)) {
                    $lastLetter = $existingSecs[0]['letra'];
                    $nextLetter = chr(ord($lastLetter) + 1);
                }
                $stmtInsSec = $db->prepare("INSERT INTO seccion (id_topico, nombre, letra) VALUES (:tid, :nom, :let)");
                $stmtInsSec->execute([
                    ':tid' => $topicoId,
                    ':nom' => $tipoEjercicio,
                    ':let' => $nextLetter
                ]);
                $idSeccion = (int)$db->lastInsertId();
            }

            // Parse text
            $lines = explode("\n", $questionsText);
            $parsedQuestions = [];
            $currentQuestion = null;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (preg_match('/^\s*(\d+)\s*[\.\)]\s*(.+)$/', $line, $qMatches)) {
                    if ($currentQuestion !== null) {
                        $parsedQuestions[] = $currentQuestion;
                    }
                    $currentQuestion = [
                        'texto' => trim($qMatches[2]),
                        'opciones' => []
                    ];
                } elseif ($currentQuestion !== null && preg_match('/^\s*(\*?)\s*([a-zA-Z])\s*[\.\)]\s*(\*?)(.+)$/', $line, $oMatches)) {
                    $isCorrect = (!empty($oMatches[1]) || !empty($oMatches[3])) ? 1 : 0;
                    $currentQuestion['opciones'][] = [
                        'letra' => strtoupper($oMatches[2]),
                        'texto' => trim($oMatches[4]),
                        'es_correcta' => $isCorrect
                    ];
                }
            }
            if ($currentQuestion !== null) {
                $parsedQuestions[] = $currentQuestion;
            }

            if (empty($parsedQuestions)) {
                $db->rollBack();
                return ['success' => false, 'message' => 'No se pudieron procesar las preguntas. Verifica el formato (número al inicio para preguntas y letras para opciones).'];
            }

            // Find maximum existing question number to avoid collision
            $stmtMaxNum = $db->query("SELECT MAX(numero) FROM pregunta");
            $maxNum = (int)$stmtMaxNum->fetchColumn();

            $stmtInsQ = $db->prepare("INSERT INTO pregunta (id_seccion, numero, texto) VALUES (:sid, :num, :txt)");
            $stmtInsO = $db->prepare("INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (:qid, :let, :txt, :corr)");

            $qCount = 0;
            $oCount = 0;
            foreach ($parsedQuestions as $pq) {
                $maxNum++;
                $stmtInsQ->execute([
                    ':sid' => $idSeccion,
                    ':num' => $maxNum,
                    ':txt' => $pq['texto']
                ]);
                $qid = $db->lastInsertId();
                $qCount++;

                $hasCorrect = false;
                foreach ($pq['opciones'] as $o) {
                    if ($o['es_correcta']) $hasCorrect = true;
                }

                foreach ($pq['opciones'] as $index => $o) {
                    $isCorrect = $o['es_correcta'];
                    if (!$hasCorrect && $index === 0) {
                        $isCorrect = 1;
                    }
                    $stmtInsO->execute([
                        ':qid' => $qid,
                        ':let' => $o['letra'],
                        ':txt' => $o['texto'],
                        ':corr' => $isCorrect
                    ]);
                    $oCount++;
                }
            }

            $db->commit();
            return [
                'success' => true, 
                'message' => "Se crearon con éxito {$qCount} preguntas y {$oCount} opciones en la sección '{$tipoEjercicio}' del tema."
            ];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => 'Error al guardar preguntas: ' . $e->getMessage()];
        }
    }

    /**
     * Save/assign a homework activity with an optional topic connection (AJAX).
     */
    public function saveQuizHomework(): array
    {
        if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['docente', 'admin'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $idActividad = (int)($_POST['id_actividad'] ?? 0);
        $grupoId = (int)($_POST['id_grupo'] ?? 0);
        $parcial = (int)($_POST['parcial'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        
        $db = Database::getConnection();
        
        $idTopico = null;
        $distJson = null;
        
        $isOnline = isset($_POST['is_online']) && ($_POST['is_online'] === '1' || $_POST['is_online'] === 'on');
        
        if ($isOnline) {
            $topicVal = $_POST['id_topico'] ?? '';
            if ($topicVal === 'new') {
                $resTopic = $this->saveQuizLabTopic();
                if (!$resTopic['success']) {
                    return $resTopic;
                }
                $idTopico = (int)$db->query("SELECT MAX(id_topico) FROM topico")->fetchColumn();
                
                // Count questions of new topic
                $stmtCount = $db->prepare("
                    SELECT COUNT(p.id_pregunta) 
                    FROM pregunta p 
                    INNER JOIN seccion s ON p.id_seccion = s.id_seccion 
                    WHERE s.id_topico = :tid
                ");
                $stmtCount->execute([':tid' => $idTopico]);
                $qCount = (int)$stmtCount->fetchColumn();
                
                $distJson = json_encode([$idTopico => $qCount]);
            } else {
                $idTopico = $topicVal !== '' ? (int)$topicVal : null;
                
                // Read distribution inputs
                $distribucion = $_POST['distribucion'] ?? [];
                $filteredDist = [];
                foreach ($distribucion as $tid => $qty) {
                    $qtyVal = (int)$qty;
                    if ($qtyVal > 0) {
                        $filteredDist[(int)$tid] = $qtyVal;
                        if ($idTopico === null) {
                            $idTopico = (int)$tid;
                        }
                    }
                }
                if (!empty($filteredDist)) {
                    $distJson = json_encode($filteredDist);
                }
            }
        }

        if ($grupoId <= 0 || $parcial < 1 || $parcial > 3 || empty($nombre)) {
            return ['success' => false, 'message' => 'Nombre, grupo y parcial son obligatorios.'];
        }

        try {
            if ($idActividad > 0) {
                $stmt = $db->prepare("
                    UPDATE actividad 
                    SET nombre = :nom, id_topico = :tid, distribucion_preguntas = :dist 
                    WHERE id_actividad = :id
                ");
                $stmt->execute([
                    ':nom' => $nombre,
                    ':tid' => $idTopico,
                    ':dist' => $distJson,
                    ':id' => $idActividad
                ]);
                $message = 'Tarea modificada correctamente.';
            } else {
                $stmtOrder = $db->prepare("SELECT MAX(orden) FROM actividad WHERE id_grupo = :gid AND tipo = 'tarea' AND parcial = :p");
                $stmtOrder->execute([':gid' => $grupoId, ':p' => $parcial]);
                $maxOrder = (int)$stmtOrder->fetchColumn();

                $stmt = $db->prepare("
                    INSERT INTO actividad (id_grupo, tipo, parcial, nombre, orden, id_topico, distribucion_preguntas) 
                    VALUES (:gid, 'tarea', :p, :nom, :ord, :tid, :dist)
                ");
                $stmt->execute([
                    ':gid' => $grupoId,
                    ':p' => $parcial,
                    ':nom' => $nombre,
                    ':ord' => $maxOrder + 1,
                    ':tid' => $idTopico,
                    ':dist' => $distJson
                ]);
                $message = 'Tarea creada y asignada correctamente.';
            }

            return ['success' => true, 'message' => $message];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al guardar tarea: ' . $e->getMessage()];
        }
    }

    /**
     * Submit and auto-grade homework (AJAX).
     */
    public function submitHomework(): array
    {
        if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['alumno', 'docente', 'admin'])) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $isTestRun = in_array($_SESSION['usuario']['rol'], ['docente', 'admin']);
        $alumnoId = $isTestRun ? 0 : (int)($_SESSION['alumno']['id_alumno'] ?? 0);
        $actividadId = (int)($_POST['id_actividad'] ?? 0);
        $answers = $_POST['answers'] ?? [];

        if ($actividadId <= 0) {
            return ['success' => false, 'message' => 'Datos de tarea no válidos.'];
        }
        if (!$isTestRun && $alumnoId <= 0) {
            return ['success' => false, 'message' => 'Alumno no identificado.'];
        }

        $db = Database::getConnection();

        try {
            $stmtAct = $db->prepare("SELECT id_topico, nombre, distribucion_preguntas FROM actividad WHERE id_actividad = :aid AND tipo = 'tarea' LIMIT 1");
            $stmtAct->execute([':aid' => $actividadId]);
            $actividad = $stmtAct->fetch();

            if (!$actividad || (!$actividad['id_topico'] && empty($actividad['distribucion_preguntas']))) {
                return ['success' => false, 'message' => 'La tarea no tiene preguntas asociadas.'];
            }

            $idTopico = (int)$actividad['id_topico'];

            if (!$isTestRun) {
                $stmtCheck = $db->prepare("SELECT id_calificacion_actividad FROM calificacion_actividad WHERE id_actividad = :aid AND id_alumno = :alu LIMIT 1");
                $stmtCheck->execute([':aid' => $actividadId, ':alu' => $alumnoId]);
                if ($stmtCheck->fetch()) {
                    return ['success' => false, 'message' => 'Ya has presentado esta tarea anteriormente.'];
                }
            }

            $totalQuestions = 0;
            if (!empty($actividad['distribucion_preguntas'])) {
                $dist = json_decode($actividad['distribucion_preguntas'], true);
                if (is_array($dist)) {
                    $totalQuestions = array_sum($dist);
                }
            }
            if ($totalQuestions <= 0 && $idTopico > 0) {
                $stmtCount = $db->prepare("
                    SELECT COUNT(p.id_pregunta) 
                    FROM pregunta p 
                    INNER JOIN seccion s ON p.id_seccion = s.id_seccion 
                    WHERE s.id_topico = :tid
                ");
                $stmtCount->execute([':tid' => $idTopico]);
                $totalQuestions = (int)$stmtCount->fetchColumn();
            }
            if ($totalQuestions <= 0) {
                $totalQuestions = max(1, count($answers));
            }

            $correctCount = 0;
            if (!empty($answers)) {
                $questionIds = array_map('intval', array_keys($answers));
                $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
                
                $stmtCorrect = $db->prepare("
                    SELECT id_pregunta, id_opcion 
                    FROM opcion 
                    WHERE id_pregunta IN ($placeholders) AND es_correcta = 1
                ");
                $stmtCorrect->execute($questionIds);
                $correctAnswers = $stmtCorrect->fetchAll(PDO::FETCH_KEY_PAIR);

                foreach ($answers as $qId => $optId) {
                    $qId = (int)$qId;
                    $optId = (int)$optId;
                    if (isset($correctAnswers[$qId]) && (int)$correctAnswers[$qId] === $optId) {
                        $correctCount++;
                    }
                }
            }

            $grade = round(($correctCount / $totalQuestions) * 10, 2);

            if (!$isTestRun) {
                $answeredIdsJson = json_encode($questionIds);
                $stmtSave = $db->prepare("
                    INSERT INTO calificacion_actividad (id_actividad, id_alumno, calificacion, preguntas_respondidas)
                    VALUES (:aid, :alu, :cal, :pr)
                    ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion), preguntas_respondidas = VALUES(preguntas_respondidas), updated_at = NOW()
                ");
                $stmtSave->execute([
                    ':aid' => $actividadId,
                    ':alu' => $alumnoId,
                    ':cal' => $grade,
                    ':pr' => $answeredIdsJson
                ]);
            }

            return [
                'success' => true,
                'message' => $isTestRun ? 'Tarea calificada con éxito (Prueba de Docente).' : 'Tarea guardada y calificada con éxito.',
                'score' => $correctCount,
                'total' => $totalQuestions,
                'grade' => $grade
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al calificar tarea: ' . $e->getMessage()];
        }
    }
}
