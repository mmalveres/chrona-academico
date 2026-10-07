<?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clipboard2-check-fill me-2 text-primary"></i>
            Relatório de Visita Fiscal
        </h4>
        <small class="text-muted">
            <?= htmlspecialchars($relatorio['nome_obra'] ?? '') ?> &mdash;
            <?= date('d/m/Y', strtotime($relatorio['data_visita'])) ?>
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?acao=relatorio_fiscal_imprimir&id=<?= $relatorio['id'] ?>"
           class="btn btn-outline-secondary btn-sm" target="_blank">
            <i class="bi bi-printer me-1"></i>Imprimir
        </a>
        <a href="index.php?acao=relatorio_fiscal" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'criado'): ?>
<div class="alert alert-success border-0 py-2 mb-3">
    <i class="bi bi-check-circle-fill me-2"></i>Relatório criado com sucesso.
</div>
<?php endif; ?>

<div class="row g-4">

    <div class="col-lg-8">

        <!-- Cabeçalho do relatório -->
        <div class="card-custom mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3 pb-3"
                 style="border-bottom:2px solid #e5e7eb;">
                <div>
                    <div class="text-muted small mb-1" style="letter-spacing:1px;text-transform:uppercase;font-weight:700;">
                        Relatório de Visita de Fiscalização
                    </div>
                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($relatorio['orgao']) ?></h5>
                </div>
                <?php if (!empty($relatorio['logo_orgao'])): ?>
                <img src="assets/img/<?= htmlspecialchars($relatorio['logo_orgao']) ?>"
                     alt="Logo do Órgão" style="max-height:60px;max-width:120px;object-fit:contain;">
                <?php endif; ?>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="small text-muted fw-bold">OBRA INSPECIONADA</div>
                    <div class="fw-bold"><?= htmlspecialchars($relatorio['nome_obra'] ?? '—') ?></div>
                    <?php if (!empty($relatorio['cidade'])): ?>
                    <div class="text-muted small"><?= htmlspecialchars($relatorio['cidade']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted fw-bold">DATA DA VISITA</div>
                    <div class="fw-bold"><?= date('d/m/Y', strtotime($relatorio['data_visita'])) ?></div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted fw-bold">RESULTADO</div>
                    <?php if ($relatorio['status'] === 'conforme'): ?>
                    <span class="badge bg-success bg-opacity-75 fs-6 py-1 px-2">
                        <i class="bi bi-check-circle me-1"></i>Conforme
                    </span>
                    <?php else: ?>
                    <span class="badge bg-danger bg-opacity-75 fs-6 py-1 px-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>Não Conforme
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted fw-bold">FISCAL RESPONSÁVEL</div>
                    <div><?= htmlspecialchars($relatorio['nome_fiscal'] ?? '—') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted fw-bold">ÓRGÃO</div>
                    <div><?= htmlspecialchars($relatorio['orgao_fiscalizador'] ?? $relatorio['orgao']) ?></div>
                </div>
            </div>
        </div>

        <!-- Observações -->
        <?php if (!empty($relatorio['observacoes'])): ?>
        <div class="card-custom mb-4">
            <h6 class="fw-bold mb-3 pb-2" style="border-bottom:2px solid #e5e7eb;">
                <i class="bi bi-pencil-square me-2 text-primary"></i>Observações e Apontamentos
            </h6>
            <div style="white-space:pre-wrap;line-height:1.8;font-size:.92rem;">
                <?= htmlspecialchars($relatorio['observacoes']) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fotos -->
        <?php if (!empty($fotos)): ?>
        <div class="card-custom">
            <h6 class="fw-bold mb-3 pb-2" style="border-bottom:2px solid #e5e7eb;">
                <i class="bi bi-images me-2 text-primary"></i>Registro Fotográfico
                <span class="badge bg-light text-dark fw-normal ms-1"><?= count($fotos) ?> foto<?= count($fotos) > 1 ? 's' : '' ?></span>
            </h6>
            <div class="row g-3">
                <?php foreach ($fotos as $foto): ?>
                <div class="col-md-4 col-6">
                    <a href="<?= htmlspecialchars($foto['caminho']) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($foto['caminho']) ?>"
                             class="img-fluid rounded shadow-sm"
                             style="width:100%;height:160px;object-fit:cover;cursor:zoom-in;">
                    </a>
                    <?php if (!empty($foto['legenda'])): ?>
                    <p class="text-muted small mt-1 mb-0 text-center"><?= htmlspecialchars($foto['legenda']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Ações laterais -->
    <div class="col-lg-4">
        <div class="card-custom mb-3">
            <h6 class="fw-bold mb-3">Ações</h6>
            <div class="d-grid gap-2">
                <a href="index.php?acao=relatorio_fiscal_imprimir&id=<?= $relatorio['id'] ?>"
                   class="btn btn-outline-primary" target="_blank">
                    <i class="bi bi-file-pdf me-2"></i>Gerar PDF / Imprimir
                </a>
                <a href="index.php?acao=relatorio_fiscal_excluir&id=<?= $relatorio['id'] ?>&csrf=<?= \Aplicacao\Helpers\CsrfHelper::gerar() ?>"
                   class="btn btn-outline-danger"
                   onclick="return confirm('Excluir este relatório permanentemente?')">
                    <i class="bi bi-trash me-2"></i>Excluir Relatório
                </a>
            </div>
        </div>

        <div class="card-custom" style="background:#f8f9fa;">
            <div class="small text-muted fw-bold mb-1">REGISTRADO EM</div>
            <div class="small"><?= date('d/m/Y \à\s H:i', strtotime($relatorio['created_at'])) ?></div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
