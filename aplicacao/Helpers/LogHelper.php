<?php
namespace Aplicacao\Helpers;

use Aplicacao\Configuracao\BancoDeDados;

class LogHelper
{
    public static function registrar(string $acao, string $detalhes = ''): void
    {
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();

            $idUsuario   = $_SESSION['usuario_id']  ?? null;
            $nomeUsuario = $_SESSION['usuario_nome'] ?? 'Sistema';
            $idEmpresa   = $_SESSION['id_empresa']  ?? null;

            $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
                ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]
                : ($_SERVER['REMOTE_ADDR'] ?? '');

            $conn = BancoDeDados::getConexao();
            $conn->prepare(
                "INSERT INTO logs_sistema (id_usuario, nome_usuario, id_empresa, acao, detalhes, ip)
                 VALUES (?, ?, ?, ?, ?, ?)"
            )->execute([$idUsuario, $nomeUsuario, $idEmpresa, $acao, $detalhes ?: null, trim($ip)]);

        } catch (\Exception) {
            // Falha silenciosa — log nunca deve quebrar a aplicação
        }
    }

    public static function criarTabelaSeNecessario(): void
    {
        try {
            BancoDeDados::getConexao()->exec("
                CREATE TABLE IF NOT EXISTS logs_sistema (
                    id           BIGINT       AUTO_INCREMENT PRIMARY KEY,
                    id_usuario   INT          NULL,
                    nome_usuario VARCHAR(255) NOT NULL DEFAULT '',
                    id_empresa   INT          NULL,
                    acao         VARCHAR(255) NOT NULL,
                    detalhes     TEXT         NULL,
                    ip           VARCHAR(45)  NULL,
                    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_created_at (created_at),
                    INDEX idx_id_empresa (id_empresa),
                    INDEX idx_id_usuario (id_usuario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (\Exception) {}
    }
}
