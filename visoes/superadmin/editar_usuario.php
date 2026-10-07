<?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item">
                    <a href="index.php?acao=sa_listar_usuarios" class="text-decoration-none text-muted">Todos os Usuários</a>
                </li>
                <li class="breadcrumb-item active fw-bold text-primary">Editar</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0" style="color:var(--primary-color);">
            <i class="bi bi-pencil-square me-2"></i>Editar: <?= htmlspecialchars($usuario['nome']) ?>
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
            <form method="POST" action="index.php?acao=sa_editar_usuario&id=<?= $usuario['id'] ?>">

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nome Completo *</label>
                    <input type="text" name="nome" class="form-control py-2"
                           value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">E-mail *</label>
                    <input type="email" name="email" class="form-control py-2"
                           value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Trocar Senha</label>
                        <input type="password" name="senha" class="form-control py-2"
                               placeholder="Deixe em branco para não alterar">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Perfil / Nível *</label>
                        <select name="nivel_acesso" class="form-select py-2" required>
                            <option value="admin"      <?= $usuario['nivel_acesso']==='admin'      ?'selected':'' ?>>Administrador</option>
                            <option value="engenheiro" <?= $usuario['nivel_acesso']==='engenheiro' ?'selected':'' ?>>Engenheiro</option>
                            <option value="mestre"     <?= $usuario['nivel_acesso']==='mestre'     ?'selected':'' ?>>Mestre de Obras</option>
                            <option value="financeiro" <?= $usuario['nivel_acesso']==='financeiro' ?'selected':'' ?>>Financeiro</option>
                            <option value="cliente"    <?= $usuario['nivel_acesso']==='cliente'    ?'selected':'' ?>>Cliente / Fiscal</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label small fw-bold text-muted">Empresa *</label>
                    <select name="id_empresa" class="form-select py-2" required>
                        <?php foreach ($empresas as $emp): ?>
                        <option value="<?= $emp['id'] ?>"
                            <?= $usuario['id_empresa'] == $emp['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['nome_fantasia']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text text-muted small">
                        <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                        Alterar a empresa muda o acesso total deste usuário.
                    </div>
                </div>

                <div class="mt-2 mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="ativo" class="form-check-input" id="ativo"
                               <?= $usuario['ativo'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="ativo">Usuário Ativo</label>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-3 justify-content-end">
                    <a href="index.php?acao=sa_listar_usuarios" class="btn btn-light border fw-bold px-4">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-warning fw-bold px-5 text-dark shadow-sm">
                        <i class="bi bi-save-fill me-2"></i>Salvar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>