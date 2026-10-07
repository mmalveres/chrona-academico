<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Documentos - <?= htmlspecialchars($obra['nome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

    <div class="container mt-4 mb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="index.php?acao=listar_obras">Obras</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($obra['nome']); ?></li>
                    </ol>
                </nav>
                <h3><i class="bi bi-folder2-open"></i> Gestão Documental</h3>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUpload">
                <i class="bi bi-cloud-upload"></i> Novo Documento
            </button>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'sucesso'): ?>
                <div class="alert alert-success alert-dismissible fade show">Documento arquivado com sucesso! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php elseif ($_GET['msg'] == 'erro_upload'): ?>
                <div class="alert alert-danger alert-dismissible fade show">Erro ao fazer upload. Verifique o tamanho do arquivo. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="row">
            <?php if(count($documentos) > 0): ?>
                <?php foreach($documentos as $doc): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100 border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <?php 
                                            // Ícone baseado na extensão ou tipo
                                            $ext = pathinfo($doc['caminho_arquivo'], PATHINFO_EXTENSION);
                                            $icone = 'bi-file-earmark-text';
                                            $cor = 'text-secondary';
                                            
                                            if(in_array(strtolower($ext), ['pdf'])) { $icone = 'bi-file-earmark-pdf'; $cor = 'text-danger'; }
                                            if(in_array(strtolower($ext), ['jpg','jpeg','png'])) { $icone = 'bi-file-earmark-image'; $cor = 'text-primary'; }
                                            if(in_array(strtolower($ext), ['xls','xlsx','csv'])) { $icone = 'bi-file-earmark-excel'; $cor = 'text-success'; }
                                            if(in_array(strtolower($ext), ['dwg','dxf'])) { $icone = 'bi-layers'; $cor = 'text-dark'; }
                                        ?>
                                        <i class="bi <?= $icone; ?> <?= $cor; ?>" style="font-size: 2rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-0 fw-bold"><?= htmlspecialchars($doc['titulo']); ?></h6>
                                        <span class="badge bg-light text-dark border"><?= ucfirst($doc['tipo']); ?></span>
                                    </div>
                                </div>
                                
                                <p class="card-text small text-muted mb-2">
                                    <?= !empty($doc['descricao']) ? htmlspecialchars($doc['descricao']) : 'Sem descrição.'; ?>
                                </p>

                                <?php if($doc['validade']): ?>
                                    <?php 
                                        $hoje = date('Y-m-d');
                                        $venceu = $doc['validade'] < $hoje;
                                        $perto = $doc['validade'] < date('Y-m-d', strtotime('+30 days'));
                                        $classeVal = $venceu ? 'text-danger fw-bold' : ($perto ? 'text-warning fw-bold' : 'text-success');
                                    ?>
                                    <p class="small mb-2">
                                        <i class="bi bi-calendar-event"></i> Validade: 
                                        <span class="<?= $classeVal; ?>"><?= date('d/m/Y', strtotime($doc['validade'])); ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
                                <small class="text-muted align-self-center"><?= date('d/m/Y', strtotime($doc['data_upload'])); ?></small>
                                <div>
                                    <a href="uploads/documentos/<?= $doc['caminho_arquivo']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Baixar/Visualizar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <a href="index.php?acao=excluir_documento&id=<?= $doc['id']; ?>&id_obra=<?= $obra['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir permanentemente?');" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-secondary text-center py-5">
                        <i class="bi bi-folder-x display-1 text-muted opacity-50"></i>
                        <h4 class="mt-3">Nenhum documento arquivado</h4>
                        <p>Faça o upload de Projetos, Contratos, ARTs ou Licenças desta obra.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="modalUpload" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Arquivar Documento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php?acao=salvar_documento" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id_obra" value="<?= $obra['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Título do Documento *</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ex: Projeto Elétrico Rev.02" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoria *</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="projeto">Projeto Técnico</option>
                                    <option value="art">ART / RRT</option>
                                    <option value="licenca">Licença / Alvará</option>
                                    <option value="contrato">Contrato</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Validade (Opcional)</label>
                                <input type="date" name="validade" class="form-control" title="Para licenças e alvarás">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Arquivo *</label>
                            <input type="file" name="arquivo" class="form-control" required>
                            <div class="form-text">PDF, Imagens, Planilhas ou DWG (Max: 64MB)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrição / Observação</label>
                            <textarea name="descricao" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Arquivar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../estruturas/rodape.php'; ?>
</body>
</html>