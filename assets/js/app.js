/**
 * ClassHub — Client-Side Logic
 * 
 * Handles: toast notifications, grade validation, table sorting,
 * exam grade saving, modal management, unsaved changes warning.
 */

/* ── Toast Notifications ──────────────────────────────────── */
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const icons = {
    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="toast-icon"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
    error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="toast-icon"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="toast-icon"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
  };

  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  toast.innerHTML = `${icons[type] || icons.info}<span>${message}</span>`;
  container.appendChild(toast);

  // Auto-dismiss after 4 seconds
  setTimeout(() => {
    toast.classList.add('toast-out');
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

/* ── Modal Management ─────────────────────────────────────── */
function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('active');
  }
}

// Close modal when clicking overlay
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
  }
});

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
  }
});

/* ── Grade Validation ─────────────────────────────────────── */
function validateGrade(input) {
  const value = parseFloat(input.value);

  if (input.value === '') {
    input.classList.remove('fail');
    return;
  }

  // Clamp to valid range (RN-02)
  if (value < 0) input.value = '0.00';
  if (value > 10) input.value = '10.00';

  // Red highlight for failing grades (RN-01)
  if (value < 7) {
    input.classList.add('fail');
  } else {
    input.classList.remove('fail');
  }

  // Mark unsaved changes
  window._hasUnsavedChanges = true;
}

/* ── Save Exam Grades (WE / OE) ──────────────────────────── */
function saveExamGrades(type) {
  const inputs = document.querySelectorAll(`.grade-input[data-parcial]`);
  const grades = [];

  inputs.forEach(input => {
    const alumnoId = input.getAttribute('data-alumno');
    const parcial = input.getAttribute('data-parcial');
    // Only include if data-alumno is present (skip activity inputs)
    if (alumnoId && parcial && !input.hasAttribute('data-actividad')) {
      grades.push({
        id_alumno: alumnoId,
        parcial: parcial,
        calificacion: input.value,
      });
    }
  });

  const formData = new FormData();
  formData.append('type', type);
  grades.forEach((g, i) => {
    formData.append(`grades[${i}][id_alumno]`, g.id_alumno);
    formData.append(`grades[${i}][parcial]`, g.parcial);
    formData.append(`grades[${i}][calificacion]`, g.calificacion);
  });

  fetch('index.php?action=save_grades', {
    method: 'POST',
    body: formData,
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast(data.message, 'success');
        window._hasUnsavedChanges = false;
      } else {
        showToast(data.message, 'error');
      }
    })
    .catch(() => {
      showToast('Error de conexión al guardar', 'error');
    });
}

/* ── Auto-save Single Grade ───────────────────────────────── */
function autoSaveSingleGrade(input, type) {
  const isActivity = input.hasAttribute('data-actividad');
  const formData = new FormData();
  formData.append('type', isActivity ? 'activity' : type);
  
  formData.append('grades[0][id_alumno]', input.getAttribute('data-alumno'));
  formData.append('grades[0][calificacion]', input.value);
  
  if (isActivity) {
    formData.append('grades[0][id_actividad]', input.getAttribute('data-actividad'));
  } else {
    formData.append('grades[0][parcial]', input.getAttribute('data-parcial'));
  }

  fetch('index.php?action=save_grades', {
    method: 'POST',
    body: formData,
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('Guardado automático', 'success');
      window._hasUnsavedChanges = false;
    } else {
      showToast(data.message, 'error');
    }
  })
  .catch(() => {
    showToast('Error al auto-guardar', 'error');
  });
}

/* ── Table Sorting ────────────────────────────────────────── */
function sortTable(tableId, colIndex) {
  const table = document.getElementById(tableId);
  if (!table) return;

  const tbody = table.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr'));
  const th = table.querySelectorAll('thead th')[colIndex];

  // Toggle sort direction
  const ascending = !th.classList.contains('sorted-asc');
  
  // Clear all sort classes
  table.querySelectorAll('thead th').forEach(h => {
    h.classList.remove('sorted', 'sorted-asc', 'sorted-desc');
  });

  th.classList.add('sorted', ascending ? 'sorted-asc' : 'sorted-desc');

  rows.sort((a, b) => {
    const aText = a.cells[colIndex]?.textContent?.trim() || '';
    const bText = b.cells[colIndex]?.textContent?.trim() || '';
    
    const aNum = parseFloat(aText);
    const bNum = parseFloat(bText);

    // Numeric comparison if both are numbers
    if (!isNaN(aNum) && !isNaN(bNum)) {
      return ascending ? aNum - bNum : bNum - aNum;
    }

    // String comparison
    return ascending 
      ? aText.localeCompare(bText, 'es') 
      : bText.localeCompare(aText, 'es');
  });

  rows.forEach(row => tbody.appendChild(row));
}

/* ── Unsaved Changes Warning (RF-40) ─────────────────────── */
window._hasUnsavedChanges = false;

// Track changes on grade inputs
document.addEventListener('input', (e) => {
  if (e.target.classList.contains('grade-input')) {
    window._hasUnsavedChanges = true;
  }
});

window.addEventListener('beforeunload', (e) => {
  if (window._hasUnsavedChanges) {
    e.preventDefault();
    e.returnValue = '¿Estás seguro? Hay cambios sin guardar.';
  }
});

/* ── Logout ───────────────────────────────────────────────── */
function doLogout() {
  const modal = document.getElementById('confirm-logout-modal');
  if (modal) {
    modal.classList.add('active');
  } else {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
      proceedLogout();
    }
  }
}

function proceedLogout() {
  closeModal('confirm-logout-modal');
  fetch('index.php?action=logout', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window._hasUnsavedChanges = false;
        window.location.href = data.redirect || 'index.php?page=login';
      }
    });
}

/* ── Initialize ───────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  // Set today's date for any date inputs without value
  document.querySelectorAll('input[type="date"]:not([value])').forEach(input => {
    if (!input.value) {
      input.value = new Date().toISOString().split('T')[0];
    }
  });
});

/* ── Group & Cycle Switching ──────────────────────────────── */
function setActiveGroup(id) {
  const formData = new FormData();
  formData.append('id_grupo', id);
  fetch('index.php?action=set_active_grupo', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        showToast(data.message, 'error');
      }
    });
}

function setActiveCiclo(ciclo) {
  const formData = new FormData();
  formData.append('ciclo', ciclo);
  fetch('index.php?action=set_active_ciclo', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.location.href = 'index.php?page=home';
      } else {
        showToast(data.message, 'error');
      }
    });
}
