<?php
namespace Aplicacao\Configuracao;

use PDO;
use PDOException;

class BancoDeDados {
    private static ?BancoDeDados $instancia = null;
    private PDO $conexao;

    private function __construct() {
        $host      = getenv('DB_HOST') ?: 'localhost';
        $nomeBanco = getenv('DB_NAME') ?: '';
        $usuario   = getenv('DB_USER') ?: '';
        $senha     = getenv('DB_PASS') ?: '';

        try {
            $dsn    = "mysql:host={$host};dbname={$nomeBanco};charset=utf8mb4";
            $opcoes = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conexao = new PDO($dsn, $usuario, $senha, $opcoes);
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados.");
        }
    }

    public static function getConexao() {
        if (self::$instancia === null) {
            self::$instancia = new BancoDeDados();
        }
        return self::$instancia->conexao;
    }
}
