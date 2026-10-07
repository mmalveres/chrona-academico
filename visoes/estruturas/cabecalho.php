<?php
// estruturas/cabecalho.php

// 1. CONFIGURAÇÃO DE SESSÃO
if (session_status() === PHP_SESSION_NONE) {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $isMobile = (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false);
    $tempoVida = $isMobile ? 31536000 : 7200;

    ini_set('session.gc_maxlifetime', $tempoVida);
    session_set_cookie_params(['lifetime' => $isMobile ? $tempoVida : 0, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// 2. PLANO HELPER
use Aplicacao\Helpers\PlanoHelper;

$usuarioLogado = isset($_SESSION['usuario_id']);
$nivelAcesso   = isset($_SESSION['nivel_acesso']) ? strtolower($_SESSION['nivel_acesso']) : '';
$idEmpresa     = isset($_SESSION['id_empresa']) ? $_SESSION['id_empresa'] : 0;
$rotaAtual     = $_GET['acao'] ?? 'painel';

// Lógica da Logo
$pathLogo = __DIR__ . '/../../publico/assets/img/logo_empresa_' . $idEmpresa . '.png';
$urlLogo  = 'assets/img/logo_empresa_' . $idEmpresa . '.png?' . time();
$temLogo  = file_exists($pathLogo);

// Badge do plano (cores por tipo)
$planoCores = [
    'autonomo'    => ['bg' => '#6c757d', 'label' => 'Autônomo'],
    'construtora' => ['bg' => '#0d6efd', 'label' => 'Construtora'],
    'empresarial' => ['bg' => '#FFB300', 'label' => 'Empresarial'],
    'mensal'      => ['bg' => '#0d6efd', 'label' => 'Construtora'],
];
$planoAtual   = $_SESSION['plano'] ?? 'autonomo';
$planoCor     = $planoCores[$planoAtual]['bg']    ?? '#6c757d';
$planoLabel   = $planoCores[$planoAtual]['label'] ?? 'Autônomo';
$planoTexto   = in_array($planoAtual, ['empresarial']) ? '#333' : '#fff';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>CHRONA - Gestão</title>
    
    <meta name="theme-color" content="#003366">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css"/>

    <style>
        :root {
            --primary-color: #003366;
            --accent-color: #FFB300;
            --bg-body: #F4F7FE;
            --text-dark: #2B3674;
            --safe-top: env(safe-area-inset-top);
            --safe-bottom: env(safe-area-inset-bottom);
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Nunito Sans', sans-serif;
            color: var(--text-dark);
            margin: 0; padding: 0; overflow-x: hidden;
        }

        #wrapper { display: flex; width: 100%; min-height: 100vh; }

        /* --- SIDEBAR (PC) --- */
        #sidebar-wrapper {
            width: 260px;
            background-color: var(--primary-color) !important;
            color: white;
            display: flex; flex-direction: column;
            position: fixed; height: 100vh; z-index: 1000; left: 0; top: 0;
        }

        .sidebar-brand {
            height: 80px; display: flex; align-items: center; padding-left: 25px;
            font-family: 'Montserrat', sans-serif; font-size: 1.4rem; font-weight: 800; color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .list-group-item {
            background: transparent; border: none; color: rgba(255,255,255,0.7) !important;
            padding: 15px 25px; font-weight: 600; display: flex; align-items: center; text-decoration: none;
        }
        .list-group-item:hover, .list-group-item.active {
            color: #fff !important; background-color: rgba(255,255,255,0.1);
            border-left: 4px solid var(--accent-color);
        }
        .list-group-item i { font-size: 1.2rem; width: 30px; text-align: center; margin-right: 10px; }

        /* Item bloqueado no sidebar */
        .list-group-item-locked {
            background: transparent; border: none; color: rgba(255,255,255,0.3) !important;
            padding: 15px 25px; font-weight: 600; display: flex; align-items: center;
            text-decoration: none; cursor: default;
        }
        .list-group-item-locked i { font-size: 1.2rem; width: 30px; text-align: center; margin-right: 10px; }

        #page-content-wrapper { width: 100%; margin-left: 260px; display: flex; flex-direction: column; }

        .topbar {
            height: 70px; background: #fff; display: flex; align-items: center;
            justify-content: space-between; padding: 0 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        /* Badge do plano na topbar */
        .badge-plano {
            font-size: 0.65rem; font-weight: 800; padding: 4px 10px;
            border-radius: 20px; letter-spacing: 0.5px; text-transform: uppercase;
        }

        /* --- CLASSES DO PAINEL --- */
        .card-custom { background: #fff; border-radius: 20px; border: none; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); margin-bottom: 20px; }
        .btn-action-lg { display: block; width: 100%; padding: 15px; border-radius: 12px; color: white; font-weight: 700; text-align: center; text-decoration: none; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: 0.2s; border: none; }
        .btn-action-lg:hover { transform: translateY(-2px); opacity: 0.9; color: white; }
        .btn-yellow { background-color: var(--accent-color); color: #333; }
        .btn-green { background-color: #10B981; } .btn-purple { background-color: #8B5CF6; }
        .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
        .bg-blue { background-color: #3B82F6; } .bg-green { background-color: #10B981; } .bg-yellow { background-color: #F59E0B; } .bg-cloud { background-color: #3B82F6; }
        .stat-value { font-family: 'Montserrat', sans-serif; font-size: 1.8rem; font-weight: 700; }
        .badge-status { padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; }
        .badge-active { background-color: #05CD99; color: white; } .badge-done { background-color: #E0E5F2; color: var(--text-dark); }

        /* --- MOBILE --- */
        @media (max-width: 768px) {
            #sidebar-wrapper { display: none !important; }
            #page-content-wrapper { margin-left: 0 !important; }
            .topbar {
                position: sticky; top: 0; z-index: 990;
                background: #fff;
                border-top: env(safe-area-inset-top) solid var(--primary-color);
                padding-top: 10px;
                height: auto; min-height: 70px;
            }
            body { padding-bottom: 90px; }

            .mobile-bottom-nav {
                display: flex !important;
                position: fixed; bottom: 0; left: 0; width: 100%;
                height: calc(65px + var(--safe-bottom));
                background: #fff; border-top: 1px solid #eee;
                justify-content: space-around; align-items: flex-start;
                padding-top: 10px; z-index: 1050;
                box-shadow: 0 -5px 20px rgba(0,0,0,0.05);
            }
            .mobile-nav-item {
                display: flex; flex-direction: column; align-items: center;
                text-decoration: none; color: #A3AED0; font-size: 0.65rem; font-weight: 700; width: 60px;
            }
            .mobile-nav-item i { font-size: 1.4rem; margin-bottom: 2px; }
            .mobile-nav-item.active { color: var(--primary-color); }

            .mobile-fab {
                width: 50px; height: 50px; background: var(--accent-color); color: var(--primary-color);
                border-radius: 50%; display: flex; align-items: center; justify-content: center;
                font-size: 1.5rem; margin-top: -25px; border: 4px solid var(--bg-body);
                box-shadow: 0 4px 10px rgba(255, 193, 7, 0.4);
            }
        }

        @media (min-width: 769px) { .mobile-bottom-nav { display: none !important; } }
    </style>
</head>
<body data-pagina="<?= htmlspecialchars($rotaAtual) ?>">

<div id="wrapper">

    <?php if ($usuarioLogado): ?>

    <div id="sidebar-wrapper">
        <div class="sidebar-brand">
            <?php if ($temLogo): ?>
                <img src="<?= $urlLogo; ?>" alt="Logo" style="max-height: 45px; max-width: 100%;">
            <?php else: ?>
                <i class="bi bi-buildings-fill text-warning me-2"></i> CHRONA
            <?php endif; ?>
        </div>

        <div class="list-group list-group-flush mt-3 px-2">

            <?php if ($nivelAcesso === 'fiscal'): ?>
            <a href="index.php?acao=relatorio_fiscal" class="list-group-item <?= str_starts_with($rotaAtual, 'relatorio_fiscal') ? 'active' : '' ?>">
                <i class="bi bi-clipboard2-check-fill"></i> Relatórios de Visita
            </a>
            <a href="index.php?acao=sair" class="list-group-item text-danger mt-4">
                <i class="bi bi-box-arrow-left"></i> Sair
            </a>

            <?php else: ?>
            <a href="index.php?acao=painel" class="list-group-item <?= $rotaAtual == 'painel' ? 'active' : ''; ?>">
                <i class="bi bi-grid-fill"></i> Painel
            </a>
            <a href="index.php?acao=listar_obras" class="list-group-item <?= $rotaAtual == 'listar_obras' ? 'active' : ''; ?>">
                <i class="bi bi-building"></i> Obras
            </a>

            <?php if ($nivelAcesso === 'admin'): ?>
                <a href="index.php?acao=listar_usuarios" class="list-group-item <?= $rotaAtual == 'listar_usuarios' ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i> Equipe
                </a>
                <a href="index.php?acao=financeiro" class="list-group-item <?= $rotaAtual == 'financeiro' ? 'active' : ''; ?>">
                    <i class="bi bi-wallet2"></i> Financeiro
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['is_superadmin'] ?? false): ?>
                <a href="index.php?acao=relatorio_fiscal" class="list-group-item <?= str_starts_with($rotaAtual, 'relatorio_fiscal') ? 'active' : '' ?>">
                    <i class="bi bi-clipboard2-check-fill"></i> Fiscalização
                </a>
                <a href="index.php?acao=listar_empresas" class="list-group-item <?= $rotaAtual == 'listar_empresas' ? 'active' : ''; ?>">
                    <i class="bi bi-stars"></i> SaaS Admin
                </a>
                <a href="index.php?acao=logs_sistema" class="list-group-item <?= $rotaAtual == 'logs_sistema' ? 'active' : ''; ?>">
                    <i class="bi bi-journal-text"></i> Logs
                </a>
            <?php endif; ?>

            <a href="index.php?acao=configuracoes" class="list-group-item <?= $rotaAtual == 'configuracoes' ? 'active' : ''; ?>">
                <i class="bi bi-gear-fill"></i> Config
            </a>

            <a href="index.php?acao=sair" class="list-group-item text-danger mt-4">
                <i class="bi bi-box-arrow-left"></i> Sair
            </a>
            <?php endif; ?>

        </div>

        <!-- Badge do plano no rodapé da sidebar -->
        <div class="mt-auto p-3" style="border-top: 1px solid rgba(255,255,255,0.08);">
            <div class="d-flex align-items-center gap-2">
                <span class="badge-plano" style="background-color:<?= $planoCor ?>; color:<?= $planoTexto ?>;">
                    <?= $planoLabel ?>
                </span>
                <?php if (!PlanoHelper::podeUsarMedicao()): ?>
                    <a href="<?= PlanoHelper::getUrlUpgrade() ?>" target="_blank"
                       class="text-warning small fw-bold text-decoration-none"
                       title="Fazer upgrade de plano">
                        <i class="bi bi-arrow-up-circle-fill"></i> Upgrade
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="mobile-bottom-nav">
        <?php if ($nivelAcesso === 'fiscal'): ?>
        <a href="index.php?acao=relatorio_fiscal" class="mobile-nav-item <?= str_starts_with($rotaAtual, 'relatorio_fiscal') ? 'active' : '' ?>">
            <i class="bi bi-clipboard2-check-fill"></i> Relatórios
        </a>
        <a href="index.php?acao=sair" class="mobile-nav-item text-danger">
            <i class="bi bi-box-arrow-left"></i> Sair
        </a>
        <?php else: ?>
        <a href="index.php?acao=painel" class="mobile-nav-item <?= $rotaAtual == 'painel' ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i> Início
        </a>

        <a href="index.php?acao=listar_obras" class="mobile-nav-item <?= $rotaAtual == 'listar_obras' ? 'active' : ''; ?>">
            <i class="bi bi-building"></i> Obras
        </a>

        <div style="width: 60px; position: relative; text-align: center;">
            <?php if ($nivelAcesso === 'admin'): ?>
            <a href="index.php?acao=nova_obra" class="mobile-fab">
                <i class="bi bi-plus-lg"></i>
            </a>
            <?php endif; ?>
        </div>

        <a href="index.php?acao=financeiro" class="mobile-nav-item <?= $rotaAtual == 'financeiro' ? 'active' : ''; ?>">
            <i class="bi bi-wallet2"></i> Finan.
        </a>

        <?php if ($_SESSION['is_superadmin'] ?? false): ?>
        <a href="index.php?acao=listar_empresas" class="mobile-nav-item <?= $rotaAtual == 'listar_empresas' ? 'active' : ''; ?>">
            <i class="bi bi-stars"></i> SaaS
        </a>
        <?php endif; ?>

        <a href="index.php?acao=configuracoes" class="mobile-nav-item <?= $rotaAtual == 'configuracoes' ? 'active' : ''; ?>">
            <i class="bi bi-gear-fill"></i> Config
        </a>
        <?php endif; ?>
    </nav>

    <?php endif; ?>

    <div id="page-content-wrapper">

        <?php if (($_SESSION['is_superadmin'] ?? false) && ($idEmpresa != 1)): ?>
        <div style="background:#FFB300;color:#003366;padding:8px 20px;font-weight:700;font-size:.9rem;display:flex;align-items:center;gap:12px;z-index:999;">
            <i class="bi bi-eye-fill"></i>
            Visualizando como: <strong><?= htmlspecialchars($_SESSION['sa_empresa_nome'] ?? '') ?></strong>
            <a href="index.php?acao=sa_sair_empresa"
               style="background:#003366;color:#fff;padding:4px 12px;border-radius:20px;text-decoration:none;font-size:.8rem;">
                <i class="bi bi-arrow-left"></i> Voltar ao SuperAdmin
            </a>
        </div>
        <?php endif; ?>

        <?php if ($usuarioLogado): ?>
        <nav class="topbar">
            <div class="d-flex align-items-center">
                <div class="d-lg-none">
                    <?php if ($temLogo): ?>
                        <img src="<?= $urlLogo; ?>" alt="Logo" style="max-height: 35px;">
                    <?php else: ?>
                        <span class="fw-bold text-primary" style="font-family: 'Montserrat';">CHRONA</span>
                    <?php endif; ?>
                </div>
                <div class="d-none d-lg-block text-muted small fw-bold">
                    Gestão Inteligente / <span class="text-dark">Visão Geral</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">

                <!-- Badge do plano na topbar (visível em telas médias+) -->
                <span class="badge-plano d-none d-sm-inline"
                      style="background-color:<?= $planoCor ?>; color:<?= $planoTexto ?>;">
                    <?= $planoLabel ?>
                </span>

                <!-- Botão de ajuda / tour -->
                <button id="btnTourAjuda" title="Tour guiado — como usar o sistema"
                        style="width:32px;height:32px;border-radius:50%;border:2px solid var(--primary-color);
                               background:#fff;color:var(--primary-color);font-weight:800;font-size:.95rem;
                               cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    ?
                </button>

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                             style="width: 35px; height: 35px;">
                            <?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 1)); ?>
                        </div>
                        <span class="d-none d-md-block fw-bold text-dark small">
                            <?= explode(' ', $_SESSION['usuario_nome'] ?? 'User')[0]; ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                        <li><h6 class="dropdown-header small text-muted">Minha Conta</h6></li>
                        <li>
                            <span class="dropdown-item-text small text-muted d-flex align-items-center gap-2 pb-1">
                                Plano:
                                <span class="badge-plano" style="background-color:<?= $planoCor ?>; color:<?= $planoTexto ?>;">
                                    <?= $planoLabel ?>
                                </span>
                            </span>
                        </li>
                        <?php if (!PlanoHelper::podeUsarMedicao()): ?>
                            <li>
                                <a class="dropdown-item text-warning fw-bold small" href="<?= PlanoHelper::getUrlUpgrade() ?>" target="_blank">
                                    <i class="bi bi-arrow-up-circle-fill me-1"></i> Fazer Upgrade
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php?acao=alterar_senha">Alterar Senha</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="index.php?acao=sair">Sair</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php endif; ?>

        <div class="container-fluid px-3 py-4">

<?php if ($usuarioLogado): ?>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
<script src="assets/js/tour.js"></script>
<?php endif; ?>