<?php
// visoes/obras/imprimir_medicao.php

// 1. Configurações Iniciais e Logo
$idEmpresa = $_SESSION['id_empresa'] ?? 0;
$caminhoLogo = __DIR__ . '/../../publico/assets/img/logo_empresa_' . $idEmpresa . '.png';
$urlLogo = 'assets/img/logo_empresa_' . $idEmpresa . '.png?' . time();
$temLogo = file_exists($caminhoLogo);

// 2. Filtros de Período (Para exibição no cabeçalho)
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');
$meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
$nomeMes = $meses[(int)$mes] ?? '-';

// 3. Cálculos de Totais (Rodamos o loop antes para preencher o cabeçalho)
$totalContrato = 0;
$totalAnterior = 0;
$totalPeriodo = 0; // Valor a faturar neste boletim

if (!empty($itensMedicao)) {
    foreach ($itensMedicao as $item) {
        if ($item['tipo'] !== 'etapa') {
            $preco = (float) $item['preco_unitario'];
            $totalContrato += (float) $item['qtd_orcada'] * $preco;
            $totalAnterior += (float) $item['qtd_anterior'] * $preco; // Calculado no SQL
            $totalPeriodo  += (float) $item['qtd_periodo'] * $preco;  // Calculado no SQL
        }
    }
}

