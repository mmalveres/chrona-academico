<?php
/** @var array $obrasCliente */
include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="text-center mb-5 mt-3">
    <h4 class="fw-bold" style="color:var(--primary-color);">
        <i class="bi bi-buildings me-2"></i> Suas Obras
    </h4>
    <p class="text-muted">Selecione a obra para visualizar o diário</p>
</div>

<div class="row justify-content-center g-3" style="max-width:700px;margin:0 auto;">
    <?php foreach ($obrasCliente as $obra): ?>
    <div class="col-md-6">
        <a href="index.php?acao=ver_diario&id_obra=<?= $obra['id'] ?>"
           class="card-custom d-block text-decoration-none p-4 text-center"
           style="transition:all .2s;"
           onmouseover="this.style.transform='translateY(-4px)'"
           onmouseout="this.style.transform='translateY(0)'">
            <i class="bi bi-building fs-1 text-primary d-block mb-2"></i>
            <div class="fw-bold text-dark fs-5"><?= htmlspecialchars($obra['nome']) ?></div>
            <div class="text-muted small mb-2">
                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($obra['cidade'] ?? '') ?>
            </div>
            <span class="badge bg-light text-muted border" style="font-size:.72rem;">
                <i class="bi bi-buildings me-1"></i><?= htmlspecialchars($obra['empresa']) ?>
            </span>
            <div class="mt-3">
                <span class="btn btn-primary btn-sm fw-bold px-4">
                    <i class="bi bi-journal-text me-1"></i> Ver Diários
                </span>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>