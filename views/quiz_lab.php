<?php
/**
 * ClassHub — Quiz Lab View
 * 
 * Central workshop for teachers to design and assign homework and manage the shared question bank.
 */

$topics = $topics ?? [];
$activities = $activities ?? [];
$docenteGrupos = $docenteGrupos ?? [];
$grupoActivo = $grupoActivo ?? null;

$activeTab = $_GET['tab'] ?? 'tareas';
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon" style="background: linear-gradient(135deg, #ec4899, #8b5cf6); color: white;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <ellipse cx="12" cy="12" rx="4" ry="10" fill="none" stroke="currentColor" stroke-width="2"/>
        <ellipse cx="12" cy="12" rx="4" ry="10" fill="none" stroke="currentColor" stroke-width="2" transform="rotate(60 12 12)"/>
        <ellipse cx="12" cy="12" rx="4" ry="10" fill="none" stroke="currentColor" stroke-width="2" transform="rotate(120 12 12)"/>
      </svg>
    </span>
    Quiz Lab
  </h1>
  <p class="page-description">Diseñador y programador de evaluaciones para tu grupo activo: <strong><?= $grupoActivo ? htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) : 'Ninguno' ?></strong></p>
</div>

<!-- Tab Navigation -->
<div class="tabs-container" style="margin-bottom: var(--space-6);">
  <div style="display: flex; gap: var(--space-2); border-bottom: 2px solid var(--gray-200); padding-bottom: 2px;">
    <button onclick="switchTab('tareas')" class="btn tab-trigger <?= $activeTab === 'tareas' ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px 20px 0 0; padding: var(--space-3) var(--space-6);">Tareas (Homework)</button>
    <button onclick="switchTab('banco')" class="btn tab-trigger <?= $activeTab === 'banco' ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 20px 20px 0 0; padding: var(--space-3) var(--space-6);">Banco de Preguntas</button>
  </div>
</div>

