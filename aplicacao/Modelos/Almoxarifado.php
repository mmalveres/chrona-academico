<?php
// aplicacao/Modelos/Almoxarifado.php

namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Almoxarifado {
    private $conn;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
    }

    public function listarMovimentacoes($id_obra, $filtros = []) {
        $where  = ['m.id_obra = :id_obra'];
        $params = [':id_obra' => $id_obra];

        if (!empty($filtros['tipo'])) {
            $where[]        = 'm.tipo = :tipo';
            $params[':tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['material'])) {
            $where[]            = 'm.descricao LIKE :material';
            $params[':material'] = '%' . $filtros['material'] . '%';
        }
        if (!empty($filtros['periodo_inicio'])) {
            $where[]                   = 'm.data_movimentacao >= :periodo_inicio';
            $params[':periodo_inicio'] = $filtros['periodo_inicio'];
        }
        if (!empty($filtros['periodo_fim'])) {
            $where[]                = 'm.data_movimentacao <= :periodo_fim';
            $params[':periodo_fim'] = $filtros['periodo_fim'];
        }

        $sql = "SELECT m.*, oi.codigo_referencia, oi.descricao as item_orcamento_desc
                FROM almoxarifado_movimentacoes m
                LEFT JOIN orcamento_itens oi ON m.id_item_orcamento = oi.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY m.data_movimentacao DESC, m.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Saldo agrupado por material (para a tabela de estoque)
    public function buscarSaldo($id_obra) {
        $sql = "SELECT
                    descricao,
                    unidade,
                    SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END)  as total_entradas,
                    SUM(CASE WHEN tipo = 'saida'   THEN quantidade ELSE 0 END)  as total_saidas,
                    SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE -quantidade END) as saldo
                FROM almoxarifado_movimentacoes
                WHERE id_obra = :id_obra
                GROUP BY descricao, unidade
                ORDER BY descricao ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_obra' => $id_obra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cards de resumo
    public function buscarResumo($id_obra) {
        $sql = "SELECT
                    COUNT(DISTINCT descricao)                                          as total_materiais,
                    SUM(CASE WHEN tipo='entrada' THEN quantidade * preco_unitario ELSE 0 END) as valor_entradas,
                    COUNT(CASE WHEN tipo='entrada' THEN 1 END)                         as qtd_entradas,
                    COUNT(CASE WHEN tipo='saida'   THEN 1 END)                         as qtd_saidas
                FROM almoxarifado_movimentacoes
                WHERE id_obra = :id_obra";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_obra' => $id_obra]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        // Itens com saldo zero ou negativo (alertas)
        $stmtAlerta = $this->conn->prepare(
            "SELECT COUNT(*) FROM (
                SELECT descricao,
                       SUM(CASE WHEN tipo='entrada' THEN quantidade ELSE -quantidade END) as saldo
                FROM almoxarifado_movimentacoes
                WHERE id_obra = :id_obra
                GROUP BY descricao, unidade
                HAVING saldo <= 0
             ) sub"
        );
        $stmtAlerta->execute([':id_obra' => $id_obra]);
        $itensEsgotados = (int)$stmtAlerta->fetchColumn();

        return [
            'total_materiais' => (int)($r['total_materiais'] ?? 0),
            'valor_entradas'  => (float)($r['valor_entradas'] ?? 0),
            'qtd_entradas'    => (int)($r['qtd_entradas'] ?? 0),
            'qtd_saidas'      => (int)($r['qtd_saidas'] ?? 0),
            'itens_esgotados' => $itensEsgotados,
        ];
    }

    // Lista de nomes de materiais já cadastrados (para datalist de autocomplete)
    public function buscarNomesMateriais($id_obra) {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT descricao, unidade
             FROM almoxarifado_movimentacoes
             WHERE id_obra = :id_obra
             ORDER BY descricao ASC"
        );
        $stmt->execute([':id_obra' => $id_obra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastrar($dados) {
        $sql = "INSERT INTO almoxarifado_movimentacoes
                    (id_obra, tipo, descricao, unidade, quantidade, preco_unitario,
                     id_item_orcamento, fornecedor, nota_fiscal, data_movimentacao, observacao)
                VALUES
                    (:id_obra, :tipo, :descricao, :unidade, :quantidade, :preco_unitario,
                     :id_item_orcamento, :fornecedor, :nota_fiscal, :data, :observacao)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_obra'           => $dados['id_obra'],
            ':tipo'              => $dados['tipo'],
            ':descricao'         => $dados['descricao'],
            ':unidade'           => $dados['unidade'],
            ':quantidade'        => $dados['quantidade'],
            ':preco_unitario'    => $dados['preco_unitario'] ?? 0,
            ':id_item_orcamento' => $dados['id_item_orcamento'] ?: null,
            ':fornecedor'        => $dados['fornecedor'] ?: null,
            ':nota_fiscal'       => $dados['nota_fiscal'] ?: null,
            ':data'              => $dados['data'],
            ':observacao'        => $dados['observacao'] ?: null,
        ]);
    }

    public function excluir($id) {
        $stmt = $this->conn->prepare(
            "DELETE FROM almoxarifado_movimentacoes WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }
}
