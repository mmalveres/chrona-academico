<?php
if (!class_exists('Aplicacao\\Helpers\\PrecosHelper')) {
    require_once __DIR__ . '/../autoload.php';
}
use Aplicacao\Helpers\PrecosHelper;
$_precos = PrecosHelper::getPrecos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos — CHRONA Gestão de Obras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy:   #003366;
            --gold:   #FFB300;
            --light:  #F4F7FE;
            --dark:   #0d1b2a;
            --text:   #2B3674;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito Sans', sans-serif;
            background: var(--dark);
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── FUNDO ANIMADO ── */
        .bg-grid {
            position: fixed; inset: 0; z-index: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 60px 60px;
        }
        .bg-glow {
            position: fixed; z-index: 0;
            border-radius: 50%; filter: blur(100px); pointer-events: none;
        }
        .glow-1 { width: 500px; height: 500px; top: -150px; left: -100px; background: rgba(0,51,102,.6); }
        .glow-2 { width: 400px; height: 400px; bottom: -100px; right: -100px; background: rgba(255,179,0,.15); }

        .content { position: relative; z-index: 1; }

        /* ── HEADER ── */
        header {
            padding: 28px 40px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }
        .logo {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900; font-size: 1.5rem; color: #fff; text-decoration: none;
            display: flex; align-items: center; gap: 10px;
        }
        .logo-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--gold); display: inline-block; }

        /* ── HERO ── */
        .hero { text-align: center; padding: 70px 20px 50px; }
        .hero-tag {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,179,0,.12); border: 1px solid rgba(255,179,0,.3);
            color: var(--gold); border-radius: 50px;
            padding: 6px 18px; font-size: .8rem; font-weight: 700;
            letter-spacing: .5px; text-transform: uppercase; margin-bottom: 24px;
        }
        .hero h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900; font-size: clamp(2rem, 5vw, 3.5rem);
            line-height: 1.1; margin-bottom: 18px;
        }
        .hero h1 span { color: var(--gold); }
        .hero p { color: rgba(255,255,255,.6); font-size: 1.1rem; max-width: 520px; margin: 0 auto; }

        /* ── TOGGLE MENSAL/ANUAL ── */
        .billing-toggle {
            display: flex; align-items: center; justify-content: center;
            gap: 14px; margin: 36px auto 0;
        }
        .toggle-label { font-weight: 700; color: rgba(255,255,255,.5); font-size: .9rem; }
        .toggle-label.active { color: #fff; }
        .toggle-switch {
            position: relative; width: 52px; height: 28px; cursor: pointer;
        }
        .toggle-switch input { display: none; }
        .toggle-track {
            width: 100%; height: 100%; background: rgba(255,255,255,.15);
            border-radius: 50px; transition: background .3s;
        }
        .toggle-thumb {
            position: absolute; top: 4px; left: 4px;
            width: 20px; height: 20px; border-radius: 50%;
            background: var(--gold); transition: transform .3s;
        }
        .toggle-switch input:checked ~ .toggle-track { background: rgba(255,179,0,.3); }
        .toggle-switch input:checked ~ .toggle-thumb { transform: translateX(24px); }
        .badge-save {
            background: #10B981; color: #fff; font-size: .65rem;
            font-weight: 800; padding: 3px 8px; border-radius: 20px;
        }

        /* ── CARDS DE PLANO ── */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            max-width: 1050px; margin: 0 auto;
            padding: 50px 20px 80px;
        }

        .plan-card {
            border-radius: 24px;
            padding: 36px 32px;
            position: relative;
            transition: transform .25s, box-shadow .25s;
            cursor: default;
        }
        .plan-card:hover { transform: translateY(-6px); }

        /* Autônomo */
        .plan-autonomo {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
        }

        /* Construtora — destaque */
        .plan-construtora {
            background: linear-gradient(145deg, rgba(0,51,102,.95), rgba(0,80,160,.6));
            border: 2px solid rgba(255,179,0,.5);
            box-shadow: 0 0 60px rgba(255,179,0,.12);
        }

        /* Empresarial */
        .plan-empresarial {
            background: linear-gradient(145deg, rgba(20,20,40,.9), rgba(10,10,25,.95));
            border: 1px solid rgba(255,179,0,.2);
        }

        /* Badge "Mais Vendido" */
        .badge-popular {
            position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
            background: var(--gold); color: #003366;
            font-family: 'Montserrat', sans-serif;
            font-size: .7rem; font-weight: 900;
            padding: 5px 18px; border-radius: 50px;
            letter-spacing: .5px; text-transform: uppercase;
            white-space: nowrap;
        }

        .plan-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800; font-size: 1.3rem; margin-bottom: 4px;
        }
        .plan-desc { color: rgba(255,255,255,.5); font-size: .85rem; margin-bottom: 28px; }

        .plan-price {
            display: flex; align-items: flex-end; gap: 4px; margin-bottom: 6px;
        }
        .price-currency { font-size: 1.1rem; font-weight: 700; opacity: .7; margin-bottom: 8px; }
        .price-value {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900; font-size: 3.5rem; line-height: 1;
        }
        .price-period { color: rgba(255,255,255,.4); font-size: .85rem; margin-bottom: 8px; }
        .price-annual { font-size: .78rem; color: #10B981; font-weight: 700; margin-bottom: 28px; min-height: 20px; }

        .divider { height: 1px; background: rgba(255,255,255,.08); margin-bottom: 24px; }

        .feature-list { list-style: none; padding: 0; margin-bottom: 32px; }
        .feature-list li {
            display: flex; align-items: center; gap: 10px;
            font-size: .9rem; padding: 7px 0;
            color: rgba(255,255,255,.8);
        }
        .feature-list li .fi { font-size: 1rem; flex-shrink: 0; }
        .fi-check { color: #10B981; }
        .fi-x     { color: rgba(255,255,255,.2); }
        .fi-wp    { color: #25D366; }
        .feature-list li.locked { color: rgba(255,255,255,.3); }

        /* Botões CTA */
        .btn-cta {
            display: block; width: 100%; padding: 15px;
            border-radius: 14px; font-family: 'Montserrat', sans-serif;
            font-weight: 800; font-size: .95rem; text-align: center;
            text-decoration: none; border: none; cursor: pointer;
            transition: all .2s; letter-spacing: .3px;
        }
        .btn-cta:hover { transform: translateY(-2px); filter: brightness(1.1); }

        .btn-autonomo {
            background: rgba(255,255,255,.1); color: #fff;
            border: 1px solid rgba(255,255,255,.2);
        }
        .btn-construtora {
            background: var(--gold); color: #003366;
        }
        .btn-empresarial {
            background: linear-gradient(135deg, #FFB300, #FF6B00);
            color: #fff;
        }

        /* ── GARANTIA ── */
        .garantia {
            text-align: center; padding: 0 20px 60px;
        }
        .garantia-box {
            display: inline-flex; align-items: center; gap: 16px;
            background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px; padding: 20px 32px; max-width: 500px;
        }
        .garantia-box i { font-size: 2rem; color: var(--gold); flex-shrink: 0; }
        .garantia-box p { text-align: left; color: rgba(255,255,255,.6); font-size: .85rem; margin: 0; }
        .garantia-box strong { color: #fff; display: block; margin-bottom: 4px; }

        /* ── FAQ ── */
        .faq-section { max-width: 680px; margin: 0 auto; padding: 0 20px 80px; }
        .faq-section h2 {
            font-family: 'Montserrat', sans-serif; font-weight: 800;
            text-align: center; margin-bottom: 36px; font-size: 1.6rem;
        }
        .faq-item {
            border-bottom: 1px solid rgba(255,255,255,.08); padding: 18px 0; cursor: pointer;
        }
        .faq-question {
            display: flex; justify-content: space-between; align-items: center;
            font-weight: 700; font-size: .95rem;
        }
        .faq-question i { transition: transform .3s; color: var(--gold); }
        .faq-answer {
            color: rgba(255,255,255,.5); font-size: .875rem;
            line-height: 1.7; max-height: 0; overflow: hidden; transition: max-height .3s, padding .3s;
        }
        .faq-item.open .faq-question i { transform: rotate(45deg); }
        .faq-item.open .faq-answer { max-height: 200px; padding-top: 12px; }

        /* ── FOOTER ── */
        footer {
            text-align: center; padding: 30px 20px;
            border-top: 1px solid rgba(255,255,255,.07);
            color: rgba(255,255,255,.25); font-size: .8rem;
        }
        footer a { color: var(--gold); text-decoration: none; }

        /* ── MODAL CONTATO ── */
        .modal-content {
            background: #0f1e33; border: 1px solid rgba(255,255,255,.1);
            border-radius: 20px; color: #fff;
        }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,.08); }
        .modal-footer { border-top: 1px solid rgba(255,255,255,.08); }
        .form-control, .form-select {
            background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
            color: #fff; border-radius: 10px;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,.1); border-color: var(--gold);
            color: #fff; box-shadow: 0 0 0 3px rgba(255,179,0,.15);
        }
        .form-control::placeholder { color: rgba(255,255,255,.3); }
        .form-label { font-weight: 700; font-size: .85rem; color: rgba(255,255,255,.7); }

        /* ── MOBILE ── */
        @media (max-width: 768px) {
            header { padding: 20px; }
            .hero { padding: 50px 20px 30px; }
            .plans-grid { padding: 30px 16px 60px; gap: 16px; }
            .plan-card { padding: 28px 22px; }
        }
    </style>
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow glow-1"></div>
<div class="bg-glow glow-2"></div>

<div class="content">

    <!-- HEADER -->
    <header>
        <a href="index.php?acao=painel" class="logo">
            <span class="logo-dot"></span> CHRONA
        </a>
        <a href="index.php?acao=painel" class="btn btn-outline-light btn-sm px-4" style="border-radius: 50px; font-weight: 700;">
            <i class="bi bi-arrow-left me-1"></i> Voltar ao Sistema
        </a>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-tag">
            <i class="bi bi-lightning-charge-fill"></i> Escolha seu plano
        </div>
        <h1>Gestão de obras<br><span>sem complicação</span></h1>
        <p>Do engenheiro autônomo à grande construtora, temos o plano certo para o seu negócio crescer.</p>

        <div class="billing-toggle">
            <span class="toggle-label active" id="labelMensal">Mensal</span>
            <label class="toggle-switch">
                <input type="checkbox" id="toggleAnual" onchange="alternarBilling()">
                <div class="toggle-track"></div>
                <div class="toggle-thumb"></div>
            </label>
            <span class="toggle-label" id="labelAnual">Anual <span class="badge-save">-20%</span></span>
        </div>
    </section>

    <!-- PLANOS -->
    <section class="plans-grid">

        <!-- AUTÔNOMO -->
        <div class="plan-card plan-autonomo">
            <div class="plan-name">Autônomo</div>
            <div class="plan-desc">Para engenheiros independentes</div>

            <div class="plan-price">
                <span class="price-currency">R$</span>
                <span class="price-value" id="preco-autonomo"><?= (int)$_precos['autonomo']['mensal'] ?></span>
            </div>
            <div class="price-period">por mês</div>
            <div class="price-annual" id="annual-autonomo"></div>

            <div class="divider"></div>

            <ul class="feature-list">
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> 1 Usuário Gestor</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> 1 Obra Ativa</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Importação de Orçamento</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> RDO Ilimitado</li>
                <li class="locked"><i class="bi bi-x-circle fi fi-x"></i> Cronograma Físico-Financeiro</li>
                <li class="locked"><i class="bi bi-x-circle fi fi-x"></i> Medição Automática</li>
            </ul>

            <a href="#" class="btn-cta btn-autonomo" onclick="irCheckout('autonomo'); return false;">
                Começar Agora
            </a>
        </div>

        <!-- CONSTRUTORA -->
        <div class="plan-card plan-construtora">
            <div class="badge-popular">⭐ Mais Vendido</div>

            <div class="plan-name">Construtora</div>
            <div class="plan-desc">Para pequenas e médias empresas</div>

            <div class="plan-price">
                <span class="price-currency">R$</span>
                <span class="price-value" id="preco-construtora"><?= (int)$_precos['construtora']['mensal'] ?></span>
            </div>
            <div class="price-period">por mês</div>
            <div class="price-annual" id="annual-construtora"></div>

            <div class="divider"></div>

            <ul class="feature-list">
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> 3 Usuários</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Até 3 Obras Ativas</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Medição Automática</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Cronograma Físico-Financeiro</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Importação de Orçamento</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Compressão de Imagens</li>
            </ul>

            <a href="#" class="btn-cta btn-construtora" onclick="irCheckout('construtora'); return false;">
                Começar Agora
            </a>
        </div>

        <!-- EMPRESARIAL -->
        <div class="plan-card plan-empresarial">
            <div class="plan-name" style="color: var(--gold);">Empresarial</div>
            <div class="plan-desc">Gestão total sem limites</div>

            <div class="plan-price">
                <span class="price-currency">R$</span>
                <span class="price-value" id="preco-empresarial"><?= (int)$_precos['empresarial']['mensal'] ?></span>
            </div>
            <div class="price-period">por mês</div>
            <div class="price-annual" id="annual-empresarial"></div>

            <div class="divider"></div>

            <ul class="feature-list">
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Usuários Ilimitados</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Obras Ilimitadas</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Todas as Funcionalidades</li>
                <li><i class="bi bi-check-circle-fill fi fi-check"></i> Relatórios Personalizados</li>
                <li><i class="bi bi-whatsapp fi fi-wp"></i> Suporte Prioritário (WhatsApp)</li>
            </ul>

            <a href="#" class="btn-cta btn-empresarial" onclick="irCheckout('empresarial'); return false;">
                Quero Ilimitado
            </a>
        </div>

    </section>

    <!-- GARANTIA -->
    <section class="garantia">
        <div class="garantia-box">
            <i class="bi bi-shield-check"></i>
            <p>
                <strong>Garantia de 7 dias</strong>
                Teste o sistema sem risco. Se não ficar satisfeito em 7 dias, devolvemos 100% do valor pago.
            </p>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq-section">
        <h2>Perguntas frequentes</h2>

        <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-question">
                Como funciona o pagamento? <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div class="faq-answer">
                O pagamento é realizado mensalmente via cartão de crédito, boleto ou PIX. Após a confirmação do pagamento, o acesso é liberado imediatamente. Para o plano anual, você paga o equivalente a 10 meses com 2 meses gratuitos.
            </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-question">
                Posso trocar de plano a qualquer momento? <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div class="faq-answer">
                Sim! Você pode fazer upgrade ou downgrade do plano a qualquer momento. No upgrade, o novo acesso é liberado imediatamente. No downgrade, a mudança entra em vigor no próximo ciclo de cobrança.
            </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-question">
                Meus dados ficam seguros? <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div class="faq-answer">
                Todos os dados são armazenados em servidores seguros com backup diário. Cada empresa tem seus dados completamente isolados. Você pode exportar todos os seus dados a qualquer momento.
            </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-question">
                O plano Autônomo tem limite de RDOs? <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div class="faq-answer">
                Não! O Diário de Obras (RDO) é ilimitado em todos os planos. O limite do plano Autônomo é de 1 obra ativa e 1 usuário gestor, mas você pode registrar quantos diários quiser.
            </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-question">
                Como entro em contato para contratar? <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div class="faq-answer">
                Clique em "Começar Agora" no plano desejado e preencha o formulário. Nossa equipe entrará em contato em até 24 horas para configurar seu acesso. Você também pode nos contatar diretamente pelo WhatsApp.
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>© <?= date('Y') ?> CHRONA — Gestão Inteligente de Obras</p>
        <p class="mt-1">
            <a href="index.php?acao=painel">Acessar o sistema</a> ·
            <a href="#">Termos de uso</a> ·
            <a href="#">Privacidade</a>
        </p>
    </footer>

</div>

<!-- MODAL DE CONTATO / INTERESSE -->
<div class="modal fade" id="modalContato" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="fw-bold mb-1" id="modalTitulo">Contratar Plano</h5>
                    <p class="text-muted small mb-0" id="modalSubtitulo"></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="formContato" onsubmit="enviarInteresse(event)">
                    <input type="hidden" id="planoSelecionado">

                    <div class="mb-3">
                        <label class="form-label">Nome completo</label>
                        <input type="text" class="form-control" id="nomeContato" placeholder="Seu nome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp</label>
                        <input type="tel" class="form-control" id="whatsContato" placeholder="(00) 00000-0000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="emailContato" placeholder="seu@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome da empresa</label>
                        <input type="text" class="form-control" id="empresaContato" placeholder="Sua construtora ou seu nome">
                    </div>

                    <!-- Alerta de sucesso (oculto) -->
                    <div id="sucessoContato" class="d-none">
                        <div class="text-center py-4">
                            <div style="font-size: 3rem;">✅</div>
                            <h5 class="fw-bold mt-3">Interesse registrado!</h5>
                            <p class="text-muted small">Nossa equipe vai entrar em contato em até 24h pelo WhatsApp ou e-mail informado.</p>
                        </div>
                    </div>

                    <div id="formFields">
                        <button type="submit" class="btn-cta btn-construtora mt-2" style="border-radius: 12px;">
                            <i class="bi bi-send-fill me-2"></i> Quero este plano
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Preços ──
    const precos = {
        mensal:  { autonomo: <?= $_precos['autonomo']['mensal'] ?>,    construtora: <?= $_precos['construtora']['mensal'] ?>, empresarial: <?= $_precos['empresarial']['mensal'] ?> },
        anual:   { autonomo: <?= $_precos['autonomo']['anual'] ?>,     construtora: <?= $_precos['construtora']['anual'] ?>,  empresarial: <?= $_precos['empresarial']['anual'] ?>  },
    };

    let modoAnual = false;

    function alternarBilling() {
        modoAnual = document.getElementById('toggleAnual').checked;
        const modo = modoAnual ? 'anual' : 'mensal';

        document.getElementById('labelMensal').classList.toggle('active', !modoAnual);
        document.getElementById('labelAnual').classList.toggle('active', modoAnual);

        ['autonomo','construtora','empresarial'].forEach(p => {
            document.getElementById('preco-' + p).textContent = precos[modo][p];
            const el = document.getElementById('annual-' + p);
            if (modoAnual) {
                const total = precos.anual[p] * 12;
                el.textContent = `R$ ${total}/ano — economize R$ ${(precos.mensal[p] - precos.anual[p]) * 12}`;
            } else {
                el.textContent = '';
            }
        });
    }

    // ── FAQ ──
    function toggleFaq(el) {
        const isOpen = el.classList.contains('open');
        document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
        if (!isOpen) el.classList.add('open');
    }

    // ── Checkout ──
    function irCheckout(plano) {
        const billing = modoAnual ? 'anual' : 'mensal';
        window.location.href = 'index.php?acao=checkout&plano=' + plano + '&billing=' + billing;
    }

    // ── Modal Contato (mantido caso seja necessário em outro contexto) ──
    function abrirContato(plano) {
        document.getElementById('planoSelecionado').value = plano;
        document.getElementById('modalTitulo').textContent = 'Contratar Plano ' + plano;

        const precoAtual = modoAnual ? precos.anual[plano.toLowerCase()] : precos.mensal[plano.toLowerCase()];
        document.getElementById('modalSubtitulo').textContent =
            `R$ ${precoAtual}/mês${modoAnual ? ' (cobrança anual)' : ''} · Ativação em até 24h`;

        // Reset form
        document.getElementById('formContato').reset();
        document.getElementById('sucessoContato').classList.add('d-none');
        document.getElementById('formFields').classList.remove('d-none');

        new bootstrap.Modal(document.getElementById('modalContato')).show();
    }

    function enviarInteresse(e) {
        e.preventDefault();

        const dados = {
            plano:   document.getElementById('planoSelecionado').value,
            nome:    document.getElementById('nomeContato').value,
            whats:   document.getElementById('whatsContato').value,
            email:   document.getElementById('emailContato').value,
            empresa: document.getElementById('empresaContato').value,
            billing: modoAnual ? 'anual' : 'mensal',
        };

        // ── AQUI: integre com seu backend, webhook, e-mail ou CRM ──
        // Exemplo simples: redireciona para WhatsApp com mensagem pré-preenchida
        const msg = encodeURIComponent(
            `Olá! Tenho interesse no plano *${dados.plano}* da CHRONA.\n` +
            `Nome: ${dados.nome}\nEmpresa: ${dados.empresa}\nE-mail: ${dados.email}`
        );

        // Mostra sucesso e abre WhatsApp
        document.getElementById('formFields').classList.add('d-none');
        document.getElementById('sucessoContato').classList.remove('d-none');

        // Substitua pelo seu número de WhatsApp
        setTimeout(() => {
            window.open(`https://wa.me/5561985303841?text=${msg}`, '_blank');
        }, 1200);
    }

    // ── Máscara WhatsApp ──
    document.addEventListener('DOMContentLoaded', function() {
        const wEl = document.getElementById('whatsContato');
        if (wEl) {
            wEl.addEventListener('input', function(e) {
                let v = e.target.value.replace(/\D/g,'').substring(0,11);
                if (v.length > 6) v = `(${v.substring(0,2)}) ${v.substring(2,7)}-${v.substring(7)}`;
                else if (v.length > 2) v = `(${v.substring(0,2)}) ${v.substring(2)}`;
                else if (v.length > 0) v = `(${v}`;
                e.target.value = v;
            });
        }
    });
</script>

</body>
</html>