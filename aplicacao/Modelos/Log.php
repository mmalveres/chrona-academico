<?php
namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Log {

    public static function registrar($acao, $detalhes = null) {
        $conn = BancoDeDados::getConexao();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        $nome_usuario = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Visitante/Sistema';
        $ip = $_SERVER['REMOTE_ADDR'];

        $sql = "INSERT INTO sistema_logs (id_usuario, nome_usuario, acao, detalhes, ip) 
                VALUES (:id, :nome, :acao, :detalhes, :ip)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $id_usuario,
            ':nome' => $nome_usuario,
            ':acao' => $acao,
            ':detalhes' => $detalhes,
            ':ip' => $ip
        ]);
    }

    // SAAS: Lista apenas logs de usuários da minha empresa
    public function listarTodos($limite = 100) {
        $conn = BancoDeDados::getConexao();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id_empresa = $_SESSION['id_empresa'] ?? 0;

        // JOIN com a tabela usuarios para filtrar por empresa
        $sql = "SELECT l.* FROM sistema_logs l
                INNER JOIN usuarios u ON l.id_usuario = u.id
                WHERE u.id_empresa = :id_empresa
                ORDER BY l.data_hora DESC LIMIT " . intval($limite);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}