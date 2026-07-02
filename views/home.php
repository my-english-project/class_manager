<?php
/**
 * ClassHub — Home Dashboard (HM)
 * 
 * Groups listing + Stat cards.
 */

$totalGroups = $totalGroups ?? 0;
$totalStudents = $totalStudents ?? 0;
$avgAttendance = $avgAttendance ?? 0;
$passCount = $passCount ?? 0;
$failCount = $failCount ?? 0;
$grupos = $grupos ?? [];
$grupoActivo = $grupoActivo ?? null;

$dbTemp = Database::getConnection();
$stmtCyclesTemp = $dbTemp->query("SELECT codigo FROM ciclo");
$ciclosExistentes = $stmtCyclesTemp->fetchAll(PDO::FETCH_COLUMN);

$userRol = $userRol ?? $_SESSION['usuario']['rol'] ?? 'docente';
if ($userRol === 'alumno'):
  $studentData = $studentData ?? [];
  ?>
  <style>
    .special-avg-card {
      display: flex;
      align-items: center;
      gap: var(--space-3);
      padding: 8px var(--space-4);
      border-radius: 12px;
      border: 1.5px solid var(--border-color);
      background: var(--bg-surface, #ffffff);
      width: 220px;
      box-sizing: border-box;
      flex-shrink: 0;
    }

    @media (max-width: 576px) {
      .page-title {
        line-height: 1.15 !important;
        font-size: 22px !important;
        margin-bottom: var(--space-2);
      }

      .special-avg-card {
        width: 100% !important;
      }
    }
  </style>

  <div class="page-header">
    <h1 class="page-title">
      <span class="page-title-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
      </span>
      Portal Académico
    </h1>
    <!--<p class="page-description">Consulta tus calificaciones y presenta tus exámenes en línea</p>-->
  </div>

  <div class="student-dashboard" style="margin-top: var(--space-1);">
    <?php if (empty($studentData)): ?>
      <div class="card"
        style="border: 2px solid var(--gray-200); border-radius: 12px; padding: var(--space-8); background: var(--bg-surface); text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div class="empty-state">
          <div
            style="width: 64px; height: 64px; border-radius: 50%; background: #fef2f2; border: 2px solid #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
            <svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" width="32" height="32">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
              <line x1="12" y1="9" x2="12" y2="13" />
              <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
          </div>
          <h3 style="font-size: 18px; font-weight: 700; color: var(--gray-800); margin-bottom: var(--space-2);">No estás
            asignado a ningún grupo</h3>
          <p style="color: var(--gray-500); font-size: var(--text-sm); max-width: 420px; margin: 0 auto; line-height: 1.5;">
            No tienes materias inscritas ni estás asignado a ningún grupo para el ciclo escolar activo
            <strong><?= htmlspecialchars($_SESSION['ciclo_activo'] ?? '') ?></strong>. Comunícate con el área escolar o con
            tu Administrador para tu asignación.
          </p>
        </div>
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: 1fr; gap: var(--space-6);">
        <?php foreach ($studentData as $i => $sd):
          $g = $sd['grupo'];
          $groupName = htmlspecialchars($g['materia_nombre'] ?? $g['carrera']);
          $groupSiglas = strtolower($g['materia_siglas'] . $g['cuatrimestre'] . $g['grupo']);

          // Map to correct oral exam review path
          if (str_contains($groupSiglas, 'ier8a')) {
            $oralPath = 'exams/oral/oral_exam_3_ier8a_REVIEW.html';
          } elseif (str_contains($groupSiglas, 'ier9a')) {
            $oralPath = 'exams/oral/oral_exam_1_ier9a_REVIEW.html';
          } elseif (str_contains($groupSiglas, 'itea9a')) {
            $oralPath = 'exams/oral/oral_exam_1_itea9a_REVIEW.html';
          } else {
            $oralPath = '#';
          }
          ?>
          <div class="card"
            style="border: 2px solid var(--border-color); border-radius: 12px; padding: var(--space-6); background: var(--card-bg); box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <div
              style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid var(--border-color); padding-bottom: var(--space-3); margin-bottom: var(--space-4);">
              <div>
                <h2
                  style="font-size: var(--text-lg); font-weight: 700; color: var(--primary-800); margin: 0; font-family: var(--font-heading); display: inline-flex; align-items: baseline; gap: var(--space-2);">
                  <?= $groupName ?>
                  <span
                    style="font-size: var(--text-xs); font-weight: 500; color: var(--gray-500); font-family: var(--font-body);">
                    (<?= htmlspecialchars($g['ciclo'] ?? '') ?>)
                  </span>
                </h2>
                <p
                  style="font-size: var(--text-sm); font-weight: 600; color: var(--gray-700); margin-top: 4px; margin-bottom: 2px;">
                  Alumno:
                  <?= htmlspecialchars(trim(($_SESSION['alumno']['nombre'] ?? '') . ' ' . ($_SESSION['alumno']['apellido_pat'] ?? '') . ' ' . ($_SESSION['alumno']['apellido_mat'] ?? ''))) ?>
                  (<?= htmlspecialchars(trim(($g['siglas'] ?? '') . ' ' . ($g['cuatrimestre'] ?? '') . ($g['grupo'] ?? ''))) ?>)
                </p>
                <p style="font-size: var(--text-xs); color: var(--gray-500); margin-top: 2px; margin-bottom: 0;">Profesor:
                  <?= htmlspecialchars($g['docente_nombre'] . " " . $g['docente_apellido']) ?>
                </p>
              </div>
            </div>

            <!-- Parcial Selector & Special Widget -->
            <?php
            $pGrades = [];
            $allPartialsComplete = true;
            for ($pKey = 1; $pKey <= 3; $pKey++) {
              $gradesKey = $sd['parciales'][$pKey];
              $weKey = $gradesKey['we'];
              $oeKey = $gradesKey['oe'];
              $pfKey = $gradesKey['pf'];
              $hwKey = $gradesKey['hw'];
              $attPresKey = $gradesKey['att_present'];
              $attTotKey = $gradesKey['att_total'];

              $weValKey = $weKey !== null ? (float) $weKey : 0;
              $oeValKey = $oeKey !== null ? (float) $oeKey : 0;
              $pfValKey = $pfKey !== null ? (float) $pfKey : 0;
              $hwValKey = $hwKey !== null ? (float) $hwKey : 0;
              $atValKey = $attTotKey > 0 ? ($attPresKey / $attTotKey) * 10 : 10;

              $pGradeKey = ($weValKey * 0.30) + ($oeValKey * 0.36) + ($hwValKey * 0.12) + ($pfValKey * 0.12) + ($atValKey * 0.10);
              $pGrades[$pKey] = round($pGradeKey, 2);

              if ($weKey === null || $oeKey === null || $pfKey === null || $hwKey === null) {
                $allPartialsComplete = false;
              }
            }

            $finalAvg = array_sum($pGrades) / 3.0;
            $finalAvg = round($finalAvg, 2);

            $specialWidgetColor = $allPartialsComplete ? '#22c55e' : '#f97316';
            $specialWidgetLabel = $allPartialsComplete ? 'Promedio Final' : 'Promedio Parcial';
            $specialWidgetGlow = $allPartialsComplete ? 'rgba(34, 197, 94, 0.08)' : 'rgba(249, 115, 22, 0.08)';
            ?>
            <div
              style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-5); gap: var(--space-4); flex-wrap: wrap;">
              <div class="parcial-tabs-<?= $i ?>" style="display: flex; gap: var(--space-2);">
                <button class="btn btn-primary btn-sm t-btn-<?= $i ?>" onclick="switchStudentParcial(<?= $i ?>, 1)"
                  id="t-btn-<?= $i ?>-1">Parcial 1</button>
                <button class="btn btn-outline btn-sm t-btn-<?= $i ?>" onclick="switchStudentParcial(<?= $i ?>, 2)"
                  id="t-btn-<?= $i ?>-2">Parcial 2</button>
                <button class="btn btn-outline btn-sm t-btn-<?= $i ?>" onclick="switchStudentParcial(<?= $i ?>, 3)"
                  id="t-btn-<?= $i ?>-3">Parcial 3</button>
              </div>

              <!-- Special Widget (Width matching cards, compact height, next to tabs) -->
              <div class="special-avg-card"
                style="border-left: 4.5px solid <?= $specialWidgetColor ?>; box-shadow: 0 2px 6px <?= $specialWidgetGlow ?>;">
                <div
                  style="width: 24px; height: 24px; border-radius: 6px; background: <?= $specialWidgetColor ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round" style="width:12px; height:12px; color:white;">
                    <circle cx="12" cy="8" r="7" />
                    <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" />
                  </svg>
                </div>
                <div style="display: flex; flex-direction: column; text-align: left;">
                  <span
                    style="font-size: 9px; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; line-height: 1;"><?= $specialWidgetLabel ?></span>
                  <span style="font-size: 16px; font-weight: 800; color: var(--gray-800); line-height: 1.1; margin-top: 2px;">
                    <?= number_format($finalAvg, 2) ?> <span
                      style="font-size: 11px; font-weight: 600; color: var(--gray-400);">/10</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Parcial Contents -->
            <?php for ($p = 1; $p <= 3; $p++):
              $grades = $sd['parciales'][$p];
              $weGrade = $grades['we'];
              $oeGrade = $grades['oe'];
              $pfGrade = $grades['pf'];
              $hwGrade = $grades['hw'];
              $attPresent = $grades['att_present'];
              $attTotal = $grades['att_total'];
              $examGenerated = !empty($grades['exam_generated']);

              // Attendance percentage
              $attPct = $attTotal > 0 ? round(($attPresent / $attTotal) * 100, 1) : null;
              ?>
              <div class="parcial-content-<?= $i ?>" id="parcial-content-<?= $i ?>-<?= $p ?>"
                style="<?= $p === 1 ? '' : 'display:none;' ?>">
                <div
                  style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">

                  <!-- 1. Asistencia Card -->
                  <div class="stat-card"
                    style="display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-start; padding: var(--space-5); border-radius: 12px; border: 1.5px solid var(--border-color); border-top: 4.5px solid #3b82f6; background: var(--bg-surface, #ffffff); box-shadow: var(--shadow-sm); text-align: left; min-height: 180px;">
                    <div
                      style="width: 32px; height: 32px; border-radius: 8px; background: #3b82f6; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-3);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" style="width:16px; height:16px; color:white;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                      </svg>
                    </div>
                    <span
                      style="font-size: var(--text-xs); font-weight: 700; color: var(--gray-500); margin-bottom: var(--space-2);">Asistencia</span>
                    <span
                      style="font-size: 26px; font-weight: 800; color: var(--gray-800); font-family: var(--font-heading); margin-bottom: var(--space-1); line-height: 1;">
                      <?= $attPct !== null ? $attPct . '%' : '—' ?>
                    </span>
                    <span
                      style="font-size: 11px; color: var(--gray-500); font-weight: 500; margin-top: auto;"><?= $attPresent ?> de
                      <?= $attTotal ?> clases</span>
                  </div>

                  <!-- 2. Examen Escrito Card -->
                  <div class="stat-card"
                    style="display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-start; padding: var(--space-5); border-radius: 12px; border: 1.5px solid var(--border-color); border-top: 4.5px solid #ef4444; background: var(--bg-surface, #ffffff); box-shadow: var(--shadow-sm); text-align: left; transition: all 0.2s; min-height: 180px;">
                    <div
                      style="width: 32px; height: 32px; border-radius: 8px; background: #ef4444; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-3);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" style="width:16px; height:16px; color:white;">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                      </svg>
                    </div>
                    <span
                      style="font-size: var(--text-xs); font-weight: 700; color: var(--gray-500); margin-bottom: var(--space-2);">Examen
                      Escrito</span>
                    <span
                      style="font-size: 26px; font-weight: 800; color: var(--gray-800); font-family: var(--font-heading); margin-bottom: var(--space-1); line-height: 1;">
                      <?= $weGrade !== null ? number_format($weGrade, 2) : '—' ?>
                      <?php if ($weGrade !== null): ?><span
                          style="font-size: 13px; font-weight: 600; color: var(--gray-400); margin-left: 2px;">/10</span><?php endif; ?>
                    </span>
                    <span
                      style="font-size: 11px; color: var(--gray-500); font-weight: 500; margin-bottom: var(--space-3);">Saber
                      (30%)</span>
                    <?php if ($weGrade === null): ?>
                      <?php if ($examGenerated): ?>
                        <a href="index.php?page=take_written_exam&id_grupo=<?= $g['id_grupo'] ?>&parcial=<?= $p ?>"
                          class="btn btn-primary btn-sm"
                          style="width: 100%; text-align: center; border-radius: 20px; font-size: 11px; padding: 4px 8px; font-weight: bold; margin-top: auto;">Presentar</a>
                      <?php else: ?>
                        <span style="font-size: 11px; color: var(--gray-400); font-weight: 600; margin-top: auto;">No
                          habilitado</span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span
                        style="font-size: 11px; color: #ef4444; font-weight: 600; margin-top: auto; display: flex; align-items: center; gap: 4px;">✓
                        Completado</span>
                    <?php endif; ?>
                  </div>

                  <!-- 3. Examen Oral Card -->
                  <div class="stat-card"
                    style="display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-start; padding: var(--space-5); border-radius: 12px; border: 1.5px solid var(--border-color); border-top: 4.5px solid #6366f1; background: var(--bg-surface, #ffffff); box-shadow: var(--shadow-sm); text-align: left; transition: all 0.2s; min-height: 180px;">
                    <div
                      style="width: 32px; height: 32px; border-radius: 8px; background: #6366f1; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-3);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" style="width:16px; height:16px; color:white;">
                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                        <line x1="12" y1="19" x2="12" y2="23" />
                        <line x1="8" y1="23" x2="16" y2="23" />
                      </svg>
                    </div>
                    <span
                      style="font-size: var(--text-xs); font-weight: 700; color: var(--gray-500); margin-bottom: var(--space-2);">Examen
                      Oral</span>
                    <span
                      style="font-size: 26px; font-weight: 800; color: var(--gray-800); font-family: var(--font-heading); margin-bottom: var(--space-1); line-height: 1;">
                      <?= $oeGrade !== null ? number_format($oeGrade, 2) : '—' ?>
                      <?php if ($oeGrade !== null): ?><span
                          style="font-size: 13px; font-weight: 600; color: var(--gray-400); margin-left: 2px;">/10</span><?php endif; ?>
                    </span>
                    <span
                      style="font-size: 11px; color: var(--gray-500); font-weight: 500; margin-bottom: var(--space-3);">Saber
                      (36%)</span>
                    <?php
                    $idOralText = $grades['id_oral_text'] ?? null;
                    if ($oeGrade === null && $idOralText !== null):
                      ?>
                      <a href="index.php?page=take_oral_exam&id_grupo=<?= $g['id_grupo'] ?>&parcial=<?= $p ?>"
                        class="btn btn-primary btn-sm"
                        style="width: 100%; text-align: center; border-radius: 20px; font-size: 11px; padding: 4px 8px; font-weight: bold; margin-top: auto;">Ver
                        Examen Oral</a>
                    <?php elseif ($oeGrade !== null): ?>
                      <span
                        style="font-size: 11px; color: #6366f1; font-weight: 600; margin-top: auto; display: flex; align-items: center; gap: 4px;">✓
                        Completado</span>
                    <?php else: ?>
                      <span style="font-size: 11px; color: var(--gray-400); font-weight: 600; margin-top: auto;">No
                        asignado</span>
                    <?php endif; ?>
                  </div>

                  <!-- 4. Portafolio Card -->
                  <div class="stat-card"
                    style="display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-start; padding: var(--space-5); border-radius: 12px; border: 1.5px solid var(--border-color); border-top: 4.5px solid #a855f7; background: var(--bg-surface, #ffffff); box-shadow: var(--shadow-sm); text-align: left; min-height: 180px;">
                    <div
                      style="width: 32px; height: 32px; border-radius: 8px; background: #a855f7; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-3);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" style="width:16px; height:16px; color:white;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                      </svg>
                    </div>
                    <span
                      style="font-size: var(--text-xs); font-weight: 700; color: var(--gray-500); margin-bottom: var(--space-2);">Portafolio
                      de Evidencias</span>
                    <span
                      style="font-size: 26px; font-weight: 800; color: var(--gray-800); font-family: var(--font-heading); margin-bottom: var(--space-1); line-height: 1;">
                      <?= $pfGrade !== null ? number_format($pfGrade, 2) : '—' ?>
                      <?php if ($pfGrade !== null): ?><span
                          style="font-size: 13px; font-weight: 600; color: var(--gray-400); margin-left: 2px;">/10</span><?php endif; ?>
                    </span>
                    <span style="font-size: 11px; color: var(--gray-500); font-weight: 500; margin-top: auto;">Saber Hacer
                      (12%)</span>
                  </div>

                  <?php
                  $totalTasks = 0;
                  $answeredTasks = 0;
                  if (isset($studentData) && is_array($studentData)) {
                    foreach ($studentData as $sd) {
                      foreach ($sd['parciales'] as $pNum => $pData) {
                        foreach ($pData['hw_list'] ?? [] as $hwItem) {
                          $isResolvable = (!empty($hwItem['id_topico']) || !empty($hwItem['distribucion_preguntas']));
                          if ($isResolvable) {
                            $totalTasks++;
                            if ($hwItem['calificacion'] !== null) {
                              $answeredTasks++;
                            }
                          }
                        }
                      }
                    }
                  }
                  $hasPendingHws = ($answeredTasks < $totalTasks);
                  ?>
                  <!-- 5. Tareas Card -->
                  <div class="stat-card"
                    style="display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-start; padding: var(--space-5); border-radius: 12px; border: 1.5px solid var(--border-color); border-top: 4.5px solid #06b6d4; background: var(--bg-surface, #ffffff); box-shadow: var(--shadow-sm); text-align: left; min-height: 180px;">
                    <div
                      style="width: 32px; height: 32px; border-radius: 8px; background: #06b6d4; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-3);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" style="width:16px; height:16px; color:white;">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                      </svg>
                    </div>
                    <span
                      style="font-size: var(--text-xs); font-weight: 700; color: var(--gray-500); margin-bottom: var(--space-2);">Tareas</span>
                    <span
                      style="font-size: 26px; font-weight: 800; color: var(--gray-800); font-family: var(--font-heading); margin-bottom: var(--space-1); line-height: 1;">
                      <?= $hwGrade !== null ? number_format($hwGrade, 2) : '—' ?>
                      <?php if ($hwGrade !== null): ?><span
                          style="font-size: 13px; font-weight: 600; color: var(--gray-400); margin-left: 2px;">/10</span><?php endif; ?>
                    </span>
                    <span
                      style="font-size: 11px; color: var(--gray-500); font-weight: 500; margin-bottom: var(--space-2);">Saber
                      Hacer (12%)</span>

                    <!-- Tareas asignadas button -->
                    <?php if ($hasPendingHws): ?>
                      <button class="btn btn-sm" onclick="openStudentHomeworksModal()"
                        style="margin-top: var(--space-3); border-radius: 20px; font-weight: bold; width: 100%; font-size: 11px; background: #eb5757; color: white; border: 1.5px solid #eb5757; box-shadow: 0 4px 10px rgba(235,87,87,0.2);">
                        Tareas contestadas <?= $answeredTasks ?>/<?= $totalTasks ?>
                      </button>
                    <?php else: ?>
                      <button class="btn btn-sm" onclick="openStudentHomeworksModal()"
                        style="margin-top: var(--space-3); border-radius: 20px; font-weight: bold; width: 100%; font-size: 11px; background: var(--uts-green); color: white; border: 1.5px solid var(--uts-green); box-shadow: 0 4px 10px rgba(10,111,81,0.2);">
                        Tareas contestadas <?= $answeredTasks ?>/<?= $totalTasks ?>
                      </button>
                    <?php endif; ?>
                  </div>

                  <!-- 6. Calificación Final / Acumulada Card -->
                  <?php
                  $weVal = $weGrade !== null ? (float) $weGrade : 0;
                  $oeVal = $oeGrade !== null ? (float) $oeGrade : 0;
                  $pfVal = $pfGrade !== null ? (float) $pfGrade : 0;
                  $hwVal = $hwGrade !== null ? (float) $hwGrade : 0;
                  $atVal = $attTotal > 0 ? ($attPresent / $attTotal) * 10 : 10;

                  $parcialGrade = ($weVal * 0.30) + ($oeVal * 0.36) + ($hwVal * 0.12) + ($pfVal * 0.12) + ($atVal * 0.10);
                  $parcialGrade = round($parcialGrade, 2);

                  // Final only if all four components are present
                  $isFinal = ($weGrade !== null && $oeGrade !== null && $pfGrade !== null && $hwGrade !== null);
                  $themeColor = $isFinal ? '#22c55e' : '#f97316';
                  $shadowGlow = $isFinal ? 'rgba(34, 197, 94, 0.08)' : 'rgba(249, 115, 22, 0.08)';
                  $labelText = $isFinal ? 'Calificación Final' : 'Calificación Acumulada';
                  ?>
                  <div class="stat-card"
                    style="display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-start; padding: var(--space-5); border-radius: 12px; border: 1.5px solid var(--border-color); border-top: 4.5px solid <?= $themeColor ?>; background: var(--bg-surface, #ffffff); box-shadow: 0 4px 12px <?= $shadowGlow ?>; text-align: left; min-height: 180px;">
                    <div
                      style="width: 32px; height: 32px; border-radius: 8px; background: <?= $themeColor ?>; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-3);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" style="width:16px; height:16px; color:white;">
                        <circle cx="12" cy="8" r="7" />
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" />
                      </svg>
                    </div>
                    <span
                      style="font-size: var(--text-xs); font-weight: 700; color: var(--gray-500); margin-bottom: var(--space-2);"><?= $labelText ?></span>
                    <span
                      style="font-size: 26px; font-weight: 800; color: var(--gray-800); font-family: var(--font-heading); margin-bottom: var(--space-1); line-height: 1;">
                      <?= number_format($parcialGrade, 2) ?>
                      <span style="font-size: 13px; font-weight: 600; color: var(--gray-400); margin-left: 2px;">/10</span>
                    </span>
                    <span style="font-size: 11px; color: var(--gray-500); font-weight: 500; margin-top: auto;">Calificación
                      Ponderada</span>
                  </div>

                </div>
              </div>
            <?php endfor; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Modal para Tareas Asignadas (Alumno) -->
  <div class="modal-overlay" id="modal-student-homeworks">
    <div class="modal"
      style="max-width: 500px; text-align: left; padding: var(--space-6); border-radius: 16px; box-shadow: var(--shadow-lg);">
      <div class="modal-handle"></div>
      <h3 style="margin-top: 0; margin-bottom: var(--space-3); font-weight: 800; color: var(--primary-800);">Tareas
        Asignadas</h3>

      <div id="student-homeworks-list"
        style="display: flex; flex-direction: column; gap: var(--space-3); max-height: 300px; overflow-y: auto;">
        <?php
        $allAssignedHws = [];
        if (isset($studentData) && is_array($studentData)) {
          foreach ($studentData as $sd) {
            foreach ($sd['parciales'] as $pNum => $pData) {
              foreach ($pData['hw_list'] ?? [] as $hwItem) {
                $allAssignedHws[] = [
                  'id_actividad' => $hwItem['id_actividad'],
                  'nombre' => $hwItem['nombre'],
                  'parcial' => $pNum,
                  'calificacion' => $hwItem['calificacion'],
                  'id_topico' => $hwItem['id_topico'],
                  'distribucion_preguntas' => $hwItem['distribucion_preguntas'] ?? null
                ];
              }
            }
          }
        }

        if (empty($allAssignedHws)):
          ?>
          <div style="text-align: center; color: var(--gray-400); padding: var(--space-4);">No tienes tareas asignadas.
          </div>
        <?php else: ?>
          <?php foreach ($allAssignedHws as $h): ?>
            <div
              style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3) var(--space-4); background: var(--gray-50); border: 1.5px solid var(--border-color); border-radius: 12px;">
              <div>
                <strong
                  style="display: block; font-size: var(--text-sm); color: var(--gray-800);"><?= htmlspecialchars($h['nombre']) ?></strong>
                <span style="font-size: 11px; color: var(--gray-500); font-weight: 600;">Parcial <?= $h['parcial'] ?></span>
              </div>
              <div>
                <?php if ($h['calificacion'] !== null): ?>
                  <span
                    style="font-weight: 800; color: var(--uts-green); font-size: var(--text-sm);"><?= number_format($h['calificacion'], 1) ?></span>
                <?php elseif ($h['id_topico'] || !empty($h['distribucion_preguntas'])): ?>
                  <a href="index.php?page=take_homework&id_actividad=<?= $h['id_actividad'] ?>" class="btn btn-primary btn-sm"
                    style="border-radius: 12px; font-size: 11px; font-weight: bold; padding: var(--space-1) var(--space-3);">Resolver</a>
                <?php else: ?>
                  <span style="font-size: var(--text-xs); color: var(--gray-400); font-weight: 600;">Captura Manual</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div style="margin-top: var(--space-5); text-align: right;">
        <button class="btn btn-outline btn-sm" onclick="closeModal('modal-student-homeworks')"
          style="border-radius: 20px;">Cerrar</button>
      </div>
    </div>
  </div>

  <script>
    function openStudentHomeworksModal() {
      document.getElementById('modal-student-homeworks').classList.add('active');
    }

    function switchStudentParcial(groupIndex, parcial) {
      // Hide all contents for this group
      document.querySelectorAll('.parcial-content-' + groupIndex).forEach(el => el.style.display = 'none');

      // Show selected parcial content
      document.getElementById('parcial-content-' + groupIndex + '-' + parcial).style.display = 'block';

      // Reset tab button styles
      document.querySelectorAll('.t-btn-' + groupIndex).forEach(el => {
        el.classList.remove('btn-primary');
        el.classList.add('btn-outline');
      });

      // Set selected tab button as active
      const targetBtn = document.getElementById('t-btn-' + groupIndex + '-' + parcial);
      targetBtn.classList.remove('btn-outline');
      targetBtn.classList.add('btn-primary');
    }
  </script>
