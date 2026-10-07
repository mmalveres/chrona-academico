<?php //visoes/superadmin/listar_empresas.php ?>
<?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<?php
// ── Busca todos os usuários para a aba (já vem via SuperAdminControlador) ──
// Se não veio, busca aqui
if (!isset($todosUsuarios)) {
    $conn = \Aplicacao\Configuracao\BancoDeDados::getConexao();
    $stmt = $conn->query(
        "SELECT u.*, e.nome_fantasia as nome_empresa, e.plano
         FROM usuarios u
         LEFT JOIN empresas e ON u.id_empresa = e.id
         ORDER BY e.nome_fantasia ASC, u.nome ASC"
    );
    $todosUsuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

// Mapa id_empresa => e-mail do admin (para pré-preencher o envio de teste grátis)
$emailAdminPorEmpresa = [];
foreach ($todosUsuarios as $u) {
    if ($u['nivel_acesso'] === 'admin' && !empty($u['id_empresa']) && !isset($emailAdminPorEmpresa[$u['id_empresa']])) {
        $emailAdminPorEmpresa[$u['id_empresa']] = $u['email'];
    }
}

function badgePlano($plano) {
    $map = [
        'autonomo'    => ['Autônomo',    '#6c757d', '#fff'],
        'construtora' => ['Construtora', '#0d6efd', '#fff'],
        'empresarial' => ['Empresarial', '#FFB300', '#333'],
        'mensal'      => ['Construtora', '#0d6efd', '#fff'],
    ];
    $p = $map[$plano] ?? [ucfirst($plano), '#6c757d', '#fff'];
    return "<span class='badge fw-bold px-2 py-1' style='background:{$p[1]};color:{$p[2]};font-size:.7rem;'>{$p[0]}</span>";
}

function badgeVencimento($empresa) {
    $data = $empresa['data_vencimento'];
    $hoje = date('Y-m-d');
    if (!$data) return "<span class='text-muted small'>—</span>";
    if (date('Y', strtotime($data)) >= 2099)
        return "<span class='badge bg-info text-dark'>Vitalício</span>";
    if (strtotime($data) < strtotime($hoje))
        return "<span class='badge bg-danger'>Venceu " . date('d/m/Y', strtotime($data)) . "</span>";
    $diff = (strtotime($data) - strtotime($hoje)) / 86400;
    $cor = $diff <= 15 ? 'warning text-dark' : 'success';
    return "<span class='badge bg-{$cor}'>Vence " . date('d/m/Y', strtotime($data)) . "</span>";
}

function badgeCargo($nivel) {
    $map = [
        'admin'      => ['Administrador',   'bg-dark'],
        'engenheiro' => ['Engenheiro',      'bg-primary'],
        'mestre'     => ['Mestre de Obras', 'bg-success'],
        'financeiro' => ['Financeiro',      'bg-warning text-dark'],
        'cliente'    => ['Cliente/Fiscal',  'bg-info text-dark'],
    ];
    $c = $map[$nivel] ?? [ucfirst($nivel), 'bg-secondary'];
    return "<span class='badge {$c[1]} rounded-pill px-2 py-1 fw-bold' style='font-size:.68rem;'>{$c[0]}</span>";
}

// Aba ativa (empresas ou usuarios)
$abaAtiva = $_GET['aba'] ?? 'empresas';
?>

<!-- CABEÇALHO -->
<div class="d-flex justify-content-between align-items-center mb-3 mt-2">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--primary-color);">
            <i class="bi bi-shield-fill-check me-2 text-warning"></i> Super Admin
        </h4>
        <p class="text-secondary mb-0 small text-uppercase fw-bold d-none d-md-block">
            <?= count($empresas) ?> empresa(s) · <?= count($todosUsuarios) ?> usuário(s)
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?acao=precos_planos" class="btn btn-warning fw-bold shadow-sm" style="border-radius:10px;color:#333;">
            <i class="bi bi-currency-dollar me-1"></i>
            <span class="d-none d-sm-inline">Preços</span>
        </a>
        <?php if ($abaAtiva === 'empresas'): ?>
        <a href="index.php?acao=nova_empresa" class="btn btn-success fw-bold shadow-sm" style="border-radius: 10px;">
            <i class="bi bi-plus-lg me-1"></i>
            <span class="d-none d-sm-inline">Nova Empresa</span>
            <span class="d-inline d-sm-none">Nova</span>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ALERTAS -->
