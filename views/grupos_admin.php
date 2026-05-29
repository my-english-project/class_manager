<?php
/**
 * ClassHub — Grupos Management View for Administrator (Mod 15)
 */
$db = Database::getConnection();
$cicloActivo = $_SESSION['ciclo_activo'] ?? '';

// Fetch all available cycles
$stmtCiclos = $db->query("SELECT codigo FROM ciclo ORDER BY codigo DESC");
$ciclosDisponibles = $stmtCiclos->fetchAll(PDO::FETCH_COLUMN);

if (empty($cicloActivo) && !empty($ciclosDisponibles)) {
  $cicloActivo = $ciclosDisponibles[0];
  $_SESSION['ciclo_activo'] = $cicloActivo;
}

// Fetch teachers for group creation/edit
$stmtD = $db->query("SELECT * FROM docente WHERE activo = 1 ORDER BY nombre, apellido_pat");
$docentes = $stmtD->fetchAll();

// Fetch active subjects for group creation/edit
$stmtM = $db->query("SELECT * FROM materia WHERE activo = 1 ORDER BY nombre");
$materias = $stmtM->fetchAll();

// Fetch groups of this cycle
$stmtG = $db->prepare("
    SELECT g.*, m.nombre as materia_nombre, d.nombre as docente_nombre, d.apellido_pat as docente_apellido,
           (SELECT COUNT(*) FROM grupo_alumno ga WHERE ga.id_grupo = g.id_grupo) as total_alumnos
    FROM grupo g
    LEFT JOIN materia m ON g.id_materia = m.id_materia
    LEFT JOIN docente d ON g.id_docente = d.id_docente
    WHERE g.ciclo = :ciclo AND g.activo = 1
    ORDER BY g.siglas, g.cuatrimestre, g.grupo
");
$stmtG->execute([':ciclo' => $cicloActivo]);
$grupos = $stmtG->fetchAll();
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M9 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M2 21v-2a4 4 0 0 1 4-4h5a4 4 0 0 1 4 4v2" />
        <circle cx="8.5" cy="7" r="4" />
      </svg>
    </span>
    Gestión de Grupos
  </h1>
  <p class="page-description">Consulta y administra la asignación de materias, carreras y maestros para el ciclo
    seleccionado</p>
</div>

<div class="toolbar"
  style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); gap: var(--space-4); flex-wrap: wrap;">
  <div style="display: flex; gap: var(--space-2); align-items: center;">
    <span style="font-size: var(--text-sm); font-weight: 700; color: var(--gray-500);">Filtrar por Ciclo:</span>
    <select class="form-control form-select" onchange="setActiveCicloLocal(this.value)"
      style="width: auto; min-width: 150px; font-weight: 600; border-radius: 20px; height: 36px; padding: 0 var(--space-4);">
      <?php foreach ($ciclosDisponibles as $c): ?>
        <option value="<?= $c ?>" <?= $cicloActivo === $c ? 'selected' : '' ?>>Ciclo <?= $c ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <button class="btn btn-primary" onclick="openGroupModal()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
      <line x1="12" y1="5" x2="12" y2="19" />
      <line x1="5" y1="12" x2="19" y2="12" />
    </svg>
    Nuevo Grupo
  </button>
</div>

