<?php
// aplicacao/Controladores/ObraControlador.php

namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Obra;
use Aplicacao\Modelos\Usuario;
use Aplicacao\Modelos\Log;
use Aplicacao\Configuracao\BancoDeDados;
use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Helpers\PlanoHelper;
use Aplicacao\Helpers\CsrfHelper;
use Aplicacao\Helpers\XlsxReader;
use PDOException;
use PDO;

class ObraControlador {

    public function index() { $this->listar(); }

public function listar() {
    Autenticacao::verificarLogin();
 
    $nivel    = $_SESSION['nivel_acesso'] ?? '';
    $obraModelo = new Obra();
 
    // ── CLIENTE: busca obras via tabela usuario_obras_acesso ─────────
    if ($nivel === 'cliente') {
        $conn   = \Aplicacao\Configuracao\BancoDeDados::getConexao();
        $idUser = $_SESSION['usuario_id'] ?? 0;

        $stmt = $conn->prepare(
            "SELECT o.*, u.nome as responsavel_nome
             FROM usuario_obras_acesso uoa
             INNER JOIN obras o ON uoa.id_obra = o.id
             LEFT JOIN usuarios u ON o.id_responsavel_tecnico = u.id
             WHERE uoa.id_usuario = ?
             ORDER BY o.nome ASC"
        );
        $stmt->execute([$idUser]);
        $obras = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($obras)) {
            $semObraVinculada = true;
        } elseif (count($obras) === 1) {
            // Uma única obra — redireciona direto para o diário
            header("Location: index.php?acao=ver_diario&id_obra={$obras[0]['id']}");
            exit;
        }
        // Múltiplas obras — exibe a listagem normalmente
        $totalPaginas = 1;
        $pagina       = 1;
    } else {
        $pagina       = max(1, (int)($_GET['pagina'] ?? 1));
        $porPagina    = 10;
        $totalObrasDB = $obraModelo->contarTotal();
        $totalPaginas = (int)ceil($totalObrasDB / $porPagina);
        $obras        = $obraModelo->listarPaginado($pagina, $porPagina);
    }

