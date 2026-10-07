<?php // visoes/superadmin/logs_sistema.php ?>
<?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-2">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Logs do Sistema</h4>
        <small class="text-muted">Registro de logins e ações realizadas por todos os usuários</small>
    </div>
    <a href="index.php?acao=listar_empresas" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<!-- Filtros -->
<div class="card-custom mb-3">
    <form method="GET" action="index.php" class="row g-2 align-items-end">
        <input type="hidden" name="acao" value="logs_sistema">

        <div class="col-md-4">
            <label class="form-label small fw-bold">Empresa</label>
            <select name="empresa" class="form-select form-select-sm">
                <option value="">Todas as empresas</option>
                <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e['id'] ?>" <?= ($filtroEmpresa == $e['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nome_fantasia']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-5">
            <label class="form-label small fw-bold">Buscar (ação, usuário ou detalhe)</label>
            <input type="text" name="busca" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($filtroBusca) ?>" placeholder="Ex: Login, Obra criada...">
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="bi bi-search me-1"></i> Filtrar
            </button>
            <a href="index.php?acao=logs_sistema" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<!-- Contagem -->
<div class="d-flex justify-content-between align-items-center mb-2">
    <small class="text-muted">
        <?= number_format($totalRegistros, 0, ',', '.') ?> registro(s) encontrado(s)
    </small>
    <small class="text-muted">Página <?= $pagina ?> de <?= $totalPaginas ?></small>
</div>

<!-- Tabela Desktop -->
<div class="card-custom d-none d-md-block p-0" style="overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.85rem;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th class="ps-3 py-2">Data/Hora</th>
                    <th>Usuário</th>
                    <th>Empresa</th>
                    <th>Ação</th>
                    <th>Detalhes</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Nenhum registro encontrado.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="ps-3 text-nowrap text-muted" style="font-size:.8rem;">
                        <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                    </td>
                    <td class="fw-bold"><?= htmlspecialchars($log['nome_usuario']) ?></td>
                    <td>
                        <?php if ($log['id_empresa'] == 1): ?>
                            <span class="badge" style="background:#FFB300;color:#003366;">SuperAdmin</span>
                        <?php elseif ($log['id_empresa']): ?>
                            <span class="text-dark small">Empresa #<?= $log['id_empresa'] ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $acaoBadge = [
                            'Login'   => 'bg-success',
                            'Logout'  => 'bg-secondary',
                            'Empresa criada'  => 'bg-primary',
                            'Empresa editada' => 'bg-warning text-dark',
                            'Empresa excluída'=> 'bg-danger',
                            'Usuário editado' => 'bg-warning text-dark',
                            'Usuário excluído'=> 'bg-danger',
                        ];
                        $badgeClass = $acaoBadge[$log['acao']] ?? 'bg-info text-dark';
                        ?>
                        <span class="badge <?= $badgeClass ?>" style="font-size:.75rem;">
                            <?= htmlspecialchars($log['acao']) ?>
                        </span>
                    </td>
                    <td class="text-muted" style="max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($log['detalhes'] ?? '—') ?>
                    </td>
                    <td class="text-muted" style="font-size:.78rem;"><?= htmlspecialchars($log['ip'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cards Mobile -->
<div class="d-md-none">
    <?php if (empty($logs)): ?>
    <div class="text-center text-muted py-4">Nenhum registro encontrado.</div>
    <?php else: ?>
    <?php foreach ($logs as $log): ?>
    <div class="card-custom mb-2 p-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
            <strong><?= htmlspecialchars($log['nome_usuario']) ?></strong>
            <small class="text-muted"><?= date('d/m H:i', strtotime($log['created_at'])) ?></small>
        </div>
        <div class="mb-1">
            <?php
            $acaoBadge = ['Login'=>'bg-success','Logout'=>'bg-secondary','Empresa criada'=>'bg-primary','Empresa editada'=>'bg-warning text-dark','Empresa excluída'=>'bg-danger','Usuário editado'=>'bg-warning text-dark','Usuário excluído'=>'bg-danger'];
            $badgeClass = $acaoBadge[$log['acao']] ?? 'bg-info text-dark';
            ?>
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($log['acao']) ?></span>
        </div>
        <?php if ($log['detalhes']): ?>
        <small class="text-muted"><?= htmlspecialchars($log['detalhes']) ?></small>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Paginação -->
<?php if ($totalPaginas > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center flex-wrap">
        <?php
        $queryBase = http_build_query(array_filter([
            'acao'    => 'logs_sistema',
            'empresa' => $filtroEmpresa ?: null,
            'busca'   => $filtroBusca ?: null,
        ]));
        $inicio = max(1, $pagina - 3);
        $fim    = min($totalPaginas, $pagina + 3);
        ?>
        <?php if ($pagina > 1): ?>
        <li class="page-item">
            <a class="page-link" href="index.php?<?= $queryBase ?>&pagina=<?= $pagina - 1 ?>">‹</a>
        </li>
        <?php endif; ?>
        <?php for ($p = $inicio; $p <= $fim; $p++): ?>
        <li class="page-item <?= ($p == $pagina) ? 'active' : '' ?>">
            <a class="page-link" href="index.php?<?= $queryBase ?>&pagina=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
        <li class="page-item">
            <a class="page-link" href="index.php?<?= $queryBase ?>&pagina=<?= $pagina + 1 ?>">›</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
