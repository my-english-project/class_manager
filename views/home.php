<?php
/**
 * ClassHub — Home Dashboard (HM)
 * 
 * Groups listing + Stat cards.
 */

$totalGroups   = $totalGroups ?? 0;
$totalStudents = $totalStudents ?? 0;
$avgAttendance = $avgAttendance ?? 0;
$passCount     = $passCount ?? 0;
$failCount     = $failCount ?? 0;
$grupos        = $grupos ?? [];
$grupoActivo   = $grupoActivo ?? null;
?>

<!-- Page Title -->
<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </span>
    Dashboard
  </h1>
  <p class="page-description">Selecciona un grupo para comenzar</p>
</div>

<div class="toolbar">
  <div style="display: flex; gap: var(--space-4); align-items: center;">
    <select class="form-control form-select" onchange="setActiveCiclo(this.value)" style="width: auto; min-width: 150px; font-weight: 600; border-radius: 20px;">
      <?php foreach ($ciclosDisponibles as $c): ?>
        <option value="<?= $c ?>" <?= ($cicloActivo ?? '') === $c ? 'selected' : '' ?>>Ciclo <?= $c ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="toolbar-right" style="margin-left: auto; display: flex; gap: var(--space-3);">
    <button class="btn btn-primary" id="btn-new-cycle" onclick="openCycleModal()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 8 12 12 16 14"/></svg>
      Nuevo Ciclo
    </button>
    <button class="btn btn-primary" id="btn-new-group" onclick="openGroupModal()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Nuevo Grupo
    </button>
  </div>
</div>

<!-- Grupos Grid -->
<?php if (count($grupos) > 0): ?>
<div class="group-grid" style="margin-bottom: var(--space-6);">
  <?php foreach ($grupos as $grupo): ?>
  <div class="group-card <?= $grupoActivo && $grupoActivo['id_grupo'] == $grupo['id_grupo'] ? 'active' : '' ?>"
       onclick="setActiveGroup(<?= $grupo['id_grupo'] ?>)" id="group-<?= $grupo['id_grupo'] ?>">
    <div class="group-card-header">
      <span class="group-card-siglas"><?= htmlspecialchars($grupo['siglas'] . $grupo['cuatrimestre'] . $grupo['grupo']) ?></span>
      <span class="group-card-ciclo"><?= htmlspecialchars($grupo['ciclo']) ?></span>
    </div>
    <div class="group-card-career"><?= htmlspecialchars($grupo['carrera']) ?></div>
    <div class="group-card-meta">
      <span>📅 <?= htmlspecialchars($grupo['periodo']) ?> <?= $grupo['anio'] ?></span>
      <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <span>👥 <?= $grupo['total_alumnos'] ?> alumnos</span>
        <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); openPromoteModal(<?= $grupo['id_grupo'] ?>, '<?= addslashes($grupo['carrera']) ?>', '<?= addslashes($grupo['siglas']) ?>', <?= $grupo['cuatrimestre'] ?>, '<?= addslashes($grupo['grupo']) ?>')" style="padding: 2px 8px; font-size: 12px; height: 26px;">Promover</button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card" style="margin-bottom: var(--space-6);">
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
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
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
    </div>
    <div class="stat-value"><?= $totalGroups ?></div>
    <div class="stat-label">Grupos Activos</div>
  </div>

  <div class="stat-card stat-card--blue">
    <div class="stat-icon stat-icon--blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="stat-value"><?= $totalStudents ?></div>
    <div class="stat-label">Total Alumnos</div>
  </div>

  <div class="stat-card stat-card--teal">
    <div class="stat-icon stat-icon--teal">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <div class="stat-value"><?= $avgAttendance ?>%</div>
    <div class="stat-label">Asistencia Prom.</div>
  </div>

  <div class="stat-card stat-card--orange">
    <div class="stat-icon stat-icon--orange">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    </div>
    <div class="stat-value"><?= $passCount ?><span style="font-size: var(--text-sm); color: var(--gray-400);"> / <?= $failCount ?></span></div>
    <div class="stat-label">Aprobados / Reprobados</div>
  </div>
</div>

<!-- New Group Modal from former grupos.php -->
<div class="modal-overlay" id="modal-group">
  <div class="modal">
    <div class="modal-handle"></div>
    <h2 class="modal-title">Nuevo Grupo</h2>
    
    <form id="form-group" onsubmit="saveGroup(event)">
      <input type="hidden" id="group-id" name="id_grupo" value="0">
      
      <div class="form-group">
        <label class="form-label" for="group-carrera">Carrera</label>
        <input type="text" class="form-control" id="group-carrera" name="carrera" 
               placeholder="Ej. Ingeniería en Energías Renovables" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="group-siglas">Siglas</label>
          <input type="text" class="form-control" id="group-siglas" name="siglas" 
                 placeholder="Ej. IER" maxlength="20" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="group-cuatrimestre">Cuatrimestre</label>
          <input type="number" class="form-control" id="group-cuatrimestre" name="cuatrimestre" 
                 min="1" max="10" placeholder="1-10" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="group-grupo">Grupo</label>
          <input type="text" class="form-control" id="group-grupo" name="grupo" 
                 placeholder="A" maxlength="10" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="group-anio">Año</label>
          <input type="number" class="form-control" id="group-anio" name="anio" 
                 value="<?= date('Y') ?>" min="2020" max="2030" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="group-periodo">Periodo</label>
        <select class="form-control form-select" id="group-periodo" name="periodo" required>
          <option value="ene-abr">Enero - Abril</option>
          <option value="may-ago">Mayo - Agosto</option>
          <option value="sep-dic">Septiembre - Diciembre</option>
        </select>
      </div>

      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('modal-group')">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;" id="btn-save-group">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Promote Group Modal -->
