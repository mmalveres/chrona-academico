<?php
// visoes/obras/curva_s.php
/** @var array      $obra       */
/** @var array|null $dadosCurva */
include __DIR__ . '/../estruturas/cabecalho.php';

$idObra = (int)$obra['id'];

$semDados = empty($dadosCurva);

if (!$semDados) {
    $curva           = $dadosCurva['curva'];
    $totalContrato   = $dadosCurva['total_contrato'];
    $percPlanAtual   = $dadosCurva['perc_plan_atual'];
    $percRealAtual   = $dadosCurva['perc_real_atual'];
    $desvio          = $dadosCurva['desvio'];

    // Arrays para o Chart.js
    $labels      = array_column($curva, 'label');
    $planPct     = array_column($curva, 'plan_pct');
    $realPct     = array_map(fn($c) => $c['real_pct'], $curva);   // null para meses futuros
}
?>

<div class="container-fluid mt-3 mb-5">

    <!-- ── Cabeçalho ── -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="index.php?acao=listar_obras" class="text-decoration-none text-muted">Obras</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary">Curva S</li>
                </ol>
            </nav>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-graph-up-arrow me-2 text-primary"></i>
                Curva S — <?= htmlspecialchars($obra['nome']) ?>
            </h5>
            <small class="text-muted">Planejado × Realizado físico-financeiro</small>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?acao=cronograma&id_obra=<?= $idObra ?>" class="btn btn-outline-info btn-sm fw-bold">
                <i class="bi bi-calendar-week me-1"></i> Cronograma
            </a>
            <a href="index.php?acao=medicao_obra&id_obra=<?= $idObra ?>" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="bi bi-bar-chart-steps me-1"></i> Medição
            </a>
            <a href="index.php?acao=listar_obras" class="btn btn-light border btn-sm">Voltar</a>
        </div>
    </div>

    <?php if ($semDados): ?>
    <!-- ── Estado vazio ── -->
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-graph-up display-1 text-muted opacity-25"></i>
            <h5 class="mt-3 text-muted">Sem dados suficientes</h5>
            <p class="text-muted small mb-4">
                Para gerar a Curva S, a obra precisa ter um <strong>orçamento importado</strong>,
                <strong>datas definidas no cronograma</strong> e <strong>atividades lançadas no diário</strong>.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="index.php?acao=cronograma&id_obra=<?= $idObra ?>" class="btn btn-info fw-bold">
                    <i class="bi bi-calendar-week me-2"></i> Definir Cronograma
                </a>
                <a href="index.php?acao=ver_diario&id_obra=<?= $idObra ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-journal-text me-2"></i> Ver Diário
                </a>
            </div>
        </div>
    </div>

    <?php else: ?>

    <!-- ── Cards de resumo ── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold mb-1">Valor Contrato</h6>
                    <div class="fw-bold text-dark fs-6">R$ <?= number_format($totalContrato, 2, ',', '.') ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="text-primary small text-uppercase fw-bold mb-1">Planejado Atual</h6>
                    <div class="fw-bold text-dark fs-4"><?= number_format($percPlanAtual, 1, ',', '.') ?>%</div>
                    <div class="small text-muted">do contrato conforme cronograma</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-success small text-uppercase fw-bold mb-1">Realizado</h6>
                    <div class="fw-bold text-dark fs-4"><?= number_format($percRealAtual, 1, ',', '.') ?>%</div>
                    <div class="small text-muted">executado conforme diários</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <?php
            $corDesvio  = $desvio >= 0 ? 'success' : 'danger';
            $iconeDesv  = $desvio >= 0 ? 'bi-arrow-up-circle-fill' : 'bi-arrow-down-circle-fill';
            $labelDesv  = $desvio >= 0 ? 'Adiantado' : 'Atrasado';
            ?>
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-<?= $corDesvio ?>">
                <div class="card-body">
                    <h6 class="text-<?= $corDesvio ?> small text-uppercase fw-bold mb-1">Desvio</h6>
                    <div class="fw-bold text-<?= $corDesvio ?> fs-4">
                        <i class="bi <?= $iconeDesv ?> me-1 fs-6"></i>
                        <?= number_format(abs($desvio), 1, ',', '.') ?>%
                    </div>
                    <div class="small text-muted"><?= $labelDesv ?> em relação ao plano</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Gráfico Curva S ── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Curva S — % Acumulado
                </h6>
                <div class="d-flex gap-3 small">
                    <span><span class="badge bg-primary">&nbsp;</span> Planejado</span>
                    <span><span class="badge bg-success">&nbsp;</span> Realizado</span>
                </div>
            </div>
            <canvas id="graficoCurvaS" height="<?= count($curva) > 18 ? '80' : '100' ?>"></canvas>
        </div>
    </div>

    <!-- ── Tabela mensal ── -->
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light text-muted text-uppercase small">
                        <tr>
                            <th class="ps-4">Mês</th>
                            <th class="text-end">Plan. Mês (R$)</th>
                            <th class="text-end">Real. Mês (R$)</th>
                            <th class="text-end text-primary">Plan. Acum. %</th>
                            <th class="text-end text-success">Real. Acum. %</th>
                            <th class="pe-4">Desvio</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($curva as $linha):
                        $desvLinha  = $linha['real_pct'] !== null
                            ? round($linha['real_pct'] - $linha['plan_pct'], 1)
                            : null;
                        $corDev     = $desvLinha === null ? 'text-muted' : ($desvLinha >= 0 ? 'text-success' : 'text-danger');
                        $ehHoje     = $linha['mes'] === date('Y-m');
                    ?>
                    <tr class="<?= $ehHoje ? 'table-primary' : '' ?>">
                        <td class="ps-4 fw-bold <?= $ehHoje ? 'text-primary' : 'text-muted' ?>">
                            <?= $linha['label'] ?>
                            <?php if ($ehHoje): ?><span class="badge bg-primary ms-1 small">Hoje</span><?php endif; ?>
                        </td>
                        <td class="text-end">R$ <?= number_format($linha['plan_mes'], 2, ',', '.') ?></td>
                        <td class="text-end">
                            <?= $linha['real_mes'] !== null
                                ? 'R$ ' . number_format($linha['real_mes'], 2, ',', '.')
                                : '<span class="text-muted">–</span>' ?>
                        </td>
                        <td class="text-end fw-bold text-primary"><?= number_format($linha['plan_pct'], 1, ',', '.') ?>%</td>
                        <td class="text-end fw-bold text-success">
                            <?= $linha['real_pct'] !== null
                                ? number_format($linha['real_pct'], 1, ',', '.') . '%'
                                : '<span class="text-muted">–</span>' ?>
                        </td>
                        <td class="pe-4 <?= $corDev ?> fw-bold">
                            <?php if ($desvLinha !== null): ?>
                                <?= $desvLinha >= 0 ? '+' : '' ?><?= number_format($desvLinha, 1, ',', '.') ?>%
                            <?php else: ?>
                                <span class="text-muted">–</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<?php if (!$semDados): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const labels   = <?= json_encode($labels) ?>;
const planPct  = <?= json_encode($planPct) ?>;
const realPct  = <?= json_encode($realPct) ?>;

new Chart(document.getElementById('graficoCurvaS'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: 'Planejado (%)',
                data: planPct,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,.08)',
                borderWidth: 2.5,
                borderDash: [6, 3],
                pointRadius: 3,
                pointBackgroundColor: '#2563eb',
                fill: true,
                tension: 0.35,
            },
            {
                label: 'Realizado (%)',
                data: realPct,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,.08)',
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#16a34a',
                fill: true,
                tension: 0.35,
                spanGaps: false,
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y !== null ? ctx.parsed.y.toFixed(1) + '%' : '–'}`
                }
            }
        },
        scales: {
            y: {
                min: 0,
                max: 100,
                ticks: { callback: v => v + '%' },
                grid: { color: 'rgba(0,0,0,.05)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
