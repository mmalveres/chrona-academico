<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Senha — CHRONA</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
:root{--ch:#0066ff;--ch-g:linear-gradient(135deg,#0066ff,#00c9ff);
      --g50:#f9fafb;--g200:#e5e7eb;--g400:#9ca3af;--g600:#4b5563;--g900:#111827;}
*{box-sizing:border-box;margin:0;padding:0;}html,body{height:100%;}
body{font-family:'Inter',sans-serif;background:linear-gradient(150deg,#f0f4ff 0%,#e8f0fe 45%,#f5f8ff 100%);
     min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;
     position:relative;overflow:hidden;}
body::before{content:'';position:absolute;top:-180px;right:-180px;width:520px;height:520px;
  border-radius:50%;background:radial-gradient(circle,rgba(0,102,255,.09),transparent 68%);pointer-events:none;}
body::after{content:'';position:absolute;bottom:-140px;left:-120px;width:420px;height:420px;
  border-radius:50%;background:radial-gradient(circle,rgba(0,201,255,.07),transparent 68%);pointer-events:none;}
.login-wrap{width:100%;max-width:420px;position:relative;z-index:1;}
.logo-area{text-align:center;margin-bottom:1.8rem;}
.logo-icon{width:52px;height:52px;border-radius:13px;background:var(--ch-g);display:inline-flex;
           align-items:center;justify-content:center;font-family:'Montserrat',sans-serif;
           font-weight:900;font-size:1.1rem;color:#fff;margin-bottom:.65rem;
           box-shadow:0 8px 20px rgba(0,102,255,.28);}
.logo-name{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.5rem;
           color:var(--g900);letter-spacing:-.02em;display:block;margin-bottom:.2rem;}
.logo-sub{font-size:.76rem;color:var(--g400);}
.card-login{background:#fff;border:1px solid var(--g200);border-radius:20px;padding:2rem 2rem 1.6rem;
            box-shadow:0 8px 40px rgba(0,102,255,.08),0 2px 8px rgba(0,0,0,.06);}
.card-title{font-family:'Montserrat',sans-serif;font-weight:800;font-size:1.1rem;color:var(--g900);margin-bottom:.3rem;}
.card-sub{font-size:.81rem;color:var(--g400);margin-bottom:1.5rem;line-height:1.6;}
.field{margin-bottom:1rem;}
.field label{display:block;font-size:.77rem;font-weight:600;color:var(--g600);margin-bottom:.4rem;letter-spacing:.2px;}
.field .inp{width:100%;padding:.72rem 1rem;background:var(--g50);border:1.5px solid var(--g200);
            border-radius:10px;color:var(--g900);font-family:'Inter',sans-serif;font-size:.9rem;
            transition:all .2s;outline:none;}
.field .inp::placeholder{color:var(--g400);}
.field .inp:focus{border-color:rgba(0,102,255,.5);background:#fff;box-shadow:0 0 0 3px rgba(0,102,255,.12);}
.btn-enter{display:block;width:100%;padding:.85rem;background:var(--ch-g);border:none;border-radius:10px;
           font-family:'Montserrat',sans-serif;font-weight:800;font-size:.93rem;color:#fff;
           cursor:pointer;transition:all .25s;box-shadow:0 6px 18px rgba(0,102,255,.25);margin-top:.3rem;}
.btn-enter:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(0,102,255,.4);}
.alerta-erro{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:.65rem .9rem;
             display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:#dc2626;margin-bottom:1rem;}
.alerta-ok{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:.65rem .9rem;
           display:flex;align-items:flex-start;gap:.6rem;font-size:.82rem;color:#15803d;margin-bottom:1rem;}
.link-sm{font-size:.78rem;color:var(--g400);text-decoration:none;font-weight:500;transition:color .2s;}
.link-sm:hover{color:var(--ch);}
.login-footer{text-align:center;margin-top:1.4rem;font-size:.72rem;color:var(--g400);}
</style>
</head>
<body>
<div class="login-wrap">

  <div class="logo-area">
    <div class="logo-icon">CH</div>
    <span class="logo-name">CHRONA</span>
    <span class="logo-sub">Sistema de Gestão de Obras</span>
  </div>

  <div class="card-login">
    <div class="card-title">Recuperar acesso</div>
    <div class="card-sub">Digite seu e-mail cadastrado e enviaremos um link para redefinir sua senha.</div>

    <?php if (isset($msg_sucesso)): ?>
    <div class="alerta-ok">
      <i class="bi bi-check-circle-fill mt-1"></i>
      <span><?= htmlspecialchars($msg_sucesso) ?></span>
    </div>
    <?php endif; ?>

    <?php if (isset($erro)): ?>
    <div class="alerta-erro">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <?= htmlspecialchars($erro) ?>
    </div>
    <?php endif; ?>

    <form action="index.php?acao=enviar_reset" method="POST">
      <div class="field">
        <label for="email"><i class="bi bi-envelope me-1"></i>E-mail cadastrado</label>
        <input class="inp" type="email" id="email" name="email" placeholder="seu@email.com" required autocomplete="email">
      </div>
      <button type="submit" class="btn-enter">
        <i class="bi bi-send-fill me-2"></i>Enviar link de recuperação
      </button>
    </form>

    <div style="text-align:center;margin-top:1.2rem;">
      <a href="index.php?acao=login" class="link-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar ao login
      </a>
    </div>
  </div>

  <div class="login-footer">&copy; <?= date('Y') ?> CHRONA · Eng. Marcos Alveres</div>
</div>
</body>
</html>
