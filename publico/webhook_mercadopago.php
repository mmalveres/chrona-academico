<?php
// publico/webhook_mercadopago.php
// Recebe notificações do Mercado Pago quando pagamentos mudam de status.

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../config/mercadopago.php';

use Aplicacao\Configuracao\BancoDeDados;
use Aplicacao\Helpers\MercadoPagoHelper;

// ── SEU E-MAIL (igual ao processar_checkout.php) ─────────────────
define('EMAIL_ADMIN', 'mmalveres@gmail.com'); // ← ALTERE para o seu e-mail

// ── Log de debug ─────────────────────────────────────────────────
$logFile = __DIR__ . '/../logs/webhook_mp.log';
@mkdir(dirname($logFile), 0755, true);

function logWebhook(string $msg): void
{
    global $logFile;
    @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n", FILE_APPEND);
}

// ── Lê dados da requisição ────────────────────────────────────────
$body    = file_get_contents('php://input');
$data    = json_decode($body, true) ?? [];
$headers = function_exists('getallheaders') ? getallheaders() : [];

logWebhook("Recebido: " . $body);

// ── Valida assinatura ─────────────────────────────────────────────
$xSignature = $headers['X-Signature']
           ?? $headers['x-signature']
           ?? $_SERVER['HTTP_X_SIGNATURE']
           ?? '';
$xRequestId = $headers['X-Request-Id']
           ?? $headers['x-request-id']
           ?? $_SERVER['HTTP_X_REQUEST_ID']
           ?? '';
$dataId = $data['data']['id'] ?? $_GET['data.id'] ?? '';

logWebhook("X-Signature: {$xSignature} | X-Request-Id: {$xRequestId} | dataId: {$dataId}");

if ($xSignature && !MercadoPagoHelper::verificarWebhook($xSignature, $xRequestId, $dataId)) {
    logWebhook("ASSINATURA INVÁLIDA — ignorando.");
    http_response_code(401);
    exit;
}

// ── Processa apenas eventos de pagamento ─────────────────────────
$tipo = $data['type'] ?? $_GET['type'] ?? '';
if ($tipo !== 'payment') {
    http_response_code(200);
    exit;
}

$paymentId = $data['data']['id'] ?? $_GET['data.id'] ?? '';
if (!$paymentId) {
    logWebhook("Sem payment_id.");
    http_response_code(200);
    exit;
}

// ── Consulta status real na API do MP ────────────────────────────
$pagamento = MercadoPagoHelper::consultarPagamento($paymentId);
$status    = $pagamento['status']             ?? 'unknown';
$extRef    = $pagamento['external_reference'] ?? '';
$valor     = (float)($pagamento['transaction_amount'] ?? 0);

logWebhook("Payment {$paymentId} | status: {$status} | ref: {$extRef} | valor: {$valor}");

// ── Identifica empresa pelo external_reference ────────────────────
// Formato: empresa_<ID>_<plano>_<billing>
if (!preg_match('/^empresa_(\d+)_(\w+)_(\w+)$/', $extRef, $m)) {
    logWebhook("external_reference inválido: {$extRef}");
    http_response_code(200);
    exit;
}

[, $idEmpresa, $plano, $billing] = $m;
$idEmpresa = (int) $idEmpresa;

