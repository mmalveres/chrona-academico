<?php
/** @var array $usuarios */
use Aplicacao\Helpers\CsrfHelper;
include __DIR__ . '/../estruturas/cabecalho.php';
$csrfToken   = CsrfHelper::gerarToken();
$csrfQuery   = CsrfHelper::queryParam();

function renderizaBadgeCargo($nivel) {
    $cargos = [
        'admin'       => ['nome' => 'Administrador',    'cor' => 'bg-dark'],
        'engenheiro'  => ['nome' => 'Engenheiro',       'cor' => 'bg-primary'],
        'mestre'      => ['nome' => 'Mestre de Obras',  'cor' => 'bg-success'],
        'financeiro'  => ['nome' => 'Financeiro',       'cor' => 'bg-warning text-dark'],
        'funcionario' => ['nome' => 'Funcionário',      'cor' => 'bg-secondary'],
        'cliente'     => ['nome' => 'Cliente / Fiscal', 'cor' => 'bg-info text-dark'],
    ];
    $label  = $cargos[$nivel]['nome'] ?? ucfirst($nivel);
    $classe = $cargos[$nivel]['cor']  ?? 'bg-secondary';
    echo "<span class='badge {$classe} rounded-pill px-3 py-2 fw-bold' style='font-size:.75rem;'>{$label}</span>";
}
?>

<!-- Campo de Busca -->
<div class="mb-3">
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="campoBuscaUsuarios" class="form-control border-start-0 ps-0"
               placeholder="Buscar por nome ou e-mail..." oninput="filtrarUsuarios(this.value)">
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h4 class="fw-bold mb-1" style="color:var(--primary-color);">Equipe</h4>
        <p class="text-secondary mb-0 small text-uppercase fw-bold">Gerencie usuários e permissões</p>
    </div>
    <a href="index.php?acao=novo_usuario"
       class="btn btn-action-lg btn-green mb-0 d-flex align-items-center justify-content-center shadow-sm"
       style="width:auto;padding:10px 20px;">
        <i class="bi bi-person-plus-fill me-2"></i>
        <span class="d-none d-sm-inline">Novo Usuário</span>
        <span class="d-inline d-sm-none">Novo</span>
    </a>
</div>

