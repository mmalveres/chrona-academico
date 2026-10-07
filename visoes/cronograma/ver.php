<?php
// visoes/cronograma/ver.php
/** @var array $obra             */
/** @var array $itensCronograma  */
include __DIR__ . '/../estruturas/cabecalho.php';

$podeEditar = in_array($_SESSION['nivel_acesso'], ['admin', 'engenheiro', 'mestre']);

// ── Agrupa em etapas raiz com sub-etapas, acumulando valores ──────────
$grupos  = [];
$idxRaiz = -1;

foreach ($itensCronograma as $item) {
    if ($item['tipo'] === 'etapa') {
        $cod      = trim($item['itemizacao'] ?? '');
        $codLimpo = rtrim($cod, '.');
        $pontos   = $codLimpo !== '' ? substr_count($codLimpo, '.') : 0;

        if ($pontos === 0) {
            $idxRaiz++;
            $grupos[$idxRaiz] = [
                'id_item'    => $item['id_item'],
                'codigo'     => $codLimpo ?: rtrim(trim($item['codigo_referencia'] ?? ''), '.'),
                'descricao'  => $item['descricao'],
                'total'      => 0.0,
                'data_inicio'=> $item['data_inicio'] ?? '',
                'data_fim'   => $item['data_fim']    ?? '',
                'subitens'   => [],
            ];
        } elseif ($pontos === 1 && $idxRaiz >= 0) {
            $grupos[$idxRaiz]['subitens'][] = [
                'id_item'    => $item['id_item'],
                'codigo'     => $codLimpo,
                'descricao'  => $item['descricao'],
                'total'      => 0.0,
                'data_inicio'=> $item['data_inicio'] ?? '',
                'data_fim'   => $item['data_fim']    ?? '',
            ];
        }
        continue;
    }

    // Composicao: acumula no sub-item mais recente e no grupo raiz
    if ($idxRaiz >= 0) {
        $valor  = (float)($item['preco_total'] ?? 0);
        $grupos[$idxRaiz]['total'] += $valor;
        $idxSub = count($grupos[$idxRaiz]['subitens']) - 1;
        if ($idxSub >= 0) {
            $grupos[$idxRaiz]['subitens'][$idxSub]['total'] += $valor;
        }
    }
}

$totalGeral    = array_sum(array_column($grupos, 'total'));
$totalSubitens = array_sum(array_map(fn($g) => count($g['subitens']), $grupos));
?>

<form id="formCronograma" action="index.php?acao=salvar_cronograma" method="POST"
      onsubmit="return prepararEnvio()"
      data-inicio-obra="<?= htmlspecialchars($obra['data_inicio'] ?? '') ?>"
      data-fim-obra="<?= htmlspecialchars($obra['data_previsao_fim'] ?? '') ?>">
    <input type="hidden" name="id_obra" value="<?= $obra['id'] ?>">

