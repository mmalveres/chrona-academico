<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Auditoria do Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="bi bi-shield-lock"></i> Logs de Auditoria</h3>
            <span class="badge bg-secondary">Últimos 200 registros</span>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Data/Hora</th>
                                <th>Usuário</th>
                                <th>Ação</th>
                                <th>Detalhes</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($logs) > 0): ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td class="small" style="white-space: nowrap;">
                                            <?= date('d/m/Y H:i:s', strtotime($log['data_hora'])); ?>
                                        </td>
                                        <td>
                                            <i class="bi bi-person"></i> <?= htmlspecialchars($log['nome_usuario']); ?>
                                        </td>
                                        <td>
                                            <?php
                                                // Cores dinâmicas para ações
                                                $classe = 'bg-secondary';
                                                if (stripos($log['acao'], 'Login') !== false) $classe = 'bg-success';
                                                if (stripos($log['acao'], 'Exclusão') !== false) $classe = 'bg-danger';
                                                if (stripos($log['acao'], 'Edição') !== false) $classe = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?= $classe; ?>"><?= htmlspecialchars($log['acao']); ?></span>
                                        </td>
                                        <td class="text-muted small">
                                            <?= htmlspecialchars($log['detalhes']); ?>
                                        </td>
                                        <td class="small text-muted font-monospace">
                                            <?= htmlspecialchars($log['ip']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Nenhum registro de log encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../estruturas/rodape.php'; ?>
</body>
</html>