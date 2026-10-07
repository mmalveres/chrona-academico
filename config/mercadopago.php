<?php
// ============================================================
// config/mercadopago.php
// Coloque este arquivo FORA da pasta publico/ por segurança.
// Caminho sugerido: CHRONA/config/mercadopago.php
// ============================================================

// ═══════════════════════════════════════════════════════
// MODO SANDBOX (testes) — use credenciais TEST-
// Para produção: troque pelas APP_USR- e MP_SANDBOX = false
// ═══════════════════════════════════════════════════════

// EXEMPLO — credenciais de SANDBOX (teste). Removidas as credenciais reais
// de produção para esta cópia de entrega acadêmica — nunca comitar tokens reais.
define('MP_ACCESS_TOKEN', 'TEST-0000000000000000-000000-00000000000000000000000000000000-000000000');
define('MP_PUBLIC_KEY',   'TEST-00000000-0000-0000-0000-000000000000');

define('MP_WEBHOOK_SECRET', 'EXEMPLO_SUBSTITUA_PELO_SEU_SECRET');
// Assinatura desabilitada — segurança garantida pela consulta direta à API do MP em cada webhook

// ── AMBIENTE ─────────────────────────────────────────────────
define('MP_SANDBOX', true);
// true  = modo de testes (não cobra de verdade)
// false = produção (cobra de verdade)

// ── URL DO SEU SISTEMA ────────────────────────────────────────
define('APP_URL', 'https://engmarcosalveres.com.br/CHRONA');
// ↑ URL base sem barra no final. Usada para montar o webhook URL.