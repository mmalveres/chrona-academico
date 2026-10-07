<?php
// visoes/obras/criar.php
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
.page-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 20px;
    color: #fff;
}
.btn-salvar {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 12px 32px;
    font-weight: 800;
    font-size: .95rem;
    box-shadow: 0 4px 14px rgba(21,128,61,.35);
    transition: transform .15s, box-shadow .15s;
}
.btn-salvar:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(21,128,61,.45); }
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
</style>

<!-- Cabeçalho hero -->
<div class="page-hero">
    <div class="d-flex align-items-center gap-3">
        <a href="index.php?acao=listar_obras"
           class="btn btn-sm d-flex align-items-center gap-1"
           style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;font-weight:700;font-size:.8rem;text-decoration:none;padding:6px 14px;">
            <i class="bi bi-arrow-left"></i> Obras
        </a>
        <div>
            <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;opacity:.7;font-weight:700;">Nova obra</div>
            <h4 class="mb-0 fw-bold" style="font-size:1.1rem;">Cadastrar Obra</h4>
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

<form action="index.php?acao=nova_obra" method="POST">
    <input type="hidden" name="status" value="ativa">

    <!-- Seção 1: Identificação -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="bi bi-building"></i> Identificação
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome da Obra *</label>
                <input type="text" name="nome" class="form-control"
                       placeholder="Ex: Residencial Flores" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nº Contrato</label>
                <input type="text" name="codigo_contrato" class="form-control"
                       placeholder="Ex: 123/2024">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cidade / UF *</label>
                <input type="text" name="cidade" class="form-control"
                       placeholder="Ex: Brasília - DF" required>
            </div>
            <div class="col-12">
                <label class="form-label">Endereço Completo *</label>
                <input type="text" name="endereco" class="form-control"
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
                <input type="date" name="data_inicio" class="form-control" required>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Previsão de Término *</label>
                <input type="date" name="data_previsao_fim" class="form-control" required>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Orçamento Previsto</label>
                <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="text" name="orcamento_previsto" class="form-control"
                           placeholder="0,00" onkeyup="formatarMoeda(this)">
                </div>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Responsável Técnico *</label>
                <select name="id_responsavel_tecnico" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php if (isset($usuarios)): foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id']; ?>">
                            <?= htmlspecialchars($u['nome']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Botões -->
    <div class="d-flex justify-content-end align-items-center gap-3 pb-5">
        <a href="index.php?acao=listar_obras" class="btn-cancelar">
            <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button type="submit" class="btn-salvar">
            <i class="bi bi-check-lg me-1"></i> Salvar Obra
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
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
