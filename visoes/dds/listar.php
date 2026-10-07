<?php
// visoes/dds/listar.php
/** @var array $obra        */
/** @var array $registros   */
/** @var array $resumo      */
/** @var bool  $podeCriar   */
/** @var bool  $podeExcluir */
include __DIR__ . '/../estruturas/cabecalho.php';

$idObra = (int)$obra['id'];
?>
<style>
.dds-card { border-left: 4px solid #0d6efd; transition: box-shadow .15s; }
.dds-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08) !important; }
.fab-dds {
    position:fixed; bottom:90px; right:20px; width:56px; height:56px;
    background:#0d6efd; color:#fff; border-radius:50%; display:flex;
    align-items:center; justify-content:center;
    box-shadow:0 4px 12px rgba(13,110,253,.4); font-size:24px; z-index:1000; border:none;
}
.presenca-row input { font-size:.85rem; }
</style>

<div class="container-fluid mt-3 mb-5">

    <!-- ── Cabeçalho ── -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="index.php?acao=listar_obras" class="text-decoration-none text-muted">Obras</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary">DDS</li>
                </ol>
            </nav>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-shield-check me-2 text-primary"></i>
                DDS — <?= htmlspecialchars($obra['nome']) ?>
            </h5>
            <small class="text-muted">Diálogo Diário de Segurança (NR-18)</small>
        </div>
        <div class="d-none d-md-flex gap-2">
            <?php if ($podeCriar): ?>
            <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoDDS">
                <i class="bi bi-plus-lg me-1"></i> Novo DDS
            </button>
            <?php endif; ?>
            <a href="index.php?acao=listar_obras" class="btn btn-light border shadow-sm">Voltar</a>
        </div>
    </div>

    <!-- ── Alertas ── -->
    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = ['sucesso'=>['success','check-circle-fill','DDS salvo com sucesso!'],
                       'excluido'=>['warning','trash-fill','DDS excluído.'],
                       'erro'=>['danger','exclamation-triangle-fill','Erro ao salvar.']];
              $m = $msgs[$_GET['msg']] ?? null; ?>
        <?php if ($m): ?><div class="alert alert-<?= $m[0] ?> border-0 shadow-sm py-2 mb-3"><i class="bi bi-<?= $m[1] ?> me-2"></i><?= $m[2] ?></div><?php endif; ?>
    <?php endif; ?>

    <!-- ── Cards resumo ── -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-3 text-primary"><?= $resumo['total_dds'] ?></div>
                <div class="small text-muted">DDS realizados</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-3 text-success"><?= $resumo['dias_com_dds'] ?></div>
                <div class="small text-muted">dias cobertos</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-3 text-warning"><?= $resumo['total_presencas'] ?></div>
                <div class="small text-muted">presenças registradas</div>
            </div>
        </div>
    </div>

    <!-- ── Lista de DDS ── -->
    <?php if (empty($registros)): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <i class="bi bi-shield-check display-1 text-muted opacity-25"></i>
        <p class="mt-3 text-muted">Nenhum DDS registrado ainda.</p>
        <?php if ($podeCriar): ?>
        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalNovoDDS">
            <i class="bi bi-plus-lg me-1"></i> Registrar primeiro DDS
        </button>
        <?php endif; ?>
    </div>
    <?php else: ?>

    <div class="d-flex flex-column gap-3" id="listaDDS">
    <?php foreach ($registros as $r): ?>
    <div class="card border-0 shadow-sm dds-card">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary rounded-pill"><?= date('d/m/Y', strtotime($r['data'])) ?></span>
                        <span class="badge bg-light text-secondary border">
                            <i class="bi bi-people-fill me-1"></i><?= $r['total_presentes'] ?> presente(s)
                        </span>
                    </div>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($r['tema']) ?></div>
                    <div class="small text-muted mt-1">
                        Registrado por: <?= htmlspecialchars($r['nome_usuario'] ?? 'Sistema') ?>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="index.php?acao=ver_dds&id=<?= $r['id'] ?>&id_obra=<?= $idObra ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> Ver
                    </a>
                    <?php if ($podeExcluir): ?>
                    <a href="index.php?acao=excluir_dds&id=<?= $r['id'] ?>&id_obra=<?= $idObra ?>"
                       class="btn btn-sm btn-light text-danger rounded-circle"
                       onclick="return confirm('Excluir este DDS e lista de presença?')" title="Excluir">
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
<button class="fab-dds d-md-none" data-bs-toggle="modal" data-bs-target="#modalNovoDDS">
    <i class="bi bi-plus-lg"></i>
</button>

<!-- ── Modal novo DDS ── -->
<div class="modal fade" id="modalNovoDDS" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2"></i>Novo DDS</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="index.php?acao=salvar_dds" method="POST">
                    <input type="hidden" name="id_obra" value="<?= $idObra ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Data</label>
                            <input type="date" name="data" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-muted">Tema / Assunto</label>
                            <input type="text" name="tema" list="listaTemas" class="form-control"
                                   placeholder="Ex: Uso correto de EPI, Trabalho em altura..." required>
                            <datalist id="listaTemas">
                                <option value="Uso correto de EPI">
                                <option value="Prevenção de quedas — Trabalho em altura">
                                <option value="Ordem e limpeza no canteiro">
                                <option value="Manuseio de materiais pesados">
                                <option value="Prevenção de incêndio">
                                <option value="Sinalização e isolamento de área">
                                <option value="Uso seguro de ferramentas elétricas">
                                <option value="Hidratação e exposição ao sol">
                                <option value="Prevenção de acidentes com veículos">
                            </datalist>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Conteúdo / Observações</label>
                            <textarea name="conteudo" class="form-control" rows="3"
                                      placeholder="Descreva o conteúdo abordado no DDS..."></textarea>
                        </div>

                        <!-- Lista de presença -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold text-muted mb-0">Lista de Presença</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="adicionarLinha()">
                                    <i class="bi bi-plus-lg me-1"></i> Adicionar
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0" id="tabelaPresenca">
                                    <thead class="bg-light small text-muted text-uppercase">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Função</th>
                                            <th style="width:40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="corpoPresenca">
                                        <tr class="presenca-row">
                                            <td><input type="text" name="presenca[0][nome]" class="form-control form-control-sm" placeholder="Nome completo"></td>
                                            <td><input type="text" name="presenca[0][funcao]" class="form-control form-control-sm" placeholder="Pedreiro, Servente..."></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary fw-bold py-3 rounded-3">
                            <i class="bi bi-check-lg me-2"></i>Salvar DDS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
let linhaIdx = 1;
function adicionarLinha() {
    const tbody = document.getElementById('corpoPresenca');
    const tr = document.createElement('tr');
    tr.className = 'presenca-row';
    tr.innerHTML = `
        <td><input type="text" name="presenca[${linhaIdx}][nome]" class="form-control form-control-sm" placeholder="Nome completo"></td>
        <td><input type="text" name="presenca[${linhaIdx}][funcao]" class="form-control form-control-sm" placeholder="Função"></td>
        <td><button type="button" class="btn btn-sm btn-light text-danger" onclick="this.closest('tr').remove()">
            <i class="bi bi-x-lg"></i></button></td>`;
    tbody.appendChild(tr);
    linhaIdx++;
}
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
