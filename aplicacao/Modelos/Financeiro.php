<?php
// aplicacao/Modelos/Financeiro.php

namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Financeiro {
    private $conn;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
    }

    public function listarPorObra($id_obra, $filtros = []) {
        $where  = ['f.id_obra = :id_obra'];
        $params = [':id_obra' => $id_obra];

        if (!empty($filtros['periodo_inicio'])) {
            $where[]                    = 'f.data_movimentacao >= :periodo_inicio';
            $params[':periodo_inicio']  = $filtros['periodo_inicio'];
        }
        if (!empty($filtros['periodo_fim'])) {
            $where[]                 = 'f.data_movimentacao <= :periodo_fim';
            $params[':periodo_fim']  = $filtros['periodo_fim'];
        }
        if (!empty($filtros['tipo'])) {
            $where[]        = 'f.tipo = :tipo';
            $params[':tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['status'])) {
            $where[]           = 'f.status = :status';
            $params[':status'] = $filtros['status'];
        }

        $sql = "SELECT f.*, oi.codigo_referencia, oi.descricao as orcamento_desc
                FROM financeiro_movimentacoes f
                LEFT JOIN orcamento_itens oi ON f.id_item_orcamento = oi.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY f.data_movimentacao DESC, f.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM financeiro_movimentacoes WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrar($dados) {
        $sql = "INSERT INTO financeiro_movimentacoes
                    (id_obra, tipo, categoria, id_item_orcamento, descricao, valor, data_movimentacao, status, caminho_arquivo)
                VALUES
                    (:id_obra, :tipo, :categoria, :id_item_orcamento, :descricao, :valor, :data, :status, :arquivo)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_obra',            $dados['id_obra']);
        $stmt->bindParam(':tipo',               $dados['tipo']);
        $stmt->bindParam(':categoria',          $dados['categoria']);
        $idOrcamento = !empty($dados['id_item_orcamento']) ? $dados['id_item_orcamento'] : null;
        $stmt->bindParam(':id_item_orcamento',  $idOrcamento);
        $stmt->bindParam(':descricao',          $dados['descricao']);
        $stmt->bindParam(':valor',              $dados['valor']);
        $stmt->bindParam(':data',               $dados['data']);
        $stmt->bindParam(':status',             $dados['status']);
        $stmt->bindParam(':arquivo',            $dados['caminho_arquivo']);
        return $stmt->execute();
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE financeiro_movimentacoes SET
                    tipo               = :tipo,
                    categoria          = :categoria,
                    id_item_orcamento  = :id_item_orcamento,
                    descricao          = :descricao,
                    valor              = :valor,
                    data_movimentacao  = :data,
                    status             = :status
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $idOrcamento = !empty($dados['id_item_orcamento']) ? $dados['id_item_orcamento'] : null;
        $stmt->bindParam(':tipo',              $dados['tipo']);
        $stmt->bindParam(':categoria',         $dados['categoria']);
        $stmt->bindParam(':id_item_orcamento', $idOrcamento);
        $stmt->bindParam(':descricao',         $dados['descricao']);
        $stmt->bindParam(':valor',             $dados['valor']);
        $stmt->bindParam(':data',              $dados['data']);
        $stmt->bindParam(':status',            $dados['status']);
        $stmt->bindParam(':id',                $id);
        return $stmt->execute();
    }

    public function excluir($id) {
        $stmt = $this->conn->prepare("SELECT caminho_arquivo FROM financeiro_movimentacoes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $arquivo = $stmt->fetchColumn();

        if ($arquivo && file_exists(__DIR__ . '/../../publico/uploads/financeiro/' . $arquivo)) {
            unlink(__DIR__ . '/../../publico/uploads/financeiro/' . $arquivo);
        }

        $stmt = $this->conn->prepare("DELETE FROM financeiro_movimentacoes WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function buscarResumo($id_obra, $filtros = []) {
        $where  = ['id_obra = :id_obra'];
        $params = [':id_obra' => $id_obra];

        if (!empty($filtros['periodo_inicio'])) {
            $where[]                   = 'data_movimentacao >= :periodo_inicio';
            $params[':periodo_inicio'] = $filtros['periodo_inicio'];
        }
        if (!empty($filtros['periodo_fim'])) {
            $where[]                = 'data_movimentacao <= :periodo_fim';
            $params[':periodo_fim'] = $filtros['periodo_fim'];
        }
        if (!empty($filtros['tipo'])) {
            $where[]         = 'tipo = :tipo';
            $params[':tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['status'])) {
            $where[]           = 'status = :status';
            $params[':status'] = $filtros['status'];
        }

        $sql = "SELECT
                    SUM(CASE WHEN tipo='receita' AND status='pago'    THEN valor ELSE 0 END) as total_receitas,
                    SUM(CASE WHEN tipo='despesa' AND status='pago'    THEN valor ELSE 0 END) as total_despesas,
                    SUM(CASE WHEN status='pendente'                   THEN valor ELSE 0 END) as total_pendente
                FROM financeiro_movimentacoes
                WHERE " . implode(' AND ', $where);

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        $receitas = (float)($r['total_receitas'] ?? 0);
        $despesas = (float)($r['total_despesas'] ?? 0);
        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'saldo'    => $receitas - $despesas,
            'pendente' => (float)($r['total_pendente'] ?? 0),
        ];
    }

    // Agrupamento mensal dos últimos 12 meses para o gráfico
    public function buscarMensal($id_obra) {
        $sql = "SELECT
                    DATE_FORMAT(data_movimentacao, '%Y-%m') as mes,
                    SUM(CASE WHEN tipo='receita' AND status='pago' THEN valor ELSE 0 END) as receitas,
                    SUM(CASE WHEN tipo='despesa' AND status='pago' THEN valor ELSE 0 END) as despesas
                FROM financeiro_movimentacoes
                WHERE id_obra = :id_obra
                  AND data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(data_movimentacao, '%Y-%m')
                ORDER BY mes ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_obra' => $id_obra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Etapas do cronograma com data_fim já passada e sem receita lançada no período
    public function buscarAlertasCronograma($id_obra) {
        $sql = "SELECT
                    RTRIM(TRIM(oi.itemizacao)) as codigo,
                    oi.descricao,
                    cp.data_inicio,
                    cp.data_fim,
                    DATEDIFF(CURDATE(), cp.data_fim) as dias_atraso
                FROM orcamento_itens oi
                JOIN orcamentos o       ON oi.id_orcamento = o.id
                JOIN cronograma_planejamento cp ON oi.id = cp.id_item_orcamento
                WHERE o.id_obra  = :id_obra
                  AND oi.tipo    = 'etapa'
                  AND cp.data_fim < CURDATE()
                  AND NOT EXISTS (
                      SELECT 1
                      FROM financeiro_movimentacoes fm
                      WHERE fm.id_obra = :id_obra2
                        AND fm.tipo    = 'receita'
                        AND fm.data_movimentacao BETWEEN cp.data_inicio AND cp.data_fim
                  )
                ORDER BY cp.data_fim ASC
                LIMIT 5";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_obra' => $id_obra, ':id_obra2' => $id_obra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
