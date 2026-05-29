<?php
/**
 * ClassHub — Layout Footer
 * Bottom navigation for mobile + toast container + JS.
 */
?>
    </main><!-- /.app-main -->
  </div><!-- /.app-wrapper -->

  <!-- Bottom Navigation (Mobile) -->
  <?php if (empty($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'alumno'): ?>
  <nav class="nav-bottom" id="nav-bottom">
    <?php
    $bottomItems = [
        ['slug' => 'home',       'label' => 'HM',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'],
        ['slug' => 'alumnos',    'label' => 'AL',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'],
        ['slug' => 'attendance', 'label' => 'AT',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'],
        ['slug' => 'write_exam', 'label' => 'WE',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>'],
        ['slug' => 'oral_exam',  'label' => 'OE',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>'],
        ['slug' => 'portfolio',  'label' => 'PF',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>'],
        ['slug' => 'homework',   'label' => 'HW',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>'],
        ['slug' => 'exam',       'label' => 'EX',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>'],
        ['slug' => 'sito',       'label' => 'ST',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>'],
    ];
    if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'alumno') {
        $bottomItems = [
            ['slug' => 'home',       'label' => 'HM',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'],
        ];
    }
    foreach ($bottomItems as $item):
    ?>
      <a href="index.php?page=<?= $item['slug'] ?>"
         class="nav-bottom-item <?= ($currentPage ?? '') === $item['slug'] ? 'active' : '' ?>"
         id="bnav-<?= $item['slug'] ?>">
        <?= $item['icon'] ?>
        <span><?= $item['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <?php endif; ?>

  <!-- Toast Container -->
  <div class="toast-container" id="toast-container"></div>

  <!-- Custom Confirm Logout Modal -->
  <div class="modal-overlay" id="confirm-logout-modal">
    <div class="modal" style="max-width: 400px; text-align: center; border-radius: var(--radius-xl);">
      <div class="modal-handle"></div>
      <div style="width: 56px; height: 56px; border-radius: 50%; background: #fffbeb; border: 2px solid #fef3c7; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-3);">
        <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" width="28" height="28" style="flex-shrink: 0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <h3 style="margin-bottom: var(--space-2); font-weight: 700;">¿Cerrar Sesión?</h3>
      <p style="color: var(--gray-500); font-size: var(--text-sm); margin-bottom: var(--space-5); line-height: 1.5;">
        ¿Estás seguro de que deseas salir del sistema? Tendrás que volver a ingresar tus credenciales para acceder.
      </p>
      <div style="display: flex; gap: var(--space-3); margin-top: var(--space-4);">
        <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('confirm-logout-modal')">Cancelar</button>
        <button type="button" class="btn btn-primary" style="flex:1; background: #d97706; border-color: #d97706;" onclick="proceedLogout()">Cerrar Sesión</button>
      </div>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>
