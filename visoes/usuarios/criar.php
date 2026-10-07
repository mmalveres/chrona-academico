<?php //visoes/usuarios/criar.php
include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item">
                    <a href="index.php?acao=listar_usuarios" class="text-decoration-none text-muted">Equipe</a>
                </li>
                <li class="breadcrumb-item active fw-bold text-primary">Novo Usuário</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0" style="color:var(--primary-color);">
            <i class="bi bi-person-plus-fill me-2"></i>Novo Membro da Equipe
        </h4>
    </div>
</div>

<?php if (isset($erro)): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <i class="bi bi-x-circle-fill me-2"></i><?= $erro ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card-custom">
            <form method="POST" action="index.php?acao=novo_usuario" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nome Completo *</label>
                    <input type="text" name="nome" class="form-control py-2"
                           required placeholder="Ex: João da Silva">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">E-mail (Login) *</label>
                    <input type="email" name="email" class="form-control py-2"
                           required placeholder="joao@email.com">
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Senha Inicial *</label>
                        <input type="password" name="senha" class="form-control py-2" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Função / Nível de Acesso *</label>
                        <select name="nivel_acesso" class="form-select py-2" id="selectNivel" required>
                            <option value="" selected disabled>Selecione...</option>
                            <option value="engenheiro">Engenheiro</option>
                            <option value="mestre">Mestre de Obras</option>
                            <option value="financeiro">Financeiro</option>
                            <option value="cliente">Cliente</option>
                            <?php if ($_SESSION['is_superadmin'] ?? false): ?>
                            <option value="fiscal">Fiscal de Obra (Órgão Externo)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Legenda dinâmica do perfil -->
                <div class="mt-3 p-3 rounded border" id="legendaPerfil" style="display:none;background:#f8f9fa;">
                    <div id="legendaEngenheiro" class="d-none small">
                        <i class="bi bi-hard-hat text-primary me-2"></i>
                        <strong>Engenheiro:</strong> acesso total — diário, medição, cronograma e documentos.
                    </div>
                    <div id="legendaMestre" class="d-none small">
                        <i class="bi bi-tools text-success me-2"></i>
                        <strong>Mestre de Obras:</strong> cria e edita diários. Sem acesso a financeiro e cronograma.
                    </div>
                    <div id="legendaFinanceiro" class="d-none small">
                        <i class="bi bi-cash-coin text-warning me-2"></i>
                        <strong>Financeiro:</strong> acessa apenas o módulo financeiro e relatórios.
                    </div>
                    <div id="legendaCliente" class="d-none small">
                        <i class="bi bi-eye text-info me-2"></i>
                        <strong>Cliente:</strong> visualiza <u>apenas</u> os diários <strong>assinados</strong>
                        da obra selecionada. Sem acesso a medição, cronograma, financeiro ou documentos.
                    </div>
                    <div id="legendaFiscal" class="d-none small">
                        <i class="bi bi-clipboard2-check-fill text-danger me-2"></i>
                        <strong>Fiscal de Obra:</strong> usuário de órgão externo (ex: Aeronáutica). Emite relatórios
                        de visita de fiscalização com o timbre do próprio órgão. Não acessa nenhum módulo da empresa.
                    </div>
                </div>

                <!-- Bloco exclusivo para FISCAL -->
                <div id="blocoFiscal" class="mt-3" style="display:none;">
                    <div class="p-3 rounded-3 border border-danger" style="background:rgba(220,53,69,.04);">
                        <h6 class="text-danger fw-bold mb-3"><i class="bi bi-clipboard2-check-fill me-2"></i>Dados do Órgão Fiscalizador</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nome do Órgão *</label>
                            <input type="text" name="orgao_fiscalizador" class="form-control py-2"
                                   placeholder="Ex: Força Aérea Brasileira — 1ª COMAR">
                            <div class="form-text">Aparecerá como cabeçalho nos relatórios de visita fiscal.</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted">Logo do Órgão (opcional)</label>
                            <input type="file" name="logo_orgao" class="form-control py-2" accept="image/*">
                            <div class="form-text">PNG, JPG ou SVG recomendado. Aparece no cabeçalho do relatório PDF.</div>
                        </div>
                    </div>
                </div>

                <!-- Bloco exclusivo para CLIENTE -->
                <div id="blocoObraCliente" class="mt-3" style="display:none;">
                    <div class="p-3 rounded-3 border border-info"
                        style="background:rgba(13,202,240,.05);">
                        <label class="form-label small fw-bold text-info mb-2">
                            <i class="bi bi-building me-1"></i>
                            Obras que este cliente poderá visualizar *
                        </label>
                        <?php if (!empty($obras)): ?>
                        <div style="max-height:220px;overflow-y:auto;border:1px solid #dee2e6;border-radius:8px;padding:8px;background:#fff;">
                            <?php foreach ($obras as $obra): ?>
                            <div class="form-check py-1 border-bottom">
                                <input class="form-check-input chk-obra-cliente"
                                    type="checkbox"
                                    name="obras_acesso[]"
                                    value="<?= $obra['id'] ?>"
                                    id="obra_<?= $obra['id'] ?>">
                                <label class="form-check-label" for="obra_<?= $obra['id'] ?>">
                                    <strong><?= htmlspecialchars($obra['nome']) ?></strong>
                                    <?php if (!empty($obra['cidade'])): ?>
                                        <span class="text-muted small"> — <?= htmlspecialchars($obra['cidade']) ?></span>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-text text-muted mt-1">
                            <i class="bi bi-info-circle me-1"></i>
                            Marque uma ou mais obras. O cliente verá apenas os diários <strong>assinados</strong>.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning border-0 mb-0 py-2 small">
                            Nenhuma obra cadastrada ainda.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-3 justify-content-end">
                    <a href="index.php?acao=listar_usuarios" class="btn btn-light border fw-bold px-4">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary fw-bold px-5 shadow-sm">
                        <i class="bi bi-person-check-fill me-2"></i>Salvar Usuário
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
const selectNivel  = document.getElementById('selectNivel');
const legendaBox   = document.getElementById('legendaPerfil');
const blocoObra    = document.getElementById('blocoObraCliente');
const blocoFiscal  = document.getElementById('blocoFiscal');

const mapa = {
    'engenheiro': 'Engenheiro',
    'mestre':     'Mestre',
    'financeiro': 'Financeiro',
    'cliente':    'Cliente',
    'fiscal':     'Fiscal',
};

selectNivel.addEventListener('change', function() {
    const val = this.value;

    ['Engenheiro','Mestre','Financeiro','Cliente','Fiscal'].forEach(p => {
        const el = document.getElementById('legenda' + p);
        if (el) el.classList.add('d-none');
    });

    if (val) {
        legendaBox.style.display = 'block';
        const el = document.getElementById('legenda' + (mapa[val] || ''));
        if (el) el.classList.remove('d-none');
    } else {
        legendaBox.style.display = 'none';
    }

    blocoObra.style.display   = (val === 'cliente') ? 'block' : 'none';
    blocoFiscal.style.display = (val === 'fiscal')  ? 'block' : 'none';
});
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>