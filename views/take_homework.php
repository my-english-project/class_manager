<?php
/**
 * Class Manager — Take Homework (take_homework.php)
 * 
 * Online homework solver matching the exact format of the written exam wizard.
 */

if (empty($_SESSION['logged_in']) || !in_array($_SESSION['usuario']['rol'] ?? '', ['alumno', 'docente', 'admin'])) {
  header('Location: index.php?page=login');
  exit;
}
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php

$db = Database::getConnection();
$alumnoId = (int) ($_SESSION['alumno']['id_alumno'] ?? 0);
$actividadId = (int) ($_GET['id_actividad'] ?? 0);

$actividad = $actividad ?? null;
$questions = $questions ?? [];

if (!$actividad) {
  echo "<div class='card' style='padding: var(--space-6); text-align: center;'><h3>Tarea no encontrada o inválida.</h3><a href='index.php?page=home' class='btn btn-primary'>Volver al inicio</a></div>";
  return;
}

// Check if student has already completed this homework
$stmtCheck = $db->prepare("SELECT calificacion FROM calificacion_actividad WHERE id_actividad = :aid AND id_alumno = :alu LIMIT 1");
$stmtCheck->execute([':aid' => $actividadId, ':alu' => $alumnoId]);
$existingGrade = $stmtCheck->fetchColumn();

if ($existingGrade !== false && $existingGrade !== null) {
  echo "
    <div class='card' style='padding: var(--space-8); text-align: center; max-width: 600px; margin: 40px auto; border-radius: 16px; border: 2px solid var(--border-color); box-shadow: 0 8px 24px rgba(0,0,0,0.06);'>
        <div style='font-size: 64px; margin-bottom: var(--space-4);'>📚</div>
        <h2 style='font-family: var(--font-heading); color: var(--uts-green); font-weight: 800; margin-bottom: var(--space-2);'>Tarea completada</h2>
        <p style='color: var(--gray-600); margin-bottom: var(--space-6);'>Ya has completado esta tarea. Tu calificación obtenida es:</p>
        <div style='font-size: 48px; font-weight: 900; color: var(--color-primary); font-family: var(--font-heading); margin-bottom: var(--space-6); background: #faf9f6; padding: var(--space-4); border-radius: 12px; border: 1.5px dashed var(--border-color); display: inline-block; min-width: 150px;'>
            " . number_format((float) $existingGrade, 2) . "
        </div>
        <div>
            <a href='index.php?page=home' class='btn btn-primary' style='border-radius: 20px; padding: var(--space-3) var(--space-6);'>Volver al Dashboard</a>
        </div>
    </div>";
  return;
}

if (empty($questions)) {
  echo "<div class='card' style='padding: var(--space-6); text-align: center;'><h3>Esta tarea no tiene preguntas configuradas.</h3><a href='index.php?page=home' class='btn btn-primary'>Volver al inicio</a></div>";
  return;
}

// Shuffle questions for homework just like in written exam
shuffle($questions);
?>

<div id="welcome-card" class="card"
  style="max-width: 650px; margin: 40px auto; padding: var(--space-8); border-radius: 16px; border: 2px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; background: var(--card-bg);">
  <h2 style="font-family: var(--font-heading); color: var(--primary-800); font-weight: 800; margin-bottom: var(--space-3); font-size: 26px;">
    Resolver Tarea
  </h2>
  <h4 style="color: var(--gray-600); font-weight: 600; margin-bottom: var(--space-4);"><?= htmlspecialchars($actividad['nombre']) ?></h4>

  <div style="text-align: left; background: #faf9f6; padding: var(--space-5); border-radius: 12px; border: 1.5px dashed var(--border-color); margin-bottom: var(--space-6); font-size: var(--text-sm); line-height: 1.6; color: var(--gray-700);">
    <p style="margin-top: 0; font-weight: 700;">Instrucciones:</p>
    <ul style="padding-left: var(--space-4); margin-bottom: 0; display: flex; flex-direction: column; gap: var(--space-2);">
      <li>Lee detenidamente cada pregunta y selecciona la respuesta correcta.</li>
      <li>Al comprobar cada respuesta verás una retroalimentación con la explicación de la regla gramatical aplicada.</li>
      <li>Una vez enviada, la tarea se calificará automáticamente y la nota se registrará de inmediato.</li>
    </ul>
  </div>

  <div style="display: flex; justify-content: space-between; align-items: center; margin-top: var(--space-6);">
    <a href="index.php?page=home" class="btn btn-outline" style="border-radius: 20px; font-weight: bold; padding: var(--space-3) var(--space-8);">
      Cancelar
    </a>
    <button type="button" class="btn btn-primary" onclick="startHomeworkWizard()" style="border-radius: 20px; font-weight: bold; padding: var(--space-3) var(--space-8); box-shadow: 0 4px 12px rgba(10,111,81,0.2);">
      Comenzar
    </button>
  </div>
