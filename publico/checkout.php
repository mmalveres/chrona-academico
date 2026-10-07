<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — CHRONA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- SDK oficial MP — DEVE ser carregado antes de qualquer uso de MercadoPago() -->
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <!-- Script de segurança do MP: captura fingerprint do dispositivo (reduz cc_rejected_high_risk) -->
    <script src="https://www.mercadopago.com/v2/security.js" view="checkout"></script>
    <style>
        :root{--navy:#003366;--gold:#FFB300;--light:#F4F7FE;--text:#2B3674;--green:#10B981;--red:#EF4444;}
        *{box-sizing:border-box;}
        body{font-family:'Nunito Sans',sans-serif;background:var(--light);color:var(--text);min-height:100vh;}
        .topbar{background:var(--navy);padding:14px 30px;display:flex;align-items:center;justify-content:space-between;}
        .topbar-logo{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.3rem;color:#fff;text-decoration:none;}
        .topbar-secure{color:rgba(255,255,255,.6);font-size:.8rem;display:flex;align-items:center;gap:6px;}
        .topbar-secure i{color:var(--green);}
        .checkout-progress{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 30px;}
        .progress-steps{display:flex;max-width:600px;margin:0 auto;}
        .step{flex:1;text-align:center;padding:16px 8px;font-size:.78rem;font-weight:700;color:#9ca3af;}
        .step.active{color:var(--navy);}
        .step.done{color:var(--green);}
        .step-num{width:28px;height:28px;border-radius:50%;background:#e5e7eb;color:#9ca3af;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;margin:0 auto 6px;transition:all .3s;}
        .step.active .step-num{background:var(--navy);color:#fff;}
        .step.done .step-num{background:var(--green);color:#fff;}
        .checkout-body{max-width:1000px;margin:0 auto;padding:30px 16px 60px;}
        .checkout-grid{display:grid;grid-template-columns:1fr 380px;gap:24px;}
        @media(max-width:768px){.checkout-grid{grid-template-columns:1fr;}}
        .card-box{background:#fff;border-radius:20px;border:1px solid #e5e7eb;box-shadow:0 4px 20px rgba(0,0,0,.04);padding:28px;}
        .card-box h5{font-family:'Montserrat',sans-serif;font-weight:800;font-size:1rem;color:var(--navy);margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #f3f4f6;}
        .plan-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;}
        .plan-price-big{font-family:'Montserrat',sans-serif;font-weight:900;font-size:2.5rem;color:var(--navy);line-height:1;}
        .plan-feature{display:flex;align-items:center;gap:8px;font-size:.875rem;padding:6px 0;}
        .fi-ok{color:var(--green);}
        .fi-no{color:#d1d5db;}
        .total-box{background:var(--light);border-radius:14px;padding:16px;margin-top:20px;}
        .total-row{display:flex;justify-content:space-between;align-items:center;font-size:.875rem;padding:4px 0;}
        .total-row.main{font-weight:800;font-size:1rem;margin-top:8px;padding-top:10px;border-top:1px solid #e5e7eb;}
        .badge-trial{background:#ecfdf5;color:var(--green);border:1px solid #a7f3d0;border-radius:8px;padding:12px 16px;font-size:.85rem;font-weight:700;display:flex;align-items:center;gap:10px;margin-top:16px;}
        .pay-tabs{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:24px;}
        .pay-tab{border:2px solid #e5e7eb;border-radius:12px;padding:10px 8px;text-align:center;cursor:pointer;transition:all .2s;background:#fff;}
        .pay-tab:hover{border-color:var(--navy);}
        .pay-tab.active{border-color:var(--navy);background:#eef3fc;}
        .pay-tab i{font-size:1.3rem;color:var(--navy);display:block;margin-bottom:4px;}
        .pay-tab span{font-size:.72rem;font-weight:700;color:var(--text);}
        .form-label{font-weight:700;font-size:.82rem;color:#4b5563;margin-bottom:6px;}
        .form-control,.form-select{border-radius:10px;border:1.5px solid #e5e7eb;padding:11px 14px;font-size:.9rem;transition:border-color .2s;}
        .form-control:focus,.form-select:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(0,51,102,.08);}
        .form-control.is-invalid{border-color:var(--red);}
        .card-visual{background:linear-gradient(135deg,var(--navy),#0056b3);border-radius:16px;padding:22px 24px;color:#fff;margin-bottom:20px;position:relative;overflow:hidden;min-height:160px;}
        .card-visual::before{content:'';position:absolute;top:-40px;right:-40px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.06);}
        .card-chip{width:40px;height:30px;background:var(--gold);border-radius:6px;margin-bottom:20px;}
        .card-number-display{font-size:1.1rem;letter-spacing:3px;font-weight:700;margin-bottom:16px;font-family:'Montserrat',sans-serif;}
        .card-meta{display:flex;justify-content:space-between;font-size:.75rem;opacity:.8;}
        .pix-qr{display:flex;flex-direction:column;align-items:center;padding:20px;}
        .pix-qr-img{width:200px;height:200px;border:3px solid var(--navy);border-radius:16px;padding:8px;margin-bottom:16px;display:flex;align-items:center;justify-content:center;background:#fff;}
        .pix-qr-img img{width:100%;height:100%;object-fit:contain;}
        .pix-key-box{background:var(--light);border-radius:10px;padding:12px 16px;font-size:.78rem;word-break:break-all;text-align:center;color:var(--navy);font-weight:700;margin-bottom:12px;width:100%;}
        .btn-copy{background:var(--navy);color:#fff;border:none;border-radius:10px;padding:10px 20px;font-weight:700;font-size:.85rem;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:6px;}
        .btn-copy:hover{background:#002255;}
        .btn-copy.copied{background:var(--green);}
        .pix-steps{margin-top:20px;width:100%;}
        .pix-step{display:flex;align-items:flex-start;gap:10px;font-size:.82rem;color:#4b5563;padding:6px 0;}
        .pix-step-num{width:22px;height:22px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;flex-shrink:0;}
        .pix-timer{background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:10px 14px;margin-top:14px;display:flex;align-items:center;gap:8px;font-size:.82rem;color:#92400e;font-weight:700;}
        .pix-aguardando{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-top:12px;display:flex;align-items:center;gap:10px;font-size:.82rem;color:#1e40af;font-weight:700;}
        .boleto-info{background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;padding:20px;text-align:center;}
        .boleto-info i{font-size:2.5rem;color:#f59e0b;margin-bottom:12px;}
        .boleto-barcode{font-family:'Courier New',monospace;font-size:.75rem;letter-spacing:1px;background:#f3f4f6;padding:10px;border-radius:8px;margin:14px 0;word-break:break-all;}
        .trial-hero{text-align:center;padding:10px 0 20px;}
        .trial-hero .emoji{font-size:3rem;margin-bottom:12px;}
        .trial-badge-big{display:inline-block;background:var(--green);color:#fff;font-family:'Montserrat',sans-serif;font-weight:900;font-size:1rem;padding:8px 22px;border-radius:50px;margin-bottom:16px;}
        .trial-feature{display:flex;align-items:center;gap:10px;font-size:.9rem;padding:7px 0;}
        .trial-feature i{color:var(--green);font-size:1rem;}
        .btn-pay{width:100%;padding:16px;border:none;border-radius:14px;font-family:'Montserrat',sans-serif;font-weight:800;font-size:1rem;cursor:pointer;transition:all .2s;letter-spacing:.3px;background:var(--gold);color:var(--navy);}
        .btn-pay:hover{filter:brightness(1.05);transform:translateY(-1px);}
        .btn-pay:disabled{opacity:.6;cursor:not-allowed;transform:none;}
        .btn-pay-trial{background:var(--green);color:#fff;}
        .seals{display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin-top:16px;}
        .seal{display:flex;align-items:center;gap:5px;font-size:.72rem;color:#6b7280;font-weight:700;}
        .seal i{color:var(--green);}
        .success-screen{text-align:center;padding:40px 20px;}
        .success-screen .big-icon{font-size:5rem;margin-bottom:20px;}
        .success-screen h3{font-family:'Montserrat',sans-serif;font-weight:900;color:var(--navy);margin-bottom:10px;}
        .spinner-overlay{display:none;position:fixed;inset:0;background:rgba(255,255,255,.85);z-index:9999;align-items:center;justify-content:center;flex-direction:column;gap:16px;}
        .spinner-overlay.show{display:flex;}
        .cpf-note{font-size:.75rem;color:#9ca3af;margin-top:4px;}
        .alerta-cred{background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:12px 16px;font-size:.82rem;color:#856404;margin-bottom:16px;display:none;}
    </style>
</head>
<body>

<div class="spinner-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
    <p class="fw-bold text-secondary" id="loadingMsg">Processando...</p>
</div>

<div class="topbar">
    <a href="landing.php" class="topbar-logo"><span style="color:var(--gold);">●</span> CHRONA</a>
    <div class="topbar-secure"><i class="bi bi-shield-lock-fill"></i> Pagamento 100% seguro · SSL</div>
</div>

<div class="checkout-progress">
    <div class="progress-steps">
        <div class="step done"   id="step1"><div class="step-num"><i class="bi bi-check"></i></div>Plano</div>
        <div class="step active" id="step2"><div class="step-num">2</div>Dados</div>
        <div class="step"        id="step3"><div class="step-num">3</div>Pagamento</div>
        <div class="step"        id="step4"><div class="step-num">4</div>Ativação</div>
    </div>
</div>

<?php
require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../config/mercadopago.php';

use Aplicacao\Configuracao\BancoDeDados;
use Aplicacao\Helpers\PrecosHelper;

if (session_status() === PHP_SESSION_NONE) session_start();

// Detecta upgrade de usuário já logado
$isUpgrade   = isset($_SESSION['usuario_id']);
$idEmpresa   = (int)($_SESSION['id_empresa'] ?? 0);
$usuarioNome = '';
$usuarioEmail= '';
$empresaNome = '';

if ($isUpgrade && $idEmpresa) {
    try {
        $conn = BancoDeDados::getConexao();
        $st   = $conn->prepare(
            "SELECT u.nome, u.email, e.nome_fantasia
             FROM usuarios u JOIN empresas e ON e.id = u.id_empresa
             WHERE u.id = ? LIMIT 1"
        );
        $st->execute([$_SESSION['usuario_id']]);
        if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $usuarioNome  = $row['nome'];
            $usuarioEmail = $row['email'];
            $empresaNome  = $row['nome_fantasia'];
        }
    } catch (\Exception $e) { $isUpgrade = false; }
}

$precosDB = PrecosHelper::getPrecos();
$planos = [
    'autonomo'    => ['label'=>'Autônomo',    'cor'=>'#6c757d','texto'=>'#fff',
        'preco_m'=>$precosDB['autonomo']['mensal'],    'preco_a'=>$precosDB['autonomo']['anual'],
        'features'=>['1 Usuário','1 Obra Ativa','RDO Ilimitado','Importar Orçamento'],'bloqueados'=>['Cronograma','Medição Automática']],
    'construtora' => ['label'=>'Construtora', 'cor'=>'#0d6efd','texto'=>'#fff',
        'preco_m'=>$precosDB['construtora']['mensal'], 'preco_a'=>$precosDB['construtora']['anual'],
        'features'=>['3 Usuários','Até 3 Obras','Medição Automática','Cronograma','Importar Orçamento','Compressão de Imagens'],'bloqueados'=>[]],
    'empresarial' => ['label'=>'Empresarial', 'cor'=>'#FFB300','texto'=>'#333',
        'preco_m'=>$precosDB['empresarial']['mensal'], 'preco_a'=>$precosDB['empresarial']['anual'],
        'features'=>['Usuários Ilimitados','Obras Ilimitadas','Todas as Funções','Relatórios Plus','Suporte WhatsApp'],'bloqueados'=>[]],
];
$planoKey   = $_GET['plano']   ?? 'construtora';
if (!isset($planos[$planoKey])) $planoKey = 'construtora';
$plano      = $planos[$planoKey];
$isTrial    = isset($_GET['trial']) && $_GET['trial'] == '1';
$billing    = $_GET['billing'] ?? 'mensal';
$preco      = $billing === 'anual' ? $plano['preco_a'] : $plano['preco_m'];
$precoAnual = $preco * 12;
$economia   = ($plano['preco_m'] - $plano['preco_a']) * 12;
$mpPublicKey = MP_PUBLIC_KEY;
?>

<div class="checkout-body">
<div class="checkout-grid">

<!-- ═══════ COLUNA PRINCIPAL ═══════ -->
<div>

    <!-- DADOS -->
    <div class="card-box mb-4">
        <h5><i class="bi bi-person-fill me-2 text-primary"></i> Seus dados</h5>
        <?php if ($isUpgrade): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-3" style="font-size:.85rem;border-radius:10px;">
            <i class="bi bi-shield-check-fill fs-5"></i>
            <span>Upgrade da conta <strong><?= htmlspecialchars($usuarioEmail) ?></strong> — seus dados e obras serão mantidos.</span>
        </div>
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome completo *</label>
                <input type="text" class="form-control" id="nome" placeholder="Seu nome"
                       value="<?= htmlspecialchars($usuarioNome) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Nome da empresa</label>
                <input type="text" class="form-control" id="empresa" placeholder="Sua construtora"
                       value="<?= htmlspecialchars($empresaNome) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">E-mail *</label>
                <input type="email" class="form-control" id="email" placeholder="seu@email.com"
                       value="<?= htmlspecialchars($usuarioEmail) ?>"
                       <?= $isUpgrade ? 'readonly style="background:#f3f4f6;cursor:not-allowed;" title=\'E-mail não pode ser alterado no upgrade\'' : '' ?>>
                <?php if ($isUpgrade): ?>
                <div class="cpf-note"><i class="bi bi-lock-fill"></i> E-mail vinculado à conta existente</div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">WhatsApp *</label>
                <input type="tel" class="form-control" id="whatsapp" placeholder="(00) 00000-0000">
            </div>
            <div class="col-md-6">
                <label class="form-label">CPF *</label>
                <input type="text" class="form-control" id="cpf" placeholder="000.000.000-00" maxlength="14">
                <div class="cpf-note">Exigido pelo Mercado Pago para processar o pagamento</div>
            </div>
            <?php if (!$isUpgrade): ?>
            <div class="col-md-6">
                <label class="form-label">Senha de acesso *</label>
                <input type="password" class="form-control" id="senha" placeholder="Mínimo 8 caracteres">
            </div>
            <div class="col-12">
                <label class="form-label">Confirmar senha *</label>
                <input type="password" class="form-control" id="confirmaSenha" placeholder="Repita a senha">
            </div>
            <?php else: ?>
            <input type="hidden" id="senha" value="__upgrade__">
            <input type="hidden" id="confirmaSenha" value="__upgrade__">
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isTrial): ?>
    <!-- TRIAL -->
    <div class="card-box mb-4">
        <div class="trial-hero">
            <div class="emoji">🎁</div>
            <div class="trial-badge-big">30 dias grátis</div>
            <p class="text-muted" style="font-size:.9rem;max-width:380px;margin:0 auto 20px;">
                Teste o plano <strong><?= $plano['label'] ?></strong> gratuitamente. Nenhum cartão necessário.
            </p>
        </div>
        <div class="px-3">
            <?php foreach($plano['features'] as $f): ?>
                <div class="trial-feature"><i class="bi bi-check-circle-fill"></i> <?= $f ?></div>
            <?php endforeach; ?>
            <div class="trial-feature" style="color:#9ca3af;margin-top:8px;">
                <i class="bi bi-info-circle" style="color:#9ca3af;"></i>
                Após 30 dias, escolha o plano ou cancele — sem compromisso.
            </div>
        </div>
        <button class="btn-pay btn-pay-trial mt-4" onclick="ativarTrial()">
            <i class="bi bi-lightning-charge-fill me-2"></i> Ativar Trial Gratuito Agora
        </button>
        <div class="seals mt-3">
            <div class="seal"><i class="bi bi-shield-check"></i> Sem cartão</div>
            <div class="seal"><i class="bi bi-x-circle"></i> Sem compromisso</div>
            <div class="seal"><i class="bi bi-arrow-counterclockwise"></i> Cancele quando quiser</div>
        </div>
    </div>

    <?php else: ?>
    <!-- PAGAMENTO -->
    <div class="card-box mb-4">
        <h5><i class="bi bi-credit-card-fill me-2 text-primary"></i> Forma de pagamento</h5>

        <div class="pay-tabs">
            <div class="pay-tab active" onclick="trocarPagamento('pix',this)">
                <i class="bi bi-qr-code-scan"></i><span>PIX</span>
            </div>
            <div class="pay-tab" onclick="trocarPagamento('cartao',this)">
                <i class="bi bi-credit-card-2-front"></i><span>Cartão</span>
            </div>
            <div class="pay-tab" onclick="trocarPagamento('boleto',this)">
                <i class="bi bi-upc-scan"></i><span>Boleto</span>
            </div>
            <div class="pay-tab" onclick="trocarPagamento('trial',this)">
                <i class="bi bi-gift"></i><span>Trial 30d</span>
            </div>
        </div>

        <!-- PIX -->
        <div id="panel-pix">
            <div id="pix-inicio" class="text-center py-4">
                <i class="bi bi-qr-code-scan" style="font-size:3rem;color:var(--navy);"></i>
                <p class="mt-3 text-muted" style="font-size:.9rem;">Preencha seus dados acima e clique para gerar o QR Code PIX.</p>
                <button class="btn-pay mt-2" onclick="gerarPix()" style="max-width:320px;margin:0 auto;">
                    <i class="bi bi-qr-code me-2"></i> Gerar QR Code PIX
                </button>
            </div>
            <div id="pix-gerado" class="d-none">
                <div class="pix-qr">
                    <div class="pix-qr-img" id="pixQrContainer"></div>
                    <div class="pix-key-box" id="pixKey">—</div>
                    <button class="btn-copy" id="btnCopy" onclick="copiarPix()">
                        <i class="bi bi-clipboard"></i> Copiar código PIX
                    </button>
                    <div class="pix-timer">
                        <i class="bi bi-clock-fill"></i> Válido por: <span id="pixTimer">30:00</span>
                    </div>
                    <div class="pix-aguardando">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                        Aguardando pagamento… Esta página atualiza automaticamente.
                    </div>
                    <div class="pix-steps">
                        <div class="pix-step"><div class="pix-step-num">1</div> Abra seu banco pelo celular</div>
                        <div class="pix-step"><div class="pix-step-num">2</div> Acesse PIX → "Pagar com código"</div>
                        <div class="pix-step"><div class="pix-step-num">3</div> Cole o código copiado e confirme</div>
                        <div class="pix-step"><div class="pix-step-num">4</div> Acesso liberado em até 5 minutos</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARTÃO -->
        <div id="panel-cartao" class="d-none">
            <!-- Preview visual do cartão -->
            <div class="card-visual">
                <div class="card-chip"></div>
                <div class="card-number-display" id="cardNumberDisplay">•••• •••• •••• ••••</div>
                <div class="card-meta">
                    <div>
                        <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;">Titular</div>
                        <div style="font-size:.85rem;" id="cardNameDisplay">SEU NOME</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;">Validade</div>
                        <div style="font-size:.85rem;" id="cardExpDisplay">MM/AA</div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Número do cartão *</label>
                    <input type="text" class="form-control" id="cardNumber"
                           placeholder="0000 0000 0000 0000" maxlength="19" autocomplete="cc-number">
                </div>
                <div class="col-12">
                    <label class="form-label">Nome no cartão *</label>
                    <input type="text" class="form-control" id="cardName"
                           placeholder="NOME COMO NO CARTÃO" autocomplete="cc-name">
                </div>
                <div class="col-6">
                    <label class="form-label">Validade * (MM/AA)</label>
                    <input type="text" class="form-control" id="cardExp"
                           placeholder="MM/AA" maxlength="5" autocomplete="cc-exp">
                </div>
                <div class="col-6">
                    <label class="form-label">CVV *</label>
                    <input type="text" class="form-control" id="cardCVV"
                           placeholder="•••" maxlength="4" autocomplete="cc-csc">
                </div>
                <div class="col-12">
                    <label class="form-label">Parcelas</label>
                    <select class="form-select" id="parcelas">
                        <option value="1">1x de R$ <?= number_format($preco,2,',','.') ?> sem juros</option>
                        <?php if($preco>=100): ?>
                        <option value="2">2x de R$ <?= number_format($preco/2,2,',','.') ?> sem juros</option>
                        <option value="3">3x de R$ <?= number_format($preco/3,2,',','.') ?> sem juros</option>
                        <?php endif; ?>
                        <?php if($billing==='anual'): ?>
                        <option value="6">6x de R$ <?= number_format($precoAnual/6,2,',','.') ?> sem juros</option>
                        <option value="12">12x de R$ <?= number_format($precoAnual/12,2,',','.') ?> sem juros</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <button class="btn-pay mt-4" id="btnPagarCartao" onclick="pagarCartao()">
                <i class="bi bi-lock-fill me-2"></i> Pagar com segurança
            </button>
            <div class="seals">
                <div class="seal"><i class="bi bi-shield-lock"></i> SSL 256-bit</div>
                <div class="seal"><i class="bi bi-check-circle"></i> Visa/Master/Elo</div>
                <div class="seal"><i class="bi bi-arrow-counterclockwise"></i> Reembolso 7 dias</div>
            </div>
        </div>

        <!-- BOLETO -->
        <div id="panel-boleto" class="d-none">
            <div class="boleto-info">
                <i class="bi bi-upc-scan d-block"></i>
                <h6 class="fw-bold text-dark mb-2">Boleto Bancário</h6>
                <p class="text-muted small mb-0">Vencimento em 3 dias úteis. Acesso liberado após compensação (até 2 dias).</p>
                <div id="boletoBarcode" class="boleto-barcode d-none"></div>
                <div class="small text-muted mt-2">Valor: <strong>R$ <?= number_format($preco,2,',','.') ?></strong></div>
            </div>
            <div id="boletoLinks" class="d-none mt-3">
                <a id="boletoUrl" href="#" target="_blank"
                   class="btn-pay d-block text-center text-decoration-none"
                   style="background:var(--navy);color:#fff;padding:14px;border-radius:14px;font-weight:800;">
                    <i class="bi bi-box-arrow-up-right me-2"></i> Abrir / Imprimir Boleto
                </a>
                <button class="btn-copy mt-2 w-100 justify-content-center" onclick="copiarBoleto()">
                    <i class="bi bi-clipboard"></i> Copiar código de barras
                </button>
            </div>
            <button class="btn-pay mt-4" id="btnGerarBoleto" onclick="gerarBoleto()" style="background:var(--navy);color:#fff;">
                <i class="bi bi-download me-2"></i> Gerar Boleto
            </button>
            <p class="text-center text-muted small mt-3">
                <i class="bi bi-info-circle me-1"></i> O boleto será enviado também ao seu e-mail.
            </p>
        </div>

        <!-- TRIAL (aba) -->
        <div id="panel-trial" class="d-none">
            <div class="trial-hero" style="padding:10px 0 16px;">
                <div class="emoji">🎁</div>
                <div class="trial-badge-big">30 dias grátis</div>
                <p class="text-muted" style="font-size:.875rem;max-width:340px;margin:0 auto;">
                    Sem cartão. Sem compromisso. Teste e assine apenas se gostar.
                </p>
            </div>
            <button class="btn-pay btn-pay-trial" onclick="ativarTrial()">
                <i class="bi bi-lightning-charge-fill me-2"></i> Ativar Trial Gratuito
            </button>
            <div class="seals mt-3">
                <div class="seal"><i class="bi bi-shield-check"></i> Sem cartão</div>
                <div class="seal"><i class="bi bi-x-circle"></i> Cancele quando quiser</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════ SIDEBAR ═══════ -->
<div>
    <div class="card-box" style="position:sticky;top:20px;">
        <h5><i class="bi bi-receipt me-2"></i> Resumo do pedido</h5>
        <span class="plan-badge" style="background:<?= $plano['cor'] ?>;color:<?= $plano['texto'] ?>;">
            <?= $plano['label'] ?>
        </span>
        <div class="plan-price-big">
            R$ <?= $billing==='anual' ? number_format($preco,2,',','.') : $preco ?>
            <span style="font-size:1rem;font-weight:400;color:#9ca3af;">/mês</span>
        </div>
        <div style="font-size:.8rem;color:#9ca3af;margin-bottom:20px;">
            Cobrança <?= $billing==='anual' ? 'anual — R$ '.number_format($precoAnual,2,',','.').'/ano' : 'mensal' ?>
        </div>
        <?php foreach($plano['features'] as $f): ?>
            <div class="plan-feature"><i class="bi bi-check-circle-fill fi-ok"></i> <?= $f ?></div>
        <?php endforeach; ?>
        <?php foreach($plano['bloqueados'] as $f): ?>
            <div class="plan-feature" style="color:#d1d5db;"><i class="bi bi-x-circle fi-no"></i> <?= $f ?></div>
        <?php endforeach; ?>
        <div class="total-box">
            <div class="total-row"><span>Plano <?= $plano['label'] ?></span><span>R$ <?= $preco ?>/mês</span></div>
            <?php if($billing==='anual'): ?>
            <div class="total-row"><span>Desconto anual</span><span style="color:var(--green);">-R$ <?= $economia ?>/ano</span></div>
            <?php endif; ?>
            <?php if($isTrial): ?>
            <div class="total-row"><span>Trial 30 dias</span><span style="color:var(--green);">GRÁTIS</span></div>
            <?php endif; ?>
            <div class="total-row main">
                <span>Total hoje</span>
                <span><?= $isTrial ? 'R$ 0,00' : 'R$ '.($billing==='anual' ? number_format($precoAnual,2,',','.') : number_format($preco,2,',','.')) ?></span>
            </div>
        </div>
        <?php if($isTrial): ?>
        <div class="badge-trial">
            <i class="bi bi-gift-fill" style="font-size:1.3rem;color:var(--green);"></i>
            <div>
                <strong>30 dias grátis</strong>
                <div style="font-size:.78rem;font-weight:400;color:#4b5563;">Depois R$ <?= $preco ?>/mês. Cancele quando quiser.</div>
            </div>
        </div>
        <?php endif; ?>
        <div class="seals mt-4">
            <div class="seal"><i class="bi bi-shield-check"></i> Garantia 7 dias</div>
            <div class="seal"><i class="bi bi-lock-fill"></i> Dados seguros</div>
        </div>
        <a href="landing.php#pricing" class="d-block text-center mt-3"
           style="font-size:.8rem;color:#9ca3af;text-decoration:none;">
            <i class="bi bi-arrow-left me-1"></i> Trocar de plano
        </a>
    </div>
</div>

</div><!-- /checkout-grid -->
</div><!-- /checkout-body -->

<!-- TELA DE SUCESSO -->
<div id="successScreen" class="d-none" style="position:fixed;inset:0;background:linear-gradient(150deg,#f0fdf4 0%,#dcfce7 60%,#f0fdf4 100%);z-index:9998;overflow-y:auto;padding:40px 20px;display:flex;align-items:flex-start;justify-content:center;">
    <div class="success-screen" style="max-width:520px;width:100%;margin:auto;">
        <div class="big-icon" id="successIcon" style="font-size:4.5rem;margin-bottom:16px;">✅</div>
        <h3 id="successTitle" style="font-family:'Montserrat',sans-serif;font-weight:900;color:#14532d;font-size:1.7rem;">Pagamento confirmado!</h3>
        <p class="mb-4" id="successMsg" style="color:#374151;font-size:.97rem;line-height:1.6;">Seu acesso foi liberado.</p>

        <div class="card-box mb-3" style="text-align:left;border:1.5px solid #bbf7d0;background:#fff;">
            <div class="row g-3">
                <div class="col-6">
                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.7px;font-weight:700;color:#6b7280;margin-bottom:3px;">Plano</div>
                    <div style="font-weight:800;color:#166534;font-size:1rem;" id="successPlano"><?= ucfirst($planoKey) ?></div>
                </div>
                <div class="col-6">
                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.7px;font-weight:700;color:#6b7280;margin-bottom:3px;">Período</div>
                    <div style="font-weight:700;color:#1a1a2e;" id="successBilling"><?= $billing === 'anual' ? 'Anual' : 'Mensal' ?></div>
                </div>
                <div class="col-12">
                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.7px;font-weight:700;color:#6b7280;margin-bottom:3px;">E-mail de acesso</div>
                    <div style="font-weight:700;color:#1a1a2e;" id="credEmail">—</div>
                </div>
                <div class="col-12" id="senhaInfo">
                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.7px;font-weight:700;color:#6b7280;margin-bottom:3px;">Senha</div>
                    <div style="font-weight:700;color:#1a1a2e;">A que você cadastrou</div>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:10px 14px;font-size:.82rem;color:#14532d;">
            <i class="bi bi-envelope-check-fill fs-5"></i>
            <span>Confirmação enviada para <strong id="credEmailRepeat">—</strong></span>
        </div>

        <a id="btnEntrar" href="index.php" class="btn-pay d-block text-decoration-none mb-3"
           style="display:flex;align-items:center;justify-content:center;gap:10px;max-width:400px;margin:0 auto;background:linear-gradient(135deg,#16a34a,#15803d);box-shadow:0 4px 16px rgba(22,163,74,.35);">
            <i class="bi bi-box-arrow-in-right"></i>
            <span id="btnEntrarTxt">Entrar na plataforma</span>
        </a>

        <p id="redirectCountdown" class="d-none text-center mt-2" style="color:#6b7280;font-size:.82rem;">
            <i class="bi bi-clock"></i> Redirecionando automaticamente em <strong id="cdTimer">5</strong>s
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ════════════════════════════════════════════════════
// INICIALIZAÇÃO — Public Key vem do PHP (config/mercadopago.php)
// ════════════════════════════════════════════════════
const MP_PUBLIC_KEY = '<?= htmlspecialchars($mpPublicKey, ENT_QUOTES) ?>';
const mp        = new MercadoPago(MP_PUBLIC_KEY, { locale: 'pt-BR' });
const PLANO     = '<?= $planoKey ?>';
const BILLING   = '<?= $billing ?>';
const IS_UPGRADE = <?= $isUpgrade ? 'true' : 'false' ?>;
const ID_EMPRESA = <?= $idEmpresa ?>;

// ── Troca abas ──
function trocarPagamento(tipo, el) {
    ['pix','cartao','boleto','trial'].forEach(p => {
        document.getElementById('panel-'+p)?.classList.add('d-none');
    });
    document.getElementById('panel-'+tipo)?.classList.remove('d-none');
    document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');
}

// ── Detecção de bandeira via BIN lookup do MP ──
let detectedPaymentMethodId = '';
let detectedIssuerId        = '';

// ── Máscaras ──
document.getElementById('cardNumber')?.addEventListener('input', async function(e) {
    let v = e.target.value.replace(/\D/g,'').substring(0,16);
    v = v.match(/.{1,4}/g)?.join(' ') || v;
    e.target.value = v;
    document.getElementById('cardNumberDisplay').textContent = v || '•••• •••• •••• ••••';

    const bin = v.replace(/\s/g,'').substring(0, 6);
    if (bin.length >= 6) {
        try {
            const methods = await mp.getPaymentMethods({ bin });
            if (methods?.results?.[0]) {
                detectedPaymentMethodId = methods.results[0].id;           // ex: "visa", "master"
                detectedIssuerId        = String(methods.results[0].issuer?.id ?? '');
            }
        } catch(_) { /* silencioso */ }
    }
});
document.getElementById('cardName')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
    document.getElementById('cardNameDisplay').textContent = e.target.value || 'SEU NOME';
});
document.getElementById('cardExp')?.addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g,'');
    if (v.length > 2) v = v.substring(0,2)+'/'+v.substring(2,4);
    e.target.value = v;
    document.getElementById('cardExpDisplay').textContent = v || 'MM/AA';
});
document.getElementById('cardCVV')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g,'').substring(0,4);
});
document.getElementById('whatsapp')?.addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g,'').substring(0,11);
    if (v.length>6) v=`(${v.substring(0,2)}) ${v.substring(2,7)}-${v.substring(7)}`;
    else if (v.length>2) v=`(${v.substring(0,2)}) ${v.substring(2)}`;
    e.target.value = v;
});
document.getElementById('cpf')?.addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g,'').substring(0,11);
    if (v.length>9) v=v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/,'$1.$2.$3-$4');
    else if (v.length>6) v=v.replace(/(\d{3})(\d{3})(\d+)/,'$1.$2.$3');
    else if (v.length>3) v=v.replace(/(\d{3})(\d+)/,'$1.$2');
    e.target.value = v;
});

// ── Helpers ──
function getDados() {
    return {
        nome:       document.getElementById('nome').value.trim(),
        empresa:    document.getElementById('empresa').value.trim(),
        email:      document.getElementById('email').value.trim(),
        whatsapp:   document.getElementById('whatsapp').value.trim(),
        cpf:        document.getElementById('cpf').value.trim(),
        senha:      IS_UPGRADE ? '' : document.getElementById('senha').value,
        plano:      PLANO,
        billing:    BILLING,
        is_upgrade: IS_UPGRADE,
        id_empresa: ID_EMPRESA,
        device_id:  window.MP_DEVICE_SESSION_ID || '',
    };
}

function validarDados() {
    let ok = true;
    const campos = IS_UPGRADE ? ['nome','email','cpf'] : ['nome','email','cpf','senha','confirmaSenha'];
    campos.forEach(id => {
        const el = document.getElementById(id);
        if (!el || el.type === 'hidden') return;
        if (!el.value.trim()) { el.classList.add('is-invalid'); ok = false; }
        else el.classList.remove('is-invalid');
    });
    if (!ok) { alert('Preencha todos os campos obrigatórios.'); return false; }
    if (!IS_UPGRADE) {
        if (document.getElementById('senha').value !== document.getElementById('confirmaSenha').value) {
            document.getElementById('confirmaSenha').classList.add('is-invalid');
            alert('As senhas não conferem.'); return false;
        }
    }
    if (document.getElementById('cpf').value.replace(/\D/g,'').length !== 11) {
        alert('CPF inválido. Digite os 11 dígitos.'); return false;
    }
    return true;
}

function showLoading(msg) {
    document.getElementById('loadingMsg').textContent = msg || 'Processando...';
    document.getElementById('loadingOverlay').classList.add('show');
}
function hideLoading() {
    document.getElementById('loadingOverlay').classList.remove('show');
}

let _redirectTimer = null;

function mostrarSucesso(titulo, msg, icone, redirectUrl) {
    document.getElementById('successTitle').textContent = titulo;
    document.getElementById('successMsg').textContent   = msg;
    document.getElementById('successIcon').textContent  = icone || '✅';
    const email = document.getElementById('email').value;
    document.getElementById('credEmail').textContent       = email;
    document.getElementById('credEmailRepeat').textContent = email;

    // Upgrade: oculta campo de senha (usuário já tem senha)
    const senhaInfo = document.getElementById('senhaInfo');
    if (senhaInfo) senhaInfo.style.display = IS_UPGRADE ? 'none' : '';

    // Atualiza botão de entrada
    const btnEntrar    = document.getElementById('btnEntrar');
    const btnEntrarTxt = document.getElementById('btnEntrarTxt');
    if (redirectUrl && btnEntrar) {
        btnEntrar.href = redirectUrl;
        if (btnEntrarTxt) btnEntrarTxt.textContent = IS_UPGRADE ? 'Ir para o painel' : 'Entrar na plataforma';

        // Countdown de 5s antes do redirect automático
        const countdown = document.getElementById('redirectCountdown');
        const cdTimer   = document.getElementById('cdTimer');
        if (countdown && cdTimer) {
            countdown.classList.remove('d-none');
            let s = 5;
            cdTimer.textContent = s;
            if (_redirectTimer) clearInterval(_redirectTimer);
            _redirectTimer = setInterval(() => {
                s--;
                cdTimer.textContent = s;
                if (s <= 0) {
                    clearInterval(_redirectTimer);
                    window.location.href = redirectUrl;
                }
            }, 1000);
        }
    }

    document.getElementById('successScreen').classList.remove('d-none');
    document.getElementById('step2').className = 'step done';
    document.getElementById('step3').className = 'step done';
    document.getElementById('step4').className = 'step active';
}

async function postCheckout(body) {
    const r = await fetch('index.php?acao=processar_checkout', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(body)
    });
    return r.json();
}

// ════════════════════════════════════════════════
// TRIAL
// ════════════════════════════════════════════════
async function ativarTrial() {
    if (!validarDados()) return;
    showLoading('Ativando trial...');
    try {
        const data = await postCheckout({ acao:'trial', ...getDados() });
        hideLoading();
        if (data.sucesso) {
            mostrarSucesso('🎁 Trial ativado! 30 dias grátis',
                'Explore tudo sem limites por 30 dias. Sem cobrança agora.', '🎁',
                IS_UPGRADE ? 'index.php?acao=painel' : 'index.php');
        } else {
            alert('Erro: ' + (data.mensagem || 'Tente novamente.'));
        }
    } catch(e) {
        hideLoading();
        alert('Erro de conexão. Verifique sua internet e tente novamente.');
    }
}

// ════════════════════════════════════════════════
// PIX
// ════════════════════════════════════════════════
let pixPaymentId = null;
let pixPollInterval = null;
let pixTimerInterval = null;
let pixSegundos = 1800;

async function gerarPix() {
    if (!validarDados()) return;
    showLoading('Gerando QR Code PIX...');
    try {
        const data = await postCheckout({ acao:'pix', ...getDados() });
        hideLoading();
        if (!data.sucesso) { alert('Erro: ' + (data.mensagem || 'Tente novamente.')); return; }

        pixPaymentId = data.payment_id;
        const qrContainer = document.getElementById('pixQrContainer');
        if (data.qr_code_base64) {
            qrContainer.innerHTML = `<img src="data:image/png;base64,${data.qr_code_base64}" alt="QR Code PIX" style="width:100%;height:100%;object-fit:contain;">`;
        } else {
            qrContainer.innerHTML = `<div style="font-size:.65rem;word-break:break-all;padding:8px;color:#003366;">${data.qr_code}</div>`;
        }
        document.getElementById('pixKey').textContent = data.qr_code || '—';
        document.getElementById('pix-inicio').classList.add('d-none');
        document.getElementById('pix-gerado').classList.remove('d-none');
        iniciarTimerPix();
        pixPollInterval = setInterval(verificarPagamentoPix, 5000);
    } catch(e) {
        hideLoading();
        alert('Erro ao gerar PIX. Verifique sua conexão e tente novamente.');
    }
}

function iniciarTimerPix() {
    pixTimerInterval = setInterval(() => {
        if (pixSegundos <= 0) {
            clearInterval(pixTimerInterval);
            clearInterval(pixPollInterval);
            document.getElementById('pixTimer').textContent = 'Expirado';
            document.getElementById('pixTimer').style.color = 'red';
            return;
        }
        pixSegundos--;
        const m = String(Math.floor(pixSegundos/60)).padStart(2,'0');
        const s = String(pixSegundos%60).padStart(2,'0');
        document.getElementById('pixTimer').textContent = `${m}:${s}`;
    }, 1000);
}

async function verificarPagamentoPix() {
    if (!pixPaymentId) return;
    try {
        const r = await fetch(`index.php?acao=verificar_pagamento&payment_id=${pixPaymentId}`);
        const data = await r.json();
        if (data.status === 'approved') {
            clearInterval(pixPollInterval);
            clearInterval(pixTimerInterval);
            mostrarSucesso('✅ PIX recebido!',
                'Pagamento confirmado. Seu acesso foi liberado imediatamente.', '✅',
                IS_UPGRADE ? 'index.php?acao=painel' : 'index.php');
        }
    } catch(e) { /* silencioso */ }
}

// ════════════════════════════════════════════════
// 3DS — autenticação bancária extra
// ════════════════════════════════════════════════
function handle3DSChallenge(paymentId, threeDsInfo) {
    document.getElementById('successIcon').textContent  = '🔐';
    document.getElementById('successTitle').textContent = 'Confirme no seu banco';
    document.getElementById('successMsg').textContent   =
        'Seu banco exige autenticação adicional. Verifique o app do seu banco, SMS ou e-mail e aprove a transação. Esta tela atualiza automaticamente.';
    const email = document.getElementById('email').value;
    document.getElementById('credEmail').textContent       = email;
    document.getElementById('credEmailRepeat').textContent = email;
    document.getElementById('successScreen').classList.remove('d-none');

    // Se o banco fornecer uma URL de desafio, abre em popup
    const challengeUrl = threeDsInfo?.external_resource_url;
    if (challengeUrl) {
        const popup = window.open(challengeUrl, '3ds_challenge', 'width=500,height=600,scrollbars=yes');
        if (!popup) {
            // Popup bloqueado → abre em nova aba
            const link = document.createElement('a');
            link.href = challengeUrl;
            link.target = '_blank';
            link.textContent = 'Clique aqui para autenticar no seu banco';
            link.style.cssText = 'display:block;margin:12px auto;text-align:center;color:#003366;font-weight:700;';
            document.getElementById('successMsg').after(link);
        }
    }

    // Polling aguarda confirmação bancária (até 5 minutos)
    iniciarPollingCartao(paymentId, 60);
}

// ════════════════════════════════════════════════
// POLLING PARA CARTÃO in_process / pending
// ════════════════════════════════════════════════
function iniciarPollingCartao(paymentId, maxTentativasOverride) {
    // Mostra tela de "em análise" se ainda não estiver visível
    if (document.getElementById('successScreen').classList.contains('d-none')) {
        document.getElementById('successIcon').textContent  = '⏳';
        document.getElementById('successTitle').textContent = 'Aguardando confirmação';
        document.getElementById('successMsg').textContent   = 'Pagamento em análise antifraude. Aguarde, isso costuma levar menos de 1 minuto...';
        const email = document.getElementById('email').value;
        document.getElementById('credEmail').textContent       = email;
        document.getElementById('credEmailRepeat').textContent = email;
        document.getElementById('successScreen').classList.remove('d-none');
    }

    let tentativas = 0;
    const maxTentativas = maxTentativasOverride ?? 24; // 2 min (24×5s) ou 5 min para 3DS (60×5s)
    const intervalo = setInterval(async () => {
        tentativas++;
        try {
            const r    = await fetch(`index.php?acao=verificar_pagamento&payment_id=${paymentId}`);
            const data = await r.json();

            if (data.status === 'approved') {
                clearInterval(intervalo);
                mostrarSucesso('💳 Pagamento aprovado!',
                    IS_UPGRADE ? 'Sua licença foi renovada com sucesso!' : 'Cartão aprovado. Acesso liberado.',
                    '💳',
                    IS_UPGRADE ? 'index.php?acao=painel' : 'index.php');

            } else if (data.status === 'rejected' || data.status === 'cancelled') {
                clearInterval(intervalo);
                document.getElementById('successScreen').classList.add('d-none');
                alert('❌ Pagamento recusado após análise. Tente outro cartão ou use PIX.');

            } else if (tentativas >= maxTentativas) {
                clearInterval(intervalo);
                document.getElementById('successMsg').textContent =
                    'Análise demorou mais que o esperado. Verifique seu e-mail — você receberá a confirmação assim que aprovado.';
            }
        } catch(_) { /* silencioso */ }
    }, 5000);
}

function copiarPix() {
    const key = document.getElementById('pixKey').textContent.trim();
    navigator.clipboard.writeText(key).then(() => {
        const btn = document.getElementById('btnCopy');
        btn.classList.add('copied');
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Copiado!';
        setTimeout(() => {
            btn.classList.remove('copied');
            btn.innerHTML = '<i class="bi bi-clipboard"></i> Copiar código PIX';
        }, 3000);
    });
}

// ════════════════════════════════════════════════
// CARTÃO — tokeniza com MP.js v2
// REGRA: ano sempre com 2 dígitos ("30", não "2030")
// identificationType/identificationNumber ficam NO SERVIDOR
// ════════════════════════════════════════════════
async function pagarCartao() {
    if (!validarDados()) return;

    const numero   = document.getElementById('cardNumber').value.replace(/\s/g,'');
    const nome     = document.getElementById('cardName').value.trim();
    const cvv      = document.getElementById('cardCVV').value.trim();
    const parcelas = document.getElementById('parcelas').value;

    if (!numero || !nome || !cvv) {
        alert('Preencha todos os dados do cartão.'); return;
    }

    // Extrai mês e ano — força SEMPRE 2 dígitos no ano
    const partes = document.getElementById('cardExp').value.split('/');
    const mes    = (partes[0] || '').trim().padStart(2, '0');
    const ano    = (partes[1] || '').trim().slice(-2); // "2030"→"30" | "30"→"30"

    if (mes.length !== 2 || mes === '00' || ano.length !== 2) {
        alert('Validade inválida. Use o formato MM/AA (ex: 11/30).'); return;
    }

    const btn = document.getElementById('btnPagarCartao');
    btn.disabled = true;
    showLoading('Tokenizando cartão com segurança...');

    try {
        // ─ MP.js gera o token seguro no navegador ─
        // O número do cartão NUNCA é enviado para o seu servidor
        const token = await mp.createCardToken({
            cardNumber:          numero,
            cardholderName:      nome,
            cardExpirationMonth: mes,  // "11"
            cardExpirationYear:  ano,  // "30"  ← 2 dígitos obrigatório no MP.js v2
            securityCode:        cvv,
        });

        if (!token || !token.id) {
            btn.disabled = false;
            hideLoading();
            const causa = token?.cause?.[0]?.description || 'Verifique os dados do cartão.';
            alert('Cartão não validado: ' + causa);
            return;
        }

        document.getElementById('loadingMsg').textContent = 'Processando pagamento...';

        // ─ Envia token + dados pessoais para o servidor ─
        const data = await postCheckout({
            acao:               'cartao',
            card_token:         token.id,   // apenas o token, nunca o número real
            parcelas:           parseInt(parcelas),
            payment_method_id:  detectedPaymentMethodId,  // "visa", "master", etc.
            issuer_id:          detectedIssuerId,
            ...getDados()
        });

        btn.disabled = false;
        hideLoading();

        if (data.sucesso && data.status === 'approved') {
            mostrarSucesso(
                IS_UPGRADE ? '🎉 Licença renovada!' : '💳 Pagamento aprovado!',
                IS_UPGRADE ? 'Seu plano foi atualizado com sucesso. Bem-vindo de volta!' : 'Cartão aprovado. Acesso liberado imediatamente.',
                IS_UPGRADE ? '🎉' : '💳',
                data.redirect || (IS_UPGRADE ? 'index.php?acao=painel' : 'index.php')
            );
        } else if (data.sucesso && data.status === 'pending_challenge') {
            // 3DS: banco exige autenticação do portador
            handle3DSChallenge(data.payment_id, data.three_ds_info);
        } else if (data.sucesso && ['in_process', 'pending', 'authorized'].includes(data.status)) {
            // Em análise antifraude — aguarda webhook/polling
            iniciarPollingCartao(data.payment_id);
        } else {
            // Rejeitado
            const msg = data.mensagem || 'Pagamento recusado. Verifique os dados do cartão.';
            alert('❌ ' + msg);
            console.error('[CHRONA] Cartão recusado:', data);
        }

    } catch(err) {
        btn.disabled = false;
        hideLoading();
        console.error('Erro MP:', err);
        // Trata mensagem de erro do MP de forma amigável
        const mpMsg = err?.message || '';
        if (mpMsg.includes('token')) {
            alert('Erro de autenticação com o Mercado Pago. Verifique se as credenciais no config/mercadopago.php (Public Key e Access Token) pertencem ao mesmo app.');
        } else {
            alert('Erro ao processar cartão: ' + (mpMsg || 'Tente novamente.'));
        }
    }
}

// ════════════════════════════════════════════════
// BOLETO
// ════════════════════════════════════════════════
async function gerarBoleto() {
    if (!validarDados()) return;
    showLoading('Gerando boleto...');
    try {
        const data = await postCheckout({ acao:'boleto', ...getDados() });
        hideLoading();
        if (!data.sucesso) { alert('Erro: ' + (data.mensagem || 'Tente novamente.')); return; }
        if (data.barcode) {
            document.getElementById('boletoBarcode').textContent = data.barcode;
            document.getElementById('boletoBarcode').classList.remove('d-none');
        }
        if (data.boleto_url) {
            document.getElementById('boletoUrl').href = data.boleto_url;
            document.getElementById('boletoLinks').classList.remove('d-none');
        }
        document.getElementById('btnGerarBoleto').classList.add('d-none');
        mostrarSucesso('📄 Boleto gerado!',
            'Pague até o vencimento. Acesso liberado em até 2 dias úteis após o pagamento.', '📄');
    } catch(e) {
        hideLoading();
        alert('Erro ao gerar boleto. Tente novamente.');
    }
}

function copiarBoleto() {
    navigator.clipboard.writeText(document.getElementById('boletoBarcode').textContent.trim())
        .then(() => alert('Código de barras copiado!'));
}

// Mobile: remove sticky do sidebar
if (window.innerWidth < 768) {
    const sb = document.querySelector('.card-box[style*="sticky"]');
    if (sb) sb.style.position = 'static';
}
</script>
</body>
</html>