<?php
// visoes/diario/assinar.php
include __DIR__ . '/../estruturas/cabecalho.php';

// Variáveis de segurança para não quebrar a tela
$id = $diario['id'] ?? $_GET['id'];
$idObra = $diario['id_obra'] ?? 0;
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="bi bi-pen-fill me-2"></i> Assinar Diário de Obra #<?= $id; ?>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> 
                        Ao assinar, este documento será finalizado e não poderá mais ser editado.
                    </p>

                    <form id="formAssinar" action="index.php?acao=salvar_assinatura" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>">
                        <input type="hidden" name="assinatura_base64" id="assinatura_base64">
                        <input type="hidden" name="id_obra" value="<?= $idObra; ?>">

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
                                Declaro que as informações são verdadeiras e correspondem à execução da obra.
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg fw-bold">Confirmar e Assinar</button>
                            <a href="index.php?acao=ver_diario&id_obra=<?= $idObra; ?>" class="btn btn-light border text-muted">Cancelar</a>
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

        // Ajusta tamanho do canvas
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

        document.getElementById('formAssinar').addEventListener('submit', function (e) {
            // Se desenhou algo, salva no input hidden
            if (!signaturePad.isEmpty()) {
                var dataURL = signaturePad.toDataURL();
                document.getElementById('assinatura_base64').value = dataURL;
            }
            // Se não desenhou e nem fez upload, avisa (opcional, pois o required do checkbox já segura)
        });
    });
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>