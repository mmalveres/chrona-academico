<?php
namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Cronograma {
    private $conn;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
    }

    public function listarCronogramaDaObra($id_obra) {
        // Query ajustada para pegar os dados corretos
        $sql = "SELECT
                    oi.id as id_item,
                    oi.codigo_referencia,
                    oi.itemizacao,
                    oi.descricao,
                    oi.tipo,
                    oi.unidade,
                    oi.quantidade_total as quantidade,
                    oi.preco_unitario,
                    (oi.quantidade_total * oi.preco_unitario) as preco_total,
                    cp.id as id_cronograma,
                    cp.data_inicio,
                    cp.data_fim,
                    cp.observacao,
                    DATEDIFF(cp.data_fim, cp.data_inicio) as duracao_dias
                FROM orcamento_itens oi
                JOIN orcamentos o ON oi.id_orcamento = o.id
                LEFT JOIN cronograma_planejamento cp ON oi.id = cp.id_item_orcamento
                WHERE o.id_obra = :id_obra
                /* IMPORTANTE: Agrupar por item para evitar duplicatas visuais se o banco estiver sujo */
                GROUP BY oi.id 
                ORDER BY oi.id ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_obra' => $id_obra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvarPlanejamento($id_obra, $itens) {
        try {
            $this->conn->beginTransaction();

            // 1. Prepara queries de LIMPEZA e INSERÇÃO
            // Essa estratégia apaga qualquer rastro anterior do item e insere o novo limpo
            $sqlDelete = "DELETE FROM cronograma_planejamento WHERE id_item_orcamento = :id_item";
            $sqlInsert = "INSERT INTO cronograma_planejamento (id_obra, id_item_orcamento, data_inicio, data_fim) 
                          VALUES (:id_obra, :id_item, :inicio, :fim)";

            $stmtDelete = $this->conn->prepare($sqlDelete);
            $stmtInsert = $this->conn->prepare($sqlInsert);

            foreach ($itens as $id_item => $datas) {
                // Trata datas vazias como NULL
                $inicio = !empty($datas['inicio']) ? $datas['inicio'] : null;
                $fim    = !empty($datas['fim']) ? $datas['fim'] : null;

                // Passo 1: Remove agendamento antigo desse item (Remove duplicatas se houver)
                $stmtDelete->execute([':id_item' => $id_item]);

                // Passo 2: Se tiver data para salvar, insere o novo registro
                if ($inicio || $fim) {
                    $stmtInsert->execute([
                        ':id_obra' => $id_obra,
                        ':id_item' => $id_item,
                        ':inicio'  => $inicio,
                        ':fim'     => $fim
                    ]);
                }
            }

            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            // Para debug, descomente a linha abaixo se der erro
            // die($e->getMessage()); 
            return false;
        }
    }
}