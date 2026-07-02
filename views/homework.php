<?php
/**
 * ClassHub — Homework View (HW)
 * 
 * Dynamic homework columns with grade capture (RF-32/33).
 */

$alumnos = $alumnos ?? [];
$activities = $activities ?? [];
$activityGrades = $activityGrades ?? [];
$grupoActivo = $grupoActivo ?? null;
$currentParcial = (int) ($_GET['parcial'] ?? 1);
$filteredActs = array_filter($activities, fn($a) => $a['parcial'] == $currentParcial);
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
      </svg>
    </span>
    Tareas
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
  <p class="page-description">Registro de tareas — Homework</p>
</div>

<?php if (!$grupoActivo || count($alumnos) === 0): ?>
  <div class="card">
    <div class="empty-state">
      <h3><?= !$grupoActivo ? 'Sin grupo activo' : 'Sin alumnos' ?></h3>
    </div>
  </div>
<?php else: ?>

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); border-bottom: 1px solid var(--border-color); padding-bottom: var(--space-2);">
    <div>
      <button class="btn btn-outline btn-sm" onclick="openActivityModal('tarea', <?= $currentParcial ?>)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
          <line x1="12" y1="5" x2="12" y2="19" />
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Agregar Tarea
      </button>
    </div>
    <div class="tabs" style="display: flex; gap: var(--space-2);">
      <a href="index.php?page=homework&parcial=1"
        class="btn tab-btn <?= $currentParcial === 1 ? 'btn-primary' : 'btn-outline' ?>"><span class="tab-text-full">Parcial 1</span><span class="tab-text-short">P1</span></a>
      <a href="index.php?page=homework&parcial=2"
        class="btn tab-btn <?= $currentParcial === 2 ? 'btn-primary' : 'btn-outline' ?>"><span class="tab-text-full">Parcial 2</span><span class="tab-text-short">P2</span></a>
      <a href="index.php?page=homework&parcial=3"
        class="btn tab-btn <?= $currentParcial === 3 ? 'btn-primary' : 'btn-outline' ?>"><span class="tab-text-full">Parcial 3</span><span class="tab-text-short">P3</span></a>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="data-table" id="table-homework">
      <thead>
        <tr>
          <th class="col-num" width="40">#</th>
          <th class="col-name">Nombre</th>
          <?php $idx = 1;
          foreach ($filteredActs as $act): ?>
            <th style="text-align:center; min-width:64px; cursor:pointer;" title="<?= htmlspecialchars($act['nombre']) ?>"
              onclick="editActivityModal(<?= $act['id_actividad'] ?>, '<?= htmlspecialchars($act['nombre']) ?>', '<?= $act['id_topico'] ?>', '<?= htmlspecialchars($act['distribucion_preguntas'] ?? '') ?>')">
              HW<?= $idx++ ?></th>
          <?php endforeach; ?>
          <th style="text-align:center; background: rgba(255,255,255,0.1);">Promedio</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($alumnos as $i => $alumno): ?>
          <?php
          $actVals = [];
          foreach ($filteredActs as $act) {
            $val = $activityGrades[$alumno['id_alumno']][$act['id_actividad']] ?? null;
            if ($val !== null)
              $actVals[] = (float) $val;
          }
          $actAvg = count($actVals) > 0 ? array_sum($actVals) / count($actVals) : null;
          ?>
          <tr>
            <td class="col-num"><?= $i + 1 ?></td>
            <td class="col-name"><?= htmlspecialchars($alumno['nombre_completo']) ?></td>
            <?php foreach ($filteredActs as $act):
              $val = $activityGrades[$alumno['id_alumno']][$act['id_actividad']] ?? '';
              ?>
              <td style="text-align:center;">
                <input type="number" class="grade-input <?= $val !== '' && round((float) $val, 2) < 7 ? 'fail' : '' ?>"
                  data-alumno="<?= $alumno['id_alumno'] ?>" data-actividad="<?= $act['id_actividad'] ?>"
                  value="<?= $val !== '' && $val !== null ? number_format((float) $val, 2) : '' ?>" min="0" max="10"
                  step="0.01" oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'activity')">
              </td>
            <?php endforeach; ?>
            <td style="text-align:center;"
              class="font-bold <?= $actAvg !== null && round((float) $actAvg, 2) < 7 ? 'grade-fail' : '' ?>">
              <?= $actAvg !== null ? number_format($actAvg, 2) : '—' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<!-- Modal de Configuración de Tarea -->
