<?php
/**
 * ClassHub — Materias Management View for Administrator
 */
$db = Database::getConnection();
$stmt = $db->query("SELECT * FROM materia WHERE activo = 1 ORDER BY nombre ASC");
$materias = $stmt->fetchAll();
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
      </svg>
    </span>
    Gestión de Materias
  </h1>
  <p class="page-description">Administra el catálogo de asignaturas disponibles para cursar en los grupos</p>
</div>

<div class="toolbar" style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: var(--space-4);">
  <button class="btn btn-primary" onclick="openMateriaModal()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
      <line x1="12" y1="5" x2="12" y2="19" />
      <line x1="5" y1="12" x2="19" y2="12" />
    </svg>
    Nueva Materia
  </button>
</div>

<div class="table-wrapper">
  <table class="data-table" id="table-materias">
    <thead>
      <tr>
        <th class="col-siglas" width="120">Siglas</th>
        <th class="col-name">Nombre de Materia</th>
        <th class="col-cuatrimestre" width="120" style="text-align: center;">Cuatrimestre</th>
        <th class="col-desc">Descripción</th>
        <th width="180" style="text-align: center;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($materias) === 0): ?>
        <tr>
          <td colspan="5" style="text-align: center; color: var(--gray-400); padding: var(--space-8); font-weight: 600;">
            No hay materias registradas.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($materias as $m): ?>
          <tr id="row-materia-<?= $m['id_materia'] ?>">
            <td class="col-siglas">
              <span class="badge badge-outline" style="font-size: 13px; font-weight: 700; color: var(--primary-700);"><?= htmlspecialchars($m['siglas']) ?></span>
            </td>
            <td class="col-name" style="font-weight: 700; color: var(--primary-800);"><?= htmlspecialchars($m['nombre']) ?></td>
            <td class="col-cuatrimestre" style="text-align: center; font-weight: 700; color: var(--gray-700);"><?= (int)$m['cuatrimestre'] ?>°</td>
            <td class="col-desc" style="color: var(--gray-600); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              <?= htmlspecialchars($m['descripcion'] ?? 'Sin descripción') ?>
            </td>
            <td style="text-align: center; white-space: nowrap;">
              <button class="btn btn-outline btn-sm" onclick="editMateria(<?= htmlspecialchars(json_encode($m)) ?>)" style="padding: 2px 8px; font-size: 12px; height: 26px; margin-right: 4px;">Editar</button>
              <button class="btn btn-danger btn-sm" onclick="askDeleteMateria(<?= $m['id_materia'] ?>, '<?= htmlspecialchars($m['nombre'], ENT_QUOTES) ?>')" style="padding: 2px 8px; font-size: 12px; height: 26px; background: #ef4444; border-color: #ef4444; color: white;">Baja</button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- New/Edit Materia Modal -->
<div class="modal-overlay" id="modal-materia">
  <div class="modal">
    <div class="modal-handle"></div>
    <h2 class="modal-title">Nueva Materia</h2>
    <form id="form-materia" onsubmit="saveMateria(event)">
      <input type="hidden" id="materia-id" name="id_materia" value="0">
      
      <div class="form-group">
        <label class="form-label" for="materia-nombre">Nombre de la Materia</label>
        <input type="text" class="form-control" id="materia-nombre" name="nombre" placeholder="Ej. Inglés VIII" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="materia-siglas">Siglas</label>
          <input type="text" class="form-control" id="materia-siglas" name="siglas" placeholder="Ej. ING8" maxlength="20" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="materia-cuatrimestre">Cuatrimestre</label>
          <input type="number" class="form-control" id="materia-cuatrimestre" name="cuatrimestre" min="1" max="15" placeholder="1-15" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="materia-desc">Descripción</label>
        <textarea class="form-control" id="materia-desc" name="descripcion" placeholder="Breve descripción de la asignatura..." rows="3" style="resize: vertical;"></textarea>
      </div>

      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('modal-materia')">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;" id="btn-save-materia">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Confirm Delete Materia Modal -->
<div class="modal-overlay" id="modal-delete-materia">
  <div class="modal" style="max-width: 400px; text-align: center;">
    <div class="modal-handle"></div>
    <div style="width: 56px; height: 56px; border-radius: 50%; background: #fef2f2; border: 2px solid #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
      <svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" width="28" height="28">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
    </div>
    <h3 style="font-size: 18px; font-weight: 700; color: var(--gray-800); margin-bottom: var(--space-2);">¿Dar de baja materia?</h3>
    <p style="color: var(--gray-500); font-size: var(--text-sm); line-height: 1.5; margin-bottom: var(--space-5);">
      Esta acción desactivará la materia <strong id="delete-materia-nombre"></strong> del catálogo. No se podrá seleccionar en nuevos grupos, pero se conservará en el historial.
    </p>
    <input type="hidden" id="delete-materia-id">
    <div style="display: flex; gap: var(--space-3);">
      <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeModal('modal-delete-materia')">Cancelar</button>
      <button type="button" class="btn btn-danger" style="flex: 1; background: #ef4444; border-color: #ef4444; color: white;" onclick="confirmDeleteMateria()">Dar de Baja</button>
    </div>
  </div>
</div>

<script>
  function openMateriaModal() {
    document.getElementById('form-materia').reset();
    document.getElementById('materia-id').value = '0';
    document.getElementById('materia-cuatrimestre').value = '1';
    document.getElementById('modal-materia').querySelector('.modal-title').textContent = 'Nueva Materia';
    document.getElementById('modal-materia').classList.add('active');
  }

  function editMateria(m) {
    document.getElementById('form-materia').reset();
    document.getElementById('materia-id').value = m.id_materia;
    document.getElementById('materia-nombre').value = m.nombre;
    document.getElementById('materia-siglas').value = m.siglas;
    document.getElementById('materia-cuatrimestre').value = m.cuatrimestre || '1';
    document.getElementById('materia-desc').value = m.descripcion || '';
    document.getElementById('modal-materia').querySelector('.modal-title').textContent = 'Editar Materia';
    document.getElementById('modal-materia').classList.add('active');
  }

  function saveMateria(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-materia');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    const formData = new FormData(document.getElementById('form-materia'));

    fetch('index.php?action=save_materia', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        btn.disabled = false;
        btn.textContent = 'Guardar';
        if (data.success) {
          closeModal('modal-materia');
          showToast(data.message, 'success');
          setTimeout(() => window.location.reload(), 800);
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Guardar';
        showToast('Error de red al guardar la materia.', 'error');
      });
  }

  function askDeleteMateria(id, nombre) {
    document.getElementById('delete-materia-id').value = id;
    document.getElementById('delete-materia-nombre').textContent = nombre;
    document.getElementById('modal-delete-materia').classList.add('active');
  }

  function confirmDeleteMateria() {
    const id = document.getElementById('delete-materia-id').value;
    const formData = new FormData();
    formData.append('id_materia', id);

    fetch('index.php?action=delete_materia', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        closeModal('modal-delete-materia');
        if (data.success) {
          showToast(data.message, 'success');
          const row = document.getElementById('row-materia-' + id);
          if (row) row.remove();
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(() => {
        closeModal('modal-delete-materia');
        showToast('Error de red al dar de baja la materia.', 'error');
      });
  }
</script>
