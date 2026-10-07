<?php // visoes/superadmin/precos_planos.php ?>
<?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-2">
    <div>
        <h4 class="fw-bold mb-0" style="color:var(--text-dark);">
            <i class="bi bi-currency-dollar me-2 text-warning"></i>Preços dos Planos
        </h4>
        <p class="text-muted small mb-0">Alterações entram em vigor imediatamente no checkout e na página de vendas.</p>
    </div>
    <a href="index.php?acao=listar_empresas" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<?php if (!empty($sucesso)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($sucesso) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($erro)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($erro) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="index.php?acao=salvar_precos" id="formPrecos">

    <!-- Desconto anual global -->
    <div class="card-custom mb-4 p-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div>
                <label class="form-label fw-bold mb-1">
                    <i class="bi bi-tag-fill text-success me-1"></i> Desconto para plano anual
                </label>
                <p class="text-muted small mb-0">Aplicado a todos os planos. Altere o mensal → o anual recalcula automaticamente.</p>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <input type="number" id="desconto_global" name="desconto_global"
                       class="form-control text-center fw-bold"
                       style="width:90px;font-size:1.1rem;"
                       min="0" max="80" step="1"
                       value="<?= $descontoPct ?>">
                <span class="fw-bold fs-5 text-muted">%</span>
            </div>
        </div>
    </div>

    <!-- Cards dos planos -->
    <div class="row g-3 mb-4">
        <?php
        $planosConfig = [
            'autonomo'    => ['label' => 'Autônomo',    'icon' => 'person-fill',   'cor' => '#6c757d', 'badge' => 'secondary'],
            'construtora' => ['label' => 'Construtora', 'icon' => 'buildings-fill','cor' => '#0d6efd', 'badge' => 'primary'],
            'empresarial' => ['label' => 'Empresarial', 'icon' => 'trophy-fill',   'cor' => '#FFB300', 'badge' => 'warning'],
        ];
        foreach ($planosConfig as $key => $cfg):
            $p = $precos[$key];
        ?>
        <div class="col-md-4">
            <div class="card-custom h-100" style="border-top:3px solid <?= $cfg['cor'] ?>;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-<?= $cfg['badge'] ?> rounded-pill px-3 py-2 fw-bold">
                        <i class="bi bi-<?= $cfg['icon'] ?> me-1"></i><?= $cfg['label'] ?>
                    </span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase letter-spacing-1">
                        Preço Mensal (R$)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text fw-bold">R$</span>
                        <input type="number" step="0.01" min="1"
                               class="form-control form-control-lg fw-bold preco-mensal"
                               id="mensal_<?= $key ?>"
                               name="mensal_<?= $key ?>"
                               data-plano="<?= $key ?>"
                               value="<?= number_format($p['mensal'], 2, '.', '') ?>"
                               style="font-size:1.4rem;">
                    </div>
                </div>

                <div class="p-3 rounded-3" style="background:#f8f9fa;border:1px dashed #dee2e6;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted fw-bold">Preço Anual (por mês)</span>
                        <span class="badge bg-success" id="badge_desc_<?= $key ?>">-<?= $p['desc_pct'] ?>%</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <span class="text-muted" style="font-size:.9rem;">R$</span>
                        <span class="fw-bold" id="anual_display_<?= $key ?>"
                              style="font-size:1.6rem;color:var(--text-dark);">
                            <?= number_format($p['anual'], 2, ',', '.') ?>
                        </span>
                        <span class="text-muted small">/mês</span>
                    </div>
                    <div class="text-success small mt-1">
                        <i class="bi bi-piggy-bank-fill me-1"></i>
                        Economia de <strong id="economia_<?= $key ?>">R$ <?= number_format($p['economia'], 2, ',', '.') ?></strong>/ano
                    </div>
                    <div class="text-muted small">
                        Total anual: <strong id="total_anual_<?= $key ?>">R$ <?= number_format($p['anual_total'], 2, ',', '.') ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex gap-2 justify-content-end">
        <a href="index.php?acao=listar_empresas" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-5 fw-bold">
            <i class="bi bi-floppy-fill me-2"></i> Salvar Preços
        </button>
    </div>
</form>

<script>
const inputs  = document.querySelectorAll('.preco-mensal');
const descGlobal = document.getElementById('desconto_global');

function recalcularTudo() {
    const desc = parseFloat(descGlobal.value) || 0;
    inputs.forEach(inp => recalcular(inp, desc));
}

function recalcular(inp, desc) {
    const plano   = inp.dataset.plano;
    const mensal  = parseFloat(inp.value) || 0;
    const anual   = mensal * (1 - desc / 100);
    const total   = anual * 12;
    const econ    = (mensal - anual) * 12;

    document.getElementById('anual_display_' + plano).textContent =
        anual.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('badge_desc_' + plano).textContent = '-' + desc.toFixed(0) + '%';
    document.getElementById('economia_' + plano).textContent =
        'R$ ' + econ.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('total_anual_' + plano).textContent =
        'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

inputs.forEach(inp => inp.addEventListener('input', () => recalcular(inp, parseFloat(descGlobal.value) || 0)));
descGlobal.addEventListener('input', recalcularTudo);
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