    require_once __DIR__ . '/../../visoes/obras/listar.php';
}

    public function criar() {
        Autenticacao::verificarLogin();

        if ($_SESSION['nivel_acesso'] !== 'admin') {
            header('Location: index.php?acao=listar_obras&msg=sem_permissao');
            exit;
        }

        // ── VERIFICAÇÃO DE PLANO: limite de obras ───────────────────────
        $obraModelo = new Obra();
        $totalObras = count($obraModelo->listar());

        if (!PlanoHelper::podeAdicionarObra($totalObras)) {
            $max   = PlanoHelper::getMaxObras();
            $plano = PlanoHelper::getLabelPlano();
            $url   = PlanoHelper::getUrlUpgrade();

            // Carrega cabeçalho e exibe bloqueio amigável
            require_once __DIR__ . '/../../visoes/estruturas/cabecalho.php';
            echo PlanoHelper::htmlBloqueio(
                "criação de mais de {$max} obra(s)",
                'index.php?acao=listar_obras'
            );
            require_once __DIR__ . '/../../visoes/estruturas/rodape.php';
            exit;
        }
        // ────────────────────────────────────────────────────────────────

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $dados = [
                    'nome'                   => $_POST['nome'],
                    'codigo_contrato'        => $_POST['codigo_contrato'] ?? null,
                    'endereco'               => $_POST['endereco'],
                    'cidade'                 => $_POST['cidade'],
                    'data_inicio'            => $_POST['data_inicio'],
                    'data_previsao_fim'      => $_POST['data_previsao_fim'],
                    'status'                 => 'ativa',
                    'id_responsavel_tecnico' => !empty($_POST['id_responsavel_tecnico'])
                                                    ? $_POST['id_responsavel_tecnico']
                                                    : $_SESSION['usuario_id'],
                    'orcamento_previsto'     => $this->converterMoeda($_POST['orcamento_previsto']),
                ];

                if ($obraModelo->cadastrar($dados)) {
                    Log::registrar('Criação', "Cadastrou a obra: " . $_POST['nome']);
                    header('Location: index.php?acao=listar_obras&msg=obra_criada');
                    exit;
                }
            } catch (PDOException $e) {
                header('Location: index.php?acao=nova_obra&erro=' . urlencode("Erro ao salvar no banco: " . $e->getMessage()));
                exit;
            } catch (\Exception $e) {
                header('Location: index.php?acao=nova_obra&erro=' . urlencode($e->getMessage()));
                exit;
            }
        }

        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->listar();
        require_once __DIR__ . '/../../visoes/obras/criar.php';
    }

    public function editar() {
        Autenticacao::verificarLogin();

        if ($_SESSION['nivel_acesso'] !== 'admin') {
            header('Location: index.php?acao=listar_obras');
            exit;
        }

        $id = $_GET['id'];
        $obraModelo = new Obra();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $dados = [
                    'id'                     => $id,
                    'nome'                   => $_POST['nome'],
                    'codigo_contrato'        => $_POST['codigo_contrato'] ?? null,
                    'endereco'               => $_POST['endereco'],
                    'cidade'                 => $_POST['cidade'],
                    'data_inicio'            => $_POST['data_inicio'],
                    'data_previsao_fim'      => $_POST['data_previsao_fim'],
                    'status'                 => $_POST['status'],
                    'id_responsavel_tecnico' => $_POST['id_responsavel_tecnico'],
                    'orcamento_previsto'     => $this->converterMoeda($_POST['orcamento_previsto']),
                ];

                if ($obraModelo->atualizar($dados)) {
                    Log::registrar('Edição', "Atualizou a Obra ID: $id");
                    header('Location: index.php?acao=listar_obras&msg=obra_atualizada');
                    exit;
                }
            } catch (PDOException $e) {
                header('Location: index.php?acao=editar_obra&id=' . $id . '&erro=' . urlencode("Erro no Banco de Dados: " . $e->getMessage()));
                exit;
            }
        }

        $obra = $obraModelo->buscarPorId($id);
        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->listar();
        require_once __DIR__ . '/../../visoes/obras/editar.php';
    }

    public function excluir() {
        Autenticacao::verificarLogin();
        CsrfHelper::verificarOuAbortar();
        if ($_SESSION['nivel_acesso'] !== 'admin') { header('Location: index.php?acao=listar_obras'); exit; }
        $id = $_GET['id'];
        $obraModelo = new Obra();
        $obra = $obraModelo->buscarPorId($id);

        $conn = BancoDeDados::getConexao();
        try {
            $conn->beginTransaction();
            $conn->prepare("DELETE FROM documentos WHERE id_obra = ?")->execute([$id]);
            $stmtDiarios = $conn->prepare("SELECT id FROM diario_obras WHERE id_obra = ?");
            $stmtDiarios->execute([$id]);
            $diarios = $stmtDiarios->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($diarios)) {
                $idsString = implode(',', $diarios);
                $conn->query("DELETE FROM diario_midias WHERE id_diario IN ($idsString)");
                $conn->query("DELETE FROM diario_atividades WHERE id_diario IN ($idsString)");
                $conn->query("DELETE FROM diario_mao_obra WHERE id_diario IN ($idsString)");
                $conn->query("DELETE FROM diario_equipamentos WHERE id_diario IN ($idsString)");
                $conn->prepare("DELETE FROM diario_obras WHERE id_obra = ?")->execute([$id]);
            }
            $conn->prepare("DELETE FROM financeiro_movimentacoes WHERE id_obra = ?")->execute([$id]);
            $conn->prepare("DELETE FROM orcamento_itens WHERE id_orcamento IN (SELECT id FROM orcamentos WHERE id_obra = ?)")->execute([$id]);
            $conn->prepare("DELETE FROM orcamentos WHERE id_obra = ?")->execute([$id]);
            $obraModelo->excluir($id);
            $conn->commit();
            header('Location: index.php?acao=listar_obras&msg=obra_excluida'); exit;
        } catch (\Exception $e) {
            $conn->rollBack();
            header('Location: index.php?acao=listar_obras&msg=erro_exclusao'); exit;
        }
    }

    // ── CURVA S ────────────────────────────────────────────────────────
    public function curvaS() {
        Autenticacao::verificarLogin();

        if (!PlanoHelper::podeUsarCronograma()) {
            require_once __DIR__ . '/../../visoes/estruturas/cabecalho.php';
            echo PlanoHelper::htmlBloqueio('Curva S', 'index.php?acao=listar_obras');
            require_once __DIR__ . '/../../visoes/estruturas/rodape.php';
            exit;
        }

        $id_obra   = (int)($_GET['id_obra'] ?? 0);
        $obraModel = new Obra();
        $obra      = $obraModel->buscarPorId($id_obra);

        if (!$obra) {
            header('Location: index.php?acao=listar_obras');
            exit;
        }

        $dadosCurva = $obraModel->calcularCurvaS($id_obra);

        require_once __DIR__ . '/../../visoes/obras/curva_s.php';
    }

    // ── MEDIÇÃO ────────────────────────────────────────────────────────
    public function medicao() {
        Autenticacao::verificarLogin();

        if (!PlanoHelper::podeUsarMedicao()) {
            require_once __DIR__ . '/../../visoes/estruturas/cabecalho.php';
            echo PlanoHelper::htmlBloqueio('Medição Automática', 'index.php?acao=listar_obras');
            require_once __DIR__ . '/../../visoes/estruturas/rodape.php';
            exit;
        }

        $id_obra = (int)($_GET['id_obra'] ?? 0);
        $mes     = (int)($_GET['mes']     ?? date('m'));
        $ano     = (int)($_GET['ano']     ?? date('Y'));

        $dataInicio = "$ano-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
        $dataFim    = date("Y-m-t", strtotime($dataInicio));

        $obraModelo = new Obra();
        $obraModelo->criarTabelaMedicoesManual(); // auto-migrate
        $obra         = $obraModelo->buscarPorId($id_obra);
        $itensMedicao = $obraModelo->gerarMedicaoPorPeriodo($id_obra, $dataInicio, $dataFim);

        require_once __DIR__ . '/../../visoes/obras/medicao.php';
    }

    public function salvarMedicaoManual() {
        Autenticacao::verificarLogin();
        header('Content-Type: application/json');

        if (!PlanoHelper::podeUsarMedicao()) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Plano não permite esta ação.']);
            exit;
        }

        $input    = json_decode(file_get_contents('php://input'), true) ?? [];
        $idObra   = (int)($input['id_obra']    ?? 0);
        $idItem   = (int)($input['id_item']    ?? 0);
        $mes      = (int)($input['mes']        ?? 0);
        $ano      = (int)($input['ano']        ?? 0);
        $qtd      = (float)($input['quantidade'] ?? 0);
        $obs      = trim($input['observacoes'] ?? '');
        $forcar   = (bool)($input['forcar']    ?? false);
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);

        if (!$idObra || !$idItem || !$mes || !$ano || $qtd < 0) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
            exit;
        }

        try {
            $conn = \Aplicacao\Configuracao\BancoDeDados::getConexao();

            $dataInicio = "$ano-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
            $dataFim    = date("Y-m-t", strtotime($dataInicio));

            // Busca entradas do diário para o item neste período
            $stmtD = $conn->prepare(
                "SELECT da.id, da.quantidade, dobr.id AS id_diario, dobr.data_diario
                 FROM diario_atividades da
                 JOIN diario_obras dobr ON da.id_diario = dobr.id
                 WHERE da.id_item_orcamento = :id_item
                   AND dobr.id_obra = :id_obra
                   AND dobr.data_diario BETWEEN :di AND :df
                 ORDER BY dobr.data_diario ASC, da.id ASC"
            );
            $stmtD->execute([':id_item' => $idItem, ':id_obra' => $idObra,
                              ':di' => $dataInicio, ':df' => $dataFim]);
            $entradasDiario = $stmtD->fetchAll(\PDO::FETCH_ASSOC);

            // Tem entradas do diário E ainda não confirmou → pede confirmação
            if (!empty($entradasDiario) && !$forcar) {
                $totalDiario = array_sum(array_column($entradasDiario, 'quantidade'));
                echo json_encode([
                    'sucesso'            => false,
                    'requer_confirmacao' => true,
                    'total_diario'       => (float)$totalDiario,
                    'entradas'           => array_map(fn($e) => [
                        'id_diario'  => (int)$e['id_diario'],
                        'data'       => date('d/m/Y', strtotime($e['data_diario'])),
                        'quantidade' => (float)$e['quantidade'],
                    ], $entradasDiario),
                ]);
                exit;
            }

            // Tem entradas do diário e usuário confirmou → ajusta o diário
            if (!empty($entradasDiario) && $forcar) {
                $totalAtual = array_sum(array_column($entradasDiario, 'quantidade'));
                $diff       = $qtd - $totalAtual;
                $ultimo     = end($entradasDiario);
                $novaQtd    = round(max(0, (float)$ultimo['quantidade'] + $diff), 4);
                $conn->prepare("UPDATE diario_atividades SET quantidade = ? WHERE id = ?")
                     ->execute([$novaQtd, $ultimo['id']]);

                echo json_encode(['sucesso' => true, 'modo' => 'diario',
                                  'nova_quantidade' => $novaQtd]);
                exit;
            }

            // Sem entradas do diário → lançamento manual (upsert)
            $conn->prepare(
                "INSERT INTO medicoes_manuais
                    (id_obra, id_item_orcamento, mes, ano, quantidade, observacoes, id_usuario)
                 VALUES (:id_obra, :id_item, :mes, :ano, :qtd, :obs, :uid)
                 ON DUPLICATE KEY UPDATE
                    quantidade = :qtd2, observacoes = :obs2,
                    id_usuario = :uid2, atualizado_em = NOW()"
            )->execute([
                ':id_obra' => $idObra, ':id_item' => $idItem,
                ':mes'     => $mes,    ':ano'     => $ano,
                ':qtd'     => $qtd,    ':obs'     => $obs ?: null, ':uid' => $idUsuario,
                ':qtd2'    => $qtd,    ':obs2'    => $obs ?: null, ':uid2' => $idUsuario,
            ]);

            echo json_encode(['sucesso' => true, 'modo' => 'manual']);

        } catch (\Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()]);
        }
        exit;
    }

    public function imprimirMedicao() {
        Autenticacao::verificarLogin();

        // ── VERIFICAÇÃO DE PLANO ──────────────────────────────────────
        if (!PlanoHelper::podeUsarMedicao()) {
            require_once __DIR__ . '/../../visoes/estruturas/cabecalho.php';
            echo PlanoHelper::htmlBloqueio(
                'Medição Automática',
                'index.php?acao=listar_obras'
            );
            require_once __DIR__ . '/../../visoes/estruturas/rodape.php';
            exit;
        }
        // ─────────────────────────────────────────────────────────────

        $id_obra = $_GET['id_obra'] ?? 0;
        $mes     = $_GET['mes'] ?? date('m');
        $ano     = $_GET['ano'] ?? date('Y');
        $dataInicio = "$ano-$mes-01";
        $dataFim    = date("Y-m-t", strtotime($dataInicio));

        $obraModelo   = new Obra();
        $obra         = $obraModelo->buscarPorId($id_obra);
        $itensMedicao = $obraModelo->gerarMedicaoPorPeriodo($id_obra, $dataInicio, $dataFim);

        require_once __DIR__ . '/../../visoes/obras/imprimir_medicao.php';
    }

    // ── IMPORTAR ORÇAMENTO ─────────────────────────────────────────────
    public function importarOrcamento() {
        Autenticacao::verificarLogin();

        // ── VERIFICAÇÃO DE PLANO ──────────────────────────────────────
        if (!PlanoHelper::podeImportarOrcamento()) {
            require_once __DIR__ . '/../../visoes/estruturas/cabecalho.php';
            echo PlanoHelper::htmlBloqueio(
                'Importação de Orçamento',
                'index.php?acao=listar_obras'
            );
            require_once __DIR__ . '/../../visoes/estruturas/rodape.php';
            exit;
        }
        // ─────────────────────────────────────────────────────────────

        set_time_limit(300);

        $id_obra     = $_POST['id_obra'] ?? 0;
        $modo        = $_POST['modo_importacao'] ?? 'atualizar';
        $linhaInicio = max(1, (int)($_POST['linha_inicio'] ?? 2)) - 1; // converte para índice 0-based

        $idxCodigo     = $this->letraParaIndice($_POST['col_codigo']     ?? 'A');
        $idxItemizacao = $this->letraParaIndice($_POST['col_itemizacao'] ?? 'B');
        $idxDescricao  = $this->letraParaIndice($_POST['col_descricao']  ?? 'C');
        $idxUnidade    = $this->letraParaIndice($_POST['col_unidade']    ?? 'D');
        $idxQtd        = $this->letraParaIndice($_POST['col_qtd']        ?? 'E');
        $idxPreco      = $this->letraParaIndice($_POST['col_preco']      ?? 'F');
        $maxIdx        = max($idxCodigo, $idxItemizacao, $idxDescricao, $idxUnidade, $idxQtd, $idxPreco);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_csv'])) {
            $arquivo = $_FILES['arquivo_csv'];

            if ($arquivo['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

                // Carrega as linhas conforme o formato do arquivo
                if ($ext === 'xlsx') {
                    $linhas = XlsxReader::lerLinhas($arquivo['tmp_name']);
                    if (empty($linhas)) {
                        header("Location: index.php?acao=listar_obras&msg=erro_importacao");
                        exit;
                    }
                } else {
                    // CSV: lê todas as linhas para array uniforme
                    $handle = fopen($arquivo['tmp_name'], "r");
                    if (!$handle) die("Erro Crítico: Não foi possível abrir o arquivo temporário.");
                    $linhas = [];
                    while (($dados = fgetcsv($handle, 0, ";")) !== false) {
                        $linhas[] = $dados;
                    }
                    fclose($handle);
                }

                $conn = BancoDeDados::getConexao();

                try {
                    $conn->beginTransaction();

                    $stmtOrc = $conn->prepare("SELECT id FROM orcamentos WHERE id_obra = ?");
                    $stmtOrc->execute([$id_obra]);
                    $id_orcamento = $stmtOrc->fetchColumn();

                    if (!$id_orcamento) {
                        $stmt = $conn->prepare("INSERT INTO orcamentos (id_obra, nome_arquivo, id_usuario) VALUES (?, ?, ?)");
                        $stmt->execute([$id_obra, $arquivo['name'], $_SESSION['usuario_id']]);
                        $id_orcamento = $conn->lastInsertId();
                    } else {
                        $conn->prepare("UPDATE orcamentos SET nome_arquivo = ?, id_usuario = ?, data_upload = NOW() WHERE id = ?")
                             ->execute([$arquivo['name'], $_SESSION['usuario_id'], $id_orcamento]);
                    }

                    $mapaExistentes = [];
                    if ($modo === 'substituir') {
                        $conn->prepare("DELETE FROM orcamento_itens WHERE id_orcamento = ?")->execute([$id_orcamento]);
                    } else {
                        $stmtEx = $conn->prepare("SELECT id, codigo_referencia FROM orcamento_itens WHERE id_orcamento = ?");
                        $stmtEx->execute([$id_orcamento]);
                        while ($row = $stmtEx->fetch(\PDO::FETCH_ASSOC)) {
                            $chave = trim($row['codigo_referencia']);
                            if (!empty($chave)) $mapaExistentes[$chave] = $row['id'];
                        }
                    }

                    $stmtInsert = $conn->prepare("INSERT INTO orcamento_itens (id_orcamento, codigo_referencia, itemizacao, descricao, unidade, quantidade_total, preco_unitario, tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtUpdate = $conn->prepare("UPDATE orcamento_itens SET itemizacao = ?, descricao = ?, unidade = ?, quantidade_total = ?, preco_unitario = ?, tipo = ? WHERE id = ?");

                    $contadorNovos       = 0;
                    $contadorAtualizados = 0;

                    foreach ($linhas as $idx => $dados) {
                        if ($idx < $linhaInicio) continue; // pula cabeçalho
                        if (count($dados) <= $maxIdx) {
                            $dados = array_pad($dados, $maxIdx + 1, '');
                        }

                        $codRef       = trim($dados[$idxCodigo]      ?? '');
                        $itemizacao   = trim($dados[$idxItemizacao]   ?? '');
                        $rawDescricao = trim($dados[$idxDescricao]    ?? '');
                        $unidade      = trim($dados[$idxUnidade]      ?? '');
                        $rawQtd       = $dados[$idxQtd]   ?? '0';
                        $rawPreco     = $dados[$idxPreco]  ?? '0';

                        if (empty($rawDescricao)) continue;
                        if (stripos($rawDescricao, 'DESCRIÇÃO') !== false || stripos($rawDescricao, 'Preço Unit') !== false) continue;

                        $descricao = mb_check_encoding($rawDescricao, 'UTF-8')
                            ? $rawDescricao
                            : mb_convert_encoding($rawDescricao, 'UTF-8', 'ISO-8859-1');

                        $qtd   = $this->converterNumero($rawQtd);
                        $preco = $this->converterNumero($rawPreco);

                        $tipo = $this->detectarTipoItem($codRef, $itemizacao, $unidade);

                        try {
                            if ($modo === 'atualizar' && isset($mapaExistentes[$codRef]) && !empty($codRef)) {
                                $stmtUpdate->execute([$itemizacao, $descricao, $unidade, $qtd, $preco, $tipo, $mapaExistentes[$codRef]]);
                                $contadorAtualizados++;
                            } else {
                                $stmtInsert->execute([$id_orcamento, $codRef, $itemizacao, $descricao, $unidade, $qtd, $preco, $tipo]);
                                $contadorNovos++;
                            }
                        } catch (\Exception $eItem) {
                            continue;
                        }
                    }

                    $conn->commit();

                    $detalhe = ($modo === 'substituir')
                        ? "Substituídos: $contadorNovos"
                        : "Atualizados: $contadorAtualizados | Novos: $contadorNovos";

                    header("Location: index.php?acao=listar_obras&msg=orcamento_sucesso&detalhe=" . urlencode($detalhe));
                    exit;

                } catch (\Exception $e) {
                    $conn->rollBack();
                    echo "<div style='padding:20px;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;margin:20px;font-family:sans-serif;'>";
                    echo "<h3>❌ Erro Detalhado na Importação:</h3>";
                    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
                    echo "<a href='index.php?acao=listar_obras'>Voltar</a>";
                    echo "</div>";
                    exit;
                }
            }
        }
        header("Location: index.php?acao=listar_obras&msg=erro_upload");
        exit;
    }

    // ── AUXILIARES ─────────────────────────────────────────────────────

    // Detecta se um item é etapa (grupo) ou composicao (item real)
    // Verifica codigo_referencia e itemizacao — funciona mesmo que o código
    // hierárquico esteja em qualquer um dos dois campos
    private function detectarTipoItem(string $codRef, string $itemizacao, string $unidade): string {
        // Função interna: conta pontos após remover ponto final (formato SINAPI)
        $contarPontos = function(string $s): int {
            return substr_count(rtrim(trim($s), '.'), '.');
        };

        // Padrão numérico hierárquico: primeiro segmento com no máximo 3 dígitos
        // (ex: 1, 1.1, 1.1.1, 10.2.3) — exclui códigos SINAPI (5-6 dígitos como 88316)
        $ehHierarquico = fn(string $s): bool => (bool) preg_match('/^[1-9]\d{0,2}(\.\d+)*\.?$/', trim($s));

        // Prioridade: checa codRef primeiro, depois itemizacao
        foreach ([$codRef, $itemizacao] as $candidato) {
            if (!empty($candidato) && $ehHierarquico($candidato)) {
                return ($contarPontos($candidato) >= 2) ? 'composicao' : 'etapa';
            }
        }

        // Nenhum campo hierárquico encontrado: usa unidade como fallback
        return !empty($unidade) ? 'composicao' : 'etapa';
    }

    private function letraParaIndice($letra) {
        $letra = strtoupper(trim($letra));
        if (empty($letra)) return 0;
        return ord($letra) - 65;
    }

    private function converterNumero($valor): float {
        $valor = trim((string)$valor);
        if ($valor === '') return 0.0;
        // Remove tudo exceto dígitos, ponto e vírgula
        $valor = preg_replace('/[^0-9.,]/', '', $valor);
        if ($valor === '') return 0.0;

        if (strpos($valor, ',') !== false) {
            // Formato brasileiro: 1.500,50 — remove milhar e troca vírgula por ponto
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        // Formato XLSX/internacional: 1500.50 — já está correto
        return (float) $valor;
    }

    private function converterMoeda($valor) {
        return $this->converterNumero($valor);
    }
}