<!-- ======================= TAB: TAREAS ======================= -->
<div id="tab-content-tareas" class="tab-pane" style="display: <?= $activeTab === 'tareas' ? 'block' : 'none' ?>;">
  <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: var(--space-6); align-items: start;">
    
    <!-- Tareas List -->
    <div class="card" style="padding: var(--space-5);">
      <h3 style="margin-top: 0; margin-bottom: var(--space-4); font-weight: 800; color: var(--primary-800);">Tareas Asignadas al Grupo</h3>
      <?php if (empty($activities)): ?>
        <div class="empty-state" style="padding: var(--space-6);">
          <p>No hay tareas asignadas en este grupo.</p>
        </div>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: var(--space-3); max-height: 500px; overflow-y: auto;">
          <?php foreach ($activities as $act): ?>
            <div style="padding: var(--space-4); background: var(--gray-50); border: 1.5px solid var(--gray-200); border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
              <div>
                <strong style="display: block; font-size: var(--text-base); color: var(--gray-800);"><?= htmlspecialchars($act['nombre']) ?></strong>
                <span style="font-size: var(--text-xs); color: var(--gray-500); font-weight: 600;">Parcial <?= $act['parcial'] ?></span>
                <?php if ($act['id_topico']): 
                  // Find topic name
                  $topicName = 'Tópico no encontrado';
                  foreach ($topics as $t) {
                    if ($t['id_topico'] == $act['id_topico']) $topicName = $t['nombre'] . ' (' . $t['total_preguntas'] . ' preguntas)';
                  }
                ?>
                  <div style="margin-top: 4px;">
                    <span class="badge badge-outline" style="color: var(--uts-blue); border-color: var(--uts-blue); font-weight: 700; font-size: 10px;">
                      💻 Autocalificable: <?= htmlspecialchars($topicName) ?>
                    </span>
                  </div>
                <?php else: ?>
                  <div style="margin-top: 4px;">
                    <span class="badge badge-outline" style="color: var(--gray-500); border-color: var(--gray-400); font-size: 10px;">
                      ✍️ Captura Manual
                    </span>
                  </div>
                <?php endif; ?>
              </div>
              <div>
                <button class="btn btn-outline btn-sm" onclick="editHomework(<?= htmlspecialchars(json_encode($act)) ?>)">Editar</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Tarea Creator/Editor Form -->
    <div class="card" style="padding: var(--space-6);">
      <h3 id="form-hw-title" style="margin-top: 0; margin-bottom: var(--space-4); font-weight: 800; color: var(--primary-800);">Crear o Asignar Tarea</h3>
      
      <form id="form-quiz-homework" onsubmit="submitQuizHomework(event)">
        <input type="hidden" name="id_actividad" id="hw-id" value="0">
        <input type="hidden" name="id_grupo" value="<?= $grupoActivo['id_grupo'] ?? 0 ?>">
        
        <div class="form-group">
          <label class="form-label" for="hw-nombre">Nombre de la Tarea</label>
          <input type="text" class="form-control" name="nombre" id="hw-nombre" placeholder="Ej. Tarea 1: Verbos Regulares" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="hw-parcial">Parcial</label>
          <select class="form-control form-select" name="parcial" id="hw-parcial" required>
            <option value="1">Parcial 1</option>
            <option value="2">Parcial 2</option>
            <option value="3">Parcial 3</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" style="display: flex; align-items: center; gap: 8px; font-weight: 700; cursor: pointer;">
            <input type="checkbox" id="hw-is-online" onchange="toggleOnlineFields(this.checked)" style="width: 18px; height: 18px; accent-color: var(--uts-green);">
            <span>¿Hacer tarea autocalificable en línea?</span>
          </label>
          <input type="hidden" name="is_online" id="hw-is-online-hidden" value="0">
        </div>

        <div id="online-fields-container" style="display: none; padding: var(--space-4); background: #faf9f6; border: 1.5px solid var(--border-color); border-radius: 12px; margin-bottom: var(--space-4);">
          <div class="form-group">
            <label class="form-label" for="hw-topic-select">Tipo de Configuración</label>
            <select class="form-control form-select" name="id_topico" id="hw-topic-select" onchange="handleTopicSelectChange(this.value)">
              <option value="">Seleccionar reactivos de tópicos existentes</option>
              <option value="new">+ Crear un Nuevo Tópico y Preguntas...</option>
            </select>
          </div>

          <!-- Grid of existing topics to distribute questions -->
          <div id="hw-existing-topics-grid" style="margin-bottom: var(--space-4);">
            <label class="form-label" style="font-weight: 700; color: var(--gray-700); margin-bottom: var(--space-2); display: block;">Tópicos Disponibles y Cantidad de Reactivos</label>
            <div style="max-height: 200px; overflow-y: auto; border: 1.5px solid var(--border-color); border-radius: 12px; padding: var(--space-3); background: #ffffff; display: flex; flex-direction: column; gap: var(--space-2);">
              <?php foreach ($topics as $top): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--gray-100); padding-bottom: 4px;">
                  <span style="font-size: var(--text-sm); font-weight: 600; color: var(--gray-700); max-width: 70%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($top['nombre']) ?>">
                    <?= htmlspecialchars($top['nombre']) ?> (<?= $top['total_preguntas'] ?>)
                  </span>
                  <input type="number" class="form-control" name="distribucion[<?= $top['id_topico'] ?>]" min="0" max="<?= $top['total_preguntas'] ?>" value="0" style="width: 70px; height: 30px; border-radius: 8px; text-align: center; padding: 2px;">
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Fields to create new topic/questions -->
          <div id="new-topic-fields" style="display: none; border-top: 1px solid var(--gray-200); padding-top: var(--space-4); margin-top: var(--space-4);">
            <div class="form-group">
              <label class="form-label" for="hw-new-topic-name">Nombre del Nuevo Tópico</label>
              <input type="text" class="form-control" id="hw-new-topic-name" name="nuevo_topico_nombre" placeholder="Ej. Tarea 1: Verbos Regulares">
            </div>

            <div class="form-group">
              <label class="form-label" for="hw-tipo-ejercicio">Tipo de Ejercicios</label>
              <select class="form-control form-select" id="hw-tipo-ejercicio" name="tipo_ejercicio">
                <option value="Multiple choice">Multiple choice</option>
                <option value="Word ordering">Word ordering</option>
                <option value="Error identification">Error identification</option>
                <option value="Theoretical">Theoretical</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="hw-questions-text" style="display: flex; justify-content: space-between;">
                <span>Redacción de Preguntas y Opciones</span>
                <a href="#" onclick="showFormatHelp(event)" style="font-size: var(--text-xs); color: var(--uts-blue);">Ver Formato</a>
              </label>
              <textarea class="form-control" id="hw-questions-text" name="questions_text" rows="8" placeholder="1. What is the past tense of 'see'?&#10;a. seed&#10;*b. saw&#10;c. seen" style="font-family: monospace; font-size: var(--text-xs); line-height: 1.5;"></textarea>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: var(--space-3); margin-top: var(--space-5);">
          <button type="button" class="btn btn-outline" style="flex:1;" onclick="resetHwForm()">Limpiar</button>
          <button type="submit" class="btn btn-primary" style="flex:1;" id="btn-save-hw">Guardar Tarea</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ======================= TAB: BANCO DE PREGUNTAS ======================= -->
