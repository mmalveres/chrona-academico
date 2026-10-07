<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma - <?= htmlspecialchars($obra['nome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .progress { height: 25px; font-weight: bold; font-size: 0.9rem; }
        /* Remove setas do input number */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; margin: 0; 
        }

        /* --- ESTILOS RESPONSIVOS PARA TABELA --- */
        @media (max-width: 768px) {
            /* Esconde cabeçalho da tabela */
            .table thead { display: none; }
            
            /* Transforma linhas em cards */
            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                background: #fff;
                border: 1px solid #e9ecef;
                border-radius: 12px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                padding: 1rem;
            }

            /* Ajusta células para serem blocos */
            .table td {
                display: block;
                border: none;
                padding: 0.5rem 0;
                text-align: left !important; /* Força alinhamento à esquerda */
            }

            /* Ordem (Número) */
            .table td:nth-child(1) {
                font-size: 0.8rem;
                color: #6c757d;
                margin-bottom: 0.2rem;
            }
            .table td:nth-child(1)::before { content: "Etapa "; }

            /* Nome da Etapa */
            .table td:nth-child(2) {
                font-size: 1.1rem;
                font-weight: 700;
                color: #212529;
                margin-bottom: 0.5rem;
            }

            /* Datas */
            .table td:nth-child(3) {
                display: flex;
                justify-content: space-between;
                background: #f8f9fa;
                padding: 0.5rem;
                border-radius: 6px;
                margin-bottom: 0.5rem;
                font-size: 0.9rem;
            }

            /* Progresso */
            .table td:nth-child(4) {
                margin-bottom: 0.5rem;
            }

            /* Ações */
            .table td:nth-child(5) {
                border-top: 1px solid #e9ecef;
                padding-top: 0.75rem;
                text-align: right !important; /* Botão excluir à direita */
            }
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

    <div class="container mt-4 mb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="index.php?acao=listar_obras">Obras</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($obra['nome']); ?></li>
                    </ol>
                </nav>
                <h3 class="h4 mb-0 fw-bold"><i class="bi bi-calendar-range text-primary"></i> Cronograma Físico</h3>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovaEtapa">
                <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Adicionar Etapa</span>
            </button>
        </div>

        <div class="card shadow-sm mb-4 border-primary">
            <div class="card-body">
                <h5 class="card-title text-primary h6 text-uppercase fw-bold">Progresso Geral</h5>
                <div class="progress mt-2" style="height: 30px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" 
                         style="width: <?= $progressoGeral; ?>%" aria-valuenow="<?= $progressoGeral; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?= $progressoGeral; ?>%
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 bg-transparent">
            <div class="table-responsive-md"> <table class="table table-hover align-middle mb-0 bg-white rounded shadow-sm overflow-hidden">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="30%">Etapa</th>
                            <th width="25%">Datas Previstas</th>
                            <th width="30%">Progresso (%)</th>
                            <th width="10%" class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($etapas) > 0): ?>
                            <?php foreach($etapas as $etapa): ?>
                                <tr>
                                    <td class="fw-bold text-center text-muted"><?= $etapa['ordem']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($etapa['nome']); ?></div>
                                        <div class="d-md-none mt-1"> <?php 
                                                $hoje = date('Y-m-d');
                                                if($etapa['progresso'] == 100) echo '<span class="badge bg-success">Concluída</span>';
                                                elseif($etapa['progresso'] < 100 && $etapa['data_fim_prevista'] < $hoje) echo '<span class="badge bg-danger">Atrasada</span>';
                                                elseif($etapa['progresso'] > 0) echo '<span class="badge bg-info text-dark">Em andamento</span>';
                                                else echo '<span class="badge bg-secondary">Pendente</span>';
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column flex-md-row gap-md-3">
                                            <small class="text-muted"><i class="bi bi-calendar-event me-1"></i> <?= date('d/m/y', strtotime($etapa['data_inicio_prevista'])); ?></small>
                                            <small class="text-muted"><i class="bi bi-flag me-1"></i> <?= date('d/m/y', strtotime($etapa['data_fim_prevista'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <form action="index.php?acao=atualizar_etapa" method="POST" class="d-flex align-items-center">
                                            <input type="hidden" name="id_obra" value="<?= $obra['id']; ?>">
                                            <input type="hidden" name="id_etapa" value="<?= $etapa['id']; ?>">
                                            
                                            <div class="flex-grow-1 me-2 d-none d-md-block"> <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $etapa['progresso']; ?>%"></div>
                                                </div>
                                            </div>
                                            
                                            <div class="input-group input-group-sm" style="width: 100%; max-width: 130px;">
                                                <input type="number" name="progresso" class="form-control text-center fw-bold" 
                                                       value="<?= $etapa['progresso']; ?>" min="0" max="100" step="1">
                                                <button class="btn btn-outline-success" type="submit" title="Salvar">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <a href="index.php?acao=excluir_etapa&id=<?= $etapa['id']; ?>&id_obra=<?= $obra['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Excluir esta etapa do cronograma?');">
                                            <i class="bi bi-trash"></i> <span class="d-md-none">Excluir</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted opacity-50 mb-2"><i class="bi bi-calendar-x" style="font-size: 2rem;"></i></div>
                                    <h6 class="text-muted">Nenhuma etapa cadastrada</h6>
                                    <button class="btn btn-link btn-sm text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalNovaEtapa">Adicionar primeira etapa</button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4 text-center text-md-start">
            <a href="index.php?acao=listar_obras" class="btn btn-outline-secondary rounded-pill px-4"><i class="bi bi-arrow-left"></i> Voltar para Obras</a>
        </div>
    </div>

    <div class="modal fade" id="modalNovaEtapa" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nova Etapa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php?acao=salvar_etapa" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_obra" value="<?= $obra['id']; ?>">
                        
                        <div class="row g-3">
                            <div class="col-3">
                                <label class="form-label fw-bold small text-muted">Ordem</label>
                                <input type="number" name="ordem" class="form-control" value="<?= count($etapas) + 1; ?>" required>
                            </div>
                            <div class="col-9">
                                <label class="form-label fw-bold small text-muted">Nome da Etapa</label>
                                <input type="text" name="nome" class="form-control" placeholder="Ex: Fundação" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Início Previsto</label>
                                <input type="date" name="inicio" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Término Previsto</label>
                                <input type="date" name="fim" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../estruturas/rodape.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>