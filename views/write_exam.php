<?php
/**
 * ClassHub — Write Exam View (WE)
 * 
 * Written exam grade capture by parcial (RF-23/24/25).
 */

$alumnos = $alumnos ?? [];
$grades = $grades ?? [];
$grupoActivo = $grupoActivo ?? null;
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <polyline points="14 2 14 8 20 8" />
        <line x1="16" y1="13" x2="8" y2="13" />
        <line x1="16" y1="17" x2="8" y2="17" />
      </svg>
    </span>
    Examen Escrito
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
  <p class="page-description">Captura de calificaciones — Saber (30%)</p>
</div>

<?php if (!$grupoActivo || count($alumnos) === 0): ?>
  <div class="card">
    <div class="empty-state">
      <h3><?= !$grupoActivo ? 'Sin grupo activo' : 'Sin alumnos' ?></h3>
      <p><?= !$grupoActivo ? 'Selecciona un grupo.' : 'Agrega alumnos al grupo.' ?></p>
    </div>
  </div>
<?php else: ?>


  <div class="table-wrapper">
    <table class="data-table" id="table-we">
      <thead>
        <tr>
          <th class="col-num" width="40">#</th>
          <th class="col-matricula" width="120">Matrícula</th>
          <th class="col-name">Nombre</th>
          <th style="text-align:center;"><span class="tab-text-full">Parcial 1</span><span class="tab-text-short">P1</span></th>
          <th style="text-align:center;"><span class="tab-text-full">Parcial 2</span><span class="tab-text-short">P2</span></th>
          <th style="text-align:center;"><span class="tab-text-full">Parcial 3</span><span class="tab-text-short">P3</span></th>
          <th style="text-align:center;">Promedio</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($alumnos as $i => $alumno): ?>
          <?php
          $p1 = $grades[$alumno['id_alumno']][1] ?? '';
          $p2 = $grades[$alumno['id_alumno']][2] ?? '';
          $p3 = $grades[$alumno['id_alumno']][3] ?? '';
          $vals = array_filter([$p1, $p2, $p3], fn($v) => $v !== '' && $v !== null);
          $avg = count($vals) > 0 ? array_sum($vals) / count($vals) : null;
          ?>
          <tr>
            <td class="col-num"><?= $i + 1 ?></td>
            <td class="col-matricula"><?= htmlspecialchars($alumno['matricula']) ?></td>
            <td class="col-name"><?= htmlspecialchars($alumno['nombre_completo']) ?></td>
            <td style="text-align:center;">
              <input type="number" class="grade-input <?= $p1 !== '' && round((float) $p1, 2) < 7 ? 'fail' : '' ?>"
                data-alumno="<?= $alumno['id_alumno'] ?>" data-parcial="1"
                value="<?= $p1 !== '' && $p1 !== null ? number_format((float) $p1, 2) : '' ?>" min="0" max="10" step="0.01"
                oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'we')">
            </td>
            <td style="text-align:center;">
              <input type="number" class="grade-input <?= $p2 !== '' && round((float) $p2, 2) < 7 ? 'fail' : '' ?>"
                data-alumno="<?= $alumno['id_alumno'] ?>" data-parcial="2"
                value="<?= $p2 !== '' && $p2 !== null ? number_format((float) $p2, 2) : '' ?>" min="0" max="10" step="0.01"
                oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'we')">
            </td>
            <td style="text-align:center;">
              <input type="number" class="grade-input <?= $p3 !== '' && round((float) $p3, 2) < 7 ? 'fail' : '' ?>"
                data-alumno="<?= $alumno['id_alumno'] ?>" data-parcial="3"
                value="<?= $p3 !== '' && $p3 !== null ? number_format((float) $p3, 2) : '' ?>" min="0" max="10" step="0.01"
                oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'we')">
            </td>
            <td style="text-align:center;"
              class="font-bold <?= $avg !== null && round((float) $avg, 2) < 7 ? 'grade-fail' : '' ?>">
              <?= $avg !== null ? number_format($avg, 2) : '—' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>