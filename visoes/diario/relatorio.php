<?php
// visoes/diario/relatorio.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Usa a empresa da obra (não do usuário logado) — correto para usuários multi-empresa
$idEmpresa = !empty($obra['id_empresa']) ? (int)$obra['id_empresa'] : ($_SESSION['id_empresa'] ?? 0);
$caminhoLogo = __DIR__ . '/../../publico/assets/img/logo_empresa_' . $idEmpresa . '.png';
$urlLogo = 'assets/img/logo_empresa_' . $idEmpresa . '.png?' . time();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Obras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; font-family: Arial, sans-serif; font-size: 10pt; margin: 0; }
            .no-print { display: none !important; }
            .quebra-pagina { page-break-after: always; display: block; height: 0; }
            .img-foto { max-height: 250px !important; }
            .container-rdo { box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
        body { font-family: Arial, sans-serif; font-size: 10pt; background: #555; }
        
        .container-rdo { 
            max-width: 210mm; 
            min-height: 297mm; 
            margin: 20px auto; 
            background: white; 
            padding: 20px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.5); 
            position: relative;
        }

        /* --- ESTILOS DO NOVO CABEÇALHO --- */
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .status-badge { padding: 5px 15px; color: white; font-weight: bold; border-radius: 0; font-size: 14px; }
        .bg-aprovado { background-color: #28a745; }
        .bg-rascunho { background-color: #ffc107; color: #000; }

        .layout-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 10px; }
        .layout-table td { border: 1px solid #000; vertical-align: middle; padding: 0; }
        
        .left-col { width: 70%; vertical-align: top; }
        .logo-area { text-align: center; padding: 10px; border-bottom: 1px solid #000; height: 80px; display:flex; align-items:center; justify-content:center; }
        .logo-area img { max-height: 70px; max-width: 90%; }
        .rdo-title-bar { text-align: center; font-weight: bold; font-size: 14px; padding: 5px; background-color: #fff; border-bottom: 1px solid #000; }
        
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { border: none; border-bottom: 1px solid #000; padding: 3px 5px; font-size: 10px; }
        .info-table tr:last-child td { border-bottom: none; }
        .info-label { font-weight: bold; width: 70px; background-color: #f0f0f0; border-right: 1px solid #000; }

        .right-col { width: 30%; vertical-align: top; background-color: #fff; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 3px 5px; border-bottom: 1px solid #000; font-size: 10px; }
        .meta-table tr:last-child td { border-bottom: none; }
        .meta-label { font-weight: bold; background-color: #f0f0f0; width: 100px; border-right: 1px solid #000; }
        .meta-value { font-weight: bold; text-align: left; }

        /* --- ESTILOS DO CORPO --- */
        .rdo-table { width: 100%; border-collapse: collapse; border: 2px solid #444; }
        .rdo-table th, .rdo-table td { border: 1px solid #666; padding: 4px 8px; vertical-align: middle; }
        .bg-header { background-color: #e9ecef; font-weight: bold; color: #333; font-size: 0.9rem; }
        
        .section-title { background-color: #ddd; font-weight: bold; padding: 5px 10px; border: 2px solid #444; border-bottom: none; margin-top: 15px; font-size: 0.95rem; }
        .box-container { border: 2px solid #444; padding: 0; }
        
        .img-foto { width: 100%; height: 250px; object-fit: contain; background: #f8f9fa; border: 1px solid #eee; }
        .assinatura-img { max-height: 60px; max-width: 200px; }
    </style>
</head>
<body>

    <div class="container mt-3 mb-3 no-print text-center">
        <div class="bg-white p-3 rounded shadow">
            <h4><i class="bi bi-printer"></i> Relatório de Diários</h4>
            <?php if(isset($inicio) && isset($fim)): ?>
                <p>Período: <strong><?= date('d/m/Y', strtotime($inicio)); ?></strong> até <strong><?= date('d/m/Y', strtotime($fim)); ?></strong></p>
            <?php endif; ?>
            <p class="text-muted small">Total de <?= count($diarios); ?> registros.</p>
            <button onclick="window.print()" class="btn btn-primary btn-lg">🖨️ Imprimir / Salvar PDF</button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">Fechar</button>
        </div>
    </div>

    <?php if (count($diarios) == 0): ?>
        <div class="container-rdo text-center pt-5">
            <h3>Nenhum diário encontrado neste período.</h3>
        </div>
    <?php endif; ?>

    <?php foreach ($diarios as $index => $diario): ?>
        
        <?php 
            // --- RECUPERAÇÃO DOS DADOS ---
            $maoObra = $diario['mao_obra'] ?? [];
            $equipamentos = $diario['equipamentos'] ?? []; 
            $fotos = $diario['fotos'] ?? [];
            $listaAtividades = $diario['atividades'] ?? []; 
            $nomeResponsavel = $diario['responsavel_nome'] ?? 'Responsável Técnico';
            
            $diasSemanaTraducao = [
                'Sunday' => 'Domingo', 'Monday' => 'Segunda-feira', 'Tuesday' => 'Terça-feira',
                'Wednesday' => 'Quarta-feira', 'Thursday' => 'Quinta-feira', 'Friday' => 'Sexta-feira', 'Saturday' => 'Sábado'
            ];
            $diaIngles = date('l', strtotime($diario['data_diario']));
            $diaSemana = $diasSemanaTraducao[$diaIngles];

            // --- CÁLCULOS DE PRAZOS (ESPECÍFICO PARA CADA DIÁRIO NO LOOP) ---
            $contrato = isset($obra['codigo_contrato']) ? $obra['codigo_contrato'] : str_pad($obra['id'], 4, '0', STR_PAD_LEFT);
            $dtInicio = !empty($obra['data_inicio']) ? strtotime($obra['data_inicio']) : null;
            $dtFim    = !empty($obra['data_previsao_fim']) ? strtotime($obra['data_previsao_fim']) : null;
            $dtDiario = strtotime($diario['data_diario']);

            $prazoContratual = '-';
            $prazoDecorrido  = '-';
            $prazoVencer     = '-';

            if ($dtInicio && $dtFim) {
                // Prazo Total
                $diffTotal = ($dtFim - $dtInicio) / 86400; 
                $prazoContratual = ceil($diffTotal) . ' dias';

                // Prazo Decorrido (Baseado na data DESTE diário)
                $diffDecorrido = ($dtDiario - $dtInicio) / 86400;
                $diasDecorrido = ceil($diffDecorrido);
                if ($diasDecorrido < 0) $diasDecorrido = 0; 
                $prazoDecorrido = $diasDecorrido . ' dias';

                // Prazo a Vencer (Baseado na data DESTE diário)
                $diffVencer = ($dtFim - $dtDiario) / 86400;
                $diasVencer = ceil($diffVencer);
                $prazoVencer = ($diasVencer < 0) ? abs($diasVencer) . ' dias (Vencido)' : $diasVencer . ' dias';
            }

            // Local
            $localObra = $obra['cidade'] ?? '';
            if (!empty($obra['endereco'])) $localObra = $obra['endereco'] . ' - ' . $localObra;
        ?>

        <div class="container-rdo">
            
            <div class="header-top">
                <div class="fw-bold fs-6">
                    Relatório <?= date('d/m/Y', strtotime($diario['data_diario'])); ?> nº <?= $diario['id']; ?>
                </div>
                <div>
                    <?php if ($diario['status'] == 'assinado'): ?>
                        <span class="status-badge bg-aprovado">Aprovado</span>
                    <?php else: ?>
                        <span class="status-badge bg-rascunho">Rascunho</span>
                    <?php endif; ?>
                </div>
            </div>

            <table class="layout-table">
                <tr>
                    <td class="left-col">
                        <div class="logo-area">
                            <?php if(file_exists($caminhoLogo)): ?>
                                <img src="<?= $urlLogo; ?>" alt="Logo Empresa">
                            <?php else: ?>
                                <h3 class="text-secondary fw-bold m-0">LOGO</h3>
                            <?php endif; ?>
                        </div>
                        <div class="rdo-title-bar">Relatório Diário de Obra (RDO)</div>
                        <table class="info-table">
                            <tr>
                                <td class="info-label">Obra</td>
                                <td colspan="3"><?= htmlspecialchars($obra['nome']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Local</td>
                                <td colspan="3"><?= htmlspecialchars($localObra); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Contratante</td>
                                <td><?= $_SESSION['empresa_nome'] ?? 'Cliente Padrão'; ?></td>
                                <td class="info-label" style="width: 70px; border-left:1px solid #000;">Responsável</td>
                                <td><?= htmlspecialchars($nomeResponsavel); ?></td>
                            </tr>
                        </table>
                    </td>
                    <td class="right-col">
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label">Relatório nº</td>
                                <td class="meta-value"><?= $diario['id']; ?></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Data do relatório</td>
                                <td class="meta-value"><?= date('d/m/Y', strtotime($diario['data_diario'])); ?></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Dia da semana</td>
                                <td class="meta-value"><?= $diaSemana; ?></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Contrato</td>
                                <td class="meta-value"><?= htmlspecialchars($contrato); ?></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Prazo contratual</td>
                                <td class="meta-value"><?= $prazoContratual; ?></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Prazo decorrido</td>
                                <td class="meta-value"><?= $prazoDecorrido; ?></td>
                            </tr>
                            <tr>
                                <td class="meta-label">Prazo a vencer</td>
                                <td class="meta-value"><?= $prazoVencer; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="section-title text-center" style="margin-top:0;">CONDIÇÕES CLIMÁTICAS</div>
            <table class="rdo-table mb-3">
                <tr class="bg-header text-center">
                    <td>Manhã: <?= ucfirst($diario['clima_manha']); ?></td>
                    <td>Tarde: <?= ucfirst($diario['clima_tarde']); ?></td>
                </tr>
            </table>

            <?php 
                $totalMO = 0;
                foreach($maoObra as $m) $totalMO += intval($m['quantidade']);
            ?>
            <div class="section-title">1. Mão de Obra (Total: <?= $totalMO; ?>)</div>
            <table class="rdo-table mb-3 text-center">
                <tr class="bg-header">
                    <td class="text-start">Função</td>
                    <td width="15%">Qtd.</td>
                    <td width="25%">Tipo</td>
                </tr>
                <?php if (count($maoObra) > 0): ?>
                    <?php foreach($maoObra as $m): ?>
                    <tr>
                        <td class="text-start"><?= htmlspecialchars($m['funcao']); ?></td>
                        <td class="fw-bold"><?= $m['quantidade']; ?></td>
                        <td class="small text-muted"><?= isset($m['tipo']) ? ucfirst($m['tipo'] == 'direta' ? 'Própria' : 'Sub') : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-muted fst-italic py-1">Nenhum registro.</td></tr>
                <?php endif; ?>
            </table>

            <div class="section-title">2. Equipamentos</div>
            <table class="rdo-table mb-3 text-center">
                <tr class="bg-header">
                    <td class="text-start">Descrição</td>
                    <td width="15%">Qtd.</td>
                </tr>
                <?php if (count($equipamentos) > 0): ?>
                    <?php foreach($equipamentos as $eq): 
                        $nomeEq = $eq['nome_equipamento'] ?? $eq['nome'];
                    ?>
                    <tr>
                        <td class="text-start"><?= htmlspecialchars($nomeEq); ?></td>
                        <td><?= $eq['quantidade']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-muted fst-italic py-1">Nenhum registro.</td></tr>
                <?php endif; ?>
            </table>

            <div class="section-title">3. Atividades Executadas</div>
            <table class="rdo-table mb-3">
                <tr class="bg-header">
                    <td width="15%">Cód.</td>
                    <td>Descrição do Serviço</td>
                    <td width="10%" class="text-center">Unid.</td>
                    <td width="15%" class="text-center">Qtd.</td>
                </tr>
                <?php if (count($listaAtividades) > 0): ?>
                    <?php foreach($listaAtividades as $atv): 
                        $temVinculo = !empty($atv['nome_servico_orcamento']);
                        $nomeOrcamento = $atv['nome_servico_orcamento'] ?? '';
                        $nomeManual    = $atv['descricao_manual'] ?? ($atv['descricao'] ?? '');

                        $descricaoPrincipal = $temVinculo ? $nomeOrcamento : $nomeManual;
                        $detalhe = ($temVinculo && !empty($nomeManual)) ? $nomeManual : '';
                        
                        $codigo = $atv['codigo_referencia'] ?? '-';
                        $unidade = $atv['unidade'] ?? '-';
                    ?>
                    <tr>
                        <td class="small text-muted"><?= htmlspecialchars($codigo); ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($descricaoPrincipal); ?></div>
                            <?php if($detalhe): ?>
                                <div class="small text-muted fst-italic">Obs: <?= htmlspecialchars($detalhe); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center small"><?= htmlspecialchars($unidade); ?></td>
                        <td class="text-center fw-bold bg-light"><?= number_format($atv['quantidade'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="p-2">
                            <?php 
                            $textoAntigo = $diario['atividades_executadas'] ?? '';
                            if (!empty($textoAntigo)) echo nl2br(htmlspecialchars($textoAntigo));
                            else echo "<span class='text-muted fst-italic'>Nenhuma atividade registrada.</span>";
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <?php 
                $obs = $diario['observacoes_gerais'] ?? $diario['observacoes'] ?? '';
                if (!empty($obs)): 
            ?>
            <div class="section-title">4. Observações</div>
            <div class="box-container p-2 mb-3" style="min-height: 40px;">
                <?= nl2br(htmlspecialchars($obs)); ?>
            </div>
            <?php endif; ?>

            <?php if(count($fotos) > 0): ?>
                <div class="section-title">5. Registro Fotográfico</div>
                <div class="box-container p-2 border-0">
                    <div class="row g-3">
                        <?php foreach($fotos as $foto): ?>
                            <div class="col-6 text-center mb-3">
                                <div class="border p-1 bg-white">
                                    <img src="uploads/<?= $foto['caminho_arquivo']; ?>" class="img-foto">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($diario['status'] == 'assinado'): ?>
                <div class="text-center mt-4 mb-2">
                    <?php 
                    $arqAss = $diario['arquivo_assinatura'] ?? $diario['assinatura_imagem'];
                    if (!empty($arqAss) && file_exists(__DIR__ . '/../../publico/uploads/assinaturas/' . $arqAss)): 
                    ?>
                        <img src="uploads/assinaturas/<?= $arqAss; ?>" class="assinatura-img">
                    <?php endif; ?>
                    <div style="border-top: 1px solid #000; width: 50%; margin: 5px auto;">
                        <strong><?= htmlspecialchars($nomeResponsavel); ?></strong><br>
                        <small>Assinado em <?= date('d/m/Y H:i', strtotime($diario['data_assinatura'] ?? $diario['data_diario'])); ?></small>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        
        <div class="quebra-pagina"></div>

    <?php endforeach; ?> 

</body>
</html>