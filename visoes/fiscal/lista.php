<?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-2">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-clipboard2-check-fill me-2 text-primary"></i>Relatórios de Visita Fiscal</h4>
        <small class="text-muted">Registros de inspeção e fiscalização de obras</small>
    </div>
    <a href="index.php?acao=relatorio_fiscal_novo" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Novo Relatório
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'excluido'): ?>
    <div class="alert alert-warning border-0 py-2 mb-3">
        <i class="bi bi-check-circle-fill me-2"></i>Relatório excluído com sucesso.
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (empty($relatorios)): ?>
<div class="card-custom text-center py-5">
    <i class="bi bi-clipboard2-x" style="font-size:2.5rem;color:var(--bs-secondary);opacity:.4;"></i>
    <p class="mt-3 text-muted">Nenhum relatório de visita registrado ainda.</p>
    <a href="index.php?acao=relatorio_fiscal_novo" class="btn btn-primary btn-sm mt-1">
        <i class="bi bi-plus-lg me-1"></i>Criar primeiro relatório
    </a>
</div>
<?php else: ?>

<!-- Desktop -->
<div class="card-custom d-none d-md-block p-0" style="overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.88rem;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th class="ps-3 py-2">Data da Visita</th>
                    <th>Obra</th>
                    <th>Órgão Fiscalizador</th>
                    <th><?= ($_SESSION['is_superadmin'] ?? false) ? 'Fiscal' : '' ?></th>
                    <th>Resultado</th>
                    <th class="pe-3 text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($relatorios as $r): ?>
            <tr>
                <td class="ps-3 text-nowrap fw-bold">
                    <?= date('d/m/Y', strtotime($r['data_visita'])) ?>
                </td>
                <td><?= htmlspecialchars($r['nome_obra'] ?? '—') ?></td>
                <td class="text-muted small"><?= htmlspecialchars($r['orgao_fiscalizador'] ?? $r['orgao']) ?></td>
                <td class="text-muted small">
                    <?php if ($_SESSION['is_superadmin'] ?? false): ?>
                        <?= htmlspecialchars($r['nome_fiscal'] ?? '') ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($r['status'] === 'conforme'): ?>
                        <span class="badge bg-success bg-opacity-75"><i class="bi bi-check-circle me-1"></i>Conforme</span>
                    <?php else: ?>
                        <span class="badge bg-danger bg-opacity-75"><i class="bi bi-exclamation-triangle me-1"></i>Não Conforme</span>
                    <?php endif; ?>
                </td>
                <td class="pe-3 text-end">
                    <div class="d-flex justify-content-end gap-1">
                        <a href="index.php?acao=relatorio_fiscal_ver&id=<?= $r['id'] ?>"
                           class="btn btn-sm btn-outline-primary" title="Visualizar">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="index.php?acao=relatorio_fiscal_imprimir&id=<?= $r['id'] ?>"
                           class="btn btn-sm btn-outline-secondary" title="Imprimir" target="_blank">
                            <i class="bi bi-printer"></i>
                        </a>
                        <a href="index.php?acao=relatorio_fiscal_excluir&id=<?= $r['id'] ?>&csrf=<?= \Aplicacao\Helpers\CsrfHelper::gerar() ?>"
                           class="btn btn-sm btn-outline-danger"
                           title="Excluir"
                           onclick="return confirm('Excluir este relatório? Esta ação não pode ser desfeita.')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile -->
<div class="d-md-none">
    <?php foreach ($relatorios as $r): ?>
    <div class="card-custom mb-2 p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="fw-bold"><?= htmlspecialchars($r['nome_obra'] ?? '—') ?></div>
                <small class="text-muted"><?= htmlspecialchars($r['orgao']) ?></small>
            </div>
            <span class="text-muted small"><?= date('d/m/Y', strtotime($r['data_visita'])) ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <?php if ($r['status'] === 'conforme'): ?>
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Conforme</span>
            <?php else: ?>
                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Não Conforme</span>
            <?php endif; ?>
            <div class="d-flex gap-1">
                <a href="index.php?acao=relatorio_fiscal_ver&id=<?= $r['id'] ?>"
                   class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                <a href="index.php?acao=relatorio_fiscal_imprimir&id=<?= $r['id'] ?>"
                   class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-printer"></i></a>
                <a href="index.php?acao=relatorio_fiscal_excluir&id=<?= $r['id'] ?>&csrf=<?= \Aplicacao\Helpers\CsrfHelper::gerar() ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Excluir?')"><i class="bi bi-trash"></i></a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