<div class="container-fluid mt-3 mb-5">

    <!-- ── Cabeçalho ──────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="index.php?acao=listar_obras" class="text-decoration-none text-muted">Obras</a></li>
                    <li class="breadcrumb-item active fw-bold text-info">Cronograma</li>
                </ol>
            </nav>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-calendar-week me-2 text-info"></i>
                Cronograma: <?= htmlspecialchars($obra['nome']) ?>
            </h5>
            <small class="text-muted">
                <?= count($grupos) ?> etapa(s)
                <?= $totalSubitens > 0 ? '— ' . $totalSubitens . ' sub-etapa(s)' : '' ?>
                — valor total: <strong>R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong>
            </small>
        </div>

        <div class="d-flex gap-2">
            <?php if ($podeEditar): ?>
                <button type="button" class="btn btn-outline-warning fw-bold shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#modalAutoPreencher">
                    <i class="bi bi-magic me-2"></i><span class="d-none d-md-inline">Preencher</span> Auto
                </button>
            <?php endif; ?>

            <a href="index.php?acao=imprimir_cronograma&id_obra=<?= $obra['id'] ?>"
               target="_blank" class="btn btn-dark fw-bold shadow-sm">
                <i class="bi bi-printer me-2"></i><span class="d-none d-md-inline">PDF</span>
            </a>

            <?php if ($podeEditar): ?>
                <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">
                    <i class="bi bi-check-lg me-2"></i>Salvar
                </button>
            <?php endif; ?>

            <a href="index.php?acao=listar_obras" class="btn btn-light border">Voltar</a>
        </div>
    </div>

    <!-- ── Alertas ────────────────────────────────────────────── -->
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'sucesso'): ?>
            <div class="alert alert-success border-0 shadow-sm mb-3"><i class="bi bi-check-circle-fill me-2"></i>Cronograma salvo com sucesso!</div>
        <?php elseif ($_GET['msg'] === 'erro'): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Erro ao salvar. Verifique o banco de dados.</div>
        <?php elseif ($_GET['msg'] === 'vazio'): ?>
            <div class="alert alert-warning border-0 shadow-sm mb-3"><i class="bi bi-exclamation-circle me-2"></i>Nenhuma data foi preenchida.</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (empty($grupos)): ?>
        <div class="card-custom text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
            <p>Nenhum item encontrado. <a href="index.php?acao=listar_obras">Importe um orçamento primeiro.</a></p>
        </div>
    <?php else: ?>

    <!-- ── TABELA DESKTOP ─────────────────────────────────────── -->
    <div class="card shadow-sm border-0 d-none d-md-block view-desktop">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-bordered">
                    <thead class="bg-light small text-secondary text-uppercase">
                        <tr>
                            <th width="7%"  class="text-center">Etapa</th>
                            <th width="38%">Discriminação</th>
                            <th width="12%" class="text-end">Valor Total</th>
                            <th width="7%"  class="text-end text-muted small">% Contrato</th>
                            <th width="14%" class="text-center bg-info bg-opacity-10">Início</th>
                            <th width="14%" class="text-center bg-info bg-opacity-10">Fim</th>
                            <th width="8%"  class="text-center">Dias</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grupos as $g):
                            $temSubitens = !empty($g['subitens']);
                            $percGrupo   = $totalGeral > 0 ? $g['total'] / $totalGeral * 100 : 0;
                            $durGrupo    = (!$temSubitens && $g['data_inicio'] && $g['data_fim'])
                                ? (int)((strtotime($g['data_fim']) - strtotime($g['data_inicio'])) / 86400) + 1
                                : null;
                        ?>

                        <!-- Linha da etapa raiz -->
                        <tr class="table-secondary <?= !$temSubitens ? 'container-cronograma' : '' ?>"
                            <?= !$temSubitens ? 'data-tipo="composicao" data-custo="' . $g['total'] . '"' : '' ?>>
                            <td class="text-center fw-bold text-dark"><?= htmlspecialchars($g['codigo']) ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($g['descricao']) ?></td>
                            <td class="text-end small">
                                <span class="fw-bold">R$ <?= number_format($g['total'], 2, ',', '.') ?></span>
                            </td>
                            <td class="text-end small text-muted"><?= number_format($percGrupo, 1, ',', '.') ?>%</td>

                            <?php if ($temSubitens): ?>
                                <td class="p-1 bg-info bg-opacity-10 text-center text-muted small">–</td>
                                <td class="p-1 bg-info bg-opacity-10 text-center text-muted small">–</td>
                                <td class="text-center small text-muted">–</td>
                            <?php else: ?>
                                <td class="p-1 bg-info bg-opacity-10">
                                    <input type="date"
                                           name="cronograma[<?= $g['id_item'] ?>][inicio]"
                                           class="form-control form-control-sm border-0 text-center bg-transparent fw-bold text-primary js-inicio"
                                           value="<?= htmlspecialchars($g['data_inicio'] ?? '') ?>"
                                           <?= !$podeEditar ? 'readonly' : '' ?>
                                           onchange="calcDuracao(this)">
                                </td>
                                <td class="p-1 bg-info bg-opacity-10">
                                    <input type="date"
                                           name="cronograma[<?= $g['id_item'] ?>][fim]"
                                           class="form-control form-control-sm border-0 text-center bg-transparent fw-bold text-primary js-fim"
                                           value="<?= htmlspecialchars($g['data_fim'] ?? '') ?>"
                                           <?= !$podeEditar ? 'readonly' : '' ?>
                                           onchange="calcDuracao(this)">
                                </td>
                                <td class="text-center small text-muted duracao-texto">
                                    <?= $durGrupo !== null ? $durGrupo . ' d' : '–' ?>
                                </td>
                            <?php endif; ?>
                        </tr>

                        <!-- Sub-etapas -->
                        <?php foreach ($g['subitens'] as $sub):
                            $percSub = $totalGeral > 0 ? $sub['total'] / $totalGeral * 100 : 0;
                            $durSub  = $sub['data_inicio'] && $sub['data_fim']
                                ? (int)((strtotime($sub['data_fim']) - strtotime($sub['data_inicio'])) / 86400) + 1
                                : null;
                        ?>
                        <tr class="container-cronograma" data-tipo="composicao" data-custo="<?= $sub['total'] ?>">
                            <td class="text-center small text-primary ps-4 fw-semibold">
                                <?= htmlspecialchars($sub['codigo']) ?>
                            </td>
                            <td class="text-dark small ps-4">
                                <span class="text-muted me-1">↳</span><?= htmlspecialchars($sub['descricao']) ?>
                            </td>
                            <td class="text-end small">R$ <?= number_format($sub['total'], 2, ',', '.') ?></td>
                            <td class="text-end small text-muted"><?= number_format($percSub, 1, ',', '.') ?>%</td>
                            <td class="p-1 bg-info bg-opacity-10">
                                <input type="date"
                                       name="cronograma[<?= $sub['id_item'] ?>][inicio]"
                                       class="form-control form-control-sm border-0 text-center bg-transparent fw-bold text-primary js-inicio"
                                       value="<?= htmlspecialchars($sub['data_inicio'] ?? '') ?>"
                                       <?= !$podeEditar ? 'readonly' : '' ?>
                                       onchange="calcDuracao(this)">
                            </td>
                            <td class="p-1 bg-info bg-opacity-10">
                                <input type="date"
                                       name="cronograma[<?= $sub['id_item'] ?>][fim]"
                                       class="form-control form-control-sm border-0 text-center bg-transparent fw-bold text-primary js-fim"
                                       value="<?= htmlspecialchars($sub['data_fim'] ?? '') ?>"
                                       <?= !$podeEditar ? 'readonly' : '' ?>
                                       onchange="calcDuracao(this)">
                            </td>
                            <td class="text-center small text-muted duracao-texto">
                                <?= $durSub !== null ? $durSub . ' d' : '–' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-dark fw-bold small">
                            <td colspan="2" class="ps-3">TOTAL</td>
                            <td class="text-end">R$ <?= number_format($totalGeral, 2, ',', '.') ?></td>
                            <td class="text-end">100%</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ── CARDS MOBILE ───────────────────────────────────────── -->
    <div class="d-md-none view-mobile">
        <?php foreach ($grupos as $g):
            $temSubitens = !empty($g['subitens']);
            $percGrupo   = $totalGeral > 0 ? $g['total'] / $totalGeral * 100 : 0;
        ?>

        <!-- Cabeçalho do grupo -->
        <div class="d-flex align-items-center gap-2 mt-3 mb-2 px-1">
            <span class="badge bg-dark rounded-pill px-2"><?= htmlspecialchars($g['codigo']) ?></span>
            <span class="fw-bold text-dark small flex-grow-1"><?= htmlspecialchars($g['descricao']) ?></span>
            <span class="text-primary fw-bold small">R$ <?= number_format($g['total'], 2, ',', '.') ?></span>
        </div>

        <?php if (!$temSubitens):
            $durGrupo = $g['data_inicio'] && $g['data_fim']
                ? (int)((strtotime($g['data_fim']) - strtotime($g['data_inicio'])) / 86400) + 1
                : null;
        ?>
        <!-- Card único para etapa sem sub-itens -->
        <div class="card bg-white border-0 shadow-sm mb-3 item-cronograma-mobile container-cronograma"
             data-tipo="composicao" data-custo="<?= $g['total'] ?>">
            <div class="card-body p-3">
                <div class="d-flex justify-content-end mb-2">
                    <span class="small text-muted"><?= number_format($percGrupo, 1, ',', '.') ?>% do contrato</span>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="small text-muted">Início</label>
                        <input type="date"
                               name="cronograma[<?= $g['id_item'] ?>][inicio]"
                               class="form-control form-control-sm fw-bold text-primary js-inicio"
                               value="<?= htmlspecialchars($g['data_inicio'] ?? '') ?>"
                               <?= !$podeEditar ? 'readonly' : '' ?>
                               onchange="calcDuracaoMobile(this)">
                    </div>
                    <div class="col-6">
                        <label class="small text-muted">Fim</label>
                        <input type="date"
                               name="cronograma[<?= $g['id_item'] ?>][fim]"
                               class="form-control form-control-sm fw-bold text-primary js-fim"
                               value="<?= htmlspecialchars($g['data_fim'] ?? '') ?>"
                               <?= !$podeEditar ? 'readonly' : '' ?>
                               onchange="calcDuracaoMobile(this)">
                    </div>
                    <div class="col-12 text-end">
                        <small class="text-muted">Duração: </small>
                        <span class="fw-bold text-dark duracao-mobile">
                            <?= $durGrupo !== null ? $durGrupo . ' dias' : '–' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>

        <!-- Cards para cada sub-etapa -->
        <?php foreach ($g['subitens'] as $sub):
            $percSub = $totalGeral > 0 ? $sub['total'] / $totalGeral * 100 : 0;
            $durSub  = $sub['data_inicio'] && $sub['data_fim']
                ? (int)((strtotime($sub['data_fim']) - strtotime($sub['data_inicio'])) / 86400) + 1
                : null;
        ?>
        <div class="card bg-white border-0 shadow-sm mb-2 ms-3 item-cronograma-mobile container-cronograma"
             data-tipo="composicao" data-custo="<?= $sub['total'] ?>">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary rounded-pill px-2 small"><?= htmlspecialchars($sub['codigo']) ?></span>
                    <span class="small text-muted"><?= number_format($percSub, 1, ',', '.') ?>% do contrato</span>
                </div>
                <p class="text-dark mb-1 small"><?= htmlspecialchars($sub['descricao']) ?></p>
                <p class="text-primary fw-bold mb-3 small">R$ <?= number_format($sub['total'], 2, ',', '.') ?></p>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="small text-muted">Início</label>
                        <input type="date"
                               name="cronograma[<?= $sub['id_item'] ?>][inicio]"
                               class="form-control form-control-sm fw-bold text-primary js-inicio"
                               value="<?= htmlspecialchars($sub['data_inicio'] ?? '') ?>"
                               <?= !$podeEditar ? 'readonly' : '' ?>
                               onchange="calcDuracaoMobile(this)">
                    </div>
                    <div class="col-6">
                        <label class="small text-muted">Fim</label>
                        <input type="date"
                               name="cronograma[<?= $sub['id_item'] ?>][fim]"
                               class="form-control form-control-sm fw-bold text-primary js-fim"
                               value="<?= htmlspecialchars($sub['data_fim'] ?? '') ?>"
                               <?= !$podeEditar ? 'readonly' : '' ?>
                               onchange="calcDuracaoMobile(this)">
                    </div>
                    <div class="col-12 text-end">
                        <small class="text-muted">Duração: </small>
                        <span class="fw-bold text-dark duracao-mobile">
                            <?= $durSub !== null ? $durSub . ' dias' : '–' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php endif; ?>

        <?php endforeach; ?>
        <div style="height:80px"></div>
    </div>

    <?php endif; ?>

