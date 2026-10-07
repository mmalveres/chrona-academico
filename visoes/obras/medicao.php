<?php
// visoes/obras/medicao.php
include __DIR__ . '/../estruturas/cabecalho.php';

$totalContrato = 0;
$totalAnterior = 0;
$totalPeriodo  = 0;

if (!empty($itensMedicao)) {
    foreach ($itensMedicao as $item) {
        if ($item['tipo'] !== 'etapa') {
            $preco          = (float)$item['preco_unitario'];
            $totalContrato += (float)$item['qtd_orcada']  * $preco;
            $totalAnterior += (float)$item['qtd_anterior'] * $preco;
            $totalPeriodo  += (float)$item['qtd_periodo']  * $preco;
        }
    }
}
$totalAcumulado = $totalAnterior + $totalPeriodo;
$percGeral = ($totalContrato > 0) ? ($totalAcumulado / $totalContrato) * 100 : 0;

$meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',
          6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',
          10=>'Outubro',11=>'Novembro',12=>'Dezembro'];

// Navegação de meses
$mesPrev = $mes == 1  ? 12 : $mes - 1;
$anoPrev = $mes == 1  ? $ano - 1 : $ano;
$mesProx = $mes == 12 ? 1  : $mes + 1;
$anoProx = $mes == 12 ? $ano + 1 : $ano;
$baseUrl = "index.php?acao=medicao_obra&id_obra={$obra['id']}";
$urlPrev = "$baseUrl&mes=$mesPrev&ano=$anoPrev";
$urlProx = "$baseUrl&mes=$mesProx&ano=$anoProx";

// Limite inferior: mês/ano de início da obra
$dataInicio  = $obra['data_inicio'] ?? date('Y-m-d');
$mesInicio   = (int) date('n', strtotime($dataInicio));
$anoInicio   = (int) date('Y', strtotime($dataInicio));
$podeVoltar  = ($ano * 100 + $mes) > ($anoInicio * 100 + $mesInicio);
?>

<style>
/* ── Célula editável ── */
.cell-edit-wrap {
    display: flex; align-items: center; justify-content: flex-end; gap: 6px; min-width: 120px;
}
.cell-edit-wrap .qtd-display { font-weight: 700; cursor: pointer; }
.cell-edit-wrap .btn-edit-cell {
    background: none; border: none; padding: 0; color: #adb5bd; cursor: pointer;
    font-size: .8rem; line-height: 1;
    opacity: 0; transition: opacity .15s;
}
tr:hover .btn-edit-cell { opacity: 1; }

