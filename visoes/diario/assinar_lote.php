<?php
// visoes/diario/assinar_lote.php
/** @var array $obra */
/** @var array $diariosAbertos */
include __DIR__ . '/../estruturas/cabecalho.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="bi bi-pen-fill me-2"></i> Assinar Diários em Aberto
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        A mesma assinatura será aplicada aos <strong><?= count($diariosAbertos) ?></strong>
                        diário(s) abaixo. Após assinar, eles não poderão mais ser editados.
                    </p>

                    <div class="mb-4" style="max-height: 180px; overflow-y: auto;">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($diariosAbertos as $d): ?>
                            <li class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"><i class="bi bi-calendar-event me-2 text-muted"></i><?= date('d/m/Y', strtotime($d['data_diario'])); ?></span>
                                    <span class="badge bg-warning text-dark">Rascunho</span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <?php $resumo = trim($d['atividades_executadas'] ?? ''); ?>
                                    <?= $resumo !== '' ? htmlspecialchars(mb_strimwidth($resumo, 0, 90, '...')) : 'Nenhuma atividade registrada.'; ?>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <form id="formAssinarLote" action="index.php?acao=salvar_assinatura_lote" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_obra" value="<?= $obra['id']; ?>">
                        <input type="hidden" name="assinatura_base64" id="assinatura_base64">

                        <label class="form-label fw-bold text-dark">Opção A: Desenhar na tela</label>
                        <div class="border rounded bg-light position-relative mb-2" style="height: 180px; cursor: crosshair;">
                            <canvas id="signature-pad" class="w-100 h-100"></canvas>
                            <div class="position-absolute bottom-0 start-0 w-100 text-center text-muted small pe-none pb-1 opacity-50">Assine aqui</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-4 w-100" id="limparCanvas">
                            <i class="bi bi-eraser"></i> Limpar Desenho
                        </button>

                        <div class="text-center text-muted fw-bold mb-3 small">- OU -</div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Opção B: Enviar arquivo (Foto/Scan)</label>
                            <input type="file" name="imagem_assinatura" class="form-control" accept="image/png, image/jpeg, image/jpg">
                        </div>

                        <div class="form-check mb-3 bg-light p-3 rounded border">
                            <input class="form-check-input" type="checkbox" required id="confirma">
                            <label class="form-check-label small" for="confirma">
                                Declaro que as informações são verdadeiras e correspondem à execução da obra em todos os diários listados acima.
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg fw-bold">Confirmar e Assinar Todos</button>
                            <a href="index.php?acao=ver_diario&id_obra=<?= $obra['id']; ?>" class="btn btn-light border text-muted">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var canvas = document.getElementById('signature-pad');
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: 'rgb(0, 0, 0)'
        });

        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }
        window.onresize = resizeCanvas;
        resizeCanvas();

        document.getElementById('limparCanvas').addEventListener('click', function () {
            signaturePad.clear();
        });

        document.getElementById('formAssinarLote').addEventListener('submit', function (e) {
            if (!signaturePad.isEmpty()) {
                var dataURL = signaturePad.toDataURL();
                document.getElementById('assinatura_base64').value = dataURL;
            }
        });
    });
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>