<div class="modal-overlay" id="modal-activity">
  <div class="modal" style="max-width: 500px; text-align: left; padding: var(--space-6); border-radius: 16px; box-shadow: var(--shadow-lg);">
    <div class="modal-handle"></div>
    <h3 style="margin-bottom: var(--space-1); font-weight: 800; font-size: var(--text-lg); color: var(--primary-800);" id="modal-activity-title">Configurar Tarea</h3>
    <p style="color: var(--gray-500); font-size: var(--text-xs); margin-bottom: var(--space-4);">Elige la distribución de reactivos y publica la tarea a uno o varios grupos.</p>

    <form id="form-activity" onsubmit="saveNewActivity(event)">
      <input type="hidden" id="activity-id" name="id_actividad" value="">
      <input type="hidden" id="activity-tipo" name="tipo" value="tarea">
      <input type="hidden" id="activity-parcial" name="parcial" value="<?= $currentParcial ?>">

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
                <?= htmlspecialchars($top['nombre']) ?> (<?= (int)($top['total_preguntas'] ?? 0) ?>)
              </div>
              <div>
                <input type="number" class="form-control topic-input" 
                       id="topic-input-<?= $top['id_topico'] ?>" 
                       name="distribucion[<?= $top['id_topico'] ?>]" 
                       min="0" max="<?= (int)($top['total_preguntas'] ?? 0) ?>" value="0"
                       style="border-radius: 8px; text-align: center; height: 36px; padding: 4px; font-weight: 600;">
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Groups and Name row -->
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
          <label class="form-label" for="activity-nombre" style="font-weight: 700;">Nombre de la Tarea</label>
          <input type="text" class="form-control" id="activity-nombre" name="nombre" placeholder="Ej. Tarea 1" required style="border-radius: 8px; height: 40px; font-weight: 600;">
        </div>
      </div>

      <div style="display: flex; gap: var(--space-3); justify-content: flex-end; align-items: center;">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-activity')" style="border-radius: 20px; font-weight: bold;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="border-radius: 20px; font-weight: bold;" id="btn-save-activity">Habilitar Tarea</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openActivityModal(tipo, parcial) {
    document.getElementById('activity-id').value = '';
    document.getElementById('activity-tipo').value = tipo;
    document.getElementById('activity-parcial').value = parcial;
    document.getElementById('activity-nombre').value = '';
    
    // Reset all topic inputs to 0
    document.querySelectorAll('.topic-input').forEach(input => {
      input.value = 0;
    });

    document.getElementById('modal-activity-title').textContent = 'Configurar Tarea';
    document.getElementById('modal-activity').classList.add('active');
  }

  function editActivityModal(id, nombre, idTopico, distribucion) {
    document.getElementById('activity-id').value = id;
    document.getElementById('activity-nombre').value = nombre;
    
    // Reset all first
    document.querySelectorAll('.topic-input').forEach(input => {
      input.value = 0;
    });

    if (distribucion) {
      try {
        const dist = JSON.parse(distribucion);
        for (const [tid, qty] of Object.entries(dist)) {
          const input = document.getElementById('topic-input-' + tid);
          if (input) {
            input.value = qty;
          }
        }
      } catch (e) {}
    } else if (idTopico) {
      const input = document.getElementById('topic-input-' + idTopico);
      if (input) {
        input.value = 10;
      }
    }
    
    document.getElementById('modal-activity-title').textContent = 'Modificar Tarea';
    document.getElementById('modal-activity').classList.add('active');
  }

  function saveNewActivity(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('form-activity'));
    fetch('index.php?action=save_activity', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showToast(data.message, 'success');
          setTimeout(() => window.location.reload(), 800);
        } else { showToast(data.message, 'error'); }
      });
  }

  function saveActivityGrades(tipo) {
    const inputs = document.querySelectorAll('.grade-input[data-actividad]');
    const grades = [];
    inputs.forEach(input => {
      grades.push({
        id_actividad: input.getAttribute('data-actividad'),
        id_alumno: input.getAttribute('data-alumno'),
        calificacion: input.value
      });
    });
    const formData = new FormData();
    formData.append('type', 'activity');
    grades.forEach((g, i) => {
      formData.append('grades[' + i + '][id_actividad]', g.id_actividad);
      formData.append('grades[' + i + '][id_alumno]', g.id_alumno);
      formData.append('grades[' + i + '][calificacion]', g.calificacion);
    });
    fetch('index.php?action=save_grades', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success) { showToast(data.message, 'success'); }
        else { showToast(data.message, 'error'); }
      });
  }
</script>