<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Assinar Diário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Assinar Diário de Obra</h5>
                    </div>
                    <div class="card-body">
                        <p>Você está prestes a finalizar este relatório. Após assinar, ele <strong>não poderá ser alterado</strong>.</p>

                        <form action="index.php?acao=salvar_assinatura&id=<?= $id; ?>" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Imagem da Assinatura (Opcional)</label>
                                <input type="file" name="imagem_assinatura" class="form-control" accept="image/png, image/jpeg, image/jpg">
                                <div class="form-text">Envie uma foto da sua rubrica ou assinatura (JPG ou PNG).</div>
                            </div>

                            <div class="form-check mb-3 p-3 bg-warning-subtle rounded border border-warning">
                                <input class="form-check-input" type="checkbox" required id="confirma">
                                <label class="form-check-label small" for="confirma">
                                    Declaro que as informações preenchidas neste diário são verdadeiras e correspondem à execução da obra nesta data.
                                </label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">Confirmar e Assinar</button>
                                <a href="index.php?acao=ver_diario&id=<?= $id; ?>&id_obra=<?= $idObra; ?>" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>