<?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-2">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-clipboard2-plus-fill me-2 text-primary"></i>Novo Relatório de Visita Fiscal</h4>
        <small class="text-muted">Registre as observações da visita de fiscalização</small>
    </div>
    <a href="index.php?acao=relatorio_fiscal" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<?php if (isset($_GET['erro'])): ?>
<div class="alert alert-danger border-0 py-2 mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Preencha os campos obrigatórios: obra inspecionada e órgão fiscalizador.
</div>
<?php endif; ?>

<form method="POST" action="index.php?acao=relatorio_fiscal_salvar"
      enctype="multipart/form-data" id="formRelatorio">
<div class="row g-4">

    <!-- Coluna principal -->
    <div class="col-lg-8">

        <div class="card-custom mb-4">
            <h6 class="fw-bold mb-3 pb-2" style="border-bottom:2px solid var(--primary-color);">
                <i class="bi bi-info-circle-fill me-2 text-primary"></i>Identificação da Visita
            </h6>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Obra Inspecionada *</label>
                    <select name="id_obra" class="form-select py-2" required>
                        <option value="">Selecione a obra...</option>
                        <?php foreach ($obras as $obra): ?>
                        <option value="<?= $obra['id'] ?>">
                            <?= htmlspecialchars($obra['nome']) ?>
                            <?php if (!empty($obra['cidade'])): ?> — <?= htmlspecialchars($obra['cidade']) ?><?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Data da Visita *</label>
                    <input type="date" name="data_visita" class="form-control py-2"
                           value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Órgão Fiscalizador *</label>
                <input type="text" name="orgao" class="form-control py-2"
                       value="<?= htmlspecialchars($orgaoPadrao) ?>"
                       placeholder="Ex: Força Aérea Brasileira — 1ª Regional" required>
                <div class="form-text">Nome completo do órgão que aparecerá no relatório impresso.</div>
            </div>

            <div class="mb-0">
                <label class="form-label small fw-bold text-muted">Resultado da Inspeção *</label>
                <div class="row g-3 mt-0">
                    <div class="col-md-6">
                        <label class="d-block p-3 rounded-3 border cursor-pointer resultado-card"
                               style="cursor:pointer;transition:all .2s;" id="card-conforme">
                            <input type="radio" name="status" value="conforme" class="form-check-input me-2"
                                   checked onchange="toggleResultado(this.value)">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            <strong class="text-success">Conforme</strong>
                            <div class="text-muted small mt-1 ms-4">A obra está dentro do esperado. Nenhum apontamento crítico.</div>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="d-block p-3 rounded-3 border cursor-pointer resultado-card"
                               style="cursor:pointer;transition:all .2s;" id="card-nao_conforme">
                            <input type="radio" name="status" value="nao_conforme" class="form-check-input me-2"
                                   onchange="toggleResultado(this.value)">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                            <strong class="text-danger">Não Conforme</strong>
                            <div class="text-muted small mt-1 ms-4">Foram identificados apontamentos ou irregularidades.</div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-custom mb-4">
            <h6 class="fw-bold mb-3 pb-2" style="border-bottom:2px solid var(--primary-color);">
                <i class="bi bi-pencil-square me-2 text-primary"></i>Observações e Apontamentos
            </h6>
            <textarea name="observacoes" class="form-control" rows="10"
                      placeholder="Descreva o que foi verificado durante a visita, apontamentos encontrados, prazos de correção, e qualquer outra observação relevante..."></textarea>
            <div class="form-text">
                Informe tudo o que foi observado — tanto o que está em conformidade quanto as pendências encontradas.
            </div>
        </div>

    </div>

    <!-- Coluna lateral -->
    <div class="col-lg-4">

        <div class="card-custom mb-4">
            <h6 class="fw-bold mb-3 pb-2" style="border-bottom:2px solid var(--primary-color);">
                <i class="bi bi-images me-2 text-primary"></i>Registro Fotográfico
            </h6>
            <div class="border-2 border-dashed border rounded-3 p-3 text-center mb-3"
                 id="dropZone" style="cursor:pointer;transition:all .2s;border-style:dashed!important;">
                <i class="bi bi-cloud-upload text-muted" style="font-size:2rem;"></i>
                <p class="text-muted small mb-2 mt-1">Arraste fotos aqui ou clique para selecionar</p>
                <p class="text-muted" style="font-size:.72rem;">JPG, PNG ou WEBP · Múltiplas fotos</p>
                <input type="file" name="fotos[]" id="inputFotos" multiple accept="image/*"
                       class="d-none" onchange="previewFotos(this)">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        onclick="document.getElementById('inputFotos').click()">
                    <i class="bi bi-folder2-open me-1"></i>Selecionar Fotos
                </button>
            </div>
            <div id="previewContainer" class="row g-2"></div>
            <div id="legendasContainer"></div>
        </div>

        <div class="card-custom mb-4" style="background:#f8f9fa;">
            <h6 class="fw-bold mb-2"><i class="bi bi-lightbulb-fill text-warning me-2"></i>Dica</h6>
            <p class="text-muted small mb-0" style="line-height:1.6;">
                Registre fotos de todos os pontos verificados — tanto os conformes quanto os com apontamentos.
                Isso documenta a vistoria completa da obra.
            </p>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold">
                <i class="bi bi-save-fill me-2"></i>Salvar Relatório
            </button>
            <a href="index.php?acao=relatorio_fiscal" class="btn btn-light border fw-bold">
                Cancelar
            </a>
        </div>

    </div>
</div>
</form>

<style>
.resultado-card:has(input:checked) {
    border-color: #0d6efd !important;
    background: rgba(13,110,253,.04);
}
#card-conforme:has(input:checked) { border-color: #198754 !important; background: rgba(25,135,84,.04); }
#card-nao_conforme:has(input:checked) { border-color: #dc3545 !important; background: rgba(220,53,69,.04); }
</style>

<script>
function toggleResultado(val) {
    // handled by CSS :has
}

function previewFotos(input) {
    const container = document.getElementById('previewContainer');
    const legendas  = document.getElementById('legendasContainer');
    container.innerHTML = '';
    legendas.innerHTML  = '';

    if (!input.files.length) return;

    Array.from(input.files).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const col = document.createElement('div');
            col.className = 'col-6';
            col.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" style="height:80px;width:100%;object-fit:cover;">`;
            container.appendChild(col);
        };
        reader.readAsDataURL(file);

        const div = document.createElement('div');
        div.className = 'mt-2';
        div.innerHTML = `<input type="text" name="legenda[]" class="form-control form-control-sm"
                           placeholder="Legenda da foto ${i+1} (opcional)">`;
        legendas.appendChild(div);
    });
}

// Drag and drop
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = '#0d6efd'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = ''; });
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.style.borderColor = '';
    const input = document.getElementById('inputFotos');
    input.files = e.dataTransfer.files;
    previewFotos(input);
});
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