<div class="modal-overlay" id="modal-promote">
  <div class="modal">
    <div class="modal-handle"></div>
    <h2 class="modal-title">Promover Grupo</h2>
    <p style="font-size: var(--text-sm); color: var(--gray-600); margin-bottom: var(--space-4);">
      Esto creará un nuevo grupo en el ciclo objetivo e inscribirá automáticamente a todos los alumnos actuales.
    </p>

    <form id="form-promote" onsubmit="promoteGroup(event)">
      <input type="hidden" id="promote-id-grupo" name="id_grupo_old">
      <input type="hidden" id="promote-carrera" name="carrera">
      <input type="hidden" id="promote-siglas" name="siglas">
      <input type="hidden" id="promote-grupo" name="grupo">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="promote-cuatrimestre">Nuevo Cuatrimestre</label>
          <input type="number" class="form-control" id="promote-cuatrimestre" name="cuatrimestre" 
                 min="1" max="10" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="promote-anio">Nuevo Año</label>
          <input type="number" class="form-control" id="promote-anio" name="anio" 
                 value="<?= date('Y') ?>" min="2020" max="2030" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="promote-periodo">Nuevo Periodo</label>
        <select class="form-control form-select" id="promote-periodo" name="periodo" required>
          <option value="ene-abr">Enero - Abril</option>
          <option value="may-ago">Mayo - Agosto</option>
          <option value="sep-dic">Septiembre - Diciembre</option>
        </select>
      </div>

      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('modal-promote')">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;" id="btn-save-promote">Promover</button>
      </div>
    </form>
  </div>
</div>

<!-- New Cycle Modal -->
<div class="modal-overlay" id="modal-cycle">
  <div class="modal">
    <div class="modal-handle"></div>
    <h2 class="modal-title">Nuevo Ciclo</h2>
    <form id="form-cycle" onsubmit="createCycle(event)">
      <div class="form-group">
        <label class="form-label" for="cycle-anio">Año</label>
        <input type="number" class="form-control" id="cycle-anio" name="anio" 
               value="<?= date('Y') ?>" min="2020" max="2030" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="cycle-periodo">Periodo</label>
        <select class="form-control form-select" id="cycle-periodo" name="periodo" required>
          <option value="ene-abr">Enero - Abril</option>
          <option value="may-ago">Mayo - Agosto</option>
          <option value="sep-dic">Septiembre - Diciembre</option>
        </select>
      </div>
      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('modal-cycle')">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;" id="btn-save-cycle">Crear</button>
      </div>
    </form>
  </div>
</div>


<script>
function openGroupModal() {
  document.getElementById('form-group').reset();
  document.getElementById('group-id').value = '0';
  document.getElementById('modal-group').classList.add('active');
}

function openCycleModal() {
  document.getElementById('form-cycle').reset();
  document.getElementById('modal-cycle').classList.add('active');
}

function openPromoteModal(id, carrera, siglas, cuatrimestre, grupoLetra) {
  document.getElementById('form-promote').reset();
  document.getElementById('promote-id-grupo').value = id;
  document.getElementById('promote-carrera').value = carrera;
  document.getElementById('promote-siglas').value = siglas;
  document.getElementById('promote-grupo').value = grupoLetra;
  document.getElementById('promote-cuatrimestre').value = parseInt(cuatrimestre, 10) + 1;
  document.getElementById('modal-promote').classList.add('active');
}

function createCycle(e) {
  e.preventDefault();
  const formData = new FormData(document.getElementById('form-cycle'));
  const anio = formData.get('anio');
  const periodo = formData.get('periodo');
  let periodoNum = 1;
  if(periodo === 'may-ago') periodoNum = 2;
  else if(periodo === 'sep-dic') periodoNum = 3;
  const ciclo = anio.slice(-2) + 'C' + periodoNum;
  setActiveCiclo(ciclo);
}


function saveGroup(e) {
  e.preventDefault();
  const formData = new FormData(document.getElementById('form-group'));

  fetch('index.php?action=save_grupo', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        closeModal('modal-group');
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 800);
      } else {
        showToast(data.message, 'error');
      }
    });
}

function promoteGroup(e) {
  e.preventDefault();
  const formData = new FormData(document.getElementById('form-promote'));

  fetch('index.php?action=promote_grupo', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        closeModal('modal-promote');
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 800);
      } else {
        showToast(data.message, 'error');
      }
    });
}
</script>
