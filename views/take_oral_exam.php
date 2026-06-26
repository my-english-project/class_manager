<?php
/**
 * ClassHub — Take Oral Exam (OE)
 * 
 * Practice and listening page for students presenting the oral exam (RF-28).
 */

$assigned_text = $assigned_text ?? null;
$parcial = $parcial ?? 1;
?>

<link
  href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Source+Serif+4:ital,opsz,wght@0,8..60,300;0,8..60,400;1,8..60,300&display=swap"
  rel="stylesheet">

<style>
  .oral-exam-container {
    --bg-oral: var(--bg-body);
    --surface-oral: var(--bg-surface);
    --ink-oral: var(--text-primary);
    --accent-oral: var(--color-primary);
    --accent-light-oral: rgba(58, 155, 92, 0.12);
    --muted-oral: var(--text-secondary);
    --border-oral: var(--gray-200);

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
    box-shadow: var(--shadow-sm);
  }

  .oral-header h2 {
    font-family: var(--font-heading);
    color: var(--text-inverse);
    margin: 0;
    font-size: 24px;
    font-weight: 700;
  }

  .oral-header p {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.75);
    margin: 4px 0 0;
  }

  .oral-controls {
    background: var(--gray-50);
    border: 1px solid var(--border-oral);
    border-radius: var(--radius-md);
    padding: var(--space-4);
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-4);
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-6);
  }

  .font-controls {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .font-controls span {
    font-size: 11px;
    color: var(--muted-oral);
    letter-spacing: 1px;
    text-transform: uppercase;
    font-family: var(--font-heading);
    font-weight: 600;
  }

  .btn-oral {
    background: var(--uts-blue);
    color: var(--text-inverse);
    border: none;
    border-radius: var(--radius-sm);
    width: 36px;
    height: 36px;
    font-size: 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background var(--transition-fast), transform var(--transition-fast);
    flex-shrink: 0;
  }

  .btn-oral:hover {
    background: var(--accent-oral);
  }

  .btn-oral:active {
    transform: scale(0.95);
  }

  .btn-tts-play {
    background: var(--accent-oral) !important;
    color: #fff !important;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-full);
    font-size: 18px;
    flex-shrink: 0;
  }

  .btn-tts-play:hover {
    background: var(--color-primary-dark) !important;
  }

  .btn-tts-stop {
    background: var(--muted-oral) !important;
    color: #fff !important;
    flex-shrink: 0;
  }

  .btn-tts-stop:hover {
    background: var(--gray-600) !important;
  }

  .tts-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
  }

  .speed-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .speed-label {
    font-size: 11px;
    color: var(--muted-oral);
    text-transform: uppercase;
    font-family: var(--font-heading);
    font-weight: 600;
  }

  .speed-display {
    font-size: 13px;
    color: var(--accent-oral);
    font-weight: 700;
    min-width: 40px;
    text-align: center;
  }

  .tts-status {
    font-size: 12px;
    color: var(--muted-oral);
    font-style: italic;
    min-width: 80px;
  }

  .tts-status.speaking {
    color: var(--accent-oral);
    animation: oralPulse 1.2s ease-in-out infinite;
  }

  @keyframes oralPulse {

    0%,
    100% {
      opacity: 1;
    }

    50% {
      opacity: 0.4;
    }
  }

  .exam-text-card {
    background: var(--gray-50);
    border: 1px solid var(--border-oral);
    border-radius: var(--radius-md);
    padding: var(--space-6);
    min-height: 200px;
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
    font-size: 22px;
    line-height: 1.8;
    color: var(--text-primary);
    transition: font-size 0.2s ease;
    overflow-wrap: break-word;
    font-family: var(--font-body);
  }

  .word-highlight {
    background: var(--accent-light-oral);
    border-radius: var(--radius-sm);
    padding: 2px 4px;
  }

  .tts-active {
    background: var(--accent-light-oral);
    border-radius: var(--radius-sm);
    padding: 2px 4px;
  }

  .word-tip {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: var(--space-4);
    padding: 10px 14px;
    background: var(--accent-light-oral);
    border: 1px solid var(--border-oral);
    border-radius: var(--radius-sm);
    font-size: 13px;
    color: var(--text-primary);
  }

  .word-tip-dismiss {
    margin-left: auto;
    background: none;
    border: none;
    color: var(--muted-oral);
    cursor: pointer;
    font-size: 14px;
  }

  @media (max-width: 768px) {
    .oral-controls {
      gap: var(--space-2);
      padding: var(--space-2) var(--space-3);
    }
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
    Examen Oral
  </h1>
</div>

<div class="oral-exam-container">
  <?php if (!$assigned_text): ?>
    <div class="empty-state" style="padding: var(--space-8); text-align: center;">
      <h3>Examen Oral no asignado</h3>
      <p style="color: var(--muted-oral);">Tu profesor aún no ha asignado un texto de examen oral para este parcial.</p>
      <a href="index.php?page=home" class="btn btn-primary" style="margin-top: var(--space-4);">Volver al Home</a>
    </div>
  <?php else: ?>

    <div class="oral-header">
      <h2>Oral Exam Parcial <?= $parcial ?></h2>
      <p>
        <?= htmlspecialchars($assigned_text['siglas'] . ' — Group ' . $assigned_text['cuatrimestre'] . $assigned_text['grupo']) ?>
      </p>
    </div>

    <div class="oral-controls"
      style="display: flex; flex-direction: row; flex-wrap: wrap; justify-content: center; align-items: center; gap: var(--space-3); padding: var(--space-3) var(--space-4);">
      <!-- Font Size Controls (no "SIZE" label, -A / +A buttons) -->
      <div class="font-controls" style="display: flex; align-items: center; gap: var(--space-2);">
        <button class="btn-oral" onclick="changeFont(-2)" aria-label="Decrease font size"
          style="font-size: var(--text-xs); font-weight: 700;">-A</button>
        <span class="speed-display" id="sizeDisplay"
          style="min-width: 24px; font-weight: 700; color: var(--uts-blue);">22</span>
        <button class="btn-oral" onclick="changeFont(2)" aria-label="Increase font size"
          style="font-size: var(--text-xs); font-weight: 700;">+A</button>
      </div>

      <!-- Playback Controls (Play / Stop) -->
      <div style="display: flex; align-items: center; gap: 8px;">
        <button class="btn-oral btn-tts-play" id="btnPlay" onclick="toggleSpeech()" title="Play/Pause"
          style="width: 36px; height: 36px; font-size: 14px;">▶</button>
        <button class="btn-oral btn-tts-stop" id="btnStop" onclick="stopSpeech()" title="Stop"
          style="width: 36px; height: 36px; font-size: 14px;">■</button>
      </div>

      <!-- Speed Controls (no "SPEED" label, speedometer SVGs) -->
      <div class="speed-wrap" style="display: flex; align-items: center; gap: var(--space-2);">
        <button class="btn-oral" onclick="changeSpeed(-0.25)" aria-label="Decrease speed" title="Slower">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"
            style="vertical-align: middle;">
            <path d="M20 19A9 9 0 0 0 4 19" />
            <path d="M12 19V10L8 12" />
            <circle cx="12" cy="19" r="1" />
            <line x1="3" y1="7" x2="7" y2="7" />
          </svg>
        </button>
        <span class="speed-display" id="speedDisplay"
          style="min-width: 44px; font-weight: 700; color: var(--color-primary);">0.85×</span>
        <button class="btn-oral" onclick="changeSpeed(0.25)" aria-label="Increase speed" title="Faster">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"
            style="vertical-align: middle;">
            <path d="M20 19A9 9 0 0 0 4 19" />
            <path d="M12 19V10l4 2" />
            <circle cx="12" cy="19" r="1" />
            <line x1="17" y1="7" x2="21" y2="7" />
            <line x1="19" y1="5" x2="19" y2="9" />
          </svg>
        </button>
      </div>

      <!-- Exit Button -->
      <div>
        <a href="index.php?page=home" class="btn btn-outline"
          style="border-radius: var(--radius-sm); font-size: 12px; font-weight: 700; height: 36px; padding: 0 var(--space-4); display: inline-flex; align-items: center; justify-content: center; border-color: var(--border-oral); color: var(--ink-oral); transition: all var(--transition-fast);">Salir</a>
      </div>
    </div>

    <div class="exam-text-card">
      <?php if (!empty($assigned_text['titulo'])): ?>
        <div class="topic-title">
          <?= htmlspecialchars($assigned_text['titulo']) ?>
        </div>
      <?php endif; ?>
      <p class="exam-text" id="examText"><?= htmlspecialchars($assigned_text['texto']) ?></p>

      <div class="word-tip" id="wordTip">
        <span>💡</span>
        <span>Selecciona o haz clic en cualquier palabra para escuchar su pronunciación de forma individual.</span>
        <button class="word-tip-dismiss" onclick="document.getElementById('wordTip').style.display='none'">✕</button>
      </div>
    </div>

    <script>
      const currentText = <?= json_encode($assigned_text['texto']) ?>;
      let fontSize = 22;
      const MIN_FS = 14, MAX_FS = 36;
      let speechRate = 0.85;
      const SPEED_MIN = 0.25, SPEED_MAX = 2.0;
      let currentUtterance = null;
      let isSpeaking = false;
      let isPaused = false;
      let speechGeneration = 0;

      function changeFont(delta) {
        fontSize = Math.min(MAX_FS, Math.max(MIN_FS, fontSize + delta));
        document.getElementById('sizeDisplay').textContent = fontSize;
        document.getElementById('examText').style.fontSize = fontSize + 'px';
      }

      function changeSpeed(delta) {
        speechRate = Math.min(SPEED_MAX, Math.max(SPEED_MIN, +(speechRate + delta).toFixed(2)));
        document.getElementById('speedDisplay').textContent = speechRate + '×';
        if (isSpeaking && !isPaused) {
          startSpeech();
        }
      }

      function getEnglishVoice() {
        const voices = speechSynthesis.getVoices();
        const preferred = voices.find(v => v.lang.startsWith('en') && v.name.includes('Daniel'));
        if (preferred) return preferred;
        const networkEn = voices.find(v => v.lang.startsWith('en') && !v.localService);
        if (networkEn) return networkEn;
        return voices.find(v => v.lang.startsWith('en')) || null;
      }

      function wrapWordsInSpans(text) {
        const words = text.split(/\s+/);
        return words.map((w, i) => `<span class="tts-word" data-index="${i}">${w}</span>`).join(' ');
      }

      function highlightWord(index) {
        document.querySelectorAll('.tts-word.word-highlight').forEach(el => {
          el.classList.remove('word-highlight');
        });
        const target = document.querySelector(`.tts-word[data-index="${index}"]`);
        if (target) {
          target.classList.add('word-highlight');
          target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      }

      function clearHighlights() {
        document.querySelectorAll('.tts-word.word-highlight').forEach(el => {
          el.classList.remove('word-highlight');
        });
      }

      function updateTTSUI(state) {
        const btnPlay = document.getElementById('btnPlay');
        const status = document.getElementById('ttsStatus');

        switch (state) {
          case 'playing':
            if (btnPlay) btnPlay.textContent = '⏸';
            if (status) {
              status.textContent = '🔊 Reading…';
              status.className = 'tts-status speaking';
            }
            break;
          case 'paused':
            if (btnPlay) btnPlay.textContent = '▶';
            if (status) {
              status.textContent = '⏸ Paused';
              status.className = 'tts-status';
            }
            break;
          case 'idle':
          default:
            if (btnPlay) btnPlay.textContent = '▶';
            if (status) {
              status.textContent = '';
              status.className = 'tts-status';
            }
            break;
        }
      }

      function toggleSpeech() {
        if (isSpeaking && !isPaused) {
          speechSynthesis.pause();
          isPaused = true;
          updateTTSUI('paused');
          return;
        }
        if (isPaused) {
          speechSynthesis.resume();
          isPaused = false;
          updateTTSUI('playing');
          return;
        }
        startSpeech();
      }

      function startSpeech() {
        const gen = ++speechGeneration;
        speechSynthesis.cancel();

        const examEl = document.getElementById('examText');
        if (!examEl) return;

        examEl.innerHTML = wrapWordsInSpans(currentText);

        const utterance = new SpeechSynthesisUtterance(currentText);
        utterance.lang = 'en-US';
        utterance.rate = speechRate;

        const voice = getEnglishVoice();
        if (voice) utterance.voice = voice;

        const words = currentText.split(/\s+/);

        utterance.onboundary = function (e) {
          if (gen !== speechGeneration) return;
          if (e.name === 'word') {
            const charIndex = e.charIndex;
            let cumulative = 0;
            for (let i = 0; i < words.length; i++) {
              if (cumulative >= charIndex) {
                highlightWord(i);
                break;
              }
              cumulative += words[i].length + 1;
            }
          }
        };

        utterance.onend = function () {
          if (gen !== speechGeneration) return;
          isSpeaking = false;
          isPaused = false;
          clearHighlights();
          examEl.textContent = currentText;
          updateTTSUI('idle');
        };

        utterance.onerror = function (e) {
          if (gen !== speechGeneration) return;
          if (e.error === 'interrupted' || e.error === 'canceled') return;
          isSpeaking = false;
          isPaused = false;
          clearHighlights();
          examEl.textContent = currentText;
          updateTTSUI('idle');
        };

        currentUtterance = utterance;
        isSpeaking = true;
        isPaused = false;
        updateTTSUI('playing');

        setTimeout(() => {
          if (gen !== speechGeneration) return;
          speechSynthesis.speak(utterance);
        }, 50);
      }

      function stopSpeech() {
        speechGeneration++;
        speechSynthesis.cancel();
        isSpeaking = false;
        isPaused = false;
        clearHighlights();
        updateTTSUI('idle');
        const examEl = document.getElementById('examText');
        if (examEl) {
          examEl.textContent = currentText;
        }
      }

      // Single word double-click pronunciation
      document.addEventListener('dblclick', () => {
        const sel = window.getSelection();
        const word = sel.toString().trim().replace(/[.,;:!?"'()“”]/g, '');
        if (!word || word.includes(' ') || word.length < 2) return;

        speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(word);
        u.lang = 'en-US';
        u.rate = Math.max(0.6, speechRate - 0.1);
        const voice = getEnglishVoice();
        if (voice) u.voice = voice;
        speechSynthesis.speak(u);
      });

      // Mobile tap word pronunciation
      document.addEventListener('touchend', (e) => {
        if (isSpeaking) return;
        if (e.changedTouches.length !== 1) return;

        const touch = e.changedTouches[0];
        const examEl = document.getElementById('examText');
        if (!examEl) return;

        const target = document.elementFromPoint(touch.clientX, touch.clientY);
        if (!target || !examEl.contains(target)) return;

        let range;
        if (document.caretRangeFromPoint) {
          range = document.caretRangeFromPoint(touch.clientX, touch.clientY);
        } else if (document.caretPositionFromPoint) {
          const pos = document.caretPositionFromPoint(touch.clientX, touch.clientY);
          if (pos) {
            range = document.createRange();
            range.setStart(pos.offsetNode, pos.offset);
            range.setEnd(pos.offsetNode, pos.offset);
          }
        }
        if (!range || !range.startContainer || range.startContainer.nodeType !== 3) return;

        const textNode = range.startContainer;
        const offset = range.startOffset;
        const fullText = textNode.textContent;

        let start = offset, end = offset;
        while (start > 0 && /\S/.test(fullText[start - 1])) start--;
        while (end < fullText.length && /\S/.test(fullText[end])) end++;

        const word = fullText.slice(start, end).replace(/[.,;:!?"'()]/g, '');
        if (!word || word.length < 2) return;

        const before = fullText.slice(0, start);
        const after = fullText.slice(end);
        const wordRaw = fullText.slice(start, end);

        const span = document.createElement('span');
        span.className = 'tts-active';
        span.textContent = wordRaw;

        const parent = textNode.parentNode;
        const beforeNode = document.createTextNode(before);
        const afterNode = document.createTextNode(after);
        parent.replaceChild(afterNode, textNode);
        parent.insertBefore(span, afterNode);
        parent.insertBefore(beforeNode, span);

        speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(word);
        u.lang = 'en-US';
        u.rate = Math.max(0.6, speechRate - 0.1);
        const voice = getEnglishVoice();
        if (voice) u.voice = voice;

        u.onend = () => {
          parent.replaceChild(document.createTextNode(before + wordRaw + after), beforeNode);
          if (span.parentNode) span.remove();
          if (afterNode.parentNode) afterNode.remove();
        };
        u.onerror = u.onend;

        speechSynthesis.speak(u);
      });

      // Warm up voices
      if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = () => { };
      }
    </script>
  <?php endif; ?>
</div>