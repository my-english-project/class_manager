<?php
/**
 * ClassHub — Periodos (Ciclos) Management View (Mod 15)
 */
$db = Database::getConnection();
$stmt = $db->query("SELECT * FROM ciclo ORDER BY codigo DESC");
$ciclos = $stmt->fetchAll();

// Determine date-based default period
$currentMonth = (int)date('n');
$defaultPeriod = 'ene-abr';
if ($currentMonth >= 5 && $currentMonth <= 8) {
    $defaultPeriod = 'may-ago';
} elseif ($currentMonth >= 9 && $currentMonth <= 12) {
    $defaultPeriod = 'sep-dic';
}
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
        <line x1="16" y1="2" x2="16" y2="6" />
        <line x1="8" y1="2" x2="8" y2="6" />
        <line x1="3" y1="10" x2="21" y2="10" />
      </svg>
    </span>
    Gestión de Periodos (Ciclos)
  </h1>
  <p class="page-description">Administra los ciclos y periodos escolares vigentes en la institución</p>
</div>

<div class="toolbar"
  style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: var(--space-4);">
  <button class="btn btn-primary" onclick="openCycleModalLocal()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
      <line x1="12" y1="5" x2="12" y2="19" />
      <line x1="5" y1="12" x2="19" y2="12" />
    </svg>
    Nuevo Periodo
  </button>
</div>

<div class="table-wrapper">
  <table class="data-table" id="table-periodos">
    <thead>
      <tr>
        <th class="col-name" width="120">ID Ciclo</th>
        <th class="col-code">Código de Ciclo</th>
        <th class="col-status" width="120">Estado</th>
        <th class="col-date">Fecha de Creación</th>
        <th width="100" style="text-align: center;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($ciclos as $c): ?>
        <tr>
          <td class="col-name" style="font-weight: 600;">#<?= $c['id_ciclo'] ?></td>
          <td class="col-code"><span class="badge badge-outline"
              style="font-size: 13px; font-weight: 700; color: var(--primary-700);"><?= htmlspecialchars($c['codigo']) ?></span>
          </td>
          <td class="col-status">
            <span class="badge <?= $c['activo'] ? 'badge-success' : 'badge-danger' ?>">
              <?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
            </span>
          </td>
          <td class="col-date" style="color: var(--gray-500); font-size: var(--text-xs);">
            <?= htmlspecialchars($c['created_at']) ?></td>
          <td style="text-align: center;">
            <button class="btn btn-outline btn-sm" onclick="editCycle(<?= htmlspecialchars(json_encode($c)) ?>)" style="padding: 2px 8px; font-size: 12px; height: 26px;">Editar</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- New/Edit Cycle Modal -->
<div class="modal-overlay" id="modal-cycle-local">
  <div class="modal">
    <div class="modal-handle"></div>
    <h2 class="modal-title">Nuevo Periodo / Ciclo</h2>
    <form id="form-cycle-local" onsubmit="createCycleLocal(event)">
      <input type="hidden" id="cycle-id-local" name="id_ciclo" value="0">
      
      <div class="form-group">
        <label class="form-label" for="cycle-anio-local">Año</label>
        <input type="number" class="form-control" id="cycle-anio-local" name="anio" value="<?= date('Y') ?>" min="2020"
          max="2030" required>
      </div>
      
      <div class="form-group">
        <label class="form-label" for="cycle-periodo-local">Periodo</label>
        <select class="form-control form-select" id="cycle-periodo-local" name="periodo" required>
          <option value="ene-abr" <?= $defaultPeriod === 'ene-abr' ? 'selected' : '' ?>>Enero - Abril</option>
          <option value="may-ago" <?= $defaultPeriod === 'may-ago' ? 'selected' : '' ?>>Mayo - Agosto</option>
          <option value="sep-dic" <?= $defaultPeriod === 'sep-dic' ? 'selected' : '' ?>>Septiembre - Diciembre</option>
        </select>
      </div>

      <div class="form-group" id="cycle-status-group" style="display: none;">
        <label class="form-label" for="cycle-activo-local">Estado</label>
        <select class="form-control form-select" id="cycle-activo-local" name="activo">
          <option value="1">Activo</option>
          <option value="0">Inactivo</option>
        </select>
      </div>

      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;"
          onclick="closeModal('modal-cycle-local')">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;" id="btn-save-cycle-local">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
  const systemDefaultPeriod = '<?= $defaultPeriod ?>';

  function openCycleModalLocal() {
    document.getElementById('form-cycle-local').reset();
    document.getElementById('cycle-id-local').value = '0';
    document.getElementById('cycle-anio-local').value = '<?= date("Y") ?>';
    document.getElementById('cycle-periodo-local').value = systemDefaultPeriod;
    document.getElementById('cycle-status-group').style.display = 'none';
    document.getElementById('modal-cycle-local').querySelector('.modal-title').textContent = 'Nuevo Periodo / Ciclo';
    document.getElementById('modal-cycle-local').classList.add('active');
  }

  function editCycle(c) {
    document.getElementById('form-cycle-local').reset();
    document.getElementById('cycle-id-local').value = c.id_ciclo;
    document.getElementById('modal-cycle-local').querySelector('.modal-title').textContent = 'Editar Periodo / Ciclo';
    
    // Parse cycle code (e.g. "26C2")
    const code = c.codigo;
    const yearSuffix = code.substring(0, 2);
    const periodCode = code.substring(2);
    
    document.getElementById('cycle-anio-local').value = '20' + yearSuffix;
    
    const periodMap = { 'C1': 'ene-abr', 'C2': 'may-ago', 'C3': 'sep-dic' };
    document.getElementById('cycle-periodo-local').value = periodMap[periodCode] || 'ene-abr';
    
    // Show active toggler in edit mode
    document.getElementById('cycle-status-group').style.display = 'block';
    document.getElementById('cycle-activo-local').value = c.activo;
    
    document.getElementById('modal-cycle-local').classList.add('active');
  }

  function createCycleLocal(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('form-cycle-local'));
    const isEdit = parseInt(document.getElementById('cycle-id-local').value, 10) > 0;

    fetch('index.php?action=save_ciclo', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          closeModal('modal-cycle-local');
          showToast(data.message, 'success');
          
          if (isEdit) {
            setTimeout(() => window.location.reload(), 800);
          } else {
            const anio = formData.get('anio');
            const periodo = formData.get('periodo');
            let periodoNum = 1;
            if (periodo === 'may-ago') periodoNum = 2;
            else if (periodo === 'sep-dic') periodoNum = 3;
            const ciclo = anio.slice(-2) + 'C' + periodoNum;
            
            setTimeout(() => {
              if (typeof setActiveCiclo === 'function') {
                setActiveCiclo(ciclo);
              } else {
                window.location.reload();
              }
            }, 800);
          }
        } else {
          showToast(data.message, 'error');
        }
      });
  }
</script>