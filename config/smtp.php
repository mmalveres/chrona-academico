<?php
// ============================================================
// config/smtp.php
// Coloque este arquivo FORA da pasta publico/ por segurança.
// Credenciais SMTP da caixa contato@engmarcosalveres.com.br
// (cPanel da hospedagem) — usadas pelo EmailHelper para envio
// autenticado, no lugar do mail() nativo do PHP (que não estava
// entregando e-mails em produção).
// ============================================================

define('SMTP_HOST',   'mail.exemplo.com.br');
define('SMTP_PORT',   465);
define('SMTP_SECURE', 'ssl'); // 'ssl' (porta 465) ou 'tls' (porta 587)
define('SMTP_USER',   'seu-email@exemplo.com.br');
define('SMTP_PASS',   'EXEMPLO_SUBSTITUA_PELA_SENHA_REAL');

define('SMTP_FROM_EMAIL', 'seu-email@exemplo.com.br');
define('SMTP_FROM_NAME',  'CHRONA');
