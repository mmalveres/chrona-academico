<?php
/**
 * @var array $diario
 * @var array $obra
 * @var array $fotos
 * @var array $maoObra
 * @var array $equipamentos
 * @var array $listaAtividades
 */
// visoes/diario/imprimir.php

// Usa a empresa da obra (não do usuário logado) — correto para usuários multi-empresa
$idEmpresa = !empty($obra['id_empresa']) ? (int)$obra['id_empresa'] : ($_SESSION['id_empresa'] ?? 0);
$caminhoLogo = __DIR__ . '/../../publico/assets/img/logo_empresa_' . $idEmpresa . '.png';
$urlLogo = 'assets/img/logo_empresa_' . $idEmpresa . '.png?' . time();

$conn = \Aplicacao\Configuracao\BancoDeDados::getConexao();
$stmtEmp = $conn->prepare("SELECT nome_fantasia FROM empresas WHERE id = :id");
$stmtEmp->execute([':id' => $idEmpresa]);
$dadosEmpresa = $stmtEmp->fetch(PDO::FETCH_ASSOC);
$nomeEmpresa = !empty($dadosEmpresa['nome_fantasia']) ? $dadosEmpresa['nome_fantasia'] : 'Sua Empresa';

// Garante arrays para evitar erros
if (isset($atividades) && !isset($listaAtividades)) $listaAtividades = $atividades;
if (!isset($equipamentos)) $equipamentos = [];
if (!isset($maoObra)) $maoObra = [];
if (!isset($listaAtividades)) $listaAtividades = [];

// Fallback: se a obra não foi carregada (usuário multi-empresa), busca sem filtro de empresa
if (empty($obra) && !empty($diario['id_obra'])) {
    $stmtObra = $conn->prepare(
        "SELECT o.*, u.nome as nome_responsavel_tecnico
         FROM obras o
         LEFT JOIN usuarios u ON o.id_responsavel_tecnico = u.id
         WHERE o.id = :id"
    );
    $stmtObra->execute([':id' => $diario['id_obra']]);
    $obra = $stmtObra->fetch(PDO::FETCH_ASSOC);
}

// --- CÁLCULOS DE PRAZOS (Baseado na tabela Obras) ---
$contrato = !empty($obra['codigo_contrato'])
    ? $obra['codigo_contrato']
    : str_pad((string)($obra['id'] ?? '0'), 4, '0', STR_PAD_LEFT);

// Datas
$dtInicio = !empty($obra['data_inicio']) ? strtotime($obra['data_inicio']) : null;
$dtFim    = !empty($obra['data_previsao_fim']) ? strtotime($obra['data_previsao_fim']) : null;
$dtDiario = strtotime($diario['data_diario']);

// Variáveis de Prazo
$prazoContratual = '-';
$prazoDecorrido  = '-';
$prazoVencer     = '-';

if ($dtInicio && $dtFim) {
    // Dias totais (Fim - Início)
    $diffTotal = ($dtFim - $dtInicio) / 86400; 
    $prazoContratual = ceil($diffTotal) . ' dias';

    // Dias Decorridos (Diário - Início)
    $diffDecorrido = ($dtDiario - $dtInicio) / 86400;
    $diasDecorrido = ceil($diffDecorrido);
    if ($diasDecorrido < 0) $diasDecorrido = 0; 
    $prazoDecorrido = $diasDecorrido . ' dias';

    // Dias a Vencer (Fim - Diário)
    $diffVencer = ($dtFim - $dtDiario) / 86400;
    $diasVencer = ceil($diffVencer);
    $prazoVencer = $diasVencer . ' dias';
    
    if ($diasVencer < 0) {
        $prazoVencer = abs($diasVencer) . ' dias (Vencido)';
    }
}

