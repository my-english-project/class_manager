<?php
/**
 * ClassHub — Portfolio View (PF)
 * 
 * Dynamic portfolio activity columns with grade capture (RF-28/29/30/31).
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
        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
      </svg>
    </span>
    Portafolio de Evidencias
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
  <p class="page-description">Actividades del libro — Portafolio / Hacer (40%)</p>
</div>

<?php if (!$grupoActivo || count($alumnos) === 0): ?>
  <div class="card">
    <div class="empty-state">
      <h3><?= !$grupoActivo ? 'Sin grupo activo' : 'Sin alumnos' ?></h3>
    </div>
  </div>
<?php else: ?>

  <!-- Parcial Tabs -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); border-bottom: 1px solid var(--border-color); padding-bottom: var(--space-2);">
    <div>
      <button class="btn btn-outline btn-sm" onclick="openActivityModal('portafolio', <?= $currentParcial ?>)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
          <line x1="12" y1="5" x2="12" y2="19" />
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Agregar Actividad
      </button>
    </div>
    <div class="tabs" style="display: flex; gap: var(--space-2);">
      <a href="index.php?page=portfolio&parcial=1"
        class="btn tab-btn <?= $currentParcial === 1 ? 'btn-primary' : 'btn-outline' ?>"><span class="tab-text-full">Parcial 1</span><span class="tab-text-short">P1</span></a>
      <a href="index.php?page=portfolio&parcial=2"
        class="btn tab-btn <?= $currentParcial === 2 ? 'btn-primary' : 'btn-outline' ?>"><span class="tab-text-full">Parcial 2</span><span class="tab-text-short">P2</span></a>
      <a href="index.php?page=portfolio&parcial=3"
        class="btn tab-btn <?= $currentParcial === 3 ? 'btn-primary' : 'btn-outline' ?>"><span class="tab-text-full">Parcial 3</span><span class="tab-text-short">P3</span></a>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="data-table" id="table-portfolio">
      <thead>
        <tr>
          <th class="col-num" width="40">#</th>
          <th class="col-name">Nombre</th>
          <?php $idx = 1;
          foreach ($filteredActs as $act): ?>
            <th style="text-align:center; min-width:64px; cursor:pointer;" title="<?= htmlspecialchars($act['nombre']) ?>"
              onclick="editActivityModal(<?= $act['id_actividad'] ?>, '<?= htmlspecialchars($act['nombre']) ?>')">
              PF<?= $idx++ ?></th>
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

<!-- Activity Modal (shared with homework) -->
<div class="modal-overlay" id="modal-activity">
  <div class="modal">
    <div class="modal-handle"></div>
    <h2 class="modal-title" id="modal-activity-title">Agregar Actividad</h2>
    <form id="form-activity" onsubmit="saveNewActivity(event)">
      <input type="hidden" id="activity-id" name="id_actividad" value="">
      <input type="hidden" id="activity-tipo" name="tipo" value="portafolio">
      <input type="hidden" id="activity-parcial" name="parcial" value="<?= $currentParcial ?>">
      <div class="form-group">
        <label class="form-label" for="activity-nombre">Nombre de la Actividad</label>
        <input type="text" class="form-control" id="activity-nombre" name="nombre"
          placeholder="Ej. Act. 1, Libro U1, Tarea 1" required>
      </div>
      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;"
          onclick="closeModal('modal-activity')">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Agregar</button>
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
    document.getElementById('modal-activity-title').textContent =
      tipo === 'portafolio' ? 'Agregar Actividad de Portafolio' : 'Agregar Tarea';
    document.getElementById('modal-activity').classList.add('active');
  }

  function editActivityModal(id, nombre) {
    document.getElementById('activity-id').value = id;
    document.getElementById('activity-nombre').value = nombre;
    document.getElementById('modal-activity-title').textContent = 'Modificar Actividad';
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