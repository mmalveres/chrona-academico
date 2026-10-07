<?php
// visoes/obras/listar.php
/** @var array $obras */
use Aplicacao\Helpers\CsrfHelper;
include __DIR__ . '/../estruturas/cabecalho.php';
$csrfToken = CsrfHelper::queryParam();

use Aplicacao\Helpers\PlanoHelper;

$nivel = $_SESSION['nivel_acesso'] ?? '';

// Pré-calcula permissões de plano (evita chamar várias vezes no loop)
$podeCronograma  = PlanoHelper::podeUsarCronograma();
$podeMedicao     = PlanoHelper::podeUsarMedicao();
$podeNC          = PlanoHelper::podeUsarNC();
$urlUpgrade      = PlanoHelper::getUrlUpgrade();

// Verifica se ainda pode criar obras (usado no botão "Nova Obra")
$totalObras  = count($obras);
$podeNovaObra = PlanoHelper::podeAdicionarObra($totalObras);
$maxObras     = PlanoHelper::getMaxObras();

// Função Auxiliar de Status Visual
function getStatusBadge(?string $status): string {
    $st = trim(mb_strtolower($status ?? '', 'UTF-8'));
    $ativos = ['ativa', 'ativo', '1', 'em andamento', 'iniciada'];
    $isAtiva = in_array($st, $ativos);

    if (!$isAtiva && strpos($st, 'conclu') === false && strpos($st, 'inativa') === false && $st != '0') {
        $isAtiva = true;
    }

    if ($isAtiva) {
        return '<span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success fw-bold px-3"><i class="bi bi-activity me-1"></i> Em Andamento</span>';
    } else {
        return '<span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary fw-bold px-3"><i class="bi bi-check-circle-fill me-1"></i> Concluída</span>';
    }
}
?>