</div>
</form>

<!-- ── Modal Preencher Auto ─────────────────────────────────── -->
<div class="modal fade" id="modalAutoPreencher" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fs-5"><i class="bi bi-magic me-2"></i>Preenchimento Automático</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                <div class="mb-4">
                    <label class="form-label fw-bold small">Lógica de Agendamento</label>

                    <div class="form-check border p-3 rounded mb-2 bg-warning bg-opacity-10 border-warning"
                         onclick="alternarModo('proporcional')">
                        <input class="form-check-input" type="radio" name="autoModo"
                               id="modoProporcional" value="proporcional" checked>
                        <label class="form-check-label fw-bold" for="modoProporcional" style="cursor:pointer">
                            <i class="bi bi-pie-chart-fill me-1 text-warning"></i>
                            Por Custo do Orçamento
                            <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">Recomendado</span>
                        </label>
                        <div class="small text-muted mt-1">Cada etapa recebe dias proporcionais ao seu peso financeiro no contrato.</div>
                    </div>

                    <div class="form-check border p-3 rounded" onclick="alternarModo('sequencial')">
                        <input class="form-check-input" type="radio" name="autoModo"
                               id="modoSequencial" value="sequencial">
                        <label class="form-check-label" for="modoSequencial" style="cursor:pointer">
                            Sequencial — X dias por etapa (cascata)
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Data de Início</label>
                    <input type="date" id="autoDataInicio" class="form-control"
                           value="<?= htmlspecialchars($obra['data_inicio'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="mb-3" id="campoDataFim">
                    <label class="form-label fw-bold small">Data de Término Previsto</label>
                    <input type="date" id="autoDataFim" class="form-control"
                           value="<?= htmlspecialchars($obra['data_previsao_fim'] ?? '') ?>">
                    <div class="form-text text-muted">Pré-preenchido com o prazo cadastrado na obra.</div>
                </div>

                <div class="mb-3 d-none" id="campoDias">
                    <label class="form-label fw-bold small">Dias por Etapa</label>
                    <input type="number" id="autoDias" class="form-control" value="30" min="1">
                </div>

                <button type="button" class="btn btn-warning fw-bold w-100 text-dark"
                        onclick="executarPreenchimento()">
                    <i class="bi bi-calendar-check me-2"></i>Aplicar Datas
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Envio: desativa inputs do lado oculto ──────────────────────
function prepararEnvio() {
    const isMobile = window.getComputedStyle(document.querySelector('.view-mobile')).display !== 'none';
    const desativar = isMobile
        ? document.querySelectorAll('.view-desktop input')
        : document.querySelectorAll('.view-mobile input');
    desativar.forEach(i => i.disabled = true);
    return true;
}

