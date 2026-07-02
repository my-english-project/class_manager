<?php
/**
 * ClassHub — Alumnos View
 * 
 * Student management supporting both Teacher and Admin roles.
 */

$db = Database::getConnection();
$rol = $_SESSION['usuario']['rol'] ?? 'docente';

if ($rol === 'admin'):
  // ==========================================
  // ADMIN STUDENT MANAGEMENT VIEW
  // ==========================================
  $cicloActivo = $_SESSION['ciclo_activo'] ?? '';

  // Fetch all available cycles
  $stmtCiclos = $db->query("SELECT codigo FROM ciclo ORDER BY codigo DESC");
  $ciclosDisponibles = $stmtCiclos->fetchAll(PDO::FETCH_COLUMN);

  if (empty($cicloActivo) && !empty($ciclosDisponibles)) {
    $cicloActivo = $ciclosDisponibles[0];
    $_SESSION['ciclo_activo'] = $cicloActivo;
  }

  // Fetch groups of this cycle
  $stmtGroups = $db->prepare("
        SELECT id_grupo, siglas, cuatrimestre, grupo, carrera 
        FROM grupo 
        WHERE ciclo = :ciclo AND activo = 1 
        ORDER BY siglas, cuatrimestre, grupo
    ");
  $stmtGroups->execute([':ciclo' => $cicloActivo]);
  $groupsOfCycle = $stmtGroups->fetchAll();

  // Filters
  $selectedGroupId = $_GET['filter_grupo'] ?? 'all';
  $searchQuery = trim($_GET['search'] ?? '');

  // Build Student Query
  $sql = "
        SELECT DISTINCT a.id_alumno, a.matricula, a.nombre, a.apellido_pat, a.apellido_mat,
               CONCAT(a.apellido_pat, ' ', COALESCE(a.apellido_mat, ''), ' ', a.nombre) as nombre_completo,
               g.id_grupo, g.siglas, g.cuatrimestre, g.grupo, g.carrera
        FROM alumno a
        INNER JOIN grupo_alumno ga ON a.id_alumno = ga.id_alumno
        INNER JOIN grupo g ON ga.id_grupo = g.id_grupo
        WHERE g.activo = 1 AND g.ciclo = :ciclo
    ";

  $params = [':ciclo' => $cicloActivo];

  if ($selectedGroupId !== 'all') {
    $sql .= " AND g.id_grupo = :gid";
    $params[':gid'] = (int) $selectedGroupId;
  }

  if (!empty($searchQuery)) {
    $sql .= " AND (a.matricula LIKE :q1 OR a.nombre LIKE :q2 OR a.apellido_pat LIKE :q3 OR a.apellido_mat LIKE :q4)";
    $likeVal = "%$searchQuery%";
    $params[':q1'] = $likeVal;
    $params[':q2'] = $likeVal;
    $params[':q3'] = $likeVal;
    $params[':q4'] = $likeVal;
  }

  $sql .= " ORDER BY g.siglas, g.cuatrimestre, g.grupo, a.apellido_pat, a.apellido_mat, a.nombre";

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $allFilteredStudents = $stmt->fetchAll();

  // Pagination
  $totalCount = count($allFilteredStudents);
  $limit = 10;
  $totalPages = max(1, ceil($totalCount / $limit));
  $pageCurrent = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
  if ($pageCurrent > $totalPages)
    $pageCurrent = $totalPages;
  $offset = ($pageCurrent - 1) * $limit;

  $studentsPaginated = array_slice($allFilteredStudents, $offset, $limit);
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
      Gestión General de Alumnos
    </h1>
    <p class="page-description">Consulta, inscribe y administra los alumnos registrados en todos los grupos del ciclo</p>
  </div>

  <!-- Toolbar Filters -->
  <div class="toolbar"
    style="display: flex; flex-direction: column; gap: var(--space-4); margin-bottom: var(--space-4); background: var(--bg-surface); padding: var(--space-4); border-radius: 12px; border: 1px solid var(--gray-200);">

    <div style="display: flex; flex-wrap: wrap; gap: var(--space-4); align-items: center; width: 100%;">
      <!-- 1. Ciclo Select -->
      <div style="display: flex; gap: var(--space-2); align-items: center; min-width: 220px;">
        <span
          style="font-size: var(--text-xs); font-weight: 700; color: var(--gray-500); text-transform: uppercase;">Ciclo:</span>
        <select class="form-control form-select" onchange="setCycleAdmin(this.value)"
          style="border-radius: 20px; font-weight: 600; height: 36px; padding: 0 var(--space-4);">
          <?php foreach ($ciclosDisponibles as $c): ?>
            <option value="<?= $c ?>" <?= $cicloActivo === $c ? 'selected' : '' ?>>Ciclo <?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 2. Grupo Select -->
      <div style="display: flex; gap: var(--space-2); align-items: center; min-width: 280px; flex: 1;">
        <span
          style="font-size: var(--text-xs); font-weight: 700; color: var(--gray-500); text-transform: uppercase;">Grupo:</span>
        <select class="form-control form-select" onchange="setGroupAdmin(this.value)"
          style="border-radius: 20px; font-weight: 600; height: 36px; padding: 0 var(--space-4);">
          <option value="all" <?= $selectedGroupId === 'all' ? 'selected' : '' ?>>Todos los alumnos del ciclo</option>
          <?php foreach ($groupsOfCycle as $g): ?>
            <option value="<?= $g['id_grupo'] ?>" <?= (string) $selectedGroupId === (string) $g['id_grupo'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['siglas'] . $g['cuatrimestre'] . $g['grupo'] . ' — ' . $g['carrera']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 3. New Student Button -->
      <button class="btn btn-primary" onclick="openStudentModalAdmin()" style="margin-left: auto; height: 36px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
          <line x1="12" y1="5" x2="12" y2="19" />
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Nuevo Alumno
      </button>
    </div>

    <div
      style="display: flex; gap: var(--space-3); width: 100%; border-top: 1px solid var(--gray-100); padding-top: var(--space-3); align-items: center;">
      <!-- 4. Search Input -->
      <div style="position: relative; flex: 1;">
        <input type="text" id="search-input" class="form-control" placeholder="Buscar alumnos por matrícula o nombre..."
          value="<?= htmlspecialchars($searchQuery) ?>"
          style="height: 38px; padding-left: var(--space-10); border-radius: 20px;" onkeypress="checkSearch(event)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          style="position: absolute; left: var(--space-4); top: 11px; width: 16px; height: 16px; color: var(--gray-400);">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
      </div>
      <button class="btn btn-outline" onclick="applyFilters()"
        style="height: 38px; border-radius: 20px; padding: 0 var(--space-6);">Buscar</button>
      <?php if (!empty($searchQuery) || $selectedGroupId !== 'all'): ?>
        <button class="btn btn-outline" onclick="clearFilters()"
          style="height: 38px; border-radius: 20px; color: var(--gray-500);">Limpiar</button>
      <?php endif; ?>
    </div>

  </div>

  <!-- Data Table -->
  <div class="table-wrapper">
    <table class="data-table" id="table-alumnos-admin">
      <thead>
        <tr>
          <th class="col-matricula" width="140">Matrícula</th>
          <th class="col-name">Nombre Completo</th>
          <th class="col-career">Grupo Asignado</th>
          <th width="120" style="text-align: center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($studentsPaginated) === 0): ?>
          <tr>
            <td colspan="4" style="text-align: center; color: var(--gray-400); padding: var(--space-8); font-weight: 600;">
              No se encontraron alumnos para los criterios seleccionados.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($studentsPaginated as $alumno): ?>
            <tr id="row-alumno-<?= $alumno['id_alumno'] ?>">
              <td class="col-matricula" style="font-weight: 700; color: var(--primary-800);">
                <?= htmlspecialchars($alumno['matricula']) ?></td>
              <td class="col-name" style="font-weight: 600;"><?= htmlspecialchars($alumno['nombre_completo']) ?></td>
              <td class="col-career">
                <span class="badge badge-outline"
                  style="font-weight: 700; color: var(--uts-green); border-color: var(--uts-green);">
                  <?= htmlspecialchars($alumno['siglas'] . $alumno['cuatrimestre'] . $alumno['grupo']) ?>
                </span>
                <span
                  style="font-size: var(--text-xs); color: var(--gray-500); margin-left: var(--space-2);"><?= htmlspecialchars($alumno['carrera']) ?></span>
              </td>
              <td style="text-align: center;">
                <div style="display:inline-flex; gap: var(--space-2);">
                  <button class="btn btn-outline btn-sm"
                    onclick="editStudentAdmin(<?= htmlspecialchars(json_encode($alumno)) ?>)" title="Editar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                  </button>
                  <button class="btn btn-outline btn-sm"
                    onclick="deleteStudentAdmin(<?= $alumno['id_alumno'] ?>, <?= $alumno['id_grupo'] ?>, '<?= htmlspecialchars($alumno['nombre_completo']) ?>')"
                    title="Eliminar del Grupo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                      <polyline points="3 6 5 6 21 6" />
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination Controls -->
  <?php if ($totalPages > 1): ?>
    <div class="pagination"
      style="display: flex; justify-content: center; align-items: center; gap: var(--space-2); margin-top: var(--space-6);">
      <button class="btn btn-outline btn-sm" <?= $pageCurrent === 1 ? 'disabled' : '' ?>
        onclick="changePage(<?= $pageCurrent - 1 ?>)">« Anterior</button>

      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <button class="btn <?= $p === $pageCurrent ? 'btn-primary' : 'btn-outline' ?> btn-sm"
          style="min-width: 32px; height: 32px;" onclick="changePage(<?= $p ?>)"><?= $p ?></button>
      <?php endfor; ?>

      <button class="btn btn-outline btn-sm" <?= $pageCurrent === $totalPages ? 'disabled' : '' ?>
        onclick="changePage(<?= $pageCurrent + 1 ?>)">Siguiente »</button>
    </div>
  <?php endif; ?>

  <!-- Admin Add/Edit Student Modal -->
  <div class="modal-overlay" id="modal-student-admin">
    <div class="modal">
      <div class="modal-handle"></div>
      <h2 class="modal-title">Nuevo Alumno</h2>

      <form id="form-student-admin" onsubmit="saveStudentAdmin(event)">
        <input type="hidden" id="student-id-admin" name="id_alumno" value="0">

        <div class="form-group" id="group-select-container">
          <label class="form-label" for="student-group-select">Grupo de Destino</label>
          <select class="form-control form-select" id="student-group-select" name="id_grupo" required>
            <option value="" disabled selected>Selecciona un grupo del ciclo...</option>
            <?php foreach ($groupsOfCycle as $g): ?>
              <option value="<?= $g['id_grupo'] ?>">
                <?= htmlspecialchars($g['siglas'] . $g['cuatrimestre'] . $g['grupo'] . ' — ' . $g['carrera']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p style="font-size: 11px; color: var(--gray-500); margin-top: 4px;">El alumno se inscribirá automáticamente en
            este grupo.</p>
        </div>

        <div class="form-group">
          <label class="form-label" for="student-matricula-admin">Matrícula</label>
          <input type="text" class="form-control" id="student-matricula-admin" name="matricula"
            placeholder="Ej. 612310503" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="student-apellido-pat-admin">Apellido Paterno</label>
          <input type="text" class="form-control" id="student-apellido-pat-admin" name="apellido_pat" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="student-apellido-mat-admin">Apellido Materno</label>
          <input type="text" class="form-control" id="student-apellido-mat-admin" name="apellido_mat">
        </div>

        <div class="form-group">
          <label class="form-label" for="student-nombre-admin">Nombre(s)</label>
          <input type="text" class="form-control" id="student-nombre-admin" name="nombre" required>
        </div>

        <!-- Credential management section -->
        <div id="reset-password-container"
          style="display: none; margin-top: var(--space-5); padding: var(--space-4); background: var(--gray-50); border-radius: var(--radius-md); border: 1px dashed var(--gray-200);">
          <span
            style="font-size: var(--text-xs); font-weight: 700; color: var(--gray-500); text-transform: uppercase; display: block; margin-bottom: var(--space-2);">Seguridad</span>
          <button type="button" class="btn btn-warning btn-block" onclick="resetPasswordAdmin()"
            style="justify-content: center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Restablecer la contraseña del alumno
          </button>
        </div>

        <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
          <button type="button" class="btn btn-outline" style="flex:1;"
            onclick="closeModal('modal-student-admin')">Cancelar</button>
          <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function setCycleAdmin(ciclo) {
      const formData = new FormData();
      formData.append('ciclo', ciclo);
      fetch('index.php?action=set_active_ciclo', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            // Clear filtered group when cycle changes to avoid invalid cross-cycle filters
            const url = new URL(window.location.href);
            url.searchParams.set('page', 'alumnos');
            url.searchParams.delete('filter_grupo');
            url.searchParams.delete('p');
            window.location.href = url.toString();
          } else {
            showToast(data.message, 'error');
          }
        });
    }

    function setGroupAdmin(groupId) {
      const url = new URL(window.location.href);
      url.searchParams.set('filter_grupo', groupId);
      url.searchParams.delete('p');
      window.location.href = url.toString();
    }

    function checkSearch(e) {
      if (e.key === 'Enter') {
        applyFilters();
      }
    }

    function applyFilters() {
      const query = document.getElementById('search-input').value.trim();
      const url = new URL(window.location.href);
      if (query) {
        url.searchParams.set('search', query);
      } else {
        url.searchParams.delete('search');
      }
      url.searchParams.delete('p');
      window.location.href = url.toString();
    }

    function clearFilters() {
      const url = new URL(window.location.href);
      url.searchParams.delete('search');
      url.searchParams.set('filter_grupo', 'all');
      url.searchParams.delete('p');
      window.location.href = url.toString();
    }

    function changePage(p) {
      const url = new URL(window.location.href);
      url.searchParams.set('p', p);
      window.location.href = url.toString();
    }

    function openStudentModalAdmin() {
      document.getElementById('form-student-admin').reset();
      document.getElementById('student-id-admin').value = '0';
      document.getElementById('group-select-container').style.display = 'block';
      document.getElementById('student-group-select').disabled = false;
      document.getElementById('reset-password-container').style.display = 'none';
      document.getElementById('modal-student-admin').querySelector('.modal-title').textContent = 'Nuevo Alumno';
      document.getElementById('modal-student-admin').classList.add('active');
    }

    function editStudentAdmin(a) {
      document.getElementById('form-student-admin').reset();
      document.getElementById('student-id-admin').value = a.id_alumno;
      document.getElementById('student-matricula-admin').value = a.matricula;
      document.getElementById('student-apellido-pat-admin').value = a.apellido_pat;
      document.getElementById('student-apellido-mat-admin').value = a.apellido_mat || '';
      document.getElementById('student-nombre-admin').value = a.nombre;

      // Set and disable the group select because the group association is already formed
      document.getElementById('student-group-select').value = a.id_grupo;
      document.getElementById('group-select-container').style.display = 'block';
      document.getElementById('student-group-select').disabled = true; // prevent group modification during general data edit

      document.getElementById('reset-password-container').style.display = 'block';

      document.getElementById('modal-student-admin').querySelector('.modal-title').textContent = 'Editar Alumno';
      document.getElementById('modal-student-admin').classList.add('active');
    }

    function resetPasswordAdmin() {
      const studentId = document.getElementById('student-id-admin').value;
      const nombre = document.getElementById('student-nombre-admin').value;
      const apellidoPat = document.getElementById('student-apellido-pat-admin').value;
      const matricula = document.getElementById('student-matricula-admin').value;
      const nombreCompleto = `${apellidoPat} ${nombre}`;

      if (!confirm(`¿Estás seguro de que deseas restablecer la contraseña de ${nombreCompleto}? La nueva contraseña será su matrícula: ${matricula}`)) {
        return;
      }

      const formData = new FormData();
      formData.append('id_alumno', studentId);

      fetch('index.php?action=reset_alumno_password', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showToast(data.message, 'success');
            closeModal('modal-student-admin');
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(err => {
          showToast('Error de red al intentar restablecer la contraseña.', 'error');
        });
    }

    function saveStudentAdmin(e) {
      e.preventDefault();
      // Enable temporarily to let form serialized capture group ID
      document.getElementById('student-group-select').disabled = false;
      const formData = new FormData(document.getElementById('form-student-admin'));

      fetch('index.php?action=save_alumno', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            closeModal('modal-student-admin');
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 800);
          } else {
            showToast(data.message, 'error');
            // Restore disabled state on error
            if (parseInt(document.getElementById('student-id-admin').value, 10) > 0) {
              document.getElementById('student-group-select').disabled = true;
            }
          }
        });
    }

    function deleteStudentAdmin(idAlumno, idGrupo, nombreCompleto) {
      const modal = document.getElementById('confirm-logout-modal');
      // We can use confirm warning as it's safe or regular alert
      if (!confirm('¿Deseas desvincular a ' + nombreCompleto + ' de este grupo?')) return;

      const formData = new FormData();
      formData.append('id_alumno', idAlumno);
      formData.append('id_grupo', idGrupo);

      fetch('index.php?action=delete_alumno', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('row-alumno-' + idAlumno).remove();
            setTimeout(() => window.location.reload(), 500);
          } else {
            showToast(data.message, 'error');
          }
        });
    }
  </script>

  <?php
else:
  // ==========================================
  // ORIGINAL TEACHER STUDENT LIST
  // ==========================================
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
        <a href="index.php?page=home" class="btn btn-primary">Ir a Dashboard</a>
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
<?php endif; ?>