try {
    $conn = BancoDeDados::getConexao();

    switch ($status) {

        // ── APROVADO → ativa acesso + envia e-mails ──────────────
        case 'approved':
            $dias       = $billing === 'anual' ? 365 : 30;
            $vencimento = date('Y-m-d', strtotime("+{$dias} days"));

            $conn->prepare(
                "UPDATE empresas SET ativo = 1, plano = ?, data_vencimento = ? WHERE id = ?"
            )->execute([$plano, $vencimento, $idEmpresa]);

            // Busca dados do cliente para os e-mails
            $u = $conn->prepare("SELECT nome, email FROM usuarios WHERE id_empresa = ? LIMIT 1");
            $u->execute([$idEmpresa]);
            $usuario = $u->fetch(\PDO::FETCH_ASSOC);

            if ($usuario) {
                // E-mail de boas-vindas para o CLIENTE
                enviarEmailBoasVindas(
                    $usuario['email'],
                    $usuario['nome'],
                    ucfirst($plano),
                    $plano,
                    $billing
                );

                // Descobre método de pagamento para a notificação
                $metodo = ucfirst($pagamento['payment_method_id'] ?? 'desconhecido');
                if (isset($pagamento['payment_type_id'])) {
                    $tipos = ['credit_card'=>'Cartão de Crédito','debit_card'=>'Cartão de Débito','pix'=>'PIX','bolbradesco'=>'Boleto','account_money'=>'Saldo MP'];
                    $metodo = $tipos[$pagamento['payment_type_id']] ?? $metodo;
                }

                // Notificação de venda para o ADMIN
                notificarAdmin(
                    $usuario['nome'],
                    $usuario['email'],
                    $plano,
                    $billing,
                    $valor,
                    $metodo
                );
            }

            logWebhook("Empresa {$idEmpresa} ATIVADA até {$vencimento}.");
            break;

        // ── CANCELADO / RECUSADO ──────────────────────────────────
        case 'cancelled':
        case 'rejected':
            $chk = $conn->prepare("SELECT ativo FROM empresas WHERE id = ?");
            $chk->execute([$idEmpresa]);
            $emp = $chk->fetch(\PDO::FETCH_ASSOC);
            if ($emp) {
                if (!$emp['ativo']) {
                    // Nunca foi ativada — apaga para liberar o e-mail para nova tentativa
                    $conn->prepare("DELETE FROM usuarios WHERE id_empresa = ?")->execute([$idEmpresa]);
                    $conn->prepare("DELETE FROM empresas WHERE id = ?")->execute([$idEmpresa]);
                    logWebhook("Empresa {$idEmpresa} REMOVIDA (pagamento rejeitado antes de ativação).");
                } else {
                    // Conta ativa com pagamento rejeitado (renovação) — só bloqueia
                    $conn->prepare("UPDATE empresas SET ativo = 0 WHERE id = ?")->execute([$idEmpresa]);
                    logWebhook("Empresa {$idEmpresa} BLOQUEADA (status: {$status}).");
                }
            }
            break;

        // ── CHARGEBACK → bloqueia imediatamente ──────────────────
        case 'charged_back':
            $conn->prepare("UPDATE empresas SET ativo = 0 WHERE id = ?")->execute([$idEmpresa]);
            logWebhook("Empresa {$idEmpresa} BLOQUEADA por chargeback.");
            break;

        // ── REEMBOLSO → bloqueia imediatamente ───────────────────
        case 'refunded':
            $conn->prepare(
                "UPDATE empresas SET ativo = 0, data_vencimento = CURDATE() WHERE id = ?"
            )->execute([$idEmpresa]);

            logWebhook("Empresa {$idEmpresa} REEMBOLSADA e bloqueada.");
            break;

        default:
            // pending, in_process, authorized — aguarda
            logWebhook("Status '{$status}' para empresa {$idEmpresa} — sem ação.");
    }

} catch (\Exception $e) {
    logWebhook("ERRO: " . $e->getMessage());
    http_response_code(500);
    exit;
}

http_response_code(200);
echo 'OK';

// ════════════════════════════════════════════════════════════════
// FUNÇÕES DE E-MAIL (idênticas ao processar_checkout.php)
// ════════════════════════════════════════════════════════════════