// ── Modal: alterna campos visíveis ─────────────────────────────
function alternarModo(modo) {
    document.getElementById('modoProporcional').checked = (modo === 'proporcional');
    document.getElementById('modoSequencial').checked   = (modo === 'sequencial');
    document.getElementById('campoDataFim').classList.toggle('d-none', modo !== 'proporcional');
    document.getElementById('campoDias').classList.toggle('d-none',    modo === 'proporcional');
}

// ── Preenchimento automático ───────────────────────────────────
function executarPreenchimento() {
    const modo = document.querySelector('input[name="autoModo"]:checked').value;
    modo === 'proporcional' ? executarProporcional() : executarSequencial();
    bootstrap.Modal.getInstance(document.getElementById('modalAutoPreencher')).hide();
}

function executarProporcional() {
    const d0 = new Date(document.getElementById('autoDataInicio').value);
    const d1 = new Date(document.getElementById('autoDataFim').value);
    if (isNaN(d0) || isNaN(d1)) { alert('Preencha as datas de início e término.'); return; }

    const totalDias  = Math.max(1, Math.round((d1 - d0) / 86400000));
    const containers = coletarContainersUnicos();
    const totalCusto = containers.reduce((s, c) => s + (parseFloat(c.el.dataset.custo) || 0), 0);
    if (totalCusto === 0) { alert('Etapas sem valor. Verifique o orçamento importado.'); return; }

    let dataAtual = new Date(d0);
    containers.forEach((c, idx) => {
        const custo    = parseFloat(c.el.dataset.custo) || 0;
        const diasItem = idx === containers.length - 1
            ? Math.max(1, Math.round((d1 - dataAtual) / 86400000) + 1)
            : Math.max(1, Math.round((custo / totalCusto) * totalDias));

        const fimItem = new Date(dataAtual);
        fimItem.setDate(fimItem.getDate() + diasItem - 1);

        aplicarDatasNoContainer(c.id, dataAtual.toISOString().split('T')[0], fimItem.toISOString().split('T')[0]);
        dataAtual.setDate(dataAtual.getDate() + diasItem);
    });
}

