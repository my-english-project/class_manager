<?php
/**
 * ClassHub — Alumnos View
 * 
 * Student management for the active group (RF-08/09/10/11).
 */

$alumnos = $alumnos ?? [];
$grupoActivo = $grupoActivo ?? null;
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
    </span>
    Alumnos
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
  <p class="page-description">
    <?php if ($grupoActivo): ?>
      Gestionar alumnos de
      <?= htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) ?>
    <?php else: ?>
      Selecciona un grupo para gestionar alumnos
    <?php endif; ?>
  </p>
</div>

<?php if (!$grupoActivo): ?>
  <div class="card">
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <rect x="3" y="3" width="7" height="7" />
        <rect x="14" y="3" width="7" height="7" />
        <rect x="14" y="14" width="7" height="7" />
        <rect x="3" y="14" width="7" height="7" />
      </svg>
      <h3>Sin grupo activo</h3>
      <p>Selecciona un grupo primero.</p>
      <a href="index.php?page=grupos" class="btn btn-primary">Ir a Grupos</a>
    </div>
  </div>
<?php else: ?>

  <div class="toolbar" style="display: flex; justify-content: space-between; align-items: center;">
    <span class="text-sm" style="color: var(--text-secondary); font-weight: 500;"><?= count($alumnos) ?> alumnos</span>
    <button class="btn btn-primary" id="btn-new-student" onclick="openStudentModal()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
        <line x1="12" y1="5" x2="12" y2="19" />
        <line x1="5" y1="12" x2="19" y2="12" />
      </svg>
      Agregar Alumno
    </button>
  </div>

  <?php if (count($alumnos) > 0): ?>
    <div class="table-wrapper">
      <table class="data-table" id="table-alumnos">
        <thead>
          <tr>
            <th class="col-num" width="40">#</th>
            <th class="col-matricula" width="120">Matrícula</th>
            <th class="col-name">Nombre</th>
            <th class="col-acciones" width="120">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alumnos as $i => $alumno): ?>
            <tr id="row-alumno-<?= $alumno['id_alumno'] ?>">
              <td class="col-num"><?= $i + 1 ?></td>
              <td class="col-matricula"><?= htmlspecialchars($alumno['matricula']) ?></td>
              <td class="col-name"><?= htmlspecialchars($alumno['nombre_completo']) ?></td>
              <td class="col-acciones">
                <div style="display:flex; gap: var(--space-1);">
                  <button class="btn btn-outline btn-sm" onclick="editStudent(<?= htmlspecialchars(json_encode($alumno)) ?>)"
                    title="Editar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                  </button>
                  <button class="btn btn-outline btn-sm"
                    onclick="deleteStudent(<?= $alumno['id_alumno'] ?>, '<?= htmlspecialchars($alumno['nombre_completo']) ?>')"
                    title="Eliminar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                      <polyline points="3 6 5 6 21 6" />
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
        </svg>
        <h3>Sin alumnos</h3>
        <p>Agrega alumnos a este grupo.</p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Add Student Modal -->
  <div class="modal-overlay" id="modal-student">
    <div class="modal">
      <div class="modal-handle"></div>
      <h2 class="modal-title">Agregar Alumno</h2>

      <form id="form-student" onsubmit="saveStudent(event)">
        <input type="hidden" id="student-id" name="id_alumno">
        <div class="form-group">
          <label class="form-label" for="student-matricula">Matrícula</label>
          <input type="text" class="form-control" id="student-matricula" name="matricula" placeholder="Ej. 612310503"
            required>
        </div>
        <div class="form-group">
          <label class="form-label" for="student-apellido-pat">Apellido Paterno</label>
          <input type="text" class="form-control" id="student-apellido-pat" name="apellido_pat" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="student-apellido-mat">Apellido Materno</label>
          <input type="text" class="form-control" id="student-apellido-mat" name="apellido_mat">
        </div>
        <div class="form-group">
          <label class="form-label" for="student-nombre">Nombre(s)</label>
          <input type="text" class="form-control" id="student-nombre" name="nombre" required>
        </div>

        <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
          <button type="button" class="btn btn-outline" style="flex:1;"
            onclick="closeModal('modal-student')">Cancelar</button>
          <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openStudentModal() {
      document.getElementById('form-student').reset();
      document.getElementById('student-id').value = '';
      document.querySelector('.modal-title').textContent = 'Agregar Alumno';
      document.getElementById('modal-student').classList.add('active');
    }

    function editStudent(alumno) {
      document.getElementById('form-student').reset();
      document.getElementById('student-id').value = alumno.id_alumno;
      document.getElementById('student-matricula').value = alumno.matricula;
      document.getElementById('student-apellido-pat').value = alumno.apellido_pat;
      document.getElementById('student-apellido-mat').value = alumno.apellido_mat || '';
      document.getElementById('student-nombre').value = alumno.nombre;

      document.querySelector('.modal-title').textContent = 'Editar Alumno';
      document.getElementById('modal-student').classList.add('active');
    }

    function saveStudent(e) {
      e.preventDefault();
      const formData = new FormData(document.getElementById('form-student'));

      fetch('index.php?action=save_alumno', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            closeModal('modal-student');
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 800);
          } else {
            showToast(data.message, 'error');
          }
        });
    }

    function deleteStudent(id, name) {
      if (!confirm('¿Eliminar a ' + name + ' de este grupo?')) return;

      const formData = new FormData();
      formData.append('id_alumno', id);

      fetch('index.php?action=delete_alumno', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('row-alumno-' + id).remove();
          } else {
            showToast(data.message, 'error');
          }
        });
    }
  </script>
<?php endif; ?>