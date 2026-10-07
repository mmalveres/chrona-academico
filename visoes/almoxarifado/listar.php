<?php
// visoes/almoxarifado/listar.php
/** @var array  $obra             */
/** @var array  $movimentacoes    */
/** @var array  $saldo            */
/** @var array  $resumo           */
/** @var array  $nomesMateriais   */
/** @var array  $itensOrcamento   */
include __DIR__ . '/../estruturas/cabecalho.php';

$idObra    = (int)$obra['id'];
$podeEditar = in_array($_SESSION['nivel_acesso'], ['admin', 'engenheiro', 'mestre']);

$fTipo    = $_GET['tipo']           ?? '';
$fMat     = $_GET['material']       ?? '';
$fInicio  = $_GET['periodo_inicio'] ?? '';
$fFim     = $_GET['periodo_fim']    ?? '';
$temFiltro = $fTipo || $fMat || $fInicio || $fFim;
?>
<style>
.badge-entrada { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
.badge-saida   { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
.saldo-zero    { color:#dc2626; font-weight:700; }
.saldo-ok      { color:#16a34a; font-weight:700; }
.fab-almo {
    position:fixed; bottom:90px; right:20px; width:56px; height:56px;
    background:#0d6efd; color:#fff; border-radius:50%; display:flex;
    align-items:center; justify-content:center;
    box-shadow:0 4px 12px rgba(13,110,253,.4); font-size:24px; z-index:1000; border:none;
}
</style>

<div class="container-fluid mt-3 mb-5">

    <!-- ── Cabeçalho ── -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="index.php?acao=listar_obras" class="text-decoration-none text-muted">Obras</a></li>
                    <li class="breadcrumb-item active fw-bold text-warning">Almoxarifado</li>
                </ol>
            </nav>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-boxes me-2 text-warning"></i>
                Almoxarifado — <?= htmlspecialchars($obra['nome']) ?>
            </h5>
            <small class="text-muted">Controle de entrada e saída de materiais em obra</small>
        </div>
        <div class="d-none d-md-flex gap-2">
            <?php if ($podeEditar): ?>
            <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEntrada">
                <i class="bi bi-box-arrow-in-down me-1"></i> Entrada
            </button>
            <button class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSaida">
                <i class="bi bi-box-arrow-up me-1"></i> Saída
            </button>
            <?php endif; ?>
            <a href="index.php?acao=listar_obras" class="btn btn-light border shadow-sm">Voltar</a>
        </div>
    </div>

    <!-- ── Alertas de mensagem ── -->
    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = ['sucesso'=>['success','check-circle-fill','Lançamento salvo!'],
                       'excluido'=>['warning','trash-fill','Lançamento excluído.'],
                       'erro'=>['danger','exclamation-triangle-fill','Erro ao salvar.']];
              $m = $msgs[$_GET['msg']] ?? null; ?>
        <?php if ($m): ?>
        <div class="alert alert-<?= $m[0] ?> border-0 shadow-sm py-2 mb-3">
            <i class="bi bi-<?= $m[1] ?> me-2"></i><?= $m[2] ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ── Cards de resumo ── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-5 border-warning">
                <div class="card-body">
                    <h6 class="text-uppercase text-warning small fw-bold mb-1">Materiais</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= $resumo['total_materiais'] ?></h3>
                    <small class="text-muted">itens diferentes</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-5 border-success">
                <div class="card-body">
                    <h6 class="text-uppercase text-success small fw-bold mb-1">Valor Entradas</h6>
                    <h3 class="fw-bold text-dark mb-0 fs-5">R$ <?= number_format($resumo['valor_entradas'], 2, ',', '.') ?></h3>
                    <small class="text-muted"><?= $resumo['qtd_entradas'] ?> lançamento(s)</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-5 border-danger">
                <div class="card-body">
                    <h6 class="text-uppercase text-danger small fw-bold mb-1">Saídas</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= $resumo['qtd_saidas'] ?></h3>
                    <small class="text-muted">lançamento(s)</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 <?= $resumo['itens_esgotados'] > 0 ? 'border-start border-5 border-danger' : 'border-start border-5 border-secondary' ?>">
                <div class="card-body">
                    <h6 class="text-uppercase small fw-bold mb-1 <?= $resumo['itens_esgotados'] > 0 ? 'text-danger' : 'text-secondary' ?>">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Esgotados
                    </h6>
                    <h3 class="fw-bold mb-0 <?= $resumo['itens_esgotados'] > 0 ? 'text-danger' : 'text-secondary' ?>">
                        <?= $resumo['itens_esgotados'] ?>
                    </h3>
                    <small class="text-muted">material(is) sem saldo</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Estoque atual ── -->
    <?php if (!empty($saldo)): ?>
    <div class="card border-0 shadow-sm mb-4" id="secaoEstoque">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-2">
            <i class="bi bi-archive-fill text-warning"></i>
            <span class="fw-bold">Estoque Atual</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light text-muted text-uppercase small">
                        <tr>
                            <th class="ps-4">Material</th>
                            <th class="text-center">Unidade</th>
                            <th class="text-end">Entradas</th>
                            <th class="text-end">Saídas</th>
                            <th class="text-end pe-4">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($saldo as $item):
                        $saldoVal = (float)$item['saldo'];
                        $cls = $saldoVal <= 0 ? 'saldo-zero' : 'saldo-ok';
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($item['descricao']) ?></td>
                        <td class="text-center text-muted"><?= htmlspecialchars($item['unidade']) ?></td>
                        <td class="text-end text-success"><?= number_format($item['total_entradas'], 3, ',', '.') ?></td>
                        <td class="text-end text-danger"><?= number_format($item['total_saidas'], 3, ',', '.') ?></td>
                        <td class="text-end pe-4 <?= $cls ?>">
                            <?= number_format($saldoVal, 3, ',', '.') ?>
                            <?php if ($saldoVal <= 0): ?>
                                <i class="bi bi-exclamation-triangle-fill ms-1 text-danger small"></i>
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

    <!-- ── Filtro ── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="acao"    value="almoxarifado">
                <input type="hidden" name="id_obra" value="<?= $idObra ?>">
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Tipo</label>
                    <select name="tipo" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="entrada" <?= $fTipo==='entrada' ? 'selected':'' ?>>Entrada</option>
                        <option value="saida"   <?= $fTipo==='saida'   ? 'selected':'' ?>>Saída</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Material</label>
                    <input type="text" name="material" class="form-control form-control-sm"
                           placeholder="Buscar..." value="<?= htmlspecialchars($fMat) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">De</label>
                    <input type="date" name="periodo_inicio" class="form-control form-control-sm" value="<?= $fInicio ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Até</label>
                    <input type="date" name="periodo_fim" class="form-control form-control-sm" value="<?= $fFim ?>">
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                        <i class="bi bi-funnel-fill me-1"></i> Filtrar
                    </button>
                    <?php if ($temFiltro): ?>
                    <a href="index.php?acao=almoxarifado&id_obra=<?= $idObra ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg me-1"></i> Limpar
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Tabela de movimentações (desktop) ── -->
    <div class="card shadow-sm border-0 d-none d-md-block" id="secaoMovimentacoes">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-2">
            <i class="bi bi-list-ul text-secondary"></i>
            <span class="fw-bold">Movimentações</span>
            <span class="badge bg-secondary ms-1"><?= count($movimentacoes) ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light text-muted text-uppercase small">
                        <tr>
                            <th class="ps-4">Data</th>
                            <th class="text-center">Tipo</th>
                            <th>Material</th>
                            <th class="text-center">Qtd / Un.</th>
                            <th class="text-end">Preço Un.</th>
                            <th>Fornecedor / NF</th>
                            <th>Serviço vinculado</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($movimentacoes)): foreach ($movimentacoes as $mov): ?>
                    <tr>
                        <td class="ps-4 text-muted fw-bold"><?= date('d/m/Y', strtotime($mov['data_movimentacao'])) ?></td>
                        <td class="text-center">
                            <?php if ($mov['tipo'] === 'entrada'): ?>
                                <span class="badge badge-entrada"><i class="bi bi-box-arrow-in-down me-1"></i>Entrada</span>
                            <?php else: ?>
                                <span class="badge badge-saida"><i class="bi bi-box-arrow-up me-1"></i>Saída</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($mov['descricao']) ?></td>
                        <td class="text-center">
                            <span class="fw-bold"><?= number_format($mov['quantidade'], 3, ',', '.') ?></span>
                            <span class="text-muted ms-1"><?= htmlspecialchars($mov['unidade']) ?></span>
                        </td>
                        <td class="text-end text-muted">
                            <?= $mov['preco_unitario'] > 0 ? 'R$ ' . number_format($mov['preco_unitario'], 2, ',', '.') : '–' ?>
                        </td>
                        <td class="text-muted">
                            <?= $mov['fornecedor'] ? htmlspecialchars($mov['fornecedor']) : '–' ?>
                            <?php if ($mov['nota_fiscal']): ?>
                                <br><span class="badge bg-light text-secondary border small">NF: <?= htmlspecialchars($mov['nota_fiscal']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                            <?= !empty($mov['codigo_referencia'])
                                ? '[' . $mov['codigo_referencia'] . '] ' . mb_strimwidth($mov['item_orcamento_desc'], 0, 40, '…')
                                : '–' ?>
                        </td>
                        <td class="text-end pe-4">
                            <?php if ($podeEditar): ?>
                            <a href="index.php?acao=excluir_almoxarifado&id=<?= $mov['id'] ?>&id_obra=<?= $idObra ?>"
                               class="btn btn-sm btn-light text-danger rounded-circle"
                               onclick="return confirm('Excluir este lançamento?')" title="Excluir">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">Nenhuma movimentação encontrada.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Lista mobile ── -->
    <div class="d-md-none">
        <h6 class="text-muted small fw-bold mb-2 text-uppercase ms-1">Movimentações</h6>
        <?php if (!empty($movimentacoes)): foreach ($movimentacoes as $mov):
            $isEntrada = $mov['tipo'] === 'entrada';
            $cor   = $isEntrada ? '#16a34a' : '#dc2626';
            $icone = $isEntrada ? 'bi-box-arrow-in-down' : 'bi-box-arrow-up';
            $bgIco = $isEntrada ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger';
        ?>
        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm mb-2" style="border-left:4px solid <?= $cor ?>">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 <?= $bgIco ?>" style="width:40px;height:40px">
                <i class="bi <?= $icone ?> fs-5"></i>
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($mov['descricao']) ?></div>
                <div class="small text-muted">
                    <?= date('d/m', strtotime($mov['data_movimentacao'])) ?>
                    · <?= number_format($mov['quantidade'], 3, ',', '.') ?> <?= htmlspecialchars($mov['unidade']) ?>
                    <?= $mov['fornecedor'] ? '· ' . htmlspecialchars($mov['fornecedor']) : '' ?>
                </div>
            </div>
            <?php if ($podeEditar): ?>
            <a href="index.php?acao=excluir_almoxarifado&id=<?= $mov['id'] ?>&id_obra=<?= $idObra ?>"
               onclick="return confirm('Excluir?')" class="btn btn-sm btn-light text-danger rounded-circle flex-shrink-0">
                <i class="bi bi-trash-fill"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; else: ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-boxes display-1 opacity-25"></i>
            <p class="mt-3">Nenhuma movimentação.</p>
        </div>
        <?php endif; ?>
        <div style="height:80px"></div>
    </div>

</div>

<?php if ($podeEditar): ?>
<div class="d-md-none d-flex gap-2" style="position:fixed;bottom:90px;right:16px;z-index:1000">
    <button class="fab-almo" style="background:#dc2626" data-bs-toggle="modal" data-bs-target="#modalSaida" title="Saída">
        <i class="bi bi-box-arrow-up"></i>
    </button>
    <button class="fab-almo" data-bs-toggle="modal" data-bs-target="#modalEntrada" title="Entrada">
        <i class="bi bi-box-arrow-in-down"></i>
    </button>
</div>
<?php endif; ?>

<!-- ── Modal Entrada ── -->
<div class="modal fade" id="modalEntrada" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-box-arrow-in-down me-2"></i>Entrada de Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="index.php?acao=salvar_almoxarifado" method="POST">
                    <input type="hidden" name="id_obra" value="<?= $idObra ?>">
                    <input type="hidden" name="tipo"    value="entrada">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Material</label>
                            <input type="text" name="descricao" list="listaMateriais"
                                   class="form-control" placeholder="Ex: Cimento CP-II" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Unidade</label>
                            <input type="text" name="unidade" list="listaUnidades"
                                   class="form-control" placeholder="sc, m³, kg..." required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Quantidade</label>
                            <input type="text" name="quantidade" class="form-control fw-bold"
                                   placeholder="0,000" required onkeyup="fmtQtd(this)">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Preço Unitário (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 small">R$</span>
                                <input type="text" name="preco_unitario" class="form-control border-start-0"
                                       placeholder="0,00" onkeyup="fmtMoeda(this)">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Data</label>
                            <input type="date" name="data" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Fornecedor</label>
                            <input type="text" name="fornecedor" class="form-control" placeholder="Opcional">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Nota Fiscal</label>
                            <input type="text" name="nota_fiscal" class="form-control" placeholder="Nº da NF">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Observação</label>
                            <input type="text" name="observacao" class="form-control" placeholder="Opcional">
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success fw-bold py-3 rounded-3">Confirmar Entrada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal Saída ── -->
<div class="modal fade" id="modalSaida" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-box-arrow-up me-2"></i>Saída de Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="index.php?acao=salvar_almoxarifado" method="POST">
                    <input type="hidden" name="id_obra" value="<?= $idObra ?>">
                    <input type="hidden" name="tipo"    value="saida">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Material</label>
                            <input type="text" name="descricao" list="listaMateriais"
                                   class="form-control" placeholder="Ex: Cimento CP-II" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Unidade</label>
                            <input type="text" name="unidade" list="listaUnidades"
                                   class="form-control" placeholder="sc, m³, kg..." required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Quantidade</label>
                            <input type="text" name="quantidade" class="form-control fw-bold"
                                   placeholder="0,000" required onkeyup="fmtQtd(this)">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Data</label>
                            <input type="date" name="data" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Vincular ao Serviço</label>
                            <select name="id_item_orcamento" class="form-select">
                                <option value="">— Sem vínculo —</option>
                                <?php foreach ($itensOrcamento as $item): if ($item['tipo'] === 'etapa') continue; ?>
                                    <option value="<?= $item['id'] ?>">[<?= $item['codigo_referencia'] ?>] <?= mb_strimwidth($item['descricao'], 0, 45, '…') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Observação</label>
                            <input type="text" name="observacao" class="form-control" placeholder="Ex: Aplicado na fundação...">
                        </div>
                        <input type="hidden" name="preco_unitario" value="0">
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-danger fw-bold py-3 rounded-3">Confirmar Saída</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Datalists de autocomplete -->
<datalist id="listaMateriais">
    <?php foreach ($nomesMateriais as $nm): ?>
        <option value="<?= htmlspecialchars($nm['descricao']) ?>">
    <?php endforeach; ?>
</datalist>
<datalist id="listaUnidades">
    <option value="sc"><option value="kg"><option value="g"><option value="t">
    <option value="m"><option value="m²"><option value="m³"><option value="l">
    <option value="un"><option value="cx"><option value="pc"><option value="vb">
    <option value="rl"><option value="fd"><option value="par">
</datalist>

<script>
function fmtQtd(i) {
    var v = i.value.replace(/\D/g, '');
    v = (parseInt(v || '0') / 1000).toFixed(3);
    v = v.replace('.', ',');
    i.value = v;
}
function fmtMoeda(i) {
    var v = i.value.replace(/\D/g, '');
    v = (v / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    i.value = v;
}
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
