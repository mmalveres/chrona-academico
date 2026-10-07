<?php
// aplicacao/Helpers/MercadoPagoHelper.php
// Wrapper simples para a API do Mercado Pago (sem SDK, só cURL)

namespace Aplicacao\Helpers;

class MercadoPagoHelper
{
    private static string $baseUrl = 'https://api.mercadopago.com';

    // ── Faz chamada à API ──────────────────────────────────────
    private static function request(string $method, string $endpoint, array $body = []): array
    {
        $token = defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : '';

        $ch = curl_init(self::$baseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'X-Idempotency-Key: ' . uniqid('chrona_', true),
            ],
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);

        if ($method === 'POST' && !empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => true, 'message' => 'cURL error: ' . $error];
        }

        $data = json_decode($response, true) ?? [];
        $data['_http_code'] = $httpCode;
        return $data;
    }

    // ── CRIAR PAGAMENTO PIX ───────────────────────────────────
    public static function criarPix(
        float  $valor,
        string $email,
        string $nome,
        string $cpf,
        string $descricao,
        string $referencia   // ID interno (ex: "empresa_5_construtora")
    ): array {
        $partes = explode(' ', trim($nome), 2);

        return self::request('POST', '/v1/payments', [
            'transaction_amount' => round($valor, 2),
            'payment_method_id'  => 'pix',
            'description'        => $descricao,
            'external_reference' => $referencia,
            'notification_url'   => defined('APP_URL') ? APP_URL . '/publico/webhook_mercadopago.php' : '',
            'payer' => [
                'email'      => $email,
                'first_name' => $partes[0],
                'last_name'  => $partes[1] ?? '',
                'identification' => [
                    'type'   => 'CPF',
                    'number' => preg_replace('/\D/', '', $cpf),
                ],
            ],
        ]);
    }

    // ── CRIAR PAGAMENTO CARTÃO ────────────────────────────────
    public static function criarCartao(
        float  $valor,
        string $token,
        int    $parcelas,
        string $email,
        string $nome,
        string $cpf,
        string $descricao,
        string $referencia,
        string $whatsapp        = '',
        string $paymentMethodId = ''
    ): array {
        $partes    = explode(' ', trim($nome), 2);
        $firstName = $partes[0];
        $lastName  = $partes[1] ?? $partes[0];

        // Extrai DDD + número do WhatsApp (se informado)
        $foneDigitos = preg_replace('/\D/', '', $whatsapp);
        $ddd    = strlen($foneDigitos) >= 10 ? substr($foneDigitos, 0, 2)  : '';
        $numero = strlen($foneDigitos) >= 10 ? substr($foneDigitos, 2)     : $foneDigitos;

        $body = [
            'transaction_amount'  => round($valor, 2),
            'token'               => $token,
            'installments'        => max(1, (int) $parcelas),
            'description'         => $descricao,
            'statement_descriptor'=> 'CHRONA',  // nome na fatura do cliente
            'external_reference'  => $referencia,
            'notification_url'    => defined('APP_URL') ? APP_URL . '/publico/webhook_mercadopago.php' : '',
            'three_d_secure_mode' => 'optional',
            'binary_mode'         => false,  // nunca rejeitar na hora — deixa análise antifraude agir
            'payer' => [
                'email'      => $email,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'identification' => [
                    'type'   => 'CPF',
                    'number' => preg_replace('/\D/', '', $cpf),
                ],
            ],
            'additional_info' => [
                'items' => [[
                    'id'          => $referencia,
                    'title'       => $descricao,
                    'description' => 'Software de gestão de obras CHRONA',
                    'quantity'    => 1,
                    'unit_price'  => round($valor, 2),
                    'category_id' => 'services',
                    'type'        => 'digital',
                ]],
                'payer' => [
                    'first_name'        => $firstName,
                    'last_name'         => $lastName,
                    'is_prime_user'     => '0',
                    'is_first_purchase' => '1',
                    'registration_date' => date('Y-m-d\TH:i:s\.000-03:00'),
                    'phone' => $ddd ? [
                        'area_code' => $ddd,
                        'number'    => $numero,
                    ] : null,
                ],
            ],
        ];

        // Remove phone se vazio
        if (empty($body['additional_info']['payer']['phone'])) {
            unset($body['additional_info']['payer']['phone']);
        }

        // payment_method_id detectado via BIN lookup no frontend ("visa", "master", etc.)
        if (!empty($paymentMethodId)) {
            $body['payment_method_id'] = $paymentMethodId;
        }

        return self::request('POST', '/v1/payments', $body);
    }

    // ── CRIAR BOLETO ──────────────────────────────────────────
    public static function criarBoleto(
        float  $valor,
        string $email,
        string $nome,
        string $cpf,
        string $descricao,
        string $referencia
    ): array {
        $partes = explode(' ', trim($nome), 2);

        return self::request('POST', '/v1/payments', [
            'transaction_amount' => round($valor, 2),
            'payment_method_id'  => 'bolbradesco',
            'description'        => $descricao,
            'external_reference' => $referencia,
            'notification_url'   => defined('APP_URL') ? APP_URL . '/publico/webhook_mercadopago.php' : '',
            'payer' => [
                'email'      => $email,
                'first_name' => $partes[0],
                'last_name'  => $partes[1] ?? '',
                'identification' => [
                    'type'   => 'CPF',
                    'number' => preg_replace('/\D/', '', $cpf),
                ],
            ],
        ]);
    }

    // ── CONSULTAR STATUS DO PAGAMENTO ─────────────────────────
    public static function consultarPagamento(string $paymentId): array
    {
        return self::request('GET', '/v1/payments/' . $paymentId);
    }

    // ── VERIFICAR ASSINATURA DO WEBHOOK ──────────────────────
    public static function verificarWebhook(string $xSignature, string $xRequestId, string $dataId): bool
    {
        if (!defined('MP_WEBHOOK_SECRET') || empty(MP_WEBHOOK_SECRET)) return true; // sem validação se não configurou

        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:" . explode(',', $xSignature)[0];
        // extrai ts e v1 do header x-signature
        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
            $parts[trim($k)] = trim($v);
        }
        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:" . ($parts['ts'] ?? '');
        $expected = hash_hmac('sha256', $manifest, MP_WEBHOOK_SECRET);
        return hash_equals($expected, $parts['v1'] ?? '');
    }
}