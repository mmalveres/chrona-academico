<?php
namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Diario {
    private $conn;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
    }

    // --- LISTAGENS ---
    public function listarPorObra($id_obra) {
        $sql = "SELECT d.*, u.nome as responsavel_nome 
                FROM diario_obras d 
                LEFT JOIN usuarios u ON d.id_usuario = u.id 
                WHERE d.id_obra = :id 
                ORDER BY d.data_diario DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id_obra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM diario_obras WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- VERIFICAÇÃO DE DATA ---
    public function verificarDuplicidade($id_obra, $data) {
        $sql = "SELECT id FROM diario_obras WHERE id_obra = :id_obra AND data_diario = :data";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_obra' => $id_obra, ':data' => $data]);
        return $stmt->fetchColumn();
    }

    // --- CADASTRO ---
    public function cadastrar($dados) {
        $sql = "INSERT INTO diario_obras (
                    id_obra, data_diario, clima_manha, clima_tarde, 
                    observacoes_gerais, atividades_executadas, ocorrencias, 
                    id_usuario, status
                ) VALUES (
                    :id_obra, :data_diario, :clima_manha, :clima_tarde, 
                    :obs, '', '', 
                    :id_usuario, 'rascunho'
                )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id_obra'    => $dados['id_obra'],
            ':data_diario'=> $dados['data_diario'],
            ':clima_manha'=> $dados['clima_manha'],
            ':clima_tarde'=> $dados['clima_tarde'],
            ':obs'        => $dados['observacoes'],
            ':id_usuario' => $dados['id_usuario']
        ]);
        return $this->conn->lastInsertId();
    }

    public function atualizar($dados) {
        $sql = "UPDATE diario_obras SET 
                data_diario = :dt, 
                clima_manha = :cm, 
                clima_tarde = :ct, 
                observacoes_gerais = :obs 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':dt'  => $dados['data_diario'],
            ':cm'  => $dados['clima_manha'],
            ':ct'  => $dados['clima_tarde'],
            ':obs' => $dados['observacoes'],
            ':id'  => $dados['id']
        ]);
    }

    // --- SALVAR DETALHES (MÃO DE OBRA, ATIVIDADES, EQUIPAMENTOS) ---
    public function salvarDetalhes($id_diario, $maoObra, $atividades, $equipamentos) {
        try {
            $this->conn->beginTransaction();

            // 1. Limpa dados antigos para regravação limpa
            $this->conn->prepare("DELETE FROM diario_mao_obra WHERE id_diario = ?")->execute([$id_diario]);
            $this->conn->prepare("DELETE FROM diario_atividades WHERE id_diario = ?")->execute([$id_diario]);
            $this->conn->prepare("DELETE FROM diario_equipamentos WHERE id_diario = ?")->execute([$id_diario]);

            // 2. Salva Mão de Obra
            if (isset($maoObra['funcao']) && is_array($maoObra['funcao'])) {
                $stmt = $this->conn->prepare("INSERT INTO diario_mao_obra (id_diario, funcao, quantidade, tipo) VALUES (?, ?, ?, ?)");
                foreach ($maoObra['funcao'] as $k => $funcao) {
                    $qtd = $maoObra['quantidade'][$k] ?? 0;
                    $tipo = $maoObra['tipo'][$k] ?? 'direta';
                    if (!empty($funcao) && $qtd > 0) {
                        $stmt->execute([$id_diario, $funcao, $qtd, $tipo]);
                    }
                }
            }

            // 3. Salva Equipamentos
            if (isset($equipamentos['nome']) && is_array($equipamentos['nome'])) {
                $stmt = $this->conn->prepare("INSERT INTO diario_equipamentos (id_diario, nome_equipamento, quantidade) VALUES (?, ?, ?)");
                foreach ($equipamentos['nome'] as $k => $nome) {
                    $qtd = $equipamentos['quantidade'][$k] ?? 0;
                    if (!empty($nome) && $qtd > 0) {
                        $stmt->execute([$id_diario, $nome, $qtd]);
                    }
                }
            }

            // 4. Salva Atividades (LÓGICA HÍBRIDA ATUALIZADA)
            if (isset($atividades['quantidade']) && is_array($atividades['quantidade'])) {
                // Prepara inserção com suporte a descrição manual
                $sqlAtiv = "INSERT INTO diario_atividades (id_diario, id_item_orcamento, descricao_manual, quantidade) 
                            VALUES (:id_diario, :id_item, :desc_manual, :qtd)";
                $stmt = $this->conn->prepare($sqlAtiv);

                foreach ($atividades['quantidade'] as $k => $qtd) {
                    $idItem = $atividades['id_item_orcamento'][$k] ?? null;
                    $descManual = $atividades['descricao_manual'][$k] ?? null;
                    $obsExtra = $atividades['descricao'][$k] ?? ''; // Campo extra de observação

                    // Lógica:
                    // Se idItem vier vazio, salvamos NULL no banco (indica atividade manual).
                    // Se for manual, usamos o texto digitado.
                    // Se for orçamento, concatenamos a obs extra se houver.
                    
                    $idSalvar = !empty($idItem) ? $idItem : null;
                    
                    // Prioriza a descrição manual se existir, senão usa a obs extra
                    // Se for manual e tiver obs extra, junta tudo
                    if (empty($idSalvar)) {
                        $textoSalvar = $descManual . ($obsExtra ? " (" . $obsExtra . ")" : "");
                    } else {
                        $textoSalvar = $obsExtra; // Apenas obs extra, pois o nome vem do JOIN com orçamento
                    }

                    if ($qtd > 0) {
                        $stmt->execute([
                            ':id_diario'   => $id_diario,
                            ':id_item'     => $idSalvar,
                            ':desc_manual' => $textoSalvar,
                            ':qtd'         => $qtd
                        ]);
                    }
                }
            }

            $this->conn->commit();
            return true;

        } catch (\Exception $e) {
            $this->conn->rollBack();
            // Log de erro opcional: error_log($e->getMessage());
            return false;
        }
    }

    // --- GETTERS ---
    public function buscarMaoDeObra($id) {
        $stmt = $this->conn->prepare("SELECT * FROM diario_mao_obra WHERE id_diario = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarEquipamentos($id) {
        $stmt = $this->conn->prepare("SELECT id, id_diario, nome_equipamento as nome, quantidade FROM diario_equipamentos WHERE id_diario = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarAtividades($id) {
        // Busca híbrida: Traz nome do orçamento OU a descrição manual
        $sql = "SELECT da.*, 
                       oi.descricao as nome_servico_orcamento, 
                       oi.unidade, 
                       oi.codigo_referencia 
                FROM diario_atividades da 
                LEFT JOIN orcamento_itens oi ON da.id_item_orcamento = oi.id 
                WHERE da.id_diario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // --- MIDIAS ---
    public function adicionarMidia($id, $caminho, $tipo) {
        $stmt = $this->conn->prepare("INSERT INTO diario_midias (id_diario, caminho_arquivo, tipo) VALUES (?, ?, ?)");
        return $stmt->execute([$id, $caminho, $tipo]);
    }
    
    public function buscarMidias($id) {
        $stmt = $this->conn->prepare("SELECT * FROM diario_midias WHERE id_diario = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function removerMidia($id) {
        return $this->conn->prepare("DELETE FROM diario_midias WHERE id = ?")->execute([$id]);
    }
    
    public function excluir($id) {
        return $this->conn->prepare("DELETE FROM diario_obras WHERE id = ?")->execute([$id]);
    }
    
    // --- ASSINATURA DIGITAL ---
    public function assinar($id, $hash, $arquivo, $idUsuario) {
        $sql = "UPDATE diario_obras SET 
                status = 'assinado', 
                hash_assinatura = ?, 
                assinatura_imagem = ?, 
                data_assinatura = NOW() 
                WHERE id = ?";
        return $this->conn->prepare($sql)->execute([$hash, $arquivo, $id]);
    }

    public function reverterParaRascunho($id) {
        return $this->conn->prepare("UPDATE diario_obras SET status = 'rascunho' WHERE id = ?")->execute([$id]);
    }

    public function buscarPorPeriodo($id_obra, $inicio, $fim, string $status = null) {
        $sql = "SELECT * FROM diario_obras
                WHERE id_obra = :id_obra
                AND data_diario BETWEEN :inicio AND :fim";

        if ($status !== null) {
            $sql .= " AND status = :status";
        }

        $sql .= " ORDER BY data_diario ASC";

        $params = [':id_obra' => $id_obra, ':inicio' => $inicio, ':fim' => $fim];
        if ($status !== null) {
            $params[':status'] = $status;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function listarPorObraEStatus(int $idObra, string $status): array {
    $conn = BancoDeDados::getConexao();
    $stmt = $conn->prepare(
        "SELECT d.*, u.nome as responsavel_nome 
         FROM diario_obras d
         LEFT JOIN usuarios u ON d.id_usuario = u.id
         WHERE d.id_obra = ? AND d.status = ?
         ORDER BY d.data_diario DESC"
    );
    $stmt->execute([$idObra, $status]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
}