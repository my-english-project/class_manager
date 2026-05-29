<?php
/**
 * ClassHub — Write Exam View (WE)
 * 
 * Written exam grade capture by parcial (RF-23/24/25).
 */

$alumnos = $alumnos ?? [];
$grades = $grades ?? [];
$grupoActivo = $grupoActivo ?? null;
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <polyline points="14 2 14 8 20 8" />
        <line x1="16" y1="13" x2="8" y2="13" />
        <line x1="16" y1="17" x2="8" y2="17" />
      </svg>
    </span>
    Examen Escrito
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
  <p class="page-description">Captura de calificaciones — Saber (30%)</p>
</div>

<?php if (!$grupoActivo || count($alumnos) === 0): ?>
  <div class="card">
    <div class="empty-state">
      <h3><?= !$grupoActivo ? 'Sin grupo activo' : 'Sin alumnos' ?></h3>
      <p><?= !$grupoActivo ? 'Selecciona un grupo.' : 'Agrega alumnos al grupo.' ?></p>
    </div>
  </div>
<?php else: ?>


  <?php
  $examConfigs = $examConfigs ?? [];
  $cicloActivo = $_SESSION['ciclo_activo'] ?? '';
  ?>
  <div class="card" style="border: 2px solid var(--border-color); border-radius: 12px; padding: var(--space-4); margin-bottom: var(--space-5); background: var(--card-bg);">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--space-3);">
      <div>
        <h3 style="font-size: var(--text-md); font-weight: 700; color: var(--gray-800); margin: 0;">Generar y Habilitar Exámenes Escritos en Línea</h3>
        <p style="font-size: var(--text-xs); color: var(--gray-500); margin: 2px 0 0 0;">Ciclo Activo Vigente: <strong><?= htmlspecialchars($cicloActivo) ?></strong>. Activa los exámenes para que sean visibles por los alumnos.</p>
      </div>
      <div style="display:flex; gap:var(--space-2);">
        <?php for ($p = 1; $p <= 3; $p++): 
            $isGenerated = !empty($examConfigs[$p]) && (int)$examConfigs[$p]['generado'] === 1;
        ?>
          <button onclick="openExamConfig(<?= $p ?>)" 
                  class="btn <?= $isGenerated ? 'btn-primary' : 'btn-outline' ?> btn-sm" 
                  style="border-radius: 20px; font-weight: bold; min-width: 130px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
            <?php if ($isGenerated): ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
              Parcial <?= $p ?>: Habilitado
            <?php else: ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
              Habilitar P<?= $p ?>
            <?php endif; ?>
          </button>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="data-table" id="table-we">
      <thead>
        <tr>
          <th class="col-num" width="40">#</th>
          <th class="col-matricula" width="120">Matrícula</th>
          <th class="col-name">Nombre</th>
          <th style="text-align:center;"><span class="tab-text-full">Parcial 1</span><span class="tab-text-short">P1</span></th>
          <th style="text-align:center;"><span class="tab-text-full">Parcial 2</span><span class="tab-text-short">P2</span></th>
          <th style="text-align:center;"><span class="tab-text-full">Parcial 3</span><span class="tab-text-short">P3</span></th>
          <th style="text-align:center;">Promedio</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($alumnos as $i => $alumno): ?>
          <?php
          $p1 = $grades[$alumno['id_alumno']][1] ?? '';
          $p2 = $grades[$alumno['id_alumno']][2] ?? '';
          $p3 = $grades[$alumno['id_alumno']][3] ?? '';
          $vals = array_filter([$p1, $p2, $p3], fn($v) => $v !== '' && $v !== null);
          $avg = count($vals) > 0 ? array_sum($vals) / count($vals) : null;
          ?>
          <tr>
            <td class="col-num"><?= $i + 1 ?></td>
            <td class="col-matricula"><?= htmlspecialchars($alumno['matricula']) ?></td>
            <td class="col-name"><?= htmlspecialchars($alumno['nombre_completo']) ?></td>
            <td style="text-align:center;">
              <input type="number" class="grade-input <?= $p1 !== '' && round((float) $p1, 2) < 7 ? 'fail' : '' ?>"
                data-alumno="<?= $alumno['id_alumno'] ?>" data-parcial="1"
                value="<?= $p1 !== '' && $p1 !== null ? number_format((float) $p1, 2) : '' ?>" min="0" max="10" step="0.01"
                oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'we')">
            </td>
            <td style="text-align:center;">
              <input type="number" class="grade-input <?= $p2 !== '' && round((float) $p2, 2) < 7 ? 'fail' : '' ?>"
                data-alumno="<?= $alumno['id_alumno'] ?>" data-parcial="2"
                value="<?= $p2 !== '' && $p2 !== null ? number_format((float) $p2, 2) : '' ?>" min="0" max="10" step="0.01"
                oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'we')">
            </td>
            <td style="text-align:center;">
              <input type="number" class="grade-input <?= $p3 !== '' && round((float) $p3, 2) < 7 ? 'fail' : '' ?>"
                data-alumno="<?= $alumno['id_alumno'] ?>" data-parcial="3"
                value="<?= $p3 !== '' && $p3 !== null ? number_format((float) $p3, 2) : '' ?>" min="0" max="10" step="0.01"
                oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'we')">
            </td>
            <td style="text-align:center;"
              class="font-bold <?= $avg !== null && round((float) $avg, 2) < 7 ? 'grade-fail' : '' ?>">
              <?= $avg !== null ? number_format($avg, 2) : '—' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal de Configuración de Examen -->
  <div class="modal-overlay" id="exam-config-modal">
    <div class="modal" style="max-width: 500px; text-align: left; padding: var(--space-6); border-radius: 16px; box-shadow: var(--shadow-lg);">
      <div class="modal-handle"></div>
      <h3 style="margin-bottom: var(--space-1); font-weight: 800; font-size: var(--text-lg); color: var(--primary-800);" id="modal-exam-title">Configurar Examen Escrito</h3>
      <p style="color: var(--gray-500); font-size: var(--text-xs); margin-bottom: var(--space-4);">Elige la distribución de reactivos y publica el examen a uno o varios grupos.</p>

      <form id="form-exam-config" onsubmit="saveExamConfig(event)">
        <input type="hidden" name="parcial" id="config-parcial" value="">
        <input type="hidden" name="generado" id="config-generado" value="1">
        <input type="hidden" name="id_grupo" value="<?= $grupoActivo['id_grupo'] ?? 0 ?>">

        <!-- Dynamic Topics Reactivos List -->
        <div style="margin-bottom: var(--space-4); border-bottom: 1.5px solid var(--border-color); padding-bottom: var(--space-3);">
          <div style="display: grid; grid-template-columns: 1fr 100px; gap: var(--space-3); border-bottom: 2px solid var(--border-color); padding-bottom: 6px; margin-bottom: var(--space-3); font-weight: 800; color: var(--gray-700); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
            <div>Tema</div>
            <div style="text-align: right;">Reactivos</div>
          </div>
          
          <div style="max-height: 180px; overflow-y: auto; display: flex; flex-direction: column; gap: var(--space-3); padding-right: var(--space-1);">
            <?php foreach ($topics as $top): ?>
              <div style="display: grid; grid-template-columns: 1fr 90px; gap: var(--space-3); align-items: center;">
                <div style="font-size: var(--text-sm); font-weight: 600; color: var(--gray-800); display: flex; align-items: center; gap: 8px;">
                  <span style="color: var(--uts-green); font-size: 16px; line-height: 1;">•</span>
                  <?= htmlspecialchars($top['nombre']) ?>
                </div>
                <div>
                  <input type="number" class="form-control topic-input" 
                         id="topic-input-<?= $top['id_topico'] ?>" 
                         name="distribucion[<?= $top['id_topico'] ?>]" 
                         min="0" max="60" value="20" required 
                         style="border-radius: 8px; text-align: center; height: 36px; padding: 4px; font-weight: 600;">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Groups and Duration row -->
        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: var(--space-4); margin-bottom: var(--space-5); align-items: start;">
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-weight: 700; margin-bottom: var(--space-2); display: block;">Publicar en Grupo(s)</label>
            <div style="display: flex; flex-direction: column; gap: var(--space-2); max-height: 110px; overflow-y: auto; padding: var(--space-2); border: 1.5px solid var(--border-color); border-radius: 8px; background: #faf9f6;">
              <?php foreach ($docenteGrupos as $g): ?>
                <label style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-sm); font-weight: 600; cursor: pointer; color: var(--gray-700); margin: 0;">
                  <input type="checkbox" name="target_groups[]" value="<?= $g['id_grupo'] ?>" 
                         <?= $g['id_grupo'] == $grupoActivo['id_grupo'] ? 'checked onclick="return false;"' : '' ?>
                         style="width: 16px; height: 16px; accent-color: var(--uts-green);">
                  <span><?= htmlspecialchars($g['siglas'] . $g['cuatrimestre'] . $g['grupo']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="duracion" style="font-weight: 700;">Duración (min)</label>
            <input type="number" class="form-control" id="duracion" name="duracion" min="5" max="300" value="50" required style="border-radius: 8px; height: 40px; font-weight: 600;">
          </div>
        </div>

        <div style="display: flex; gap: var(--space-3); justify-content: flex-end; align-items: center;">
          <button type="button" class="btn btn-outline" onclick="closeModal('exam-config-modal')" style="border-radius: 20px; font-weight: bold;">Cancelar</button>
          <button type="button" id="btn-disable-exam" class="btn btn-outline" style="border-color: #eb5757; color: #eb5757; border-radius: 20px; font-weight: bold; display: none;" onclick="disableExam()">Deshabilitar Examen</button>
          <button type="submit" class="btn btn-primary" style="border-radius: 20px; font-weight: bold;" id="btn-save-exam-config">Habilitar Examen</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  const examConfigsJson = <?= json_encode($examConfigs) ?>;

  function openExamConfig(parcial) {
    document.getElementById('config-parcial').value = parcial;
    const config = examConfigsJson[parcial] || null;
    const isGenerated = config ? parseInt(config.generado, 10) === 1 : false;
    
    document.getElementById('modal-exam-title').innerText = `Configurar Examen Escrito - Parcial ${parcial}`;
    document.getElementById('duracion').value = config ? config.duracion : 50;
    
    // Parse dynamic distribution
    let distribution = {};
    if (config && config.distribucion_preguntas) {
      try {
        distribution = JSON.parse(config.distribucion_preguntas);
      } catch(e) {
        console.error("Error parsing distribution JSON:", e);
      }
    }
    
    // Reset and fill topic inputs
    const topicInputs = document.querySelectorAll('.topic-input');
    topicInputs.forEach(input => {
      const topicId = input.id.replace('topic-input-', '');
      // Fallback: Default to 20 if not defined
      input.value = (distribution && distribution[topicId] !== undefined) ? distribution[topicId] : 20;
    });
    
    const disableBtn = document.getElementById('btn-disable-exam');
    const submitBtn = document.getElementById('btn-save-exam-config');
    
    if (isGenerated) {
      disableBtn.style.display = 'inline-block';
      submitBtn.innerText = 'Actualizar';
      document.getElementById('config-generado').value = "1";
    } else {
      disableBtn.style.display = 'none';
      submitBtn.innerText = 'Habilitar Examen';
      document.getElementById('config-generado').value = "1";
    }
    
    document.getElementById('exam-config-modal').classList.add('active');
  }

  function disableExam() {
    document.getElementById('config-generado').value = "0";
    document.getElementById('form-exam-config').requestSubmit();
  }

  function saveExamConfig(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-exam-config');
    btn.disabled = true;
    
    const form = document.getElementById('form-exam-config');
    const formData = new FormData(form);
    
    // Always ensure active group is included
    if (!formData.has('target_groups[]')) {
      formData.append('target_groups[]', '<?= $grupoActivo['id_grupo'] ?? 0 ?>');
    }
    
    fetch('index.php?action=toggle_written_exam', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      if (data.success) {
        showToast(data.message, 'success');
        closeModal('exam-config-modal');
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.message, 'error');
      }
    })
    .catch(() => {
      btn.disabled = false;
      showToast('Error de red al guardar la configuración', 'error');
    });
  }
  </script>
<?php endif; ?>