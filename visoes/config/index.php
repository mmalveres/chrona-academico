<?php
// visoes/config/index.php

// Configurações iniciais da imagem e variáveis
$idEmpresa = $_SESSION['id_empresa'] ?? 0;
$nomeArquivoLogo = "logo_empresa_{$idEmpresa}.png";
$caminhoFisico = __DIR__ . '/../../publico/assets/img/' . $nomeArquivoLogo;
$urlImagem = "assets/img/" . $nomeArquivoLogo . "?t=" . time();

// Instancia o modelo para uso nas listagens
$catalogo = new \Aplicacao\Modelos\Catalogo();

// 1. Inclui a estrutura base (Sidebar + Topbar)
include __DIR__ . '/../estruturas/cabecalho.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-2">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--primary-color);">Configurações</h3>
        <p class="text-secondary mb-0">Personalize a identidade visual, dados da empresa e catálogos.</p>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-5">
        
        <div class="card-custom mb-4">
            <h5 class="fw-bold mb-4" style="color: var(--primary-color);">
                <i class="bi bi-building me-2"></i> Dados da Empresa
            </h5>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'dados_salvos'): ?>
                <div class="alert alert-success border-0 shadow-sm py-2 small fw-bold mb-3 text-center">
                    <i class="bi bi-check-circle me-2"></i> Dados atualizados com sucesso!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'erro_bd'): ?>
                <div class="alert alert-danger border-0 shadow-sm py-2 small fw-bold mb-3 text-center">
                    <i class="bi bi-exclamation-triangle me-2"></i> Erro ao salvar. Tente novamente.
                </div>
            <?php endif; ?>

            <form action="index.php?acao=salvar_dados_empresa" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nome Fantasia</label>
                    <input type="text" name="nome_empresa" class="form-control" 
                           value="<?= htmlspecialchars($dadosEmpresa['nome_fantasia'] ?? '') ?>" 
                           placeholder="Nome da sua empresa" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">CNPJ</label>
                    <input type="text" name="cnpj_empresa" id="cnpj_empresa" class="form-control" 
                           value="<?= htmlspecialchars($dadosEmpresa['cnpj'] ?? '') ?>" 
                           placeholder="00.000.000/0000-00">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary fw-bold" style="background-color: var(--primary-color); border: none;">
                        Salvar Dados
                    </button>
                </div>
            </form>
        </div>

        <div class="card-custom h-auto">
            <h5 class="fw-bold mb-4" style="color: var(--primary-color);">
                <i class="bi bi-image me-2"></i> Logomarca
            </h5>

            <div class="text-center">
                <div class="mb-4 p-4 rounded-4 d-flex align-items-center justify-content-center bg-light position-relative" 
                     style="height: 200px; border: 2px dashed #cbd5e1;">
                    
                    <?php if(file_exists($caminhoFisico)): ?>
                        <img src="<?= $urlImagem; ?>" class="img-fluid" style="max-height: 150px; object-fit: contain;">
                    <?php else: ?>
                        <div class="text-center text-muted opacity-50">
                            <i class="bi bi-cloud-upload fs-1"></i>
                            <div class="fw-bold mt-2">Logo Padrão</div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                    <?php 
                        $tipoAlert = 'info';
                        $icone = 'info-circle';
                        $texto = '';
                        
                        if ($_GET['msg'] == 'sucesso_logo') { $tipoAlert = 'success'; $icone='check-circle'; $texto='Logo atualizada com sucesso!'; }
                        elseif ($_GET['msg'] == 'logo_removida') { $tipoAlert = 'warning'; $icone='trash'; $texto='Logo removida. Usando padrão.'; }
                        elseif ($_GET['msg'] == 'erro_extensao') { $tipoAlert = 'danger'; $icone='x-circle'; $texto='Erro: Apenas JPG ou PNG.'; }
                        elseif ($_GET['msg'] == 'erro_upload') { $tipoAlert = 'danger'; $icone='x-circle'; $texto='Erro no upload da imagem.'; }
                    ?>
                    <?php if($texto): ?>
                        <div class="alert alert-<?= $tipoAlert; ?> border-0 shadow-sm py-2 small fw-bold mb-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-<?= $icone; ?> me-2"></i> <?= $texto; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <form action="index.php?acao=salvar_logo" method="POST" enctype="multipart/form-data">
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold text-muted text-uppercase">Alterar Imagem</label>
                        <input type="file" name="logo" class="form-control" required accept="image/png, image/jpeg">
                        <div class="form-text small mt-2">
                            <i class="bi bi-info-circle"></i> Recomendado: Fundo transparente (PNG).
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold" style="background-color: var(--primary-color); border: none;">
                            Salvar Alteração
                        </button>
                        
                        <?php if(file_exists($caminhoFisico)): ?>
                            <a href="index.php?acao=remover_logo" class="btn btn-light text-danger fw-bold" onclick="return confirm('Voltar para a logo padrão?');">
                                <i class="bi bi-trash me-2"></i> Remover Logo
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        
        <div class="card-custom mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0" style="color: var(--primary-color);">
                    <i class="bi bi-people-fill me-2"></i> Mão de Obra
                </h5>
                <span class="badge bg-light text-secondary border">Cargos</span>
            </div>

            <form action="index.php?acao=gerenciar_catalogo" method="POST" class="d-flex gap-2 mb-4">
                <input type="hidden" name="tipo" value="mao_obra">
                <input type="hidden" name="acao_catalogo" value="adicionar">
                <input type="text" name="nome_item" class="form-control" placeholder="Ex: Pedreiro, Servente..." required style="background-color: #f8f9fa;">
                <button type="submit" class="btn btn-success fw-bold px-3"><i class="bi bi-plus-lg"></i></button>
            </form>

            <div class="custom-scroll" style="max-height: 220px; overflow-y: auto; padding-right: 5px;">
                <ul class="list-group list-group-flush">
                    <?php 
                    $itensMO = $catalogo->listarMaoObra();
                    if(count($itensMO) > 0):
                        foreach($itensMO as $item): 
                            $ehPadrao = ($item['id_empresa'] == 0);
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom">
                        <span class="fw-bold text-dark">
                            <?= htmlspecialchars($item['nome']); ?>
                            <?php if($ehPadrao): ?>
                                <i class="bi bi-patch-check-fill text-primary ms-1" title="Padrão do Sistema" style="font-size: 0.8rem; opacity: 0.5;"></i>
                            <?php endif; ?>
                        </span>

                        <?php if(!$ehPadrao): ?>
                            <form action="index.php?acao=gerenciar_catalogo" method="POST" style="display:inline;">
                                <input type="hidden" name="tipo" value="mao_obra">
                                <input type="hidden" name="acao_catalogo" value="remover">
                                <input type="hidden" name="id_item" value="<?= $item['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-light text-danger border-0" title="Excluir" onclick="return confirm('Remover este item?');">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <small class="text-muted" style="font-size: 0.7rem;">PADRÃO</small>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; else: ?>
                        <li class="list-group-item text-center text-muted py-4 small">Nenhum cargo cadastrado.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0" style="color: var(--primary-color);">
                    <i class="bi bi-tools me-2"></i> Equipamentos
                </h5>
                <span class="badge bg-light text-secondary border">Ferramentas</span>
            </div>

            <form action="index.php?acao=gerenciar_catalogo" method="POST" class="d-flex gap-2 mb-4">
                <input type="hidden" name="tipo" value="equipamento">
                <input type="hidden" name="acao_catalogo" value="adicionar">
                <input type="text" name="nome_item" class="form-control" placeholder="Ex: Furadeira, Betoneira..." required style="background-color: #f8f9fa;">
                <button type="submit" class="btn btn-success fw-bold px-3"><i class="bi bi-plus-lg"></i></button>
            </form>

            <div class="custom-scroll" style="max-height: 220px; overflow-y: auto; padding-right: 5px;">
                <ul class="list-group list-group-flush">
                    <?php 
                    $itensEq = $catalogo->listarEquipamentos();
                    if(count($itensEq) > 0):
                        foreach($itensEq as $item): 
                            $ehPadrao = ($item['id_empresa'] == 0);
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom">
                        <span class="fw-bold text-dark">
                            <?= htmlspecialchars($item['nome']); ?>
                            <?php if($ehPadrao): ?>
                                <i class="bi bi-patch-check-fill text-primary ms-1" title="Padrão" style="font-size: 0.8rem; opacity: 0.5;"></i>
                            <?php endif; ?>
                        </span>

                        <?php if(!$ehPadrao): ?>
                            <form action="index.php?acao=gerenciar_catalogo" method="POST" style="display:inline;">
                                <input type="hidden" name="tipo" value="equipamento">
                                <input type="hidden" name="acao_catalogo" value="remover">
                                <input type="hidden" name="id_item" value="<?= $item['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-light text-danger border-0" title="Excluir" onclick="return confirm('Remover este item?');">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <small class="text-muted" style="font-size: 0.7rem;">PADRÃO</small>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; else: ?>
                        <li class="list-group-item text-center text-muted py-4 small">Nenhum equipamento cadastrado.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

    </div>
</div>

<style>
    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: #999; }
</style>

<script>
    document.getElementById('cnpj_empresa').addEventListener('input', function (e) {
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/);
        e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + '.' + x[3] + '/' + x[4] + (x[5] ? '-' + x[5] : '');
    });
</script>

<?php 
// 2. Inclui o Rodapé (Fecha HTML e Scripts)
include __DIR__ . '/../estruturas/rodape.php'; 
?>