<?php if (isset($_GET['msg']) || isset($_GET['modal'])): ?>
<div class="mb-4">
    <?php $msg = $_GET['msg'] ?? ''; ?>
    <?php if ($msg === 'sucesso' || $msg === 'usuario_criado'): ?>
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i> Usuário criado com sucesso!
        </div>
    <?php elseif ($msg === 'atualizado' || $msg === 'usuario_atualizado'): ?>
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
            <i class="bi bi-pencil-fill fs-4 me-3"></i> Dados atualizados com sucesso!
        </div>
    <?php elseif ($msg === 'usuario_excluido'): ?>
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center">
            <i class="bi bi-trash-fill fs-4 me-3"></i> Usuário excluído com sucesso.
        </div>
    <?php elseif ($msg === 'erro_proprio'): ?>
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i> Você não pode excluir sua própria conta.
        </div>
    <?php elseif ($msg === 'erro_exclusao'): ?>
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center">
            <i class="bi bi-x-circle-fill fs-4 me-3"></i> Erro ao excluir. Tente novamente.
        </div>
    <?php elseif ($msg === 'erro_vinculo'): ?>
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center">
            <i class="bi bi-x-circle-fill fs-4 me-3"></i> Usuário possui vínculos. Apenas desative-o.
        </div>

    <?php elseif ($msg === 'obras_vinculadas'): ?>
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center">
        <i class="bi bi-link-45deg fs-4 me-3"></i>
        Obras vinculadas com sucesso ao usuário existente!
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- TABELA DESKTOP -->
<div class="card-custom d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-custom align-middle w-100">
            <thead>
                <tr>
                    <th class="ps-0">COLABORADOR</th>
                    <th>CARGO</th>
                    <th>STATUS</th>
                    <th class="text-end pe-0">AÇÕES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td class="ps-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle d-flex justify-content-center align-items-center me-3 text-primary fw-bold border"
                                 style="width:40px;height:40px;font-size:1.1rem;">
                                <?= strtoupper(substr($u['nome'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($u['nome']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?php renderizaBadgeCargo($u['nivel_acesso']); ?></td>
                    <td>
                        <?php if ($u['ativo']): ?>
                            <span class="badge-status badge-active"><i class="bi bi-check-circle me-1"></i> Ativo</span>
                        <?php else: ?>
                            <span class="badge-status badge-done"><i class="bi bi-x-circle me-1"></i> Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-0">
                        <a href="index.php?acao=editar_usuario&id=<?= $u['id'] ?>"
                           class="btn btn-sm btn-light text-primary me-1" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                        <button type="button"
                                class="btn btn-sm btn-light text-danger btn-excluir"
                                data-id="<?= $u['id'] ?>"
                                data-nome="<?= htmlspecialchars($u['nome']) ?>"
                                title="Excluir">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- CARDS MOBILE -->
<div class="d-md-none">
    <?php foreach ($usuarios as $u): ?>
    <div class="card-custom p-3 mb-3 position-relative">
        <div class="position-absolute top-0 end-0 mt-3 me-3">
            <?php if ($u['ativo']): ?>
                <i class="bi bi-check-circle-fill text-success fs-4"></i>
            <?php else: ?>
                <i class="bi bi-slash-circle-fill text-danger fs-4"></i>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center mb-3">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold border border-primary border-opacity-10"
                 style="width:50px;height:50px;font-size:1.3rem;">
                <?= strtoupper(substr($u['nome'], 0, 1)) ?>
            </div>
            <div>
                <h6 class="fw-bold text-dark mb-0 fs-5"><?= htmlspecialchars($u['nome']) ?></h6>
                <small class="text-muted">ID: #<?= $u['id'] ?></small>
            </div>
        </div>
        <div class="mb-4">
            <div class="d-flex align-items-center text-muted mb-2">
                <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($u['email']) ?>
            </div>
            <?php renderizaBadgeCargo($u['nivel_acesso']); ?>
        </div>
        <div class="row g-2">
            <div class="col-<?= ($u['id'] != $_SESSION['usuario_id']) ? '6' : '12' ?>">
                <a href="index.php?acao=editar_usuario&id=<?= $u['id'] ?>"
                   class="btn btn-outline-primary w-100 fw-bold py-2" style="border-radius:12px;">
                    <i class="bi bi-pencil-square me-1"></i> Editar
                </a>
            </div>
            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
            <div class="col-6">
                <button type="button"
                        class="btn btn-outline-danger w-100 fw-bold py-2 btn-excluir"
                        data-id="<?= $u['id'] ?>"
                        data-nome="<?= htmlspecialchars($u['nome']) ?>"
                        style="border-radius:12px;">
                    <i class="bi bi-trash me-1"></i> Excluir
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($usuarios)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
        <p>Nenhum usuário encontrado.</p>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════
     MODAL 1 — Confirmação simples (sem obras)
     ═══════════════════════════════════════════ -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2 pb-1">
                <p class="text-muted mb-0">
                    Tem certeza que deseja excluir o usuário
                    <strong id="nomeUsuarioConfirmar" class="text-dark"></strong>?<br>
                    <small class="text-danger">Esta ação não pode ser desfeita.</small>
                </p>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="linkConfirmarExclusao" class="btn btn-danger fw-bold px-4">
                    <i class="bi bi-trash-fill me-2"></i>Excluir
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     MODAL 2 — Substituir responsável técnico
     ═══════════════════════════════════════════ -->
<div class="modal fade" id="modalSubstituir" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header border-0 pb-2" style="background:linear-gradient(135deg,#fff3cd,#ffe69c);">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        Substituição necessária
                    </h5>
                    <p class="text-muted small mb-0 mt-1">
                        Este usuário é responsável técnico de obras ativas.
                    </p>
                </div>
            </div>

            <div class="modal-body p-4">

                <p class="mb-3">
                    Antes de excluir <strong id="nomeUsuarioSubstituir" class="text-danger"></strong>,
                    escolha um novo responsável para as obras abaixo:
                </p>

                <!-- obras afetadas -->
                <div id="listaObrasModal"
                     class="mb-3 p-3 rounded border bg-light"
                     style="max-height:150px;overflow-y:auto;">
                    <div class="text-center text-muted small py-2">
                        <div class="spinner-border spinner-border-sm me-2"></div>Carregando obras...
                    </div>
                </div>

                <form method="POST" action="index.php?acao=excluir_usuario_confirmar" id="formSubstituir">
                    <?= CsrfHelper::inputHidden() ?>
                    <input type="hidden" name="id_usuario" id="inputIdUsuario">

                    <label class="form-label fw-bold small text-uppercase text-muted mb-1">
                        Novo responsável técnico
                    </label>
                    <select name="id_substituto" id="selectSubstituto" class="form-select mb-3" required>
                        <option value="">— Selecione um usuário —</option>
                        <?php foreach ($usuarios as $u):
                            if (in_array($u['nivel_acesso'], ['admin','engenheiro'])): ?>
                        <option value="<?= $u['id'] ?>" data-original-id="<?= $u['id'] ?>">
                            <?= htmlspecialchars($u['nome']) ?>
                            (<?= ucfirst($u['nivel_acesso']) ?>)
                        </option>
                        <?php endif; endforeach; ?>
                    </select>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger fw-bold py-2 rounded-3">
                            <i class="bi bi-arrow-left-right me-2"></i>
                            Substituir e excluir usuário
                        </button>
                        <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function filtrarUsuarios(termo) {
    termo = termo.toLowerCase().trim();
    document.querySelectorAll('table tbody tr').forEach(function(tr) {
        tr.style.display = (!termo || tr.innerText.toLowerCase().includes(termo)) ? '' : 'none';
    });
    document.querySelectorAll('.card-custom.p-3.mb-3').forEach(function(card) {
        card.style.display = (!termo || card.innerText.toLowerCase().includes(termo)) ? '' : 'none';
    });
}

document.querySelectorAll('.btn-excluir').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id   = this.dataset.id;
        const nome = this.dataset.nome;

        // Verifica via AJAX se o usuário é responsável técnico de obras
        fetch('index.php?acao=verificar_responsavel&id=' + id)
            .then(r => r.json())
            .then(data => {

                if (data.obras && data.obras.length > 0) {
                    // ── TEM obras vinculadas → modal de substituição ──
                    document.getElementById('nomeUsuarioSubstituir').textContent = nome;
                    document.getElementById('inputIdUsuario').value = id;

                    // Remove o próprio usuário do select (não pode ser substituto de si mesmo)
                    const select = document.getElementById('selectSubstituto');
                    Array.from(select.options).forEach(opt => {
                        if (opt.value == id) {
                            opt.remove();
                        }
                    });

                    // Preenche lista de obras afetadas
                    const lista = document.getElementById('listaObrasModal');
                    lista.innerHTML = data.obras.map(o =>
                        `<div class="d-flex align-items-center gap-2 py-1 border-bottom">
                            <i class="bi bi-building-fill text-warning"></i>
                            <span class="fw-bold text-dark">${o.nome}</span>
                         </div>`
                    ).join('');

                    new bootstrap.Modal(document.getElementById('modalSubstituir')).show();

                } else {
                    // ── SEM obras vinculadas → confirmação simples ──
                    document.getElementById('nomeUsuarioConfirmar').textContent = nome;
                    document.getElementById('linkConfirmarExclusao').href =
                        'index.php?acao=excluir_usuario&id=' + id + '&<?= $csrfQuery ?>';

                    new bootstrap.Modal(document.getElementById('modalConfirmarExclusao')).show();
                }
            })
            .catch(() => {
                // Fallback se AJAX falhar
                if (confirm('Excluir o usuário ' + nome + '?')) {
                    window.location.href = 'index.php?acao=excluir_usuario&id=' + id;
                }
            });
    });
});
</script>

<?php
$urlBase = 'index.php?acao=listar_usuarios';
include __DIR__ . '/../estruturas/paginacao.php';
include __DIR__ . '/../estruturas/rodape.php';
?>