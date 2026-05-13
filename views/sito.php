<?php
/**
 * ClassHub — SITO View (ST)
 * 
 * Institutional format: Ser / Saber / Hacer / SITO grade per Parcial (RF-37/38).
 * Printable and optionally exportable to CSV.
 */

$alumnos = $alumnos ?? [];
$examSummary = $examSummary ?? [];
$grupoActivo = $grupoActivo ?? null;
?>

<div class="page-header">
  <h1 class="page-title">
    <span class="page-title-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path
          d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
      </svg>
    </span>
    SITO — Calificaciones Finales
    <?= $grupoActivo ? " <span style='color: var(--uts-green); margin-left: var(--space-2);'>" . htmlspecialchars($grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo']) . "</span>" : "" ?>
  </h1>
  <p class="page-description">Formato institucional por competencias</p>
</div>

<?php if (!$grupoActivo || count($examSummary) === 0): ?>
  <div class="card">
    <div class="empty-state">
      <h3><?= !$grupoActivo ? 'Sin grupo activo' : 'Sin datos' ?></h3>
    </div>
  </div>
<?php else: ?>

  <div class="tabs"
    style="display: flex; justify-content: flex-end; align-items: center; gap: var(--space-2); margin-bottom: var(--space-4); border-bottom: 1px solid var(--border-color); padding-bottom: var(--space-2);">
    <div style="display: flex; gap: var(--space-2); margin-right: auto;">
      <button class="btn btn-outline btn-sm" onclick="window.print()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
          <polyline points="6 9 6 2 18 2 18 9" />
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
          <rect x="6" y="14" width="12" height="8" />
        </svg>
        Imprimir
      </button>
      <button class="btn btn-outline btn-sm" onclick="exportCSV()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="7 10 12 15 17 10" />
          <line x1="12" y1="15" x2="12" y2="3" />
        </svg>
        CSV
      </button>
    </div>
    <button class="btn btn-primary tab-btn" onclick="showParcial(1)" id="tab-1"><span class="tab-text-full">Parcial 1</span><span class="tab-text-short">P1</span></button>
    <button class="btn btn-outline tab-btn" onclick="showParcial(2)" id="tab-2"><span class="tab-text-full">Parcial 2</span><span class="tab-text-short">P2</span></button>
    <button class="btn btn-outline tab-btn" onclick="showParcial(3)" id="tab-3"><span class="tab-text-full">Parcial 3</span><span class="tab-text-short">P3</span></button>
  </div>

  <?php for ($p = 1; $p <= 3; $p++): ?>
    <div class="table-wrapper parcial-table" id="table-p<?= $p ?>" style="<?= $p === 1 ? '' : 'display: none;' ?>">
      <table class="data-table" id="table-sito-<?= $p ?>">
        <thead>
          <tr>
            <th class="col-num" rowspan="2" width="40">#</th>
            <th class="col-name" rowspan="2">Nombre</th>
            <th colspan="6" style="text-align:center; border-bottom: 2px solid rgba(255,255,255,0.2);">SITO Parcial
              <?= $p ?>
            </th>
          </tr>
          <tr>
            <th style="text-align:center; font-size:10px;">Calif.<br>Ser</th>
            <th style="text-align:center; font-size:10px;">Calif.<br>Saber</th>
            <th style="text-align:center; font-size:10px;">Calif.<br>Hacer</th>
            <th style="text-align:center; font-size:10px; background: rgba(58,155,92,0.3);">Calif.<br>SITO</th>
            <th style="text-align:center; font-size:10px;">Estado</th>
            <th style="text-align:center; font-size:10px;">Final</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($examSummary as $i => $s):
            $at = $s["at_p{$p}"] ?? null;
            $we = $s["we_p{$p}"] ?? null;
            $oe = $s["oe_p{$p}"] ?? null;
            $pf = $s["pf_p{$p}"] ?? null;
            $hw = $s["hw_p{$p}"] ?? null;

            $sitoSer = $at;
            $sitoSaber = $we;

            $sitoHacer = null;
            if ($oe !== null || $pf !== null || $hw !== null) {
              $sitoHacer = ($oe ?? 0) * 0.60 + ($pf ?? 0) * 0.20 + ($hw ?? 0) * 0.20;
            }

            $sitoGrade = $s["parcial_p{$p}"] ?? null;
            $sitoGradeRound = $sitoGrade !== null ? round((float) $sitoGrade, 1) : null;
            $aprobado = $sitoGradeRound !== null && $sitoGradeRound >= 7;
            ?>
            <tr>
              <td class="col-num"><?= $i + 1 ?></td>
              <td class="col-name"><?= htmlspecialchars($s['nombre_completo']) ?></td>
              <td style="text-align:center;"><?= $sitoSer !== null ? number_format($sitoSer, 1) : '—' ?></td>
              <td style="text-align:center;"
                class="<?= $sitoSaber !== null && round((float) $sitoSaber, 1) < 7 ? 'grade-fail' : '' ?>">
                <?= $sitoSaber !== null ? number_format($sitoSaber, 1) : '—' ?>
              </td>
              <td style="text-align:center;"
                class="<?= $sitoHacer !== null && round((float) $sitoHacer, 1) < 7 ? 'grade-fail' : '' ?>">
                <?= $sitoHacer !== null ? number_format($sitoHacer, 1) : '—' ?>
              </td>
              <td
                style="text-align:center; font-weight:700; <?= $sitoGradeRound !== null && $sitoGradeRound >= 7 ? 'background:rgba(39,174,96,0.1); color:var(--uts-green-dark);' : ($sitoGradeRound !== null ? 'background:rgba(235,87,87,0.1); color:var(--text-grade-fail);' : '') ?>">
                <?= $sitoGrade !== null ? number_format($sitoGrade, 1) : '—' ?>
              </td>
              <td
                style="text-align:center; font-weight:600; color: <?= $aprobado ? 'var(--uts-green)' : 'var(--color-danger)' ?>;">
                <?= $sitoGrade !== null ? ($aprobado ? 'VERDADERO' : 'FALSO') : '—' ?>
              </td>
              <td style="text-align:center;"
                class="font-bold <?= $sitoGradeRound !== null && $sitoGradeRound < 7 ? 'grade-fail' : '' ?>">
                <?= $sitoGrade !== null ? number_format($sitoGrade, 1) : '—' ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endfor; ?>

  <script>
    let currentParcial = 1;

    function showParcial(p) {
      currentParcial = p;
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

    function exportCSV() {
      const table = document.getElementById('table-sito-' + currentParcial);
      const rows = table.querySelectorAll('tr');
      let csv = [];

      rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];
        cells.forEach(cell => {
          let text = cell.textContent.trim().replace(/"/g, '""');
          rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
      });

      const blob = new Blob(['\ufeff' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'SITO_P' + currentParcial + '_<?= $grupoActivo ? $grupoActivo['siglas'] . $grupoActivo['cuatrimestre'] . $grupoActivo['grupo'] . '_' . $grupoActivo['ciclo'] : 'export' ?>.csv';
      link.click();
      URL.revokeObjectURL(url);
      showToast('CSV descargado correctamente', 'success');
    }
  </script>
<?php endif; ?>