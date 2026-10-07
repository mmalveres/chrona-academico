<?php 
// visoes/financeiro/geral.php
include __DIR__ . '/../estruturas/cabecalho.php'; 
?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--primary-color);">Financeiro Geral</h4>
            <p class="text-muted small fw-bold text-uppercase">Visão consolidada de todas as obras</p>
        </div>
    </div>

    <div class="row g-3">
        <?php if (!empty($obras)): foreach ($obras as $obra): 
            $corSaldo = $obra['saldo'] >= 0 ? 'text-success' : 'text-danger';
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($obra['nome']); ?></h6>
                        <span class="badge bg-light text-secondary border">ID: <?= $obra['id']; ?></span>
                    </div>
                    
                    <div class="row text-center mb-3">
                        <div class="col-6 border-end">
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Receitas</small>
                            <div class="text-success fw-bold">R$ <?= number_format($obra['receitas'], 2, ',', '.'); ?></div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Despesas</small>
                            <div class="text-danger fw-bold">R$ <?= number_format($obra['despesas'], 2, ',', '.'); ?></div>
                        </div>
                    </div>

                    <div class="bg-light p-2 rounded text-center mb-3">
                        <small class="text-muted fw-bold">SALDO ATUAL</small>
                        <h4 class="fw-bold <?= $corSaldo ?> mb-0">R$ <?= number_format($obra['saldo'], 2, ',', '.'); ?></h4>
                    </div>

                    <div class="d-grid">
                        <a href="index.php?acao=financeiro&id_obra=<?= $obra['id']; ?>" class="btn btn-outline-primary fw-bold btn-sm">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Abrir Fluxo de Caixa
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-building-slash fs-1 opacity-25"></i>
                <p class="mt-2">Nenhuma obra cadastrada.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>