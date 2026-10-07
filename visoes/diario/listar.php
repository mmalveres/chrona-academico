<?php
// visoes/diario/listar.php
/**
 * @var array $diarios
 * @var array $obra
 */
use Aplicacao\Modelos\Diario;
use Aplicacao\Helpers\CsrfHelper;
include __DIR__ . '/../estruturas/cabecalho.php';
$csrfToken = CsrfHelper::queryParam();

$nivelAcesso = $_SESSION['nivel_acesso'] ?? 'funcionario';
$podeEditar = in_array($nivelAcesso, ['admin', 'engenheiro', 'mestre']);
$podeAssinar = in_array($nivelAcesso, ['admin', 'engenheiro']); // Engenheiros e Admins podem assinar
$temDiariosAbertos = count(array_filter($diarios, fn($d) => $d['status'] !== 'assinado')) > 0;

if (!isset($diarioModel)) $diarioModel = new Diario();
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">

<style>
    .feed-card { background: #fff; border-radius: 16px; border: none; box-shadow: 0 2px 15px rgba(0,0,0,0.03); margin-bottom: 24px; overflow: hidden; }
    .feed-header { padding: 15px; display: flex; align-items: center; justify-content: space-between; }
    .feed-hero-img-container { width: 100%; height: 250px; overflow: hidden; position: relative; background-color: #f0f2f5; display: flex; align-items: center; justify-content: center; }
    .feed-hero-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .feed-hero-img:hover { transform: scale(1.02); }
    .feed-gallery-row { display: flex; gap: 2px; margin-top: 2px; }
    .feed-thumb { flex: 1; height: 80px; object-fit: cover; cursor: pointer; opacity: 0.9; }
    .more-photos-overlay { position: relative; flex: 1; height: 80px; background: #000; overflow: hidden; }
    .more-photos-overlay img { width: 100%; height: 100%; object-fit: cover; opacity: 0.5; }
    .more-photos-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-size: 1.2rem; }
    .feed-body { padding: 15px; }
    .feed-footer { padding: 10px 15px; border-top: 1px solid #f0f2f5; display: flex; justify-content: space-between; background: #fafafa; }
    .fab-add { position: fixed; bottom: 85px; right: 20px; width: 56px; height: 56px; border-radius: 50%; background: var(--accent-color); color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 1060; border: 3px solid #fff; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="index.php?acao=listar_obras" class="text-decoration-none text-muted">Obras</a></li>
                <li class="breadcrumb-item active fw-bold text-primary"><?= isset($obra['nome']) ? htmlspecialchars($obra['nome']) : 'Obra'; ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0" style="color: var(--primary-color);"><i class="bi bi-camera-reels me-2"></i>Diário de Obras</h4>
    </div>
    <div class="d-none d-md-flex gap-2">
        <button type="button" class="btn btn-light border shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalImprimirLote">
            <i class="bi bi-printer me-2"></i> Relatório PDF
        </button>
        <?php if ($podeAssinar && $temDiariosAbertos): ?>
            <a href="index.php?acao=assinar_lote&id_obra=<?= $obra['id']; ?>" class="btn btn-success fw-bold px-4 shadow-sm">
                <i class="bi bi-pen-fill me-2"></i> Assinar Todos
            </a>
        <?php endif; ?>
        <?php if ($podeEditar): ?>
            <a href="index.php?acao=preencher_diario&id_obra=<?= $obra['id']; ?>" class="btn btn-primary fw-bold px-4 shadow-sm"><i class="bi bi-plus-lg me-2"></i> Novo Registro</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="mb-4">
        <?php if ($_GET['msg'] == 'excluido'): ?><div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-trash-fill me-2"></i> Excluído com sucesso.</div><?php endif; ?>
        <?php if ($_GET['msg'] == 'sucesso_criacao'): ?><div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i> Criado com sucesso!</div><?php endif; ?>
        <?php if ($_GET['msg'] == 'sucesso_edicao'): ?><div class="alert alert-info border-0 shadow-sm"><i class="bi bi-pencil-fill me-2"></i> Atualizado!</div><?php endif; ?>
        <?php if ($_GET['msg'] == 'assinado'): ?><div class="alert alert-success border-0 shadow-sm"><i class="bi bi-patch-check-fill me-2"></i> Assinado com sucesso!</div><?php endif; ?>
        <?php if ($_GET['msg'] == 'assinado_lote'): ?><div class="alert alert-success border-0 shadow-sm"><i class="bi bi-patch-check-fill me-2"></i> Diários assinados em lote com sucesso!</div><?php endif; ?>
        <?php if ($_GET['msg'] == 'sem_pendentes'): ?><div class="alert alert-info border-0 shadow-sm"><i class="bi bi-info-circle-fill me-2"></i> Não há diários em aberto para assinar.</div><?php endif; ?>
        <?php if ($_GET['msg'] == 'reaberto'): ?><div class="alert alert-warning border-0 shadow-sm"><i class="bi bi-unlock-fill me-2"></i> Reaberto para edição.</div><?php endif; ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if (count($diarios) > 0): foreach ($diarios as $diario): 
            $isAssinado = ($diario['status'] == 'assinado');
            $fotos = $diarioModel->buscarMidias($diario['id']);
            $atividades = $diarioModel->buscarAtividades($diario['id']);
            $clima = strtolower($diario['clima_manha']);
            $iconeClima = ($clima == 'ensolarado') ? 'bi-sun-fill text-warning' : (($clima == 'chuvoso') ? 'bi-cloud-drizzle-fill text-primary' : 'bi-cloud-fill text-secondary');
            $autorNome = isset($diario['responsavel_nome']) ? $diario['responsavel_nome'] : 'Usuário';
        ?>
            <div class="feed-card">
                <div class="feed-header">
                    <div class="d-flex align-items-center">
                        <div class="bg-light border rounded-3 text-center py-1 px-2 me-3">
                            <div class="fw-bold text-dark lh-1" style="font-size: 1.4rem;"><?= date('d', strtotime($diario['data_diario'])); ?></div>
                            <div class="text-uppercase text-secondary fw-bold" style="font-size: 0.65rem;"><?= date('M', strtotime($diario['data_diario'])); ?></div>
                        </div>
                        <div>
                            <div class="fw-bold text-dark"><?= explode(' ', $autorNome)[0]; ?></div>
                            <div class="text-muted small"><i class="bi <?= $iconeClima; ?> me-1"></i> <?= ucfirst($clima); ?></div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="index.php?acao=imprimir_diario&id=<?= $diario['id']; ?>" target="_blank"><i class="bi bi-file-pdf me-2"></i> Gerar PDF</a></li>
                            
                            <?php if ($podeEditar && !$isAssinado): ?>
                                <li><a class="dropdown-item" href="index.php?acao=editar_diario&id=<?= $diario['id']; ?>"><i class="bi bi-pencil me-2"></i> Editar</a></li>
                            <?php endif; ?>
                            
                            <?php if ($nivelAcesso === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="index.php?acao=excluir_diario&id=<?= $diario['id']; ?>&<?= $csrfToken ?>" onclick="return confirm('Excluir?');"><i class="bi bi-trash me-2"></i> Excluir</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <?php if (count($fotos) > 0): ?>
                    <div class="feed-hero-img-container">
                        <a href="uploads/<?= $fotos[0]['caminho_arquivo']; ?>" data-lightbox="diario-<?= $diario['id']; ?>">
                            <img src="uploads/<?= $fotos[0]['caminho_arquivo']; ?>" class="feed-hero-img" onerror="this.style.display='none'">
                        </a>
                    </div>
                    <?php if (count($fotos) > 1): ?>
                        <div class="feed-gallery-row">
                            <?php for($i=1; $i < count($fotos); $i++): if($i > 3) break; ?>
                                <a href="uploads/<?= $fotos[$i]['caminho_arquivo']; ?>" data-lightbox="diario-<?= $diario['id']; ?>" style="flex:1;">
                                    <img src="uploads/<?= $fotos[$i]['caminho_arquivo']; ?>" class="feed-thumb" style="width:100%;" onerror="this.style.display='none'">
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="bg-light text-center py-4 border-top border-bottom"><i class="bi bi-image text-muted fs-1 opacity-25"></i><div class="text-muted small mt-2">Sem fotos</div></div>
                <?php endif; ?>

                <div class="feed-body">
                    <?php if(!empty($atividades)): ?>
                        <div class="mb-2">
                            <?php foreach($atividades as $idx => $ativ): 
                                // Limita a 3 atividades na prévia
                                if($idx > 2) break; 
                                
                                // --- LÓGICA CORRIGIDA: HÍBRIDO (Orçamento vs Manual) ---
                                $temVinculo = !empty($ativ['nome_servico_orcamento']);
                                $nomeOrcamento = $ativ['nome_servico_orcamento'] ?? '';
                                // Pega a descrição manual (que pode estar em 'descricao_manual' ou 'descricao')
                                $nomeManual = $ativ['descricao_manual'] ?? ($ativ['descricao'] ?? '');

                                // Se tiver vínculo com orçamento, usa o nome do orçamento.
                                // Se não, usa o texto manual.
                                $textoPrincipal = $temVinculo ? $nomeOrcamento : $nomeManual;
                                
                                // Se tiver código de referência (ex: 1.2.3) E for do orçamento
                                if(isset($ativ['codigo_referencia']) && $temVinculo) {
                                    $textoPrincipal = "<small class='text-muted me-1 fw-bold'>[{$ativ['codigo_referencia']}]</small> " . $textoPrincipal;
                                }
                                
                                // Complemento: Se for orçamento, mas o usuário digitou algo extra na descrição manual
                                $complemento = "";
                                if ($temVinculo && !empty($nomeManual) && $nomeManual != $nomeOrcamento) {
                                    $complemento = " <span class='text-muted small fst-italic'>({$nomeManual})</span>";
                                }
                            ?>
                                <div class="d-flex align-items-start mb-2 border-bottom pb-1">
                                    <i class="bi bi-check2-circle text-primary me-2 mt-1"></i>
                                    <div class="flex-grow-1 lh-sm">
                                        <span class="text-dark"><?= $textoPrincipal; ?></span>
                                        <?= $complemento; ?>
                                    </div>
                                    
                                    <?php if(!empty($ativ['quantidade']) && $ativ['quantidade'] > 0): ?>
                                        <span class="badge bg-light text-secondary border ms-2 small text-nowrap">
                                            <?= number_format($ativ['quantidade'], 2, ',', '.'); ?> <?= $ativ['unidade'] ?? ''; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if(count($atividades) > 3): ?>
                                <div class="text-center mt-2">
                                    <span class="badge rounded-pill bg-light text-muted border">
                                        + <?= count($atividades)-3; ?> atividades...
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted small fst-italic p-2 bg-light rounded">
                            <?= !empty($diario['atividades_executadas']) ? nl2br(htmlspecialchars($diario['atividades_executadas'])) : 'Nenhuma atividade registrada.'; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="feed-footer">
                    <?php if ($podeAssinar && !$isAssinado): ?>
                        <a href="index.php?acao=assinar&id=<?= $diario['id']; ?>" class="btn btn-success flex-fill me-2 fw-bold shadow-sm"><i class="bi bi-pen-fill me-2"></i> Assinar</a>
                    <?php endif; ?>
                    
                    <?php if ($isAssinado && in_array($nivelAcesso, ['admin'])): ?>
                        <a href="index.php?acao=reabrir_diario&id=<?= $diario['id']; ?>&<?= $csrfToken ?>" class="btn btn-outline-warning text-dark flex-fill me-2 fw-bold" onclick="return confirm('Reabrir?');"><i class="bi bi-unlock me-2"></i> Reabrir</a>
                    <?php endif; ?>

                    <a href="index.php?acao=imprimir_diario&id=<?= $diario['id']; ?>" target="_blank" class="btn btn-light border text-secondary fw-bold" style="min-width: 60px;"><i class="bi bi-file-pdf fs-5"></i></a>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="text-center py-5"><p class="text-muted">Nenhum diário encontrado.</p></div>
        <?php endif; ?>
        <div style="height: 100px;"></div>
    </div>
</div>

<?php if ($podeEditar): ?><a href="index.php?acao=preencher_diario&id_obra=<?= $obra['id']; ?>" class="fab-add d-md-none"><i class="bi bi-plus-lg"></i></a><?php endif; ?>

<div class="modal fade" id="modalImprimirLote" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-file-earmark-pdf me-2"></i> Relatório Completo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="index.php?acao=imprimir_diarios_lote" method="POST" target="_blank">
                    <input type="hidden" name="id_obra" value="<?= $obra['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Período</label>
                        <div class="input-group">
                            <input type="date" name="data_inicio" class="form-control" required>
                            <span class="input-group-text bg-light">até</span>
                            <input type="date" name="data_fim" class="form-control" required value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3">Baixar PDF</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<?php include __DIR__ . '/../estruturas/rodape.php'; ?>