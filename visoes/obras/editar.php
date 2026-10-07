<?php
// visoes/obras/editar.php
include __DIR__ . '/../estruturas/cabecalho.php';
?>

<style>
.form-section {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #f0f0f0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    padding: 24px;
    margin-bottom: 20px;
}
.form-section-title {
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 800;
    color: #9ca3af;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-section-title i { font-size: .95rem; color: var(--primary-color, #0d6efd); }
.form-label { font-size: .8rem; font-weight: 700; color: #374151; margin-bottom: 5px; }
.form-control, .form-select {
    border-radius: 10px;
    border: 1.5px solid #e5e7eb;
    padding: 10px 14px;
    font-size: .9rem;
    transition: border-color .15s;
}
.form-control:focus, .form-select:focus {
    border-color: var(--primary-color, #0d6efd);
    box-shadow: 0 0 0 3px rgba(13,110,253,.1);
}
.input-group .input-group-text {
    border-radius: 10px 0 0 10px;
    border: 1.5px solid #e5e7eb;
    border-right: none;
    background: #f9fafb;
    font-weight: 700;
    color: #6b7280;
}
.input-group .form-control { border-radius: 0 10px 10px 0; }
.obra-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 20px;
    color: #fff;
}
.btn-salvar {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 12px 32px;
    font-weight: 800;
    font-size: .95rem;
    box-shadow: 0 4px 14px rgba(217,119,6,.35);
    transition: transform .15s, box-shadow .15s;
}
.btn-salvar:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(217,119,6,.45); }
.btn-cancelar {
    background: #f3f4f6;
    color: #374151;
    border: none;
    border-radius: 12px;
    padding: 12px 24px;
    font-weight: 700;
    font-size: .95rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .15s;
}
.btn-cancelar:hover { background: #e5e7eb; color: #1f2937; }

/* Status selector visual */
.status-option {
    flex: 1;
    padding: 12px;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    cursor: pointer;
    text-align: center;
    transition: all .15s;
}
.status-option input[type=radio] { display: none; }
.status-option.ativa  { border-color: #bbf7d0; background: #f0fdf4; }
.status-option.ativa label  { color: #15803d; cursor: pointer; }
.status-option.concluida { border-color: #e2e8f0; background: #f8fafc; }
.status-option.concluida label { color: #64748b; cursor: pointer; }
.status-option input:checked + label { font-weight: 800; }
.status-option.ativa input:checked ~ * { font-weight: 800; }
</style>

<!-- Cabeçalho hero da obra -->
<div class="obra-hero">
    <div class="d-flex align-items-center gap-3">
        <a href="index.php?acao=listar_obras"
           class="btn btn-sm d-flex align-items-center gap-1"
           style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;font-weight:700;font-size:.8rem;text-decoration:none;padding:6px 14px;">
            <i class="bi bi-arrow-left"></i> Obras
        </a>
        <div>
            <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;opacity:.7;font-weight:700;">Editando obra</div>
            <h4 class="mb-0 fw-bold" style="font-size:1.1rem;"><?= htmlspecialchars($obra['nome']); ?></h4>
        </div>
    </div>
</div>

<?php if (isset($_GET['erro'])): ?>
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?= htmlspecialchars($_GET['erro']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form action="index.php?acao=editar_obra&id=<?= $obra['id']; ?>" method="POST">

    <!-- Seção 1: Identificação -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="bi bi-building"></i> Identificação
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome da Obra *</label>
                <input type="text" name="nome" class="form-control"
                       value="<?= htmlspecialchars($obra['nome']); ?>"
                       placeholder="Ex: Residencial Flores" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nº Contrato</label>
                <input type="text" name="codigo_contrato" class="form-control"
                       value="<?= htmlspecialchars($obra['codigo_contrato'] ?? ''); ?>"
                       placeholder="Ex: 123/2024">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cidade / UF *</label>
                <input type="text" name="cidade" class="form-control"
                       value="<?= htmlspecialchars($obra['cidade']); ?>"
                       placeholder="Ex: Brasília - DF" required>
            </div>
            <div class="col-12">
                <label class="form-label">Endereço Completo *</label>
                <input type="text" name="endereco" class="form-control"
                       value="<?= htmlspecialchars($obra['endereco']); ?>"
                       placeholder="Rua, Número, Bairro" required>
            </div>
        </div>
    </div>

    <!-- Seção 2: Planejamento -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="bi bi-calendar-range"></i> Planejamento
        </div>
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <label class="form-label">Data de Início *</label>
                <input type="date" name="data_inicio" class="form-control"
                       value="<?= $obra['data_inicio']; ?>" required>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Previsão de Término *</label>
                <input type="date" name="data_previsao_fim" class="form-control"
                       value="<?= $obra['data_previsao_fim']; ?>" required>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Orçamento Previsto</label>
                <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="text" name="orcamento_previsto" class="form-control"
                           value="<?= number_format($obra['orcamento_previsto'], 2, ',', '.'); ?>"
                           placeholder="0,00" onkeyup="formatarMoeda(this)">
                </div>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Responsável Técnico *</label>
                <select name="id_responsavel_tecnico" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php if (isset($usuarios)): foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id']; ?>"
                            <?= ($obra['id_responsavel_tecnico'] == $u['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($u['nome']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Seção 3: Status -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="bi bi-activity"></i> Status da Obra
        </div>
        <div class="d-flex gap-3">
            <label class="status-option ativa d-flex flex-column align-items-center gap-1">
                <input type="radio" name="status" value="ativa"
                    <?= (in_array($obra['status'], ['ativa', 'ativo', '1', 'em andamento'])) ? 'checked' : ''; ?>>
                <i class="bi bi-activity fs-4 text-success"></i>
                <span style="font-size:.82rem;font-weight:700;color:#15803d;">Em Andamento</span>
                <span style="font-size:.72rem;color:#6b7280;">Obra ativa</span>
            </label>
            <label class="status-option concluida d-flex flex-column align-items-center gap-1">
                <input type="radio" name="status" value="concluida"
                    <?= (in_array($obra['status'], ['concluida', 'concluído', '0', 'inativa'])) ? 'checked' : ''; ?>>
                <i class="bi bi-check-circle fs-4 text-secondary"></i>
                <span style="font-size:.82rem;font-weight:700;color:#64748b;">Concluída</span>
                <span style="font-size:.72rem;color:#9ca3af;">Encerrada</span>
            </label>
        </div>
    </div>

    <!-- Botões -->
    <div class="d-flex justify-content-end align-items-center gap-3 pb-5">
        <a href="index.php?acao=listar_obras" class="btn-cancelar">
            <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button type="submit" class="btn-salvar">
            <i class="bi bi-check-lg me-1"></i> Salvar Alterações
        </button>
    </div>

</form>

<script>
function formatarMoeda(i) {
    var v = i.value.replace(/\D/g, '');
    v = (v / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/(\d)(\d{3})(\d{3}),/g, '$1.$2.$3,');
    v = v.replace(/(\d)(\d{3}),/g, '$1.$2,');
    i.value = v;
}

// Destaca visualmente o status selecionado
document.querySelectorAll('.status-option input[type=radio]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.status-option').forEach(function(opt) {
            opt.style.borderWidth = '2px';
            opt.style.opacity = '1';
        });
        this.closest('.status-option').style.borderWidth = '2.5px';
    });
    // Aplica no carregamento
    if (radio.checked) radio.closest('.status-option').style.borderWidth = '2.5px';
});
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
