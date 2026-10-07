<?php
// Preços padrão (usados se o CHRONA não estiver acessível)
$precMensalAut = '49';
$precMensalCon = '149';
$precMensalEmp = '250';

// Tenta ler preços dinâmicos do banco do CHRONA
// site.php fica na raiz do domínio; CHRONA está em /CHRONA/
$_autoload = __DIR__ . '/CHRONA/autoload.php';
if (file_exists($_autoload)) {
    try {
        require_once $_autoload;
        $_p = \Aplicacao\Helpers\PrecosHelper::getPrecos();
        $precMensalAut = number_format($_p['autonomo']['mensal'],    0, ',', '.');
        $precMensalCon = number_format($_p['construtora']['mensal'], 0, ',', '.');
        $precMensalEmp = number_format($_p['empresarial']['mensal'], 0, ',', '.');
        unset($_p);
    } catch (\Throwable $_e) { /* mantém defaults */ }
}
unset($_autoload);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eng. Marcos Alveres — Super Planilha + CHRONA Gestão de Obras</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
:root {
  --sp:     #1a7a3c;
  --sp2:    #25a85a;
  --sp-l:   #e8f5ee;
  --sp-g:   linear-gradient(135deg,#1a7a3c,#25a85a);
  --ch:     #0066ff;
  --ch2:    #00c9ff;
  --ch-l:   #e8f0fe;
  --ch-g:   linear-gradient(135deg,#0066ff,#00c9ff);
  --gold:   #ffb300;
  --g50:  #f9fafb; --g100:#f3f4f6; --g200:#e5e7eb;
  --g400:#9ca3af;  --g500:#6b7280; --g600:#4b5563;
  --g700:#374151;  --g800:#1f2937; --g900:#111827;
  --ok:   #10b981;
}
*{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;scroll-padding-top:80px;}
body{font-family:'Inter',sans-serif;color:var(--g700);background:var(--g50);overflow-x:hidden;}
h1,h2,h3,h4,h5{font-family:'Montserrat',sans-serif;font-weight:800;line-height:1.15;letter-spacing:-.02em;}

/* NAV */
.navbar{padding:.85rem 0;background:rgba(255,255,255,.97);backdrop-filter:blur(10px);
  box-shadow:0 2px 12px rgba(0,0,0,.07);position:fixed;top:0;width:100%;z-index:1000;}
.navbar-brand{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.3rem;color:var(--g900)!important;display:flex;align-items:center;gap:.5rem;}
.logo-m{width:34px;height:34px;border-radius:9px;background:var(--g900);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1rem;}
.nav-link{font-weight:600;color:var(--g600)!important;border-radius:8px;padding:.45rem .85rem!important;transition:all .2s;}
.nav-link:hover{color:var(--g900)!important;background:var(--g100);}
.btn-sp-nav{background:var(--sp-g);color:#fff!important;border-radius:9px;padding:.5rem 1.1rem!important;font-weight:700!important;}
.btn-ch-nav{background:var(--ch-g);color:#fff!important;border-radius:9px;padding:.5rem 1.1rem!important;font-weight:700!important;}
.btn-sp-nav:hover,.btn-ch-nav:hover{transform:translateY(-1px);opacity:.92;}

/* HERO */
.hero{padding:140px 0 90px;background:linear-gradient(160deg,var(--g900) 0%,#0d1f3c 100%);position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;top:-200px;right:-200px;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(0,102,255,.18),transparent 70%);}
.hero::after{content:'';position:absolute;bottom:-150px;left:-100px;width:450px;height:450px;border-radius:50%;background:radial-gradient(circle,rgba(26,122,60,.14),transparent 70%);}
.hero-inner{position:relative;z-index:1;}
.chip{display:inline-flex;align-items:center;gap:5px;padding:.3rem .85rem;border-radius:50px;font-size:.75rem;font-weight:700;}
.chip-sp{background:rgba(26,122,60,.22);border:1px solid rgba(37,168,90,.3);color:#4fd88a;}
.chip-ch{background:rgba(0,102,255,.2);border:1px solid rgba(0,102,255,.35);color:#6ab3ff;}
.hero h1{font-size:clamp(2.2rem,5vw,3.6rem);color:#fff;margin-bottom:1.1rem;}
.hero-sub{font-size:1.05rem;color:rgba(255,255,255,.6);line-height:1.75;margin-bottom:1.8rem;max-width:560px;}
.btn-hero-sp{background:var(--sp-g);color:#fff;padding:.9rem 1.8rem;border-radius:12px;font-weight:700;font-size:.95rem;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;transition:all .2s;}
.btn-hero-sp:hover{color:#fff;transform:translateY(-2px);box-shadow:0 10px 24px rgba(26,122,60,.4);}
.btn-hero-ch{background:var(--ch-g);color:#fff;padding:.9rem 1.8rem;border-radius:12px;font-weight:700;font-size:.95rem;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;transition:all .2s;}
.btn-hero-ch:hover{color:#fff;transform:translateY(-2px);box-shadow:0 10px 24px rgba(0,102,255,.4);}
.stat-n{font-family:'Montserrat',sans-serif;font-size:1.9rem;font-weight:900;color:#fff;}
.stat-l{font-size:.76rem;color:rgba(255,255,255,.4);}

/* PRODUTO MINI CARDS NO HERO */
.mini-card{border-radius:14px;padding:1.2rem;}
.mini-sp{background:rgba(26,122,60,.15);border:1px solid rgba(37,168,90,.25);}
.mini-ch{background:rgba(0,102,255,.15);border:1px solid rgba(0,102,255,.3);}
.mini-logo{width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.8rem;color:#fff;font-family:'Montserrat',sans-serif;}
.mini-title{font-family:'Montserrat',sans-serif;font-weight:800;font-size:.88rem;color:#fff;}
.mini-tag{font-size:.62rem;padding:2px 7px;border-radius:20px;font-weight:700;}
.btn-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:4px;}
.bg{background:#217346;color:#fff;border-radius:4px;padding:6px 4px;font-size:.58rem;font-weight:700;text-align:center;}
.rdo-mini{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:.7rem;}
.pill{display:inline-flex;align-items:center;gap:3px;font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:20px;margin-right:3px;}
.pg{background:rgba(16,185,129,.18);color:#10b981;}
.pb{background:rgba(0,102,255,.22);color:#6ab3ff;}
.py{background:rgba(255,179,0,.18);color:#ffb300;}
.prog{height:3px;background:rgba(255,255,255,.08);border-radius:2px;margin-top:.45rem;}
.progf{height:100%;border-radius:2px;}

/* SEÇÃO GENÉRICA */
.sec-tag{display:inline-block;font-size:.72rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;padding:.32rem .9rem;border-radius:50px;margin-bottom:1rem;}
.st-sp{background:var(--sp-l);color:var(--sp);}
.st-ch{background:var(--ch-l);color:var(--ch);}
.st-n{background:var(--g100);color:var(--g600);}
.sec-h{font-size:clamp(1.7rem,3.5vw,2.6rem);color:var(--g900);margin-bottom:.8rem;}
.sec-sub{font-size:1rem;color:var(--g500);line-height:1.75;}

/* SOBRE */
.avatar-wrap{width:170px;height:170px;border-radius:50%;overflow:hidden;border:4px solid var(--g200);background:var(--g100);display:flex;align-items:center;justify-content:center;font-size:4.5rem;margin:0 auto 1rem;}
.sobre-stat{background:var(--g50);border:1px solid var(--g200);border-radius:12px;padding:1rem;text-align:center;}
.sn{font-family:'Montserrat',sans-serif;font-size:1.6rem;font-weight:900;}
.exp-tag{display:inline-block;background:var(--g100);color:var(--g700);padding:.28rem .8rem;border-radius:7px;font-size:.77rem;font-weight:600;margin:.2rem;}

/* PLANILHA */
.sp-sec{background:linear-gradient(180deg,#f0faf4,#fff);padding:100px 0;border-top:3px solid var(--sp);}
.sp-card{background:#fff;border-radius:16px;padding:1.6rem;border:1px solid rgba(26,122,60,.12);box-shadow:0 4px 18px rgba(26,122,60,.06);height:100%;transition:all .32s;position:relative;overflow:hidden;}
.sp-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--sp-g);}
.sp-card:hover{transform:translateY(-5px);box-shadow:0 14px 36px rgba(26,122,60,.12);}
.sp-icon{width:46px;height:46px;border-radius:11px;background:var(--sp-l);color:var(--sp);display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:.9rem;}
.sp-card h5{font-size:.92rem;color:var(--g900);margin-bottom:.35rem;font-family:'Inter',sans-serif;font-weight:700;}
.sp-card p{font-size:.83rem;color:var(--g500);line-height:1.62;}
.sp-cta{background:linear-gradient(135deg,#0f4522,#1a7a3c);border-radius:18px;padding:2.5rem 2rem;position:relative;overflow:hidden;}
.sp-cta::before{content:'';position:absolute;top:-80px;right:-80px;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,.05);}
.sp-cta-inner{position:relative;z-index:1;}
.price-big{font-family:'Montserrat',sans-serif;font-size:2.6rem;font-weight:900;color:#fff;line-height:1;}
.btn-buy{background:#fff;color:var(--sp);padding:.95rem 1.8rem;border-radius:11px;font-weight:800;font-size:.92rem;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;transition:all .2s;}
.btn-buy:hover{background:#f0faf4;transform:translateY(-2px);color:var(--sp);}
.g7-box{background:linear-gradient(135deg,#fffbf0,#fff9e6);border:2px solid #ffd166;border-radius:14px;padding:1.2rem 1.4rem;display:flex;gap:.9rem;align-items:flex-start;margin-top:1.1rem;}
.g7-ico{font-size:1.8rem;flex-shrink:0;}
.g7-box h6{font-family:'Montserrat',sans-serif;font-weight:800;font-size:.9rem;color:#92400e;margin-bottom:.3rem;}
.g7-box p{font-size:.8rem;color:#78350f;line-height:1.55;margin:0;}

/* DIVISOR */
.divisor{background:var(--g900);padding:56px 0;text-align:center;position:relative;overflow:hidden;}
.divisor::before{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(26,122,60,.12),rgba(0,102,255,.12));}
.divisor-inner{position:relative;z-index:1;}
.divisor h2{color:#fff;font-size:clamp(1.4rem,3vw,2.1rem);margin-bottom:.7rem;}
.divisor p{color:rgba(255,255,255,.48);max-width:540px;margin:0 auto 1.6rem;font-size:.95rem;}
.dpill{padding:.48rem 1.2rem;border-radius:50px;font-size:.8rem;font-weight:600;border:1px solid;}
.dp-sp{background:rgba(26,122,60,.2);border-color:rgba(37,168,90,.4);color:#4fd88a;}
.dp-ar{color:rgba(255,255,255,.3);border-color:transparent;background:transparent;}
.dp-ch{background:rgba(0,102,255,.2);border-color:rgba(0,102,255,.4);color:#6ab3ff;}

/* CHRONA */
.ch-sec{background:linear-gradient(180deg,#f0f4ff,#fff);padding:100px 0;border-top:3px solid var(--ch);}
.ch-feat{background:#fff;border-radius:14px;padding:1.3rem;border:1px solid rgba(0,102,255,.1);display:flex;gap:.9rem;transition:all .28s;}
.ch-feat:hover{border-color:rgba(0,102,255,.25);box-shadow:0 7px 22px rgba(0,102,255,.08);transform:translateY(-3px);}
.ch-icon{width:42px;height:42px;border-radius:10px;background:var(--ch-l);color:var(--ch);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.ch-feat h5{font-family:'Inter',sans-serif;font-size:.88rem;font-weight:700;margin-bottom:.25rem;color:var(--g900);}
.ch-feat p{font-size:.78rem;color:var(--g500);margin:0;line-height:1.52;}
.ch-mock{background:var(--g900);border-radius:16px;padding:1.3rem;box-shadow:0 20px 48px rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.07);}
.ch-mock-hdr{display:flex;align-items:center;gap:.55rem;padding-bottom:11px;border-bottom:1px solid rgba(255,255,255,.07);margin-bottom:11px;}
.ch-dot{width:7px;height:7px;border-radius:50%;background:var(--gold);}
.ch-logo{font-family:'Montserrat',sans-serif;font-weight:900;font-size:.88rem;color:#fff;}
.ch-nl{font-size:.68rem;color:rgba(255,255,255,.32);}
.ch-nl.a{color:rgba(255,255,255,.88);}
.rdo{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:9px;padding:.85rem;margin-bottom:.55rem;}
.rdo-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:.35rem;}
.rdo-t{font-size:.76rem;font-weight:700;color:#fff;}
.rdo-d{font-size:.62rem;color:rgba(255,255,255,.28);}
.rdo-b{font-size:.68rem;color:rgba(255,255,255,.5);line-height:1.48;margin-bottom:.45rem;}
.ch-strip{background:var(--ch-g);border-radius:10px;padding:.8rem 1.1rem;display:flex;align-items:center;justify-content:space-between;margin-top:.7rem;flex-wrap:wrap;gap:.4rem;}
.ch-strip span{font-size:.75rem;color:rgba(255,255,255,.85);font-weight:600;}
.ch-strip a{background:#fff;color:var(--ch);padding:.38rem .9rem;border-radius:7px;font-size:.72rem;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:4px;}
.trial-box{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.22);border-radius:12px;padding:.9rem 1.1rem;display:flex;align-items:center;gap:.75rem;margin-bottom:1.3rem;}
.trial-box i{font-size:1.4rem;color:var(--ok);}
.trial-box strong{display:block;font-size:.88rem;color:var(--g900);}
.trial-box span{font-size:.77rem;color:var(--g500);}

/* PLANOS */
.plano{background:#fff;border-radius:16px;padding:2rem;border:1px solid var(--g200);height:100%;transition:all .32s;}
.plano:hover{transform:translateY(-5px);box-shadow:0 14px 36px rgba(0,0,0,.09);}
.plano.pop{border:2px solid var(--ch);box-shadow:0 8px 28px rgba(0,102,255,.12);}
.pop-tag{background:var(--ch-g);color:#fff;font-size:.66rem;font-weight:700;padding:.28rem .9rem;border-radius:50px;display:inline-block;margin-bottom:.8rem;}
.plano-p{font-family:'Montserrat',sans-serif;font-size:2.6rem;font-weight:900;color:var(--g900);margin:.6rem 0 .25rem;line-height:1;}
.plano-p.ch{color:var(--ch);}
.plano-feats{list-style:none;padding:0;margin:1rem 0 1.3rem;}
.plano-feats li{display:flex;align-items:center;gap:.45rem;font-size:.86rem;margin-bottom:.6rem;}
.ok{color:var(--ok);}
.no{color:var(--g300);}
.no-t{color:var(--g400);}
.btn-pl-ch{display:block;text-align:center;padding:.85rem;border-radius:10px;background:var(--ch-g);color:#fff;font-weight:700;font-size:.88rem;text-decoration:none;transition:all .2s;}
.btn-pl-ch:hover{color:#fff;transform:translateY(-2px);box-shadow:0 8px 18px rgba(0,102,255,.3);}
.btn-pl-out{display:block;text-align:center;padding:.85rem;border-radius:10px;border:1.5px solid var(--g200);color:var(--g700);font-weight:700;font-size:.88rem;text-decoration:none;transition:all .2s;}
.btn-pl-out:hover{border-color:var(--ch);color:var(--ch);}

/* DEPOIMENTOS */
.dep{background:var(--g50);padding:90px 0;}
.dep-card{background:#fff;border-radius:15px;padding:1.7rem;border:1px solid var(--g200);height:100%;box-shadow:0 4px 16px rgba(0,0,0,.05);position:relative;transition:all .28s;}
.dep-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.09);}
.dep-card::before{content:'"';position:absolute;top:.7rem;right:1.1rem;font-size:3.2rem;color:var(--g100);font-family:Georgia,serif;line-height:1;}
.dep-strip{display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .8rem;border-radius:7px;font-size:.7rem;font-weight:700;margin-bottom:.8rem;}
.ds-sp{background:var(--sp-l);color:var(--sp);}
.ds-ch{background:var(--ch-l);color:var(--ch);}
.stars{color:var(--gold);font-size:.83rem;letter-spacing:2px;margin-bottom:.55rem;}
.dep-t{font-size:.875rem;color:var(--g700);line-height:1.7;margin-bottom:1.1rem;}
.dep-auth{display:flex;align-items:center;gap:.75rem;}
.dep-av{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Montserrat',sans-serif;font-weight:900;font-size:.82rem;color:#fff;flex-shrink:0;}
.av-sp{background:var(--sp-g);}
.av-ch{background:var(--ch-g);}
.dep-n{font-weight:700;font-size:.875rem;color:var(--g900);}
.dep-r{font-size:.73rem;color:var(--g400);}

/* GARANTIAS */
.gar-sec{background:#fff;padding:90px 0;}
.gar-block{border-radius:18px;padding:2.2rem;height:100%;}
.gb-sp{background:linear-gradient(135deg,#f0faf4,#e8f5ee);border:2px solid rgba(26,122,60,.18);}
.gb-ch{background:linear-gradient(135deg,#f0f4ff,#e8f0fe);border:2px solid rgba(0,102,255,.13);}
.gb-hdr{display:flex;align-items:center;gap:.9rem;padding-bottom:1.1rem;border-bottom:1px solid rgba(0,0,0,.06);margin-bottom:1.3rem;}
.gb-ico{width:54px;height:54px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;}
.gi-sp{background:var(--sp-l);}
.gi-ch{background:var(--ch-l);}
.gb-hdr h4{font-size:1.15rem;color:var(--g900);margin-bottom:.15rem;}
.gb-hdr p{font-size:.8rem;color:var(--g500);margin:0;}
.gb-item{display:flex;align-items:flex-start;gap:.65rem;margin-bottom:.85rem;}
.gb-item i{font-size:.95rem;margin-top:2px;flex-shrink:0;}
.gb-item-sp i{color:var(--sp);}
.gb-item-ch i{color:var(--ch);}
.gb-item strong{font-size:.875rem;color:var(--g900);display:block;margin-bottom:.15rem;}
.gb-item span{font-size:.79rem;color:var(--g500);line-height:1.52;}
.btn-sp{background:var(--sp-g);color:#fff;padding:.9rem 1.6rem;border-radius:10px;font-weight:700;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;transition:all .2s;width:100%;justify-content:center;}
.btn-sp:hover{color:#fff;transform:translateY(-2px);box-shadow:0 8px 22px rgba(26,122,60,.32);}
.btn-ch{background:var(--ch-g);color:#fff;padding:.9rem 1.6rem;border-radius:10px;font-weight:700;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;transition:all .2s;width:100%;justify-content:center;}
.btn-ch:hover{color:#fff;transform:translateY(-2px);box-shadow:0 8px 22px rgba(0,102,255,.32);}

/* CTA FINAL */
.cta-final{background:var(--g900);padding:100px 0;position:relative;overflow:hidden;}
.cta-final::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(26,122,60,.09),rgba(0,102,255,.11));}
.cta-inner{position:relative;z-index:1;}
.cta-duo{display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;max-width:740px;margin:0 auto;}
.cta-c{border-radius:16px;padding:2rem;text-align:center;}
.cta-sp{background:var(--sp-g);}
.cta-ch{background:var(--ch-g);}
.cta-c h4{color:#fff;font-size:1.05rem;margin-bottom:.3rem;}
.cta-price{font-family:'Montserrat',sans-serif;font-size:2rem;font-weight:900;color:#fff;margin:.4rem 0;}
.cta-det{font-size:.75rem;color:rgba(255,255,255,.62);margin-bottom:1.1rem;}
.btn-wh{background:#fff;font-weight:800;font-size:.85rem;padding:.72rem 1.4rem;border-radius:9px;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;transition:all .2s;}
.btn-wh-sp{color:var(--sp);}
.btn-wh-sp:hover{background:#f0faf4;color:var(--sp);transform:translateY(-1px);}
.btn-wh-ch{color:var(--ch);}
.btn-wh-ch:hover{background:#f0f4ff;color:var(--ch);transform:translateY(-1px);}

/* FOOTER */
footer{background:#080e1a;padding:3rem 0 1.5rem;}
.f-logo{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.15rem;color:#fff;display:flex;align-items:center;gap:.45rem;margin-bottom:.7rem;}
.flink{color:var(--g500);font-size:.84rem;text-decoration:none;display:block;margin-bottom:.45rem;transition:color .2s;}
.flink:hover{color:#fff;}
.fh{color:#fff;font-family:'Montserrat',sans-serif;font-size:.82rem;font-weight:700;margin-bottom:.8rem;}
.fsoc{width:33px;height:33px;border-radius:7px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.45);display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.9rem;transition:all .2s;}
.fsoc:hover{background:rgba(255,255,255,.14);color:#fff;}
.fdot{width:7px;height:7px;border-radius:50%;display:inline-block;margin-right:5px;vertical-align:middle;}

@media(max-width:992px){
  .cta-duo{grid-template-columns:1fr;max-width:380px;}
  .hero{padding:120px 0 70px;}
}
@media(max-width:576px){
  .hero h1{font-size:2.1rem;}
  .gar-block{padding:1.4rem;}
}
</style>
</head>
<body>
<!-- NAV -->
<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container">
    <a class="navbar-brand" href="#"><div class="logo-m">M</div>Marcos Alveres</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li><a class="nav-link" href="#planilha"><i class="bi bi-file-earmark-spreadsheet me-1" style="color:var(--sp);"></i>Super Planilha</a></li>
        <li><a class="nav-link" href="#chrona"><i class="bi bi-buildings me-1" style="color:var(--ch);"></i>CHRONA</a></li>
        <li><a class="nav-link" href="#garantias">Garantias</a></li>
        <li class="ms-1"><a href="https://payment.hotmart.com/L6589686T?checkoutMode=10" class="btn-sp-nav nav-link"><i class="bi bi-cart-fill me-1"></i>Comprar Planilha</a></li>
        <li><a href="https://engmarcosalveres.com.br/CHRONA/publico/checkout.php?plano=construtora&billing=mensal&trial=1" class="btn-ch-nav nav-link"><i class="bi bi-lightning-charge-fill me-1"></i>Trial CHRONA</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="container hero-inner">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <div class="d-flex gap-2 mb-3">
          <span class="chip chip-sp"><i class="bi bi-file-earmark-spreadsheet-fill"></i>Super Planilha</span>
          <span class="chip chip-ch"><i class="bi bi-buildings"></i>CHRONA</span>
        </div>
        <h1>Ferramentas de engenheiro<br>para <span style="color:var(--gold);">engenheiros.</span></h1>
        <p class="hero-sub">Da planilha de orçamento ao sistema de gestão digital. Dois produtos que eliminam o retrabalho da sua rotina de obra.</p>
        <div class="d-flex flex-wrap gap-3 mb-4">
          <a href="#planilha" class="btn-hero-sp"><i class="bi bi-file-earmark-spreadsheet-fill"></i>Ver Super Planilha</a>
          <a href="#chrona" class="btn-hero-ch"><i class="bi bi-buildings"></i>Conhecer CHRONA</a>
        </div>
        <div class="d-flex gap-4 flex-wrap pt-3" style="border-top:1px solid rgba(255,255,255,.1);">
          <div><div class="stat-n">+6.000</div><div class="stat-l">Planilhas vendidas</div></div>
          <div><div class="stat-n">95%</div><div class="stat-l">Economia de tempo</div></div>
          <div><div class="stat-n">100%</div><div class="stat-l">Base SINAPI</div></div>
          <div><div class="stat-n">30 dias</div><div class="stat-l">Trial CHRONA grátis</div></div>
        </div>
      </div>
      <div class="col-lg-6 d-none d-lg-block">
        <div class="row g-2">
          <div class="col-12">
            <div class="mini-card mini-sp">
              <div class="d-flex align-items-center gap-2 mb-2">
                <div class="mini-logo" style="background:var(--sp);">Sp</div>
                <span class="mini-title">SUPER PLANILHA v3.0</span>
                <span class="mini-tag ms-auto" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);">EXCEL</span>
              </div>
              <div class="btn-grid mb-1">
                <div class="bg">ESTACAS</div><div class="bg">FUNDAÇÃO</div>
                <div class="bg">PILARES</div><div class="bg">VIGAS</div>
                <div class="bg">LAJES</div><div class="bg">PAREDES</div>
                <div class="bg" style="background:var(--sp);">ORÇAMENTO</div>
                <div class="bg" style="background:var(--sp);">CRONOGRAMA</div>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:.62rem;color:rgba(255,255,255,.42);">
                <span><i class="bi bi-check-circle-fill" style="color:#4fd88a;"></i> 100% SINAPI</span>
                <span><i class="bi bi-shield-fill-check" style="color:#ffd166;"></i> 7 dias garantia</span>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="mini-card mini-ch">
              <div class="d-flex align-items-center gap-2 mb-2">
                <div class="mini-logo" style="background:var(--ch);">CH</div>
                <span class="mini-title">CHRONA · Gestão de Obras</span>
                <span class="mini-tag ms-auto" style="background:rgba(16,185,129,.2);color:#4fd88a;">30 dias grátis</span>
              </div>
              <div class="rdo-mini">
                <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
                  <span style="font-size:.7rem;font-weight:700;color:#fff;">RDO #047 — Residência Silva</span>
                  <span style="font-size:.6rem;color:rgba(255,255,255,.28);">Hoje</span>
                </div>
                <span class="pill pg"><i class="bi bi-check-circle-fill" style="font-size:.55rem;"></i>Assinado</span>
                <span class="pill pb"><i class="bi bi-cloud-sun-fill" style="font-size:.55rem;"></i>28°C</span>
                <div class="prog mt-1"><div class="progf" style="width:68%;background:#10b981;"></div></div>
                <div style="display:flex;justify-content:space-between;font-size:.58rem;color:rgba(255,255,255,.24);margin-top:3px;"><span>Curva S — Avanço físico</span><span>68%</span></div>
              </div>
              <div style="display:flex;flex-wrap:wrap;gap:6px;font-size:.6rem;color:rgba(255,255,255,.5);margin-top:.6rem;">
                <span><i class="bi bi-calendar3" style="color:#6ab3ff;"></i> Cronograma</span>
                <span><i class="bi bi-wallet2" style="color:#6ab3ff;"></i> Financeiro</span>
                <span><i class="bi bi-boxes" style="color:#6ab3ff;"></i> Almoxarifado</span>
                <span><i class="bi bi-shield-check" style="color:#4fd88a;"></i> DDS</span>
                <span><i class="bi bi-exclamation-octagon" style="color:#fca5a5;"></i> Não Conformidades</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SOBRE -->
<section style="background:#fff;padding:80px 0;" id="sobre">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-3 text-center">
        <div class="avatar-wrap">
          <!-- Substitua pelo src da sua foto -->
          <img src="https://engmarcosalveres.com.br/wp-content/uploads/2026/05/fotoperfil-600x600.jpeg"
               alt="Marcos Alveres"
               style="width:100%;height:100%;object-fit:cover;"
               onerror="this.style.display='none';this.parentNode.innerHTML='👷';">
        </div>
        <h5 class="mb-1">Marcos Alveres</h5>
        <p style="color:var(--g400);font-size:.83rem;margin-bottom:.8rem;">Engenheiro Civil</p>
        <div class="d-flex justify-content-center gap-2">
          <a href="https://www.instagram.com/marcos.alveres/" target="_blank" class="fsoc"><i class="bi bi-instagram"></i></a>
          <a href="https://www.youtube.com/c/marcosalveres" target="_blank" class="fsoc"><i class="bi bi-youtube"></i></a>
          <a href="mailto:mmalveres@gmail.com" class="fsoc"><i class="bi bi-envelope-fill"></i></a>
        </div>
      </div>
      <div class="col-lg-9">
        <span class="sec-tag st-n">Quem criou isso</span>
        <h2 class="sec-h mb-3">Um engenheiro que sentiu o problema<br>e <span style="color:var(--sp);">criou</span> <span style="color:var(--ch);">as soluções</span></h2>
        <p style="color:var(--g500);line-height:1.8;margin-bottom:1.3rem;">Sou Engenheiro Civil, pós-graduado em Patologias de Edificações e atuo na <strong>Felipe Muniz Arquitetura</strong>. Desde 2017 criando soluções para quem trabalha com obras — a <strong>Super Planilha</strong> foi pioneira em orçamento automático no Brasil, e o <strong>CHRONA</strong> veio para digitalizar a gestão das obras de ponta a ponta.</p>
        <div class="row g-2 mb-3">
          <div class="col-4"><div class="sobre-stat"><div class="sn" style="color:var(--sp);">+6.000</div><div style="font-size:.76rem;color:var(--g400);">Planilhas vendidas</div></div></div>
          <div class="col-4"><div class="sobre-stat"><div class="sn" style="color:var(--ch);">9 anos</div><div style="font-size:.76rem;color:var(--g400);">De experiência (desde 2017)</div></div></div>
          <div class="col-4"><div class="sobre-stat"><div class="sn" style="color:var(--gold);">2</div><div style="font-size:.76rem;color:var(--g400);">Produtos criados</div></div></div>
        </div>
        <div>
          <span class="exp-tag">Pós-graduação em Patologias</span>
          <span class="exp-tag">Felipe Muniz Arquitetura</span>
          <span class="exp-tag">Orçamento SINAPI</span>
          <span class="exp-tag">Gestão Digital de Obras</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SUPER PLANILHA -->
<section class="sp-sec" id="planilha">
  <div class="container">
    <div class="row justify-content-between align-items-end mb-5">
      <div class="col-lg-7">
        <span class="sec-tag st-sp"><i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>Produto 01 — Super Planilha</span>
        <h2 class="sec-h">Orçamento profissional completo<br>em <span style="color:var(--sp);">minutos</span></h2>
        <p class="sec-sub">Informe as dimensões da estrutura e a planilha gera automaticamente: orçamento analítico SINAPI, cronograma físico-financeiro, curva ABC e memória de cálculo.</p>
      </div>
      <div class="col-lg-auto mt-3 mt-lg-0">
        <a href="https://payment.hotmart.com/L6589686T?checkoutMode=10" class="btn-buy" style="background:var(--sp-g);color:#fff;"><i class="bi bi-cart-fill"></i>Comprar agora</a>
      </div>
    </div>
    <div class="row g-4 align-items-center mb-5">
      <div class="col-lg-6">
        <img src="https://engmarcosalveres.com.br/wp-content/uploads/elementor/thumbs/1-p3herhvfqkg3xzpeb1lpu96mg5yjwugzfxljqsh954.png"
             alt="Super Planilha — Orçamento de Obras Automático v3.0"
             style="width:100%;border-radius:16px;box-shadow:0 16px 48px rgba(0,0,0,.12);display:block;">
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          <div class="col-12">
            <div class="sp-card">
              <div class="sp-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></div>
              <h5>Orçamento Sintético e Analítico</h5>
              <p>Item a item com composições SINAPI, materiais e mão de obra — gerado automaticamente ao informar as dimensões da estrutura.</p>
            </div>
          </div>
          <div class="col-6"><div class="sp-card"><div class="sp-icon"><i class="bi bi-calendar-week-fill"></i></div><h5>Cronograma Físico-Financeiro</h5><p>Padrão bancário com curva S e distribuição mensal.</p></div></div>
          <div class="col-6"><div class="sp-card"><div class="sp-icon"><i class="bi bi-bar-chart-fill"></i></div><h5>Curva ABC</h5><p>Identifique os itens mais relevantes com um clique.</p></div></div>
          <div class="col-6"><div class="sp-card"><div class="sp-icon"><i class="bi bi-arrow-repeat"></i></div><h5>Atualização SINAPI</h5><p>Vincule o arquivo e os preços atualizam automaticamente.</p></div></div>
          <div class="col-6"><div class="sp-card"><div class="sp-icon"><i class="bi bi-currency-exchange"></i></div><h5>Conversão de Moedas</h5><p>Qualquer moeda do mundo com a cotação informada.</p></div></div>
        </div>
      </div>
    </div>
    <!-- CTA Planilha + Garantia -->
    <div class="sp-cta">
      <div class="sp-cta-inner">
        <div class="row align-items-center g-4">
          <div class="col-lg-7">
            <h3 style="color:#fff;font-size:1.55rem;margin-bottom:.5rem;">Pronto para orçar em minutos?</h3>
            <p style="color:rgba(255,255,255,.65);margin-bottom:1rem;">Acesso imediato por e-mail · Microsoft Excel · Vídeo-tutorial completo · Suporte do engenheiro criador</p>
            <div style="display:flex;gap:1.2rem;flex-wrap:wrap;font-size:.8rem;color:rgba(255,255,255,.55);margin-bottom:.5rem;">
              <span><i class="bi bi-infinity" style="color:#ffd166;"></i> Pagamento único · Sem mensalidade</span>
              <span><i class="bi bi-lightning-fill" style="color:#ffd166;"></i> Acesso imediato após pagamento</span>
            </div>
            <!-- GARANTIA EM DESTAQUE -->
            <div class="g7-box">
              <div class="g7-ico">🛡️</div>
              <div>
                <h6>Garantia de 7 dias — exclusiva da Super Planilha</h6>
                <p>Se nos primeiros <strong>7 dias</strong> você não ficar satisfeito, basta enviar um e-mail para <strong>mmalveres@gmail.com</strong> e devolvemos <strong>100% do valor</strong> pago, sem perguntas e sem burocracia.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-5 text-center text-lg-end">
            <div style="color:rgba(255,255,255,.4);font-size:.7rem;text-transform:uppercase;letter-spacing:1px;">Pagamento único</div>
            <div class="price-big">R$ 249,90</div>
            <div style="color:rgba(255,255,255,.5);font-size:.8rem;margin-bottom:1.2rem;">ou 12x de R$ 24,35 no cartão</div>
            <a href="https://payment.hotmart.com/L6589686T?checkoutMode=10" class="btn-buy">
              <i class="bi bi-cart-fill"></i>Comprar Super Planilha
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DIVISOR -->
<section class="divisor">
  <div class="container divisor-inner">
    <h2>A planilha <span style="color:#4fd88a;">orça a obra.</span> O CHRONA <span style="color:#6ab3ff;">gerencia do início ao fim.</span></h2>
    <p>Do orçamento ao encerramento — diário, cronograma, medição, financeiro, almoxarifado, segurança e qualidade. Tudo integrado, tudo digital, tudo rastreável.</p>
    <div class="d-flex justify-content-center gap-2 flex-wrap">
      <div class="dpill dp-sp"><i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>Super Planilha → Orçamento em minutos</div>
      <div class="dpill dp-ar">→</div>
      <div class="dpill dp-ch"><i class="bi bi-buildings me-1"></i>CHRONA → 9 módulos integrados de gestão</div>
    </div>
  </div>
</section>

<!-- CHRONA -->
<section class="ch-sec" id="chrona">
  <div class="container">
    <div class="row align-items-center g-5 mb-5">
      <div class="col-lg-6">
        <span class="sec-tag st-ch"><i class="bi bi-buildings me-1"></i>Produto 02 — CHRONA</span>
        <h2 class="sec-h">Não é só um diário.<br>É <span style="color:var(--ch);">gestão completa</span> de obras.</h2>

        <!-- BADGE AERONÁUTICA -->
        <div style="background:linear-gradient(135deg,#0a1628,#0d2040);border:1px solid rgba(0,102,255,.3);border-radius:14px;padding:14px 18px;display:flex;align-items:center;gap:14px;margin-bottom:1.2rem;">
          <div style="font-size:2.2rem;flex-shrink:0;"><img src="./CHRONA/publico/img/Sabre_alado.png"  width="80"></div>
          <div>
            <div style="font-family:'Montserrat',sans-serif;font-weight:800;font-size:.88rem;color:#fff;margin-bottom:3px;">
              Utilizado pela Aeronáutica Brasileira
            </div>
            <div style="font-size:.78rem;color:rgba(255,255,255,.6);line-height:1.5;">
              Validado e adotado por empresas prestadoras de serviço à Força Aérea Brasileira. O nível de controle exigido pela Aeronáutica é o mais rigoroso do mercado.
            </div>
          </div>
        </div>

        <p class="sec-sub mb-4">Integra seu orçamento ao dia a dia da obra, gera medições automáticas, cronogramas, controle financeiro, almoxarifado e segurança — tudo em um único sistema.</p>
        <div class="trial-box">
          <i class="bi bi-gift-fill"></i>
          <div>
            <strong>30 dias completamente grátis — sem cartão de crédito</strong>
            <span>Acesso total ao sistema. Cancele a qualquer momento sem custo.</span>
          </div>
        </div>
        <div class="row g-2 mb-4">
          <div class="col-6"><div class="ch-feat"><div class="ch-icon"><i class="bi bi-journal-text"></i></div><div><h5>Diário de Obras (RDO)</h5><p>Fotos, clima, mão de obra e assinatura digital.</p></div></div></div>
          <div class="col-6"><div class="ch-feat"><div class="ch-icon"><i class="bi bi-bar-chart-steps"></i></div><div><h5>Medição Automática</h5><p>Boletim de medição PDF gerado direto do diário.</p></div></div></div>
          <div class="col-6"><div class="ch-feat"><div class="ch-icon"><i class="bi bi-calendar3"></i></div><div><h5>Cronograma Físico-Fin.</h5><p>Padrão Caixa/BB com barras de Gantt e % por serviço.</p></div></div></div>
          <div class="col-6"><div class="ch-feat"><div class="ch-icon"><i class="bi bi-graph-up-arrow"></i></div><div><h5>Curva S</h5><p>Planejado × Realizado mês a mês com desvio automático.</p></div></div></div>
          <div class="col-6"><div class="ch-feat"><div class="ch-icon"><i class="bi bi-wallet2"></i></div><div><h5>Fluxo de Caixa</h5><p>Receitas, despesas, alertas de etapas não faturadas e gráfico mensal.</p></div></div></div>
          <div class="col-6"><div class="ch-feat"><div class="ch-icon"><i class="bi bi-boxes"></i></div><div><h5>Almoxarifado</h5><p>Entrada e saída de materiais com saldo em estoque por item.</p></div></div></div>
          <div class="col-6"><div class="ch-feat"><div class="ch-icon"><i class="bi bi-shield-check"></i></div><div><h5>DDS — Segurança NR-18</h5><p>Diálogo Diário de Segurança com lista de presença digital.</p></div></div></div>
          <div class="col-6"><div class="ch-feat"><div class="ch-icon"><i class="bi bi-exclamation-octagon"></i></div><div><h5>Não Conformidades</h5><p>Registre, priorize e acompanhe problemas de qualidade até o fechamento.</p></div></div></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <a href="https://engmarcosalveres.com.br/CHRONA/publico/checkout.php?plano=construtora&billing=mensal&trial=1" class="btn-ch" style="width:auto;"><i class="bi bi-lightning-charge-fill"></i>Começar 30 dias grátis</a>
          <a href="https://engmarcosalveres.com.br/CHRONA/chrona.php" style="border:2px solid var(--ch);color:var(--ch);padding:.9rem 1.3rem;border-radius:10px;font-weight:700;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;transition:all .2s;" onmouseover="this.style.background='var(--ch)';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='var(--ch)'"><i class="bi bi-info-circle-fill"></i>Saiba mais</a>
          <a href="https://engmarcosalveres.com.br/CHRONA/publico/index.php" style="color:var(--g500);padding:.9rem .6rem;font-weight:600;font-size:.83rem;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;" onmouseover="this.style.color='var(--g900)'" onmouseout="this.style.color='var(--g500)'"><i class="bi bi-box-arrow-in-right"></i>Já tenho conta</a>
        </div>
      </div>
      <div class="col-lg-6">
        <img src="./CHRONA/publico/img/image_507456.png" alt="CHRONA — Sistema de Gestão de Obras"
             style="width:100%;border-radius:16px;box-shadow:0 20px 48px rgba(0,0,0,.18);display:block;">
      </div>
    </div>

    <!-- Planos -->
    <h3 class="text-center mb-1" style="font-size:1.5rem;" id="planos">Planos CHRONA</h3>
    <p class="text-center mb-4" style="color:var(--g400);font-size:.88rem;">Todos com 30 dias grátis · Sem cartão · Cancele quando quiser</p>
    <div class="row g-3 justify-content-center">
      <div class="col-lg-4 col-md-6">
        <div class="plano">
          <span class="sec-tag st-ch mb-2">Autônomo</span>
          <p style="color:var(--g500);font-size:.82rem;">Para engenheiros independentes</p>
          <div class="plano-p">R$ <?= $precMensalAut ?><span style="font-size:.95rem;font-weight:400;color:var(--g400);">/mês</span></div>
          <ul class="plano-feats">
            <li><i class="bi bi-check-circle-fill ok"></i>1 Usuário · 1 Obra Ativa</li>
            <li><i class="bi bi-check-circle-fill ok"></i>RDO Ilimitado</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Importar Orçamento (CSV/Excel)</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Fluxo de Caixa</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Almoxarifado</li>
            <li><i class="bi bi-check-circle-fill ok"></i>DDS — Segurança NR-18</li>
            <li><i class="bi bi-x-circle no"></i><span class="no-t">Cronograma + Curva S</span></li>
            <li><i class="bi bi-x-circle no"></i><span class="no-t">Medição Automática</span></li>
            <li><i class="bi bi-x-circle no"></i><span class="no-t">Não Conformidades</span></li>
          </ul>
          <a href="https://engmarcosalveres.com.br/CHRONA/publico/checkout.php?plano=autonomo&billing=mensal&trial=1" class="btn-pl-out">Começar grátis</a>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="plano pop">
          <div class="pop-tag">⭐ Mais Vendido</div>
          <span class="sec-tag st-ch mb-2">Construtora</span>
          <p style="color:var(--g500);font-size:.82rem;">Para pequenas e médias construtoras</p>
          <div class="plano-p ch">R$ <?= $precMensalCon ?><span style="font-size:.95rem;font-weight:400;color:var(--g400);">/mês</span></div>
          <ul class="plano-feats">
            <li><i class="bi bi-check-circle-fill ok"></i><strong>3 Usuários · Até 3 Obras</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i>Tudo do plano Autônomo</li>
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Cronograma Físico-Financeiro</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Curva S — Planejado × Realizado</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Medição Automática (PDF)</strong></li>
            <li><i class="bi bi-check-circle-fill ok"></i><strong>Não Conformidades</strong></li>
          </ul>
          <a href="https://engmarcosalveres.com.br/CHRONA/publico/checkout.php?plano=construtora&billing=mensal&trial=1" class="btn-pl-ch">Começar 30 dias grátis</a>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="plano">
          <span style="font-size:.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:.5rem;">Empresarial</span>
          <p style="color:var(--g500);font-size:.82rem;">Gestão total sem limites</p>
          <div class="plano-p" style="color:#b8860b;">R$ <?= $precMensalEmp ?><span style="font-size:.95rem;font-weight:400;color:var(--g400);">/mês</span></div>
          <ul class="plano-feats">
            <li><i class="bi bi-check-circle-fill ok"></i>Usuários e Obras Ilimitados</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Tudo do plano Construtora</li>
            <li><i class="bi bi-check-circle-fill ok"></i>Relatórios Avançados</li>
            <li><i class="bi bi-whatsapp ok"></i>Suporte WhatsApp Prioritário</li>
          </ul>
          <a href="https://engmarcosalveres.com.br/CHRONA/publico/checkout.php?plano=empresarial&billing=mensal&trial=1" class="btn-pl-out">Quero ilimitado</a>
        </div>
      </div>
    </div>
  <!-- SELOS DE CONFIANÇA -->
  <div style="padding:0 24px 60px;">
    <div style="background:linear-gradient(135deg,#0a1628,#0d2040);border-radius:20px;padding:2rem 2rem 1.5rem;text-align:center;">
      <p style="font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:1.4rem;">Confiado por instituições e profissionais exigentes</p>
      <div style="display:flex;justify-content:center;align-items:center;flex-wrap:wrap;gap:1.5rem 2.5rem;">
        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
          <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.08);border:1px solid rgba(0,102,255,.3);display:flex;align-items:center;justify-content:center;font-size:1.6rem;"><img src="./CHRONA/publico/img/Sabre_alado.png"
             style="width:100%;border-radius:16px;box-shadow:0 20px 48px rgba(0,0,0,.18);display:block;"></div>
          <span style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.75);text-align:center;">Aeronáutica<br>Brasileira</span>
        </div>
        <div style="width:1px;height:44px;background:rgba(255,255,255,.1);"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
          <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.08);border:1px solid rgba(0,102,255,.3);display:flex;align-items:center;justify-content:center;font-size:1.6rem;">🏗️</div>
          <span style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.75);text-align:center;">+6.000<br>Profissionais</span>
        </div>

        <div style="width:1px;height:44px;background:rgba(255,255,255,.1);"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
          <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.08);border:1px solid rgba(0,102,255,.3);display:flex;align-items:center;justify-content:center;font-size:1.6rem;">🔒</div>
          <span style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.75);text-align:center;">Pagamento<br>100% Seguro</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DEPOIMENTOS -->
<section class="dep" id="depoimentos">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-tag st-n">Depoimentos reais</span>
      <h2 class="sec-h">O que dizem nossos clientes</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4"><div class="dep-card"><div class="dep-strip ds-sp"><i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>Super Planilha</div><div class="stars">★★★★★</div><p class="dep-t">No meu primeiro orçamento já tirei o valor do produto! Economizei horas de trabalho. Parabéns pela ferramenta incrível.</p><div class="dep-auth"><div class="dep-av av-sp">PC</div><div><div class="dep-n">Pedro Cutrim</div><div class="dep-r">Engenheiro Civil</div></div></div></div></div>
      <div class="col-md-6 col-lg-4"><div class="dep-card"><div class="dep-strip ds-sp"><i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>Super Planilha</div><div class="stars">★★★★★</div><p class="dep-t">O que era em HORAS se tornou em MINUTOS. Recomendo 100%. Planilha de qualidade absurda e extremamente intuitiva.</p><div class="dep-auth"><div class="dep-av av-sp">AL</div><div><div class="dep-n">Anderson Lyhan</div><div class="dep-r">Arquiteto</div></div></div></div></div>
      <div class="col-md-6 col-lg-4"><div class="dep-card"><div class="dep-strip ds-ch"><i class="bi bi-buildings me-1"></i>CHRONA</div><div class="stars">★★★★★</div><p class="dep-t">O CHRONA integrou nosso orçamento com o diário. Antes levávamos dias para fechar a medição, agora fazemos em horas. Aprovado pelo banco na 1ª tentativa.</p><div class="dep-auth"><div class="dep-av av-ch">RF</div><div><div class="dep-n">Ricardo Fernandes</div><div class="dep-r">Eng. Civil · RF Construções</div></div></div></div></div>
      <div class="col-md-6 col-lg-4"><div class="dep-card"><div class="dep-strip ds-sp"><i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>Super Planilha</div><div class="stars">★★★★★</div><p class="dep-t">Nunca vi algo igual! Simples, prático e rápido. Agora ofereço orçamento profissional junto com meus projetos. Produto campeão.</p><div class="dep-auth"><div class="dep-av av-sp">GC</div><div><div class="dep-n">Gabriel Cardoso</div><div class="dep-r">Arquiteto</div></div></div></div></div>
      <div class="col-md-6 col-lg-4"><div class="dep-card"><div class="dep-strip ds-ch"><i class="bi bi-buildings me-1"></i>CHRONA</div><div class="stars">★★★★★</div><p class="dep-t">A integração do orçamento com o diário eliminou o retrabalho. As medições automáticas nos deram precisão incomparável. Suporte excelente!</p><div class="dep-auth"><div class="dep-av av-ch">AS</div><div><div class="dep-n">Amanda Santos</div><div class="dep-r">Gestora de Obras</div></div></div></div></div>
      <div class="col-md-6 col-lg-4"><div class="dep-card"><div class="dep-strip ds-sp"><i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>Super Planilha</div><div class="stars">★★★★★</div><p class="dep-t">A qualidade excede o preço muitas vezes. Estava procurando algo assim há muito tempo. Você salvou a minha pele.</p><div class="dep-auth"><div class="dep-av av-sp">MS</div><div><div class="dep-n">Melissa Santos</div><div class="dep-r">Engenheira Civil</div></div></div></div></div>

      <!-- Depoimento Aeronáutica -->
      <div class="col-12">
        <div class="dep-card" style="background:linear-gradient(135deg,#0a1628,#0d2040);border-color:rgba(0,102,255,.25);">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;">
            <div style="font-size:2rem;"><img src="./CHRONA/publico/img/Sabre_alado.png" width="80"></div>
            <div>
              <div style="font-family:'Montserrat',sans-serif;font-weight:800;font-size:.9rem;color:#fff;">Aeronáutica Brasileira</div>
              <div style="font-size:.75rem;color:rgba(255,255,255,.5);">Força Aérea Brasileira — Empresa prestadora de serviço</div>
            </div>
            <div class="stars ms-auto" style="color:#ffb300;">★★★★★</div>
          </div>
          <p class="dep-t" style="color:rgba(255,255,255,.8);font-size:.95rem;font-style:italic;">"O CHRONA atende ao nível de controle e rastreabilidade exigido pela Aeronáutica em obras e serviços. O sistema de diários, medições e cronogramas supre todas as exigências dos contratos com a Força Aérea Brasileira."</p>
          <div style="margin-top:1rem;padding-top:.8rem;border-top:1px solid rgba(255,255,255,.1);font-size:.75rem;color:rgba(255,255,255,.4);">
            <i class="bi bi-shield-fill-check me-1" style="color:#0066ff;"></i>
            Validado por empresas contratadas pela Aeronáutica Brasileira para execução de obras
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- GARANTIAS CLARAS -->
<section class="gar-sec" id="garantias">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-tag st-n">Sem letras miúdas</span>
      <h2 class="sec-h">As garantias de cada produto</h2>
      <p class="sec-sub mx-auto" style="max-width:500px;">Cada produto tem condições específicas — totalmente transparentes e sem surpresas.</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="gar-block gb-sp h-100">
          <div class="gb-hdr">
            <div class="gb-ico gi-sp" style="font-size:1.8rem;">📗</div>
            <div><h4>Super Planilha</h4><p>Orçamento de Obras Automático v3.0</p></div>
          </div>
          <div class="gb-item gb-item-sp">
            <i class="bi bi-shield-fill-check"></i>
            <div><strong>🛡️ 7 dias de garantia total — Risco zero</strong>
            <span>Se nos primeiros <strong>7 dias</strong> você não ficar 100% satisfeito, devolvo <strong>cada centavo</strong> do valor pago. Sem perguntas, sem burocracia. Basta enviar um e-mail para mmalveres@gmail.com.</span></div>
          </div>
          <div class="gb-item gb-item-sp">
            <i class="bi bi-lightning-charge-fill"></i>
            <div><strong>Acesso imediato após o pagamento</strong>
            <span>Link de download enviado por e-mail em instantes após a confirmação. Boleto: após compensação bancária.</span></div>
          </div>
          <div class="gb-item gb-item-sp">
            <i class="bi bi-infinity"></i>
            <div><strong>Pagamento único · Sem mensalidade</strong>
            <span>Pague uma única vez R$ 249,90 (ou 12x de R$ 24,35) e use para sempre. Sem nenhuma cobrança recorrente.</span></div>
          </div>
          <div class="gb-item gb-item-sp">
            <i class="bi bi-person-fill"></i>
            <div><strong>Suporte do próprio engenheiro</strong>
            <span>Dúvidas respondidas por mim, o Eng. Marcos Alveres, criador da ferramenta.</span></div>
          </div>
          <div class="mt-3">
            <a href="https://payment.hotmart.com/L6589686T?checkoutMode=10" class="btn-sp">
              <i class="bi bi-cart-fill"></i>Comprar com garantia de 7 dias — R$ 249,90
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="gar-block gb-ch h-100">
          <div class="gb-hdr">
            <div class="gb-ico gi-ch" style="font-size:1.8rem;">🏗️</div>
            <div><h4>CHRONA</h4><p>Sistema de Gestão de Obras</p></div>
          </div>
          <div class="gb-item gb-item-ch">
            <i class="bi bi-gift-fill"></i>
            <div><strong>🎁 30 dias completamente grátis</strong>
            <span>Teste o sistema por 30 dias <strong>sem pagar nada e sem informar cartão</strong>. Acesso completo a todas as funcionalidades do plano escolhido.</span></div>
          </div>
          <div class="gb-item gb-item-ch">
            <i class="bi bi-x-circle-fill"></i>
            <div><strong>Cancele quando quiser</strong>
            <span>Não gostou? Cancele antes do fim do trial sem nenhum custo. Sem fidelidade, sem multa, sem complicação.</span></div>
          </div>
          <div class="gb-item gb-item-ch">
            <i class="bi bi-calendar-check-fill"></i>
            <div><strong>Cobrança só depois dos 30 dias</strong>
            <span>A primeira cobrança só acontece após o período gratuito. Cancele antes e não paga nada.</span></div>
          </div>
          <div class="gb-item gb-item-ch">
            <i class="bi bi-lock-fill"></i>
            <div><strong>Pagamento seguro via Mercado Pago</strong>
            <span>PIX, cartão de crédito e boleto. Ambiente 100% seguro e criptografado.</span></div>
          </div>
          <div class="mt-3">
            <a href="https://engmarcosalveres.com.br/CHRONA/publico/checkout.php?plano=construtora&billing=mensal&trial=1" class="btn-ch">
              <i class="bi bi-lightning-charge-fill"></i>Começar 30 dias grátis — sem cartão
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cta-final">
  <div class="container cta-inner text-center">
    <h2 style="color:#fff;font-size:clamp(1.9rem,4vw,2.9rem);margin-bottom:.9rem;">Comece hoje. <span style="color:var(--gold);">Sem desculpas.</span></h2>
    <p style="color:rgba(255,255,255,.48);font-size:.98rem;line-height:1.75;margin-bottom:2.2rem;max-width:520px;margin-left:auto;margin-right:auto;">Mais de 6.000 engenheiros já usam nossas ferramentas. Escolha por onde começar.</p>
    <div class="cta-duo">
      <div class="cta-c cta-sp">
        <div style="font-size:1.9rem;margin-bottom:.4rem;">📗</div>
        <h4>Super Planilha</h4>
        <div class="cta-price">R$ 249,90</div>
        <div class="cta-det">Pagamento único · Sem mensalidade<br>✅ 7 dias de garantia total</div>
        <a href="https://payment.hotmart.com/L6589686T?checkoutMode=10" class="btn-wh btn-wh-sp"><i class="bi bi-cart-fill"></i>Comprar agora</a>
      </div>
      <div class="cta-c cta-ch">
        <div style="font-size:1.9rem;margin-bottom:.4rem;">🏗️</div>
        <h4>CHRONA</h4>
        <div class="cta-price">Grátis</div>
        <div class="cta-det">30 dias de teste · Sem cartão<br>A partir de R$ <?= $precMensalAut ?>/mês depois</div>
        <a href="https://engmarcosalveres.com.br/CHRONA/publico/checkout.php?plano=construtora&billing=mensal&trial=1" class="btn-wh btn-wh-ch"><i class="bi bi-lightning-charge-fill"></i>Testar grátis</a>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="row g-4 pb-4 mb-3" style="border-bottom:1px solid rgba(255,255,255,.07);">
      <div class="col-lg-4">
        <div class="f-logo"><div class="logo-m">M</div>Marcos Alveres</div>
        <p style="font-size:.82rem;color:var(--g500);line-height:1.7;max-width:270px;">Engenheiro Civil criador da Super Planilha de Orçamento e do CHRONA — sistema de gestão de obras digital.</p>
        <div class="d-flex gap-2 mt-3">
          <a href="https://www.instagram.com/marcos.alveres/" target="_blank" class="fsoc"><i class="bi bi-instagram"></i></a>
          <a href="https://www.youtube.com/c/marcosalveres" target="_blank" class="fsoc"><i class="bi bi-youtube"></i></a>
          <a href="mailto:mmalveres@gmail.com" class="fsoc"><i class="bi bi-envelope-fill"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <p class="fh"><span class="fdot" style="background:var(--sp);"></span>Super Planilha</p>
        <a href="#planilha" class="flink">O que é</a>
        <a href="#planilha" class="flink">Funcionalidades</a>
        <a href="https://payment.hotmart.com/L6589686T?checkoutMode=10" class="flink">Comprar</a>
        <a href="https://engmarcosalveres.com.br/calculadora/" class="flink">Calculadora</a>
      </div>
      <div class="col-6 col-lg-2">
        <p class="fh"><span class="fdot" style="background:var(--ch);"></span>CHRONA</p>
        <a href="#chrona" class="flink">O que é</a>
        <a href="#planos" class="flink">Planos</a>
        <a href="https://engmarcosalveres.com.br/CHRONA/publico/checkout.php?plano=construtora&billing=mensal&trial=1" class="flink">Trial grátis</a>
        <a href="https://engmarcosalveres.com.br/CHRONA/publico/index.php" class="flink">Login</a>
      </div>
      <div class="col-lg-4">
        <p class="fh">Contato</p>
        <p style="font-size:.82rem;color:var(--g500);line-height:2;">
          <i class="bi bi-envelope-fill me-2" style="color:var(--ch);"></i>mmalveres@gmail.com<br>
          <i class="bi bi-instagram me-2" style="color:var(--sp);"></i>@marcos.alveres<br>
          <i class="bi bi-youtube me-2" style="color:#ff0000;"></i>youtube.com/c/marcosalveres
        </p>
      </div>
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <p style="font-size:.77rem;color:var(--g600);margin:0;">© 2026 Eng. Marcos Alveres — Todos os direitos reservados</p>
      <div class="d-flex gap-2">
        <span style="background:var(--sp-l);color:var(--sp);font-size:.66rem;font-weight:700;padding:3px 9px;border-radius:20px;"><i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>Super Planilha</span>
        <span style="background:var(--ch-l);color:var(--ch);font-size:.66rem;font-weight:700;padding:3px 9px;border-radius:20px;"><i class="bi bi-buildings me-1"></i>CHRONA</span>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.addEventListener('scroll',()=>{
  document.querySelector('.navbar').style.boxShadow=
    window.scrollY>50?'0 4px 20px rgba(0,0,0,.12)':'0 2px 12px rgba(0,0,0,.07)';
});
</script>
</body>
</html>