<div id="tab-content-banco" class="tab-pane" style="display: <?= $activeTab === 'banco' ? 'block' : 'none' ?>;">
  <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: var(--space-6); align-items: start;">
    
    <!-- Left Column: Creator / Appender Form -->
    <div class="card" style="padding: var(--space-6);">
      <h3 style="margin-top: 0; margin-bottom: var(--space-4); font-weight: 800; color: var(--primary-800);">Agregar Reactivos al Banco</h3>
      
      <form id="form-quiz-topic" onsubmit="submitQuizTopic(event)">
        <div class="form-group">
          <label class="form-label" for="topic-id-select">Selecciona el Tópico</label>
          <select class="form-control form-select" name="id_topico" id="topic-id-select" onchange="handleTopicSelectChange2(this.value)" required>
            <option value="">-- Selecciona un tópico existente --</option>
            <?php foreach ($topics as $top): ?>
              <option value="<?= $top['id_topico'] ?>"><?= htmlspecialchars($top['nombre']) ?> (<?= $top['total_preguntas'] ?>)</option>
            <?php endforeach; ?>
            <option value="new">+ Crear un Nuevo Tópico...</option>
          </select>
        </div>

        <div class="form-group" id="new-topic-name-container" style="display: none;">
          <label class="form-label" for="topic-new-name">Nombre del Nuevo Tópico</label>
          <input type="text" class="form-control" id="topic-new-name" name="nuevo_topico_nombre" placeholder="Ej. Unidad 3: Condicionales">
        </div>

        <div class="form-group">
          <label class="form-label" for="topic-tipo-ejercicio">Tipo de Ejercicios / Sección</label>
          <select class="form-control form-select" id="topic-tipo-ejercicio" name="tipo_ejercicio" onchange="renderFilteredQuestions()" required>
            <option value="Multiple choice">Multiple choice</option>
            <option value="Word ordering">Word ordering</option>
            <option value="Error identification">Error identification</option>
            <option value="Theoretical">Theoretical</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="topic-questions-text" style="display: flex; justify-content: space-between;">
            <span>Escribe las Preguntas a Agregar</span>
            <a href="#" onclick="showFormatHelp(event)" style="font-size: var(--text-xs); color: var(--uts-blue);">Ver Formato</a>
          </label>
          <textarea class="form-control" id="topic-questions-text" name="questions_text" rows="10" placeholder="1. If it rains, I __________ stay at home.&#10;*a. will&#10;b. would&#10;c. had" style="font-family: monospace; font-size: var(--text-xs); line-height: 1.5;" required></textarea>
        </div>

        <div style="display: flex; gap: var(--space-3); margin-top: var(--space-5);">
          <button type="button" class="btn btn-outline" style="flex:1;" onclick="resetTopicForm()">Limpiar</button>
          <button type="submit" class="btn btn-primary" style="flex:1;" id="btn-save-topic">Guardar Preguntas</button>
        </div>
      </form>
    </div>

    <!-- Right Column: Registered Questions list in selected topic -->
    <div class="card" style="padding: var(--space-5); min-height: 400px;">
      <h3 style="margin-top: 0; margin-bottom: var(--space-4); font-weight: 800; color: var(--primary-800);" id="questions-list-title">Preguntas Registradas</h3>
      
      <div id="questions-loading-state" style="display: none; text-align: center; padding: var(--space-8); color: var(--gray-500);">
        <p>Cargando preguntas...</p>
      </div>

      <div id="questions-empty-state" style="text-align: center; padding: var(--space-8); color: var(--gray-400);">
        <p>Selecciona un tópico existente de la lista para ver los reactivos dados de alta en el banco.</p>
      </div>

      <div id="questions-container" style="display: none; flex-direction: column; gap: var(--space-4); max-height: 550px; overflow-y: auto; padding-right: var(--space-2);">
        <!-- Questions will be dynamically rendered here -->
      </div>
    </div>
  </div>
