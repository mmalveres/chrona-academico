<?php
// publico/processar_checkout.php
header('Content-Type: application/json');

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../config/mercadopago.php';
use Aplicacao\Helpers\PrecosHelper;

define('EMAIL_ADMIN', 'mmalveres@gmail.com');
use Aplicacao\Configuracao\BancoDeDados;
use Aplicacao\Helpers\MercadoPagoHelper;

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { responder(false, 'Dados inválidos.'); }

$acao            = $input['acao']       ?? '';
$plano           = $input['plano']      ?? 'construtora';
$billing         = $input['billing']    ?? 'mensal';
$nome            = trim(strip_tags($input['nome']    ?? ''));
$empresa         = trim(strip_tags($input['empresa'] ?? $nome));
$email           = filter_var(trim($input['email']   ?? ''), FILTER_VALIDATE_EMAIL);
$cpf             = preg_replace('/\D/', '', $input['cpf'] ?? '');
$senha           = $input['senha']      ?? '';
$cardToken        = $input['card_token']        ?? '';
$parcelas         = (int)($input['parcelas']         ?? 1);
$isUpgrade        = (bool)($input['is_upgrade']       ?? false);
$idEmpresaUpgrade = (int)($input['id_empresa']        ?? 0);
$whatsapp         = preg_replace('/\D/', '', $input['whatsapp'] ?? '');
$deviceId         = $input['device_id']         ?? '';
$paymentMethodId  = $input['payment_method_id'] ?? '';
$issuerId         = $input['issuer_id']         ?? '';

// Para upgrades, a sessão é a fonte de verdade do id_empresa.
// Isso cobre: session com id_empresa correto mas JS enviou 0,
// sessão expirada (trata como novo cadastro), ou tentativa de manipulação do payload.
if ($isUpgrade) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['usuario_id'])) {
        // Sessão expirou — trata como novo cadastro
        $isUpgrade = false;
    } else {
        // Usa sempre o id_empresa da sessão (mais seguro)
        $idEmpresaUpgrade = (int)($_SESSION['id_empresa'] ?? $idEmpresaUpgrade);
    }
}

if (!$nome || !$email) {
    responder(false, 'Preencha todos os campos obrigatórios.');
}
if (!$isUpgrade && strlen($senha) < 6) {
    responder(false, 'Senha deve ter pelo menos 6 caracteres.');
}

$precosDB = PrecosHelper::getPrecos();
$precos = [
    'autonomo'    => ['mensal' => $precosDB['autonomo']['mensal'],    'anual' => $precosDB['autonomo']['anual_total']],
    'construtora' => ['mensal' => $precosDB['construtora']['mensal'], 'anual' => $precosDB['construtora']['anual_total']],
    'empresarial' => ['mensal' => $precosDB['empresarial']['mensal'], 'anual' => $precosDB['empresarial']['anual_total']],
];
if (!isset($precos[$plano])) $plano = 'construtora';
$valor     = $precos[$plano][$billing] ?? $precos[$plano]['mensal'];
$descricao = 'CHRONA - Plano ' . ucfirst($plano) . ' (' . $billing . ')';