// Formatando Local
$localObra = $obra['cidade'] ?? '';
if (!empty($obra['endereco'])) {
    $localObra = $obra['endereco'] . ' - ' . $localObra;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>RDO nº <?= $diario['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; font-family: Arial, sans-serif; font-size: 9pt; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .img-foto { max-height: 280px !important; }
        }
        body { font-family: Arial, sans-serif; font-size: 10pt; background: #fff;
               padding-bottom: calc(80px + env(safe-area-inset-bottom)); }

        /* Barra de ações fixa na parte inferior — visível em qualquer tela */
        .barra-acoes {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid #dee2e6;
            padding: 12px 16px;
            padding-bottom: calc(12px + env(safe-area-inset-bottom));
            display: flex;
            gap: 10px;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0,0,0,.08);
        }
        .barra-acoes .btn { flex: 1; font-weight: 700; padding: 12px; font-size: 1rem; border-radius: 10px; }
        .container-rdo { max-width: 210mm; margin: 20px auto; background: white; padding: 0; }
        
        /* Estilos do Cabeçalho Novo */
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .status-badge { padding: 5px 15px; color: white; font-weight: bold; border-radius: 0; font-size: 14px; }
        .bg-aprovado { background-color: #28a745; }
        .bg-rascunho { background-color: #ffc107; color: #000; }

        /* Tabela Principal de Layout do Cabeçalho */
        .layout-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 20px; }
        .layout-table td { border: 1px solid #000; vertical-align: middle; padding: 0; }
        
        /* Área da Esquerda (Logo e Infos da Obra) */
        .left-col { width: 70%; vertical-align: top; }
        .logo-area { text-align: center; padding: 15px; border-bottom: 1px solid #000; height: 100px; display:flex; align-items:center; justify-content:center; }
        .logo-area img { max-height: 80px; max-width: 90%; }
        .rdo-title-bar { text-align: center; font-weight: bold; font-size: 14px; padding: 5px; background-color: #fff; border-bottom: 1px solid #000; }
        
        /* Tabela Interna de Dados da Obra */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { border: none; border-bottom: 1px solid #000; padding: 4px 8px; font-size: 11px; }
        .info-table tr:last-child td { border-bottom: none; }
        .info-label { font-weight: bold; width: 80px; background-color: #f0f0f0; border-right: 1px solid #000; }

        /* Área da Direita (Grid de Prazos) */
        .right-col { width: 30%; vertical-align: top; background-color: #fff; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 4px 5px; border-bottom: 1px solid #000; font-size: 11px; }
        .meta-table tr:last-child td { border-bottom: none; }
        .meta-label { font-weight: bold; background-color: #f0f0f0; width: 110px; border-right: 1px solid #000; }
        .meta-value { font-weight: bold; text-align: left; }

        /* Estilos do Corpo do Relatório */
        .section-title { background-color: #ddd; font-weight: bold; padding: 5px 10px; border: 1px solid #000; margin-top: 15px; font-size: 11px; border-bottom: none; }
        .data-table { width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 4px 8px; }
        .bg-header { background-color: #e9ecef; font-weight: bold; text-align: center; }
        
        .box-container { border: 1px solid #000; padding: 5px; }
        .img-foto { width: 100%; height: 250px; object-fit: contain; background: #f8f9fa; border: 1px solid #ddd; }
        .assinatura-box { text-align: center; margin-top: 30px; }
        .assinatura-img { max-height: 80px; max-width: 250px; }
    </style>
</head>
<body>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <div class="barra-acoes no-print">
        <button id="btnImprimir" class="btn btn-primary">
            <i class="bi bi-printer-fill me-2"></i>Imprimir RDO
        </button>
        <button id="btnFechar" class="btn btn-secondary">
            <i class="bi bi-x-lg me-2"></i>Fechar
        </button>
    </div>

    <script>
        // Desktop tem mouse (pointer:fine); mobile/APK é touch (pointer:coarse)
        var isDesktop = window.matchMedia('(pointer: fine)').matches;
        // APK WebView Android tem "wv" no user agent
        var isWebView = /wv/.test(navigator.userAgent);

        document.getElementById('btnImprimir').addEventListener('click', function() {
            if (isDesktop) {
                // Desktop: dialog de impressão normal
                window.print();
            } else if (isWebView) {
                // APK/WebView: abre a mesma página no Chrome do Android
                var url = window.location.href;
                window.location.href = 'intent:' + url.replace(/^https?:/, '')
                    + '#Intent;scheme=https;package=com.android.chrome;end';
            } else if (navigator.share) {
                // Navegador mobile: painel de compartilhamento (inclui "Imprimir")
                navigator.share({ title: document.title, url: window.location.href })
                    .catch(function() { window.print(); });
            } else {
                window.print();
            }
        });

        document.getElementById('btnFechar').addEventListener('click', function() {
            window.close();
            setTimeout(function() {
                if (!window.closed) {
                    window.history.length > 1
                        ? window.history.back()
                        : window.location.href = 'index.php?acao=listar_obras';
                }
            }, 400);
        });
    </script>

    <div class="container-rdo">
        
        <div class="header-top">
            <div class="fw-bold fs-5">
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
                            <img src="<?= $urlLogo; ?>" alt="Logo">
                        <?php else: ?>
                            <h2 class="text-secondary fw-bold">LOGO EMPRESA</h2>
                        <?php endif; ?>
                    </div>
                    
                    <div class="rdo-title-bar">
                        Relatório Diário de Obra (RDO)
                    </div>

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
                            <td><?= htmlspecialchars($nomeEmpresa); ?></td>
                            
                            <td class="info-label" style="width: 80px; border-left:1px solid #000;">Responsável</td>
                            <td><?= htmlspecialchars($obra['nome_responsavel_tecnico'] ?? 'Não informado'); ?></td>
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
                            <td class="meta-value">
                                <?php 
                                    $dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado']; 
                                    echo $dias[date('w', strtotime($diario['data_diario']))]; 
                                ?>
                            </td>
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

        <div class="section-title text-center">CONDIÇÕES CLIMÁTICAS</div>
        <table class="data-table">
            <tr class="bg-header">
                <td>Período da Manhã</td>
                <td>Período da Tarde</td>
            </tr>
            <tr class="text-center text-capitalize">
                <td><?= $diario['clima_manha']; ?></td>
                <td><?= $diario['clima_tarde']; ?></td>
            </tr>
        </table>

        <?php $totalMO = 0; foreach($maoObra as $m) $totalMO += intval($m['quantidade']); ?>
        <div class="section-title">1. MÃO DE OBRA (Total: <?= $totalMO; ?>)</div>
        <table class="data-table text-center">
            <tr class="bg-header"><td class="text-start">Função</td><td width="20%">Tipo</td><td width="15%">Qtd.</td></tr>
            <?php if (count($maoObra) > 0): foreach($maoObra as $m): ?>
                <tr>
                    <td class="text-start"><?= htmlspecialchars($m['funcao']); ?></td>
                    <td class="small text-muted"><?= ucfirst($m['tipo'] == 'direta' ? 'Própria' : 'Terceirizada'); ?></td>
                    <td class="fw-bold"><?= $m['quantidade']; ?></td>
                </tr>
            <?php endforeach; else: ?><tr><td colspan="3" class="text-muted fst-italic">Nenhum registro.</td></tr><?php endif; ?>
        </table>

        <div class="section-title">2. EQUIPAMENTOS</div>
        <table class="data-table text-center">
            <tr class="bg-header"><td class="text-start">Equipamento</td><td width="15%">Qtd.</td></tr>
            <?php if (count($equipamentos) > 0): foreach($equipamentos as $eq): $nomeEq = $eq['nome'] ?? ($eq['nome_equipamento'] ?? 'N/A'); ?>
                <tr><td class="text-start"><?= htmlspecialchars($nomeEq); ?></td><td><?= $eq['quantidade']; ?></td></tr>
            <?php endforeach; else: ?><tr><td colspan="2" class="text-muted fst-italic">Nenhum registro.</td></tr><?php endif; ?>
        </table>

        <div class="section-title">3. ATIVIDADES EXECUTADAS</div>
        <table class="data-table">
            <tr class="bg-header"><td width="15%">Cód.</td><td class="text-start">Descrição do Serviço</td><td width="10%">Unid.</td><td width="15%">Qtd.</td></tr>
            <?php if (count($listaAtividades) > 0): foreach($listaAtividades as $atv): 
                $temVinculo = !empty($atv['nome_servico_orcamento']);
                $descricaoPrincipal = $temVinculo ? $atv['nome_servico_orcamento'] : ($atv['descricao_manual'] ?? $atv['descricao']);
                $detalhe = ($temVinculo && !empty($atv['descricao_manual'])) ? $atv['descricao_manual'] : '';
                $codigo = $atv['codigo_referencia'] ?? '-';
            ?>
            <tr>
                <td class="text-center small"><?= htmlspecialchars($codigo); ?></td>
                <td>
                    <div class="fw-bold"><?= htmlspecialchars($descricaoPrincipal); ?></div>
                    <?php if($detalhe): ?><div class="small text-muted fst-italic">Obs: <?= htmlspecialchars($detalhe); ?></div><?php endif; ?>
                </td>
                <td class="text-center small"><?= htmlspecialchars($atv['unidade'] ?? '-'); ?></td>
                <td class="text-center fw-bold bg-light"><?= number_format($atv['quantidade'], 2, ',', '.'); ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" class="p-3 text-center text-muted fst-italic"><?= !empty($diario['atividades_executadas']) ? nl2br(htmlspecialchars($diario['atividades_executadas'])) : "Nenhuma atividade registrada."; ?></td></tr>
            <?php endif; ?>
        </table>

        <div class="section-title">4. OBSERVAÇÕES E OCORRÊNCIAS</div>
        <div class="box-container" style="min-height: 60px; font-size: 11px;">
            <?= !empty($diario['observacoes_gerais']) ? nl2br(htmlspecialchars($diario['observacoes_gerais'])) : '<span class="text-muted fst-italic">Sem observações.</span>'; ?>
        </div>

        <?php if(count($fotos) > 0): ?>
            <div class="section-title page-break">5. RELATÓRIO FOTOGRÁFICO</div>
            <div class="box-container border-0 px-0">
                <div class="row g-2">
                    <?php foreach($fotos as $foto): ?>
                        <div class="col-6 text-center mb-2"><div class="border p-1 bg-white"><img src="uploads/<?= $foto['caminho_arquivo']; ?>" class="img-foto"></div></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if($diario['status'] == 'assinado'): ?>
            <div class="assinatura-box">
                <?php 
                $arqAss = $diario['arquivo_assinatura'] ?? ($diario['assinatura_imagem'] ?? ''); 
                if (!empty($arqAss) && file_exists(__DIR__ . '/../../publico/uploads/assinaturas/' . $arqAss)): 
                ?>
                    <img src="uploads/assinaturas/<?= $arqAss; ?>" class="assinatura-img">
                <?php endif; ?>
                
                <div style="border-top: 1px solid #000; width: 60%; margin: 5px auto 0 auto; padding-top: 5px;">
                    <strong><?= htmlspecialchars($obra['nome_responsavel_tecnico'] ?? 'Responsável Técnico'); ?></strong><br>
                    <span class="small text-muted">Assinado digitalmente em <?= date('d/m/Y H:i', strtotime($diario['data_assinatura'])); ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>