<?php
// aplicacao/Modelos/NaoConformidade.php

namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class NaoConformidade {
    private $conn;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
    }

    public function listarPorObra($id_obra, $filtros = []) {
        $where  = ['nc.id_obra = :id_obra'];
        $params = [':id_obra' => $id_obra];

        if (!empty($filtros['status'])) {
            $where[]          = 'nc.status = :status';
            $params[':status'] = $filtros['status'];
        }
        if (!empty($filtros['prioridade'])) {
            $where[]              = 'nc.prioridade = :prioridade';
            $params[':prioridade'] = $filtros['prioridade'];
        }

        $sql = "SELECT nc.*, u.nome as nome_responsavel,
                       (SELECT COUNT(*) FROM nc_fotos f WHERE f.id_nc = nc.id) as total_fotos
                FROM nao_conformidades nc
                LEFT JOIN usuarios u ON nc.id_responsavel = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY FIELD(nc.prioridade,'alta','media','baixa'), nc.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare(
            "SELECT nc.*, u.nome as nome_responsavel
             FROM nao_conformidades nc
             LEFT JOIN usuarios u ON nc.id_responsavel = u.id
             WHERE nc.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $nc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$nc) return null;

        $fotos = $this->conn->prepare("SELECT * FROM nc_fotos WHERE id_nc = :id ORDER BY id ASC");
        $fotos->execute([':id' => $id]);
        $nc['fotos'] = $fotos->fetchAll(PDO::FETCH_ASSOC);
        return $nc;
    }

    public function buscarResumo($id_obra) {
        $stmt = $this->conn->prepare(
            "SELECT
                COUNT(*) as total,
                SUM(status='aberta') as abertas,
                SUM(status='em_tratamento') as em_tratamento,
                SUM(status='fechada') as fechadas,
                SUM(prioridade='alta' AND status != 'fechada') as alta_prioridade
             FROM nao_conformidades
             WHERE id_obra = :id_obra"
        );
        $stmt->execute([':id_obra' => $id_obra]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrar($dados, $fotos = []) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO nao_conformidades
                    (id_obra, titulo, descricao, local, prioridade, status, id_responsavel, prazo, id_usuario)
                 VALUES
                    (:id_obra, :titulo, :descricao, :local, :prioridade, 'aberta', :id_responsavel, :prazo, :id_usuario)"
            );
            $stmt->execute([
                ':id_obra'        => $dados['id_obra'],
                ':titulo'         => $dados['titulo'],
                ':descricao'      => $dados['descricao'] ?? null,
                ':local'          => $dados['local'] ?? null,
                ':prioridade'     => $dados['prioridade'] ?? 'media',
                ':id_responsavel' => $dados['id_responsavel'] ?: null,
                ':prazo'          => $dados['prazo'] ?: null,
                ':id_usuario'     => $dados['id_usuario'],
            ]);
            $id_nc = $this->conn->lastInsertId();

            if (!empty($fotos)) {
                $stmtF = $this->conn->prepare("INSERT INTO nc_fotos (id_nc, caminho) VALUES (:id_nc, :caminho)");
                foreach ($fotos as $caminho) {
                    $stmtF->execute([':id_nc' => $id_nc, ':caminho' => $caminho]);
                }
            }

            $this->conn->commit();
            return $id_nc;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function atualizarStatus($id, $status) {
        $stmt = $this->conn->prepare(
            "UPDATE nao_conformidades SET status = :status WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function excluir($id) {
        // Fotos deletadas em cascata via FK
        $stmt = $this->conn->prepare("DELETE FROM nao_conformidades WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