</div>

<!-- Formatting Help Modal -->
<div class="modal-overlay" id="modal-format-help">
  <div class="modal" style="max-width: 500px; text-align: left;">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Formato de Preguntas</h3>
    <div style="font-size: var(--text-sm); line-height: 1.6; color: var(--gray-700);">
      <p>Escribe las preguntas con números al inicio y las opciones con letras. **Marca la opción correcta colocando un asterisco `*` antes de la letra o antes del texto**.</p>
      
      <p style="font-weight: 700; margin-bottom: 4px;">Ejemplo:</p>
      <pre style="background: var(--gray-100); padding: var(--space-3); border-radius: 8px; font-family: monospace; font-size: 11px; margin: 0 0 var(--space-4) 0;">
1. What is the past simple of "go"?
a. goed
*b. went
c. gone

2. John __________ since 8:00 AM.
*a. has been working
b. was working
c. works</pre>
      
      <p>Asegúrate de dejar una línea en blanco entre preguntas.</p>
    </div>
    <div style="margin-top: var(--space-5); text-align: right;">
      <button class="btn btn-primary btn-sm" onclick="closeModal('modal-format-help')">Entendido</button>
    </div>
  </div>
</div>

<script>
function switchTab(tab) {
  const url = new URL(window.location.href);
  url.searchParams.set('tab', tab);
  window.history.pushState({}, '', url.toString());

  document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
  
  if (tab === 'tareas') {
    document.getElementById('tab-content-tareas').style.display = 'block';
  } else if (tab === 'banco') {
    document.getElementById('tab-content-banco').style.display = 'block';
  }

  document.querySelectorAll('.tab-trigger').forEach(el => {
    el.classList.remove('btn-primary');
    el.classList.add('btn-outline');
  });

  // Find target button
  event.target.classList.remove('btn-outline');
  event.target.classList.add('btn-primary');
}

// TAREAS JS
function toggleOnlineFields(isOnline) {
  const container = document.getElementById('online-fields-container');
  container.style.display = isOnline ? 'block' : 'none';
  document.getElementById('hw-is-online-hidden').value = isOnline ? '1' : '0';
}

function handleTopicSelectChange(val) {
  const newTopicFields = document.getElementById('new-topic-fields');
  const isNew = val === 'new';
  newTopicFields.style.display = isNew ? 'block' : 'none';
  document.getElementById('hw-new-topic-name').required = isNew;
  document.getElementById('hw-tipo-ejercicio').required = isNew;
  document.getElementById('hw-questions-text').required = isNew;
  
  document.getElementById('hw-existing-topics-grid').style.display = isNew ? 'none' : 'block';
}