<style>
    /* Card de obra */
    .card-obra {
        background: #fff; border-radius: 18px; border: 1px solid #f0f0f0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05); transition: box-shadow .2s, transform .15s;
    }
    .card-obra:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.10); transform: translateY(-2px); }

    /* Botão de ação individual */
    .btn-acao {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 4px; padding: 10px 6px; border-radius: 12px; text-decoration: none;
        font-size: .72rem; font-weight: 700; line-height: 1.2; text-align: center;
        border: 1.5px solid transparent; transition: background .15s, border-color .15s, transform .1s;
        min-height: 62px;
    }
    .btn-acao i { font-size: 1.25rem; }
    .btn-acao:hover { transform: translateY(-1px); }
    .btn-acao:active { transform: scale(.95); }

    /* Variantes de cor */
    .btn-acao-primary   { background:#EFF6FF; color:#1d4ed8; border-color:#BFDBFE; }
    .btn-acao-primary:hover { background:#DBEAFE; color:#1d4ed8; }
    .btn-acao-success   { background:#F0FDF4; color:#15803d; border-color:#BBF7D0; }
    .btn-acao-success:hover { background:#DCFCE7; color:#15803d; }
    .btn-acao-info      { background:#F0F9FF; color:#0369a1; border-color:#BAE6FD; }
    .btn-acao-info:hover { background:#E0F2FE; color:#0369a1; }
    .btn-acao-warning   { background:#FFFBEB; color:#b45309; border-color:#FDE68A; }
    .btn-acao-warning:hover { background:#FEF3C7; color:#b45309; }
    .btn-acao-danger    { background:#FFF1F2; color:#be123c; border-color:#FECDD3; }
    .btn-acao-danger:hover { background:#FFE4E6; color:#be123c; }
    .btn-acao-secondary { background:#F8FAFC; color:#64748b; border-color:#E2E8F0; }
    .btn-acao-secondary:hover { background:#F1F5F9; color:#64748b; }

    /* Botão bloqueado (plano inferior) */
    .btn-acao-locked {
        background:#F8FAFC; color:#CBD5E1; border-color:#E2E8F0; cursor:default;
        position:relative;
    }
    .btn-acao-locked:hover { transform:none; background:#F8FAFC; }
    .btn-acao-locked .badge-pro {
        position:absolute; top:4px; right:4px;
        font-size:.5rem; background:#FFB300; color:#333;
        padding:1px 4px; border-radius:6px; font-weight:800; line-height:1.4;
    }

    /* Banner de limite de obras */
    .alert-plano {
        background: linear-gradient(135deg, #fff8e1, #fff3cd);
        border: 1px solid #FFB300; border-radius: 14px; padding: 14px 18px;
        display: flex; align-items: center; gap: 14px; margin-bottom: 20px;
    }

    /* Separador de ações admin */
    .obras-admin-footer {
        border-top: 1px solid #f0f0f0; padding-top: 10px; margin-top: 4px;
    }
    .btn-admin-sm {
        font-size: .75rem; font-weight: 700; padding: 5px 12px; border-radius: 8px;
        text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
    }
</style>

<!-- Campo de Busca -->
<div class="mb-3">
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="campoBuscaObras" class="form-control border-start-0 ps-0"
               placeholder="Buscar obra por nome ou cidade..." oninput="filtrarObras(this.value)">
    </div>
</div>

<!-- Cabeçalho da Página -->
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--primary-color);">Minhas Obras</h4>
        <p class="text-secondary mb-0 small text-uppercase fw-bold d-none d-md-block">Gerencie seus projetos e contratos</p>
    </div>

    <?php if ($nivel === 'admin'): ?>
        <?php if ($podeNovaObra): ?>
            <a href="index.php?acao=nova_obra"
               class="btn btn-warning text-dark fw-bold shadow-sm" style="border-radius: 10px;">
                <i class="bi bi-plus-lg me-2"></i>
                <span class="d-none d-sm-inline">Nova Obra</span>
                <span class="d-inline d-sm-none">Nova</span>
            </a>
        <?php else: ?>
            <!-- Limite de obras atingido: botão desabilitado com tooltip de upgrade -->
            <a href="<?= $urlUpgrade ?>" target="_blank"
               class="btn btn-outline-warning fw-bold shadow-sm" style="border-radius: 10px;"
               title="Limite de <?= $maxObras ?> obra(s) atingido. Faça upgrade para adicionar mais.">
                <i class="bi bi-lock-fill me-1"></i>
                <span class="d-none d-sm-inline">Nova Obra</span>
                <span class="d-inline d-sm-none">Nova</span>
                <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Upgrade</span>
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Aviso quando limite de obras está atingido -->
<?php if ($nivel === 'admin' && !$podeNovaObra): ?>
    <div class="alert-plano mb-4">
        <i class="bi bi-lock-fill fs-4 text-warning"></i>
        <div>
            <strong class="text-dark">Limite de obras atingido</strong>
            <div class="small text-muted">
                Seu plano <strong><?= PlanoHelper::getLabelPlano() ?></strong> permite até <?= $maxObras ?> obra(s).
                <a href="<?= $urlUpgrade ?>" target="_blank" class="fw-bold text-warning text-decoration-none ms-1">
                    Fazer upgrade <i class="bi bi-arrow-up-circle-fill"></i>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Alertas de ação -->
<?php if (isset($_GET['msg'])): ?>
    <div class="mb-4">
        <?php if ($_GET['msg'] == 'obra_criada'): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center"><i class="bi bi-check-circle-fill fs-4 me-3"></i> Obra cadastrada!</div>
        <?php elseif ($_GET['msg'] == 'obra_atualizada'): ?>
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center"><i class="bi bi-pencil-fill fs-4 me-3"></i> Obra atualizada!</div>
        <?php elseif ($_GET['msg'] == 'obra_excluida'): ?>
            <div class="alert alert-secondary border-0 shadow-sm d-flex align-items-center"><i class="bi bi-trash-fill fs-4 me-3"></i> Obra excluída.</div>
        <?php elseif ($_GET['msg'] == 'orcamento_sucesso'): ?>
            <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Orçamento processado!</div>
        <?php elseif ($_GET['msg'] == 'erro_importacao'): ?>
            <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-x-circle-fill me-2"></i> Erro ao processar arquivo.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- GRID DE CARDS (desktop 2 colunas / mobile 1 coluna)         -->
<!-- ════════════════════════════════════════════════════════════ -->
<?php if (count($obras) > 0): ?>
<div class="row g-4 pb-5">
    <?php foreach ($obras as $obra): ?>
    <div class="col-12 col-lg-6">
        <div class="card-obra p-0 h-100 d-flex flex-column">

            <!-- Cabeçalho do card -->
            <div class="p-3 pb-2">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                    <h5 class="fw-bold text-dark mb-0 lh-sm" style="font-size:1rem;">
                        <?= htmlspecialchars($obra['nome']); ?>
                    </h5>
                    <?= getStatusBadge($obra['status']); ?>
                </div>
                <div class="text-muted small mb-2">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <?= htmlspecialchars($obra['cidade']); ?>
                </div>

                <!-- Info resumida: orçamento / início / responsável -->
                <div class="d-flex flex-wrap gap-3 py-2 border-top border-bottom" style="border-color:#f0f0f0 !important;">
                    <div>
                        <div style="font-size:.62rem;text-transform:uppercase;letter-spacing:.6px;font-weight:700;color:#9ca3af;">Orçamento</div>
                        <div class="fw-bold text-dark" style="font-size:.85rem;">
                            R$ <?= number_format($obra['orcamento_previsto'], 2, ',', '.'); ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:.62rem;text-transform:uppercase;letter-spacing:.6px;font-weight:700;color:#9ca3af;">Início</div>
                        <div class="fw-bold text-dark" style="font-size:.85rem;">
                            <?= date('d/m/y', strtotime($obra['data_inicio'])); ?>
                        </div>
                    </div>
                    <?php if (!empty($obra['responsavel_nome'])): ?>
                    <div>
                        <div style="font-size:.62rem;text-transform:uppercase;letter-spacing:.6px;font-weight:700;color:#9ca3af;">Responsável</div>
                        <div class="fw-bold text-dark" style="font-size:.85rem;">
                            <?= explode(' ', $obra['responsavel_nome'])[0]; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Grid de ações -->
            <div class="px-3 pb-2 flex-grow-1">
                <div class="row g-2 mt-1">

                    <!-- Diário -->
                    <div class="col-4 col-sm-3">
                        <a href="index.php?acao=ver_diario&id_obra=<?= $obra['id']; ?>"
                           class="btn-acao btn-acao-primary w-100">
                            <i class="bi bi-journal-text"></i> Diário
                        </a>
                    </div>

                    <!-- Financeiro -->
                    <?php if (in_array($nivel, ['admin', 'financeiro'])): ?>
                    <div class="col-4 col-sm-3">
                        <a href="index.php?acao=financeiro&id_obra=<?= $obra['id']; ?>"
                           class="btn-acao btn-acao-success w-100">
                            <i class="bi bi-cash-stack"></i> Financeiro
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Cronograma -->
                    <div class="col-4 col-sm-3">
                        <?php if ($podeCronograma): ?>
                        <a href="index.php?acao=cronograma&id_obra=<?= $obra['id']; ?>"
                           class="btn-acao btn-acao-info w-100">
                            <i class="bi bi-calendar-week"></i> Cronograma
                        </a>
                        <?php else: ?>
                        <a href="<?= $urlUpgrade ?>" target="_blank"
                           class="btn-acao btn-acao-locked w-100" title="Disponível nos planos Construtora e Empresarial">
                            <span class="badge-pro">Pro</span>
                            <i class="bi bi-calendar-week"></i> Cronograma
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Medição Física -->
                    <div class="col-4 col-sm-3">
                        <?php if ($podeMedicao): ?>
                        <a href="index.php?acao=medicao_obra&id_obra=<?= $obra['id']; ?>"
                           class="btn-acao btn-acao-primary w-100">
                            <i class="bi bi-bar-chart-steps"></i> Medição
                        </a>
                        <?php else: ?>
                        <a href="<?= $urlUpgrade ?>" target="_blank"
                           class="btn-acao btn-acao-locked w-100" title="Disponível nos planos Construtora e Empresarial">
                            <span class="badge-pro">Pro</span>
                            <i class="bi bi-bar-chart-steps"></i> Medição
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Curva S -->
                    <div class="col-4 col-sm-3">
                        <?php if ($podeCronograma): ?>
                        <a href="index.php?acao=curva_s&id_obra=<?= $obra['id']; ?>"
                           class="btn-acao btn-acao-info w-100">
                            <i class="bi bi-graph-up-arrow"></i> Curva S
                        </a>
                        <?php else: ?>
                        <a href="<?= $urlUpgrade ?>" target="_blank"
                           class="btn-acao btn-acao-locked w-100" title="Disponível nos planos Construtora e Empresarial">
                            <span class="badge-pro">Pro</span>
                            <i class="bi bi-graph-up-arrow"></i> Curva S
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Almoxarifado -->
                    <div class="col-4 col-sm-3">
                        <a href="index.php?acao=almoxarifado&id_obra=<?= $obra['id']; ?>"
                           class="btn-acao btn-acao-warning w-100">
                            <i class="bi bi-boxes"></i> Almoxar.
                        </a>
                    </div>

                    <!-- DDS -->
                    <div class="col-4 col-sm-3">
                        <a href="index.php?acao=dds&id_obra=<?= $obra['id']; ?>"
                           class="btn-acao btn-acao-secondary w-100">
                            <i class="bi bi-shield-check"></i> DDS
                        </a>
                    </div>

                    <!-- Não Conformidades -->
                    <div class="col-4 col-sm-3">
                        <?php if ($podeNC ?? false): ?>
                        <a href="index.php?acao=nao_conformidades&id_obra=<?= $obra['id']; ?>"
                           class="btn-acao btn-acao-danger w-100">
                            <i class="bi bi-exclamation-octagon"></i> NC
                        </a>
                        <?php else: ?>
                        <a href="<?= $urlUpgrade ?>" target="_blank"
                           class="btn-acao btn-acao-locked w-100" title="Disponível nos planos Construtora e Empresarial">
                            <span class="badge-pro">Pro</span>
                            <i class="bi bi-exclamation-octagon"></i> NC
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Documentos -->
                    <div class="col-4 col-sm-3">
                        <a href="index.php?acao=listar_documentos&id_obra=<?= $obra['id']; ?>"
                           class="btn-acao btn-acao-warning w-100">
                            <i class="bi bi-folder2-open"></i> Docs
                        </a>
                    </div>

                    <!-- Importar Orçamento -->
                    <?php if (in_array($nivel, ['admin', 'engenheiro'])): ?>
                    <div class="col-4 col-sm-3">
                        <button class="btn-acao btn-acao-success w-100"
                                data-bs-toggle="modal" data-bs-target="#modalImportarOrcamento"
                                data-idobra="<?= $obra['id']; ?>"
                                onclick="prepararImportacao(this)">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Orçamento
                        </button>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Rodapé admin: editar / excluir -->
            <?php if ($nivel === 'admin'): ?>
            <div class="obras-admin-footer px-3 pb-3 d-flex gap-2">
                <a href="index.php?acao=editar_obra&id=<?= $obra['id']; ?>"
                   class="btn-admin-sm btn btn-light border text-muted">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <a href="index.php?acao=excluir_obra&id=<?= $obra['id']; ?>&<?= $csrfToken ?>"
                   class="btn-admin-sm btn btn-light border text-danger"
                   onclick="return confirm('Excluir obra? Esta ação não pode ser desfeita.');">
                    <i class="bi bi-trash"></i> Excluir
                </a>
            </div>
            <?php endif; ?>

        </div><!-- /card-obra -->
    </div><!-- /col -->
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-building-slash fs-1 d-block mb-3 opacity-25"></i>
    <p class="mb-0">Nenhuma obra cadastrada ainda.</p>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- MODAL: Importar Orçamento                                   -->
<!-- ════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalImportarOrcamento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fs-5"><i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Importar Orçamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="index.php?acao=importar_orcamento" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_obra" id="idObraImportacao">
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2 text-dark">Modo de Importação</label>
                        <div class="form-check p-3 border rounded mb-2 bg-light">
                            <input class="form-check-input mt-2" type="radio" name="modo_importacao" id="modoAtualizar" value="atualizar" checked>
                            <label class="form-check-label" for="modoAtualizar">
                                <strong class="text-primary"><i class="bi bi-arrow-repeat"></i> Atualizar / Mesclar</strong>
                                <div class="small text-muted mt-1">Mantém vínculos existentes (Diários/Financeiro).</div>
                            </label>
                        </div>
                        <div class="form-check p-3 border rounded border-danger bg-danger bg-opacity-10">
                            <input class="form-check-input mt-2" type="radio" name="modo_importacao" id="modoSubstituir" value="substituir">
                            <label class="form-check-label" for="modoSubstituir">
                                <strong class="text-danger"><i class="bi bi-trash-fill"></i> Substituir Tudo</strong>
                                <div class="small text-danger mt-1">Apaga o orçamento atual e perde vínculos.</div>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Arquivo <span class="text-success">Excel (.xlsx)</span> ou CSV</label>
                        <input type="file" name="arquivo_csv" class="form-control" accept=".xlsx,.csv" required>
                    </div>
                    <h6 class="fw-bold text-dark mt-4 mb-3 border-bottom pb-2">Mapeamento (Colunas Excel)</h6>
                    <div class="row g-2">
                        <div class="col-4"><label class="form-label small text-muted">Código</label><input type="text" name="col_codigo" class="form-control text-center fw-bold" value="A" required></div>
                        <div class="col-4"><label class="form-label small text-muted">Item</label><input type="text" name="col_itemizacao" class="form-control text-center fw-bold" value="B" required></div>
                        <div class="col-4"><label class="form-label small text-muted">Descrição</label><input type="text" name="col_descricao" class="form-control text-center fw-bold" value="C" required></div>
                        <div class="col-4"><label class="form-label small text-muted">Unidade</label><input type="text" name="col_unidade" class="form-control text-center fw-bold" value="D" required></div>
                        <div class="col-4"><label class="form-label small text-muted">Qtd.</label><input type="text" name="col_qtd" class="form-control text-center fw-bold" value="E" required></div>
                        <div class="col-4"><label class="form-label small text-muted">Preço</label><input type="text" name="col_preco" class="form-control text-center fw-bold" value="F" required></div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label small text-muted fw-bold">Linha inicial dos dados <span class="text-secondary fw-normal">(pula cabeçalho)</span></label>
                        <input type="number" name="linha_inicio" class="form-control text-center fw-bold"
                               value="2" min="1" max="50" style="max-width:100px;"
                               title="Linha onde começam os itens do orçamento (geralmente linha 2, após o cabeçalho)">
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success fw-bold py-2">
                            <i class="bi bi-upload me-2"></i> Processar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function prepararImportacao(btn) {
        var id = btn.getAttribute('data-idobra');
        document.getElementById('idObraImportacao').value = id;
    }

    function filtrarObras(termo) {
        termo = termo.toLowerCase().trim();
        // Filtra linhas da tabela desktop
        document.querySelectorAll('table tbody tr').forEach(function(tr) {
            var texto = tr.innerText.toLowerCase();
            tr.style.display = (!termo || texto.includes(termo)) ? '' : 'none';
        });
        // Filtra cards mobile
        document.querySelectorAll('.card-obra-mobile').forEach(function(card) {
            var texto = card.innerText.toLowerCase();
            card.style.display = (!termo || texto.includes(termo)) ? '' : 'none';
        });
    }
</script>

<?php
$urlBase = 'index.php?acao=listar_obras';
include __DIR__ . '/../estruturas/paginacao.php';
include __DIR__ . '/../estruturas/rodape.php';
?>