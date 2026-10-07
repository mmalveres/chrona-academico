<?php
// aplicacao/Modelos/Documento.php

namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Documento {
    private $conn;
    private $tabela = 'documentos';
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
        // SAAS: JOIN com obras
        $sql = "SELECT d.* FROM " . $this->tabela . " d
                INNER JOIN obras o ON d.id_obra = o.id
                WHERE d.id_obra = :id_obra AND o.id_empresa = :id_empresa
                ORDER BY d.data_upload DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_obra', $id_obra);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastrar($dados) {
        if (!$this->verificarPermissaoObra($dados['id_obra'])) return false;

        $sql = "INSERT INTO " . $this->tabela . " (id_obra, titulo, tipo, caminho_arquivo, validade, descricao) 
                VALUES (:id_obra, :titulo, :tipo, :caminho, :validade, :descricao)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id_obra' => $dados['id_obra'],
            ':titulo' => $dados['titulo'],
            ':tipo' => $dados['tipo'],
            ':caminho' => $dados['caminho'],
            ':validade' => $dados['validade'],
            ':descricao' => $dados['descricao']
        ]);
        return $stmt->execute();
    }

    public function excluir($id) {
        // Verifica se o documento é da minha empresa antes de excluir
        $stmt = $this->conn->prepare("SELECT d.caminho_arquivo FROM " . $this->tabela . " d
                                      INNER JOIN obras o ON d.id_obra = o.id
                                      WHERE d.id = :id AND o.id_empresa = :id_empresa");
        $stmt->execute([':id' => $id, ':id_empresa' => $this->id_empresa]);
        $doc = $stmt->fetch();

        if ($doc) {
            if (file_exists(__DIR__ . '/../../publico/uploads/documentos/' . $doc['caminho_arquivo'])) {
                unlink(__DIR__ . '/../../publico/uploads/documentos/' . $doc['caminho_arquivo']);
            }
            $stmtDelete = $this->conn->prepare("DELETE FROM " . $this->tabela . " WHERE id = :id");
            return $stmtDelete->execute([':id' => $id]);
        }
        return false;
    }
}