<?php
/**
 * ClassHub — Oral Exam Review (OE)
 * 
 * Teacher panel to evaluate oral exams by marking mispronounced words (RF-27).
 */

$student = $student ?? null;
$parcial = $parcial ?? 1;
$assigned_text = $assigned_text ?? null;
$grupoId = $_SESSION['grupo_activo']['id_grupo'] ?? 0;
?>

<style>
  .oral-review-container {
    --bg-oral: var(--bg-body);
    --surface-oral: var(--bg-surface);
    --ink-oral: var(--text-primary);
    --accent-oral: var(--color-primary);
    --accent-light-oral: rgba(58, 155, 92, 0.12);
    --muted-oral: var(--text-secondary);
    --border-oral: var(--gray-200);
    --fs: 22px;

    font-family: var(--font-body);
    background: var(--surface-oral);
    color: var(--ink-oral);
    padding: var(--space-6);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-oral);
    margin-top: var(--space-4);
    box-shadow: var(--shadow-sm);
  }

  .oral-header {
    background: var(--uts-blue);
    color: var(--text-inverse);
    padding: var(--space-4) var(--space-6);
    border-radius: var(--radius-md);
    text-align: center;
    margin-bottom: var(--space-4);
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow-sm);
  }

  .oral-header-text {
    flex: 1;
    text-align: center;
  }

  .oral-header h2 {
    font-family: var(--font-heading);
    color: var(--text-inverse);
    margin: 0;
    font-size: 22px;
    font-weight: 700;
  }

  .oral-header p {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.75);
    margin: 4px 0 0;
  }

  .btn-grade {
    background: var(--accent-oral);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    padding: var(--space-2) var(--space-4);
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: background var(--transition-fast), transform var(--transition-fast);
  }

  .btn-grade:hover {
    background: var(--color-primary-dark);
  }

  .btn-grade:active {
    transform: scale(0.95);
  }

  .review-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: var(--space-6);
    margin-top: var(--space-4);
  }

  @media (max-width: 768px) {
    .review-layout {
      grid-template-columns: 1fr;
    }

    .word-list {
      order: -1;
    }

    .oral-header {
      flex-direction: column;
      gap: var(--space-3);
    }
  }

  .exam-text-card {
    background: var(--gray-50);
    border: 1px solid var(--border-oral);
    border-radius: var(--radius-md);
    padding: var(--space-6);
    min-height: 250px;
  }

  .topic-label {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--muted-oral);
    margin-bottom: 4px;
  }

  .topic-title {
    font-family: var(--font-heading);
    font-size: 20px;
    color: var(--uts-blue);
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-2);
    border-bottom: 1px solid var(--border-oral);
    font-weight: 700;
  }

  .exam-text {
    font-size: var(--fs);
    line-height: 1.75;
    color: var(--text-primary);
    transition: font-size 0.2s ease;
    overflow-wrap: break-word;
    font-family: var(--font-body);
  }

  .word-span {
    cursor: pointer;
    border-radius: var(--radius-sm);
    padding: 2px 4px;
    transition: background var(--transition-fast);
  }

  .word-span:hover {
    background: var(--accent-light-oral);
  }

  .word-list {
    background: var(--surface-oral);
    border: 1px solid var(--border-oral);
    border-radius: var(--radius-md);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    box-shadow: var(--shadow-sm);
  }

  .word-list h3 {
    margin: 0 0 10px 0;
    font-size: 1.1rem;
    color: var(--uts-blue);
    text-align: center;
    font-family: var(--font-heading);
    font-weight: 600;
  }

  .word-item {
    background: var(--gray-50);
    border: 1px solid var(--border-oral);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    font-size: 1rem;
    transition: background var(--transition-fast);
  }

  .word-item:hover {
    background: var(--accent-light-oral);
  }

  .word-item-text {
    flex: 1;
    text-align: left;
    cursor: pointer;
    font-family: var(--font-body);
    font-weight: 500;
  }

  .word-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--accent-oral);
  }

  .controls-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: center;
    width: 100%;
    margin-bottom: var(--space-4);
  }

  .font-controls {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .font-controls span {
    font-size: 0.78rem;
    color: var(--muted-oral);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    font-family: var(--font-heading);
    font-weight: 600;
  }

  .btn-font {
    background: var(--uts-blue);
    color: var(--text-inverse);
    border: none;
    border-radius: var(--radius-sm);
    width: 34px;
    height: 34px;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background var(--transition-fast), transform var(--transition-fast);
    user-select: none;
  }

  .btn-font:hover {
    background: var(--accent-oral);
  }

  .btn-font:active {
    transform: scale(0.92);
  }

  .size-display {
    font-size: 0.85rem;
    color: var(--accent-oral);
    font-weight: 700;
    min-width: 36px;
    text-align: center;
  }

  /* Modal Styles */
  .oral-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity var(--transition-base);
  }

  .oral-modal-overlay.active {
    display: flex;
    opacity: 1;
  }

  .oral-modal-card {
    background: var(--surface-oral);
    border: 1px solid var(--border-oral);
    border-radius: var(--radius-md);
    width: 90%;
    max-width: 400px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
  }

  .oral-modal-header {
    background: var(--uts-blue);
    color: var(--text-inverse);
    padding: var(--space-4);
    font-family: var(--font-heading);
    font-size: 18px;
    text-align: center;
    font-weight: 700;
  }

  .oral-modal-body {
    padding: var(--space-6);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-4);
  }

  .score-circle {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 4px solid var(--accent-oral);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--surface-oral) 0%, var(--gray-50) 100%);
  }

  .score-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--accent-oral);
    font-family: var(--font-heading);
  }

  .score-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted-oral);
    font-weight: 600;
  }

  .stats-container {
    width: 100%;
    background: var(--gray-50);
    border: 1px solid var(--border-oral);
    border-radius: var(--radius-sm);
    padding: var(--space-3) var(--space-4);
  }

  .stat-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin: 4px 0;
    font-family: var(--font-body);
  }

  .oral-modal-footer {
    padding: var(--space-3) var(--space-4);
    background: var(--gray-50);
    border-top: 1px solid var(--border-oral);
    display: flex;
    justify-content: center;
    gap: var(--space-3);
  }
