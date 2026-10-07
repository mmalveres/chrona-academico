<?php
// visoes/financeiro/listar.php
/** @var array $obra               */
/** @var array $movimentacoes      */
/** @var array $resumo             */
/** @var array $dadosMensais       */
/** @var array $alertasCronograma  */
/** @var array $itensOrcamento     */
include __DIR__ . '/../estruturas/cabecalho.php';

$nomeObra    = $obra['nome'] ?? 'Financeiro';
$idObra      = (int)($obra['id'] ?? 0);

// Filtros ativos vindos da URL
$fInicio  = $_GET['periodo_inicio'] ?? '';
$fFim     = $_GET['periodo_fim']    ?? '';
$fTipo    = $_GET['tipo']           ?? '';
$fStatus  = $_GET['status']         ?? '';
$temFiltro = $fInicio || $fFim || $fTipo || $fStatus;

// Prepara dados para o gráfico (últimos 12 meses)
$nomesMeses = ['01'=>'Jan','02'=>'Fev','03'=>'Mar','04'=>'Abr','05'=>'Mai','06'=>'Jun',
               '07'=>'Jul','08'=>'Ago','09'=>'Set','10'=>'Out','11'=>'Nov','12'=>'Dez'];
$labelsGrafico  = [];
$grafReceitas   = [];
$grafDespesas   = [];
foreach ($dadosMensais as $d) {
    [$ano, $mes]   = explode('-', $d['mes']);
    $labelsGrafico[] = ($nomesMeses[$mes] ?? $mes) . '/' . substr($ano, 2);
    $grafReceitas[]  = (float)$d['receitas'];
    $grafDespesas[]  = (float)$d['despesas'];
}
?>
<style>
.mobile-dashboard-card {
    background:#fff; border-radius:16px; padding:12px; height:100%;
    box-shadow:0 2px 6px rgba(0,0,0,.04); position:relative; overflow:hidden;
    display:flex; flex-direction:column; justify-content:center;
}
.mobile-dashboard-card h6 { font-size:.65rem; font-weight:700; text-transform:uppercase; margin-bottom:4px; letter-spacing:.5px; }
.mobile-dashboard-card h3 { font-size:1.1rem; font-weight:800; margin-bottom:0; }
.bg-icon-overlay { position:absolute; right:-10px; bottom:-10px; font-size:3rem; opacity:.1; transform:rotate(-15deg); }
.mobile-transaction-item {
    background:#fff; border-radius:12px; padding:12px 15px; margin-bottom:10px;
    box-shadow:0 1px 3px rgba(0,0,0,.03); border-left:4px solid transparent;
    display:flex; align-items:center; justify-content:space-between;
}
.fab-add-finance {
    position:fixed; bottom:90px; right:20px; width:56px; height:56px;
    background:#198754; color:#fff; border-radius:50%; display:flex;
    align-items:center; justify-content:center;
    box-shadow:0 4px 12px rgba(25,135,84,.4); font-size:24px; z-index:1000; border:none;
}
.card-alerta { border-left: 4px solid #fd7e14; }
</style>

<div class="container-fluid mt-3 mb-5">

    <!-- ── Cabeçalho ── -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="overflow-hidden">
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="index.php?acao=listar_obras" class="text-decoration-none text-muted">Obras</a></li>
                    <li class="breadcrumb-item active fw-bold text-success">Financeiro</li>
                </ol>
            </nav>
            <h5 class="fw-bold mb-0 text-dark text-truncate">
                <i class="bi bi-wallet2 me-2 text-success"></i><?= htmlspecialchars($nomeObra) ?>
            </h5>
        </div>
        <div class="d-none d-md-flex gap-2">
            <button class="btn btn-success shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalLancamento">
                <i class="bi bi-plus-lg me-1"></i> Novo
            </button>
            <a href="index.php?acao=listar_obras" class="btn btn-light border shadow-sm">Voltar</a>
        </div>
    </div>

    <!-- ── Alertas de mensagem ── -->
    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = ['sucesso'=>['success','check-circle-fill','Lançamento salvo!'],
                       'editado'=>['success','check-circle-fill','Lançamento atualizado!'],
                       'excluido'=>['warning','trash-fill','Lançamento excluído.'],
                       'erro'=>['danger','exclamation-triangle-fill','Erro ao salvar.']];
              $m = $msgs[$_GET['msg']] ?? null; ?>
        <?php if ($m): ?>
        <div class="alert alert-<?= $m[0] ?> border-0 shadow-sm py-2 mb-3">
            <i class="bi bi-<?= $m[1] ?> me-2"></i><?= $m[2] ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ── Filtro por período ── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2 px-3">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="acao"    value="financeiro">
                <input type="hidden" name="id_obra" value="<?= $idObra ?>">
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">De</label>
                    <input type="date" name="periodo_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($fInicio) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Até</label>
                    <input type="date" name="periodo_fim" class="form-control form-control-sm" value="<?= htmlspecialchars($fFim) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Tipo</label>
                    <select name="tipo" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="receita"  <?= $fTipo==='receita'  ? 'selected':'' ?>>Receita</option>
                        <option value="despesa"  <?= $fTipo==='despesa'  ? 'selected':'' ?>>Despesa</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="pago"     <?= $fStatus==='pago'     ? 'selected':'' ?>>Pago</option>
                        <option value="pendente" <?= $fStatus==='pendente' ? 'selected':'' ?>>Pendente</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                        <i class="bi bi-funnel-fill me-1"></i> Filtrar
                    </button>
                    <?php if ($temFiltro): ?>
                    <a href="index.php?acao=financeiro&id_obra=<?= $idObra ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg me-1"></i> Limpar
                    </a>
                    <?php endif; ?>
                    <?php if ($temFiltro): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle align-self-center ms-1 small">
                        Filtro ativo
                    </span>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Cards resumo desktop ── -->
    <div class="row g-3 mb-4 d-none d-md-flex">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-5 border-success">
                <div class="card-body">
                    <h6 class="text-uppercase text-success small fw-bold mb-1">Receitas</h6>
                    <h3 class="fw-bold text-dark mb-0">R$ <?= number_format($resumo['receitas'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-5 border-danger">
                <div class="card-body">
                    <h6 class="text-uppercase text-danger small fw-bold mb-1">Despesas</h6>
                    <h3 class="fw-bold text-dark mb-0">R$ <?= number_format($resumo['despesas'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-5 border-warning">
                <div class="card-body">
                    <h6 class="text-uppercase text-warning small fw-bold mb-1">Pendente</h6>
                    <h3 class="fw-bold text-dark mb-0">R$ <?= number_format($resumo['pendente'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 <?= $resumo['saldo'] >= 0 ? 'bg-success' : 'bg-danger' ?> text-white">
                <div class="card-body">
                    <h6 class="text-uppercase small fw-bold opacity-75">Saldo</h6>
                    <h3 class="fw-bold mb-0">R$ <?= number_format($resumo['saldo'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Cards resumo mobile ── -->
    <div class="row g-2 mb-4 d-md-none">
        <div class="col-12">
            <div class="mobile-dashboard-card <?= $resumo['saldo'] >= 0 ? 'bg-success' : 'bg-danger' ?> text-white">
                <i class="bi bi-wallet2 text-white opacity-50 bg-icon-overlay"></i>
                <h6 class="opacity-75">Saldo Disponível</h6>
                <h3 class="display-6 fw-bold">R$ <?= number_format($resumo['saldo'], 2, ',', '.') ?></h3>
            </div>
        </div>
        <div class="col-6">
            <div class="mobile-dashboard-card border border-success border-opacity-25">
                <i class="bi bi-arrow-up-circle-fill text-success"></i>
                <h6 class="text-success">Entradas</h6>
                <h3 class="text-dark">R$ <?= number_format($resumo['receitas'], 2, ',', '.') ?></h3>
            </div>
        </div>
        <div class="col-6">
            <div class="mobile-dashboard-card border border-danger border-opacity-25">
                <i class="bi bi-arrow-down-circle-fill text-danger"></i>
                <h6 class="text-danger">Saídas</h6>
                <h3 class="text-dark">R$ <?= number_format($resumo['despesas'], 2, ',', '.') ?></h3>
            </div>
        </div>
        <div class="col-12">
            <div class="mobile-dashboard-card bg-warning bg-opacity-10 border border-warning border-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-warning-emphasis mb-0">Pendente</h6>
                        <h3 class="text-dark fs-5 mt-1">R$ <?= number_format($resumo['pendente'], 2, ',', '.') ?></h3>
                    </div>
                    <i class="bi bi-hourglass-split text-warning fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Alertas: etapas sem faturamento ── -->
    <?php if (!empty($alertasCronograma)): ?>
    <div class="card border-0 shadow-sm mb-4 card-alerta d-none d-md-block">
        <div class="card-body py-3 px-4">
            <h6 class="fw-bold text-warning-emphasis mb-3">
                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                Etapas concluídas sem receita lançada
            </h6>
            <div class="row g-2">
                <?php foreach ($alertasCronograma as $alerta): ?>
                <div class="col-md-6">
                    <div class="d-flex align-items-start gap-3 p-2 rounded bg-warning bg-opacity-10">
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark small">
                                <?= htmlspecialchars(trim($alerta['codigo'], '.')) ?>
                                — <?= htmlspecialchars($alerta['descricao']) ?>
                            </div>
                            <div class="small text-muted">
                                <?= date('d/m/Y', strtotime($alerta['data_inicio'])) ?>
                                até <?= date('d/m/Y', strtotime($alerta['data_fim'])) ?>
                            </div>
                        </div>
                        <span class="badge bg-warning text-dark flex-shrink-0">
                            <?= $alerta['dias_atraso'] ?>d atraso
                        </span>
                        <button class="btn btn-sm btn-success flex-shrink-0"
                                data-bs-toggle="modal" data-bs-target="#modalLancamento"
                                title="Lançar receita">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Gráfico mensal ── -->
    <?php if (!empty($dadosMensais)): ?>
    <div class="card border-0 shadow-sm mb-4 d-none d-md-block">
        <div class="card-body">
            <h6 class="fw-bold text-muted text-uppercase small mb-3">
                <i class="bi bi-bar-chart-fill me-2 text-primary"></i>Evolução Mensal (últimos 12 meses)
            </h6>
            <canvas id="graficoMensal" height="80"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Tabela desktop ── -->
    <div class="card shadow-sm border-0 d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary text-uppercase small">
                        <tr>
                            <th class="ps-4">Data</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th>Valor</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($movimentacoes)): foreach ($movimentacoes as $mov):
                        $isDespesa  = $mov['tipo'] === 'despesa';
                        $corValor   = $isDespesa ? 'text-danger' : 'text-success';
                        $sinal      = $isDespesa ? '−' : '+';
                        $statusCls  = $mov['status'] === 'pago' ? 'bg-success' : 'bg-warning text-dark';
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold text-muted"><?= date('d/m/Y', strtotime($mov['data_movimentacao'])) ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($mov['descricao']) ?></div>
                            <?php if (!empty($mov['codigo_referencia'])): ?>
                                <span class="badge bg-light text-secondary border small mt-1">[<?= $mov['codigo_referencia'] ?>]</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($mov['categoria']) ?></span></td>
                        <td class="fw-bold <?= $corValor ?> fs-6"><?= $sinal ?> R$ <?= number_format($mov['valor'], 2, ',', '.') ?></td>
                        <td class="text-center"><span class="badge <?= $statusCls ?> rounded-pill"><?= $mov['status']==='pago' ? 'Pago' : 'Pendente' ?></span></td>
                        <td class="text-end pe-4">
                            <?php if ($mov['caminho_arquivo']): ?>
                                <a href="uploads/financeiro/<?= $mov['caminho_arquivo'] ?>" target="_blank"
                                   class="btn btn-sm btn-light text-primary rounded-circle" title="Ver comprovante">
                                    <i class="bi bi-paperclip"></i>
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-light text-primary rounded-circle btn-editar"
                                    title="Editar"
                                    data-id="<?= $mov['id'] ?>"
                                    data-tipo="<?= $mov['tipo'] ?>"
                                    data-descricao="<?= htmlspecialchars($mov['descricao'], ENT_QUOTES) ?>"
                                    data-valor="<?= number_format($mov['valor'], 2, ',', '.') ?>"
                                    data-data="<?= $mov['data_movimentacao'] ?>"
                                    data-categoria="<?= htmlspecialchars($mov['categoria'], ENT_QUOTES) ?>"
                                    data-status="<?= $mov['status'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#modalEditar">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <a href="index.php?acao=excluir_financeiro&id=<?= $mov['id'] ?>&id_obra=<?= $idObra ?>"
                               class="btn btn-sm btn-light text-danger rounded-circle"
                               onclick="return confirm('Excluir este lançamento?')" title="Excluir">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum lançamento encontrado.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Lista mobile ── -->
    <div class="d-md-none">
        <h6 class="text-muted small fw-bold mb-3 text-uppercase ms-1">Extrato</h6>
        <?php if (!empty($movimentacoes)): foreach ($movimentacoes as $mov):
            $isDespesa = $mov['tipo'] === 'despesa';
            $corBorda  = $isDespesa ? '#dc3545' : '#198754';
            $sinal     = $isDespesa ? '−' : '+';
            $icone     = $isDespesa ? 'bi-arrow-down-left' : 'bi-arrow-up-right';
            $bgIcon    = $isDespesa ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success';
        ?>
        <div class="mobile-transaction-item" style="border-left-color:<?= $corBorda ?>">
            <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                <div class="rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0 <?= $bgIcon ?>" style="width:40px;height:40px">
                    <i class="bi <?= $icone ?> fs-5"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($mov['descricao']) ?></div>
                    <div class="small text-muted">
                        <?= date('d/m', strtotime($mov['data_movimentacao'])) ?> · <?= htmlspecialchars($mov['categoria']) ?>
                    </div>
                </div>
            </div>
            <div class="text-end ms-2 flex-shrink-0">
                <div class="fw-bold fs-6 <?= $isDespesa ? 'text-danger' : 'text-success' ?>">
                    <?= $sinal ?> <?= number_format($mov['valor'], 2, ',', '.') ?>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm p-0 text-muted" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li>
                            <button class="dropdown-item btn-editar"
                                    data-id="<?= $mov['id'] ?>"
                                    data-tipo="<?= $mov['tipo'] ?>"
                                    data-descricao="<?= htmlspecialchars($mov['descricao'], ENT_QUOTES) ?>"
                                    data-valor="<?= number_format($mov['valor'], 2, ',', '.') ?>"
                                    data-data="<?= $mov['data_movimentacao'] ?>"
                                    data-categoria="<?= htmlspecialchars($mov['categoria'], ENT_QUOTES) ?>"
                                    data-status="<?= $mov['status'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#modalEditar">
                                <i class="bi bi-pencil me-2"></i> Editar
                            </button>
                        </li>
                        <?php if ($mov['caminho_arquivo']): ?>
                        <li><a class="dropdown-item" href="uploads/financeiro/<?= $mov['caminho_arquivo'] ?>" target="_blank">
                            <i class="bi bi-paperclip me-2"></i> Ver Comprovante
                        </a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item text-danger"
                               href="index.php?acao=excluir_financeiro&id=<?= $mov['id'] ?>&id_obra=<?= $idObra ?>"
                               onclick="return confirm('Excluir?')">
                            <i class="bi bi-trash me-2"></i> Excluir
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-wallet2 display-1 opacity-25"></i>
                <p class="mt-3">Sem lançamentos.</p>
            </div>
        <?php endif; ?>
        <div style="height:80px"></div>
    </div>

</div>

<button class="fab-add-finance d-md-none shadow" data-bs-toggle="modal" data-bs-target="#modalLancamento">
    <i class="bi bi-plus-lg"></i>
</button>

<!-- ── Modal: Novo Lançamento ── -->
<div class="modal fade" id="modalLancamento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-cash-stack me-2"></i> Novo Lançamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="index.php?acao=salvar_financeiro" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_obra" value="<?= $idObra ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Tipo</label>
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="tipo" id="tipoDespesa" value="despesa" checked>
                                <label class="btn btn-outline-danger flex-fill fw-bold py-2" for="tipoDespesa"><i class="bi bi-arrow-down-circle me-2"></i> Despesa</label>
                                <input type="radio" class="btn-check" name="tipo" id="tipoReceita" value="receita">
                                <label class="btn btn-outline-success flex-fill fw-bold py-2" for="tipoReceita"><i class="bi bi-arrow-up-circle me-2"></i> Receita</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Vincular ao Orçamento</label>
                            <select name="id_item_orcamento" class="form-select py-2">
                                <option value="">— Sem vínculo —</option>
                                <?php foreach ($itensOrcamento as $item): if ($item['tipo'] === 'etapa') continue; ?>
                                    <option value="<?= $item['id'] ?>">[<?= $item['codigo_referencia'] ?>] <?= mb_strimwidth($item['descricao'], 0, 50, '…') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Data</label>
                            <input type="date" name="data" class="form-control py-2" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Valor (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">R$</span>
                                <input type="tel" name="valor" class="form-control fw-bold border-start-0 ps-1" placeholder="0,00" required onkeyup="fmtMoeda(this)">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Categoria</label>
                            <input type="text" list="categorias" name="categoria" class="form-control py-2" placeholder="Ex: Material" required>
                            <datalist id="categorias">
                                <option value="Material"><option value="Mão de Obra"><option value="Equipamentos">
                                <option value="Administrativo"><option value="Alimentação"><option value="Combustível">
                                <option value="Medição"><option value="BDI/Impostos">
                            </datalist>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select name="status" class="form-select py-2">
                                <option value="pago">Pago / Realizado</option>
                                <option value="pendente">Pendente / Futuro</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Descrição</label>
                            <input type="text" name="descricao" class="form-control py-2" placeholder="Ex: Compra de Cimento..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Comprovante</label>
                            <input type="file" name="comprovante" class="form-control" accept="image/*,.pdf">
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary fw-bold py-3 rounded-3 shadow-sm">Confirmar Lançamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal: Editar Lançamento ── -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-pencil-fill me-2"></i> Editar Lançamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="index.php?acao=editar_financeiro" method="POST">
                    <input type="hidden" name="id"      id="editId">
                    <input type="hidden" name="id_obra" value="<?= $idObra ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Tipo</label>
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="tipo" id="editTipoDespesa" value="despesa">
                                <label class="btn btn-outline-danger flex-fill fw-bold py-2" for="editTipoDespesa"><i class="bi bi-arrow-down-circle me-2"></i> Despesa</label>
                                <input type="radio" class="btn-check" name="tipo" id="editTipoReceita" value="receita">
                                <label class="btn btn-outline-success flex-fill fw-bold py-2" for="editTipoReceita"><i class="bi bi-arrow-up-circle me-2"></i> Receita</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Vincular ao Orçamento</label>
                            <select name="id_item_orcamento" id="editOrcamento" class="form-select py-2">
                                <option value="">— Sem vínculo —</option>
                                <?php foreach ($itensOrcamento as $item): if ($item['tipo'] === 'etapa') continue; ?>
                                    <option value="<?= $item['id'] ?>">[<?= $item['codigo_referencia'] ?>] <?= mb_strimwidth($item['descricao'], 0, 50, '…') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Data</label>
                            <input type="date" name="data" id="editData" class="form-control py-2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Valor (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">R$</span>
                                <input type="tel" name="valor" id="editValor" class="form-control fw-bold border-start-0 ps-1" required onkeyup="fmtMoeda(this)">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Categoria</label>
                            <input type="text" list="categorias" name="categoria" id="editCategoria" class="form-control py-2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select name="status" id="editStatus" class="form-select py-2">
                                <option value="pago">Pago / Realizado</option>
                                <option value="pendente">Pendente / Futuro</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Descrição</label>
                            <input type="text" name="descricao" id="editDescricao" class="form-control py-2" required>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary fw-bold py-3 rounded-3 shadow-sm">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($dadosMensais)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('graficoMensal'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labelsGrafico) ?>,
        datasets: [
            {
                label: 'Receitas',
                data: <?= json_encode($grafReceitas) ?>,
                backgroundColor: 'rgba(25,135,84,.75)',
                borderRadius: 4,
            },
            {
                label: 'Despesas',
                data: <?= json_encode($grafDespesas) ?>,
                backgroundColor: 'rgba(220,53,69,.75)',
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: ctx => ' R$ ' + ctx.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits:2})
                }
            }
        },
        scales: {
            y: {
                ticks: {
                    callback: v => 'R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits:0})
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<script>
function fmtMoeda(i) {
    var v = i.value.replace(/\D/g, '');
    v = (v / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    i.value = v;
}

// Popula modal de edição com os dados do lançamento clicado
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-editar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('editId').value          = this.dataset.id;
            document.getElementById('editData').value        = this.dataset.data;
            document.getElementById('editValor').value       = this.dataset.valor;
            document.getElementById('editCategoria').value   = this.dataset.categoria;
            document.getElementById('editDescricao').value   = this.dataset.descricao;
            document.getElementById('editStatus').value      = this.dataset.status;

            // Tipo (radio)
            const tipo = this.dataset.tipo;
            document.getElementById('editTipoDespesa').checked = tipo === 'despesa';
            document.getElementById('editTipoReceita').checked = tipo === 'receita';
        });
    });
});
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