<div class="table-wrapper">
  <table class="data-table" id="table-grupos-admin">
    <thead>
      <tr>
        <th class="col-name" width="120">Grupo</th>
        <th class="col-career">Carrera</th>
        <th class="col-subject">Materia Asignada</th>
        <th class="col-docente">Maestro Asignado</th>
        <th class="col-students" width="120" style="text-align: center;">Alumnos</th>
        <th width="180" style="text-align: center;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($grupos) === 0): ?>
        <tr>
          <td colspan="6" style="text-align: center; color: var(--gray-400); padding: var(--space-8); font-weight: 600;">
            No hay grupos registrados para el Ciclo <?= htmlspecialchars($cicloActivo) ?>.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($grupos as $g): ?>
          <tr>
            <td class="col-name" style="font-weight: 700; color: var(--primary-700);">
              <?= htmlspecialchars($g['siglas'] . $g['cuatrimestre'] . $g['grupo']) ?></td>
            <td class="col-career" style="font-weight: 500;"><?= htmlspecialchars($g['carrera'] ?? '') ?></td>
            <td class="col-subject" style="color: var(--gray-600);">
              <?= htmlspecialchars($g['materia_nombre'] ?? 'Sin Asignar') ?></td>
            <td class="col-docente" style="font-weight: 600;">
              <?= $g['docente_nombre'] ? htmlspecialchars($g['docente_nombre'] . ' ' . $g['docente_apellido']) : '<span style="color: var(--gray-400);">Sin maestro</span>' ?>
            </td>
            <td class="col-students" style="text-align: center;"><span class="badge badge-success"
                style="font-weight: 700;"><?= $g['total_alumnos'] ?> alumnos</span></td>
            <td style="text-align: center; white-space: nowrap;">
              <button class="btn btn-outline btn-sm" onclick="editGroup(<?= htmlspecialchars(json_encode($g)) ?>)"
                style="padding: 2px 8px; font-size: 12px; height: 26px; margin-right: 4px;">Editar</button>
              <button class="btn btn-primary btn-sm" onclick="openPromoteModal(<?= htmlspecialchars(json_encode($g)) ?>)"
                style="padding: 2px 8px; font-size: 12px; height: 26px;">Promover</button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- New/Edit Group Modal -->