<?php if (isset($_GET['msg'])): ?>
<div class="mb-3">
    <?php $msg = $_GET['msg']; ?>
    <?php if ($msg === 'sucesso'): ?>
        <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i> Empresa cadastrada com sucesso!</div>
    <?php elseif ($msg === 'editado'): ?>
        <div class="alert alert-info border-0 shadow-sm"><i class="bi bi-pencil-fill me-2"></i> Dados atualizados!</div>
    <?php elseif ($msg === 'excluido'): ?>
        <div class="alert alert-warning border-0 shadow-sm"><i class="bi bi-trash-fill me-2"></i> Excluído com sucesso.</div>
    <?php elseif ($msg === 'usuario_atualizado'): ?>
        <div class="alert alert-info border-0 shadow-sm"><i class="bi bi-pencil-fill me-2"></i> Usuário atualizado!</div>
    <?php elseif ($msg === 'usuario_excluido'): ?>
        <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-trash-fill me-2"></i> Usuário excluído.</div>
    <?php elseif ($msg === 'erro_admin'): ?>
        <div class="alert alert-danger border-0 shadow-sm">Não é possível excluir o Admin Principal.</div>
    <?php elseif ($msg === 'erro_vinculo'): ?>
        <div class="alert alert-danger border-0 shadow-sm">Erro: possui dados vinculados.</div>
    <?php elseif ($msg === 'erro_proprio'): ?>
        <div class="alert alert-warning border-0 shadow-sm">Você não pode excluir sua própria conta.</div>
    <?php elseif ($msg === 'email_enviado'): ?>
        <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-envelope-check-fill me-2"></i> E-mail de teste grátis enviado com sucesso!</div>
    <?php elseif ($msg === 'email_invalido'): ?>
        <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i> Informe um e-mail válido para enviar o teste grátis.</div>
    <?php elseif ($msg === 'email_falhou'): ?>
        <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i> Não foi possível enviar o e-mail. Tente novamente.</div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ABAS -->
<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link fw-bold <?= $abaAtiva === 'empresas' ? 'active' : 'text-muted' ?>"
           href="index.php?acao=listar_empresas&aba=empresas">
            <i class="bi bi-buildings-fill me-1"></i> Empresas
            <span class="badge bg-white text-primary ms-1"><?= count($empresas) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold <?= $abaAtiva === 'usuarios' ? 'active' : 'text-muted' ?>"
           href="index.php?acao=listar_empresas&aba=usuarios">
            <i class="bi bi-people-fill me-1"></i> Todos os Usuários
            <span class="badge bg-white text-primary ms-1"><?= count($todosUsuarios) ?></span>
        </a>
    </li>
</ul>

<!-- ════════════════════════════════════════════
     ABA: EMPRESAS
     ════════════════════════════════════════════ -->
<?php if ($abaAtiva === 'empresas'): ?>

