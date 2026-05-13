<?php
/**
 * ClassHub — Sesiones View
 * 
 * Session (class dates) management for the active group.
 */

$sesiones    = $sesiones ?? [];
$grupoActivo = $grupoActivo ?? null;
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </span>
    Sesiones de Clase
  </h1>
  <p class="page-description">Calendario de sesiones</p>
</div>

<?php if (!$grupoActivo): ?>
<div class="card"><div class="empty-state"><h3>Sin grupo activo</h3></div></div>
<?php else: ?>

<div class="toolbar">
  <button class="btn btn-primary" onclick="document.getElementById('modal-session-mgmt').classList.add('active')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Agregar Fecha
  </button>
</div>

<?php if (count($sesiones) > 0): ?>
<div class="table-wrapper">
  <table class="data-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Fecha</th>
        <th>Día</th>
        <th>Parcial</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
      foreach ($sesiones as $i => $s): 
        $ts = strtotime($s['fecha']);
      ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= date('d/m/Y', $ts) ?></td>
        <td><?= $dias[date('w', $ts)] ?></td>
        <td>Parcial <?= $s['parcial'] ?></td>
        <td>
          <button class="btn btn-outline btn-sm" onclick="deleteSession(<?= $s['id_sesion'] ?>)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="card"><div class="empty-state"><h3>Sin sesiones</h3><p>Agrega las fechas de tus clases.</p></div></div>
<?php endif; ?>

<!-- Modal -->
<div class="modal-overlay" id="modal-session-mgmt">
  <div class="modal">
    <div class="modal-handle"></div>
    <h2 class="modal-title">Agregar Fecha de Clase</h2>
    <form id="form-session-mgmt" onsubmit="saveSesion(event)">
      <div class="form-group">
        <label class="form-label" for="ses-fecha">Fecha</label>
        <input type="date" class="form-control" id="ses-fecha" name="fecha" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="ses-parcial">Parcial</label>
        <select class="form-control form-select" id="ses-parcial" name="parcial">
          <option value="1">Parcial 1</option>
          <option value="2">Parcial 2</option>
          <option value="3">Parcial 3</option>
        </select>
      </div>
      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('modal-session-mgmt')">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
function saveSesion(e) {
  e.preventDefault();
  const fd = new FormData(document.getElementById('form-session-mgmt'));
  fetch('index.php?action=save_sesion', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
      else { showToast(data.message, 'error'); }
    });
}

function deleteSession(id) {
  if (!confirm('¿Eliminar esta sesión?')) return;
  const fd = new FormData();
  fd.append('id_sesion', id);
  fetch('index.php?action=delete_sesion', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
      else { showToast(data.message, 'error'); }
    });
}
</script>
<?php endif; ?>