<div class="modal-overlay" id="modal-group">
  <div class="modal">
    <div class="modal-handle"></div>
    <h2 class="modal-title">Nuevo Grupo</h2>

    <form id="form-group" onsubmit="saveGroup(event)">
      <input type="hidden" id="group-id" name="id_grupo" value="0">

      <!-- Indicador de Ciclo (Lectura) -->
      <div class="form-group" style="margin-bottom: var(--space-4);">
        <label class="form-label">Ciclo de Destino</label>
        <div id="group-ciclo-display"
          style="background: var(--gray-100); padding: var(--space-2) var(--space-3); border-radius: 8px; font-weight: 700; color: var(--primary-600); display: inline-block;">
          Ciclo <?= htmlspecialchars($cicloActivo) ?>
        </div>
        <p style="font-size: 11px; color: var(--gray-500); margin-top: 4px;">El grupo se creará en el ciclo seleccionado
          actualmente.</p>
      </div>

      <div class="form-group">
        <label class="form-label" for="group-carrera">Carrera</label>
        <input type="text" class="form-control" id="group-carrera" name="carrera"
          placeholder="Ej. Ingeniería en Energías Renovables" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="group-siglas">Siglas</label>
          <input type="text" class="form-control" id="group-siglas" name="siglas" placeholder="Ej. IER" maxlength="20"
            required>
        </div>
        <div class="form-group">
          <label class="form-label" for="group-cuatrimestre">Cuatrimestre</label>
          <input type="number" class="form-control" id="group-cuatrimestre" name="cuatrimestre" min="1" max="10"
            placeholder="1-10" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="group-grupo">Grupo</label>
          <input type="text" class="form-control" id="group-grupo" name="grupo" placeholder="A" maxlength="10" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="group-anio">Año</label>
          <input type="number" class="form-control" id="group-anio" name="anio" value="<?= date('Y') ?>" min="2020"
            max="2030" required>
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

      <div class="form-group">
        <label class="form-label" for="group-materia">Materia Cursada</label>
        <select class="form-control form-select" id="group-materia" name="id_materia" required>
          <option value="" disabled selected>Selecciona una materia...</option>
          <?php foreach ($materias as $m): ?>
            <option value="<?= $m['id_materia'] ?>"><?= htmlspecialchars($m['nombre'] . ' (' . $m['siglas'] . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="group-docente">Maestro Asignado</label>
        <select class="form-control form-select" id="group-docente" name="id_docente" required>
          <option value="" disabled selected>Selecciona un maestro...</option>
          <?php foreach ($docentes as $d): ?>
            <option value="<?= $d['id_docente'] ?>"><?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido_pat']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;"
          onclick="closeModal('modal-group')">Cancelar</button>
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
      Esto creará un nuevo grupo en el ciclo objetivo e inscribirá automáticamente a todos los alumnos actuales de este grupo.
    </p>

    <form id="form-promote" onsubmit="promoteGroup(event)">
      <input type="hidden" id="promote-id-grupo" name="id_grupo_old">
      <input type="hidden" id="promote-carrera" name="carrera">
      <input type="hidden" id="promote-siglas" name="siglas">
      <input type="hidden" id="promote-grupo" name="grupo">

      <div class="form-group" style="margin-bottom: var(--space-4);">
        <label class="form-label">Grupo de Origen</label>
        <div id="promote-origen-display"
          style="background: var(--gray-100); padding: var(--space-2) var(--space-3); border-radius: 8px; font-weight: 700; color: var(--gray-700); display: inline-block;">
          -
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="promote-cuatrimestre">Nuevo Cuatrimestre</label>
          <input type="number" class="form-control" id="promote-cuatrimestre" name="cuatrimestre" 
                 min="1" max="15" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="promote-anio">Nuevo Año</label>
          <input type="number" class="form-control" id="promote-anio" name="anio" 
                 value="<?= date('Y') ?>" min="2020" max="2030" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="promote-periodo">Nuevo Periodo</label>
        <select class="form-control form-select" id="promote-periodo" name="periodo" onchange="validatePromoteCycle()" required>
          <option value="ene-abr">Enero - Abril</option>
          <option value="may-ago">Mayo - Agosto</option>
          <option value="sep-dic">Septiembre - Diciembre</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="promote-docente">Maestro Asignado (Ciclo Nuevo)</label>
        <select class="form-control form-select" id="promote-docente" name="id_docente_new" required>
          <option value="" disabled selected>Selecciona un maestro...</option>
          <?php foreach ($docentes as $d): ?>
            <option value="<?= $d['id_docente'] ?>"><?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido_pat']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Live Cycle Validation Alert Area -->
      <div id="promote-validation-alert" style="margin-top: var(--space-4); display: none;"></div>

      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
        <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('modal-promote')">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;" id="btn-save-promote">Promover</button>
      </div>
    </form>
  </div>
</div>

<script>
  function setActiveCicloLocal(ciclo) {
    const formData = new FormData();
    formData.append('ciclo', ciclo);
    fetch('index.php?action=set_active_ciclo', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          showToast(data.message, 'error');
        }
      });
  }

  function openGroupModal() {
    const currentCiclo = '<?= $cicloActivo ?>';
    if (!currentCiclo) {
      showToast('Por favor selecciona o crea un ciclo primero.', 'error');
      return;
    }

    document.getElementById('form-group').reset();
    document.getElementById('group-id').value = '0';
    document.getElementById('modal-group').querySelector('.modal-title').textContent = 'Nuevo Grupo';

    // Parse cycle code (e.g. 26C2 -> 2026, may-ago)
    const yearPart = '20' + currentCiclo.substring(0, 2);
    const periodPart = currentCiclo.substring(2);
    const periodMap = { 'C1': 'ene-abr', 'C2': 'may-ago', 'C3': 'sep-dic' };

    document.getElementById('group-anio').value = yearPart;
    document.getElementById('group-periodo').value = periodMap[periodPart] || 'ene-abr';

    // Enforce match with active cycle
    document.getElementById('group-anio').readOnly = true;
    document.getElementById('group-periodo').style.pointerEvents = 'none';
    document.getElementById('group-periodo').style.background = 'var(--gray-50)';

    document.getElementById('group-ciclo-display').textContent = 'Ciclo ' + currentCiclo;
    document.getElementById('group-docente').value = "";
    if (document.getElementById('group-materia')) {
      document.getElementById('group-materia').value = "";
    }

    document.getElementById('modal-group').classList.add('active');
  }

  function editGroup(g) {
    document.getElementById('form-group').reset();
    document.getElementById('group-id').value = g.id_grupo;
    document.getElementById('modal-group').querySelector('.modal-title').textContent = 'Editar Grupo';

    document.getElementById('group-carrera').value = g.carrera;
    document.getElementById('group-siglas').value = g.siglas;
    document.getElementById('group-cuatrimestre').value = g.cuatrimestre;
    document.getElementById('group-grupo').value = g.grupo;
    document.getElementById('group-anio').value = g.anio;
    document.getElementById('group-periodo').value = g.periodo;

    if (document.getElementById('group-docente')) {
      document.getElementById('group-docente').value = g.id_docente || "";
    }
    if (document.getElementById('group-materia')) {
      document.getElementById('group-materia').value = g.id_materia || "";
    }

    // Allow editing cycle info in edit mode
    document.getElementById('group-anio').readOnly = false;
    document.getElementById('group-periodo').style.pointerEvents = 'auto';
    document.getElementById('group-periodo').style.background = '#ffffff';

    document.getElementById('group-ciclo-display').textContent = 'Ciclo ' + g.ciclo;

    document.getElementById('modal-group').classList.add('active');
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

  const ciclosExistentes = <?= json_encode($ciclosDisponibles) ?>;

  function validatePromoteCycle() {
    const anio = document.getElementById('promote-anio').value;
    const periodo = document.getElementById('promote-periodo').value;
    if (!anio || !periodo) return;

    let periodNum = 1;
    if (periodo === 'may-ago') periodNum = 2;
    else if (periodo === 'sep-dic') periodNum = 3;

    const targetCiclo = anio.toString().substring(2) + 'C' + periodNum;
    const alertDiv = document.getElementById('promote-validation-alert');

    if (!ciclosExistentes.includes(targetCiclo)) {
      alertDiv.style.display = 'block';
      alertDiv.innerHTML = `
        <div style="background: #fffbeb; border: 1.5px solid #fef3c7; border-left: 4px solid #f59e0b; padding: var(--space-3); border-radius: 8px; font-size: 13px; color: #b45309; font-weight: 500; text-align: left; line-height: 1.4;">
          ⚠️ El ciclo <strong>${targetCiclo}</strong> no está registrado en el sistema. Al promover, se creará este nuevo ciclo automáticamente.
        </div>
      `;
    } else {
      alertDiv.style.display = 'none';
    }
  }

  document.getElementById('promote-anio').addEventListener('input', validatePromoteCycle);

  function openPromoteModal(g) {
    document.getElementById('form-promote').reset();
    document.getElementById('promote-id-grupo').value = g.id_grupo;
    document.getElementById('promote-carrera').value = g.carrera;
    document.getElementById('promote-siglas').value = g.siglas;
    document.getElementById('promote-grupo').value = g.grupo;
    document.getElementById('promote-cuatrimestre').value = parseInt(g.cuatrimestre, 10) + 1;
    document.getElementById('promote-origen-display').textContent = g.siglas + g.cuatrimestre + g.grupo + ' (Ciclo ' + g.ciclo + ')';
    
    document.getElementById('promote-docente').value = g.id_docente || "";

    const currentCiclo = g.ciclo;
    if (currentCiclo && currentCiclo.length >= 4) {
      let yearPart = parseInt('20' + currentCiclo.substring(0, 2), 10);
      let periodNum = parseInt(currentCiclo.substring(3), 10);
      
      let nextPeriodNum = periodNum + 1;
      let nextYear = yearPart;
      if (nextPeriodNum > 3) {
        nextPeriodNum = 1;
        nextYear += 1;
      }
      
      const periodMap = { 1: 'ene-abr', 2: 'may-ago', 3: 'sep-dic' };
      document.getElementById('promote-anio').value = nextYear;
      document.getElementById('promote-periodo').value = periodMap[nextPeriodNum] || 'ene-abr';
    }

    validatePromoteCycle();
    document.getElementById('modal-promote').classList.add('active');
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