function enviarEmailBoasVindas(
    string $email, string $nome, string $planoLabel,
    string $plano, string $billing
): void {
    $url = defined('APP_URL') ? APP_URL . '/publico/index.php' : '';

    $precos = [
        'autonomo'    => ['mensal' => 49,  'anual' => 468],
        'construtora' => ['mensal' => 149, 'anual' => 1428],
        'empresarial' => ['mensal' => 250, 'anual' => 2400],
    ];
    $valor    = $precos[$plano][$billing] ?? 0;
    $valorFmt = $valor > 0 ? 'R$ ' . number_format($valor, 2, ',', '.') : 'Gratuito';

    $assunto = "Bem-vindo ao CHRONA - acesso liberado";

    $corpoTexto  = "Ola, {$nome},\n\n";
    $corpoTexto .= "Seu pagamento foi confirmado e seu acesso ao CHRONA ja esta liberado.\n\n";
    $corpoTexto .= "Resumo da assinatura:\n";
    $corpoTexto .= "Plano: {$planoLabel}\n";
    $corpoTexto .= "Valor: {$valorFmt}/" . ($billing === 'anual' ? 'ano' : 'mes') . "\n";
    $corpoTexto .= "E-mail de acesso: {$email}\n";
    $corpoTexto .= "Senha: a que voce cadastrou na compra\n\n";
    $corpoTexto .= "Para comecar, acesse:\n";
    $corpoTexto .= "{$url}\n\n";
    $corpoTexto .= "Dicas para comecar:\n";
    $corpoTexto .= "1. Faca login com seu e-mail e senha\n";
    $corpoTexto .= "2. Cadastre sua primeira obra\n";
    $corpoTexto .= "3. Importe seu orcamento em CSV\n";
    $corpoTexto .= "4. Comece a preencher o Diario de Obras\n\n";
    $corpoTexto .= "Qualquer duvida, e so responder este e-mail.\n\n";
    $corpoTexto .= "Atenciosamente,\n";
    $corpoTexto .= "Equipe CHRONA\n";
    $corpoTexto .= "https://engmarcosalveres.com.br";

    $nomeSeguro  = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $planoSeguro = htmlspecialchars($planoLabel, ENT_QUOTES, 'UTF-8');
    $emailSeguro = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $urlSegura   = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $periodo     = $billing === 'anual' ? 'ano' : 'mes';

    $corpoHtml = <<<HTML
<p style="margin:0 0 16px;">Ola, <strong>{$nomeSeguro}</strong>,</p>
<p style="margin:0 0 20px;">Seu pagamento foi confirmado e seu acesso ao <strong>CHRONA</strong> ja esta liberado.</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;background-color:#f9fafb;border-radius:8px;margin:0 0 24px;">
<tr><td style="padding:16px 20px;font-size:14px;color:#374151;">
<strong>Plano:</strong> {$planoSeguro}<br>
<strong>Valor:</strong> {$valorFmt}/{$periodo}<br>
<strong>E-mail de acesso:</strong> {$emailSeguro}<br>
<strong>Senha:</strong> a que voce cadastrou na compra
</td></tr>
</table>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
<tr><td style="background-color:#0066ff;border-radius:8px;">
<a href="{$urlSegura}" style="display:inline-block;padding:12px 28px;color:#ffffff;font-weight:bold;font-size:14px;text-decoration:none;">Acessar o CHRONA</a>
</td></tr>
</table>
<p style="margin:0 0 8px;font-weight:bold;">Dicas para comecar:</p>
<ol style="margin:0 0 20px;padding-left:20px;color:#374151;">
<li style="margin-bottom:4px;">Faca login com seu e-mail e senha</li>
<li style="margin-bottom:4px;">Cadastre sua primeira obra</li>
<li style="margin-bottom:4px;">Importe seu orcamento em CSV</li>
<li>Comece a preencher o Diario de Obras</li>
</ol>
<p style="margin:0;">Qualquer duvida, e so responder este e-mail.</p>
HTML;

    \Aplicacao\Helpers\EmailHelper::enviarHtml($email, $nome, $assunto, $corpoHtml, $corpoTexto);
}

function notificarAdmin(
    string $nomeCliente, string $emailCliente,
    string $plano, string $billing,
    float $valor, string $metodo
): void {
    if (!defined('EMAIL_ADMIN') || empty(EMAIL_ADMIN)) return;

    $valorFmt  = $valor > 0
        ? 'R$ ' . number_format($valor, 2, ',', '.')
        : 'Gratuito (Trial)';

    $painelUrl = defined('APP_URL')
        ? APP_URL . '/publico/index.php?acao=listar_empresas'
        : '';

    $assunto = "Nova venda CHRONA - " . ucfirst($plano) . " {$valorFmt}";

    $corpo  = "Nova venda realizada no CHRONA.\n\n";
    $corpo .= "Cliente: {$nomeCliente}\n";
    $corpo .= "E-mail:  {$emailCliente}\n";
    $corpo .= "Plano:   " . ucfirst($plano) . " ({$billing})\n";
    $corpo .= "Metodo:  {$metodo}\n";
    $corpo .= "Valor:   {$valorFmt}\n";
    $corpo .= "Data/hora: " . date('d/m/Y H:i:s') . "\n\n";

    if ($painelUrl) {
        $corpo .= "Ver no painel de clientes:\n{$painelUrl}\n\n";
    }

    $corpo .= "Equipe CHRONA";

    \Aplicacao\Helpers\EmailHelper::enviar(EMAIL_ADMIN, 'Admin CHRONA', $assunto, $corpo);
}