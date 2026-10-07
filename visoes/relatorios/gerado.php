<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório - <?= htmlspecialchars($obra['nome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-white">

    <div class="container mt-4 mb-5">
        
        <div class="text-center mb-4 border-bottom pb-3">
            <h2>Relatório de Acompanhamento de Obra</h2>
            <h4 class="text-primary"><?= htmlspecialchars($obra['nome']); ?></h4>
            <p class="text-muted">
                Período: <strong><?= date('d/m/Y', strtotime($inicio)); ?></strong> até <strong><?= date('d/m/Y', strtotime($fim)); ?></strong>
            </p>
        </div>

        <div class="d-flex justify-content-end mb-4 no-print">
            <button onclick="window.print()" class="btn btn-dark me-2">Imprimir / Salvar PDF</button>
            <button onclick="window.close()" class="btn btn-secondary">Fechar</button>
        </div>

        <div class="row mb-5">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">Clima no Período (Manhã)</div>
                    <div class="card-body">
                        <div style="height: 250px; width: 100%; display: flex; justify-content: center;">
                            <canvas id="graficoClima"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">Resumo Executivo</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total de Dias Registrados
                                <span class="badge bg-primary rounded-pill"><?= count($dados); ?></span>
                            </li>
                            
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Dias de Sol / Ensolarado
                                <span class="badge bg-warning text-dark rounded-pill">
                                    <?= ($statsClima['Sol'] ?? 0) + ($statsClima['Ensolarado'] ?? 0); ?>
                                </span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Dias Nublados
                                <span class="badge bg-secondary rounded-pill">
                                    <?= $statsClima['Nublado'] ?? 0; ?>
                                </span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Dias de Chuva
                                <span class="badge bg-danger rounded-pill"><?= $statsClima['Chuva'] ?? 0; ?></span>
                            </li>
                            
                            <li class="list-group-item d-flex justify-content-between align-items-center mt-2 border-top pt-3">
                                <small class="text-muted">Engenheiro Responsável</small>
                                <span class="fw-bold"><?= $_SESSION['usuario_nome'] ?? 'Admin'; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-4 mb-3">Detalhamento dos Diários</h5>
        <table class="table table-striped table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th width="15%">Data</th>
                    <th width="20%">Clima (M / T)</th>
                    <th>Atividades/Observações Principais</th>
                    <th width="10%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($dados) > 0): ?>
                    <?php foreach($dados as $d): ?>
                        <tr>
                            <td class="fw-bold"><?= date('d/m/Y', strtotime($d['data_diario'])); ?></td>
                            <td>
                                <?php 
                                    $climaManha = ucwords(str_replace('_', ' ', $d['clima_manha']));
                                    $climaTarde = ucwords(str_replace('_', ' ', $d['clima_tarde']));
                                    
                                    if(strtolower($d['clima_manha']) == 'impraticavel') $climaManha = "Chuva Forte";
                                    if(strtolower($d['clima_tarde']) == 'impraticavel') $climaTarde = "Chuva Forte";
                                ?>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-light text-dark border me-2" style="width: 25px;">M</span> 
                                    <?= $climaManha; ?>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark border me-2" style="width: 25px;">T</span> 
                                    <?= $climaTarde; ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    $texto = $d['observacoes_gerais'] ?? $d['atividades_executadas'] ?? '';
                                ?>
                                <small><?= nl2br(mb_substr($texto, 0, 300)); ?>...</small>
                            </td>
                            <td class="text-center align-middle">
                                <?php if($d['status'] == 'assinado'): ?>
                                    <span class="badge bg-success">Assinado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Rascunho</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Nenhum registro encontrado neste período.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-5 text-center text-muted small">
            <p>Relatório gerado automaticamente pelo Sistema CHRONA em <?= date('d/m/Y H:i'); ?></p>
        </div>

    </div>

    <script>
        const solTotal = <?= ($statsClima['Sol'] ?? 0) + ($statsClima['Ensolarado'] ?? 0) ?>;
        const nubladoTotal = <?= ($statsClima['Nublado'] ?? 0) ?>;
        const chuvaTotal = <?= ($statsClima['Chuva'] ?? 0) ?>;

        const climaData = [solTotal, nubladoTotal, chuvaTotal];
        
        new Chart(document.getElementById('graficoClima'), {
            type: 'doughnut',
            data: {
                labels: ['Sol/Ensolarado', 'Nublado', 'Chuva'],
                datasets: [{
                    data: climaData,
                    // Cores combinando com os Badges: Amarelo (Sol), Cinza (Nublado), Vermelho (Chuva - Atenção) ou Azul (Chuva - Água)
                    // No badge usei Danger (Vermelho) para Chuva para chamar atenção de atraso, mas no gráfico Azul fica mais semântico com clima.
                    backgroundColor: ['#ffc107', '#6c757d', '#0d6efd'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>