function submitQuizHomework(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-save-hw');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  const fd = new FormData(e.target);

  fetch('index.php?action=save_quiz_homework', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      btn.disabled = false;
      btn.textContent = 'Guardar Tarea';
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      } else {
        showToast(res.message, 'error');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = 'Guardar Tarea';
      showToast('Error de red al guardar tarea.', 'error');
    });
}

function editHomework(act) {
  document.getElementById('form-quiz-homework').reset();
  document.getElementById('hw-id').value = act.id_actividad;
  document.getElementById('hw-nombre').value = act.nombre;
  document.getElementById('hw-parcial').value = act.parcial;
  
  const isOnline = act.id_topico !== null && act.id_topico !== '';
  document.getElementById('hw-is-online').checked = isOnline;
  toggleOnlineFields(isOnline);
  
  if (isOnline) {
    if (act.distribucion_preguntas) {
      document.getElementById('hw-topic-select').value = "";
      handleTopicSelectChange("");
      try {
        const dist = JSON.parse(act.distribucion_preguntas);
        for (const [tid, qty] of Object.entries(dist)) {
          const input = document.querySelector(`input[name="distribucion[${tid}]"]`);
          if (input) {
            input.value = qty;
          }
        }
      } catch (e) {
        console.error("Error parsing distribution: ", e);
      }
    } else {
      document.getElementById('hw-topic-select').value = "new";
      handleTopicSelectChange("new");
    }
  }
  
  document.getElementById('form-hw-title').textContent = 'Modificar Tarea';
  document.getElementById('form-hw-title').scrollIntoView({ behavior: 'smooth' });
}

function resetHwForm() {
  document.getElementById('form-quiz-homework').reset();
  document.getElementById('hw-id').value = '0';
  document.getElementById('form-hw-title').textContent = 'Crear o Asignar Tarea';
  toggleOnlineFields(false);
  
  document.querySelectorAll('#hw-existing-topics-grid input[type="number"]').forEach(input => {
    input.value = 0;
  });
}

// BANCO DE PREGUNTAS JS
let loadedQuestions = [];

function handleTopicSelectChange2(val) {
  const isNew = val === 'new';
  document.getElementById('new-topic-name-container').style.display = isNew ? 'block' : 'none';
  document.getElementById('topic-new-name').required = isNew;

  const container = document.getElementById('questions-container');
  const empty = document.getElementById('questions-empty-state');
  const loading = document.getElementById('questions-loading-state');
  const title = document.getElementById('questions-list-title');

  if (isNew || val === '') {
    loadedQuestions = [];
    container.style.display = 'none';
    empty.style.display = 'block';
    empty.querySelector('p').textContent = isNew ? 'Escribe los reactivos en la izquierda para dar de alta el nuevo tópico.' : 'Selecciona un tópico existente de la lista para ver los reactivos dados de alta en el banco.';
    title.textContent = 'Preguntas Registradas';
    return;
  }

  // Load questions of topic
  empty.style.display = 'none';
  container.style.display = 'none';
  loading.style.display = 'block';

  // Get selector element text
  const selectEl = document.getElementById('topic-id-select');
  const topicName = selectEl.options[selectEl.selectedIndex].text;
  title.textContent = 'Preguntas en: ' + topicName;

  fetch('index.php?action=get_topic_questions&id_topico=' + val)
    .then(r => r.json())
    .then(data => {
      loading.style.display = 'none';
      if (data.success) {
        loadedQuestions = data.questions;
        renderFilteredQuestions();
      } else {
        loadedQuestions = [];
        empty.style.display = 'block';
        empty.querySelector('p').textContent = 'Este tópico no tiene preguntas registradas aún.';
      }
    })
    .catch(() => {
      loadedQuestions = [];
      loading.style.display = 'none';
      empty.style.display = 'block';
      empty.querySelector('p').textContent = 'Error de red al cargar las preguntas.';
    });
}