</style>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
        <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
        <line x1="12" y1="19" x2="12" y2="23" />
        <line x1="8" y1="23" x2="16" y2="23" />
      </svg>
    </span>
    Evaluación de Examen Oral
  </h1>
</div>

<div class="oral-review-container">
  <?php if (!$student || !$assigned_text): ?>
    <div class="empty-state" style="padding: var(--space-8); text-align: center;">
      <h3>Datos de evaluación no válidos</h3>
      <p style="color: var(--muted-oral);">No se encontró ningún examen oral asignado a este alumno para este parcial.</p>
      <a href="index.php?page=oral_exam" class="btn btn-primary" style="margin-top: var(--space-4);">Volver</a>
    </div>
  <?php else: ?>

    <div class="oral-header">
      <a href="index.php?page=oral_exam" class="btn btn-outline"
        style="border-color: rgba(255,255,255,0.3); color: white;">Atrás</a>
      <div class="oral-header-text">
        <h2><?= htmlspecialchars($student['nombre_completo']) ?></h2>
        <p>Matrícula: <?= htmlspecialchars($student['matricula']) ?> — Parcial <?= $parcial ?></p>
      </div>
      <button class="btn-grade" onclick="calculateGrade()">Calificar</button>
    </div>

    <div class="review-layout">
      <div class="exam-text-card">
        <?php if (!empty($assigned_text['titulo'])): ?>
          <div class="topic-title">
            <?= htmlspecialchars($assigned_text['titulo']) ?>
          </div>
        <?php endif; ?>
        <p class="exam-text" id="examText"><?= htmlspecialchars($assigned_text['texto']) ?></p>
      </div>

      <div class="word-list" id="wordList">
        <h3>Selected Words</h3>
      </div>
    </div>

    <!-- Modal Resultados -->
    <div class="oral-modal-overlay" id="gradeModal">
      <div class="oral-modal-card">
        <div class="oral-modal-header">Resultados de la Evaluación</div>
        <div class="oral-modal-body">
          <div class="score-circle">
            <span class="score-value" id="modalScore">10.0</span>
            <span class="score-label">Nota</span>
          </div>
          <div class="stats-container">
            <div class="stat-row">
              <span>Palabras correctas:</span>
              <span id="modalCorrect" style="font-weight: 700;">0</span>
            </div>
            <div class="stat-row">
              <span>Palabras incorrectas:</span>
              <span id="modalIncorrect" style="font-weight: 700; color: #ef4444;">0</span>
            </div>
          </div>
        </div>
        <div class="oral-modal-footer">
          <button class="btn btn-outline" onclick="closeModal()">Cancelar</button>
          <button class="btn btn-primary" onclick="submitGrade()">Aceptar y Guardar</button>
        </div>
      </div>
    </div>

    <script>
      const currentText = <?= json_encode($assigned_text['texto']) ?>;
      let fontSize = 22;
      const MIN_FS = 14, MAX_FS = 48;
      let selectedWords = [];
      let finalCalculatedGrade = 10.0;

      function changeFont(delta) {
        fontSize = Math.min(MAX_FS, Math.max(MIN_FS, fontSize + delta));
        document.getElementById('sizeDisplay').textContent = fontSize;
        document.getElementById('examText').style.fontSize = fontSize + 'px';
      }

      function wrapWordsInSpans(text) {
        const words = text.split(/\s+/);
        return words.map(w => `<span class="word-span">${w}</span>`).join(' ');
      }

      // Initialize spans
      document.getElementById('examText').innerHTML = wrapWordsInSpans(currentText);

      function updateWordList() {
        const wordList = document.getElementById('wordList');
        wordList.innerHTML = `<h3>Selected Words</h3>`;
        selectedWords.forEach(word => {
          const item = document.createElement('div');
          item.className = 'word-item';

          const textSpan = document.createElement('span');
          textSpan.className = 'word-item-text';
          textSpan.textContent = word;
          textSpan.onclick = () => speakWord(word);

          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.className = 'word-checkbox';
          checkbox.title = 'Marcar si lo pronunció correctamente';

          item.appendChild(textSpan);
          item.appendChild(checkbox);
          wordList.appendChild(item);
        });
      }

      document.addEventListener('mouseup', () => {
        const sel = window.getSelection();
        let word = sel.toString().trim();
        if (!word || word.includes(' ') || word.length < 2) return;
        word = word.replace(/[.,;:!?"'()“”]/g, '').toLowerCase();
        if (!selectedWords.includes(word)) {
          selectedWords.push(word);
          updateWordList();
        }
      });

      function speakWord(word) {
        speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(word);
        u.lang = 'en-US';
        u.rate = 0.8;
        const voice = getEnglishVoice();
        if (voice) u.voice = voice;
        speechSynthesis.speak(u);
      }

      function getEnglishVoice() {
        const voices = speechSynthesis.getVoices();
        const maleLocal = voices.find(v => v.lang.startsWith('en') && v.name.toLowerCase().includes('male') && v.localService);
        if (maleLocal) return maleLocal;
        const maleNetwork = voices.find(v => v.lang.startsWith('en') && v.name.toLowerCase().includes('male') && !v.localService);
        if (maleNetwork) return maleNetwork;
        const englishLocal = voices.find(v => v.lang.startsWith('en') && v.localService);
        if (englishLocal) return englishLocal;
        const networkEn = voices.find(v => v.lang.startsWith('en') && !v.localService);
        if (networkEn) return networkEn;
        return voices.find(v => v.lang.startsWith('en')) || null;
      }

      function calculateGrade() {
        const checkboxes = document.querySelectorAll('.word-checkbox');
        const totalWords = checkboxes.length;
        if (totalWords === 0) {
          showModal(10.0, 0, 0);
          return;
        }
        const correct = document.querySelectorAll('.word-checkbox:checked').length;
        const incorrect = totalWords - correct;

        finalCalculatedGrade = Math.max(0, 10 - (incorrect * 0.5));
        showModal(finalCalculatedGrade, correct, incorrect);
      }

      function showModal(grade, correct, incorrect) {
        const modal = document.getElementById('gradeModal');
        document.getElementById('modalScore').textContent = grade.toFixed(1);
        document.getElementById('modalCorrect').textContent = correct;
        document.getElementById('modalIncorrect').textContent = incorrect;

        modal.style.display = 'flex';
        modal.offsetHeight; // Force reflow
        modal.classList.add('active');
      }

      function closeModal() {
        const modal = document.getElementById('gradeModal');
        modal.classList.remove('active');
        setTimeout(() => {
          modal.style.display = 'none';
        }, 300);
      }

      function submitGrade() {
        const fd = new FormData();
        fd.append('id_grupo', <?= json_encode($grupoId) ?>);
        fd.append('id_alumno', <?= json_encode($student['id_alumno']) ?>);
        fd.append('parcial', <?= json_encode($parcial) ?>);
        fd.append('calificacion', finalCalculatedGrade);

        fetch('index.php?action=save_oral_grade', {
          method: 'POST',
          body: fd
        })
          .then(r => r.json())
          .then(res => {
            if (res.success) {
              closeModal();
              if (typeof showToast === 'function') {
                showToast(res.message, 'success');
              } else {
                alert(res.message);
              }
              setTimeout(() => {
                window.location.href = 'index.php?page=oral_exam';
              }, 1000);
            } else {
              alert(res.message);
            }
          })
          .catch(() => {
            alert('Error de red al guardar la calificación');
          });
      }

      if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = () => { /* voices ready */ };
      }
    </script>
  <?php endif; ?>
</div>