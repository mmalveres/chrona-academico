<?php
// visoes/dds/ver.php
/** @var array $obra      */
/** @var array $registro  */
include __DIR__ . '/../estruturas/cabecalho.php';

$idObra = (int)$obra['id'];
$nivel  = $_SESSION['nivel_acesso'] ?? '';
$podeExcluir = in_array($nivel, ['admin', 'engenheiro']);
?>

<div class="container-fluid mt-3 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="index.php?acao=listar_obras" class="text-decoration-none text-muted">Obras</a></li>
                    <li class="breadcrumb-item"><a href="index.php?acao=dds&id_obra=<?= $idObra ?>" class="text-decoration-none text-muted">DDS</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary"><?= date('d/m/Y', strtotime($registro['data'])) ?></li>
                </ol>
            </nav>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-shield-check me-2 text-primary"></i>
                DDS — <?= date('d/m/Y', strtotime($registro['data'])) ?>
            </h5>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-dark btn-sm fw-bold">
                <i class="bi bi-printer me-1"></i> Imprimir
            </button>
            <?php if ($podeExcluir): ?>
            <a href="index.php?acao=excluir_dds&id=<?= $registro['id'] ?>&id_obra=<?= $idObra ?>"
               class="btn btn-outline-danger btn-sm"
               onclick="return confirm('Excluir este DDS?')">
                <i class="bi bi-trash me-1"></i> Excluir
            </a>
            <?php endif; ?>
            <a href="index.php?acao=dds&id_obra=<?= $idObra ?>" class="btn btn-light border btn-sm">Voltar</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informações do DDS -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-info-circle me-2"></i>Informações
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted small fw-bold w-40">Data</th><td class="fw-bold"><?= date('d/m/Y', strtotime($registro['data'])) ?></td></tr>
                        <tr><th class="text-muted small fw-bold">Obra</th><td><?= htmlspecialchars($obra['nome']) ?></td></tr>
                        <tr><th class="text-muted small fw-bold">Responsável</th><td><?= htmlspecialchars($registro['nome_usuario'] ?? 'Sistema') ?></td></tr>
                        <tr><th class="text-muted small fw-bold">Presenças</th><td><span class="badge bg-primary"><?= count($registro['presenca']) ?></span></td></tr>
                    </table>
                    <?php if (!empty($registro['tema'])): ?>
                    <div class="mt-3">
                        <div class="small fw-bold text-muted mb-1">TEMA</div>
                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($registro['tema']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($registro['conteudo'])): ?>
                    <div class="mt-3">
                        <div class="small fw-bold text-muted mb-1">CONTEÚDO</div>
                        <div class="text-dark" style="white-space:pre-line"><?= htmlspecialchars($registro['conteudo']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Lista de presença -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-people-fill text-primary"></i>
                    Lista de Presença
                    <span class="badge bg-primary ms-1"><?= count($registro['presenca']) ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($registro['presenca'])): ?>
                    <div class="text-center py-4 text-muted small">Nenhuma presença registrada.</div>
                    <?php else: ?>
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted text-uppercase small">
                            <tr>
                                <th class="ps-4" style="width:40px">#</th>
                                <th>Nome</th>
                                <th>Função</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($registro['presenca'] as $i => $p): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($p['nome']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($p['funcao'] ?? '–') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, nav, .topbar, #sidebar-wrapper, .mobile-bottom-nav { display: none !important; }
    body { font-size: 11pt; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