<?php else: ?>
  <!-- Page Title -->
  <div class="page-header">
    <h1 class="page-title">
      <span class="page-title-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
      </span>
      Dashboard
    </h1>
    <p class="page-description">Selecciona un grupo para comenzar</p>
  </div>

  <div class="toolbar">
    <div style="display: flex; gap: var(--space-4); align-items: center;">
      <select class="form-control form-select" onchange="setActiveCiclo(this.value)"
        style="width: auto; min-width: 150px; font-weight: 600; border-radius: 20px;">
        <?php foreach ($ciclosDisponibles as $c): ?>
          <option value="<?= $c ?>" <?= ($cicloActivo ?? '') === $c ? 'selected' : '' ?>>Ciclo <?= $c ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="toolbar-right" style="margin-left: auto; display: flex; gap: var(--space-3);">
    </div>
  </div>

  <!-- Grupos Grid -->
  <?php if (count($grupos) > 0): ?>
    <h3 style="margin-bottom: var(--space-4); margin-top: var(--space-6);">
      <?= $userRol === 'admin' ? 'Grupos' : 'Mis grupos' ?>
    </h3>
    <div class="group-grid" style="margin-bottom: var(--space-6);">
      <?php
      $cardColors = [
        ['border' => '#3b82f6', 'glow' => 'rgba(59, 130, 246, 0.08)'], // Blue
        ['border' => '#10b981', 'glow' => 'rgba(16, 185, 129, 0.08)'], // Green
        ['border' => '#8b5cf6', 'glow' => 'rgba(139, 92, 246, 0.08)'], // Purple
        ['border' => '#f59e0b', 'glow' => 'rgba(245, 158, 11, 0.08)'], // Amber
        ['border' => '#ec4899', 'glow' => 'rgba(236, 72, 153, 0.08)'], // Pink
        ['border' => '#06b6d4', 'glow' => 'rgba(6, 182, 212, 0.08)'], // Cyan
      ];
      $colorIndex = 0;
      foreach ($grupos as $grupo):
        $color = $cardColors[$colorIndex % count($cardColors)];
        $colorIndex++;
        ?>
        <div class="group-card" id="group-<?= $grupo['id_grupo'] ?>"
          style="border-left: 5px solid <?= $color['border'] ?>; box-shadow: 0 4px 12px <?= $color['glow'] ?>; cursor: default;">
          <div class="group-card-header">
            <span
              class="group-card-siglas"><?= htmlspecialchars($grupo['siglas'] . $grupo['cuatrimestre'] . $grupo['grupo']) ?></span>
            <span class="group-card-ciclo"><?= htmlspecialchars($grupo['ciclo']) ?></span>
          </div>
          <div class="group-card-career"><?= htmlspecialchars($grupo['carrera']) ?></div>
          <div class="group-card-meta">
            <span>📅 <?= htmlspecialchars($grupo['periodo']) ?>       <?= $grupo['anio'] ?></span>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
              <span>👥 <?= $grupo['total_alumnos'] ?> alumnos</span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="card" style="margin-bottom: var(--space-6);">
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="3" width="7" height="7" />
          <rect x="14" y="3" width="7" height="7" />
          <rect x="14" y="14" width="7" height="7" />
          <rect x="3" y="14" width="7" height="7" />
        </svg>
        <h3>No hay grupos</h3>
        <p>Crea tu primer grupo para comenzar a gestionar tu clase.</p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Stats Grid -->
  <h3 style="margin-bottom: var(--space-4); margin-top: var(--space-6);">Estadísticas</h3>
  <div class="stats-grid">
    <div class="stat-card stat-card--green">
      <div class="stat-icon stat-icon--green">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" />
          <rect x="14" y="3" width="7" height="7" />
          <rect x="14" y="14" width="7" height="7" />
          <rect x="3" y="14" width="7" height="7" />
        </svg>
      </div>
      <div class="stat-value"><?= $totalGroups ?></div>
      <div class="stat-label">Grupos Activos</div>
    </div>

    <div class="stat-card stat-card--blue">
      <div class="stat-icon stat-icon--blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
          <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </svg>
      </div>
      <div class="stat-value"><?= $totalStudents ?></div>
      <div class="stat-label">Total Alumnos</div>
    </div>

    <div class="stat-card stat-card--teal">
      <div class="stat-icon stat-icon--teal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
      </div>
      <div class="stat-value"><?= $avgAttendance ?>%</div>
      <div class="stat-label">Asistencia Prom.</div>
    </div>

    <div class="stat-card stat-card--orange">
      <div class="stat-icon stat-icon--orange">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="20" x2="18" y2="10" />
          <line x1="12" y1="20" x2="12" y2="4" />
          <line x1="6" y1="20" x2="6" y2="14" />
        </svg>
      </div>
      <div class="stat-value"><?= $passCount ?><span style="font-size: var(--text-sm); color: var(--gray-400);"> /
          <?= $failCount ?></span></div>
      <div class="stat-label">Aprobados / Reprobados</div>
    </div>
  </div>
<?php endif; ?>