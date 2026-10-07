<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório - <?= isset($obra['nome']) ? htmlspecialchars($obra['nome']) : 'Documento'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS Específico para Impressão */
        @media print {
            body { 
                font-size: 11pt; 
                background: white; 
                /* Força a impressão de cores de fundo (bg-primary, etc) */
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
            }
            .no-print { 
                display: none !important; 
            }
            .page-break { 
                page-break-before: always; 
            }
            .card { 
                border: 1px solid #ddd !important; 
                break-inside: avoid;
            }
            /* Remove sombras e links na impressão */
            .shadow, .shadow-sm { box-shadow: none !important; }
            a { text-decoration: none; color: black; }
        }
        
        /* Estilos de Tela */
        .capa { 
            height: 280mm; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            text-align: center; 
        }
        .foto-grid { 
            height: 180px; 
            width: 100%; 
            object-fit: cover; 
            border-radius: 4px; 
        }
    </style>
</head>
<body class="bg-light">

    <div class="container mt-3 mb-3 no-print">
        <div class="alert alert-info py-2 mb-2 small">
            <i class="bi bi-info-circle"></i> Dica: Nas configurações de impressão, marque a opção <strong>"Gráficos de Plano de Fundo"</strong> para sair colorido.
        </div>
        <button onclick="window.print()" class="btn btn-primary btn-lg shadow">🖨️ Imprimir / Salvar PDF</button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg shadow ms-2">Fechar</button>
    </div>

    <div class="container bg-white shadow-sm p-5 mb-4" style="min-height: 297mm;">
        
        <div class="text-center mb-5 pt-5">
            <h1 class="display-4 fw-bold">RELATÓRIO DE ACOMPANHAMENTO</h1>
            <h3 class="text-secondary text-uppercase mt-3"><?= isset($obra['nome']) ? htmlspecialchars($obra['nome']) : 'Obra Não Identificada'; ?></h3>
            <p class="text-muted mt-2"><?= isset($obra['endereco']) ? htmlspecialchars($obra['endereco']) : ''; ?></p>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <table class="table table-bordered">
                    <tr>
                        <th class="bg-light" width="40%">Período Analisado:</th>
                        <td>
                            <?= isset($_POST['data_inicio']) ? date('d/m/Y', strtotime($_POST['data_inicio'])) : '-'; ?> 
                            até 
                            <?= isset($_POST['data_fim']) ? date('d/m/Y', strtotime($_POST['data_fim'])) : '-'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Responsável Técnico:</th>
                        <td><?= isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Não informado'; ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Data de Emissão:</th>
                        <td><?= date('d/m/Y H:i'); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <h4 class="mt-5 border-bottom pb-2">1. Resumo Financeiro do Período</h4>
        
        <?php if(isset($financeiro)): ?>
        <div class="row mt-3 text-center">
            <div class="col-4">
                <div class="border rounded p-3 bg-light h-100">
                    <small class="text-muted d-block mb-2">Entradas (Medições)</small>
                    <h4 class="text-success fw-bold">R$ <?= number_format($financeiro['receitas'], 2, ',', '.'); ?></h4>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-3 bg-light h-100">
                    <small class="text-muted d-block mb-2">Saídas (Despesas)</small>
                    <h4 class="text-danger fw-bold">R$ <?= number_format($financeiro['despesas'], 2, ',', '.'); ?></h4>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-3 bg-light h-100">
                    <small class="text-muted d-block mb-2">Resultado</small>
                    <h4 class="<?= $financeiro['saldo'] >= 0 ? 'text-primary' : 'text-danger'; ?> fw-bold">
                        R$ <?= number_format($financeiro['saldo'], 2, ',', '.'); ?>
                    </h4>
                </div>
            </div>
        </div>
        <?php else: ?>
            <p class="text-muted text-center">Dados financeiros indisponíveis.</p>
        <?php endif; ?>
        
        <div class="alert alert-light border mt-4 text-center small text-muted">
            * Este resumo contempla apenas as movimentações com data de competência dentro do período selecionado.
        </div>
    </div>

    <?php if(isset($diarios) && count($diarios) > 0): ?>
    <div class="page-break"></div>
    <div class="container bg-white shadow-sm p-5 mb-4" style="min-height: 297mm;">
        <h4 class="border-bottom pb-2 mb-4">2. Resumo das Atividades (Diário de Obras)</h4>
        
        <div class="table-responsive">
            <table class="table table-striped table-sm border align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="12%">Data</th>
                        <th width="15%">Clima</th>
                        <th>Atividades Executadas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($diarios as $d): ?>
                        <tr>
                            <td class="fw-bold"><?= date('d/m/Y', strtotime($d['data_diario'])); ?></td>
                            <td class="small">
                                <div>M: <?= ucfirst($d['clima_manha']); ?></div>
                                <div>T: <?= ucfirst($d['clima_tarde']); ?></div>
                            </td>
                            <td class="small text-justify">
                                <?= nl2br(htmlspecialchars(substr($d['atividades_executadas'], 0, 300))); ?>
                                <?= strlen($d['atividades_executadas']) > 300 ? '...' : ''; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if(isset($fotos) && count($fotos) > 0): ?>
        <div class="page-break"></div>
        <div class="container bg-white shadow-sm p-5 mb-4" style="min-height: 297mm;">
            <h4 class="border-bottom pb-2 mb-4">3. Registro Fotográfico</h4>
            <div class="row g-3">
                <?php foreach($fotos as $f): ?>
                    <div class="col-4 mb-3">
                        <div class="border p-2 rounded h-100 bg-light text-center">
                            <img src="uploads/<?= $f['caminho_arquivo']; ?>" class="foto-grid border mb-2">
                            <div class="small fw-bold text-dark">
                                <?= date('d/m/Y', strtotime($f['data_diario'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>