try {
    $conn = BancoDeDados::getConexao();

    // Para novos cadastros: bloquear e-mail duplicado
    if (!$isUpgrade) {
        $chk = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetchColumn()) {
            responder(false, 'Este e-mail ja esta cadastrado. Faca login ou use outro e-mail.');
        }
    } else {
        // Upgrade: validar que a empresa existe
        if (!$idEmpresaUpgrade) responder(false, 'Dados de upgrade invalidos.');
        $chkEmp = $conn->prepare("SELECT id FROM empresas WHERE id = ?");
        $chkEmp->execute([$idEmpresaUpgrade]);
        if (!$chkEmp->fetchColumn()) responder(false, 'Empresa nao encontrada.');
    }

    // Helper: obtém/cria o ID de empresa conforme contexto
    $resolverEmpresa = function() use ($conn, $isUpgrade, $idEmpresaUpgrade, $nome, $empresa, $email, $senha, $plano, $cpf, $whatsapp): int {
        if ($isUpgrade) return $idEmpresaUpgrade;
        return criarContaBanco($conn, $nome, $empresa, $email, $senha, $plano, 0, false, $cpf, $whatsapp);
    };

    if ($acao === 'trial') {
        if ($isUpgrade) {
            atualizarPlanoEmpresa($conn, $idEmpresaUpgrade, $plano, $billing);
            responder(true, 'Plano atualizado!', ['redirect' => 'index.php?acao=painel']);
        }
        $idEmpresa = criarContaBanco($conn, $nome, $empresa, $email, $senha, $plano, 30, true, $cpf, $whatsapp);
        enviarEmailBoasVindas($email, $nome, 'Trial 30 dias gratis');
        responder(true, 'Trial ativado!', ['redirect' => 'index.php?acao=painel']);
    }

    if ($acao === 'pix') {
        $idEmpresa  = $resolverEmpresa();
        $referencia = "empresa_{$idEmpresa}_{$plano}_{$billing}";
        $resultado  = MercadoPagoHelper::criarPix($valor, $email, $nome, $cpf, $descricao, $referencia);

        if (!empty($resultado['error']) || empty($resultado['id'])) {
            if (!$isUpgrade) {
                $conn->prepare("DELETE FROM usuarios WHERE id_empresa = ?")->execute([$idEmpresa]);
                $conn->prepare("DELETE FROM empresas WHERE id = ?")->execute([$idEmpresa]);
            }
            responder(false, 'Erro ao gerar PIX: ' . ($resultado['message'] ?? 'Tente novamente.'));
        }

        salvarPaymentId($conn, $idEmpresa, $resultado['id']);
        $pix = $resultado['point_of_interaction']['transaction_data'] ?? [];

        responder(true, 'PIX gerado!', [
            'payment_id'     => $resultado['id'],
            'qr_code'        => $pix['qr_code']        ?? '',
            'qr_code_base64' => $pix['qr_code_base64'] ?? '',
            'status'         => $resultado['status'],
        ]);
    }

    if ($acao === 'cartao') {
        if (empty($cardToken)) responder(false, 'Token do cartao nao recebido. Recarregue a pagina.');

        $idEmpresa  = $resolverEmpresa();
        $referencia = "empresa_{$idEmpresa}_{$plano}_{$billing}";
        $resultado  = MercadoPagoHelper::criarCartao($valor, $cardToken, $parcelas, $email, $nome, $cpf, $descricao, $referencia, $whatsapp, $paymentMethodId);

        if (!empty($resultado['error']) || empty($resultado['id'])) {
            if (!$isUpgrade) {
                $conn->prepare("DELETE FROM usuarios WHERE id_empresa = ?")->execute([$idEmpresa]);
                $conn->prepare("DELETE FROM empresas WHERE id = ?")->execute([$idEmpresa]);
            }
            responder(false, 'Pagamento recusado: ' . ($resultado['message'] ?? 'Verifique os dados do cartao.'));
        }

        salvarPaymentId($conn, $idEmpresa, $resultado['id']);
        $status       = $resultado['status']        ?? '';
        $statusDetail = $resultado['status_detail'] ?? '';
        $threeDsInfo  = $resultado['three_ds_info'] ?? null;

        if ($status === 'approved') {
            ativarEmpresa($conn, $idEmpresa, $plano, $billing);
            enviarEmailBoasVindas($email, $nome, ucfirst($plano));
            // Atualiza sessão com novo plano e vencimento para efeito imediato
            if ($isUpgrade && isset($_SESSION['usuario_id'])) {
                $dias = $billing === 'anual' ? 365 : 30;
                $_SESSION['plano']           = $plano;
                $_SESSION['data_vencimento'] = date('Y-m-d', strtotime("+{$dias} days"));
            }
            $redirect = $isUpgrade ? 'index.php?acao=painel' : 'index.php';
            responder(true, 'Pagamento aprovado!', ['status' => 'approved', 'redirect' => $redirect]);
        }

        // 3DS: banco requer autenticação adicional do portador
        if ($statusDetail === 'pending_challenge' || ($status === 'pending' && $threeDsInfo)) {
            responder(true, 'Autenticação bancária necessária.', [
                'status'        => 'pending_challenge',
                'payment_id'    => $resultado['id'],
                'three_ds_info' => $threeDsInfo,
            ]);
        }

        if (in_array($status, ['in_process', 'pending', 'authorized'])) {
            responder(true, 'Pagamento em análise.', [
                'status'        => $status,
                'status_detail' => $statusDetail,
                'payment_id'    => $resultado['id'],
            ]);
        }

        // Rejeitado — apaga conta criada e devolve motivo traduzido
        if (!$isUpgrade) {
            $conn->prepare("DELETE FROM usuarios WHERE id_empresa = ?")->execute([$idEmpresa]);
            $conn->prepare("DELETE FROM empresas WHERE id = ?")->execute([$idEmpresa]);
        }
        // Grava log de rejeição para diagnóstico
        $logDir = __DIR__ . '/../logs';
        @mkdir($logDir, 0755, true);
        @file_put_contents($logDir . '/cartao_debug.log',
            '[' . date('Y-m-d H:i:s') . '] ' . json_encode([
                'status'        => $status,
                'status_detail' => $statusDetail,
                'mp_id'         => $resultado['id']         ?? null,
                'http_code'     => $resultado['_http_code'] ?? null,
                'cause'         => $resultado['cause']      ?? null,
                'error'         => $resultado['error']      ?? null,
                'message'       => $resultado['message']    ?? null,
                'three_ds_info' => $threeDsInfo,
            ], JSON_PRETTY_PRINT) . "\n\n",
            FILE_APPEND
        );
        responder(false, traduzirRejeicaoCartao($statusDetail), ['status' => $status, 'status_detail' => $statusDetail]);
    }

    if ($acao === 'boleto') {
        $idEmpresa  = $resolverEmpresa();
        $referencia = "empresa_{$idEmpresa}_{$plano}_{$billing}";
        $resultado  = MercadoPagoHelper::criarBoleto($valor, $email, $nome, $cpf, $descricao, $referencia);

        if (!empty($resultado['error']) || empty($resultado['id'])) {
            if (!$isUpgrade) {
                $conn->prepare("DELETE FROM usuarios WHERE id_empresa = ?")->execute([$idEmpresa]);
                $conn->prepare("DELETE FROM empresas WHERE id = ?")->execute([$idEmpresa]);
            }
            responder(false, 'Erro ao gerar boleto: ' . ($resultado['message'] ?? 'Tente novamente.'));
        }

        salvarPaymentId($conn, $idEmpresa, $resultado['id']);
        $boleto = $resultado['transaction_details'] ?? [];

        responder(true, 'Boleto gerado!', [
            'payment_id'      => $resultado['id'],
            'boleto_url'      => $boleto['external_resource_url'] ?? '',
            'barcode'         => $resultado['barcode']['content'] ?? '',
            'data_vencimento' => date('d/m/Y', strtotime('+3 days')),
        ]);
    }

    responder(false, 'Acao desconhecida.');

} catch (\Exception $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    responder(false, 'Erro interno: ' . $e->getMessage());
}

