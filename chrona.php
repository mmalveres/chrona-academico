<?php
$precMensalAut = '49';
$precMensalCon = '149';
$precMensalEmp = '250';

$_autoload = __DIR__ . '/autoload.php';
if (file_exists($_autoload)) {
    try {
        require_once $_autoload;
        $_p = \Aplicacao\Helpers\PrecosHelper::getPrecos();
        $precMensalAut = number_format($_p['autonomo']['mensal'],    0, ',', '.');
        $precMensalCon = number_format($_p['construtora']['mensal'], 0, ',', '.');
        $precMensalEmp = number_format($_p['empresarial']['mensal'], 0, ',', '.');
        unset($_p);
    } catch (\Throwable $_e) {}
}
unset($_autoload);

$urlBase   = 'https://engmarcosalveres.com.br/CHRONA';
$urlTrial  = $urlBase . '/publico/checkout.php?plano=construtora&billing=mensal&trial=1';
$urlLogin  = $urlBase . '/publico/index.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CHRONA — Sistema Completo de Gestão de Obras Digitais</title>
<meta name="description" content="Do diário ao cronograma, do financeiro ao almoxarifado — gerencie obras de ponta a ponta no CHRONA. 30 dias grátis, sem cartão.">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
:root {
  --ch:   #0066ff; --ch2: #00c9ff;
  --ch-g: linear-gradient(135deg,#0066ff,#00c9ff);
  --ch-l: #e8f0fe;
  --ok:   #10b981;
  --gold: #ffb300;
  --g50:#f9fafb;--g100:#f3f4f6;--g200:#e5e7eb;
  --g400:#9ca3af;--g500:#6b7280;--g600:#4b5563;
  --g700:#374151;--g800:#1f2937;--g900:#111827;
}
*{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;scroll-padding-top:76px;}
body{font-family:'Inter',sans-serif;color:var(--g700);background:#fff;overflow-x:hidden;}
h1,h2,h3,h4,h5{font-family:'Montserrat',sans-serif;font-weight:800;line-height:1.15;letter-spacing:-.02em;}

/* NAV */
.navbar{padding:.8rem 0;background:rgba(255,255,255,.97);backdrop-filter:blur(10px);
  box-shadow:0 2px 12px rgba(0,0,0,.07);position:fixed;top:0;width:100%;z-index:1000;}
.nb{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.2rem;
    color:var(--g900)!important;display:flex;align-items:center;gap:.5rem;}
.nb-logo{width:32px;height:32px;border-radius:8px;background:var(--ch-g);
         color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.85rem;}
.nav-link{font-weight:600;color:var(--g600)!important;border-radius:8px;padding:.4rem .8rem!important;transition:all .2s;}
.nav-link:hover{color:var(--g900)!important;background:var(--g100);}
.btn-trial{background:var(--ch-g);color:#fff!important;border-radius:9px;
           padding:.5rem 1.2rem!important;font-weight:700!important;transition:all .2s;}
.btn-trial:hover{transform:translateY(-1px);opacity:.9;}
.btn-login{border:1.5px solid var(--g200);color:var(--g700)!important;border-radius:9px;
           padding:.48rem 1rem!important;font-weight:600!important;transition:all .2s;}
.btn-login:hover{border-color:var(--ch);color:var(--ch)!important;}

/* HERO */
.hero{padding:130px 0 80px;background:linear-gradient(160deg,#060d1e 0%,#0a1628 55%,#061630 100%);
      position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;top:-120px;right:-120px;width:520px;height:520px;
  border-radius:50%;background:radial-gradient(circle,rgba(0,102,255,.22),transparent 70%);}
.hero::after{content:'';position:absolute;bottom:-100px;left:-80px;width:400px;height:400px;
  border-radius:50%;background:radial-gradient(circle,rgba(0,201,255,.1),transparent 70%);}
.hero-inner{position:relative;z-index:2;}
.chip{display:inline-flex;align-items:center;gap:5px;padding:.28rem .8rem;
      border-radius:50px;font-size:.72rem;font-weight:700;}
.chip-ch{background:rgba(0,102,255,.2);border:1px solid rgba(0,102,255,.4);color:#6ab3ff;}
.chip-ae{background:rgba(255,179,0,.15);border:1px solid rgba(255,179,0,.35);color:#ffd166;}
.hero h1{font-size:clamp(2.2rem,5vw,3.7rem);color:#fff;margin-bottom:1rem;}
.hl{background:var(--ch-g);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.hero-sub{font-size:1.05rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:1.8rem;max-width:560px;}
.btn-cta{background:var(--ch-g);color:#fff;padding:1rem 2rem;border-radius:12px;
         font-weight:700;font-size:1rem;text-decoration:none;display:inline-flex;
         align-items:center;gap:.55rem;transition:all .25s;border:none;}
.btn-cta:hover{color:#fff;transform:translateY(-2px);box-shadow:0 12px 28px rgba(0,102,255,.45);}
.btn-out{border:2px solid rgba(255,255,255,.25);color:rgba(255,255,255,.85);padding:1rem 1.6rem;
         border-radius:12px;font-weight:700;font-size:1rem;text-decoration:none;
         display:inline-flex;align-items:center;gap:.55rem;transition:all .25s;}
.btn-out:hover{border-color:#fff;color:#fff;background:rgba(255,255,255,.06);}
.stat-n{font-family:'Montserrat',sans-serif;font-size:1.8rem;font-weight:900;color:#fff;}
.stat-l{font-size:.74rem;color:rgba(255,255,255,.38);margin-top:2px;}

/* MÓDULO MOCKUP HERO */
.mock-wrap{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);
           border-radius:18px;padding:1.2rem;position:relative;}
.mock-hdr{display:flex;align-items:center;gap:.5rem;padding-bottom:10px;
          border-bottom:1px solid rgba(255,255,255,.07);margin-bottom:10px;}
.mock-dot{width:6px;height:6px;border-radius:50%;}
.m-r{background:#ff5f56;} .m-y{background:#ffbd2d;} .m-g{background:#27c940;}
.mock-title{font-family:'Montserrat',sans-serif;font-size:.78rem;font-weight:800;color:rgba(255,255,255,.6);}
.mock-row{display:flex;align-items:center;gap:.5rem;padding:.55rem .7rem;border-radius:8px;
          background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);margin-bottom:.4rem;}
.mock-icon{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;
           justify-content:center;font-size:.75rem;flex-shrink:0;}
.mock-label{font-size:.7rem;font-weight:700;color:rgba(255,255,255,.75);}
.mock-val{font-size:.62rem;color:rgba(255,255,255,.35);margin-top:1px;}
.mock-badge{font-size:.6rem;font-weight:700;padding:2px 7px;border-radius:20px;margin-left:auto;white-space:nowrap;}
.bg-ok-s{background:rgba(16,185,129,.2);color:#4fd88a;}
.bg-ch-s{background:rgba(0,102,255,.2);color:#6ab3ff;}
.bg-yw-s{background:rgba(255,179,0,.2);color:#ffd166;}
.bg-rd-s{background:rgba(239,68,68,.2);color:#fca5a5;}
.prog-mini{height:3px;background:rgba(255,255,255,.07);border-radius:2px;margin-top:5px;}
.prog-fill{height:100%;border-radius:2px;background:var(--ch-g);}

/* SEÇÕES */
.sec-tag{display:inline-block;font-size:.7rem;font-weight:700;letter-spacing:1.3px;
         text-transform:uppercase;padding:.3rem .9rem;border-radius:50px;margin-bottom:.9rem;}
.st-ch{background:var(--ch-l);color:var(--ch);}
.st-n{background:var(--g100);color:var(--g600);}
.sec-h{font-size:clamp(1.7rem,3.5vw,2.6rem);color:var(--g900);margin-bottom:.75rem;}
.sec-sub{font-size:1rem;color:var(--g500);line-height:1.8;}

/* VALUE PROPS */
.vp-sec{background:var(--g50);padding:80px 0;}
.vp-card{background:#fff;border-radius:16px;padding:2rem;border:1px solid var(--g200);
         height:100%;transition:all .3s;text-align:center;}
.vp-card:hover{border-color:rgba(0,102,255,.25);box-shadow:0 12px 32px rgba(0,102,255,.08);transform:translateY(-4px);}
.vp-ico{width:60px;height:60px;border-radius:15px;background:var(--ch-g);
        color:#fff;display:flex;align-items:center;justify-content:center;
        font-size:1.5rem;margin:0 auto 1.2rem;}

/* MÓDULOS */
.mod-sec{padding:90px 0;}
.mod-card{border-radius:16px;padding:1.8rem;height:100%;border:1px solid var(--g200);
          background:#fff;transition:all .3s;position:relative;overflow:hidden;}
.mod-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--ch-g);}
.mod-card:hover{border-color:rgba(0,102,255,.2);box-shadow:0 10px 28px rgba(0,102,255,.08);transform:translateY(-4px);}
.mod-ico{width:48px;height:48px;border-radius:12px;background:var(--ch-l);
         color:var(--ch);display:flex;align-items:center;justify-content:center;
         font-size:1.25rem;margin-bottom:1rem;}
.mod-tag{font-size:.62rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;
         color:var(--ch);margin-bottom:.4rem;display:block;}
.mod-card h4{font-size:1.05rem;color:var(--g900);margin-bottom:.5rem;}
.mod-card p{font-size:.85rem;color:var(--g500);line-height:1.7;margin:0;}
.mod-list{list-style:none;padding:0;margin:.9rem 0 0;}
.mod-list li{font-size:.8rem;color:var(--g600);display:flex;align-items:flex-start;
             gap:.4rem;margin-bottom:.35rem;}
.mod-list li i{color:var(--ok);font-size:.75rem;margin-top:3px;flex-shrink:0;}

/* AERONÁUTICA */
.ae-sec{background:linear-gradient(135deg,#060d1e,#0a1628);padding:80px 0;}

/* COMO FUNCIONA */
.how-sec{background:var(--g50);padding:90px 0;}
.step{text-align:center;position:relative;}
.step-n{width:52px;height:52px;border-radius:50%;background:var(--ch-g);color:#fff;
        display:flex;align-items:center;justify-content:center;font-family:'Montserrat',sans-serif;
        font-weight:900;font-size:1.15rem;margin:0 auto 1.1rem;}
.step-arrow{position:absolute;top:26px;right:-28px;font-size:1.4rem;color:var(--g200);}

/* PLANOS */
.plan-sec{padding:90px 0;}
.plano{background:#fff;border-radius:16px;padding:2rem;border:1.5px solid var(--g200);height:100%;transition:all .3s;}
.plano:hover{transform:translateY(-4px);box-shadow:0 14px 36px rgba(0,0,0,.08);}
.plano.pop{border-color:var(--ch);box-shadow:0 8px 28px rgba(0,102,255,.14);}
.pop-tag{background:var(--ch-g);color:#fff;font-size:.66rem;font-weight:700;
         padding:.28rem .9rem;border-radius:50px;display:inline-block;margin-bottom:.8rem;}
.plano-p{font-family:'Montserrat',sans-serif;font-size:2.6rem;font-weight:900;
         color:var(--g900);margin:.5rem 0 .2rem;line-height:1;}
.plano-p.ch{color:var(--ch);}
.feats{list-style:none;padding:0;margin:.9rem 0 1.3rem;}
.feats li{display:flex;align-items:flex-start;gap:.45rem;font-size:.85rem;margin-bottom:.55rem;}
.feats li i.ok{color:var(--ok);flex-shrink:0;margin-top:2px;}
.feats li i.no{color:var(--g300);flex-shrink:0;margin-top:2px;}
.no-t{color:var(--g400);}
.btn-ch-full{display:block;text-align:center;padding:.9rem;border-radius:10px;
             background:var(--ch-g);color:#fff;font-weight:700;font-size:.9rem;
             text-decoration:none;transition:all .2s;}
.btn-ch-full:hover{color:#fff;transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,102,255,.3);}
.btn-out-full{display:block;text-align:center;padding:.88rem;border-radius:10px;
              border:1.5px solid var(--g200);color:var(--g700);font-weight:700;
              font-size:.9rem;text-decoration:none;transition:all .2s;}
.btn-out-full:hover{border-color:var(--ch);color:var(--ch);}

/* FAQ */
.faq-sec{background:var(--g50);padding:90px 0;}
.acc-item{background:#fff;border:1px solid var(--g200)!important;border-radius:12px!important;
          margin-bottom:.6rem;overflow:hidden;}
.acc-btn{font-weight:600;font-size:.92rem;color:var(--g900)!important;background:#fff!important;
         border:none!important;padding:1.1rem 1.3rem!important;box-shadow:none!important;}
.acc-btn:not(.collapsed){color:var(--ch)!important;}
.acc-body{font-size:.875rem;color:var(--g600);line-height:1.75;padding:.5rem 1.3rem 1.2rem;}

/* DEPOIMENTOS */
.dep-sec{padding:90px 0;}
.dep-card{background:#fff;border-radius:14px;padding:1.7rem;border:1px solid var(--g200);
          height:100%;transition:all .3s;position:relative;}
.dep-card:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(0,0,0,.07);}
.dep-card::before{content:'"';position:absolute;top:.6rem;right:1rem;font-size:3rem;
                  color:var(--g100);font-family:Georgia,serif;line-height:1;}
.stars{color:var(--gold);font-size:.82rem;letter-spacing:2px;margin-bottom:.55rem;}
.dep-t{font-size:.875rem;color:var(--g700);line-height:1.72;margin-bottom:1rem;}
.dep-auth{display:flex;align-items:center;gap:.7rem;}
.dep-av{width:38px;height:38px;border-radius:50%;background:var(--ch-g);
        color:#fff;display:flex;align-items:center;justify-content:center;
        font-family:'Montserrat',sans-serif;font-weight:900;font-size:.8rem;flex-shrink:0;}
.dep-n{font-weight:700;font-size:.875rem;color:var(--g900);}
.dep-r{font-size:.72rem;color:var(--g400);}

/* CTA FINAL */
.cta-sec{background:linear-gradient(160deg,#060d1e,#0a1628);padding:100px 0;position:relative;overflow:hidden;}
.cta-sec::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 60% 50%,rgba(0,102,255,.18),transparent 65%);}
.cta-inner{position:relative;z-index:1;}
.trial-badge{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);
             border-radius:10px;padding:.75rem 1.2rem;display:inline-flex;
             align-items:center;gap:.6rem;margin-bottom:1.5rem;}
.trial-badge i{color:var(--ok);font-size:1.1rem;}
.trial-badge span{font-size:.85rem;font-weight:600;color:rgba(255,255,255,.85);}

/* FOOTER */
footer{background:#05080f;padding:3rem 0 1.5rem;}
.flink{color:var(--g500);font-size:.84rem;text-decoration:none;display:block;
       margin-bottom:.45rem;transition:color .2s;}
.flink:hover{color:#fff;}
.fh{color:#fff;font-family:'Montserrat',sans-serif;font-size:.82rem;font-weight:700;margin-bottom:.8rem;}
.fsoc{width:33px;height:33px;border-radius:7px;background:rgba(255,255,255,.06);
      color:rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;
      text-decoration:none;font-size:.9rem;transition:all .2s;}
.fsoc:hover{background:rgba(255,255,255,.13);color:#fff;}

@media(max-width:768px){
  .step-arrow{display:none;}
  .hero{padding:110px 0 60px;}
}
@media(max-width:576px){
  .hero h1{font-size:2.1rem;}
  .btn-cta,.btn-out{width:100%;justify-content:center;}
}
</style>
</head>
<body>

<!-- NAV -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand nb" href="<?= $urlBase ?>">
      <div class="nb-logo">CH</div>CHRONA
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li><a class="nav-link" href="#modulos">Módulos</a></li>
        <li><a class="nav-link" href="#planos">Planos</a></li>
        <li><a class="nav-link" href="#faq">FAQ</a></li>
        <li><a class="nav-link" href="https://engmarcosalveres.com.br/">← Site principal</a></li>
        <li class="ms-1"><a href="<?= $urlLogin ?>" class="btn-login nav-link"><i class="bi bi-box-arrow-in-right me-1"></i>Entrar</a></li>
        <li><a href="<?= $urlTrial ?>" class="btn-trial nav-link"><i class="bi bi-lightning-charge-fill me-1"></i>30 dias grátis</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="container hero-inner">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="chip chip-ch"><i class="bi bi-buildings"></i>Sistema de Gestão de Obras</span>
          <span class="chip chip-ae"><i class="bi bi-shield-fill-check"></i>Utilizado pela Aeronáutica</span>
        </div>
        <h1>Gestão de obra <span class="hl">completa,</span><br>do diário ao financeiro.</h1>
        <p class="hero-sub">9 módulos integrados para engenheiros e construtoras que precisam de controle real: diário, cronograma, medição, curva S, financeiro, almoxarifado, segurança e qualidade — tudo em um único sistema.</p>
        <div class="d-flex flex-wrap gap-3 mb-4">
          <a href="<?= $urlTrial ?>" class="btn-cta">
            <i class="bi bi-lightning-charge-fill"></i>Começar 30 dias grátis
          </a>
          <a href="#modulos" class="btn-out">
            <i class="bi bi-grid-3x3-gap-fill"></i>Ver todos os módulos
          </a>
        </div>
        <div style="font-size:.78rem;color:rgba(255,255,255,.35);margin-bottom:1.8rem;">
          <i class="bi bi-check-circle-fill me-1" style="color:var(--ok);"></i>Sem cartão de crédito &nbsp;·&nbsp;
          <i class="bi bi-check-circle-fill me-1" style="color:var(--ok);"></i>Acesso total imediato &nbsp;·&nbsp;
          <i class="bi bi-check-circle-fill me-1" style="color:var(--ok);"></i>Cancele quando quiser
        </div>
        <div class="d-flex gap-4 flex-wrap pt-3" style="border-top:1px solid rgba(255,255,255,.08);">
          <div><div class="stat-n">9</div><div class="stat-l">Módulos integrados</div></div>
          <div><div class="stat-n">30 dias</div><div class="stat-l">Trial completamente grátis</div></div>
          <div><div class="stat-n">FAB</div><div class="stat-l">Aeronáutica Brasileira</div></div>
          <div><div class="stat-n">PDF</div><div class="stat-l">Relatórios automáticos</div></div>
        </div>
      </div>

      <div class="col-lg-6 d-none d-lg-block">
        <div class="mock-wrap">
          <div class="mock-hdr">
            <div class="mock-dot m-r"></div><div class="mock-dot m-y"></div><div class="mock-dot m-g"></div>
            <span class="mock-title ms-2">CHRONA · Painel de Obras</span>
          </div>

          <div class="mock-row">
            <div class="mock-icon" style="background:rgba(0,102,255,.15);color:#6ab3ff;"><i class="bi bi-journal-text"></i></div>
            <div class="flex-fill">
              <div class="mock-label">RDO #052 — Residência Samambaia</div>
              <div class="mock-val">Hoje · Cléber Neto · Mão de obra: 8 funcionários</div>
              <div class="prog-mini"><div class="prog-fill" style="width:78%;"></div></div>
            </div>
            <span class="mock-badge bg-ok-s"><i class="bi bi-pen-fill me-1"></i>Assinado</span>
          </div>

          <div class="mock-row">
            <div class="mock-icon" style="background:rgba(16,185,129,.12);color:#4fd88a;"><i class="bi bi-bar-chart-steps"></i></div>
            <div class="flex-fill">
              <div class="mock-label">Cronograma — Estrutura (Mês 3 de 8)</div>
              <div class="mock-val">Planejado 62% · Realizado 68% · <span style="color:#4fd88a;">+6% adiantado</span></div>
              <div class="prog-mini"><div class="prog-fill" style="width:68%;background:linear-gradient(90deg,#10b981,#34d399);"></div></div>
            </div>
            <span class="mock-badge bg-ok-s">Em dia</span>
          </div>

          <div class="mock-row">
            <div class="mock-icon" style="background:rgba(255,179,0,.12);color:#ffd166;"><i class="bi bi-wallet2"></i></div>
            <div class="flex-fill">
              <div class="mock-label">Financeiro · Julho/2026</div>
              <div class="mock-val">Receitas R$ 48.200 · Despesas R$ 31.700 · Saldo R$ 16.500</div>
            </div>
            <span class="mock-badge bg-yw-s">+R$ 16,5k</span>
          </div>

          <div class="mock-row">
            <div class="mock-icon" style="background:rgba(0,102,255,.12);color:#6ab3ff;"><i class="bi bi-boxes"></i></div>
            <div class="flex-fill">
              <div class="mock-label">Almoxarifado · Cimento CP-V</div>
              <div class="mock-val">Entrada: 120 sc · Saída: 88 sc · Saldo: 32 sc</div>
            </div>
            <span class="mock-badge bg-ch-s">32 sc</span>
          </div>

          <div class="mock-row">
            <div class="mock-icon" style="background:rgba(239,68,68,.12);color:#fca5a5;"><i class="bi bi-exclamation-octagon"></i></div>
            <div class="flex-fill">
              <div class="mock-label">NC #003 — Fissura em pilar P7</div>
              <div class="mock-val">Prioridade Alta · Resp. Cléber · Prazo 15/07</div>
            </div>
            <span class="mock-badge bg-rd-s">Aberta</span>
          </div>

          <div class="mock-row">
            <div class="mock-icon" style="background:rgba(16,185,129,.12);color:#4fd88a;"><i class="bi bi-shield-check"></i></div>
            <div class="flex-fill">
              <div class="mock-label">DDS — Trabalho em altura · NR-35</div>
              <div class="mock-val">8 presentes · Conduzido por Cléber Neto</div>
            </div>
            <span class="mock-badge bg-ok-s">Concluído</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- VALUE PROPS -->
<section class="vp-sec">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-tag st-ch">Por que o CHRONA?</span>
      <h2 class="sec-h">Feito por engenheiro,<br>para quem está na obra</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="vp-card">
          <div class="vp-ico"><i class="bi bi-puzzle-fill"></i></div>
          <h5 style="font-size:1rem;margin-bottom:.5rem;">Tudo integrado</h5>
          <p style="font-size:.85rem;color:var(--g500);line-height:1.7;">Importe o orçamento uma vez e o sistema conecta automaticamente ao cronograma, à curva S, às medições e ao financeiro. Nenhum retrabalho de digitação.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="vp-card">
          <div class="vp-ico"><i class="bi bi-file-earmark-pdf-fill"></i></div>
          <h5 style="font-size:1rem;margin-bottom:.5rem;">Relatórios automáticos</h5>
          <p style="font-size:.85rem;color:var(--g500);line-height:1.7;">Diário impresso, boletim de medição e cronograma padrão Caixa/BB gerados em PDF com um clique. Aprovados em contratos bancários e pela Aeronáutica.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="vp-card">
          <div class="vp-ico"><i class="bi bi-people-fill"></i></div>
          <h5 style="font-size:1rem;margin-bottom:.5rem;">Equipe com controle</h5>
          <p style="font-size:.85rem;color:var(--g500);line-height:1.7;">Cada usuário tem seu nível de acesso: admin, engenheiro ou técnico. Assinatura digital no diário, rastreabilidade em tudo que é feito no sistema.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MÓDULOS -->
<section class="mod-sec" id="modulos">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-tag st-ch"><i class="bi bi-grid-3x3-gap-fill me-1"></i>9 Módulos</span>
      <h2 class="sec-h">Uma ferramenta para cada parte<br>da sua obra</h2>
      <p class="sec-sub mx-auto" style="max-width:560px;">Cada módulo resolve um problema real. Todos integrados entre si.</p>
    </div>

    <div class="row g-4">

      <!-- RDO -->
      <div class="col-lg-4 col-md-6">
        <div class="mod-card">
          <div class="mod-ico"><i class="bi bi-journal-text"></i></div>
          <span class="mod-tag">Módulo 01</span>
          <h4>Diário de Obras (RDO)</h4>
          <p>Registro diário completo, com foto, condição climática, mão de obra por ofício e ocorrências. Tudo assinado digitalmente pelo engenheiro responsável.</p>
          <ul class="mod-list">
            <li><i class="bi bi-check-circle-fill"></i>Upload de fotos por obra e data</li>
            <li><i class="bi bi-check-circle-fill"></i>Registro de temperatura e precipitação</li>
            <li><i class="bi bi-check-circle-fill"></i>Assinatura digital via tela touch ou mouse</li>
            <li><i class="bi bi-check-circle-fill"></i>Impressão PDF em formato profissional</li>
            <li><i class="bi bi-check-circle-fill"></i>Lote: imprimir múltiplos diários de uma vez</li>
          </ul>
        </div>
      </div>

      <!-- Cronograma -->
      <div class="col-lg-4 col-md-6">
        <div class="mod-card">
          <div class="mod-ico"><i class="bi bi-calendar3"></i></div>
          <span class="mod-tag">Módulo 02</span>
          <h4>Cronograma Físico-Financeiro</h4>
          <p>Gerado automaticamente a partir do orçamento importado, com barras de Gantt e distribuição mensal. Padrão aceito por Caixa Econômica, Banco do Brasil e Aeronáutica.</p>
          <ul class="mod-list">
            <li><i class="bi bi-check-circle-fill"></i>Sub-etapas por fase da obra</li>
            <li><i class="bi bi-check-circle-fill"></i>% realizado por serviço</li>
            <li><i class="bi bi-check-circle-fill"></i>Layout adaptativo por número de meses</li>
            <li><i class="bi bi-check-circle-fill"></i>Exportação PDF padrão bancário</li>
          </ul>
        </div>
      </div>

      <!-- Curva S -->
      <div class="col-lg-4 col-md-6">
        <div class="mod-card">
          <div class="mod-ico"><i class="bi bi-graph-up-arrow"></i></div>
          <span class="mod-tag">Módulo 03</span>
          <h4>Curva S — Avanço Físico</h4>
          <p>Compara o planejado versus o realizado mês a mês. Desvio calculado automaticamente, com gráfico visual que identifica atrasos ou adiantamentos imediatamente.</p>
          <ul class="mod-list">
            <li><i class="bi bi-check-circle-fill"></i>Planejado × Realizado por período</li>
            <li><i class="bi bi-check-circle-fill"></i>Desvio percentual automático</li>
            <li><i class="bi bi-check-circle-fill"></i>Gráfico acumulado com linha de tendência</li>
          </ul>
        </div>
      </div>

      <!-- Medição -->
      <div class="col-lg-4 col-md-6">
        <div class="mod-card">
          <div class="mod-ico"><i class="bi bi-bar-chart-steps"></i></div>
          <span class="mod-tag">Módulo 04</span>
          <h4>Medição Automática</h4>
          <p>Boletim de Medição gerado direto a partir dos registros do diário. Calcula os quantitativos executados no período e gera PDF para envio ao contratante.</p>
          <ul class="mod-list">
            <li><i class="bi bi-check-circle-fill"></i>Cálculo por etapa e serviço</li>
            <li><i class="bi bi-check-circle-fill"></i>PDF com memória de medição completa</li>
            <li><i class="bi bi-check-circle-fill"></i>Aprovado em contratos com Aeronáutica e bancos</li>
          </ul>
        </div>
      </div>

      <!-- Financeiro -->
      <div class="col-lg-4 col-md-6">
        <div class="mod-card">
          <div class="mod-ico"><i class="bi bi-wallet2"></i></div>
          <span class="mod-tag">Módulo 05</span>
          <h4>Financeiro — Fluxo de Caixa</h4>
          <p>Lançamentos de receitas e despesas por obra, com categorias, gráfico mensal e saldo em tempo real. Alertas quando etapas faturadas não têm lançamento correspondente.</p>
          <ul class="mod-list">
            <li><i class="bi bi-check-circle-fill"></i>Receitas e despesas com categorias</li>
            <li><i class="bi bi-check-circle-fill"></i>Gráfico mensal de entradas × saídas</li>
            <li><i class="bi bi-check-circle-fill"></i>Saldo acumulado por obra</li>
            <li><i class="bi bi-check-circle-fill"></i>Alertas de etapas não faturadas</li>
          </ul>
        </div>
      </div>

      <!-- Almoxarifado -->
      <div class="col-lg-4 col-md-6">
        <div class="mod-card">
          <div class="mod-ico"><i class="bi bi-boxes"></i></div>
          <span class="mod-tag">Módulo 06</span>
          <h4>Almoxarifado</h4>
          <p>Controle de entrada e saída de materiais por obra. Saldo em estoque calculado em tempo real, com histórico completo de movimentação de cada item.</p>
          <ul class="mod-list">
            <li><i class="bi bi-check-circle-fill"></i>Entradas e saídas com data e responsável</li>
            <li><i class="bi bi-check-circle-fill"></i>Saldo atual por item de material</li>
            <li><i class="bi bi-check-circle-fill"></i>Histórico completo de movimentações</li>
          </ul>
        </div>
      </div>

      <!-- DDS -->
      <div class="col-lg-4 col-md-6">
        <div class="mod-card">
          <div class="mod-ico"><i class="bi bi-shield-check"></i></div>
          <span class="mod-tag">Módulo 07</span>
          <h4>DDS — Segurança (NR-18)</h4>
          <p>Diálogo Diário de Segurança registrado digitalmente com lista de presença, responsável e tema abordado. Conformidade com a NR-18 documentada e rastreável.</p>
          <ul class="mod-list">
            <li><i class="bi bi-check-circle-fill"></i>Registro diário com tema e responsável</li>
            <li><i class="bi bi-check-circle-fill"></i>Lista de presença digital dos trabalhadores</li>
            <li><i class="bi bi-check-circle-fill"></i>Impressão para arquivo físico</li>
            <li><i class="bi bi-check-circle-fill"></i>Histórico completo por obra</li>
          </ul>
        </div>
      </div>

      <!-- Não Conformidades -->
      <div class="col-lg-4 col-md-6">
        <div class="mod-card">
          <div class="mod-ico"><i class="bi bi-exclamation-octagon"></i></div>
          <span class="mod-tag">Módulo 08</span>
          <h4>Não Conformidades</h4>
          <p>Registre, classifique por prioridade e acompanhe a evolução de cada problema de qualidade até o fechamento. Foto, localização, responsável e prazo em cada NC.</p>
          <ul class="mod-list">
            <li><i class="bi bi-check-circle-fill"></i>Prioridade: Alta, Média ou Baixa</li>
            <li><i class="bi bi-check-circle-fill"></i>Status: Aberta → Em Tratamento → Fechada</li>
            <li><i class="bi bi-check-circle-fill"></i>Fotos, local, responsável e prazo</li>
            <li><i class="bi bi-check-circle-fill"></i>Histórico de tratamento com data e autor</li>
          </ul>
        </div>
      </div>

      <!-- Relatórios -->
      <div class="col-lg-4 col-md-6">
        <div class="mod-card">
          <div class="mod-ico"><i class="bi bi-file-earmark-pdf-fill"></i></div>
          <span class="mod-tag">Módulo 09</span>
          <h4>Relatórios e Documentos</h4>
          <p>Gere relatórios em PDF de qualquer módulo com um clique. Upload de documentos por obra (contratos, plantas, ART, alvarás). Organização digital sem papel.</p>
          <ul class="mod-list">
            <li><i class="bi bi-check-circle-fill"></i>Relatório de obras com filtros de período</li>
            <li><i class="bi bi-check-circle-fill"></i>Upload de documentos por obra</li>
            <li><i class="bi bi-check-circle-fill"></i>Catálogo de serviços personalizado</li>
            <li><i class="bi bi-check-circle-fill"></i>Logo da empresa em todos os PDFs</li>
          </ul>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- AERONÁUTICA -->
<section class="ae-sec">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 text-center">
        <img src="./publico/img/Sabre_alado.png" alt="Aeronáutica Brasileira" style="max-width:200px;filter:drop-shadow(0 0 30px rgba(0,102,255,.4));">
      </div>
      <div class="col-lg-7">
        <span class="sec-tag" style="background:rgba(255,179,0,.15);color:#ffd166;border:1px solid rgba(255,179,0,.3);">
          <i class="bi bi-shield-fill-check me-1"></i>Validação Institucional
        </span>
        <h2 class="sec-h" style="color:#fff;">Utilizado em contratos com a<br><span style="color:var(--gold);">Aeronáutica Brasileira</span></h2>
        <p style="color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:1.4rem;">O CHRONA foi adotado por empresas prestadoras de serviço à Força Aérea Brasileira — o ambiente de fiscalização mais rigoroso do país. Diários, medições e cronogramas gerados pelo sistema foram aprovados em contratos federais.</p>
        <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:1.3rem 1.5rem;">
          <p style="color:rgba(255,255,255,.75);font-style:italic;font-size:.95rem;line-height:1.75;margin-bottom:.8rem;">
            "O CHRONA atende ao nível de controle e rastreabilidade exigido pela Aeronáutica em obras e serviços. O sistema de diários, medições e cronogramas supre todas as exigências dos contratos com a Força Aérea Brasileira."
          </p>
          <div style="font-size:.75rem;color:rgba(255,255,255,.4);">
            <i class="bi bi-shield-fill-check me-1" style="color:var(--ch);"></i>
            Validado por empresas contratadas pela FAB para execução de obras
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="how-sec">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-tag st-n">Simples de começar</span>
      <h2 class="sec-h">Em 3 passos você está gerenciando</h2>
    </div>
    <div class="row g-4 justify-content-center" style="max-width:900px;margin:0 auto;">
      <div class="col-md-4">
        <div class="step">
          <div class="step-n">1</div>
          <div class="step-arrow d-none d-md-block"><i class="bi bi-arrow-right"></i></div>
          <h5 style="font-size:.97rem;margin-bottom:.4rem;">Crie sua conta grátis</h5>
          <p style="font-size:.83rem;color:var(--g500);line-height:1.65;">Cadastre nome, e-mail e senha. Sem cartão, sem burocracia. Trial de 30 dias começa imediatamente.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="step">
          <div class="step-n">2</div>
          <div class="step-arrow d-none d-md-block"><i class="bi bi-arrow-right"></i></div>
          <h5 style="font-size:.97rem;margin-bottom:.4rem;">Importe seu orçamento</h5>
          <p style="font-size:.83rem;color:var(--g500);line-height:1.65;">Suba a planilha de orçamento (Excel ou CSV) e o cronograma é gerado automaticamente, já com os serviços e valores.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="step">
          <div class="step-n">3</div>
          <h5 style="font-size:.97rem;margin-bottom:.4rem;">Comece a registrar</h5>
          <p style="font-size:.83rem;color:var(--g500);line-height:1.65;">Preencha o diário, lance o financeiro, registre o DDS. Tudo sincronizado — medição e curva S se atualizam sozinhos.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<section class="plan-sec" id="planos">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-tag st-ch">Planos e preços</span>
      <h2 class="sec-h">Escolha o tamanho certo para você</h2>
      <p class="sec-sub">Todos incluem <strong>30 dias completamente grátis</strong> — sem cartão, sem compromisso.</p>
    </div>
    <div class="row g-4 justify-content-center">

      <div class="col-lg-4 col-md-6">
        <div class="plano h-100">
          <span class="sec-tag st-ch mb-2">Autônomo</span>
          <p style="color:var(--g500);font-size:.83rem;">Engenheiros independentes com 1 obra ativa</p>
          <div class="plano-p">R$ <?= $precMensalAut ?><span style="font-size:.95rem;font-weight:400;color:var(--g400);">/mês</span></div>
          <p style="font-size:.77rem;color:var(--g400);margin-bottom:.2rem;">após 30 dias de trial</p>
          <ul class="feats">
            <li><i class="bi bi-check-circle-fill ok"></i>1 usuário · 1 obra ativa</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Diário de Obras (RDO) ilimitado</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Importar orçamento (CSV / Excel)</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Financeiro — Fluxo de Caixa</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Almoxarifado</li>
            <li><i class="bi bi-check-circle-fill ok"></i>DDS — Segurança NR-18</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Documentos por obra</li>
            <li><i class="bi bi-x-circle no"></i><span class="no-t">Cronograma + Curva S</span></li>
            <li><i class="bi bi-x-circle no"></i><span class="no-t">Medição Automática</span></li>
            <li><i class="bi bi-x-circle no"></i><span class="no-t">Não Conformidades</span></li>
          </ul>
          <a href="<?= $urlBase ?>/publico/checkout.php?plano=autonomo&billing=mensal&trial=1" class="btn-out-full">Começar grátis</a>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="plano pop h-100">
          <div class="pop-tag">⭐ Mais Vendido</div>
          <span class="sec-tag st-ch mb-2">Construtora</span>
          <p style="color:var(--g500);font-size:.83rem;">Pequenas e médias construtoras</p>
          <div class="plano-p ch">R$ <?= $precMensalCon ?><span style="font-size:.95rem;font-weight:400;color:var(--g400);">/mês</span></div>
          <p style="font-size:.77rem;color:var(--g400);margin-bottom:.2rem;">após 30 dias de trial</p>
          <ul class="feats">
            <li><i class="bi bi-check-circle-fill ok"></i><strong>3 usuários · até 3 obras</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i>Tudo do plano Autônomo</li>
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Cronograma Físico-Financeiro</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Curva S — Planejado × Realizado</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Medição Automática (PDF)</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Não Conformidades</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i>PDFs com logo da empresa</li>
          </ul>
          <a href="<?= $urlTrial ?>" class="btn-ch-full">Começar 30 dias grátis</a>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="plano h-100">
          <span style="font-size:.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:.5rem;">Empresarial</span>
          <p style="color:var(--g500);font-size:.83rem;">Gestão sem limites de obras ou usuários</p>
          <div class="plano-p" style="color:#b8860b;">R$ <?= $precMensalEmp ?><span style="font-size:.95rem;font-weight:400;color:var(--g400);">/mês</span></div>
          <p style="font-size:.77rem;color:var(--g400);margin-bottom:.2rem;">após 30 dias de trial</p>
          <ul class="feats">
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Usuários ilimitados</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Obras ilimitadas</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i>Tudo do plano Construtora</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Relatórios avançados</li>
            <li><i class="bi bi-whatsapp ok"></i>Suporte WhatsApp prioritário</li>
          </ul>
          <a href="<?= $urlBase ?>/publico/checkout.php?plano=empresarial&billing=mensal&trial=1" class="btn-out-full">Quero ilimitado</a>
        </div>
      </div>

    </div>

    <!-- Selos -->
    <div style="background:linear-gradient(135deg,#0a1628,#0d2040);border-radius:18px;padding:1.8rem 2rem;text-align:center;margin-top:2.5rem;">
      <p style="font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:1.2rem;">Aceito em contratos exigentes</p>
      <div style="display:flex;justify-content:center;align-items:center;flex-wrap:wrap;gap:1.5rem 3rem;">
        <div style="text-align:center;">
          <img src="./publico/img/Sabre_alado.png" width="44" style="margin-bottom:5px;opacity:.85;">
          <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.65);">Aeronáutica<br>Brasileira</div>
        </div>
        <div style="width:1px;height:40px;background:rgba(255,255,255,.08);"></div>
        <div style="text-align:center;">
          <div style="font-size:1.5rem;margin-bottom:5px;">🏦</div>
          <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.65);">Caixa Econômica<br>Federal</div>
        </div>
        <div style="width:1px;height:40px;background:rgba(255,255,255,.08);"></div>
        <div style="text-align:center;">
          <div style="font-size:1.5rem;margin-bottom:5px;">🏛️</div>
          <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.65);">Banco do<br>Brasil</div>
        </div>
        <div style="width:1px;height:40px;background:rgba(255,255,255,.08);"></div>
        <div style="text-align:center;">
          <div style="font-size:1.5rem;margin-bottom:5px;">🔒</div>
          <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.65);">Pagamento<br>100% Seguro</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DEPOIMENTOS -->
<section class="dep-sec" style="background:var(--g50);">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-tag st-n">Depoimentos</span>
      <h2 class="sec-h">O que dizem quem usa</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="dep-card">
          <div class="stars">★★★★★</div>
          <p class="dep-t">O CHRONA integrou nosso orçamento com o diário. Antes levávamos dias para fechar a medição, agora fazemos em horas. Aprovado pelo banco na 1ª tentativa.</p>
          <div class="dep-auth"><div class="dep-av">RF</div><div><div class="dep-n">Ricardo Fernandes</div><div class="dep-r">Eng. Civil · RF Construções</div></div></div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="dep-card">
          <div class="stars">★★★★★</div>
          <p class="dep-t">A integração do orçamento com o diário eliminou o retrabalho. As medições automáticas nos deram precisão incomparável. Suporte excelente!</p>
          <div class="dep-auth"><div class="dep-av">AS</div><div><div class="dep-n">Amanda Santos</div><div class="dep-r">Gestora de Obras</div></div></div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="dep-card">
          <div style="background:rgba(0,102,255,.08);border:1px solid rgba(0,102,255,.15);border-radius:8px;padding:.5rem .8rem;display:inline-flex;align-items:center;gap:5px;margin-bottom:.7rem;font-size:.72rem;font-weight:700;color:var(--ch);">
            <i class="bi bi-shield-fill-check"></i>Aeronáutica Brasileira
          </div>
          <div class="stars">★★★★★</div>
          <p class="dep-t">"O sistema supre todas as exigências dos contratos com a Força Aérea Brasileira."</p>
          <div class="dep-auth">
            <div class="dep-av" style="background:linear-gradient(135deg,#1a3a6e,#2b5ca8);">FAB</div>
            <div><div class="dep-n">Empresa Contratada pela FAB</div><div class="dep-r">Obras e serviços — Força Aérea Brasileira</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="faq-sec" id="faq">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-tag st-n">Dúvidas frequentes</span>
      <h2 class="sec-h">Perguntas e respostas</h2>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="accordion" id="faq-acc">

          <div class="acc-item accordion-item">
            <h2 class="accordion-header">
              <button class="acc-btn accordion-button" data-bs-toggle="collapse" data-bs-target="#f1">
                O trial é realmente grátis? Preciso de cartão?
              </button>
            </h2>
            <div id="f1" class="accordion-collapse collapse show" data-bs-parent="#faq-acc">
              <div class="acc-body">Sim, completamente grátis e sem informar cartão de crédito. Você tem 30 dias de acesso total ao plano escolhido. A cobrança só ocorre se você optar por continuar após o período de trial.</div>
            </div>
          </div>

          <div class="acc-item accordion-item">
            <h2 class="accordion-header">
              <button class="acc-btn accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f2">
                Como funciona a importação do orçamento?
              </button>
            </h2>
            <div id="f2" class="accordion-collapse collapse" data-bs-parent="#faq-acc">
              <div class="acc-body">Você exporta sua planilha de orçamento no formato CSV ou Excel com os campos de serviço, unidade, quantidade e valor. O CHRONA lê esse arquivo e gera automaticamente as etapas do cronograma e da curva S. Funciona com qualquer planilha de orçamento, inclusive a Super Planilha.</div>
            </div>
          </div>

          <div class="acc-item accordion-item">
            <h2 class="accordion-header">
              <button class="acc-btn accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f3">
                Quantos usuários posso cadastrar?
              </button>
            </h2>
            <div id="f3" class="accordion-collapse collapse" data-bs-parent="#faq-acc">
              <div class="acc-body">Depende do plano: Autônomo permite 1 usuário, Construtora permite até 3, e Empresarial é ilimitado. Cada usuário tem seu nível de acesso configurável: admin, engenheiro ou técnico de campo.</div>
            </div>
          </div>

          <div class="acc-item accordion-item">
            <h2 class="accordion-header">
              <button class="acc-btn accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f4">
                Os PDFs gerados são aceitos pelos bancos e pela Aeronáutica?
              </button>
            </h2>
            <div id="f4" class="accordion-collapse collapse" data-bs-parent="#faq-acc">
              <div class="acc-body">Sim. O cronograma exportado segue o padrão exigido pela Caixa Econômica Federal e pelo Banco do Brasil para financiamentos de obra. O sistema é utilizado por empresas contratadas pela Força Aérea Brasileira, que possui os requisitos mais rigorosos do mercado.</div>
            </div>
          </div>

          <div class="acc-item accordion-item">
            <h2 class="accordion-header">
              <button class="acc-btn accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f5">
                Posso cancelar a qualquer momento?
              </button>
            </h2>
            <div id="f5" class="accordion-collapse collapse" data-bs-parent="#faq-acc">
              <div class="acc-body">Sim. Não há fidelidade nem multa. Se cancelar durante o trial, não paga nada. Se já estiver pagando, pode cancelar antes do próximo vencimento e a conta é mantida até o fim do período pago.</div>
            </div>
          </div>

          <div class="acc-item accordion-item">
            <h2 class="accordion-header">
              <button class="acc-btn accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f6">
                O sistema funciona no celular?
              </button>
            </h2>
            <div id="f6" class="accordion-collapse collapse" data-bs-parent="#faq-acc">
              <div class="acc-body">Sim. O CHRONA é totalmente responsivo — projetado para ser usado no campo pelo encarregado com o celular, e no escritório pelo engenheiro no computador. Não precisa instalar nenhum aplicativo.</div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cta-sec">
  <div class="container cta-inner text-center">
    <div class="trial-badge">
      <i class="bi bi-gift-fill"></i>
      <span>30 dias grátis — sem cartão — acesso total</span>
    </div>
    <h2 style="color:#fff;font-size:clamp(2rem,4.5vw,3rem);margin-bottom:.9rem;">
      Comece hoje.<br><span class="hl">Sem burocracia.</span>
    </h2>
    <p style="color:rgba(255,255,255,.5);font-size:1rem;line-height:1.8;max-width:480px;margin:0 auto 2rem;">
      Cadastre agora e tenha acesso completo ao CHRONA por 30 dias. Sem pagar nada, sem informar cartão.
    </p>
    <div class="d-flex justify-content-center flex-wrap gap-3">
      <a href="<?= $urlTrial ?>" class="btn-cta" style="font-size:1.05rem;padding:1.1rem 2.2rem;">
        <i class="bi bi-lightning-charge-fill"></i>Criar conta grátis
      </a>
      <a href="<?= $urlLogin ?>" class="btn-out">
        <i class="bi bi-box-arrow-in-right"></i>Já tenho conta
      </a>
    </div>
    <p style="font-size:.75rem;color:rgba(255,255,255,.28);margin-top:1.5rem;">
      Pagamento seguro via Mercado Pago · PIX, cartão ou boleto
    </p>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="row g-4 pb-4 mb-3" style="border-bottom:1px solid rgba(255,255,255,.06);">
      <div class="col-lg-4">
        <div style="font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.1rem;color:#fff;display:flex;align-items:center;gap:.5rem;margin-bottom:.7rem;">
          <div style="width:30px;height:30px;border-radius:7px;background:var(--ch-g);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:900;color:#fff;">CH</div>
          CHRONA
        </div>
        <p style="font-size:.82rem;color:var(--g500);line-height:1.7;max-width:260px;">Sistema de gestão de obras digital — diário, cronograma, financeiro, almoxarifado, segurança e qualidade.</p>
        <div class="d-flex gap-2 mt-3">
          <a href="https://www.instagram.com/marcos.alveres/" target="_blank" class="fsoc"><i class="bi bi-instagram"></i></a>
          <a href="https://www.youtube.com/c/marcosalveres" target="_blank" class="fsoc"><i class="bi bi-youtube"></i></a>
          <a href="mailto:mmalveres@gmail.com" class="fsoc"><i class="bi bi-envelope-fill"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <p class="fh">Módulos</p>
        <a href="#modulos" class="flink">Diário de Obras</a>
        <a href="#modulos" class="flink">Cronograma</a>
        <a href="#modulos" class="flink">Financeiro</a>
        <a href="#modulos" class="flink">Almoxarifado</a>
        <a href="#modulos" class="flink">Não Conformidades</a>
      </div>
      <div class="col-6 col-lg-2">
        <p class="fh">Links</p>
        <a href="#planos" class="flink">Planos e preços</a>
        <a href="<?= $urlTrial ?>" class="flink">Trial grátis</a>
        <a href="<?= $urlLogin ?>" class="flink">Login</a>
        <a href="site.php" class="flink">← Site principal</a>
      </div>
      <div class="col-lg-4">
        <p class="fh">Contato</p>
        <p style="font-size:.82rem;color:var(--g500);line-height:2.1;">
          <i class="bi bi-envelope-fill me-2" style="color:var(--ch);"></i>mmalveres@gmail.com<br>
          <i class="bi bi-instagram me-2" style="color:#e1306c;"></i>@marcos.alveres<br>
          <i class="bi bi-youtube me-2" style="color:#ff0000;"></i>youtube.com/c/marcosalveres
        </p>
        <p style="font-size:.75rem;color:var(--g600);margin-top:.5rem;">Criado por Eng. Marcos Alveres</p>
      </div>
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <p style="font-size:.76rem;color:var(--g600);margin:0;">© 2026 CHRONA — Eng. Marcos Alveres · Todos os direitos reservados</p>
      <a href="site.php" style="font-size:.76rem;color:var(--g600);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color=''">
        <i class="bi bi-arrow-left me-1"></i>Ver todos os produtos
      </a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.addEventListener('scroll', () => {
  document.querySelector('.navbar').style.boxShadow =
    window.scrollY > 50 ? '0 4px 20px rgba(0,0,0,.14)' : '0 2px 12px rgba(0,0,0,.07)';
});
</script>
</body>
</html>