<!-- Tabela Desktop -->
<div class="card-custom d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-secondary small text-uppercase">
                <tr>
                    <th class="ps-3" width="50">#</th>
                    <th>Empresa</th>
                    <th class="text-center">Plano</th>
                    <th class="text-center">Vencimento</th>
                    <th class="text-center">Status</th>
                    <th class="text-end pe-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($empresas)): foreach ($empresas as $empresa): ?>
                <tr>
                    <td class="ps-3 fw-bold text-muted">#<?= $empresa['id'] ?></td>
                    <td>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($empresa['nome_fantasia'] ?? '') ?></div>
                        <small class="text-muted"><?= htmlspecialchars($empresa['razao_social'] ?? '') ?></small>
                        <?php if (!empty($empresa['cnpj'])): ?>
                            <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($empresa['cnpj']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= badgePlano($empresa['plano'] ?? 'autonomo') ?></td>
                    <td class="text-center"><?= badgeVencimento($empresa) ?></td>
                    <td class="text-center">
                        <?php if ($empresa['ativo']): ?>
                            <span class="badge bg-success-subtle text-success border border-success fw-bold">
                                <i class="bi bi-check-circle-fill me-1"></i>Ativo
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger fw-bold">
                                <i class="bi bi-slash-circle-fill me-1"></i>Bloqueado
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex justify-content-end gap-1">
                            <?php if ($empresa['id'] != 1): ?>
                                <a href="index.php?acao=sa_selecionar_empresa&id=<?= $empresa['id'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Visualizar como esta empresa">
                                    <i class="bi bi-eye"></i>
                                </a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    title="Enviar teste grátis por e-mail"
                                    data-bs-toggle="modal" data-bs-target="#modalTesteGratis"
                                    onclick="prepararModalTeste(<?= $empresa['id'] ?>, '<?= addslashes(htmlspecialchars($empresa['nome_fantasia'] ?? '')) ?>', '<?= addslashes(htmlspecialchars($emailAdminPorEmpresa[$empresa['id']] ?? '')) ?>')">
                                <i class="bi bi-envelope-paper"></i>
                            </button>
                            <a href="index.php?acao=editar_empresa&id=<?= $empresa['id'] ?>"
                               class="btn btn-sm btn-outline-warning fw-bold">
                                <i class="bi bi-pencil-square me-1"></i> Editar
                            </a>
                            <?php if ($empresa['id'] != 1): ?>
                                <a href="index.php?acao=excluir_empresa&id=<?= $empresa['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Excluir esta empresa e todos os seus dados?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i> Nenhuma empresa cadastrada.
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cards Mobile -->
<div class="d-md-none pb-5">
    <?php foreach ($empresas as $empresa): ?>
    <div class="card-custom mb-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($empresa['nome_fantasia'] ?? '') ?></h5>
                <small class="text-muted">#<?= $empresa['id'] ?><?= !empty($empresa['cnpj']) ? ' • ' . htmlspecialchars($empresa['cnpj']) : '' ?></small>
            </div>
            <?php if ($empresa['ativo']): ?>
                <i class="bi bi-check-circle-fill text-success fs-4"></i>
            <?php else: ?>
                <i class="bi bi-slash-circle-fill text-danger fs-4"></i>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center justify-content-between bg-light rounded p-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small fw-bold">Plano:</span>
                <?= badgePlano($empresa['plano'] ?? 'autonomo') ?>
            </div>
            <?= badgeVencimento($empresa) ?>
        </div>
        <div class="row g-2">
            <div class="col-6">
                <button type="button" class="btn btn-outline-success w-100 fw-bold" style="border-radius:10px;"
                        data-bs-toggle="modal" data-bs-target="#modalTesteGratis"
                        onclick="prepararModalTeste(<?= $empresa['id'] ?>, '<?= addslashes(htmlspecialchars($empresa['nome_fantasia'] ?? '')) ?>', '<?= addslashes(htmlspecialchars($emailAdminPorEmpresa[$empresa['id']] ?? '')) ?>')">
                    <i class="bi bi-envelope-paper me-1"></i> Teste Grátis
                </button>
            </div>
            <div class="col-6">
                <a href="index.php?acao=editar_empresa&id=<?= $empresa['id'] ?>"
                   class="btn btn-outline-warning w-100 fw-bold" style="border-radius:10px;">
                    <i class="bi bi-pencil-square me-1"></i> Editar
                </a>
            </div>
            <?php if ($empresa['id'] != 1): ?>
            <div class="col-6">
                <a href="index.php?acao=excluir_empresa&id=<?= $empresa['id'] ?>"
                   class="btn btn-outline-danger w-100 fw-bold"
                   onclick="return confirm('Excluir esta empresa?');"
                   style="border-radius:10px;">
                    <i class="bi bi-trash me-1"></i> Excluir
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal: Enviar Teste Grátis -->
<div class="modal fade" id="modalTesteGratis" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <form action="index.php?acao=sa_enviar_teste_gratis" method="POST">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-envelope-paper me-2 text-success"></i> Enviar Teste Grátis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_empresa" id="testeIdEmpresa">
                    <p class="text-muted small mb-3">
                        Envia um e-mail de boas-vindas convidando a empresa a testar o CHRONA gratuitamente por 30 dias.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nome do contato</label>
                        <input type="text" name="nome" id="testeNome" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">E-mail para envio</label>
                        <input type="email" name="email" id="testeEmail" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="bi bi-send-fill me-1"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function prepararModalTeste(id, nome, email) {
    document.getElementById('testeIdEmpresa').value = id;
    document.getElementById('testeNome').value = nome;
    document.getElementById('testeEmail').value = email;
}
</script>

<?php endif; // fim aba empresas ?>

<!-- ════════════════════════════════════════════
     ABA: TODOS OS USUÁRIOS
     ════════════════════════════════════════════ -->
<?php if ($abaAtiva === 'usuarios'): ?>

<!-- Filtros -->
<div class="card-custom mb-3 p-3">
    <div class="row g-2">
        <div class="col-md-4">
            <input type="text" id="buscaUser" class="form-control"
                   placeholder="🔍 Buscar por nome ou e-mail...">
        </div>
        <div class="col-md-4">
            <select id="filtroEmpresa" class="form-select">
                <option value="">Todas as empresas</option>
                <?php foreach ($empresas as $emp): ?>
                <option value="<?= htmlspecialchars($emp['nome_fantasia'] ?? '') ?>">
                    <?= htmlspecialchars($emp['nome_fantasia'] ?? '') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select id="filtroNivel" class="form-select">
                <option value="">Todos os perfis</option>
                <option value="admin">Administrador</option>
                <option value="engenheiro">Engenheiro</option>
                <option value="mestre">Mestre de Obras</option>
                <option value="financeiro">Financeiro</option>
                <option value="cliente">Cliente / Fiscal</option>
            </select>
        </div>
    </div>
</div>

<!-- Tabela Desktop -->
<div class="card-custom d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabelaUsuarios">
            <thead class="bg-light text-secondary small text-uppercase">
                <tr>
                    <th class="ps-3">Usuário</th>
                    <th>Empresa</th>
                    <th class="text-center">Perfil</th>
                    <th class="text-center">Status</th>
                    <th class="text-end pe-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todosUsuarios as $u): ?>
                <tr data-nome="<?= strtolower($u['nome']) ?>"
                    data-email="<?= strtolower($u['email']) ?>"
                    data-empresa="<?= htmlspecialchars($u['nome_empresa'] ?? '') ?>"
                    data-nivel="<?= $u['nivel_acesso'] ?>">
                    <td class="ps-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle d-flex justify-content-center align-items-center me-3 text-primary fw-bold border"
                                 style="width:36px;height:36px;font-size:.95rem;flex-shrink:0;">
                                <?= strtoupper(substr($u['nome'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small"><?= htmlspecialchars($u['nome']) ?></div>
                                <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold small"><?= htmlspecialchars($u['nome_empresa'] ?? '—') ?></div>
                        <div style="font-size:.7rem;"><?= badgePlano($u['plano'] ?? 'autonomo') ?></div>
                    </td>
                    <td class="text-center"><?= badgeCargo($u['nivel_acesso']) ?></td>
                    <td class="text-center">
                        <?php if ($u['ativo']): ?>
                            <span class="badge bg-success-subtle text-success border border-success" style="font-size:.68rem;">
                                <i class="bi bi-check-circle-fill me-1"></i>Ativo
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.68rem;">
                                <i class="bi bi-x-circle me-1"></i>Inativo
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-3">
                        <a href="index.php?acao=sa_editar_usuario&id=<?= $u['id'] ?>"
                           class="btn btn-sm btn-light text-primary me-1" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>

<a href="index.php?acao=sa_excluir_usuario&id=<?= $u['id'] ?>"
   class="btn btn-outline-danger w-100 fw-bold btn-excluir-sa"
   style="border-radius:10px;">
    <i class="bi bi-trash me-1"></i> Excluir
</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cards Mobile -->
<div class="d-md-none pb-5">
    <?php foreach ($todosUsuarios as $u): ?>
    <div class="card-custom mb-3 user-card"
         data-nome="<?= strtolower($u['nome']) ?>"
         data-email="<?= strtolower($u['email']) ?>"
         data-empresa="<?= htmlspecialchars($u['nome_empresa'] ?? '') ?>"
         data-nivel="<?= $u['nivel_acesso'] ?>">
        <div class="d-flex align-items-center mb-2">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold"
                 style="width:44px;height:44px;font-size:1.1rem;flex-shrink:0;">
                <?= strtoupper(substr($u['nome'], 0, 1)) ?>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-dark"><?= htmlspecialchars($u['nome']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
            </div>
            <?= $u['ativo'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-slash-circle-fill text-danger"></i>' ?>
        </div>
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <?= badgeCargo($u['nivel_acesso']) ?>
            <?= badgePlano($u['plano'] ?? 'autonomo') ?>
            <span class="badge bg-light text-dark border" style="font-size:.68rem;">
                <?= htmlspecialchars($u['nome_empresa'] ?? '—') ?>
            </span>
        </div>
        <div class="row g-2">
            <div class="col-6">
                <a href="index.php?acao=sa_editar_usuario&id=<?= $u['id'] ?>"
                   class="btn btn-outline-primary w-100 fw-bold" style="border-radius:10px;">
                    <i class="bi bi-pencil-square me-1"></i> Editar
                </a>
            </div>
            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
            <div class="col-6">
                <a href="index.php?acao=sa_excluir_usuario&id=<?= $u['id'] ?>"
                   class="btn btn-outline-danger w-100 fw-bold"
                   onclick="return confirm('Excluir <?= addslashes(htmlspecialchars($u['nome'])) ?>?');"
                   style="border-radius:10px;">
                    <i class="bi bi-trash me-1"></i> Excluir
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function filtrar() {
    const busca   = document.getElementById('buscaUser').value.toLowerCase();
    const empresa = document.getElementById('filtroEmpresa').value;
    const nivel   = document.getElementById('filtroNivel').value;

    document.querySelectorAll('#tabelaUsuarios tbody tr').forEach(tr => {
        const ok = (!busca   || tr.dataset.nome.includes(busca) || tr.dataset.email.includes(busca))
                && (!empresa || tr.dataset.empresa === empresa)
                && (!nivel   || tr.dataset.nivel   === nivel);
        tr.style.display = ok ? '' : 'none';
    });

    document.querySelectorAll('.user-card').forEach(c => {
        const ok = (!busca   || c.dataset.nome.includes(busca) || c.dataset.email.includes(busca))
                && (!empresa || c.dataset.empresa === empresa)
                && (!nivel   || c.dataset.nivel   === nivel);
        c.style.display = ok ? '' : 'none';
    });
}

document.getElementById('buscaUser').addEventListener('input', filtrar);
document.getElementById('filtroEmpresa').addEventListener('change', filtrar);
document.getElementById('filtroNivel').addEventListener('change', filtrar);

// Delegação de evento no document — garante que funciona independente de quando carrega
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-excluir-sa');
    if (!btn) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    const nome = btn.closest('tr')
        ?.querySelector('.fw-bold.text-dark.small')
        ?.textContent?.trim() ?? 'este usuário';
    if (confirm('Tem certeza que deseja excluir ' + nome + '?')) {
        window.location.href = btn.getAttribute('href');
    }
});
</script>

<?php endif; // fim aba usuarios ?>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>