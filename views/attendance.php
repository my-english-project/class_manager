<?php
/**
 * ClassHub — Attendance View (AT)
 * 
 * Attendance grid with cyclic click states.
 * Green → Yellow → Red → Orange → Green.
 */

$sesiones = $sesiones ?? [];
$alumnos = $alumnos ?? [];
$asistencias = $asistencias ?? [];
$grupoActivo = $grupoActivo ?? null;
$currentParcial = (int) ($_GET['parcial'] ?? 1);
$sesionesParcial = array_filter($sesiones, fn($s) => $s['parcial'] == $currentParcial);
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
    </span>
    Asistencia
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
  <p class="page-description">Registro de asistencia por sesión</p>
</div>

<?php if (!$grupoActivo): ?>
  <div class="card">
    <div class="empty-state">
      <h3>Sin grupo activo</h3>
      <p>Selecciona un grupo primero.</p><a href="index.php?page=grupos" class="btn btn-primary">Ir a Grupos</a>
    </div>
  </div>
<?php elseif (count($alumnos) === 0): ?>
  <div class="card">
    <div class="empty-state">
      <h3>Sin alumnos</h3>
      <p>Agrega alumnos al grupo.</p><a href="index.php?page=alumnos" class="btn btn-primary">Agregar Alumnos</a>
    </div>
  </div>