// ── Auxiliares ─────────────────────────────────────────────────

function criarContaBanco(\PDO $conn, string $nome, string $empresa, string $email, string $senha, string $plano, int $diasVencimento, bool $ativo, string $cpf = '', string $whatsapp = ''): int {
    $conn->beginTransaction();
    $dataVenc = $diasVencimento > 0 ? date('Y-m-d', strtotime("+{$diasVencimento} days")) : date('Y-m-d');
    $conn->prepare("INSERT INTO empresas (nome_fantasia, razao_social, plano, data_vencimento, ativo) VALUES (?, ?, ?, ?, ?)")
         ->execute([$empresa ?: $nome, $empresa ?: $nome, $plano, $dataVenc, $ativo ? 1 : 0]);
    $id = (int) $conn->lastInsertId();
    $conn->prepare("INSERT INTO usuarios (id_empresa, nome, email, senha, nivel_acesso, cpf, whatsapp) VALUES (?, ?, ?, ?, 'admin', ?, ?)")
         ->execute([$id, $nome, $email, password_hash($senha, PASSWORD_DEFAULT), $cpf ?: null, $whatsapp ?: null]);
    $conn->commit();
    return $id;
}

function salvarPaymentId(\PDO $conn, int $idEmpresa, string $paymentId): void {
    // Adicione a coluna se ainda não existir: ALTER TABLE empresas ADD COLUMN payment_id_mp VARCHAR(50) NULL;
    try {
        $conn->prepare("UPDATE empresas SET payment_id_mp = ? WHERE id = ?")->execute([$paymentId, $idEmpresa]);
    } catch (\Exception) { /* ignora se coluna não existir */ }
}