/* Badge de fonte */
.badge-fonte {
    font-size: .58rem; font-weight: 800; border-radius: 6px;
    padding: 2px 5px; letter-spacing: .4px; text-transform: uppercase; white-space: nowrap;
}
.badge-diario  { background: #EFF6FF; color: #1d4ed8; border: 1px solid #BFDBFE; }
.badge-manual  { background: #FFF7ED; color: #c2410c; border: 1px solid #FED7AA; }
.badge-vazio   { background: #F9FAFB; color: #9ca3af; border: 1px solid #E5E7EB; }

/* Inline edit form */
.edit-row-form { display: none; }
.edit-row-form.active { display: table-row; }
.edit-row-form td {
    background: #FFFBEB; padding: 10px 16px;
    border-bottom: 2px solid #FDE68A;
}
.edit-row-form input[type=number] {
    border-radius: 8px; border: 1.5px solid #FCD34D;
    padding: 7px 12px; font-weight: 700; width: 120px;
    font-size: .9rem;
}
.edit-row-form input[type=number]:focus { outline: none; border-color: #F59E0B; box-shadow: 0 0 0 3px rgba(245,158,11,.15); }
</style>

<!-- Navegador de período -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3 px-3">

        <!-- Linha 1: setas + mês atual -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
            <?php if ($podeVoltar): ?>
            <a href="<?= $urlPrev ?>" class="btn btn-light border fw-bold d-flex align-items-center gap-1 px-3"
               title="<?= $meses[$mesPrev] ?>/<?= $anoPrev ?>">
                <i class="bi bi-chevron-left"></i>
                <span class="d-none d-sm-inline"><?= $meses[$mesPrev] ?></span>
            </a>
            <?php else: ?>
            <button class="btn btn-light border d-flex align-items-center gap-1 px-3" disabled
                    title="Início da obra: <?= $meses[$mesInicio] ?>/<?= $anoInicio ?>">
                <i class="bi bi-chevron-left"></i>
                <span class="d-none d-sm-inline text-muted" style="font-size:.75rem;">Início</span>
            </button>
            <?php endif; ?>

            <div class="text-center">
                <div class="fw-bold text-primary" style="font-size:1.1rem;line-height:1.2;">
                    <?= $meses[$mes] ?>
                </div>
                <div class="text-muted" style="font-size:.8rem;font-weight:600;"><?= $ano ?></div>
            </div>

            <a href="<?= $urlProx ?>" class="btn btn-light border fw-bold d-flex align-items-center gap-1 px-3"
               title="<?= $meses[$mesProx] ?>/<?= $anoProx ?>">
                <span class="d-none d-sm-inline"><?= $meses[$mesProx] ?></span>
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <!-- Linha 2: seletor manual + imprimir -->
        <form action="index.php" method="GET" class="row align-items-end g-2">
            <input type="hidden" name="acao"    value="medicao_obra">
            <input type="hidden" name="id_obra" value="<?= $obra['id'] ?>">
            <div class="col-5 col-md-3">
                <select name="mes" class="form-select form-select-sm fw-bold text-primary border-primary">
                    <?php foreach ($meses as $num => $nome): ?>
                        <option value="<?= $num ?>" <?= $num == $mes ? 'selected' : '' ?>><?= $nome ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-4 col-md-2">
                <select name="ano" class="form-select form-select-sm fw-bold border-primary">
                    <?php for ($y = 2024; $y <= 2030; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-3 col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                    <i class="bi bi-search"></i><span class="d-none d-md-inline ms-1">Ir</span>
                </button>
            </div>
            <div class="col-12 col-md-5 text-md-end">
                <a href="index.php?acao=imprimir_medicao&id_obra=<?= $obra['id'] ?>&mes=<?= $mes ?>&ano=<?= $ano ?>"
                   target="_blank" class="btn btn-outline-dark btn-sm fw-bold border-2">
                    <i class="bi bi-printer-fill me-2"></i> Imprimir Boletim
                </a>
            </div>
        </form>

    </div>
</div>

<!-- Cards de totais -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-secondary">
            <div class="card-body p-3">
                <small class="text-muted fw-bold text-uppercase" style="font-size:.7rem">Acumulado Anterior</small>
                <h5 class="fw-bold text-secondary mb-0">R$ <?= number_format($totalAnterior, 2, ',', '.') ?></h5>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body p-3">
                <small class="text-success fw-bold text-uppercase" style="font-size:.7rem">Neste Período (Mês)</small>
                <h5 class="fw-bold text-success mb-0">R$ <?= number_format($totalPeriodo, 2, ',', '.') ?></h5>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body p-3">
                <small class="text-primary fw-bold text-uppercase" style="font-size:.7rem">Acumulado Total</small>
                <h5 class="fw-bold text-primary mb-0">R$ <?= number_format($totalAcumulado, 2, ',', '.') ?></h5>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-dark">
            <div class="card-body p-3">
                <small class="text-dark fw-bold text-uppercase" style="font-size:.7rem">Contrato Total</small>
                <h5 class="fw-bold text-dark mb-0">R$ <?= number_format($totalContrato, 2, ',', '.') ?></h5>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de medição -->
<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2 px-3">
        <span class="fw-bold text-dark" style="font-size:.9rem;">
            <i class="bi bi-bar-chart-steps me-2 text-primary"></i>
            Boletim de Medição — <?= $meses[(int)$mes] ?>/<?= $ano ?>
        </span>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge-fonte badge-diario"><i class="bi bi-journal-text me-1"></i>Diário</span>
            <span class="badge-fonte badge-manual"><i class="bi bi-pencil-square me-1"></i>Manual</span>
            <span class="badge-fonte badge-vazio">Sem lançamento</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.8rem;" id="tabelaMedicao">
            <thead class="bg-light text-secondary text-uppercase small">
                <tr>
                    <th class="ps-3" style="min-width:220px">Item / Descrição</th>
                    <th class="text-center" style="min-width:50px">Und</th>
                    <th class="text-end" style="min-width:90px">Vlr. Unit.</th>
                    <th class="text-end" style="min-width:80px">Qtd. Contrato</th>
                    <th class="text-end border-end" style="min-width:100px">Vlr. Contrato</th>
                    <th class="text-end bg-light border-start" style="min-width:90px">Qtd. Anterior</th>
                    <th class="text-end bg-success bg-opacity-10 text-success border-start border-end" style="min-width:110px">
                        Qtd. Mês <i class="bi bi-pencil-fill" style="font-size:.6rem;opacity:.5;"></i>
                    </th>
                    <th class="text-end bg-light" style="min-width:80px">Qtd. Total</th>
                    <th class="text-end fw-bold" style="min-width:100px">Valor Mês (R$)</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($itensMedicao)): foreach ($itensMedicao as $item):
                $isEtapa    = ($item['tipo'] === 'etapa');
                $preco      = (float)$item['preco_unitario'];
                $qtdOrcada  = (float)$item['qtd_orcada'];
                $qtdAnt     = (float)$item['qtd_anterior'];
                $qtdMes     = (float)$item['qtd_periodo'];
                $qtdTot     = $qtdAnt + $qtdMes;
                $valContrato= $qtdOrcada * $preco;
                $valMes     = $qtdMes * $preco;
                $temManual  = !empty($item['tem_manual']);
                $temDiario  = !empty($item['tem_diario']);
            ?>
                <?php if ($isEtapa): ?>
                    <tr class="table-secondary fw-bold">
                        <td colspan="9" class="ps-3">
                            <?= htmlspecialchars($item['itemizacao']) ?> — <?= htmlspecialchars($item['descricao']) ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <!-- Linha de dados -->
                    <tr data-item-id="<?= $item['id'] ?>"
                        data-tem-diario="<?= $temDiario ? '1' : '0' ?>"
                        data-tem-manual="<?= $temManual ? '1' : '0' ?>"
                        data-ids-diarios="<?= htmlspecialchars($item['ids_diarios_periodo'] ?? '') ?>"
                        data-qtd-atual="<?= $qtdMes ?>">
                        <td class="ps-3">
                            <span class="fw-bold text-primary small me-1"><?= htmlspecialchars($item['itemizacao']) ?></span>
                            <?= htmlspecialchars($item['descricao']) ?>
                        </td>
                        <td class="text-center text-muted"><?= htmlspecialchars($item['unidade']) ?></td>
                        <td class="text-end text-muted"><?= number_format($preco, 2, ',', '.') ?></td>
                        <td class="text-end text-muted"><?= number_format($qtdOrcada, 2, ',', '.') ?></td>
                        <td class="text-end fw-bold text-dark border-end">R$ <?= number_format($valContrato, 2, ',', '.') ?></td>
                        <td class="text-end text-muted border-start"><?= number_format($qtdAnt, 2, ',', '.') ?></td>
                        <td class="text-end fw-bold text-success bg-success bg-opacity-10 border-start border-end">
                            <div class="cell-edit-wrap">
                                <div class="d-flex flex-column align-items-end gap-1">
                                    <?php if ($temManual): ?>
                                        <span class="badge-fonte badge-manual"><i class="bi bi-pencil-square me-1"></i>Manual</span>
                                    <?php elseif ($temDiario): ?>
                                        <span class="badge-fonte badge-diario"><i class="bi bi-journal-text me-1"></i>Diário</span>
                                    <?php else: ?>
                                        <span class="badge-fonte badge-vazio">—</span>
                                    <?php endif; ?>
                                    <span class="qtd-display"><?= number_format($qtdMes, 2, ',', '.') ?></span>
                                </div>
                                <button class="btn-edit-cell" title="Lançar manualmente"
                                        onclick="abrirEdicao(this)">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                            </div>
                        </td>
                        <td class="text-end fw-bold"><?= number_format($qtdTot, 2, ',', '.') ?></td>
                        <td class="text-end fw-bold text-dark">R$ <?= number_format($valMes, 2, ',', '.') ?></td>
                    </tr>
                    <!-- Linha inline de edição (escondida) -->
                    <tr class="edit-row-form" id="edit-row-<?= $item['id'] ?>">
                        <td colspan="9">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div>
                                    <label class="small fw-bold text-muted d-block mb-1">
                                        Nova quantidade — <?= htmlspecialchars($item['itemizacao']) ?> (<?= htmlspecialchars($item['unidade']) ?>)
                                    </label>
                                    <input type="number" step="0.01" min="0"
                                           id="input-qtd-<?= $item['id'] ?>"
                                           value="<?= $qtdMes ?>"
                                           class="form-control d-inline-block"
                                           style="width:140px;">
                                </div>
                                <div>
                                    <label class="small fw-bold text-muted d-block mb-1">Observação (opcional)</label>
                                    <input type="text" id="input-obs-<?= $item['id'] ?>"
                                           class="form-control d-inline-block" style="width:260px;"
                                           placeholder="Ex: medido em vistoria de campo">
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn btn-warning btn-sm fw-bold"
                                            onclick="salvarEdicao(<?= $item['id'] ?>, <?= $obra['id'] ?>, <?= $mes ?>, <?= $ano ?>)">
                                        <i class="bi bi-check-lg me-1"></i> Salvar
                                    </button>
                                    <button class="btn btn-light btn-sm border"
                                            onclick="fecharEdicao(<?= $item['id'] ?>)">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; endif; ?>
            </tbody>
            <?php if (!empty($itensMedicao)): ?>
            <tfoot class="table-dark fw-bold small text-uppercase">
                <tr>
                    <td colspan="4" class="ps-3">Totais</td>
                    <td class="text-end">R$ <?= number_format($totalContrato, 2, ',', '.') ?></td>
                    <td class="text-end">R$ <?= number_format($totalAnterior, 2, ',', '.') ?></td>
                    <td class="text-end">R$ <?= number_format($totalPeriodo, 2, ',', '.') ?></td>
                    <td class="text-end">R$ <?= number_format($totalAcumulado, 2, ',', '.') ?></td>
                    <td class="text-end">R$ <?= number_format($totalPeriodo, 2, ',', '.') ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Modal de confirmação (item vinculado ao diário) -->
<div class="modal fade" id="modalConfirmacaoDiario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#FFF3CD;">
                <h5 class="modal-title fw-bold text-warning-emphasis">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                    Atenção — item vinculado ao Diário
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="mb-2">Este item possui <strong id="modal-num-entradas">X</strong> lançamento(s) no Diário de Obras neste período:</p>
                <ul class="list-unstyled small mb-3" id="modal-lista-diarios" style="background:#f8f9fa;border-radius:8px;padding:10px 14px;"></ul>
                <p class="mb-0 text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Confirmar irá <strong>ajustar a quantidade no último lançamento do diário</strong> para que o total bata com o valor informado.
                    Os demais lançamentos não serão alterados.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning fw-bold" id="btnConfirmarDiario">
                    <i class="bi bi-check-lg me-1"></i> Confirmar Alteração
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const ID_OBRA_MED = <?= (int)$obra['id'] ?>;
const MES_MED     = <?= (int)$mes ?>;
const ANO_MED     = <?= (int)$ano ?>;

let _pendingConfirm = null; // { idItem, idObra, mes, ano, qtd, obs }

function abrirEdicao(btn) {
    const tr      = btn.closest('tr');
    const idItem  = tr.dataset.itemId;
    // Fecha todos os outros primeiro
    document.querySelectorAll('.edit-row-form.active').forEach(r => {
        if (r.id !== 'edit-row-' + idItem) r.classList.remove('active');
    });
    const editRow = document.getElementById('edit-row-' + idItem);
    editRow.classList.toggle('active');
}

function fecharEdicao(idItem) {
    document.getElementById('edit-row-' + idItem).classList.remove('active');
}

async function salvarEdicao(idItem, idObra, mes, ano) {
    const qtd = parseFloat(document.getElementById('input-qtd-' + idItem).value);
    const obs = document.getElementById('input-obs-' + idItem).value.trim();

    if (isNaN(qtd) || qtd < 0) { alert('Informe uma quantidade válida.'); return; }

    const tr      = document.querySelector(`tr[data-item-id="${idItem}"]`);
    const temDiario = tr.dataset.temDiario === '1';

    const payload = { id_obra: idObra, id_item: idItem, mes, ano, quantidade: qtd,
                      observacoes: obs, forcar: false };

    try {
        const resp = await fetch('index.php?acao=salvar_medicao_manual', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await resp.json();

        if (data.requer_confirmacao) {
            // Monta modal de confirmação
            _pendingConfirm = { idItem, idObra, mes, ano, qtd, obs };
            document.getElementById('modal-num-entradas').textContent = data.entradas.length;
            const ul = document.getElementById('modal-lista-diarios');
            ul.innerHTML = data.entradas.map(e =>
                `<li><i class="bi bi-journal-text text-primary me-2"></i>
                 Diário <strong>#${e.id_diario}</strong> — ${e.data} — <em>${e.quantidade.toLocaleString('pt-BR', {minimumFractionDigits:2})} un</em></li>`
            ).join('');
            new bootstrap.Modal(document.getElementById('modalConfirmacaoDiario')).show();
            return;
        }

        if (data.sucesso) {
            // Recarrega a página para refletir o novo valor
            location.reload();
        } else {
            alert('Erro: ' + (data.mensagem || 'Tente novamente.'));
        }
    } catch (e) {
        alert('Erro de conexão. Tente novamente.');
    }
}

// Confirmação após modal
document.getElementById('btnConfirmarDiario').addEventListener('click', async () => {
    if (!_pendingConfirm) return;
    const payload = { ..._pendingConfirm, forcar: true };
    bootstrap.Modal.getInstance(document.getElementById('modalConfirmacaoDiario')).hide();

    try {
        const resp = await fetch('index.php?acao=salvar_medicao_manual', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await resp.json();
        if (data.sucesso) {
            location.reload();
        } else {
            alert('Erro: ' + (data.mensagem || 'Tente novamente.'));
        }
    } catch (e) {
        alert('Erro de conexão.');
    }
    _pendingConfirm = null;
});
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
