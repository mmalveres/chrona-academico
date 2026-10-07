<?php //visoes/painel/inicio.php
include __DIR__ . '/../estruturas/cabecalho.php'; 

// CÁLCULO DINÂMICO DO GRÁFICO
$percAtivas = 0;
if ($totalObras > 0) {
    $percAtivas = round(($obrasAndamento / $totalObras) * 100);
}
$percConcluidas = 100 - $percAtivas;
?>

<h3 class="fw-bold mb-1" style="font-family: 'Montserrat', sans-serif; color: #2B3674;">Visão Geral</h3>
<p class="text-secondary mb-4">Resumo em <?= date('d/m/Y'); ?></p>

<div class="row mb-4">
    <div class="col-6 col-xl-3 mb-3">
        <div class="card-custom d-flex align-items-center p-3 h-100">
            <div class="icon-box bg-blue me-3" style="width:45px; height:45px; font-size:1.2rem;"><i class="bi bi-buildings-fill"></i></div>
            <div>
                <div class="stat-value fs-4"><?= $totalObras; ?></div>
                <div class="stat-label" style="font-size: 0.7rem;">Obras</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-3">
        <div class="card-custom d-flex align-items-center p-3 h-100">
            <div class="icon-box bg-green me-3" style="width:45px; height:45px; font-size:1.2rem;"><i class="bi bi-cone-striped"></i></div>
            <div>
                <div class="stat-value fs-4"><?= $obrasAndamento; ?></div>
                <div class="stat-label" style="font-size: 0.7rem;">Andamento</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-3">
        <div class="card-custom d-flex align-items-center p-3 h-100">
            <div class="icon-box bg-yellow me-3" style="width:45px; height:45px; font-size:1.2rem;"><i class="bi bi-clipboard-check"></i></div>
            <div>
                <div class="stat-value fs-4"><?= $diariosHoje; ?></div>
                <div class="stat-label" style="font-size: 0.7rem;">RDOs</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-3">
        <div class="card-custom d-flex align-items-center p-3 h-100">
            <div class="icon-box bg-cloud me-3" style="width:45px; height:45px; font-size:1.2rem;"><i class="bi <?= $clima['icone'] ?? 'bi-cloud-sun'; ?>"></i></div>
            <div>
                <div class="stat-value fs-4"><?= $clima ? $clima['temperatura'].'°' : '-'; ?></div>
                <div class="stat-label" style="font-size: 0.7rem;">Clima</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card-custom h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0" style="color: #2B3674;">Obras Recentes</h5>
                <a href="index.php?acao=listar_obras" class="text-primary fw-bold text-decoration-none small">Ver todas</a>
            </div>
            
            <div class="table-responsive d-none d-md-block">
                <table class="table table-custom w-100">
                    <thead>
                        <tr>
                            <th>OBRA</th>
                            <th>LOCAL</th>
                            <th>STATUS</th>
                            <th class="text-end">AÇÃO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($ultimasObras) > 0): foreach($ultimasObras as $obra): 
                            // LÓGICA DE STATUS
                            $st = trim(mb_strtolower($obra['status'], 'UTF-8'));
                            $termosAtivos = ['ativa', 'ativo', '1', 'em andamento', 'iniciada', 'andamento'];
                            $isAtiva = in_array($st, $termosAtivos);
                            if (!$isAtiva && strpos($st, 'conclu') === false && strpos($st, 'inativa') === false && $st != '0') $isAtiva = true;
                        ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= $obra['nome']; ?></td>
                            <td class="text-muted small"><i class="bi bi-geo-alt"></i> <?= substr($obra['endereco'], 0, 20); ?></td>
                            <td>
                                <?php if($isAtiva): ?>
                                    <span class="badge-status badge-active">Em Andamento</span>
                                <?php else: ?>
                                    <span class="badge-status badge-done">Concluída</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="index.php?acao=ver_diario&id_obra=<?= $obra['id']; ?>" class="btn btn-outline-primary btn-sm fw-bold px-3" style="border-radius: 8px;">Abrir</a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center text-muted">Nenhuma obra recente.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-md-none">
                <?php if(count($ultimasObras) > 0): foreach($ultimasObras as $obra): 
                    // MESMA LÓGICA DE STATUS
                    $st = trim(mb_strtolower($obra['status'], 'UTF-8'));
                    $termosAtivos = ['ativa', 'ativo', '1', 'em andamento', 'iniciada', 'andamento'];
                    $isAtiva = in_array($st, $termosAtivos);
                    if (!$isAtiva && strpos($st, 'conclu') === false && strpos($st, 'inativa') === false && $st != '0') $isAtiva = true;
                ?>
                <div class="p-3 mb-3 border rounded-3 bg-light position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><?= $obra['nome']; ?></h6>
                            <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?= substr($obra['endereco'], 0, 25); ?></div>
                        </div>
                        <?php if($isAtiva): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">Ativa</span>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2">Fim</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="index.php?acao=ver_diario&id_obra=<?= $obra['id']; ?>" class="btn btn-white border text-primary fw-bold shadow-sm">
                            Abrir Painel
                        </a>
                    </div>
                </div>
                <?php endforeach; else: ?>
                    <div class="text-center text-muted py-3">Nenhuma obra encontrada.</div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <div class="col-lg-4">
        
        <h6 class="text-secondary fw-bold mb-3 small text-uppercase">Acesso Rápido</h6>
        
        <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'admin'): ?>
            <a href="index.php?acao=nova_obra" class="btn-action-lg btn-yellow">
                <i class="bi bi-plus-lg"></i> Nova Obra
            </a>

            <a href="index.php?acao=listar_usuarios" class="btn-action-lg btn-green">
                <i class="bi bi-people-fill"></i> Gerenciar Equipes
            </a>
        <?php endif; ?>

        <a href="index.php?acao=listar_obras" class="btn-action-lg btn-purple">
            <i class="bi bi-file-earmark-text"></i> Relatórios
        </a>

        <div class="card-custom mt-4">
            <h6 class="fw-bold mb-4" style="color: #2B3674;">Obras por Status</h6>
            
            <div class="d-flex align-items-center justify-content-center">
<?php 
    // Cria o estilo CSS dentro do PHP para não bugar o VS Code
    $estiloGrafico = "
        width: 130px; 
        height: 130px; 
        border-radius: 50%; 
        position: relative; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        flex-shrink: 0;
        background: conic-gradient(
            #05CD99 0% {$percAtivas}%, 
            #E0E5F2 {$percAtivas}% 100%
        );
    ";
?>

<div class="card-custom mt-4">
    <h6 class="fw-bold mb-4" style="color: #2B3674;">Obras por Status</h6>
    
    <div class="d-flex align-items-center justify-content-center">
        <div style="<?= $estiloGrafico; ?>">
            <div style="width: 90px; height: 90px; background: white; border-radius: 50%;"></div>
        </div>

        <div class="ms-4">
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge rounded-circle p-1 me-2" style="background-color: #05CD99; width: 8px; height: 8px;"></span> 
                            <small class="fw-bold text-dark">Ativas</small>
                        </div>
                        <h5 class="fw-bold text-dark ms-3 mb-0"><?= $percAtivas; ?>%</h5>
                    </div>
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge rounded-circle p-1 me-2" style="background-color: #E0E5F2; width: 8px; height: 8px;"></span> 
                            <small class="fw-bold text-secondary">Concluídas</small>
                        </div>
                        <h5 class="fw-bold text-secondary ms-3 mb-0"><?= $percConcluidas; ?>%</h5>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>