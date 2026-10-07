<?php
// visoes/nao_conformidades/listar.php
/** @var array $obra        */
/** @var array $ncs         */
/** @var array $resumo      */
/** @var array $usuarios    */
/** @var bool  $podeCriar   */
/** @var bool  $podeFechar  */
/** @var bool  $podeExcluir */
include __DIR__ . '/../estruturas/cabecalho.php';

$idObra = (int)$obra['id'];

$fStatus     = $_GET['status']     ?? '';
$fPrioridade = $_GET['prioridade'] ?? '';

$corPrioridade = ['alta' => 'danger', 'media' => 'warning', 'baixa' => 'secondary'];
$corStatus     = ['aberta' => 'danger', 'em_tratamento' => 'warning', 'fechada' => 'success'];
$labelStatus   = ['aberta' => 'Aberta', 'em_tratamento' => 'Em Tratamento', 'fechada' => 'Fechada'];
$proximoStatus = ['aberta' => 'em_tratamento', 'em_tratamento' => 'fechada'];
$labelProximo  = ['aberta' => 'Iniciar Tratamento', 'em_tratamento' => 'Fechar NC'];
?>
<style>
.nc-card { border-left: 4px solid; transition: box-shadow .15s; }
.nc-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1) !important; }
.nc-alta   { border-color: #dc3545; }
.nc-media  { border-color: #ffc107; }
.nc-baixa  { border-color: #6c757d; }
.nc-fechada{ opacity: .7; }
.fab-nc {
    position:fixed; bottom:90px; right:20px; width:56px; height:56px;
    background:#dc3545; color:#fff; border-radius:50%; display:flex;
    align-items:center; justify-content:center;
    box-shadow:0 4px 12px rgba(220,53,69,.4); font-size:24px; z-index:1000; border:none;
}
</style>

<div class="container-fluid mt-3 mb-5">

    <!-- ── Cabeçalho ── -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="index.php?acao=listar_obras" class="text-decoration-none text-muted">Obras</a></li>
                    <li class="breadcrumb-item active fw-bold text-danger">Não Conformidades</li>
                </ol>
            </nav>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i>
                Não Conformidades — <?= htmlspecialchars($obra['nome']) ?>
            </h5>
            <small class="text-muted">Registro e tratamento de ocorrências de qualidade</small>
        </div>
        <div class="d-none d-md-flex gap-2">
            <?php if ($podeCriar): ?>
            <button class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovaNC">
                <i class="bi bi-plus-lg me-1"></i> Nova NC
            </button>
            <?php endif; ?>
            <a href="index.php?acao=listar_obras" class="btn btn-light border shadow-sm">Voltar</a>
        </div>
    </div>

    <!-- ── Alertas ── -->
    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = ['sucesso'=>['success','check-circle-fill','NC registrada!'],
                       'status_atualizado'=>['info','arrow-repeat','Status atualizado.'],
                       'excluido'=>['warning','trash-fill','NC excluída.'],
                       'erro'=>['danger','exclamation-triangle-fill','Erro ao salvar.']];
              $m = $msgs[$_GET['msg']] ?? null; ?>
        <?php if ($m): ?><div class="alert alert-<?= $m[0] ?> border-0 shadow-sm py-2 mb-3"><i class="bi bi-<?= $m[1] ?> me-2"></i><?= $m[2] ?></div><?php endif; ?>
    <?php endif; ?>

    <!-- ── Cards resumo ── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3 <?= ($resumo['abertas'] ?? 0) > 0 ? 'border-start border-4 border-danger' : '' ?>">
                <div class="fw-bold fs-3 text-danger"><?= $resumo['abertas'] ?? 0 ?></div>
                <div class="small text-muted">Abertas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-3 text-warning"><?= $resumo['em_tratamento'] ?? 0 ?></div>
                <div class="small text-muted">Em Tratamento</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-3 text-success"><?= $resumo['fechadas'] ?? 0 ?></div>
                <div class="small text-muted">Fechadas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3 <?= ($resumo['alta_prioridade'] ?? 0) > 0 ? 'border-start border-4 border-danger' : '' ?>">
                <div class="fw-bold fs-3 <?= ($resumo['alta_prioridade'] ?? 0) > 0 ? 'text-danger' : 'text-secondary' ?>">
                    <?= $resumo['alta_prioridade'] ?? 0 ?>
                </div>
                <div class="small text-muted">Alta Prioridade (abertas)</div>
            </div>
        </div>
    </div>

    <!-- ── Filtros ── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="acao"    value="nao_conformidades">
                <input type="hidden" name="id_obra" value="<?= $idObra ?>">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="aberta"       <?= $fStatus==='aberta'       ? 'selected':'' ?>>Abertas</option>
                        <option value="em_tratamento"<?= $fStatus==='em_tratamento'? 'selected':'' ?>>Em Tratamento</option>
                        <option value="fechada"      <?= $fStatus==='fechada'      ? 'selected':'' ?>>Fechadas</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Prioridade</label>
                    <select name="prioridade" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="alta"  <?= $fPrioridade==='alta'  ? 'selected':'' ?>>Alta</option>
                        <option value="media" <?= $fPrioridade==='media' ? 'selected':'' ?>>Média</option>
                        <option value="baixa" <?= $fPrioridade==='baixa' ? 'selected':'' ?>>Baixa</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                        <i class="bi bi-funnel-fill me-1"></i> Filtrar
                    </button>
                    <?php if ($fStatus || $fPrioridade): ?>
                    <a href="index.php?acao=nao_conformidades&id_obra=<?= $idObra ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg"></i> Limpar
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Lista de NCs ── -->
    <?php if (empty($ncs)): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <i class="bi bi-check-circle display-1 text-success opacity-25"></i>
        <p class="mt-3 text-muted">Nenhuma não conformidade <?= $fStatus ? 'com esse status' : 'registrada' ?>.</p>
        <?php if ($podeCriar && !$fStatus): ?>
        <button class="btn btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#modalNovaNC">
            <i class="bi bi-plus-lg me-1"></i> Registrar NC
        </button>
        <?php endif; ?>
    </div>
    <?php else: ?>

    <div class="d-flex flex-column gap-3" id="listaNCs">
    <?php foreach ($ncs as $nc):
        $corBorda  = $corPrioridade[$nc['prioridade']] ?? 'secondary';
        $classFech = $nc['status'] === 'fechada' ? 'nc-fechada' : '';
    ?>
    <div class="card border-0 shadow-sm nc-card nc-<?= $nc['prioridade'] ?> <?= $classFech ?>">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge bg-<?= $corPrioridade[$nc['prioridade']] ?? 'secondary' ?>">
                            <?= ucfirst($nc['prioridade']) ?>
                        </span>
                        <span class="badge bg-<?= $corStatus[$nc['status']] ?? 'secondary' ?> bg-opacity-75">
                            <?= $labelStatus[$nc['status']] ?? $nc['status'] ?>
                        </span>
                        <?php if ($nc['total_fotos'] > 0): ?>
                        <span class="badge bg-light text-secondary border">
                            <i class="bi bi-camera me-1"></i><?= $nc['total_fotos'] ?> foto(s)
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($nc['titulo']) ?></div>
                    <?php if (!empty($nc['local'])): ?>
                    <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($nc['local']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($nc['descricao'])): ?>
                    <div class="small text-muted mt-1"><?= nl2br(htmlspecialchars(mb_strimwidth($nc['descricao'], 0, 120, '…'))) ?></div>
                    <?php endif; ?>
                    <div class="small text-muted mt-1 d-flex gap-3 flex-wrap">
                        <?php if ($nc['nome_responsavel']): ?>
                        <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($nc['nome_responsavel']) ?></span>
                        <?php endif; ?>
                        <?php if ($nc['prazo']): ?>
                        <span class="<?= strtotime($nc['prazo']) < time() && $nc['status'] !== 'fechada' ? 'text-danger fw-bold' : '' ?>">
                            <i class="bi bi-calendar me-1"></i>Prazo: <?= date('d/m/Y', strtotime($nc['prazo'])) ?>
                        </span>
                        <?php endif; ?>
                        <span><i class="bi bi-clock me-1"></i><?= date('d/m/Y', strtotime($nc['created_at'])) ?></span>
                    </div>
                </div>
                <div class="d-flex gap-1 flex-shrink-0 align-items-start">
                    <?php if ($podeFechar && $nc['status'] !== 'fechada' && isset($proximoStatus[$nc['status']])): ?>
                    <a href="index.php?acao=status_nc&id=<?= $nc['id'] ?>&id_obra=<?= $idObra ?>&status=<?= $proximoStatus[$nc['status']] ?>"
                       class="btn btn-sm btn-outline-<?= $corStatus[$proximoStatus[$nc['status']]] ?? 'primary' ?> fw-bold"
                       onclick="return confirm('<?= $labelProximo[$nc['status']] ?>?')">
                        <i class="bi bi-arrow-right-circle me-1"></i><?= $labelProximo[$nc['status']] ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($podeExcluir): ?>
                    <a href="index.php?acao=excluir_nc&id=<?= $nc['id'] ?>&id_obra=<?= $idObra ?>"
                       class="btn btn-sm btn-light text-danger rounded-circle"
                       onclick="return confirm('Excluir esta NC?')">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<?php if ($podeCriar): ?>
<button class="fab-nc d-md-none" data-bs-toggle="modal" data-bs-target="#modalNovaNC">
    <i class="bi bi-plus-lg"></i>
</button>

<!-- ── Modal Nova NC ── -->
<div class="modal fade" id="modalNovaNC" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-octagon me-2"></i>Registrar Não Conformidade</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="index.php?acao=salvar_nc" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_obra" value="<?= $idObra ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Título da NC</label>
                            <input type="text" name="titulo" class="form-control"
                                   placeholder="Ex: Armadura sem cobrimento adequado..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Local / Área</label>
                            <input type="text" name="local" class="form-control" placeholder="Ex: Laje do 2º pavimento">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Prioridade</label>
                            <select name="prioridade" class="form-select">
                                <option value="media">Média</option>
                                <option value="alta">Alta</option>
                                <option value="baixa">Baixa</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Prazo</label>
                            <input type="date" name="prazo" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="3"
                                      placeholder="Descreva o problema encontrado..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Responsável pela Correção</label>
                            <select name="id_responsavel" class="form-select">
                                <option value="">— A definir —</option>
                                <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Fotos (opcional)</label>
                            <input type="file" name="fotos[]" class="form-control" accept="image/*" multiple>
                            <div class="form-text">Selecione uma ou mais fotos.</div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-danger fw-bold py-3 rounded-3">
                            <i class="bi bi-exclamation-octagon me-2"></i>Registrar Não Conformidade
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
