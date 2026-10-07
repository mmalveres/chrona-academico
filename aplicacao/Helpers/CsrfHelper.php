<?php
namespace Aplicacao\Helpers;

class CsrfHelper {

    public static function gerarToken(): string {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validar(string $token): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function inputHidden(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::gerarToken()) . '">';
    }

    public static function queryParam(): string {
        return 'csrf_token=' . urlencode(self::gerarToken());
    }

    public static function verificarOuAbortar(): void {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!self::validar($token)) {
            http_response_code(403);
            die('Ação não autorizada. Token inválido.');
        }
    }
}
