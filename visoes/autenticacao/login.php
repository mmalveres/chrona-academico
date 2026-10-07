<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Entrar — CHRONA Gestão de Obras</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
:root {
  --ch:  #0066ff;
  --ch-g: linear-gradient(135deg,#0066ff,#00c9ff);
  --ok:  #10b981;
  --g50: #f9fafb; --g100:#f3f4f6; --g200:#e5e7eb;
  --g400:#9ca3af; --g500:#6b7280; --g600:#4b5563;
  --g700:#374151; --g900:#111827;
}
*{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;}
body{
  font-family:'Inter',sans-serif;
  background:linear-gradient(150deg,#f0f4ff 0%,#e8f0fe 45%,#f5f8ff 100%);
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:1rem;
  position:relative;
  overflow:hidden;
}
body::before{
  content:'';position:absolute;top:-180px;right:-180px;
  width:520px;height:520px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,102,255,.09),transparent 68%);
  pointer-events:none;
}
body::after{
  content:'';position:absolute;bottom:-140px;left:-120px;
  width:420px;height:420px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,201,255,.07),transparent 68%);
  pointer-events:none;
}

.login-wrap{width:100%;max-width:420px;position:relative;z-index:1;}

/* LOGO */
.logo-area{text-align:center;margin-bottom:1.8rem;}
.logo-icon{
  width:56px;height:56px;border-radius:14px;background:var(--ch-g);
  display:inline-flex;align-items:center;justify-content:center;
  font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.25rem;color:#fff;
  margin-bottom:.7rem;box-shadow:0 8px 22px rgba(0,102,255,.3);
}
.logo-name{
  font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.6rem;
  color:var(--g900);letter-spacing:-.02em;display:block;margin-bottom:.2rem;
}
.logo-sub{font-size:.78rem;color:var(--g400);font-weight:500;}

/* CARD */
.card-login{
  background:#fff;
  border:1px solid var(--g200);
  border-radius:20px;
  padding:2rem 2rem 1.6rem;
  box-shadow:0 8px 40px rgba(0,102,255,.08),0 2px 8px rgba(0,0,0,.06);
}
.card-title{
  font-family:'Montserrat',sans-serif;font-weight:800;font-size:1.15rem;
  color:var(--g900);margin-bottom:.3rem;
}
.card-sub{font-size:.82rem;color:var(--g400);margin-bottom:1.5rem;}

/* INPUTS */
.field{margin-bottom:1rem;}
.field label{
  display:block;font-size:.77rem;font-weight:600;
  color:var(--g600);margin-bottom:.4rem;letter-spacing:.2px;
}
.field .inp{
  width:100%;padding:.72rem 1rem;
  background:var(--g50);
  border:1.5px solid var(--g200);
  border-radius:10px;
  color:var(--g900);font-family:'Inter',sans-serif;font-size:.92rem;
  transition:all .2s;outline:none;
}
.field .inp::placeholder{color:var(--g400);}
.field .inp:focus{
  border-color:rgba(0,102,255,.5);
  background:#fff;
  box-shadow:0 0 0 3px rgba(0,102,255,.12);
}
.inp-wrap{position:relative;}
.inp-wrap .inp{padding-right:2.6rem;}
.inp-wrap .inp-icon{
  position:absolute;right:.85rem;top:50%;transform:translateY(-50%);
  color:var(--g400);font-size:.95rem;cursor:pointer;transition:color .2s;
}
.inp-wrap .inp-icon:hover{color:var(--g700);}

/* BOTÃO */
.btn-enter{
  display:block;width:100%;padding:.85rem;
  background:var(--ch-g);border:none;border-radius:10px;
  font-family:'Montserrat',sans-serif;font-weight:800;font-size:.95rem;
  color:#fff;cursor:pointer;transition:all .25s;
  box-shadow:0 6px 18px rgba(0,102,255,.28);
  letter-spacing:.2px;margin-top:.3rem;
}
.btn-enter:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(0,102,255,.42);}
.btn-enter:active{transform:translateY(0);}

/* LINKS */
.links-row{
  display:flex;justify-content:space-between;align-items:center;
  margin-top:1.2rem;
}
.link-sm{
  font-size:.78rem;color:var(--g400);text-decoration:none;
  font-weight:500;transition:color .2s;
}
.link-sm:hover{color:var(--ch);}

/* ERRO */
.alerta-erro{
  background:#fef2f2;border:1px solid #fecaca;
  border-radius:10px;padding:.65rem .9rem;
  display:flex;align-items:center;gap:.6rem;
  font-size:.82rem;color:#dc2626;margin-bottom:1rem;
}

/* RODAPÉ */
.login-footer{text-align:center;margin-top:1.4rem;font-size:.73rem;color:var(--g400);}
.login-footer a{color:var(--g400);text-decoration:none;transition:color .2s;}
.login-footer a:hover{color:var(--ch);}
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
    <div class="card-title">Bem-vindo de volta</div>
    <div class="card-sub">Entre com seu e-mail e senha para continuar</div>

    <?php if (isset($erro)): ?>
    <div class="alerta-erro" id="alerta-erro">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <?= htmlspecialchars($erro) ?>
    </div>
    <?php endif; ?>

    <form action="index.php?acao=fazer_login" method="POST" autocomplete="on">

      <div class="field">
        <label for="email"><i class="bi bi-envelope me-1"></i>E-mail</label>
        <input class="inp" type="email" id="email" name="email"
               placeholder="seu@email.com" required autocomplete="email">
      </div>

      <div class="field">
        <label for="senha"><i class="bi bi-lock me-1"></i>Senha</label>
        <div class="inp-wrap">
          <input class="inp" type="password" id="senha" name="senha"
                 placeholder="••••••••" required autocomplete="current-password">
          <i class="bi bi-eye inp-icon" id="toggle-senha" title="Mostrar senha"></i>
        </div>
      </div>

      <button type="submit" class="btn-enter">
        <i class="bi bi-box-arrow-in-right me-2"></i>Acessar o sistema
      </button>

    </form>

    <div class="links-row">
      <a href="../chrona.php" class="link-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar ao site
      </a>
      <a href="index.php?acao=esqueci_senha" class="link-sm">
        Esqueci a senha
      </a>
    </div>
  </div>

  <div class="login-footer">
    &copy; <?= date('Y') ?> CHRONA · Eng. Marcos Alveres &nbsp;·&nbsp;
    <a href="../chrona.php">Criar conta grátis</a>
  </div>

</div>

<script>
document.getElementById('toggle-senha').addEventListener('click', function () {
  const inp = document.getElementById('senha');
  const isPass = inp.type === 'password';
  inp.type = isPass ? 'text' : 'password';
  this.className = isPass ? 'bi bi-eye-slash inp-icon' : 'bi bi-eye inp-icon';
});

const alerta = document.getElementById('alerta-erro');
if (alerta) {
  setTimeout(() => {
    alerta.style.transition = 'opacity .5s';
    alerta.style.opacity = '0';
    setTimeout(() => alerta.remove(), 500);
  }, 5000);
}
</script>
</body>
</html>
