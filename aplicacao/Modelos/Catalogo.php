<?php
namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Catalogo {
    private $conn;
    private $id_empresa;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->id_empresa = $_SESSION['id_empresa'] ?? 0;
    }

    // --- MÃO DE OBRA ---
    public function listarMaoObra() {
        // SAAS: Busca os itens da empresa ATUAL + os itens GLOBAIS (id_empresa = 0)
        $sql = "SELECT * FROM catalogo_mao_obra 
                WHERE id_empresa = :id_empresa OR id_empresa = 0 
                ORDER BY nome ASC";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_empresa' => $this->id_empresa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adicionarMaoObra($nome) {
        // Adiciona sempre vinculado à empresa logada
        $stmt = $this->conn->prepare("INSERT INTO catalogo_mao_obra (id_empresa, nome) VALUES (:id_empresa, :nome)");
        return $stmt->execute([':id_empresa' => $this->id_empresa, ':nome' => $nome]);
    }

    public function removerMaoObra($id) {
        // SEGURANÇA: Só permite deletar se o id_empresa for igual ao do usuário
        // Isso impede que um usuário delete os itens globais (que são id_empresa 0)
        $stmt = $this->conn->prepare("DELETE FROM catalogo_mao_obra WHERE id = :id AND id_empresa = :id_empresa");
        return $stmt->execute([':id' => $id, ':id_empresa' => $this->id_empresa]);
    }

    // --- EQUIPAMENTOS ---
    public function listarEquipamentos() {
        // SAAS: Busca da empresa + Globais
        $sql = "SELECT * FROM catalogo_equipamentos 
                WHERE id_empresa = :id_empresa OR id_empresa = 0 
                ORDER BY nome ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_empresa' => $this->id_empresa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adicionarEquipamento($nome) {
        $stmt = $this->conn->prepare("INSERT INTO catalogo_equipamentos (id_empresa, nome) VALUES (:id_empresa, :nome)");
        return $stmt->execute([':id_empresa' => $this->id_empresa, ':nome' => $nome]);
    }

    public function removerEquipamento($id) {
        // SEGURANÇA: Só deleta itens próprios
        $stmt = $this->conn->prepare("DELETE FROM catalogo_equipamentos WHERE id = :id AND id_empresa = :id_empresa");
        return $stmt->execute([':id' => $id, ':id_empresa' => $this->id_empresa]);
    }
}