function ativarEmpresa(\PDO $conn, int $idEmpresa, string $plano, string $billing): void {
    $dias = $billing === 'anual' ? 365 : 30;
    $conn->prepare("UPDATE empresas SET ativo = 1, plano = ?, data_vencimento = ? WHERE id = ?")
         ->execute([$plano, date('Y-m-d', strtotime("+{$dias} days")), $idEmpresa]);
}

function atualizarPlanoEmpresa(\PDO $conn, int $idEmpresa, string $plano, string $billing): void {
    $dias = $billing === 'anual' ? 365 : 30;
    $conn->prepare("UPDATE empresas SET plano = ?, ativo = 1, data_vencimento = ? WHERE id = ?")
         ->execute([$plano, date('Y-m-d', strtotime("+{$dias} days")), $idEmpresa]);
}

function enviarEmailBoasVindas(string $email, string $nome, string $plano): void
{
    $url     = defined('APP_URL') ? APP_URL . '/publico/index.php' : '';
    $assunto = "Bem-vindo ao CHRONA - acesso liberado";

    $corpoTexto  = "Ola, {$nome},\n\n";
    $corpoTexto .= "Seu pagamento foi confirmado e seu acesso ao CHRONA ja esta liberado.\n\n";
    $corpoTexto .= "Resumo da assinatura:\n";
    $corpoTexto .= "Plano: {$plano}\n";
    $corpoTexto .= "E-mail de acesso: {$email}\n";
    $corpoTexto .= "Senha: a que voce cadastrou no momento da compra\n\n";
    $corpoTexto .= "Para comecar, acesse:\n";
    $corpoTexto .= "{$url}\n\n";
    $corpoTexto .= "Qualquer duvida, e so responder este e-mail.\n\n";
    $corpoTexto .= "Atenciosamente,\n";
    $corpoTexto .= "Equipe CHRONA\n";
    $corpoTexto .= "https://engmarcosalveres.com.br";

    $nomeSeguro  = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $planoSeguro = htmlspecialchars($plano, ENT_QUOTES, 'UTF-8');
    $emailSeguro = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $urlSegura   = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

    $corpoHtml = <<<HTML
<p style="margin:0 0 16px;">Ola, <strong>{$nomeSeguro}</strong>,</p>
<p style="margin:0 0 20px;">Seu pagamento foi confirmado e seu acesso ao <strong>CHRONA</strong> ja esta liberado.</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;background-color:#f9fafb;border-radius:8px;margin:0 0 24px;">
<tr><td style="padding:16px 20px;font-size:14px;color:#374151;">
<strong>Plano:</strong> {$planoSeguro}<br>
<strong>E-mail de acesso:</strong> {$emailSeguro}<br>
<strong>Senha:</strong> a que voce cadastrou no momento da compra
</td></tr>
</table>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
<tr><td style="background-color:#0066ff;border-radius:8px;">
<a href="{$urlSegura}" style="display:inline-block;padding:12px 28px;color:#ffffff;font-weight:bold;font-size:14px;text-decoration:none;">Acessar o CHRONA</a>
</td></tr>
</table>
<p style="margin:0;">Qualquer duvida, e so responder este e-mail.</p>
HTML;

    \Aplicacao\Helpers\EmailHelper::enviarHtml($email, $nome, $assunto, $corpoHtml, $corpoTexto);
}

