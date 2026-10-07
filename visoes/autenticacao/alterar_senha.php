<?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-shield-lock me-2"></i> Alterar Senha</h5>
                </div>
                <div class="card-body p-4">
                    
                    <?php if (isset($erro)): ?>
                        <div class="alert alert-danger d-flex align-items-center small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?= $erro; ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($sucesso)): ?>
                        <div class="alert alert-success d-flex align-items-center small" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div><?= $sucesso; ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?acao=salvar_nova_senha_logado" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">Senha Atual</label>
                            <input type="password" name="senha_atual" class="form-control" required placeholder="Digite sua senha atual">
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">Nova Senha</label>
                            <input type="password" name="nova_senha" class="form-control" required minlength="6" placeholder="Mínimo 6 caracteres">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Confirmar Nova Senha</label>
                            <input type="password" name="confirma_senha" class="form-control" required placeholder="Repita a nova senha">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="bi bi-save me-2"></i> Salvar Nova Senha
                            </button>
                            <a href="index.php?acao=painel" class="btn btn-outline-secondary border-0">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>