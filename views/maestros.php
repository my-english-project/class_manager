<?php
/**
 * ClassHub — Maestros Management View
 */
$maestros = $maestros ?? [];
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
    </span>
    Gestión de Maestros
  </h1>
  <p class="page-description">Administra la planta docente y consulta su historial de clases</p>
</div>

<div class="toolbar" style="display: flex; justify-content: flex-end; align-items: center;">
  <button class="btn btn-primary" onclick="openMaestroModal()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Nuevo Maestro
  </button>
</div>

<div class="table-wrapper">
  <table class="data-table" id="table-maestros">
    <thead>
      <tr>
        <th class="col-name">Nombre</th>
        <th class="col-email">Email / Usuario</th>
        <th class="col-matricula" width="120">Matrícula</th>
        <th class="col-rol" width="100">Rol</th>
        <th class="col-acciones" width="150" style="text-align: right;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($maestros as $m): ?>
      <tr>
        <td class="col-name" style="font-weight: 600;"><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido_pat'] . ' ' . ($m['apellido_mat'] ?? '')) ?></td>
        <td class="col-email"><?= htmlspecialchars($m['email'] ?? '') ?></td>
        <td class="col-matricula"><span class="badge badge-outline"><?= htmlspecialchars($m['matricula'] ?? '') ?></span></td>
        <td class="col-rol">
            <span class="badge <?= $m['user_rol'] === 'admin' ? 'badge-primary' : 'badge-success' ?>">
                <?= ucfirst($m['user_rol'] ?? 'docente') ?>
            </span>
        </td>
        <td class="col-acciones" style="text-align: right;">
          <div style="display: flex; gap: var(--space-1); justify-content: flex-end;">
            <button class="btn btn-outline btn-sm" onclick="viewHistory(<?= $m['id_docente'] ?>, '<?= addslashes($m['nombre']) ?>')" title="Historial">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </button>
            <button class="btn btn-outline btn-sm" onclick="editMaestro(<?= htmlspecialchars(json_encode($m)) ?>)" title="Editar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Maestro Modal -->
<div class="modal-overlay" id="modal-maestro">
  <div class="modal">
    <div class="modal-handle"></div>
    <h2 class="modal-title" id="maestro-modal-title">Nuevo Maestro</h2>
    <form id="form-maestro" onsubmit="saveMaestro(event)">
      <input type="hidden" name="id_docente" id="maestro-id" value="0">
      
      <div class="form-group">
        <label class="form-label" for="m-nombre">Nombre(s)</label>
        <input type="text" class="form-control" id="m-nombre" name="nombre" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="m-pat">Apellido Paterno</label>
          <input type="text" class="form-control" id="m-pat" name="apellido_pat" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="m-mat">Apellido Materno</label>
          <input type="text" class="form-control" id="m-mat" name="apellido_mat">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="m-email">Email / Usuario</label>
        <input type="email" class="form-control" id="m-email" name="email" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="m-matri">Matrícula</label>
          <input type="text" class="form-control" id="m-matri" name="matricula" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="m-rol">Rol en Sistema</label>
          <select class="form-control form-select" id="m-rol" name="rol">
            <option value="docente">Maestro</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="m-pass">Contraseña (dejar vacío para no cambiar)</label>
        <input type="password" class="form-control" id="m-pass" name="password">
      </div>

      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('modal-maestro')">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- History Modal -->
<div class="modal-overlay" id="modal-history">
  <div class="modal" style="max-width: 700px;">
    <div class="modal-handle"></div>
    <h2 class="modal-title">Historial de Clases: <span id="history-name" style="color: var(--primary);"></span></h2>
    <div id="history-content" style="max-height: 400px; overflow-y: auto; margin-top: var(--space-4);">
        <!-- Carga dinámica -->
    </div>
    <div style="margin-top: var(--space-6); text-align: right;">
        <button type="button" class="btn btn-primary" onclick="closeModal('modal-history')">Cerrar</button>
    </div>
  </div>
</div>

<script>
function openMaestroModal() {
  document.getElementById('form-maestro').reset();
  document.getElementById('maestro-id').value = '0';
  document.getElementById('maestro-modal-title').textContent = 'Nuevo Maestro';
  document.getElementById('modal-maestro').classList.add('active');
}

function editMaestro(m) {
  document.getElementById('maestro-id').value = m.id_docente;
  document.getElementById('m-nombre').value = m.nombre;
  document.getElementById('m-pat').value = m.apellido_pat;
  document.getElementById('m-mat').value = m.apellido_mat || '';
  document.getElementById('m-email').value = m.email;
  document.getElementById('m-matri').value = m.matricula;
  document.getElementById('m-rol').value = m.user_rol || 'docente';
  document.getElementById('m-pass').value = '';
  document.getElementById('maestro-modal-title').textContent = 'Editar Maestro';
  document.getElementById('modal-maestro').classList.add('active');
}

function saveMaestro(e) {
  e.preventDefault();
  const fd = new FormData(e.target);
  fetch('index.php?action=save_maestro', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if(data.success) {
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 800);
      } else {
        showToast(data.message, 'error');
      }
    });
}

function viewHistory(id, name) {
    document.getElementById('history-name').textContent = name;
    document.getElementById('history-content').innerHTML = '<div style="text-align:center; padding:20px;">Cargando historial...</div>';
    document.getElementById('modal-history').classList.add('active');

    fetch('index.php?action=maestro_history&id_docente=' + id)
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                if(data.grupos.length === 0) {
                    document.getElementById('history-content').innerHTML = '<div class="empty-state">No hay historial registrado para este maestro.</div>';
                    return;
                }
                let html = '<div class="table-wrapper"><table class="data-table"><thead><tr><th width="80">Grupo</th><th width="100">Ciclo</th><th>Carrera</th><th width="80">Alumnos</th></tr></thead><tbody>';
                data.grupos.forEach(g => {
                    html += `<tr>
                        <td style="font-weight:600;">${g.siglas}${g.cuatrimestre}${g.grupo}</td>
                        <td>${g.ciclo}</td>
                        <td>${g.carrera}</td>
                        <td style="text-align:center;">${g.total_alumnos}</td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                document.getElementById('history-content').innerHTML = html;
            } else {
                showToast(data.message, 'error');
            }
        });
}
</script>