/** E-mail de notificação de venda para o ADMIN */
function notificarAdmin(string $nomeCliente, string $emailCliente, string $plano, string $billing, float $valor, string $metodo): void
{
    if (!defined('EMAIL_ADMIN') || empty(EMAIL_ADMIN)) return;

    $assunto = "Nova venda CHRONA - {$plano} ({$billing}) R$ " . number_format($valor, 2, ',', '.');

    $corpo   = "Nova venda realizada no CHRONA.\n\n";
    $corpo  .= "Cliente: {$nomeCliente}\n";
    $corpo  .= "E-mail:  {$emailCliente}\n";
    $corpo  .= "Plano:   " . ucfirst($plano) . " ({$billing})\n";
    $corpo  .= "Metodo:  " . strtoupper($metodo) . "\n";
    $corpo  .= "Valor:   R$ " . number_format($valor, 2, ',', '.') . "\n";
    $corpo  .= "Data/hora: " . date('d/m/Y H:i:s') . "\n\n";
    $corpo  .= "Acesse o painel para visualizar o cliente:\n";
    $corpo  .= (defined('APP_URL') ? APP_URL : '') . "/publico/index.php?acao=listar_empresas\n\n";
    $corpo  .= "Equipe CHRONA";

    \Aplicacao\Helpers\EmailHelper::enviar(EMAIL_ADMIN, 'Admin CHRONA', $assunto, $corpo);
}

function traduzirRejeicaoCartao(string $detail): string {
    $msgs = [
        'cc_rejected_insufficient_amount'      => 'Saldo insuficiente no cartão. Verifique seu limite e tente novamente.',
        'cc_rejected_bad_filled_security_code' => 'Código de segurança (CVV) inválido. Confira o número no verso do cartão.',
        'cc_rejected_bad_filled_date'          => 'Data de validade inválida. Verifique o campo MM/AA.',
        'cc_rejected_bad_filled_card_number'   => 'Número do cartão inválido. Confira os 16 dígitos.',
        'cc_rejected_blacklist'                => 'Cartão bloqueado pela operadora. Entre em contato com seu banco.',
        'cc_rejected_call_for_authorize'       => 'Ligue para sua operadora para autorizar esta compra.',
        'cc_rejected_duplicated_payment'       => 'Pagamento duplicado detectado. Aguarde alguns minutos e tente novamente.',
        'cc_rejected_high_risk'                => 'Pagamento não autorizado por análise de segurança. Tente outro cartão.',
        'cc_rejected_max_attempts'             => 'Número máximo de tentativas atingido. Tente novamente mais tarde.',
        'cc_rejected_card_disabled'            => 'Cartão desabilitado. Entre em contato com sua operadora.',
    ];
    return $msgs[$detail] ?? 'Pagamento recusado pela operadora. Verifique os dados do cartão ou tente outro método de pagamento.';
}

function responder(bool $sucesso, string $msg, array $extra = []): never {
    echo json_encode(array_merge(['sucesso' => $sucesso, 'mensagem' => $msg], $extra));
    exit;
}