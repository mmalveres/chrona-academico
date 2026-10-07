<?php
// visoes/cronograma/imprimir.php

$idEmpresa     = $_SESSION['id_empresa'] ?? 0;
$dataImpressao = date('d/m/Y \à\s H:i');
$caminhoLogo   = __DIR__ . '/../../publico/assets/img/logo_empresa_' . $idEmpresa . '.png';
$urlLogo       = 'assets/img/logo_empresa_' . $idEmpresa . '.png?' . time();
$temLogo       = file_exists($caminhoLogo);

// ── 1. PERÍODO ────────────────────────────────────────────────────
$minData = null;
$maxData = null;
foreach ($itensCronograma as $item) {
    if (!empty($item['data_inicio']) && !empty($item['data_fim'])) {
        $ini = strtotime($item['data_inicio']);
        $fim = strtotime($item['data_fim']);
        if ($minData === null || $ini < $minData) $minData = $ini;
        if ($maxData === null || $fim > $maxData) $maxData = $fim;
    }
}
if ($minData === null) { $minData = time(); $maxData = strtotime('+6 months'); }

$startCursor = strtotime(date('Y-m-01', $minData));
$endCursor   = strtotime(date('Y-m-t',  $maxData));

// ── 2. COLUNAS DE MESES ───────────────────────────────────────────
$mesesHeader = [];
$nomesMeses  = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
$cursor      = $startCursor;
while ($cursor <= $endCursor) {
    $m = (int)date('m', $cursor);
    $mesesHeader[] = [
        'label' => $nomesMeses[$m - 1] . '/' . date('y', $cursor),
        'ini'   => $cursor,
        'fim'   => strtotime(date('Y-m-t', $cursor)),
    ];
    $cursor = strtotime('+1 month', $cursor);
}
$numMeses = count($mesesHeader);

// ── 3. CONFIGURAÇÃO ADAPTATIVA ────────────────────────────────────
// $fsVal: fonte dos valores nas células de mês (menor que o texto geral)
if      ($numMeses <= 6)  { $colW = '76px'; $fs = '9px';   $fsH = '8px';   $fsVal = '8.5px'; }
elseif  ($numMeses <= 12) { $colW = '63px'; $fs = '8.5px'; $fsH = '7.5px'; $fsVal = '7.5px'; }
elseif  ($numMeses <= 18) { $colW = '49px'; $fs = '8px';   $fsH = '7px';   $fsVal = '6.5px'; }
elseif  ($numMeses <= 24) { $colW = '41px'; $fs = '7.5px'; $fsH = '6.5px'; $fsVal = '5.5px'; }
else                      { $colW = '35px'; $fs = '7px';   $fsH = '6px';   $fsVal = '5px';   }

$rotacionarHeader = $numMeses > 10;

// ── 4. HELPERS ────────────────────────────────────────────────────
function ehEtapaRaiz(string $codigo, string $item_cod): bool {
    $ref = !empty($item_cod) ? $item_cod : $codigo;
    return substr_count(rtrim(trim($ref), '.'), '.') === 0;
}

function fmtR(float $v): string {
    return number_format($v, 2, ',', '.');
}

// ── 5. AGRUPAMENTO ────────────────────────────────────────────────
$relatorio      = [];
$indexEtapa     = -1;
$totalGeralObra = 0;
$totaisPorMes   = array_fill(0, $numMeses, 0);

