<?php
// visoes/relatorios/financeiro.php
$idEmpresa = $_SESSION['id_empresa'] ?? 0;
$caminhoLogo = __DIR__ . '/../../publico/assets/img/logo_empresa_' . $idEmpresa . '.png';
$urlLogo = 'assets/img/logo_empresa_' . $idEmpresa . '.png?' . time();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro - <?= htmlspecialchars($obra['nome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 10pt; }
            .no-print { display: none !important; }
            .card { border: 1px solid #ddd !important; }
            .bg-light { background-color: #f8f9fa !important; }
            .text-success { color: #198754 !important; }
            .text-danger { color: #dc3545 !important; }
        }
        .header-report { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .logo-img { max-height: 60px; }
    </style>
</head>
<body class="bg-white p-4">

    <div class="container mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
        <button onclick="window.close()" class="btn btn-secondary">Fechar</button>
    </div>

    <div class="container">
        
        <div class="header-report d-flex justify-content-between align-items-center">
            <div>
                <?php if(file_exists($caminhoLogo)): ?>
                    <img src="<?= $urlLogo; ?>" class="logo-img" alt="Logo">
                <?php else: ?>
                    <h2 class="fw-bold m-0">CHRONA</h2>
                <?php endif; ?>
            </div>
            <div class="text-end">
                <h4 class="mb-0">Relatório Financeiro</h4>
                <small class="text-muted">Período: <?= date('d/m/Y', strtotime($inicio)); ?> a <?= date('d/m/Y', strtotime($fim)); ?></small>
            </div>
        </div>

        <div class="card mb-4 border-0 bg-light">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Obra:</strong> <?= htmlspecialchars($obra['nome']); ?><br>
                        <strong>Endereço:</strong> <?= htmlspecialchars($obra['endereco']); ?>
                    </div>
                    <div class="col-md-6 text-end">
                        <strong>Emissão:</strong> <?= date('d/m/Y H:i'); ?><br>
                        <strong>Responsável:</strong> <?= $_SESSION['usuario_nome'] ?? 'Admin'; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row text-center mb-4">
            <div class="col-4">
                <div class="border rounded p-3">
                    <small class="text-muted text-uppercase">Total Receitas</small>
                    <h4 class="text-success fw-bold mb-0">R$ <?= number_format($totais['total_receitas'], 2, ',', '.'); ?></h4>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-3">
                    <small class="text-muted text-uppercase">Total Despesas</small>
                    <h4 class="text-danger fw-bold mb-0">R$ <?= number_format($totais['total_despesas'], 2, ',', '.'); ?></h4>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-3 bg-light">
                    <small class="text-muted text-uppercase">Saldo do Período</small>
                    <h4 class="<?= $totais['saldo'] >= 0 ? 'text-primary' : 'text-danger'; ?> fw-bold mb-0">
                        R$ <?= number_format($totais['saldo'], 2, ',', '.'); ?>
                    </h4>
                </div>
            </div>
        </div>

        <h5 class="border-bottom pb-2 mb-3">Detalhamento das Movimentações</h5>
        
        <?php if (count($movimentacoes) > 0): ?>
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Data</th>
                        <th>Descrição / Categoria</th>
                        <th>Tipo</th>
                        <th class="text-end">Valor (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($movimentacoes as $mov): ?>
                        <tr>
                            <td width="15%"><?= date('d/m/Y', strtotime($mov['data_movimentacao'])); ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($mov['descricao']); ?></div>
                                <small class="text-muted"><?= htmlspecialchars($mov['categoria']); ?></small>
                            </td>
                            <td width="10%" class="text-center">
                                <?php if($mov['tipo'] == 'receita'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success">Receita</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger">Despesa</span>
                                <?php endif; ?>
                            </td>
                            <td width="20%" class="text-end fw-bold <?= $mov['tipo'] == 'receita' ? 'text-success' : 'text-danger'; ?>">
                                <?= $mov['tipo'] == 'despesa' ? '-' : '+'; ?> 
                                R$ <?= number_format($mov['valor'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">SALDO FINAL:</td>
                        <td class="text-end <?= $totais['saldo'] >= 0 ? 'text-primary' : 'text-danger'; ?>">
                            R$ <?= number_format($totais['saldo'], 2, ',', '.'); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                Nenhuma movimentação financeira encontrada para este período.
            </div>
        <?php endif; ?>

        <div class="mt-5 pt-3 border-top text-center text-muted small">
            Relatório gerado pelo sistema CHRONA - Gestão de Obras
        </div>

    </div>
</body>
</html>