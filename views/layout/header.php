<?php
/**
 * ClassManager — Layout Header
 * Sticky header with logo + navigation (sidebar for tablet+, bottom for mobile).
 */

$docente = $_SESSION['docente'] ?? null;
$alumno = $_SESSION['alumno'] ?? null;
$usuario = $_SESSION['usuario'] ?? null;
$grupoActivo = $_SESSION['grupo_activo'] ?? null;
$currentPage = $currentPage ?? 'home';

$displayNombre = $docente ? ($docente['nombre'] . ' ' . $docente['apellido_pat']) : ($alumno['nombre'] ?? 'Usuario');
$displayAvatar = strtoupper(substr($docente ? $docente['nombre'] : ($alumno['nombre'] ?? 'U'), 0, 1));

// Navigation items with SVG icons
$navItems = [
    ['slug' => 'home',       'label' => 'Home',      'code' => 'HM', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'],
];

if ($usuario && $usuario['rol'] === 'admin') {
    $navItems[] = ['slug' => 'periodos', 'label' => 'Periodos', 'code' => 'PE', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'];
    $navItems[] = ['slug' => 'grupos_admin', 'label' => 'Grupos', 'code' => 'GR', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-3-3.87"/><path d="M9 21v-2a4 4 0 0 0-3-3.87"/><path d="M2 21v-2a4 4 0 0 1 4-4h5a4 4 0 0 1 4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg>'];
    $navItems[] = ['slug' => 'materias', 'label' => 'Materias', 'code' => 'MT', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>'];
    $navItems[] = ['slug' => 'maestros', 'label' => 'Maestros', 'code' => 'MA', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>'];
    $navItems[] = ['slug' => 'alumnos',    'label' => 'Alumnos',    'code' => 'AL', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'];
}

if ($usuario && $usuario['rol'] === 'docente') {
    $navItems = array_merge($navItems, [
        ['slug' => 'attendance', 'label' => 'Asistencia', 'code' => 'AT', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'],
        ['slug' => 'write_exam', 'label' => 'Ex. Escrito','code' => 'WE', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>'],
        ['slug' => 'oral_exam',  'label' => 'Ex. Oral',   'code' => 'OE', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>'],
        ['slug' => 'portfolio',  'label' => 'Portafolio', 'code' => 'PF', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>'],
        ['slug' => 'homework',   'label' => 'Tareas',     'code' => 'HW', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>'],
        ['slug' => 'exam',       'label' => 'Resumen',    'code' => 'EX', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>'],
        ['slug' => 'sito',       'label' => 'SITO',       'code' => 'ST', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>'],
    ]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Class Manager — Sistema de Administración de Clase de la Universidad Tecnológica de Salamanca">
  <title>Class Manager — <?= ucfirst(str_replace('_', ' ', $currentPage)) ?> | UTS</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="icon" type="image/png" href="assets/img/logo-uts-2024.png">
</head>
<body>
  <!-- Header -->
  <header class="app-header" id="app-header">
    <img src="assets/img/logo-uts-2024.png" alt="UTS Logo" class="header-logo">
    <div class="header-info">
      <div class="header-title">Class Manager</div>
    </div>
    
    <div class="header-group-selector" style="margin-left: auto;">
      <?php if ($usuario && $usuario['rol'] === 'docente'): ?>
        <?php
          $cicloActivo = $_SESSION['ciclo_activo'] ?? '';
          $docenteId = $docente['id_docente'] ?? 0;
          $db = Database::getConnection();
          if ($usuario['rol'] === 'admin') {
              $stmtGruposHeader = $db->prepare("SELECT * FROM grupo WHERE ciclo = :ciclo AND activo = 1 ORDER BY siglas, cuatrimestre, grupo");
              $stmtGruposHeader->execute([':ciclo' => $cicloActivo]);
          } else {
              $stmtGruposHeader = $db->prepare("SELECT * FROM grupo WHERE id_docente = :did AND ciclo = :ciclo AND activo = 1 ORDER BY siglas, cuatrimestre, grupo");
              $stmtGruposHeader->execute([':did' => $docenteId, ':ciclo' => $cicloActivo]);
          }
          $gruposCiclo = $stmtGruposHeader->fetchAll();
        ?>
        <select class="form-control form-select" onchange="setActiveGroup(this.value)" style="border-radius: 20px; padding: var(--space-1) var(--space-6) var(--space-1) var(--space-3); border: 2px solid var(--primary); font-weight: 700; font-size: var(--text-sm); color: #1A202C; background-color: #ffffff; width: auto; max-width: 130px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); height: 32px;">
          <option value="" disabled <?= !$grupoActivo ? 'selected' : '' ?>>Grupo...</option>
          <?php foreach ($gruposCiclo as $g): ?>
            <option value="<?= $g['id_grupo'] ?>" <?= $grupoActivo && $grupoActivo['id_grupo'] == $g['id_grupo'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['siglas'] . $g['cuatrimestre'] . $g['grupo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>

    <!-- Profile Avatar Button -->
    <div class="header-profile" style="margin-left: var(--space-3); display: flex; align-items: center;">
      <button class="profile-btn" onclick="document.getElementById('profile-modal').classList.add('active')" style="background: none; border: none; cursor: pointer;">
        <div class="avatar" style="width:36px; height:36px; border-radius:50%; background:var(--uts-green); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
           <?= $displayAvatar ?>
        </div>
      </button>
      <?php if ($usuario): ?>
        <button onclick="doLogout()" class="btn btn-outline" style="padding: 0; border-radius: 50%; color: white; border-color: rgba(255,255,255,0.3); height: 36px; width: 36px; display: inline-flex; align-items: center; justify-content: center; margin-left: var(--space-2); background: none;" title="Cerrar Sesión">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px; height:18px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      <?php endif; ?>
    </div>
  </header>

  <!-- Profile Modal -->
  <div class="modal-overlay" id="profile-modal">
    <div class="modal" style="max-width: 400px; text-align: center;">
      <div class="modal-handle"></div>
      <div class="avatar" style="width:64px; height:64px; border-radius:50%; background:var(--uts-green); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:24px; margin: 0 auto var(--space-3);">
        <?= $displayAvatar ?>
      </div>
      <h3 style="margin-bottom: var(--space-1);"><?= htmlspecialchars($displayNombre) ?></h3>
      <p style="color: var(--gray-500); font-size: var(--text-sm); margin-bottom: var(--space-5);">
        Usuario: <?= htmlspecialchars($usuario['username'] ?? '') ?>
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
             if (typeof showToast === 'function') {
               showToast(data.message, 'success');
             } else {
               alert(data.message);
             }
             closeModal('profile-modal');
             e.target.reset();
           } else {
             if (typeof showToast === 'function') {
               showToast(data.message, 'error');
             } else {
               alert(data.message);
             }
           }
        })
        .catch(() => {
           btn.disabled = false;
           btn.innerText = 'Actualizar';
           alert('Error de red al actualizar');
        });
    }
  </script>

  <div class="app-wrapper">
    <!-- Sidebar Navigation (Tablet+) -->
    <?php if ($usuario && $usuario['rol'] !== 'alumno'): ?>
    <nav class="nav-sidebar" id="nav-sidebar">
      <div class="nav-sidebar-section">Navegación</div>
      <?php foreach ($navItems as $item): ?>
        <a href="index.php?page=<?= $item['slug'] ?>"
           class="nav-sidebar-item <?= $currentPage === $item['slug'] ? 'active' : '' ?>"
           id="nav-<?= $item['slug'] ?>">
          <?= $item['icon'] ?>
          <span><?= $item['label'] ?></span>
        </a>
      <?php endforeach; ?>

      <?php if ($usuario && $usuario['rol'] !== 'alumno'): ?>
      <div class="nav-sidebar-section" style="margin-top: auto;">Cuenta</div>
      <a href="index.php?page=consulta" class="nav-sidebar-item" id="nav-consulta">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <span>Consulta Alumno</span>
      </a>
      <?php endif; ?>
      <a href="#" class="nav-sidebar-item" id="btn-toggle-sidebar" onclick="toggleSidebar(event)" style="border-top: 1px solid var(--gray-200);">
        <svg id="toggle-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
        <span>Contraer menú</span>
      </a>
    </nav>
    <script>
      function toggleSidebar(e) {
        if (e) e.preventDefault();
        const sidebar = document.getElementById('nav-sidebar');
        if (!sidebar) return;
        const isCollapsed = sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
        updateSidebarIcon(isCollapsed);
      }

      function updateSidebarIcon(isCollapsed) {
        const icon = document.getElementById('toggle-sidebar-icon');
        const text = document.querySelector('#btn-toggle-sidebar span');
        if (isCollapsed) {
          if (icon) icon.innerHTML = '<polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/>';
          if (text) text.style.display = 'none';
        } else {
          if (icon) icon.innerHTML = '<polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/>';
          if (text) text.style.display = 'inline';
        }
      }

      // Restore collapsed state instantly on page load
      (function() {
        const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        if (isCollapsed) {
          const applyCollapse = () => {
            const sidebar = document.getElementById('nav-sidebar');
            if (sidebar) {
              sidebar.classList.add('collapsed');
              updateSidebarIcon(true);
            }
          };
          if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', applyCollapse);
          } else {
            applyCollapse();
          }
        }
      })();
    </script>
    <?php endif; ?>

    <!-- Main Content Area -->
    <main class="app-main" id="app-main">