function executarSequencial() {
    const d0  = new Date(document.getElementById('autoDataInicio').value);
    const dias = parseInt(document.getElementById('autoDias').value) || 30;
    if (isNaN(d0)) { alert('Selecione uma data de início.'); return; }

    let dataAtual = new Date(d0);
    coletarContainersUnicos().forEach(c => {
        const fim = new Date(dataAtual);
        fim.setDate(fim.getDate() + dias - 1);
        aplicarDatasNoContainer(c.id, dataAtual.toISOString().split('T')[0], fim.toISOString().split('T')[0]);
        dataAtual.setDate(dataAtual.getDate() + dias);
    });
}

function coletarContainersUnicos() {
    const vistos = {}, lista = [];
    document.querySelectorAll('.container-cronograma').forEach(el => {
        const inp = el.querySelector('.js-inicio');
        if (!inp) return;
        const m = inp.name.match(/cronograma\[(\d+)\]/);
        if (!m || vistos[m[1]]) return;
        vistos[m[1]] = true;
        lista.push({ id: m[1], el });
    });
    return lista;
}

function aplicarDatasNoContainer(id, inicio, fim) {
    document.querySelectorAll(`input[name="cronograma[${id}][inicio]"]`).forEach(i => {
        i.value = inicio; calcDuracao(i);
    });
    document.querySelectorAll(`input[name="cronograma[${id}][fim]"]`).forEach(i => {
        i.value = fim;
    });
}

// ── Cálculo de duração ─────────────────────────────────────────
function calcDuracao(input) {
    const row = input.closest('tr');
    if (row) atualizarDiff(row, '.js-inicio', '.js-fim', '.duracao-texto', ' d');
}
function calcDuracaoMobile(input) {
    const card = input.closest('.item-cronograma-mobile');
    if (card) atualizarDiff(card, '.js-inicio', '.js-fim', '.duracao-mobile', ' dias');
}

function atualizarDiff(container, selIni, selFim, selSpan, sufixo) {
    const ini  = container.querySelector(selIni)?.value;
    const fim  = container.querySelector(selFim)?.value;
    const span = container.querySelector(selSpan);
    if (!span) return;
    if (ini && fim) {
        const diff = (new Date(fim) - new Date(ini)) / 86400000;
        span.innerText = diff >= 0 ? (Math.ceil(diff) + 1) + sufixo : '!';
        span.style.color = diff < 0 ? 'red' : '';
    } else {
        span.innerText = '–';
    }
}
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