<?php else: ?>

  <!-- Legend -->
  <div class="attendance-legend">
    <div class="legend-item">
      <div class="legend-dot legend-dot--present"></div><span>Asistencia</span>
    </div>
    <div class="legend-item">
      <div class="legend-dot legend-dot--late"></div><span>Retardo</span>
    </div>
    <div class="legend-item">
      <div class="legend-dot legend-dot--absent"></div><span>Falta</span>
    </div>
    <div class="legend-item">
      <div class="legend-dot legend-dot--justified"></div><span>Justificado</span>
    </div>
  </div>

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); border-bottom: 1px solid var(--border-color); padding-bottom: var(--space-2);">
    <div>
      <button class="btn btn-outline btn-sm" id="btn-add-session" onclick="openSessionModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
          <line x1="16" y1="2" x2="16" y2="6" />
          <line x1="8" y1="2" x2="8" y2="6" />
          <line x1="3" y1="10" x2="21" y2="10" />
        </svg>
        Agregar Fecha
      </button>
    </div>
    <div class="tabs" style="display: flex; gap: var(--space-2);">
      <a href="index.php?page=attendance&parcial=1"
        class="btn tab-btn <?= $currentParcial === 1 ? 'btn-primary' : 'btn-outline' ?>"><span class="tab-text-full">Parcial 1</span><span class="tab-text-short">P1</span></a>
      <a href="index.php?page=attendance&parcial=2"
        class="btn tab-btn <?= $currentParcial === 2 ? 'btn-primary' : 'btn-outline' ?>"><span class="tab-text-full">Parcial 2</span><span class="tab-text-short">P2</span></a>
      <a href="index.php?page=attendance&parcial=3"
        class="btn tab-btn <?= $currentParcial === 3 ? 'btn-primary' : 'btn-outline' ?>"><span class="tab-text-full">Parcial 3</span><span class="tab-text-short">P3</span></a>
    </div>
  </div>

  <!-- Attendance Table -->
  <?php if (count($sesionesParcial) > 0): ?>
    <div class="table-wrapper">
      <table class="data-table" id="table-attendance">
        <thead>
          <tr>
            <th class="col-num" width="40">#</th>
            <th class="col-name">Nombre</th>
            <?php 
            foreach ($sesionesParcial as $s): ?>
              <th style="text-align:center; min-width:48px; cursor:pointer;" title="<?= htmlspecialchars($s['tema'] ?: 'Editar Fecha') ?>"
                onclick="editSessionModal(<?= $s['id_sesion'] ?>, '<?= $s['fecha'] ?>', <?= $s['parcial'] ?>, '<?= htmlspecialchars($s['tema'] ?? '', ENT_QUOTES) ?>')">
                <?= date('d/m', strtotime($s['fecha'])) ?>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alumnos as $i => $alumno): ?>
            <tr>
              <td class="col-num"><?= $i + 1 ?></td>
              <td class="col-name"><?= htmlspecialchars($alumno['nombre_completo']) ?></td>
              <?php foreach ($sesionesParcial as $s): ?>
                <?php
                $estado = $asistencias[$alumno['id_alumno']][$s['id_sesion']] ?? '';
                $labels = ['asistencia' => 'A', 'retardo' => 'R', 'falta' => 'F', 'justificado' => 'J', '' => '·'];
                ?>
                <td style="text-align:center; padding: var(--space-2);">
                  <div class="cell-attendance" data-state="<?= $estado ?>" data-sesion="<?= $s['id_sesion'] ?>"
                    data-alumno="<?= $alumno['id_alumno'] ?>" onclick="cycleAttendance(this)"
                    title="<?= $estado ?: 'Sin registro' ?>">
                    <?= $labels[$estado] ?>
                  </div>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="empty-state">
        <h3>Sin sesiones</h3>
        <p>Agrega fechas de clase para el Parcial <?= $currentParcial ?>.</p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Add/Edit Session Modal -->
  <div class="modal-overlay" id="modal-session">
    <div class="modal">
      <div class="modal-handle"></div>
      <h2 class="modal-title" id="modal-session-title">Agregar Fecha de Clase</h2>
      <form id="form-session" onsubmit="saveSession(event)">
        <input type="hidden" name="id_sesion" id="session-id">
        <div class="form-group">
          <label class="form-label" for="session-fecha">Fecha</label>
          <input type="date" class="form-control" id="session-fecha" name="fecha" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="session-parcial">Parcial</label>
          <select class="form-control form-select" id="session-parcial" name="parcial" required>
            <option value="1" <?= $currentParcial === 1 ? 'selected' : '' ?>>Parcial 1</option>
            <option value="2" <?= $currentParcial === 2 ? 'selected' : '' ?>>Parcial 2</option>
            <option value="3" <?= $currentParcial === 3 ? 'selected' : '' ?>>Parcial 3</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="session-tema">Tema (Opcional)</label>
          <input type="text" class="form-control" id="session-tema" name="tema" placeholder="Ej. Presente Simple">
        </div>
        <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
          <button type="button" class="btn btn-danger" id="btn-delete-session" onclick="deleteSession()" style="display:none; flex:1;">Eliminar</button>
          <button type="button" class="btn btn-outline" style="flex:1;"
            onclick="closeModal('modal-session')">Cancelar</button>
          <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openSessionModal() {
      document.getElementById('form-session').reset();
      document.getElementById('session-id').value = '';
      document.getElementById('session-fecha').value = new Date().toISOString().split('T')[0];
      document.getElementById('session-parcial').value = '<?= $currentParcial ?>';
      document.getElementById('session-tema').value = '';
      document.getElementById('modal-session-title').textContent = 'Agregar Fecha de Clase';
      document.getElementById('btn-delete-session').style.display = 'none';
      document.getElementById('modal-session').classList.add('active');
    }

    function editSessionModal(id, fecha, parcial, tema) {
      document.getElementById('form-session').reset();
      document.getElementById('session-id').value = id;
      document.getElementById('session-fecha').value = fecha;
      document.getElementById('session-parcial').value = parcial;
      document.getElementById('session-tema').value = tema;
      document.getElementById('modal-session-title').textContent = 'Modificar Fecha de Clase';
      document.getElementById('btn-delete-session').style.display = 'block';
      document.getElementById('modal-session').classList.add('active');
    }

    function saveSession(e) {
      e.preventDefault();
      const formData = new FormData(document.getElementById('form-session'));
      fetch('index.php?action=save_sesion', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 800);
          } else {
            showToast(data.message, 'error');
          }
        });
    }

    function deleteSession() {
      if (!confirm('¿Seguro de que deseas eliminar esta sesión? Esta acción también eliminará todos los registros de asistencia asociados.')) return;
      
      const formData = new FormData();
      formData.append('id_sesion', document.getElementById('session-id').value);
      
      fetch('index.php?action=delete_sesion', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 800);
          } else {
            showToast(data.message, 'error');
          }
        });
    }

    // Cyclic attendance click handler (RN-06)
    function cycleAttendance(cell) {
      const states = ['', 'asistencia', 'retardo', 'falta', 'justificado'];
      const labels = { '': '·', 'asistencia': 'A', 'retardo': 'R', 'falta': 'F', 'justificado': 'J' };

      let currentState = cell.getAttribute('data-state');
      let idx = states.indexOf(currentState);
      let nextIdx = (idx + 1) % states.length;

      if (nextIdx === 0 && currentState !== '') {
        nextIdx = 1;
      }

      const nextState = states[nextIdx];
      cell.setAttribute('data-state', nextState);
      cell.textContent = labels[nextState];
      cell.title = nextState || 'Sin registro';

      if (nextState) {
        const formData = new FormData();
        formData.append('id_sesion', cell.getAttribute('data-sesion'));
        formData.append('id_alumno', cell.getAttribute('data-alumno'));
        formData.append('estado', nextState);

        fetch('index.php?action=save_attendance', { method: 'POST', body: formData })
          .then(r => r.json())
          .then(data => {
            if (!data.success) {
              showToast('Error al guardar asistencia', 'error');
            }
          });
      }
    }
  </script>
<?php endif; ?>