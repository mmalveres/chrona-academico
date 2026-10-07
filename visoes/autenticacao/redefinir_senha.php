<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nova Senha — CHRONA</title>
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
.card-sub{font-size:.81rem;color:var(--g400);margin-bottom:1.5rem;}
.field{margin-bottom:1rem;}
.field label{display:block;font-size:.77rem;font-weight:600;color:var(--g600);margin-bottom:.4rem;letter-spacing:.2px;}
.field .inp{width:100%;padding:.72rem 1rem;background:var(--g50);border:1.5px solid var(--g200);
            border-radius:10px;color:var(--g900);font-family:'Inter',sans-serif;font-size:.9rem;
            transition:all .2s;outline:none;}
.field .inp::placeholder{color:var(--g400);}
.field .inp:focus{border-color:rgba(0,102,255,.5);background:#fff;box-shadow:0 0 0 3px rgba(0,102,255,.12);}
.inp-wrap{position:relative;}
.inp-wrap .inp{padding-right:2.6rem;}
.inp-wrap .inp-icon{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);
                    color:var(--g400);font-size:.92rem;cursor:pointer;transition:color .2s;}
.inp-wrap .inp-icon:hover{color:var(--g900);}
.btn-enter{display:block;width:100%;padding:.85rem;background:var(--ch-g);border:none;border-radius:10px;
           font-family:'Montserrat',sans-serif;font-weight:800;font-size:.93rem;color:#fff;
           cursor:pointer;transition:all .25s;box-shadow:0 6px 18px rgba(0,102,255,.25);margin-top:.3rem;}
.btn-enter:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(0,102,255,.4);}
.alerta-erro{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:.65rem .9rem;
             display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:#dc2626;margin-bottom:1rem;}
.hint{font-size:.73rem;margin-top:.3rem;}
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
    <div class="card-title">Criar nova senha</div>
    <div class="card-sub">Defina uma senha segura para acessar sua conta.</div>

    <?php if (isset($erro)): ?>
    <div class="alerta-erro">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <?= htmlspecialchars($erro) ?>
    </div>
    <?php endif; ?>

    <form action="index.php?acao=salvar_nova_senha" method="POST">
      <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

      <div class="field">
        <label for="nova_senha"><i class="bi bi-lock me-1"></i>Nova senha</label>
        <div class="inp-wrap">
          <input class="inp" type="password" id="nova_senha" name="nova_senha"
                 placeholder="Mínimo 6 caracteres" required minlength="6">
          <i class="bi bi-eye inp-icon" data-target="nova_senha"></i>
        </div>
      </div>

      <div class="field">
        <label for="confirmar_senha"><i class="bi bi-check-lg me-1"></i>Confirmar senha</label>
        <div class="inp-wrap">
          <input class="inp" type="password" id="confirmar_senha" name="confirmar_senha"
                 placeholder="Repita a nova senha" required minlength="6">
          <i class="bi bi-eye inp-icon" data-target="confirmar_senha"></i>
        </div>
        <div class="hint" id="hint-match"></div>
      </div>

      <button type="submit" class="btn-enter">
        <i class="bi bi-check-circle-fill me-2"></i>Salvar nova senha
      </button>
    </form>
  </div>

  <div class="login-footer">&copy; <?= date('Y') ?> CHRONA · Eng. Marcos Alveres</div>
</div>

<script>
document.querySelectorAll('.inp-icon[data-target]').forEach(icon => {
  icon.addEventListener('click', function () {
    const inp = document.getElementById(this.dataset.target);
    const isPass = inp.type === 'password';
    inp.type = isPass ? 'text' : 'password';
    this.className = (isPass ? 'bi bi-eye-slash' : 'bi bi-eye') + ' inp-icon';
    this.setAttribute('data-target', this.dataset.target);
  });
});

const s1 = document.getElementById('nova_senha');
const s2 = document.getElementById('confirmar_senha');
const hint = document.getElementById('hint-match');
s2.addEventListener('input', () => {
  if (!s2.value) { hint.textContent = ''; return; }
  if (s2.value === s1.value) {
    hint.textContent = '✓ Senhas coincidem';
    hint.style.color = '#15803d';
  } else {
    hint.textContent = '✗ As senhas não coincidem';
    hint.style.color = '#dc2626';
  }
});
</script>
</body>
</html>
