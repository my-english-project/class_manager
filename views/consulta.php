<?php
/**
 * Class Manager — Student Consultation Portal (CU-05)
 * 
 * Read-only view for students to check grades/attendance.
 */

$searchMatricula = $_GET['matricula'] ?? '';
if (empty($searchMatricula) && isset($_SESSION['alumno'])) {
    $searchMatricula = $_SESSION['alumno']['matricula'];
}

$alumnoData = null;

if (!empty($searchMatricula)) {
    require_once BASE_PATH . '/controllers/ConsultaController.php';
    $controller = new ConsultaController();
    $_POST['matricula'] = $searchMatricula;
    $result = $controller->search();
    if ($result['success'] ?? false) {
        $alumnoData = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Class Manager — Portal de Consulta para Alumnos de la UTS">
  <title>Class Manager — Consulta de Calificaciones | UTS</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="icon" type="image/png" href="assets/img/logo-uts-2024.png">
</head>
<body>
  <header class="app-header">
    <img src="assets/img/logo-uts-2024.png" alt="UTS Logo" class="header-logo">
    <div class="header-info">
      <div class="header-title">Class Manager</div>
    </div>
    
    <?php if (isset($_SESSION['usuario'])): ?>
      <?php $displayAvatar = strtoupper(substr($_SESSION['alumno']['nombre'] ?? 'U', 0, 1)); ?>
      <div class="header-profile" style="margin-left: auto;">
        <button class="profile-btn" onclick="document.getElementById('profile-modal').classList.add('active')" style="background: none; border: none; cursor: pointer;">
          <div class="avatar" style="width:36px; height:36px; border-radius:50%; background:var(--uts-green); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
             <?= $displayAvatar ?>
          </div>
        </button>
      </div>
    <?php endif; ?>

    <a href="<?= isset($_SESSION['usuario']) ? 'index.php?action=logout' : 'index.php?page=login' ?>" class="btn btn-outline btn-sm" style="color:white; border-color:rgba(255,255,255,0.3); margin-left:<?= isset($_SESSION['usuario']) ? 'var(--space-3)' : 'auto' ?>; padding: var(--space-2);" title="<?= isset($_SESSION['usuario']) ? 'Cerrar Sesión' : 'Inicio' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
  </header>

  <?php if (isset($_SESSION['usuario'])): ?>
  <div class="modal-overlay" id="profile-modal">
    <div class="modal" style="max-width: 400px; text-align: center;">
      <div class="modal-handle"></div>
      <div class="avatar" style="width:64px; height:64px; border-radius:50%; background:var(--uts-green); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:24px; margin: 0 auto var(--space-3);">
        <?= $displayAvatar ?>
      </div>
      <h3 style="margin-bottom: var(--space-1);"><?= htmlspecialchars($_SESSION['alumno']['nombre'] . ' ' . $_SESSION['alumno']['apellido_pat']) ?></h3>
      <p style="color: var(--gray-500); font-size: var(--text-sm); margin-bottom: var(--space-5);">
        Usuario: <?= htmlspecialchars($_SESSION['usuario']['username'] ?? '') ?>
      </p>

      <form id="form-update-password" onsubmit="updatePassword(event)">
        <div class="form-group" style="text-align: left;">
          <label class="form-label" for="new_password">Nueva Contraseña</label>
          <input type="password" class="form-control" id="new_password" name="new_password" required minlength="4">
        </div>
        <div style="display: flex; gap: var(--space-3); margin-top: var(--space-4);">
          <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('profile-modal')">Cerrar</button>
          <button type="submit" class="btn btn-primary" style="flex:1;" id="btn-update-pwd">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
  <script>
    function updatePassword(e) {
      e.preventDefault();
      const btn = document.getElementById('btn-update-pwd');
      btn.disabled = true;
      btn.innerText = 'Actualizando...';
      
      const fd = new FormData(e.target);
      fetch('index.php?action=update_password', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
           btn.disabled = false;
           btn.innerText = 'Actualizar';
           if(data.success) {
             alert(data.message);
             closeModal('profile-modal');
             e.target.reset();
           } else {
             alert(data.message);
           }
        })
        .catch(() => {
           btn.disabled = false;
           btn.innerText = 'Actualizar';
           alert('Error de red al actualizar');
        });
    }
    function closeModal(id) {
      document.getElementById(id).classList.remove('active');
    }
  </script>
  <?php endif; ?>

  <main class="app-main" style="padding-bottom: var(--space-8);">
    <div class="consulta-wrapper">

      <?php if (!$alumnoData): ?>
      <!-- Search form -->
      <div class="card" style="margin-top: var(--space-6);">
        <div class="consulta-profile">
          <div class="consulta-avatar" style="background: linear-gradient(135deg, var(--gray-300), var(--gray-400));">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
          <h1 class="consulta-name" style="margin-bottom: var(--space-2);">Consulta tus Calificaciones</h1>
          <p class="consulta-mat">Ingresa tu matrícula para consultar tu información académica</p>
        </div>

        <form action="index.php" method="GET" style="max-width: 360px; margin: 0 auto;">
          <input type="hidden" name="page" value="consulta">
          <div class="form-group">
            <input type="text" class="form-control" name="matricula" 
                   placeholder="Tu matrícula (ej. 612310503)" 
                   value="<?= htmlspecialchars($searchMatricula) ?>" required
                   style="text-align:center; font-size: var(--text-lg);">
          </div>
          <button type="submit" class="btn btn-primary btn-block">Consultar</button>
        </form>

        <?php if (!empty($searchMatricula) && !$alumnoData): ?>
        <div style="text-align:center; margin-top: var(--space-5); color: var(--color-danger); font-weight:600;">
          Matrícula no encontrada. Verifica tu número e intenta de nuevo.
        </div>
        <?php endif; ?>
      </div>

      <?php else: ?>
      <!-- Student profile + grades -->
      <div class="card" style="margin-top: var(--space-6);">
        <div class="consulta-profile">
          <div class="consulta-avatar">
            <?= htmlspecialchars($alumnoData['alumno']['iniciales']) ?>
          </div>
          <div class="consulta-name"><?= htmlspecialchars($alumnoData['alumno']['nombre_completo']) ?></div>
          <div class="consulta-mat">Matrícula: <?= htmlspecialchars($alumnoData['alumno']['matricula']) ?></div>
        </div>
      </div>

      <?php if (count($alumnoData['grupos']) === 0): ?>
      <div class="card mt-4">
        <div class="empty-state">
          <h3>Sin grupos activos</h3>
          <p>No se encontraron grupos activos para esta matrícula.</p>
        </div>
      </div>
      <?php endif; ?>

      <?php foreach ($alumnoData['grupos'] as $gd): ?>
      <div class="card mt-4">
        <div class="card-header">
          <h2 class="card-title">
            <?= htmlspecialchars($gd['grupo']['siglas'] . $gd['grupo']['cuatrimestre'] . $gd['grupo']['grupo']) ?>
            — <?= htmlspecialchars($gd['grupo']['carrera']) ?>
          </h2>
          <span style="font-size: var(--text-xs); color: var(--text-secondary);">
            <?= htmlspecialchars($gd['grupo']['ciclo']) ?>
          </span>
        </div>

        <p class="text-sm mb-4" style="color: var(--text-secondary);">
          Docente: <?= htmlspecialchars($gd['grupo']['docente_apellido'] . ' ' . $gd['grupo']['docente_nombre']) ?>
        </p>

        <!-- Grade cards -->
        <h3 style="font-size: var(--text-sm); font-weight:600; margin-bottom: var(--space-3); color: var(--gray-600);">Examen Escrito</h3>
        <div class="parcial-summary mb-4">
          <?php for ($p = 1; $p <= 3; $p++): ?>
          <div class="parcial-block">
            <div class="parcial-block-label">Parcial <?= $p ?></div>
            <div class="parcial-block-value <?= isset($gd['we'][$p]) && $gd['we'][$p] < 7 ? 'fail' : '' ?>">
              <?= isset($gd['we'][$p]) ? number_format((float)$gd['we'][$p], 2) : '—' ?>
            </div>
          </div>
          <?php endfor; ?>
        </div>

        <h3 style="font-size: var(--text-sm); font-weight:600; margin-bottom: var(--space-3); color: var(--gray-600);">Examen Oral</h3>
        <div class="parcial-summary mb-4">
          <?php for ($p = 1; $p <= 3; $p++): ?>
          <div class="parcial-block">
            <div class="parcial-block-label">Parcial <?= $p ?></div>
            <div class="parcial-block-value <?= isset($gd['oe'][$p]) && $gd['oe'][$p] < 7 ? 'fail' : '' ?>">
              <?= isset($gd['oe'][$p]) ? number_format((float)$gd['oe'][$p], 2) : '—' ?>
            </div>
          </div>
          <?php endfor; ?>
        </div>

        <!-- Attendance summary -->
        <?php if (count($gd['attendance']) > 0):
          $attCounts = ['asistencia' => 0, 'retardo' => 0, 'falta' => 0, 'justificado' => 0];
          foreach ($gd['attendance'] as $att) {
            $attCounts[$att['estado']]++;
          }
          $totalAtt = array_sum($attCounts);
        ?>
        <h3 style="font-size: var(--text-sm); font-weight:600; margin-bottom: var(--space-3); color: var(--gray-600);">Asistencia</h3>
        <div class="attendance-legend" style="justify-content:space-around;">
          <div class="legend-item">
            <div class="legend-dot legend-dot--present"></div>
            <span><?= $attCounts['asistencia'] ?></span>
          </div>
          <div class="legend-item">
            <div class="legend-dot legend-dot--late"></div>
            <span><?= $attCounts['retardo'] ?></span>
          </div>
          <div class="legend-item">
            <div class="legend-dot legend-dot--absent"></div>
            <span><?= $attCounts['falta'] ?></span>
          </div>
          <div class="legend-item">
            <div class="legend-dot legend-dot--justified"></div>
            <span><?= $attCounts['justificado'] ?></span>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>

      <div style="text-align:center; margin-top: var(--space-6);">
        <a href="index.php?page=consulta" class="btn btn-outline">Consultar otra matrícula</a>
      </div>
      <?php endif; ?>

    </div>
  </main>

  <script src="assets/js/app.js"></script>
</body>
</html>
