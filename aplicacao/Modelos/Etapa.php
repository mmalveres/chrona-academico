<?php
// aplicacao/Modelos/Etapa.php

namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Etapa {
    private $conn;
    private $tabela = 'etapas';
    private $id_empresa;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->id_empresa = $_SESSION['id_empresa'] ?? 0;
    }

    private function verificarPermissaoObra($id_obra) {
        $stmt = $this->conn->prepare("SELECT id FROM obras WHERE id = :id AND id_empresa = :id_empresa");
        $stmt->execute([':id' => $id_obra, ':id_empresa' => $this->id_empresa]);
        return $stmt->fetch();
    }

    public function listarPorObra($id_obra) {
        $sql = "SELECT e.* FROM " . $this->tabela . " e
                INNER JOIN obras o ON e.id_obra = o.id
                WHERE e.id_obra = :id_obra AND o.id_empresa = :id_empresa
                ORDER BY e.ordem ASC, e.data_inicio_prevista ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_obra' => $id_obra, ':id_empresa' => $this->id_empresa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastrar($dados) {
        if (!$this->verificarPermissaoObra($dados['id_obra'])) return false;

        $sql = "INSERT INTO " . $this->tabela . " (id_obra, nome, ordem, data_inicio_prevista, data_fim_prevista, progresso, status) 
                VALUES (:id_obra, :nome, :ordem, :inicio, :fim, :progresso, :status)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_obra' => $dados['id_obra'],
            ':nome' => $dados['nome'],
            ':ordem' => $dados['ordem'],
            ':inicio' => $dados['inicio'],
            ':fim' => $dados['fim'],
            ':progresso' => $dados['progresso'],
            ':status' => $dados['status']
        ]);
    }

    public function atualizarProgresso($id, $progresso, $status) {
        // Validação via JOIN
        $sqlCheck = "SELECT e.id FROM " . $this->tabela . " e 
                     INNER JOIN obras o ON e.id_obra = o.id 
                     WHERE e.id = :id AND o.id_empresa = :id_empresa";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute([':id' => $id, ':id_empresa' => $this->id_empresa]);
        if (!$stmtCheck->fetch()) return false;

        $sql = "UPDATE " . $this->tabela . " SET progresso = :progresso, status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':progresso' => $progresso, ':status' => $status]);
    }

    public function excluir($id) {
        // Validação via JOIN
        $sqlCheck = "SELECT e.id FROM " . $this->tabela . " e 
                     INNER JOIN obras o ON e.id_obra = o.id 
                     WHERE e.id = :id AND o.id_empresa = :id_empresa";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute([':id' => $id, ':id_empresa' => $this->id_empresa]);
        if (!$stmtCheck->fetch()) return false;

        $stmt = $this->conn->prepare("DELETE FROM " . $this->tabela . " WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}