foreach ($itensCronograma as $item) {
    if ($item['tipo'] === 'etapa') {
        if (ehEtapaRaiz($item['codigo_referencia'] ?? '', $item['itemizacao'] ?? '')) {
            $indexEtapa++;
            $label = trim(($item['itemizacao'] ?? '') ?: ($item['codigo_referencia'] ?? ''));
            $relatorio[$indexEtapa] = [
                'codigo'          => rtrim($label, '.'),
                'descricao'       => $item['descricao'],
                'total_etapa'     => 0,
                'valores_mensais' => array_fill(0, $numMeses, 0),
            ];
        }
        continue;
    }

    if ($indexEtapa < 0 || empty($item['data_inicio']) || empty($item['data_fim'])) continue;

    $valorItem  = (float)$item['preco_total'];
    $relatorio[$indexEtapa]['total_etapa'] += $valorItem;
    $totalGeralObra += $valorItem;

    $dtIni      = strtotime($item['data_inicio']);
    $dtFim      = strtotime($item['data_fim']);
    $diasTotais = max(1, ($dtFim - $dtIni) / 86400 + 1);
    $valPorDia  = $valorItem / $diasTotais;

    foreach ($mesesHeader as $idx => $mes) {
        $iniInt    = max($dtIni, $mes['ini']);
        $fimInt    = min($dtFim, $mes['fim']);
        if ($iniInt > $fimInt) continue;
        $diasNoMes = ($fimInt - $iniInt) / 86400 + 1;
        $valNoMes  = $diasNoMes * $valPorDia;
        $relatorio[$indexEtapa]['valores_mensais'][$idx] += $valNoMes;
        $totaisPorMes[$idx] += $valNoMes;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cronograma — <?= htmlspecialchars($obra['nome']) ?></title>
<style>
@page { size: landscape; margin: 10mm 9mm; }

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: <?= $fs ?>;
    color: #111827;
    background: #fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

@media print { .no-print { display: none !important; } }

/* ── Barra de ações (não imprime) ─────────────────── */
.no-print {
    position: fixed; top: 16px; right: 16px;
    display: flex; gap: 8px; z-index: 999;
}
.no-print button {
    padding: 8px 18px; border: none; border-radius: 5px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    letter-spacing: .2px;
}
.btn-print  { background: #2563eb; color: #fff; }
.btn-fechar { background: #e5e7eb; color: #374151; }

/* ── Cabeçalho ────────────────────────────────────── */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding-bottom: 6px;
    margin-bottom: 2px;
    border-bottom: 2px solid #2563eb;
}
.logo-area img     { max-height: 48px; max-width: 150px; object-fit: contain; display: block; }
.logo-area .nome   { font-size: 16px; font-weight: 900; color: #111827; letter-spacing: -0.5px; }

.titulo-area       { text-align: right; }
.titulo-area h1    { font-size: 13px; font-weight: 700; color: #111827; text-transform: uppercase; letter-spacing: .3px; }
.titulo-area small { font-size: 7.5px; color: #6b7280; display: block; margin-top: 2px; }

/* ── Linha de meta-dados ─────────────────────────── */
.meta-linha {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding: 5px 0 4px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 6px;
}
.meta-esq { display: flex; gap: 20px; }
.meta-item .rotulo {
    display: block; font-size: 6px; text-transform: uppercase;
    letter-spacing: .6px; color: #9ca3af; font-weight: 700; margin-bottom: 1px;
}
.meta-item .dado   { font-size: <?= $fs ?>; font-weight: 700; color: #111827; }
.meta-item .dado.azul { color: #2563eb; }
.meta-emissao      { font-size: 7px; color: #9ca3af; text-align: right; }

/* ── Tabela ──────────────────────────────────────── */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

th, td {
    padding: 3px 5px;
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: <?= $fs ?>;
    border-bottom: 1px solid #e5e7eb;
}

.col-item  { width: 32px;  text-align: center; border-right: 1px solid #e5e7eb; }
.col-desc  { width: <?= $numMeses > 20 ? '145px' : '180px' ?>; white-space: normal; word-break: break-word; }
.col-total { width: <?= $numMeses > 18 ? '68px' : '82px' ?>; text-align: right; border-left: 1px solid #e5e7eb; }
.col-mes   { width: <?= $colW ?>; text-align: center; border-left: 1px solid #f3f4f6; vertical-align: top; padding: 2px 2px 5px; position: relative; }

/* ── Cabeçalho da tabela ─────────────────────────── */
thead tr {
    background: #111827;
    border-bottom: 2px solid #111827;
}
thead th {
    color: #f9fafb;
    font-size: <?= $fsH ?>;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
    text-align: center;
    border-bottom: none;
    border-left-color: #1f2937;
}
thead th.col-desc { text-align: left; }
thead th.col-total { border-left-color: #374151; }

<?php if ($rotacionarHeader): ?>
thead th.col-mes {
    height: 52px;
    vertical-align: bottom;
    padding: 3px 2px;
}
thead th.col-mes span {
    display: inline-block;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    white-space: nowrap;
    line-height: 1;
}
<?php endif; ?>

/* ── Linhas de etapa ─────────────────────────────── */
.row-etapa td {
    background: #f0f6ff;
    font-weight: 700;
    border-bottom: 1px solid #dbeafe;
    border-top: 2px solid #dbeafe;
}
.row-etapa td.col-item {
    color: #1d4ed8;
    font-size: calc(<?= $fs ?> + 0.5px);
    border-right-color: #dbeafe;
}
.row-etapa td.col-desc { color: #1e293b; }
.row-etapa td.col-total {
    color: #1d4ed8;
    border-left-color: #dbeafe;
}
.row-etapa td.col-mes { border-left-color: #dbeafe; }

/* ── Células de valor dos meses ─────────────────── */
.cel-valor {
    display: block;
    font-size: <?= $fsVal ?>;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.3;
}
.cel-perc {
    display: block;
    font-size: <?= $fsH ?>;
    color: #6b7280;
    line-height: 1.2;
}
/* Barra Gantt contínua — estica de borda a borda da célula */
.cel-barra-gantt {
    position: absolute;
    bottom: 0;
    left: -1px;
    right: -1px;
    height: 3px;
    background: #2563eb;
}
.cel-vazio { color: #d1d5db; font-size: <?= $fsVal ?>; }

/* ── Rodapé da tabela ───────────────────────────── */
tfoot td {
    font-size: <?= $fsVal ?>;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border-left: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
}

/* Linhas numéricas — alternância limpa escuro/claro */
.foot-mensal td  { background: #1e293b; color: #f8fafc; font-weight: 700; border-color: #374151; }
.foot-pmensal td { background: #f1f5f9; color: #374151; }
.foot-acum td    { background: #1e293b; color: #f8fafc; font-weight: 700; border-color: #374151; }
.foot-pacum td   { background: #f1f5f9; color: #374151; border-bottom: none; }

tfoot td.rotulo-cell {
    text-align: right;
    padding-right: 8px;
    font-size: <?= $fs ?>;
    white-space: nowrap;
}

/* ── Assinaturas ────────────────────────────────── */
.assinaturas {
    display: flex;
    justify-content: space-around;
    margin-top: 26px;
    page-break-inside: avoid;
}
.assinatura {
    width: 34%;
    text-align: center;
    border-top: 1px solid #9ca3af;
    padding-top: 6px;
    margin-top: 50px;
    font-size: 8px;
    color: #4b5563;
}
.assinatura strong { display: block; font-size: 8.5px; color: #111827; margin-bottom: 2px; }
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print"  onclick="window.print()">Imprimir / PDF</button>
    <button class="btn-fechar" onclick="window.close()">Fechar</button>
</div>

<!-- ── Cabeçalho ─────────────────────────────────────── -->
<div class="page-header">
    <div class="logo-area">
        <?php if ($temLogo): ?>
            <img src="<?= $urlLogo ?>" alt="Logo">
        <?php else: ?>
            <div class="nome">CHRONA</div>
        <?php endif; ?>
    </div>
    <div class="titulo-area">
        <h1>Cronograma Físico-Financeiro</h1>
        <small>Planejamento e Controle de Obra</small>
    </div>
</div>

<!-- ── Meta-dados ─────────────────────────────────────── -->
<div class="meta-linha">
    <div class="meta-esq">
        <div class="meta-item">
            <span class="rotulo">Obra</span>
            <span class="dado"><?= htmlspecialchars($obra['nome']) ?></span>
        </div>
        <?php if (!empty($obra['cidade'])): ?>
        <div class="meta-item">
            <span class="rotulo">Local</span>
            <span class="dado"><?= htmlspecialchars($obra['cidade']) ?></span>
        </div>
        <?php endif; ?>
        <div class="meta-item">
            <span class="rotulo">Período</span>
            <span class="dado"><?= date('d/m/Y', $minData) ?> — <?= date('d/m/Y', $maxData) ?> (<?= $numMeses ?> meses)</span>
        </div>
        <div class="meta-item">
            <span class="rotulo">Valor Total</span>
            <span class="dado azul">R$ <?= fmtR($totalGeralObra) ?></span>
        </div>
    </div>
    <div class="meta-emissao">Emissão: <?= $dataImpressao ?></div>
</div>

<!-- ── Tabela ────────────────────────────────────────── -->
<table>
    <thead>
        <tr>
            <th class="col-item">Nº</th>
            <th class="col-desc">Discriminação</th>
            <th class="col-total">Total (R$)</th>
            <?php foreach ($mesesHeader as $mes): ?>
                <th class="col-mes">
                    <?= $rotacionarHeader ? '<span>' . $mes['label'] . '</span>' : $mes['label'] ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($relatorio as $linha):
        if ($linha['total_etapa'] <= 0) continue;
        $percEtapa = $totalGeralObra > 0 ? $linha['total_etapa'] / $totalGeralObra * 100 : 0;

        // Primeiro e último mês com valor — define o span da barra Gantt
        $primeiroMes = -1;
        $ultimoMes   = -1;
        foreach ($linha['valores_mensais'] as $idx => $v) {
            if ($v > 0.01) {
                if ($primeiroMes === -1) $primeiroMes = $idx;
                $ultimoMes = $idx;
            }
        }
    ?>
        <tr class="row-etapa">
            <td class="col-item"><?= htmlspecialchars($linha['codigo']) ?></td>
            <td class="col-desc"><?= htmlspecialchars($linha['descricao']) ?></td>
            <td class="col-total">
                <?= fmtR($linha['total_etapa']) ?>
                <span style="display:block;font-weight:400;font-size:<?= $fsH ?>;color:#6b7280">
                    <?= number_format($percEtapa, 1, ',', '.') ?>%
                </span>
            </td>
            <?php foreach ($linha['valores_mensais'] as $idx => $valor):
                $ativo       = $valor > 0.01;
                $noIntervalo = $primeiroMes >= 0 && $idx >= $primeiroMes && $idx <= $ultimoMes;
                $isFirst     = $idx === $primeiroMes;
                $isLast      = $idx === $ultimoMes;
                $percMes     = $ativo && $linha['total_etapa'] > 0 ? $valor / $linha['total_etapa'] * 100 : 0;
                // Arredondamento nas pontas da barra
                $br = $isFirst && $isLast ? '2px' : ($isFirst ? '2px 0 0 2px' : ($isLast ? '0 2px 2px 0' : '0'));
            ?>
                <td class="col-mes">
                    <?php if ($ativo): ?>
                        <span class="cel-valor"><?= fmtR($valor) ?></span>
                        <span class="cel-perc"><?= number_format($percMes, 1, ',', '.') ?>%</span>
                    <?php endif; ?>
                    <?php if ($noIntervalo): ?>
                        <div class="cel-barra-gantt" style="border-radius:<?= $br ?>"></div>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>

    <tfoot>
        <tr class="foot-mensal">
            <td colspan="2" class="rotulo-cell">Previsto Mensal (R$)</td>
            <td><?= fmtR($totalGeralObra) ?></td>
            <?php foreach ($totaisPorMes as $t): ?>
                <td><?= $t > 0.01 ? fmtR($t) : '–' ?></td>
            <?php endforeach; ?>
        </tr>
        <tr class="foot-pmensal">
            <td colspan="2" class="rotulo-cell">% Mensal</td>
            <td>100%</td>
            <?php foreach ($totaisPorMes as $t):
                $p = $totalGeralObra > 0 ? $t / $totalGeralObra * 100 : 0;
            ?>
                <td><?= $t > 0.01 ? number_format($p, 1, ',', '.') . '%' : '–' ?></td>
            <?php endforeach; ?>
        </tr>
        <tr class="foot-acum">
            <td colspan="2" class="rotulo-cell">Acumulado (R$)</td>
            <td></td>
            <?php $acum = 0; foreach ($totaisPorMes as $t): $acum += $t; ?>
                <td><?= $acum > 0.01 ? fmtR($acum) : '–' ?></td>
            <?php endforeach; ?>
        </tr>
        <tr class="foot-pacum">
            <td colspan="2" class="rotulo-cell">% Acumulado</td>
            <td></td>
            <?php $acum = 0; foreach ($totaisPorMes as $t): $acum += $t;
                $pa = $totalGeralObra > 0 ? $acum / $totalGeralObra * 100 : 0;
            ?>
                <td><?= $acum > 0.01 ? number_format($pa, 1, ',', '.') . '%' : '–' ?></td>
            <?php endforeach; ?>
        </tr>
    </tfoot>
</table>

<!-- ── Assinaturas ───────────────────────────────────── -->
<div class="assinaturas">
    <div class="assinatura">
        <strong><?= htmlspecialchars($obra['nome_responsavel_tecnico'] ?? 'Responsável Técnico') ?></strong>
        Responsável Técnico / Engenheiro
    </div>
    <div class="assinatura">
        <strong>Contratante</strong>
        De Acordo
    </div>
</div>

</body>
</html>
