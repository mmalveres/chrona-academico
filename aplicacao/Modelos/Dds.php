<?php
// aplicacao/Modelos/Dds.php

namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Dds {
    private $conn;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
    }

    public function listarPorObra($id_obra) {
        $sql = "SELECT d.*, u.nome as nome_usuario,
                       (SELECT COUNT(*) FROM dds_presenca p WHERE p.id_dds = d.id) as total_presentes
                FROM dds_registros d
                LEFT JOIN usuarios u ON d.id_usuario = u.id
                WHERE d.id_obra = :id_obra
                ORDER BY d.data DESC, d.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_obra' => $id_obra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarComPresenca($id) {
        $dds = $this->conn->prepare(
            "SELECT d.*, u.nome as nome_usuario
             FROM dds_registros d
             LEFT JOIN usuarios u ON d.id_usuario = u.id
             WHERE d.id = :id LIMIT 1"
        );
        $dds->execute([':id' => $id]);
        $registro = $dds->fetch(PDO::FETCH_ASSOC);
        if (!$registro) return null;

        $presenca = $this->conn->prepare(
            "SELECT * FROM dds_presenca WHERE id_dds = :id ORDER BY id ASC"
        );
        $presenca->execute([':id' => $id]);
        $registro['presenca'] = $presenca->fetchAll(PDO::FETCH_ASSOC);
        return $registro;
    }

    public function cadastrar($dados, $listaPresenca) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO dds_registros (id_obra, data, tema, conteudo, id_usuario)
                 VALUES (:id_obra, :data, :tema, :conteudo, :id_usuario)"
            );
            $stmt->execute([
                ':id_obra'    => $dados['id_obra'],
                ':data'       => $dados['data'],
                ':tema'       => $dados['tema'],
                ':conteudo'   => $dados['conteudo'] ?? null,
                ':id_usuario' => $dados['id_usuario'],
            ]);
            $id_dds = $this->conn->lastInsertId();

            if (!empty($listaPresenca)) {
                $stmtP = $this->conn->prepare(
                    "INSERT INTO dds_presenca (id_dds, nome, funcao) VALUES (:id_dds, :nome, :funcao)"
                );
                foreach ($listaPresenca as $p) {
                    $nome = trim($p['nome'] ?? '');
                    if (empty($nome)) continue;
                    $stmtP->execute([
                        ':id_dds' => $id_dds,
                        ':nome'   => $nome,
                        ':funcao' => trim($p['funcao'] ?? ''),
                    ]);
                }
            }

            $this->conn->commit();
            return $id_dds;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function excluir($id) {
        // Presença é deletada em cascata via FK
        $stmt = $this->conn->prepare("DELETE FROM dds_registros WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function buscarResumo($id_obra) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as total_dds,
                    COUNT(DISTINCT data) as dias_com_dds,
                    (SELECT COUNT(*) FROM dds_presenca p
                     JOIN dds_registros dr ON p.id_dds = dr.id
                     WHERE dr.id_obra = :id_obra2) as total_presencas
             FROM dds_registros
             WHERE id_obra = :id_obra"
        );
        $stmt->execute([':id_obra' => $id_obra, ':id_obra2' => $id_obra]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
