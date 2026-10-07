<?php //visoes/superadmin/editar_empresa.php ?>
<?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<style>
    .plano-card {
        border: 2px solid #dee2e6; border-radius: 14px; padding: 16px 18px;
        cursor: pointer; transition: all 0.2s; background: #fff;
    }
    .plano-card:hover { border-color: #0d6efd; background: #f0f6ff; }
    .plano-card.selected-autonomo    { border-color: #6c757d; background: #f8f9fa; }
    .plano-card.selected-construtora { border-color: #0d6efd; background: #e8f0fe; }
    .plano-card.selected-empresarial { border-color: #FFB300; background: #fffbea; }
    .plano-card input[type=radio] { display: none; }
    .plano-icon { font-size: 1.8rem; margin-bottom: 6px; }
    .plano-preco { font-family: 'Montserrat', sans-serif; font-size: 1.3rem; font-weight: 800; }
    .plano-feature { font-size: 0.8rem; color: #555; display: flex; align-items: center; gap: 6px; margin-top: 4px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--primary-color);">
            <i class="bi bi-pencil-square me-2"></i> Editar Cliente
        </h4>
        <p class="text-muted small mb-0">
            <span class="badge bg-secondary">#<?= $empresa['id']; ?></span>
            <?= htmlspecialchars($empresa['nome_fantasia']); ?>
        </p>
    </div>
    <a href="index.php?acao=listar_empresas" class="btn btn-outline-secondary" style="border-radius: 10px;">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<form method="POST" action="index.php?acao=editar_empresa&id=<?= $empresa['id']; ?>">

    <!-- ── DADOS DA EMPRESA ─────────────────────────────────────── -->
    <div class="card-custom mb-4">
        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
            <i class="bi bi-building me-2 text-primary"></i> Dados da Empresa
        </h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Nome Fantasia <span class="text-danger">*</span></label>
                <input type="text" name="nome_fantasia" class="form-control"
                       value="<?= htmlspecialchars($empresa['nome_fantasia'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Razão Social</label>
                <input type="text" name="razao_social" class="form-control"
                       value="<?= htmlspecialchars($empresa['razao_social'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">CNPJ</label>
                <input type="text" name="cnpj" id="cnpj" class="form-control"
                       value="<?= htmlspecialchars($empresa['cnpj'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <!-- ── CREDENCIAIS DO ADMIN ──────────────────────────────────── -->
    <div class="card-custom mb-4">
        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
            <i class="bi bi-key-fill me-2 text-danger"></i> Credenciais de Acesso (Admin)
        </h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">E-mail de Acesso</label>
                <input type="email" name="usuario_email" class="form-control"
                       value="<?= htmlspecialchars($empresa['usuario_email'] ?? $empresa['email'] ?? ''); ?>"
                       placeholder="email@exemplo.com">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Redefinir Senha</label>
                <input type="text" name="usuario_senha" class="form-control"
                       placeholder="Deixe vazio para manter a atual">
                <div class="form-text text-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Preencha apenas se o cliente solicitou a troca de senha.
                </div>
            </div>
        </div>
    </div>

    <!-- ── PLANO ────────────────────────────────────────────────── -->
    <div class="card-custom mb-4">
        <h5 class="fw-bold text-dark mb-1 pb-2 border-bottom">
            <i class="bi bi-star-fill me-2 text-warning"></i> Plano Contratado
        </h5>
        <p class="text-muted small mb-4">Plano atual:
            <strong id="planoAtualLabel">
                <?php
                    $labels = ['autonomo'=>'Autônomo','construtora'=>'Construtora','empresarial'=>'Empresarial','mensal'=>'Construtora'];
                    echo $labels[$empresa['plano'] ?? 'autonomo'] ?? ucfirst($empresa['plano']);
                ?>
            </strong>
        </p>

        <div class="row g-3 mb-4">

            <!-- Autônomo -->
            <div class="col-md-4">
                <label class="plano-card d-block" id="card-autonomo" onclick="selecionarPlano('autonomo')">
                    <input type="radio" name="plano" value="autonomo"
                           <?= in_array($empresa['plano'] ?? '', ['autonomo']) ? 'checked' : ''; ?>>
                    <div class="plano-icon">🔧</div>
                    <div class="fw-bold text-dark">Autônomo</div>
                    <div class="plano-preco text-secondary">R$ 49<small class="fw-normal fs-6">/mês</small></div>
                    <div class="mt-2">
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> 1 Usuário</div>
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> 1 Obra Ativa</div>
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> RDO Ilimitado</div>
                        <div class="plano-feature"><i class="bi bi-x-circle-fill text-danger"></i> Sem Cronograma</div>
                        <div class="plano-feature"><i class="bi bi-x-circle-fill text-danger"></i> Sem Medição</div>
                    </div>
                </label>
            </div>

            <!-- Construtora -->
            <div class="col-md-4">
                <label class="plano-card d-block" id="card-construtora" onclick="selecionarPlano('construtora')">
                    <input type="radio" name="plano" value="construtora"
                           <?= in_array($empresa['plano'] ?? '', ['construtora','mensal']) ? 'checked' : ''; ?>>
                    <div class="plano-icon">🏗️</div>
                    <div class="fw-bold text-dark">Construtora <span class="badge bg-primary ms-1" style="font-size:.6rem;">+ Vendido</span></div>
                    <div class="plano-preco text-primary">R$ 149<small class="fw-normal fs-6">/mês</small></div>
                    <div class="mt-2">
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> 3 Usuários</div>
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> Até 3 Obras</div>
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> Cronograma</div>
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> Medição Automática</div>
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> Importar Orçamento</div>
                    </div>
                </label>
            </div>

            <!-- Empresarial -->
            <div class="col-md-4">
                <label class="plano-card d-block" id="card-empresarial" onclick="selecionarPlano('empresarial')">
                    <input type="radio" name="plano" value="empresarial"
                           <?= ($empresa['plano'] ?? '') === 'empresarial' ? 'checked' : ''; ?>>
                    <div class="plano-icon">🏢</div>
                    <div class="fw-bold" style="color:#b8860b;">Empresarial</div>
                    <div class="plano-preco" style="color:#b8860b;">R$ 250<small class="fw-normal fs-6 text-muted">/mês</small></div>
                    <div class="mt-2">
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> Usuários Ilimitados</div>
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> Obras Ilimitadas</div>
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> Todas as funções</div>
                        <div class="plano-feature"><i class="bi bi-check-circle-fill text-success"></i> Relatórios Plus</div>
                        <div class="plano-feature"><i class="bi bi-whatsapp text-success"></i> Suporte WhatsApp</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Vencimento + Atalhos + Status -->
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Vencimento da Licença</label>
                <input type="date" name="data_vencimento" id="dataVencimento" class="form-control"
                       value="<?= $empresa['data_vencimento'] ?? ''; ?>">
                <div class="form-text">Para renovar, altere para uma data futura.</div>
            </div>
            <div class="col-md-5">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="renovar(30)">+30 dias</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="renovar(90)">+90 dias</button>
                    <button type="button" class="btn btn-sm btn-outline-primary"   onclick="renovar(365)">+1 ano</button>
                    <button type="button" class="btn btn-sm btn-outline-warning"   onclick="renovar(36500)">Vitalício</button>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-center justify-content-center">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="ativoSwitch" name="ativo"
                           <?= $empresa['ativo'] ? 'checked' : ''; ?>
                           style="width: 50px; height: 25px;">
                    <label class="form-check-label ms-2 fw-bold" for="ativoSwitch">
                        Acesso Liberado
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões -->
    <div class="d-flex justify-content-between pb-4">
        <a href="index.php?acao=listar_empresas" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary fw-bold px-5 py-2">
            <i class="bi bi-check-lg me-2"></i> Salvar Alterações
        </button>
    </div>

</form>

<script>
// Plano atual vindo do PHP
const planoAtual = '<?= in_array($empresa['plano'] ?? '', ['mensal','construtora']) ? 'construtora' : ($empresa['plano'] ?? 'autonomo') ?>';

function selecionarPlano(plano) {
    ['autonomo','construtora','empresarial'].forEach(p => {
        document.getElementById('card-' + p).className = 'plano-card d-block';
    });
    document.getElementById('card-' + plano).classList.add('selected-' + plano);
    document.querySelector(`input[value="${plano}"]`).checked = true;
}

// Renovar a partir da data atual (não da data já salva)
function renovar(dias) {
    const base = new Date();
    base.setDate(base.getDate() + dias);
    document.getElementById('dataVencimento').value = base.toISOString().split('T')[0];
}

// Inicia com o plano atual destacado
document.addEventListener('DOMContentLoaded', function() {
    selecionarPlano(planoAtual);
});

// Máscara CNPJ
document.getElementById('cnpj').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g,'').substring(0,14);
    v = v.replace(/^(\d{2})(\d)/,'$1.$2');
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/,'$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/,'.$1/$2');
    v = v.replace(/(\d{4})(\d)/,'$1-$2');
    e.target.value = v;
});
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>