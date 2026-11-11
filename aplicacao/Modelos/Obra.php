<?php
// aplicacao/Modelos/Obra.php

namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Obra {
    private $conn;
    private $id_empresa;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->id_empresa = $_SESSION['id_empresa'] ?? 0;
    }

    public function listar() {
        $sql = "SELECT o.*, u.nome as responsavel_nome
                FROM obras o
                LEFT JOIN usuarios u ON o.id_responsavel_tecnico = u.id
                WHERE o.id_empresa = :id_empresa
                ORDER BY o.data_inicio DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPaginado(int $pagina = 1, int $porPagina = 10): array {
        $offset = ($pagina - 1) * $porPagina;
        $sql = "SELECT o.*, u.nome as responsavel_nome
                FROM obras o
                LEFT JOIN usuarios u ON o.id_responsavel_tecnico = u.id
                WHERE o.id_empresa = :id_empresa
                ORDER BY o.data_inicio DESC
                LIMIT :limite OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarTotal(): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM obras WHERE id_empresa = :id_empresa");
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

public function buscarPorId($id) {
        // Query atualizada para trazer o nome do responsável técnico
        $sql = "SELECT o.*, u.nome as nome_responsavel_tecnico 
                FROM obras o 
                LEFT JOIN usuarios u ON o.id_responsavel_tecnico = u.id
                WHERE o.id = :id AND o.id_empresa = :id_empresa";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Busca obra sem filtrar por empresa — necessário para impressão
    // de diários de usuários com acesso a múltiplas empresas
    public function buscarPorIdGlobal($id) {
        $sql = "SELECT o.*, u.nome as nome_responsavel_tecnico
                FROM obras o
                LEFT JOIN usuarios u ON o.id_responsavel_tecnico = u.id
                WHERE o.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   public function cadastrar($dados) {
        $sql = "INSERT INTO obras (
                    id_empresa, 
                    nome, 
                    codigo_contrato, 
                    endereco, 
                    cidade, 
                    data_inicio, 
                    data_previsao_fim, 
                    status, 
                    id_responsavel_tecnico, 
                    orcamento_previsto
                ) 
                VALUES (
                    :id_empresa, 
                    :nome, 
                    :codigo_contrato, 
                    :endereco, 
                    :cidade, 
                    :data_inicio, 
                    :data_previsao_fim, 
                    :status, 
                    :id_responsavel, 
                    :orcamento
                )";

        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':codigo_contrato', $dados['codigo_contrato']); // Novo Campo
        $stmt->bindParam(':endereco', $dados['endereco']);
        $stmt->bindParam(':cidade', $dados['cidade']);
        $stmt->bindParam(':data_inicio', $dados['data_inicio']);
        $stmt->bindParam(':data_previsao_fim', $dados['data_previsao_fim']);
        $stmt->bindParam(':status', $dados['status']);
        $stmt->bindParam(':id_responsavel', $dados['id_responsavel_tecnico']);
        $stmt->bindParam(':orcamento', $dados['orcamento_previsto']);
        
        return $stmt->execute();
    }

 public function atualizar($dados) {
        $sql = "UPDATE obras SET 
                nome = :nome, 
                codigo_contrato = :codigo_contrato, 
                endereco = :endereco, 
                cidade = :cidade, 
                data_inicio = :data_inicio, 
                data_previsao_fim = :data_previsao_fim,
                status = :status,
                id_responsavel_tecnico = :id_responsavel,
                orcamento_previsto = :orcamento
                WHERE id = :id AND id_empresa = :id_empresa";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':codigo_contrato', $dados['codigo_contrato']); // <--- BIND ADICIONADO
        $stmt->bindParam(':endereco', $dados['endereco']);
        $stmt->bindParam(':cidade', $dados['cidade']);
        $stmt->bindParam(':data_inicio', $dados['data_inicio']);
        $stmt->bindParam(':data_previsao_fim', $dados['data_previsao_fim']);
        $stmt->bindParam(':status', $dados['status']);
        $stmt->bindParam(':id_responsavel', $dados['id_responsavel_tecnico']);
        $stmt->bindParam(':orcamento', $dados['orcamento_previsto']);
        
        // WHERE clause
        $stmt->bindParam(':id', $dados['id']);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        
        return $stmt->execute();
    }
    public function excluir($id) {
        $stmt = $this->conn->prepare("DELETE FROM obras WHERE id = :id AND id_empresa = :id_empresa");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        return $stmt->execute();
    }

    // --- NOVO MÉTODO: Buscar Itens do Orçamento ---
    public function listarItensOrcamento($id_obra) {
        // Busca o orçamento ativo mais recente da obra
        $sql = "SELECT i.* FROM orcamento_itens i
                JOIN orcamentos o ON i.id_orcamento = o.id
                WHERE o.id_obra = :id_obra
                ORDER BY i.id ASC"; // Mantém a ordem de inserção (importante para etapas)
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_obra', $id_obra);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // --- NOVO: RELATÓRIO DE MEDIÇÃO FÍSICA ---
    public function gerarRelatorioMedicao($id_obra) {
        // Esta query pega todos os itens do orçamento e soma o que foi lançado nos diários
        $sql = "SELECT 
                    oi.id,
                    oi.codigo_referencia,
                    oi.itemizacao,
                    oi.descricao,
                    oi.unidade,
                    oi.quantidade_total as qtd_orcada,
                    oi.preco_unitario,
                    oi.tipo,
                    COALESCE(SUM(da.quantidade), 0) as qtd_executada
                FROM orcamento_itens oi
                -- Junta com o cabeçalho para filtrar a obra
                JOIN orcamentos o ON oi.id_orcamento = o.id
                -- Junta com as atividades dos diários (LEFT JOIN para trazer itens mesmo sem execução)
                LEFT JOIN diario_atividades da ON da.id_item_orcamento = oi.id
                -- Junta com o diário para garantir que só pegamos diários desta obra (segurança dupla)
                LEFT JOIN diario_obras d ON da.id_diario = d.id 
                
                WHERE o.id_obra = :id_obra
                -- Opcional: Filtrar apenas diários não excluídos ou aprovados?
                -- AND (d.id IS NULL OR d.status != 'excluido') 
                
                GROUP BY oi.id
                ORDER BY oi.id ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_obra', $id_obra);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function listarItensParaDiario($id_obra) {
        // Esta query busca o item do orçamento E SOMA tudo que já foi feito nos diários anteriores
        $sql = "SELECT 
                    oi.id, 
                    oi.codigo_referencia, 
                    oi.descricao, 
                    oi.unidade, 
                    oi.quantidade_total,
                    oi.tipo,
                    oi.itemizacao,
                    -- Subselect para somar o executado até agora (evita duplicidade no GROUP BY)
                    (SELECT COALESCE(SUM(da.quantidade), 0) 
                     FROM diario_atividades da 
                     WHERE da.id_item_orcamento = oi.id) as quantidade_acumulada
                FROM orcamento_itens oi
                WHERE oi.id_orcamento IN (SELECT id FROM orcamentos WHERE id_obra = :id_obra)
                AND (oi.tipo = 'composicao' OR oi.tipo = 'insumo')
                ORDER BY oi.id ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_obra', $id_obra);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calcularCurvaS($id_obra) {
        // Total do contrato
        $stmtTotal = $this->conn->prepare(
            "SELECT COALESCE(SUM(oi.quantidade_total * oi.preco_unitario), 0)
             FROM orcamento_itens oi
             JOIN orcamentos o ON oi.id_orcamento = o.id
             WHERE o.id_obra = :id_obra AND oi.tipo = 'composicao'"
        );
        $stmtTotal->execute([':id_obra' => $id_obra]);
        $totalContrato = (float)$stmtTotal->fetchColumn();
        if ($totalContrato <= 0) return null;

        // Itens com datas do cronograma (planejado)
        // As datas só existem nas linhas de etapa/sub-etapa (é lá que a tela
        // de cadastro do cronograma oferece os campos de data); os itens de
        // composição apenas somam valor ao grupo. Por isso o agrupamento
        // precisa ser o mesmo da tela de edição (visoes/cronograma/ver.php).
        $itensCronograma = (new Cronograma())->listarCronogramaDaObra($id_obra);

        $grupos  = [];
        $idxRaiz = -1;
        foreach ($itensCronograma as $item) {
            if ($item['tipo'] === 'etapa') {
                $cod      = trim($item['itemizacao'] ?? '');
                $codLimpo = rtrim($cod, '.');
                $pontos   = $codLimpo !== '' ? substr_count($codLimpo, '.') : 0;

                if ($pontos === 0) {
                    $idxRaiz++;
                    $grupos[$idxRaiz] = [
                        'total'       => 0.0,
                        'data_inicio' => $item['data_inicio'] ?? '',
                        'data_fim'    => $item['data_fim']    ?? '',
                        'subitens'    => [],
                    ];
                } elseif ($pontos === 1 && $idxRaiz >= 0) {
                    $grupos[$idxRaiz]['subitens'][] = [
                        'total'       => 0.0,
                        'data_inicio' => $item['data_inicio'] ?? '',
                        'data_fim'    => $item['data_fim']    ?? '',
                    ];
                }
                continue;
            }

            if ($idxRaiz >= 0) {
                $valor  = (float)($item['preco_total'] ?? 0);
                $grupos[$idxRaiz]['total'] += $valor;
                $idxSub = count($grupos[$idxRaiz]['subitens']) - 1;
                if ($idxSub >= 0) {
                    $grupos[$idxRaiz]['subitens'][$idxSub]['total'] += $valor;
                }
            }
        }

        $itensPlan = [];
        foreach ($grupos as $g) {
            $linhas = empty($g['subitens']) ? [$g] : $g['subitens'];
            foreach ($linhas as $linha) {
                if (empty($linha['data_inicio']) || empty($linha['data_fim']) || $linha['total'] <= 0) continue;
                $itensPlan[] = [
                    'data_inicio' => $linha['data_inicio'],
                    'data_fim'    => $linha['data_fim'],
                    'valor_item'  => $linha['total'],
                ];
            }
        }

        // Realizado por mês (diário de atividades)
        $stmtReal = $this->conn->prepare(
            "SELECT DATE_FORMAT(d.data_diario, '%Y-%m') as mes,
                    SUM(da.quantidade * oi.preco_unitario) as valor
             FROM diario_atividades da
             JOIN diario_obras d    ON da.id_diario          = d.id
             JOIN orcamento_itens oi ON da.id_item_orcamento = oi.id
             WHERE d.id_obra = :id_obra
               AND da.id_item_orcamento IS NOT NULL
             GROUP BY DATE_FORMAT(d.data_diario, '%Y-%m')
             ORDER BY mes ASC"
        );
        $stmtReal->execute([':id_obra' => $id_obra]);
        $realizadoMes = [];
        foreach ($stmtReal->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $realizadoMes[$r['mes']] = (float)$r['valor'];
        }

        if (empty($itensPlan) && empty($realizadoMes)) return null;

        // Determina range de meses
        $candidatos = [date('Y-m')];
        foreach ($itensPlan as $item) {
            $candidatos[] = date('Y-m', strtotime($item['data_inicio']));
            $candidatos[] = date('Y-m', strtotime($item['data_fim']));
        }
        foreach (array_keys($realizadoMes) as $m) {
            $candidatos[] = $m;
        }
        sort($candidatos);
        $mesMin = $candidatos[0];
        $mesMax = end($candidatos);

        $meses  = [];
        $cursor = strtotime($mesMin . '-01');
        $limite = strtotime($mesMax . '-01');
        while ($cursor <= $limite) {
            $meses[] = date('Y-m', $cursor);
            $cursor   = strtotime('+1 month', $cursor);
        }

        // Distribui planejado por mês (proporcional por dias)
        $planejadoMes = array_fill_keys($meses, 0.0);
        foreach ($itensPlan as $item) {
            $dtIni      = strtotime($item['data_inicio']);
            $dtFim      = strtotime($item['data_fim']);
            $diasTotais = max(1, ($dtFim - $dtIni) / 86400 + 1);
            $valPorDia  = (float)$item['valor_item'] / $diasTotais;
            foreach ($meses as $mes) {
                $mesIni = strtotime($mes . '-01');
                $mesFim = strtotime(date('Y-m-t', $mesIni));
                $iniInt = max($dtIni, $mesIni);
                $fimInt = min($dtFim, $mesFim);
                if ($iniInt > $fimInt) continue;
                $planejadoMes[$mes] += ($fimInt - $iniInt) / 86400 * $valPorDia + $valPorDia;
            }
        }

        // Acumula e calcula percentuais
        $nomesMeses = ['01'=>'Jan','02'=>'Fev','03'=>'Mar','04'=>'Abr','05'=>'Mai','06'=>'Jun',
                       '07'=>'Jul','08'=>'Ago','09'=>'Set','10'=>'Out','11'=>'Nov','12'=>'Dez'];
        $hoje      = date('Y-m');
        $curva     = [];
        $acumPlan  = 0.0;
        $acumReal  = 0.0;
        $percRealAtual = 0.0;

        foreach ($meses as $mes) {
            $acumPlan += $planejadoMes[$mes];
            $ePassado  = $mes <= $hoje;
            if ($ePassado) {
                $acumReal     += $realizadoMes[$mes] ?? 0;
                $percRealAtual = round($acumReal / $totalContrato * 100, 2);
            }
            [, $m] = explode('-', $mes);
            $curva[] = [
                'mes'       => $mes,
                'label'     => ($nomesMeses[$m] ?? $m) . '/' . substr($mes, 2, 2),
                'plan_mes'  => round($planejadoMes[$mes], 2),
                'real_mes'  => $ePassado ? round($realizadoMes[$mes] ?? 0, 2) : null,
                'plan_acum' => round($acumPlan, 2),
                'real_acum' => $ePassado ? round($acumReal, 2) : null,
                'plan_pct'  => round($acumPlan / $totalContrato * 100, 2),
                'real_pct'  => $ePassado ? round($acumReal / $totalContrato * 100, 2) : null,
            ];
        }

        $ultimoPlan = end($curva);
        return [
            'curva'           => $curva,
            'total_contrato'  => $totalContrato,
            'perc_plan_atual' => $ultimoPlan['plan_pct'] ?? 0,
            'perc_real_atual' => $percRealAtual,
            'desvio'          => round($percRealAtual - ($ultimoPlan['plan_pct'] ?? 0), 2),
        ];
    }

    public function gerarMedicaoPorPeriodo($id_obra, $dataInicio, $dataFim) {
        $mesRef = (int) date('n', strtotime($dataInicio));
        $anoRef = (int) date('Y', strtotime($dataInicio));

        $sql = "SELECT
                    oi.id,
                    oi.codigo_referencia,
                    oi.itemizacao,
                    oi.descricao,
                    oi.unidade,
                    oi.preco_unitario,
                    oi.quantidade_total AS qtd_orcada,
                    oi.tipo,

                    -- ANTERIOR: diário + manuais de períodos anteriores
                    COALESCE((
                        SELECT SUM(da.quantidade)
                        FROM diario_atividades da
                        JOIN diario_obras dobr ON da.id_diario = dobr.id
                        WHERE da.id_item_orcamento = oi.id
                        AND dobr.data_diario < :di_ant
                    ), 0)
                    + COALESCE((
                        SELECT SUM(mm.quantidade)
                        FROM medicoes_manuais mm
                        WHERE mm.id_item_orcamento = oi.id
                        AND mm.id_obra = :id_obra_ant
                        AND (mm.ano * 100 + mm.mes) < :periodo_ref
                    ), 0) AS qtd_anterior,

                    -- PERÍODO: soma do diário
                    COALESCE((
                        SELECT SUM(da.quantidade)
                        FROM diario_atividades da
                        JOIN diario_obras dobr ON da.id_diario = dobr.id
                        WHERE da.id_item_orcamento = oi.id
                        AND dobr.data_diario BETWEEN :di_per AND :df_per
                    ), 0) AS qtd_diario_periodo,

                    -- PERÍODO: manual (NULL = não existe lançamento manual)
                    (SELECT mm.quantidade
                     FROM medicoes_manuais mm
                     WHERE mm.id_item_orcamento = oi.id
                     AND mm.id_obra = :id_obra_mm
                     AND mm.mes = :mes_mm AND mm.ano = :ano_mm
                     LIMIT 1) AS qtd_manual_periodo,

                    -- ID do lançamento manual, se existir
                    (SELECT mm.id
                     FROM medicoes_manuais mm
                     WHERE mm.id_item_orcamento = oi.id
                     AND mm.id_obra = :id_obra_mid
                     AND mm.mes = :mes_mid AND mm.ano = :ano_mid
                     LIMIT 1) AS id_medicao_manual,

                    -- Nº de entradas do diário neste período
                    COALESCE((
                        SELECT COUNT(*)
                        FROM diario_atividades da
                        JOIN diario_obras dobr ON da.id_diario = dobr.id
                        WHERE da.id_item_orcamento = oi.id
                        AND dobr.data_diario BETWEEN :di_cnt AND :df_cnt
                    ), 0) AS qtd_entradas_diario,

                    -- IDs dos diários que contêm este item no período
                    (SELECT GROUP_CONCAT(DISTINCT dobr.id ORDER BY dobr.data_diario SEPARATOR ',')
                     FROM diario_atividades da
                     JOIN diario_obras dobr ON da.id_diario = dobr.id
                     WHERE da.id_item_orcamento = oi.id
                     AND dobr.data_diario BETWEEN :di_ids AND :df_ids
                    ) AS ids_diarios_periodo

                FROM orcamento_itens oi
                WHERE oi.id_orcamento IN (SELECT id FROM orcamentos WHERE id_obra = :id_obra)
                ORDER BY oi.id ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id_obra'      => $id_obra,
            ':di_ant'       => $dataInicio,
            ':id_obra_ant'  => $id_obra,
            ':periodo_ref'  => $anoRef * 100 + $mesRef,
            ':di_per'       => $dataInicio,
            ':df_per'       => $dataFim,
            ':id_obra_mm'   => $id_obra,
            ':mes_mm'       => $mesRef,
            ':ano_mm'       => $anoRef,
            ':id_obra_mid'  => $id_obra,
            ':mes_mid'      => $mesRef,
            ':ano_mid'      => $anoRef,
            ':di_cnt'       => $dataInicio,
            ':df_cnt'       => $dataFim,
            ':di_ids'       => $dataInicio,
            ':df_ids'       => $dataFim,
        ]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Resolve qtd_periodo: manual tem precedência sobre diário
        foreach ($rows as &$row) {
            $row['tem_manual']  = $row['qtd_manual_periodo'] !== null;
            $row['tem_diario']  = (int)$row['qtd_entradas_diario'] > 0;
            $row['qtd_periodo'] = $row['tem_manual']
                ? (float)$row['qtd_manual_periodo']
                : (float)$row['qtd_diario_periodo'];
        }
        unset($row);

        return $rows;
    }

    public function criarTabelaMedicoesManual(): void {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS medicoes_manuais (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            id_obra       INT NOT NULL,
            id_item_orcamento INT NOT NULL,
            mes           TINYINT(2)  NOT NULL,
            ano           SMALLINT    NOT NULL,
            quantidade    DECIMAL(12,4) NOT NULL,
            observacoes   TEXT NULL,
            id_usuario    INT NOT NULL,
            criado_em     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_mm (id_obra, id_item_orcamento, mes, ano)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

}