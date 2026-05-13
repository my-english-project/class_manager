<?php
/**
 * ClassHub — Exam Summary View (EX)
 * 
 * Weighted grade summary per parcial (RF-34/35/36).
 * Shows AT, WE, OE, HW, PF subtotals with proper weights.
 */

$alumnos = $alumnos ?? [];
$examSummary = $examSummary ?? [];
$grupoActivo = $grupoActivo ?? null;
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="20" x2="18" y2="10" />
        <line x1="12" y1="20" x2="12" y2="4" />
        <line x1="6" y1="20" x2="6" y2="14" />
      </svg>
    </span>
    Resumen de Calificaciones
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
  <p class="page-description">Vista ponderada por competencias: Ser (10%) · Saber (30%) · Hacer (60%)</p>
</div>

<?php if (!$grupoActivo || count($alumnos) === 0): ?>
  <div class="card">
    <div class="empty-state">
      <h3><?= !$grupoActivo ? 'Sin grupo activo' : 'Sin alumnos' ?></h3>
    </div>
  </div>
<?php else: ?>

  <div class="tabs"
    style="display: flex; justify-content: flex-end; gap: var(--space-2); margin-bottom: var(--space-4); border-bottom: 1px solid var(--border-color); padding-bottom: var(--space-2);">
    <button class="btn btn-primary tab-btn" onclick="showParcial(1)" id="tab-1"><span class="tab-text-full">Parcial 1</span><span class="tab-text-short">P1</span></button>
    <button class="btn btn-outline tab-btn" onclick="showParcial(2)" id="tab-2"><span class="tab-text-full">Parcial 2</span><span class="tab-text-short">P2</span></button>
    <button class="btn btn-outline tab-btn" onclick="showParcial(3)" id="tab-3"><span class="tab-text-full">Parcial 3</span><span class="tab-text-short">P3</span></button>
  </div>

  <?php for ($p = 1; $p <= 3; $p++): ?>
    <div class="table-wrapper parcial-table" id="table-p<?= $p ?>" style="<?= $p === 1 ? '' : 'display: none;' ?>">
      <table class="data-table">
        <thead>
          <tr>
            <th class="col-num" rowspan="2" width="40">#</th>
            <th rowspan="2" width="100">Mat.</th>
            <th class="col-name" rowspan="2">Nombre</th>
            <th colspan="6" style="text-align:center; border-bottom: 2px solid rgba(255,255,255,0.2);">Parcial <?= $p ?>
            </th>
          </tr>
          <tr>
            <th style="text-align:center; font-size:9px;">AT<br><span style="opacity:0.7;">10%</span></th>
            <th style="text-align:center; font-size:9px;">WE<br><span style="opacity:0.7;">30%</span></th>
            <th style="text-align:center; font-size:9px;">OE<br><span style="opacity:0.7;">36%</span></th>
            <th style="text-align:center; font-size:9px;">PF<br><span style="opacity:0.7;">12%</span></th>
            <th style="text-align:center; font-size:9px;">HW<br><span style="opacity:0.7;">12%</span></th>
            <th style="text-align:center; font-size:9px; background: rgba(255,255,255,0.05);">P<?= $p ?> Final</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($examSummary as $i => $s): ?>
            <tr>
              <td class="col-num"><?= $i + 1 ?></td>
              <td style="font-size: var(--text-xs);"><?= htmlspecialchars($s['matricula']) ?></td>
              <td class="col-name"><?= htmlspecialchars($s['nombre_completo']) ?></td>

              <td style="text-align:center; font-size: var(--text-xs);"
                class="<?= isset($s["at_p{$p}"]) && round((float) $s["at_p{$p}"], 1) < 7 ? 'grade-fail' : '' ?>">
                <?= $s["at_p{$p}"] !== null ? number_format($s["at_p{$p}"], 1) : '—' ?>
              </td>
              <td style="text-align:center; font-size: var(--text-xs);"
                class="<?= isset($s["we_p{$p}"]) && round((float) $s["we_p{$p}"], 1) < 7 ? 'grade-fail' : '' ?>">
                <?= $s["we_p{$p}"] !== null ? number_format($s["we_p{$p}"], 1) : '—' ?>
              </td>
              <td style="text-align:center; font-size: var(--text-xs);"
                class="<?= isset($s["oe_p{$p}"]) && round((float) $s["oe_p{$p}"], 1) < 7 ? 'grade-fail' : '' ?>">
                <?= $s["oe_p{$p}"] !== null ? number_format($s["oe_p{$p}"], 1) : '—' ?>
              </td>
              <td style="text-align:center; font-size: var(--text-xs);"
                class="<?= isset($s["pf_p{$p}"]) && round((float) $s["pf_p{$p}"], 1) < 7 ? 'grade-fail' : '' ?>">
                <?= $s["pf_p{$p}"] !== null ? number_format($s["pf_p{$p}"], 1) : '—' ?>
              </td>
              <td style="text-align:center; font-size: var(--text-xs);"
                class="<?= isset($s["hw_p{$p}"]) && round((float) $s["hw_p{$p}"], 1) < 7 ? 'grade-fail' : '' ?>">
                <?= $s["hw_p{$p}"] !== null ? number_format($s["hw_p{$p}"], 1) : '—' ?>
              </td>
              <td style="text-align:center; background: rgba(255,255,255,0.03); font-weight:600;"
                class="<?= isset($s["parcial_p{$p}"]) && round((float) $s["parcial_p{$p}"], 1) < 7 ? 'grade-fail' : '' ?>">
                <?= $s["parcial_p{$p}"] !== null ? number_format($s["parcial_p{$p}"], 1) : '—' ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endfor; ?>

  <script>
    function showParcial(p) {
      document.querySelectorAll('.parcial-table').forEach(el => el.style.display = 'none');
      document.getElementById('table-p' + p).style.display = 'block';

      document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('btn-primary');
        el.classList.add('btn-outline');
      });

      let targetTab = document.getElementById('tab-' + p);
      targetTab.classList.remove('btn-outline');
      targetTab.classList.add('btn-primary');
    }
  </script>
<?php endif; ?>