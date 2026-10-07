<?php
$dataVenc    = $_SESSION['data_vencimento'] ?? null;
$dataFmt     = $dataVenc ? date('d/m/Y', strtotime($dataVenc)) : '—';
$diasVencido = $dataVenc ? (int)ceil((time() - strtotime($dataVenc)) / 86400) : 0;
$nomeUsuario = explode(' ', $_SESSION['usuario_nome'] ?? '')[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Licença Expirada — CHRONA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
    background: linear-gradient(150deg, #fff8f0 0%, #fff1e6 45%, #fffaf7 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
}

.wall-card {
    background: #fff;
    border-radius: 20px;
    padding: 48px 44px;
    max-width: 560px;
    width: 100%;
    box-shadow: 0 12px 60px rgba(220, 60, 0, .10), 0 2px 8px rgba(0,0,0,.06);
    border: 1px solid #ffe4d0;
    text-align: center;
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff6b35, #e53935);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    box-shadow: 0 6px 20px rgba(229,57,53,.3);
    animation: pulse-warn 2s infinite;
}
@keyframes pulse-warn {
    0%, 100% { box-shadow: 0 6px 20px rgba(229,57,53,.3); }
    50%       { box-shadow: 0 6px 30px rgba(229,57,53,.55); }
}

.badge-vencido {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff0eb;
    color: #c0391b;
    border: 1.5px solid #ffccbc;
    border-radius: 20px;
    padding: 5px 14px;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    margin-bottom: 16px;
}

h1 {
    font-family: 'Montserrat', sans-serif;
    font-weight: 900;
    font-size: 1.75rem;
    color: #1a1a2e;
    margin-bottom: 10px;
    line-height: 1.2;
}

.subtitle {
    font-size: .95rem;
    color: #555;
    line-height: 1.65;
    margin-bottom: 24px;
}

.info-box {
    background: #fef6f2;
    border: 1px solid #ffd5c2;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 28px;
    text-align: left;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.info-item .label {
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .8px;
    font-weight: 700;
    color: #999;
    margin-bottom: 3px;
}
.info-item .value {
    font-size: .95rem;
    font-weight: 700;
    color: #1a1a2e;
}
.info-item .value.danger { color: #c0391b; }

.seguro-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 28px;
    text-align: left;
    font-size: .85rem;
    color: #14532d;
}
.seguro-box i { font-size: 1.2rem; color: #16a34a; flex-shrink: 0; }

.btn-renovar {
    display: block;
    width: 100%;
    background: linear-gradient(135deg, #ff6b35, #e53935);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 16px 24px;
    font-size: 1.05rem;
    font-weight: 800;
    font-family: 'Montserrat', sans-serif;
    text-decoration: none;
    margin-bottom: 12px;
    box-shadow: 0 4px 16px rgba(229,57,53,.35);
    transition: transform .15s, box-shadow .15s;
    letter-spacing: .3px;
}
.btn-renovar:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(229,57,53,.45);
}

.btn-whatsapp {
    display: block;
    width: 100%;
    background: #25d366;
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 13px 24px;
    font-size: .95rem;
    font-weight: 700;
    text-decoration: none;
    margin-bottom: 20px;
    transition: opacity .15s;
}
.btn-whatsapp:hover { color: #fff; opacity: .9; }

.divider {
    border: none;
    border-top: 1px solid #f0ece8;
    margin-bottom: 16px;
}

.links-footer {
    display: flex;
    justify-content: center;
    gap: 24px;
    font-size: .82rem;
}
.links-footer a {
    color: #888;
    text-decoration: none;
    transition: color .15s;
}
.links-footer a:hover { color: #333; }

.logo-topo {
    text-align: center;
    margin-bottom: 20px;
}
.logo-topo span {
    font-family: 'Montserrat', sans-serif;
    font-weight: 900;
    font-size: 1.4rem;
    color: #1a1a2e;
    letter-spacing: -1px;
}
.logo-topo span b { color: #ff6b35; }

@media (max-width: 540px) {
    .wall-card { padding: 36px 24px; }
    h1 { font-size: 1.45rem; }
    .info-box { grid-template-columns: 1fr; gap: 10px; }
}
</style>
</head>
<body>

<div class="logo-topo">
    <span>CHR<b>O</b>NA</span>
</div>

<div class="wall-card">

    <div class="icon-circle">
        <i class="bi bi-lock-fill" style="font-size:2rem;color:#fff;"></i>
    </div>

    <div class="badge-vencido">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Licença Expirada há <?= $diasVencido ?> dia<?= $diasVencido !== 1 ? 's' : '' ?>
    </div>

    <h1>Acesso temporariamente<br>suspenso, <?= htmlspecialchars($nomeUsuario) ?></h1>

    <p class="subtitle">
        A licença da sua empresa venceu e sua equipe está sem acesso ao sistema.
        Renove agora e volte a gerenciar suas obras em menos de 5 minutos.
    </p>

    <div class="info-box">
        <div class="info-item">
            <div class="label">Licença venceu em</div>
            <div class="value danger"><?= $dataFmt ?></div>
        </div>
        <div class="info-item">
            <div class="label">Situação</div>
            <div class="value danger">Suspensa</div>
        </div>
        <div class="info-item">
            <div class="label">Empresa</div>
            <div class="value"><?= htmlspecialchars($_SESSION['usuario_nome'] ?? '') ?></div>
        </div>
        <div class="info-item">
            <div class="label">Plano anterior</div>
            <div class="value"><?= ucfirst($_SESSION['plano'] ?? 'autonomo') ?></div>
        </div>
    </div>

    <div class="seguro-box">
        <i class="bi bi-shield-fill-check"></i>
        <span><strong>Seus dados estão 100% seguros.</strong> Diários, relatórios, cronogramas e documentos continuam intactos — o acesso é liberado imediatamente após a renovação.</span>
    </div>

    <a href="index.php?acao=planos" class="btn-renovar">
        <i class="bi bi-arrow-up-circle-fill me-2"></i>
        Renovar Agora — Ver Planos
    </a>

    <a href="https://wa.me/5561985303841?text=Olá!%20Quero%20renovar%20minha%20licença%20CHRONA."
       class="btn-whatsapp" target="_blank" rel="noopener">
        <i class="bi bi-whatsapp me-2"></i>
        Falar com Suporte pelo WhatsApp
    </a>

    <hr class="divider">

    <div class="links-footer">
        <a href="index.php?acao=sair">
            <i class="bi bi-box-arrow-left me-1"></i>Sair da conta
        </a>
        <a href="index.php?acao=planos" target="_blank">
            <i class="bi bi-credit-card me-1"></i>Ver planos e preços
        </a>
    </div>

</div>

</body>
</html>
