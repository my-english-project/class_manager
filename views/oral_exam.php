<?php
/**
 * ClassHub — Oral Exam View (OE)
 * 
 * Oral exam grade capture by parcial (RF-26/27).
 */

$alumnos = $alumnos ?? [];
$grades = $grades ?? [];
$grupoActivo = $grupoActivo ?? null;
?>

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
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
  <p class="page-description">Captura de calificaciones — Aplicación / Hacer (60%)</p>
</div>

<?php if (!$grupoActivo || count($alumnos) === 0): ?>
  <div class="card">
    <div class="empty-state">
      <h3><?= !$grupoActivo ? 'Sin grupo activo' : 'Sin alumnos' ?></h3>
    </div>
  </div>
<?php else: ?>


  <div class="table-wrapper">
    <table class="data-table" id="table-oe">
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
                oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'oe')">
            </td>
            <td style="text-align:center;">
              <input type="number" class="grade-input <?= $p2 !== '' && round((float) $p2, 2) < 7 ? 'fail' : '' ?>"
                data-alumno="<?= $alumno['id_alumno'] ?>" data-parcial="2"
                value="<?= $p2 !== '' && $p2 !== null ? number_format((float) $p2, 2) : '' ?>" min="0" max="10" step="0.01"
                oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'oe')">
            </td>
            <td style="text-align:center;">
              <input type="number" class="grade-input <?= $p3 !== '' && round((float) $p3, 2) < 7 ? 'fail' : '' ?>"
                data-alumno="<?= $alumno['id_alumno'] ?>" data-parcial="3"
                value="<?= $p3 !== '' && $p3 !== null ? number_format((float) $p3, 2) : '' ?>" min="0" max="10" step="0.01"
                oninput="validateGrade(this)" onchange="autoSaveSingleGrade(this, 'oe')">
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