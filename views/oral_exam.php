<?php
/**
 * ClassHub — Oral Exam View (OE)
 * 
 * Oral exam grade capture by parcial (RF-26/27).
 * Allows creating oral exam topics, parsing markdown paragraphs,
 * assigning them randomly and balancedly, and launching the review interface.
 */

$alumnos = $alumnos ?? [];
$grades = $grades ?? [];
$grupoActivo = $grupoActivo ?? null;
$assigned = $assigned ?? [];
$topics = $topics ?? [];
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
        <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
        <line x1="12" y1="19" x2="12" y2="23" />
        <line x1="8" y1="23" x2="16" y2="23" />
      </svg>
    </span>
    Examen Oral
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
</div>

<?php if (!$grupoActivo || count($alumnos) === 0): ?>
  <div class="card">
    <div class="empty-state">
      <h3><?= !$grupoActivo ? 'Sin grupo activo' : 'Sin alumnos' ?></h3>
    </div>
  </div>
<?php else: ?>

  <!-- Actions Toolbar -->
  <div class="toolbar"
    style="margin-bottom: var(--space-4); display: flex; gap: var(--space-3); flex-wrap: wrap; justify-content: flex-end;">
    <button class="btn btn-primary" onclick="openModal('modal-create-topic')"
      style="display: inline-flex; align-items: center; gap: 6px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <path d="M12 5v14M5 12h14" />
      </svg>
      Nuevo examen
    </button>
    <button class="btn btn-outline" onclick="openModal('modal-assign-exam')"
      style="display: inline-flex; align-items: center; gap: 6px; border-color: var(--primary); color: var(--primary);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <polyline points="16 11 18 13 22 9" />
      </svg>
      Asignar examen
    </button>
  </div>

  <!-- Student Grading Table -->
  <div class="table-wrapper">
    <table class="data-table" id="table-oe">
      <thead>
        <tr>
          <th class="col-num" width="40">#</th>
          <th class="col-matricula" width="120">Matrícula</th>
          <th class="col-name">Nombre</th>
          <th style="text-align:center;"><span class="tab-text-full">Parcial 1</span><span
              class="tab-text-short">P1</span></th>
          <th style="text-align:center;"><span class="tab-text-full">Parcial 2</span><span
              class="tab-text-short">P2</span></th>
          <th style="text-align:center;"><span class="tab-text-full">Parcial 3</span><span
              class="tab-text-short">P3</span></th>
          <th style="text-align:center;">Promedio</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($alumnos as $i => $alumno): ?>
          <?php
          $aid = $alumno['id_alumno'];
          $p1 = $grades[$aid][1] ?? '';
          $p2 = $grades[$aid][2] ?? '';
          $p3 = $grades[$aid][3] ?? '';

          $vals = array_filter([$p1, $p2, $p3], fn($v) => $v !== '' && $v !== null);
          $avg = count($vals) > 0 ? array_sum($vals) / count($vals) : null;
          ?>
          <tr>
            <td class="col-num"><?= $i + 1 ?></td>
            <td class="col-matricula"><?= htmlspecialchars($alumno['matricula']) ?></td>
            <td class="col-name"><?= htmlspecialchars($alumno['nombre_completo']) ?></td>

            <!-- Parcial 1 -->
            <td style="text-align:center; vertical-align: middle; padding: var(--space-2) var(--space-3);">
              <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                <input type="number" class="grade-input <?= $p1 !== '' && round((float) $p1, 2) < 7 ? 'fail' : '' ?>"
                  data-alumno="<?= $aid ?>" data-parcial="1"
                  value="<?= $p1 !== '' && $p1 !== null ? number_format((float) $p1, 2) : '' ?>" min="0" max="10"
                  step="0.01" oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'oe')">
                <?php if (isset($assigned[$aid][1]) && !empty($assigned[$aid][1]['id_oral_text'])): ?>
                  <a href="index.php?page=oral_exam_review&id_alumno=<?= $aid ?>&parcial=1" class="btn btn-outline"
                    style="font-size:14px; padding:6px; border-radius:6px; height:32px; width:32px; display:inline-flex; align-items:center; justify-content:center; border-color:var(--primary); color:var(--primary);"
                    title="Evaluar examen oral: <?= htmlspecialchars($assigned[$aid][1]['topic_name']) ?>">🎙️</a>
                <?php endif; ?>
              </div>
            </td>

            <!-- Parcial 2 -->
            <td style="text-align:center; vertical-align: middle; padding: var(--space-2) var(--space-3);">
              <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                <input type="number" class="grade-input <?= $p2 !== '' && round((float) $p2, 2) < 7 ? 'fail' : '' ?>"
                  data-alumno="<?= $aid ?>" data-parcial="2"
                  value="<?= $p2 !== '' && $p2 !== null ? number_format((float) $p2, 2) : '' ?>" min="0" max="10"
                  step="0.01" oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'oe')">
                <?php if (isset($assigned[$aid][2]) && !empty($assigned[$aid][2]['id_oral_text'])): ?>
                  <a href="index.php?page=oral_exam_review&id_alumno=<?= $aid ?>&parcial=2" class="btn btn-outline"
                    style="font-size:14px; padding:6px; border-radius:6px; height:32px; width:32px; display:inline-flex; align-items:center; justify-content:center; border-color:var(--primary); color:var(--primary);"
                    title="Evaluar examen oral: <?= htmlspecialchars($assigned[$aid][2]['topic_name']) ?>">🎙️</a>
                <?php endif; ?>
              </div>
            </td>

            <!-- Parcial 3 -->
            <td style="text-align:center; vertical-align: middle; padding: var(--space-2) var(--space-3);">
              <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                <input type="number" class="grade-input <?= $p3 !== '' && round((float) $p3, 2) < 7 ? 'fail' : '' ?>"
                  data-alumno="<?= $aid ?>" data-parcial="3"
                  value="<?= $p3 !== '' && $p3 !== null ? number_format((float) $p3, 2) : '' ?>" min="0" max="10"
                  step="0.01" oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'oe')">
                <?php if (isset($assigned[$aid][3]) && !empty($assigned[$aid][3]['id_oral_text'])): ?>
                  <a href="index.php?page=oral_exam_review&id_alumno=<?= $aid ?>&parcial=3" class="btn btn-outline"
                    style="font-size:14px; padding:6px; border-radius:6px; height:32px; width:32px; display:inline-flex; align-items:center; justify-content:center; border-color:var(--primary); color:var(--primary);"
                    title="Evaluar examen oral: <?= htmlspecialchars($assigned[$aid][3]['topic_name']) ?>">🎙️</a>
                <?php endif; ?>
              </div>
            </td>

            <td style="text-align:center; vertical-align: middle;"
              class="font-bold <?= $avg !== null && round((float) $avg, 2) < 7 ? 'grade-fail' : '' ?>">
              <?= $avg !== null ? number_format($avg, 2) : '—' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal 1: Create Oral Exam Topic -->
  <div class="modal-overlay" id="modal-create-topic">
    <div class="modal" style="max-width: 600px;">
      <div class="modal-header"
        style="display:flex; justify-content:space-between; align-items:center; margin-bottom: var(--space-4);">
        <h3 style="margin:0;">Subir Nuevo Examen Oral</h3>
        <button onclick="closeModal('modal-create-topic')"
          style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
      </div>
      <form id="form-create-topic" onsubmit="submitCreateTopic(event)">
        <div class="form-group">
          <label class="form-label" for="nombre_tema">Nombre del tema</label>
          <input type="text" class="form-control" id="nombre_tema" name="nombre" required
            placeholder="Food, animals, places, ...">
        </div>
        <div class="form-group" style="margin-top: var(--space-3);">
          <label class="form-label" for="markdown_text">Contenido (Párrafos separados por líneas en blanco)</label>
          <textarea class="form-control" id="markdown_text" name="markdown_text" required rows="10"
            placeholder="Pega el texto del examen aquí." style="font-family: inherit; resize: vertical;"></textarea>
        </div>
        <div style="display: flex; gap: var(--space-3); margin-top: var(--space-5); justify-content: flex-end;">
          <button type="button" class="btn btn-outline" onclick="closeModal('modal-create-topic')">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="btn-save-topic">Guardar Tema</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal 2: Assign Oral Exam -->
  <div class="modal-overlay" id="modal-assign-exam">
    <div class="modal" style="max-width: 450px;">
      <div class="modal-header"
        style="display:flex; justify-content:space-between; align-items:center; margin-bottom: var(--space-4);">
        <h3 style="margin:0;">Asignar Examen Oral</h3>
        <button onclick="closeModal('modal-assign-exam')"
          style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
      </div>
      <form id="form-assign-exam" onsubmit="submitAssignExam(event)">
        <input type="hidden" name="id_grupo" value="<?= $grupoActivo['id_grupo'] ?>">

        <div class="form-group">
          <label class="form-label" for="id_oral_topic">Selecciona el tema</label>
          <select class="form-control form-select" id="id_oral_topic" name="id_oral_topic" required>
            <option value="" disabled selected>-- Selecciona un tema --</option>
            <?php foreach ($topics as $top): ?>
              <option value="<?= $top['id_oral_topic'] ?>"><?= htmlspecialchars($top['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" style="margin-top: var(--space-3);">
          <label class="form-label" for="parcial_assign">Selecciona el parcial</label>
          <select class="form-control form-select" id="parcial_assign" name="parcial" required>
            <option value="1">Parcial 1</option>
            <option value="2">Parcial 2</option>
            <option value="3">Parcial 3</option>
          </select>
        </div>

        <div style="display: flex; gap: var(--space-3); margin-top: var(--space-5); justify-content: flex-end;">
          <button type="button" class="btn btn-outline" onclick="closeModal('modal-assign-exam')">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="btn-submit-assign">Asignar examen a alumnos</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(id) {
      document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
      document.getElementById(id).classList.remove('active');
    }

    function submitCreateTopic(e) {
      e.preventDefault();
      const btn = document.getElementById('btn-save-topic');
      btn.disabled = true;
      btn.textContent = 'Guardando...';

      const fd = new FormData(e.target);
      fetch('index.php?action=save_oral_topic', {
        method: 'POST',
        body: fd
      })
        .then(r => r.json())
        .then(res => {
          btn.disabled = false;
          btn.textContent = 'Guardar Tema';
          if (res.success) {
            closeModal('modal-create-topic');
            e.target.reset();
            if (typeof showToast === 'function') {
              showToast(res.message, 'success');
            } else {
              alert(res.message);
            }
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          } else {
            alert(res.message);
          }
        })
        .catch(() => {
          btn.disabled = false;
          btn.textContent = 'Guardar Tema';
          alert('Error de red al guardar tema');
        });
    }

    function submitAssignExam(e) {
      e.preventDefault();
      const btn = document.getElementById('btn-submit-assign');
      btn.disabled = true;
      btn.textContent = 'Asignando...';

      const fd = new FormData(e.target);
      fetch('index.php?action=assign_oral_exam', {
        method: 'POST',
        body: fd
      })
        .then(r => r.json())
        .then(res => {
          btn.disabled = false;
          btn.textContent = 'Asignar de Forma Equilibrada';
          if (res.success) {
            closeModal('modal-assign-exam');
            if (typeof showToast === 'function') {
              showToast(res.message, 'success');
            } else {
              alert(res.message);
            }
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          } else {
            alert(res.message);
          }
        })
        .catch(() => {
          btn.disabled = false;
          btn.textContent = 'Asignar de Forma Equilibrada';
          alert('Error de red al asignar examen');
        });
    }
  </script>

<?php endif; ?>