function renderFilteredQuestions() {
  const container = document.getElementById('questions-container');
  const empty = document.getElementById('questions-empty-state');
  const selectedType = document.getElementById('topic-tipo-ejercicio').value;

  container.innerHTML = '';
  
  const filtered = loadedQuestions.filter(q => q.seccion_nombre === selectedType);

  if (filtered.length > 0) {
    empty.style.display = 'none';
    filtered.forEach((q, idx) => {
      const qBox = document.createElement('div');
      qBox.style.padding = 'var(--space-4)';
      qBox.style.background = 'var(--gray-50)';
      qBox.style.border = '1px solid var(--gray-200)';
      qBox.style.borderRadius = '10px';
      qBox.style.marginBottom = 'var(--space-3)';

      const qHeader = document.createElement('div');
      qHeader.style.display = 'flex';
      qHeader.style.justifyContent = 'space-between';
      qHeader.style.fontSize = 'var(--text-xs)';
      qHeader.style.fontWeight = 'bold';
      qHeader.style.color = 'var(--uts-blue)';
      qHeader.style.marginBottom = '6px';
      qHeader.innerHTML = `<span>Pregunta ${idx + 1}</span> <span style="color: var(--gray-500);">${q.seccion_nombre}</span>`;
      qBox.appendChild(qHeader);

      const qText = document.createElement('p');
      qText.style.fontWeight = '600';
      qText.style.color = 'var(--gray-800)';
      qText.style.margin = '0 0 var(--space-3) 0';
      qText.textContent = q.texto;
      qBox.appendChild(qText);

      const optList = document.createElement('div');
      optList.style.display = 'flex';
      optList.style.flexDirection = 'column';
      optList.style.gap = '4px';

      q.opciones.forEach(opt => {
        const optDiv = document.createElement('div');
        optDiv.style.fontSize = '13px';
        optDiv.style.padding = '4px var(--space-3)';
        optDiv.style.borderRadius = '6px';
        optDiv.style.fontWeight = '500';
        
        if (parseInt(opt.es_correcta) === 1) {
          optDiv.style.background = 'rgba(58,155,92,0.1)';
          optDiv.style.color = 'var(--uts-green-dark)';
          optDiv.style.border = '1.5px solid var(--uts-green)';
          optDiv.innerHTML = `<strong>✓ ${opt.letra}) ${opt.texto}</strong>`;
        } else {
          optDiv.style.color = 'var(--gray-600)';
          optDiv.textContent = `${opt.letra}) ${opt.texto}`;
        }
        optList.appendChild(optDiv);
      });
      qBox.appendChild(optList);
      container.appendChild(qBox);
    });
    container.style.display = 'flex';
  } else {
    container.style.display = 'none';
    empty.style.display = 'block';
    empty.querySelector('p').textContent = `No hay preguntas de tipo "${selectedType}" registradas en este tópico.`;
  }
}

function submitQuizTopic(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-save-topic');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  const fd = new FormData(e.target);
  fetch('index.php?action=save_quiz_lab_topic', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      btn.disabled = false;
      btn.textContent = 'Guardar Preguntas';
      if (res.success) {
        showToast(res.message, 'success');
        
        // Clear text input
        document.getElementById('topic-questions-text').value = '';
        
        // Reload questions list
        const val = document.getElementById('topic-id-select').value;
        if (val === 'new') {
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          handleTopicSelectChange2(val);
        }
      } else {
        showToast(res.message, 'error');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = 'Guardar Preguntas';
      showToast('Error de red al guardar preguntas.', 'error');
    });
}

function resetTopicForm() {
  document.getElementById('form-quiz-topic').reset();
  document.getElementById('new-topic-name-container').style.display = 'none';
  document.getElementById('questions-container').style.display = 'none';
  document.getElementById('questions-empty-state').style.display = 'block';
  document.getElementById('questions-list-title').textContent = 'Preguntas Registradas';
}

// HELP MODALS
function showFormatHelp(e) {
  e.preventDefault();
  openModal('modal-format-help');
}

function openModal(id) {
  document.getElementById(id).classList.add('active');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('active');
}
</script>
