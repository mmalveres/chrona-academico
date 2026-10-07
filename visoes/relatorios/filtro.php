<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerar Relatórios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0"><i class="bi bi-file-earmark-bar-graph"></i> Gerador de Relatórios</h4>
                        <small><?= htmlspecialchars($obra['nome']); ?></small>
                    </div>
                    <div class="card-body">
                        <form action="index.php?acao=gerar_relatorio" method="POST" target="_blank">
                            <input type="hidden" name="id_obra" value="<?= $obra['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Período de Análise</label>
                                <div class="input-group">
                                    <span class="input-group-text">De</span>
                                    <input type="date" name="data_inicio" class="form-control" required>
                                    <span class="input-group-text">Até</span>
                                    <input type="date" name="data_fim" class="form-control" required value="<?= date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tipo de Relatório</label>
                                <select name="tipo" class="form-select">
                                    <option value="geral">Relatório Geral (Clima + Atividades)</option>
                                    <option value="financeiro">Relatório Financeiro (Receitas e Despesas)</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-printer"></i> Gerar Relatório
                                </button>
                                <a href="index.php?acao=listar_obras" class="btn btn-outline-secondary">Voltar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../estruturas/rodape.php'; ?>
</body>
</html>