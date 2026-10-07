<?php //visoes/usuarios/editar.php
include __DIR__ . '/../estruturas/cabecalho.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item">
                    <a href="index.php?acao=listar_usuarios" class="text-decoration-none text-muted">Equipe</a>
                </li>
                <li class="breadcrumb-item active fw-bold text-primary">Editar Usuário</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0" style="color:var(--primary-color);">
            <i class="bi bi-pencil-square me-2"></i>
            Editar: <?= htmlspecialchars($usuario['nome']) ?>
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
            <form method="POST" action="index.php?acao=editar_usuario&id=<?= $usuario['id'] ?>" enctype="multipart/form-data">

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
                        <label class="form-label small fw-bold text-muted">Função / Nível de Acesso *</label>
                        <select name="nivel_acesso" class="form-select py-2" id="selectNivel" required>
                            <option value="engenheiro"  <?= $usuario['nivel_acesso']==='engenheiro'  ?'selected':'' ?>>Engenheiro</option>
                            <option value="mestre"      <?= $usuario['nivel_acesso']==='mestre'      ?'selected':'' ?>>Mestre de Obras</option>
                            <option value="financeiro"  <?= $usuario['nivel_acesso']==='financeiro'  ?'selected':'' ?>>Financeiro</option>
                            <option value="admin"       <?= $usuario['nivel_acesso']==='admin'       ?'selected':'' ?>>Administrador</option>
                            <option value="cliente"     <?= $usuario['nivel_acesso']==='cliente'     ?'selected':'' ?>>Cliente</option>
                            <?php if ($_SESSION['is_superadmin'] ?? false): ?>
                            <option value="fiscal"      <?= $usuario['nivel_acesso']==='fiscal'      ?'selected':'' ?>>Fiscal de Obra (Órgão Externo)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Bloco exclusivo para FISCAL -->
                <div id="blocoFiscal" class="mt-3"
                     style="display:<?= $usuario['nivel_acesso']==='fiscal' ? 'block' : 'none' ?>;">
                    <div class="p-3 rounded-3 border border-danger" style="background:rgba(220,53,69,.04);">
                        <h6 class="text-danger fw-bold mb-3"><i class="bi bi-clipboard2-check-fill me-2"></i>Dados do Órgão Fiscalizador</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nome do Órgão</label>
                            <input type="text" name="orgao_fiscalizador" class="form-control py-2"
                                   value="<?= htmlspecialchars($usuario['orgao_fiscalizador'] ?? '') ?>"
                                   placeholder="Ex: Força Aérea Brasileira — 1ª COMAR">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted">Logo do Órgão</label>
                            <?php if (!empty($usuario['logo_orgao'])): ?>
                            <div class="mb-2">
                                <img src="../publico/assets/img/<?= htmlspecialchars($usuario['logo_orgao']) ?>"
                                     alt="Logo atual" style="max-height:50px;border-radius:4px;border:1px solid #ddd;padding:4px;">
                                <span class="text-muted small ms-2">Logo atual</span>
                            </div>
                            <?php endif; ?>
                            <input type="file" name="logo_orgao" class="form-control py-2" accept="image/*">
                            <div class="form-text">Deixe em branco para manter o logo atual.</div>
                        </div>
                    </div>
                </div>

                <!-- Bloco exclusivo para CLIENTE: seleção da obra -->
                <div id="blocoObraCliente" class="mt-3"
                     style="display:<?= $usuario['nivel_acesso']==='cliente' ? 'block' : 'none' ?>;">
                    <div class="p-3 rounded-3 border border-info"
                         style="background:rgba(13,202,240,.05);">
                        <label class="form-label small fw-bold text-info mb-2">
                            <i class="bi bi-building me-1"></i>
                            Obras que este cliente poderá visualizar
                        </label>

                        <?php if (!empty($obras)): ?>
                        <div style="max-height:220px;overflow-y:auto;border:1px solid #dee2e6;border-radius:8px;padding:8px;background:#fff;">
                            <?php foreach ($obras as $obra): ?>
                            <div class="form-check py-1 border-bottom">
                                <input class="form-check-input chk-obra-cliente"
                                    type="checkbox"
                                    name="obras_acesso[]"
                                    value="<?= $obra['id'] ?>"
                                    id="obra_<?= $obra['id'] ?>"
                                    <?= in_array($obra['id'], $obrasVinculadas) ? 'checked' : '' ?>>
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
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Nenhuma obra cadastrada.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="ativo" class="form-check-input"
                               id="ativo" <?= $usuario['ativo'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="ativo">Usuário Ativo</label>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-3 justify-content-end">
                    <a href="index.php?acao=listar_usuarios" class="btn btn-light border fw-bold px-4">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-warning fw-bold px-5 shadow-sm text-dark">
                        <i class="bi bi-save-fill me-2"></i>Salvar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
const selectNivel = document.getElementById('selectNivel');
const blocoObra   = document.getElementById('blocoObraCliente');
const blocoFiscal = document.getElementById('blocoFiscal');

selectNivel.addEventListener('change', function() {
    blocoObra.style.display   = (this.value === 'cliente') ? 'block' : 'none';
    blocoFiscal.style.display = (this.value === 'fiscal')  ? 'block' : 'none';
});
</script>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>