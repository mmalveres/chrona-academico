<?php //visoes/superadmin/nova_empresa.php ?>
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
    .plano-feature i { font-size: 0.75rem; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--primary-color);">
            <i class="bi bi-building-add me-2"></i> Cadastrar Novo Cliente
        </h4>
        <p class="text-muted small mb-0">Preencha os dados da empresa e escolha o plano contratado.</p>
    </div>
    <a href="index.php?acao=listar_empresas" class="btn btn-outline-secondary" style="border-radius: 10px;">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<?php if (isset($erro)): ?>
    <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-x-circle-fill me-2"></i> <?= $erro; ?></div>
<?php endif; ?>

<form method="POST" action="index.php?acao=nova_empresa">

    <!-- ── DADOS DA EMPRESA ─────────────────────────────────────── -->
    <div class="card-custom mb-4">
        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
            <i class="bi bi-building me-2 text-primary"></i> Dados da Empresa
        </h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Nome Fantasia <span class="text-danger">*</span></label>
                <input type="text" name="nome_fantasia" class="form-control" placeholder="Ex: Construtora Silva" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Razão Social</label>
                <input type="text" name="razao_social" class="form-control" placeholder="Ex: Silva Engenharia Ltda.">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">CNPJ</label>
                <input type="text" name="cnpj" class="form-control" placeholder="00.000.000/0000-00" id="cnpj">
            </div>
        </div>
    </div>

    <!-- ── PLANO ────────────────────────────────────────────────── -->
    <div class="card-custom mb-4">
        <h5 class="fw-bold text-dark mb-1 pb-2 border-bottom">
            <i class="bi bi-star-fill me-2 text-warning"></i> Plano Contratado
        </h5>
        <p class="text-muted small mb-4">Selecione o plano e defina a data de vencimento da licença.</p>

        <div class="row g-3 mb-4" id="planoCards">

            <!-- Autônomo -->
            <div class="col-md-4">
                <label class="plano-card d-block" id="card-autonomo" onclick="selecionarPlano('autonomo')">
                    <input type="radio" name="plano" value="autonomo">
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
                    <input type="radio" name="plano" value="construtora" checked>
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
                    <input type="radio" name="plano" value="empresarial">
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

        <!-- Data de vencimento -->
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Vencimento da Licença <span class="text-danger">*</span></label>
                <input type="date" name="data_vencimento" id="dataVencimento" class="form-control" required>
                <div class="form-text">Defina até quando o acesso está liberado.</div>
            </div>
            <div class="col-md-5">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="definirVencimento(30)">+30 dias</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="definirVencimento(90)">+90 dias</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="definirVencimento(365)">+1 ano</button>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="definirVencimento(36500)">Vitalício</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── DADOS DO ADMIN ────────────────────────────────────────── -->
    <div class="card-custom mb-4">
        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
            <i class="bi bi-person-check-fill me-2 text-success"></i> Primeiro Usuário Admin
        </h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Nome do Responsável <span class="text-danger">*</span></label>
                <input type="text" name="usuario_nome" class="form-control" placeholder="Ex: João Silva" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">E-mail de Acesso <span class="text-danger">*</span></label>
                <input type="email" name="usuario_email" class="form-control" placeholder="joao@empresa.com.br" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Senha Provisória <span class="text-danger">*</span></label>
                <input type="text" name="usuario_senha" class="form-control" placeholder="Mínimo 6 caracteres" required>
                <div class="form-text">O cliente deve alterar a senha no primeiro acesso.</div>
            </div>
        </div>
    </div>

    <!-- Botões -->
    <div class="d-flex justify-content-between pb-4">
        <a href="index.php?acao=listar_empresas" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-success fw-bold px-5 py-2">
            <i class="bi bi-check-lg me-2"></i> Cadastrar Empresa
        </button>
    </div>

</form>

<script>
// Seleciona plano visualmente
function selecionarPlano(plano) {
    ['autonomo','construtora','empresarial'].forEach(p => {
        const card = document.getElementById('card-' + p);
        card.className = 'plano-card d-block';
    });
    document.getElementById('card-' + plano).classList.add('selected-' + plano);
    document.querySelector(`input[value="${plano}"]`).checked = true;
}

// Atalhos de data de vencimento
function definirVencimento(dias) {
    const d = new Date();
    d.setDate(d.getDate() + dias);
    document.getElementById('dataVencimento').value = d.toISOString().split('T')[0];
}

// Inicia com Construtora selecionada e +1 ano
document.addEventListener('DOMContentLoaded', function() {
    selecionarPlano('construtora');
    definirVencimento(365);
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