</div>

<div class="take-homework-wrapper" style="display: none; max-width: 750px; margin: 0 auto;">
  <!-- Header Bar -->
  <div style="background: var(--bg-surface); border: 2.5px solid var(--border-color); border-radius: 16px; padding: var(--space-4) var(--space-6); margin-bottom: var(--space-6); display: flex; justify-content: space-between; align-items: center; flex-direction: column; gap: var(--space-3);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
      <div>
        <h2 style="font-family: var(--font-heading); font-size: 20px; font-weight: 800; color: var(--primary-800); margin: 0;"><?= htmlspecialchars($actividad['nombre']) ?></h2>
        <span style="font-size: var(--text-xs); color: var(--gray-500); font-weight: 600;">Parcial <?= $actividad['parcial'] ?> — Tarea Autocalificable</span>
      </div>
    </div>

    <!-- Progress Indicator -->
    <div style="display: flex; justify-content: space-between; align-items: center; gap: var(--space-4); width: 100%; border-top: 1px solid var(--gray-100); padding-top: var(--space-2);">
      <div style="flex: 1; display: flex; align-items: center; gap: var(--space-3);">
        <span style="font-size: 11px; font-weight: 800; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Progreso</span>
        <div style="flex: 1; height: 8px; background: var(--border-color); border-radius: 4px; overflow: hidden;">
          <div id="hw-progress-bar" style="width: <?= round(100 / count($questions), 2) ?>%; height: 100%; background: linear-gradient(90deg, var(--uts-green), #149b6e); transition: width 0.3s ease; border-radius: 4px;"></div>
        </div>
      </div>
      <span id="progress-text" style="font-size: 12px; font-weight: 800; color: var(--gray-600); white-space: nowrap;">Pregunta 1 de <?= count($questions) ?></span>
    </div>

  </div>

  <!-- Homework Wizard Form -->
  <form id="form-take-homework">
    <input type="hidden" name="id_actividad" value="<?= $actividadId ?>">

    <div class="wizard-questions-container">
      <?php foreach ($questions as $index => $q): 
        $options = $q['opciones'];
        // Shuffle options for the questions
        shuffle($options);

        $correctOptId = 0;
        $correctOptText = '';
        foreach ($options as $opt) {
          if ($opt['es_correcta']) {
            $correctOptId = $opt['id_opcion'];
            $correctOptText = $opt['texto'];
          }
        }
      ?>
        <div class="card homework-question-card" id="q-card-<?= $q['id_pregunta'] ?>" data-question-index="<?= $index ?>"
          data-correct-id="<?= $correctOptId ?>" data-correct-text="<?= htmlspecialchars($correctOptText) ?>"
          data-seccion-letra="<?= $q['seccion_letra'] ?>"
          data-part="<?= $q['id_topico'] ?>" data-section="<?= $q['seccion_letra'] ?>"
          style="border: 2.5px solid var(--border-color); border-radius: 12px; padding: var(--space-6); background: var(--card-bg); display: <?= $index === 0 ? 'block' : 'none' ?>;">
          
          <!-- Instruction Box -->
          <div style="background: rgba(10,111,81,0.03); border-left: 3.5px solid var(--uts-green); padding: var(--space-2) var(--space-4); border-radius: 6px; margin-bottom: var(--space-4);">
            <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--uts-green); letter-spacing: 0.5px; margin-bottom: 2px;">
              <?= htmlspecialchars(strtoupper($q['seccion_nombre'])) ?>
            </div>
            <div style="font-size: 11px; color: var(--gray-600); font-weight: 600; line-height: 1.45;">
              Selecciona la respuesta correcta del siguiente reactivo.
            </div>
          </div>

          <div style="display: flex; gap: var(--space-3); margin-bottom: var(--space-4);">
            <span style="background: var(--uts-green); color: white; font-weight: 800; font-size: var(--text-sm); width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; flex-shrink: 0; font-family: monospace;">
              <?= $index + 1 ?>
            </span>
            <div>
              <h3 style="font-family: var(--font-body); font-size: 16px; font-weight: 600; color: var(--gray-800); line-height: 1.5; margin: 0;">
                <?php
                if ($q['seccion_letra'] === 'C') {
                  echo preg_replace('/((?:[a-zA-Z0-9\'-]+\s+){0,2}[a-zA-Z0-9\'-]+)\s*\(([A-C])\)/u', '<u style="text-decoration: underline; text-decoration-color: var(--uts-gold); text-underline-offset: 4px; font-weight: 700;">$1</u> <b>($2)</b>', htmlspecialchars($q['texto']));
                } else {
                  echo htmlspecialchars($q['texto']);
                }
                ?>
              </h3>
            </div>
          </div>

          <!-- Options -->
          <div class="options-list-container" id="opts-container-<?= $q['id_pregunta'] ?>" style="display: flex; flex-direction: column; gap: var(--space-2);">
            <?php foreach ($options as $opt): ?>
              <label class="homework-option-label" id="opt-label-<?= $opt['id_opcion'] ?>" style="display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) var(--space-4); border: 2px solid var(--border-color); border-radius: 10px; cursor: pointer; transition: all 0.2s; background: #faf9f6; font-size: var(--text-sm); font-weight: 500; color: var(--gray-700); margin: 0; user-select: none;">
                <input type="radio" name="answers[<?= $q['id_pregunta'] ?>]" value="<?= $opt['id_opcion'] ?>"
                  onclick="selectOption(<?= $q['id_pregunta'] ?>, <?= $opt['id_opcion'] ?>)"
                  style="width: 18px; height: 18px; accent-color: var(--uts-green); margin: 0;">
                <span><?= htmlspecialchars($opt['texto']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>

          <!-- Feedback Panel -->
          <div class="feedback-panel" id="feedback-<?= $q['id_pregunta'] ?>" style="display: none; margin-top: var(--space-5); padding: var(--space-4); border-radius: 10px; border-left: 4px solid var(--border-color); font-size: var(--text-sm); line-height: 1.5;">
            <div style="font-weight: 700; margin-bottom: var(--space-2);" class="feedback-title"></div>
            <div class="feedback-explanation"></div>
          </div>

          <!-- Next Button -->
          <div style="margin-top: var(--space-6); text-align: right;">
            <button type="button" id="btn-next-<?= $q['id_pregunta'] ?>" onclick="confirmAndNext(<?= $q['id_pregunta'] ?>, <?= $index ?>)" class="btn btn-primary" disabled style="border-radius: 20px; font-weight: bold; padding: var(--space-2) var(--space-6);">
              Comprobar Respuesta
            </button>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  </form>
</div>

<script>
const totalQuestions = <?= count($questions) ?>;
let currentIndex = 0;
const selectedOptionMap = {};
const confirmedSet = new Set();

const explanations = {
  // Topic 1: Modals for Speculating
  '1_A': "En especulación en presente, 'must' expresa una alta certeza (>90%) basada en evidencias, 'might/could/may' expresan una posibilidad del ~50%, mientras que 'can't' indica una imposibilidad lógica.",
  '1_B': "El orden correcto de las oraciones especulativas en inglés es: Sujeto + Modal + Verbo en forma base sin 'to'. Ejemplo: 'The story must be true'.",
  '1_C': "Los verbos modales son auxiliares puros: nunca añaden '-s' para terceras personas (he/she/it), siempre van seguidos de un verbo en infinitivo sin 'to' y no tienen gerundios (-ing).",
  '1_D': "Especulaciones en presente: 'Must' indica certeza de que algo es verdad, 'might'/'could' señalan posibilidad abierta, y 'can't' es la deducción lógica de que algo es imposible.",
  
  // Topic 2: First Conditional
  '2_A': "El Primer Condicional con modales expresa escenarios futuros muy posibles: se usa Presente Simple en la cláusula de la condición (If-clause) y un modal en presente + forma base en la cláusula de resultado.",
  '2_B': "Estructura del primer condicional: si se inicia con la cláusula 'If', esta se separa de la principal con una coma. Si se empieza con el resultado, no se añade coma.",
  '2_C': "Error condicional común: nunca se debe colocar 'will' o 'would' inmediatamente dentro de la cláusula condicional ('If'). El presente simple es el único tiempo correcto.",
  '2_D': "Los condicionales reales (tipo 1) se estructuran con un tiempo presente en la condición para proyectar de forma verosímil y probable un desenlace futuro.",
  
  // Topic 3: Second Conditional
  '3_A': "El Segundo Condicional expresa escenarios hipotéticos, irreales o imaginarios en el presente o futuro: se usa Pasado Simple en la condición (If-clause) y would + verbo base en el resultado.",
  '3_B': "Estructura del segundo condicional: si iniciamos con la cláusula 'If', usamos la coma para separarla de la cláusula principal (ej. If I won the lottery, I would buy a house).",
  '3_C': "Error condicional de tipo 2 común: nunca uses would en la cláusula que lleva 'If' (ej. no digas 'If I would have'). En su lugar, usa el pasado simple (ej. 'If I had').",
  '3_D': "En la cláusula del Segundo Condicional, se suele utilizar 'were' para todas las personas del verbo to be (I/he/she/it), expresando subjuntivo hipotético (ej. 'If I were you')."
};

function startHomeworkWizard() {
  document.getElementById('welcome-card').style.display = 'none';
  document.querySelector('.take-homework-wrapper').style.display = 'block';
  window.scrollTo(0, 0);
}

function selectOption(qId, optId) {
  selectedOptionMap[qId] = optId;
  const btn = document.getElementById('btn-next-' + qId);
  if (btn) btn.disabled = false;
}

function confirmAndNext(qId, index) {
  const card = document.getElementById('q-card-' + qId);
  const correctId = parseInt(card.getAttribute('data-correct-id'), 10);
  const correctText = card.getAttribute('data-correct-text');
  const seccionLetra = card.getAttribute('data-seccion-letra');
  const selectedId = selectedOptionMap[qId];

  const btn = document.getElementById('btn-next-' + qId);

  if (!confirmedSet.has(qId)) {
    confirmedSet.add(qId);

    // Disable all options
    const radios = card.querySelectorAll('input[type="radio"]');
    radios.forEach(r => r.disabled = true);

    const labels = card.querySelectorAll('.homework-option-label');
    labels.forEach(l => l.style.pointerEvents = 'none');

    const isCorrect = (selectedId === correctId);

    // Style correct/incorrect options
    labels.forEach(l => {
      const radio = l.querySelector('input');
      const val = parseInt(radio.value, 10);
      if (val === correctId) {
        l.style.borderColor = 'var(--uts-green)';
        l.style.background = 'rgba(58,155,92,0.06)';
        l.style.color = 'var(--uts-green-dark)';
      } else if (val === selectedId && !isCorrect) {
        l.style.borderColor = '#eb5757';
        l.style.background = 'rgba(235,87,87,0.06)';
        l.style.color = '#c53030';
      }
    });

    // Show Feedback Panel
    const fbPanel = document.getElementById('feedback-' + qId);
    const fbTitle = fbPanel.querySelector('.feedback-title');
    const fbExplanation = fbPanel.querySelector('.feedback-explanation');

    fbPanel.style.display = 'block';

    if (isCorrect) {
      fbPanel.style.borderLeftColor = 'var(--uts-green)';
      fbPanel.style.background = 'rgba(58,155,92,0.03)';
      fbTitle.innerHTML = '✨ ¡Correcto!';
      fbTitle.style.color = 'var(--uts-green-dark)';
    } else {
      fbPanel.style.borderLeftColor = '#eb5757';
      fbPanel.style.background = 'rgba(235,87,87,0.03)';
      fbTitle.innerHTML = '❌ Incorrecto';
      fbTitle.style.color = '#c53030';
    }

    const part = card.getAttribute('data-part');
    const section = card.getAttribute('data-section');
    const key = part + '_' + section;
    const ruleExplanation = explanations[key] || "Regla gramatical inglesa general para condicionales y auxiliares modales.";
    fbExplanation.innerHTML = `<b>Respuesta correcta:</b> ${correctText}.<br><br><b>¿Por qué?</b> ${ruleExplanation}`;

    if (index === totalQuestions - 1) {
      btn.innerText = 'Enviar Tarea';
    } else {
      btn.innerText = 'Siguiente Pregunta';
    }
  } else {
    // Transition to next question
    card.style.display = 'none';

    if (index < totalQuestions - 1) {
      currentIndex = index + 1;
      const nextCardId = document.querySelectorAll('.homework-question-card')[currentIndex].id;
      const nextCard = document.getElementById(nextCardId);
      nextCard.style.display = 'block';

      // Scroll smoothly to top
      window.scrollTo({ top: 0, behavior: 'smooth' });

      // Update progress
      updateProgress();
    } else {
      // Submit homework
      submitHomeworkData(qId);
    }
  }
}

function updateProgress() {
  const activeQuestionNum = currentIndex + 1;
  const pct = (activeQuestionNum / totalQuestions) * 100;

  document.getElementById('hw-progress-bar').style.width = pct + '%';
  document.getElementById('progress-text').innerText = 'Pregunta ' + activeQuestionNum + ' de ' + totalQuestions;
}

function submitHomeworkData(qId) {
  const btn = document.getElementById('btn-next-' + qId);
  if (btn) {
    btn.disabled = true;
    btn.innerText = 'Enviando...';
  }

  const form = document.getElementById('form-take-homework');
  const formData = new FormData(form);

  for (const [qId, optId] of Object.entries(selectedOptionMap)) {
    formData.append(`answers[${qId}]`, optId);
  }

    // Show loading spinner
    Swal.fire({
      title: 'Guardando y calificando...',
      text: 'Por favor, no cierres esta pestaña.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    fetch('index.php?action=submit_homework', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            title: '¡Tarea Enviada!',
            html: `Tu tarea fue calificada automáticamente.<br><br>Calificación obtenida: <span style="font-size: 32px; font-weight: 800; color: var(--uts-green);">${data.grade}</span>`,
            icon: 'success',
            confirmButtonColor: 'var(--uts-green)',
            confirmButtonText: 'Regresar al Dashboard'
          }).then(() => {
            window.location.href = 'index.php?page=home';
          });
        } else {
          Swal.fire({
            title: 'Error',
            text: data.message || 'Hubo un error al enviar la tarea.',
            icon: 'error',
            confirmButtonColor: '#eb5757',
            confirmButtonText: 'Aceptar'
          });
          if (btn) {
            btn.disabled = false;
            btn.innerText = 'Enviar Tarea';
          }
        }
      })
      .catch(() => {
        Swal.fire({
          title: 'Error de Red',
          text: 'No se pudo conectar al servidor. Revisa tu conexión a Internet.',
          icon: 'error',
          confirmButtonColor: '#eb5757',
          confirmButtonText: 'Aceptar'
        });
        if (btn) {
          btn.disabled = false;
          btn.innerText = 'Enviar Tarea';
        }
      });
}
</script>

<style>
.homework-option-label:hover {
  border-color: var(--uts-green) !important;
  background: rgba(58,155,92,0.02) !important;
}
</style>