$totalAcumulado = $totalAnterior + $totalPeriodo;
$percFisico = ($totalContrato > 0) ? ($totalAcumulado / $totalContrato) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>BM - <?= $nomeMes; ?>/<?= $ano; ?> - <?= htmlspecialchars($obra['nome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
            -webkit-print-color-adjust: exact;
        }

        /* Utilitários de Impressão */
        .no-print { position: fixed; top: 10px; right: 10px; z-index: 9999; background: #fff; padding: 10px; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
        @media print { .no-print { display: none !important; } }

        /* Cabeçalho do Documento */
        .doc-header { border: 2px solid #000; margin-bottom: 15px; }
        .doc-header td { padding: 5px 10px; border: 1px solid #000; vertical-align: middle; }
        .logo-img { max-height: 60px; max-width: 180px; }

        /* Tabela de Resumo */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11pt; }
        .info-table td { border: 1px solid #000; padding: 4px 8px; }
        .bg-gray { background-color: #f0f0f0 !important; font-weight: bold; }

        /* Tabela Principal (Itens) */
        .main-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        .main-table th { background-color: #ddd !important; border: 1px solid #000; padding: 6px 4px; text-align: center; font-weight: bold; text-transform: uppercase; }
        .main-table td { border: 1px solid #000; padding: 4px; }
        
        /* Alinhamentos */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }

        /* Estilos de Linha */
        .row-etapa td { background-color: #eee !important; font-weight: bold; text-transform: uppercase; padding-left: 10px; }
        .col-destaque { background-color: #f8f9fa !important; font-weight: bold; } /* Destaca o Mês Atual */

        /* Assinaturas */
        .assinaturas { margin-top: 50px; page-break-inside: avoid; }
        .line-sig { border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 5px; text-align: center; }
    </style>
</head>
<body>

    <div class="no-print d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary fw-bold btn-sm">🖨️ Imprimir / Salvar PDF</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Fechar Janela</button>
    </div>

    <table class="doc-header" width="100%">
        <tr>
            <td width="20%" class="text-center">
                <?php if($temLogo): ?>
                    <img src="<?= $urlLogo; ?>" class="logo-img">
                <?php else: ?>
                    <h4 class="m-0 fw-bold">EMPRESA</h4>
                <?php endif; ?>
            </td>
            <td width="60%" class="text-center">
                <h3 class="m-0 fw-bold">BOLETIM DE MEDIÇÃO</h3>
                <div style="font-size: 1.1em; margin-top: 5px;">
                    REFERÊNCIA: <strong><?= mb_strtoupper($nomeMes); ?> / <?= $ano; ?></strong>
                </div>
            </td>
            <td width="20%" class="text-center" style="font-size: 0.9em;">
                <div>Emissão: <?= date('d/m/Y'); ?></div>
                <div class="fw-bold mt-1">Avanço Global</div>
                <div style="font-size: 1.4em;"><?= number_format($percFisico, 2, ',', '.'); ?>%</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="bg-gray" width="12%">Obra:</td>
            <td colspan="3"><?= htmlspecialchars($obra['nome']); ?></td>
            <td class="bg-gray" width="12%">Local:</td>
            <td><?= htmlspecialchars($obra['cidade']); ?></td>
        </tr>
        <tr>
            <td class="bg-gray">Contrato:</td>
            <td>R$ <?= number_format($totalContrato, 2, ',', '.'); ?></td>
            <td class="bg-gray">Anterior:</td>
            <td>R$ <?= number_format($totalAnterior, 2, ',', '.'); ?></td>
            <td class="bg-gray">Neste Mês:</td>
            <td class="fw-bold">R$ <?= number_format($totalPeriodo, 2, ',', '.'); ?></td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" width="6%">Item</th>
                <th rowspan="2">Descrição dos Serviços</th>
                <th rowspan="2" width="4%">Unid.</th>
                <th rowspan="2" width="8%">Preço Unit.</th>
                <th rowspan="2" width="7%">Qtd. Total</th>
                
                <th colspan="2" style="background-color: #f0f0f0;">Acumulado Anterior</th>
                <th colspan="2" style="background-color: #e2e3e5;">Neste Período (Mês)</th> <th colspan="2" style="background-color: #f0f0f0;">Acumulado Atual</th>
            </tr>
            <tr>
                <th width="6%">Qtd.</th>
                <th width="5%">%</th>
                
                <th width="6%" style="background-color: #e2e3e5;">Qtd.</th>
                <th width="8%" style="background-color: #e2e3e5;">Valor (R$)</th>
                
                <th width="6%">Qtd.</th>
                <th width="5%">%</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($itensMedicao)): foreach ($itensMedicao as $item): 
                $isEtapa = ($item['tipo'] === 'etapa');
                
                // Dados Básicos
                $preco = (float) $item['preco_unitario'];
                $orcado = (float) $item['qtd_orcada'];
                
                // Dados das Medições (Vindo do SQL)
                $qtdAnt = (float) $item['qtd_anterior'];
                $qtdMes = (float) $item['qtd_periodo']; // O que foi feito NESTE MÊS
                
                // Cálculos
                $qtdAcum = $qtdAnt + $qtdMes;
                $valorMes = $qtdMes * $preco;
                
                // Percentuais (Evita divisão por zero)
                $percAnt = ($orcado > 0) ? ($qtdAnt / $orcado) * 100 : 0;
                $percAcum = ($orcado > 0) ? ($qtdAcum / $orcado) * 100 : 0;
            ?>
            
                <?php if ($isEtapa): ?>
                    <tr class="row-etapa">
                        <td class="text-center"><?= $item['itemizacao']; ?></td>
                        <td colspan="10"><?= htmlspecialchars($item['descricao']); ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td class="text-center"><?= $item['itemizacao']; ?></td>
                        <td><?= htmlspecialchars($item['descricao']); ?></td>
                        <td class="text-center"><?= $item['unidade']; ?></td>
                        <td class="text-right"><?= number_format($preco, 2, ',', '.'); ?></td>
                        <td class="text-right"><?= number_format($orcado, 2, ',', '.'); ?></td>
                        
                        <td class="text-right text-muted"><?= number_format($qtdAnt, 2, ',', '.'); ?></td>
                        <td class="text-center text-muted" style="font-size: 0.85em;"><?= number_format($percAnt, 1, ',', '.'); ?>%</td>
                        
                        <td class="text-right col-destaque"><?= number_format($qtdMes, 2, ',', '.'); ?></td>
                        <td class="text-right col-destaque"><?= number_format($valorMes, 2, ',', '.'); ?></td>
                        
                        <td class="text-right fw-bold"><?= number_format($qtdAcum, 2, ',', '.'); ?></td>
                        <td class="text-center fw-bold" style="font-size: 0.85em;"><?= number_format($percAcum, 1, ',', '.'); ?>%</td>
                    </tr>
                <?php endif; ?>
                
            <?php endforeach; else: ?>
                <tr><td colspan="11" class="text-center p-3">Nenhum dado encontrado para este período.</td></tr>
            <?php endif; ?>
            
            <tr style="background-color: #ddd; font-weight: bold; border-top: 2px solid #000;">
                <td colspan="4" class="text-center">TOTAIS GERAIS</td>
                <td class="text-right">R$ <?= number_format($totalContrato, 2, ',', '.'); ?></td>
                
                <td colspan="2" class="text-right">R$ <?= number_format($totalAnterior, 2, ',', '.'); ?></td>
                
                <td colspan="2" class="text-right" style="font-size: 1.1em; background-color: #ccc;">
                    R$ <?= number_format($totalPeriodo, 2, ',', '.'); ?>
                </td>
                
                <td colspan="2" class="text-right">R$ <?= number_format($totalAcumulado, 2, ',', '.'); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="assinaturas">
        <table width="100%">
            <tr>
                <td width="50%" align="center">
                    <div class="line-sig">
                        <strong><?= isset($responsavel['nome']) ? $responsavel['nome'] : 'Engenheiro Responsável'; ?></strong><br>
                        <small>CONSTRUTORA</small>
                    </div>
                </td>
                <td width="50%" align="center">
                    <div class="line-sig">
                        <strong>FISCALIZAÇÃO / CLIENTE</strong><br>
                        <small>De Acordo</small>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>