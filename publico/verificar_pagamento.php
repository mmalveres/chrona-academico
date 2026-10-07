<?php
// publico/verificar_pagamento.php
// Consultado pelo checkout a cada 5s para verificar se o PIX foi pago.

header('Content-Type: application/json');

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../config/mercadopago.php';

use Aplicacao\Helpers\MercadoPagoHelper;
use Aplicacao\Configuracao\BancoDeDados;

$paymentId = preg_replace('/\D/', '', $_GET['payment_id'] ?? '');

if (!$paymentId) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

try {
    $pagamento = MercadoPagoHelper::consultarPagamento($paymentId);
    $status    = $pagamento['status'] ?? 'unknown';

    $extRef = $pagamento['external_reference'] ?? '';
    if (preg_match('/^empresa_(\d+)_(\w+)_(\w+)$/', $extRef, $m)) {
        [, $idEmpresa, $plano, $billing] = $m;
        $idEmpresa = (int) $idEmpresa;
        $conn = BancoDeDados::getConexao();

        if ($status === 'approved') {
            $check = $conn->prepare("SELECT ativo, data_vencimento FROM empresas WHERE id = ?");
            $check->execute([$idEmpresa]);
            $empresa = $check->fetch(\PDO::FETCH_ASSOC);

            if ($empresa) {
                // Ativa se: conta inativa OU licença vencida/expirada hoje
                // Evita reprocessar um pagamento já aplicado (licença futura válida)
                $licencaValida = $empresa['ativo'] && !empty($empresa['data_vencimento'])
                    && strtotime($empresa['data_vencimento']) >= strtotime(date('Y-m-d'));

                if (!$licencaValida) {
                    $dias     = $billing === 'anual' ? 365 : 30;
                    $novaData = date('Y-m-d', strtotime("+{$dias} days"));

                    $conn->prepare("UPDATE empresas SET ativo = 1, plano = ?, data_vencimento = ? WHERE id = ?")
                         ->execute([$plano, $novaData, $idEmpresa]);

                    // Atualiza sessão para efeito imediato no redirect pós-sucesso
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    if (isset($_SESSION['id_empresa']) && (int)$_SESSION['id_empresa'] === $idEmpresa) {
                        $_SESSION['plano']           = $plano;
                        $_SESSION['data_vencimento'] = $novaData;
                    }
                }
            }
        }

        // Rejeitado via polling: apaga conta inativa para permitir nova tentativa
        if (in_array($status, ['rejected', 'cancelled'])) {
            $check = $conn->prepare("SELECT ativo FROM empresas WHERE id = ?");
            $check->execute([$idEmpresa]);
            $empresa = $check->fetch(\PDO::FETCH_ASSOC);
            if ($empresa && !$empresa['ativo']) {
                $conn->prepare("DELETE FROM usuarios WHERE id_empresa = ?")->execute([$idEmpresa]);
                $conn->prepare("DELETE FROM empresas WHERE id = ?")->execute([$idEmpresa]);
            }
        }
    }

    echo json_encode(['status' => $status]);

} catch (\Exception $e) {
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}
