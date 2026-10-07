<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro — CHRONA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .erro-card { background: #fff; border-radius: 20px; box-shadow: 0 4px 30px rgba(0,0,0,0.07); padding: 48px 40px; max-width: 480px; width: 100%; text-align: center; }
    </style>
</head>
<body>
    <div class="erro-card">
        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3.5rem;"></i>
        <h4 class="fw-bold text-dark mt-3 mb-2">Algo deu errado</h4>
        <p class="text-muted mb-4">
            Ocorreu um erro inesperado. Nossa equipe já foi notificada.<br>
            Por favor, tente novamente em alguns instantes.
        </p>
        <a href="index.php?acao=painel" class="btn btn-primary fw-bold px-4">
            <i class="bi bi-house-fill me-2"></i> Voltar ao início
        </a>
    </div>
</body>
</html>
