<?php
namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Diario;
use Aplicacao\Modelos\Obra;
use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Configuracao\BancoDeDados;
use Aplicacao\Helpers\CsrfHelper;

class DiarioControlador {

    public function novo() {
        Autenticacao::verificarLogin();
        $id_obra = $_GET['id_obra'] ?? 0;

        // Bloqueia cliente de criar diário
        if (($_SESSION['nivel_acesso'] ?? '') === 'cliente') {
            header('Location: index.php?acao=ver_diario&id_obra=' . $id_obra);
            exit;
        }

        $obraModel = new Obra();
        $obra = $obraModel->buscarPorIdGlobal($id_obra);
        $itensOrcamento = $obraModel->listarItensParaDiario($id_obra);

        $catalogoModelo = new \Aplicacao\Modelos\Catalogo();
        $listaMaoObra = $catalogoModelo->listarMaoObra();
        $listaEquipamentos = $catalogoModelo->listarEquipamentos();

        require_once __DIR__ . '/../../visoes/diario/novo.php';
    }

    public function salvar_novo() {
        Autenticacao::verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $diarioModel = new Diario();
            $id_obra = $_POST['id_obra'];

            $existe = $diarioModel->verificarDuplicidade($id_obra, $_POST['data_diario']);
            if ($existe) {
                header("Location: index.php?acao=ver_diario&id_obra=$id_obra&msg=erro_duplicado");
                exit;
            }

            $dados = [
                'id_obra'     => $id_obra,
                'data_diario' => $_POST['data_diario'],
                'clima_manha' => $_POST['clima_manha'],
                'clima_tarde' => $_POST['clima_tarde'],
                'observacoes' => $_POST['observacoes'],
                'id_usuario'  => $_SESSION['usuario_id'] ?? 1,
            ];

            $idDiario = $diarioModel->cadastrar($dados);

            if ($idDiario) {
                $this->processarUploadFotos($idDiario);

                // Filtra atividades cujos itens têm medição manual no mês do diário
                $atividades = $_POST['atividades'] ?? [];
                $atividades = $this->filtrarAtividadesBloqueadas(
                    $atividades,
                    (int)$id_obra,
                    $_POST['data_diario']
                );

                $diarioModel->salvarDetalhes(
                    $idDiario,
                    $_POST['mao_obra']    ?? [],
                    $atividades,
                    $_POST['equipamentos'] ?? []
                );

                header("Location: index.php?acao=ver_diario&id_obra=$id_obra&msg=sucesso_criacao");
                exit;
            }
        }
    }

    public function editar() {
        Autenticacao::verificarLogin();
        $id = $_GET['id'];
        $diarioModel = new Diario();
        $diario = $diarioModel->buscarPorId($id);

        if ($diario['status'] === 'assinado') {
            header('Location: index.php?acao=ver_diario&id_obra=' . $diario['id_obra']);
            exit;
        }

        $maoObra         = $diarioModel->buscarMaoDeObra($id);
        $equipamentos    = $diarioModel->buscarEquipamentos($id);
        $listaAtividades = $diarioModel->buscarAtividades($id);
        $fotos           = $diarioModel->buscarMidias($id);

        $obraModel = new Obra();
        $obra = $obraModel->buscarPorIdGlobal($diario['id_obra']);
        $itensOrcamento = $obraModel->listarItensParaDiario($diario['id_obra']);

        $catalogoModelo = new \Aplicacao\Modelos\Catalogo();
        $listaMaoObra = $catalogoModelo->listarMaoObra();
        $listaEquipamentos = $catalogoModelo->listarEquipamentos();

        require_once __DIR__ . '/../../visoes/diario/editar.php';
    }

    public function atualizar() {
        Autenticacao::verificarLogin();
        $id = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $diarioModel    = new Diario();
            $diarioOriginal = $diarioModel->buscarPorId($id);

            $dados = [
                'id'          => $id,
                'data_diario' => $_POST['data_diario'],
                'clima_manha' => $_POST['clima_manha'],
                'clima_tarde' => $_POST['clima_tarde'],
                'observacoes' => $_POST['observacoes'],
            ];

            if ($diarioModel->atualizar($dados)) {
                $this->processarUploadFotos($id);

                $diarioModel->salvarDetalhes(
                    $id,
                    $_POST['mao_obra']     ?? [],
                    $_POST['atividades']   ?? [],
                    $_POST['equipamentos'] ?? []
                );

                header('Location: index.php?acao=ver_diario&id_obra=' . $diarioOriginal['id_obra'] . '&msg=sucesso_edicao');
                exit;
            }
        }
    }

    // ════════════════════════════════════════════════════════════
    // LISTAR — com restrição de cliente (nova tabela multi-obras)
    // ════════════════════════════════════════════════════════════
    public function listar() {
        Autenticacao::verificarLogin();

        $id_obra     = (int)($_GET['id_obra'] ?? 0);
        $nivel       = $_SESSION['nivel_acesso'] ?? '';
        $obraModel   = new Obra();
        $diarioModel = new Diario();

        if ($nivel === 'cliente') {
            $conn = BancoDeDados::getConexao();

            // Verifica se o cliente tem acesso a esta obra
            $stmt = $conn->prepare(
                "SELECT COUNT(*) FROM usuario_obras_acesso
                 WHERE id_usuario = ? AND id_obra = ?"
            );
            $stmt->execute([$_SESSION['usuario_id'], $id_obra]);

            if ($stmt->fetchColumn() == 0) {
                // Não tem acesso — manda para o painel de seleção
                header('Location: index.php?acao=painel_cliente');
                exit;
            }

            // Só vê diários ASSINADOS
            $diarios = $diarioModel->listarPorObraEStatus($id_obra, 'assinado');

        } else {
            // Demais perfis veem tudo
            $diarios = $diarioModel->listarPorObra($id_obra);
        }

        $obra = $obraModel->buscarPorIdGlobal($id_obra);
        require_once __DIR__ . '/../../visoes/diario/listar.php';
    }

    public function excluir() {
        Autenticacao::verificarLogin();
        CsrfHelper::verificarOuAbortar();
        $id = $_GET['id'];
        $diarioModel = new Diario();
        $diario = $diarioModel->buscarPorId($id);

        if ($diario) {
            $id_obra = $diario['id_obra'];
            $diarioModel->excluir($id);
            header("Location: index.php?acao=ver_diario&id_obra=$id_obra&msg=excluido");
            exit;
        }
        header("Location: index.php?acao=listar_obras");
    }

    public function imprimir() {
        Autenticacao::verificarLogin();
        $id = $_GET['id'];

        $diarioModel = new Diario();
        $diario = $diarioModel->buscarPorId($id);
        if (!$diario) die("Diário não encontrado.");

        $obraModel    = new Obra();
        $obra         = $obraModel->buscarPorIdGlobal($diario['id_obra']);
        $maoObra      = $diarioModel->buscarMaoDeObra($id);
        $equipamentos = $diarioModel->buscarEquipamentos($id);
        $atividades   = $diarioModel->buscarAtividades($id);
        $fotos        = $diarioModel->buscarMidias($id);

        require_once __DIR__ . '/../../visoes/diario/imprimir.php';
    }

    public function imprimirLote() {
        Autenticacao::verificarLogin();

        $id_obra = $_REQUEST['id_obra'] ?? 0;
        $inicio  = $_REQUEST['data_inicio'] ?? null;
        $fim     = $_REQUEST['data_fim']    ?? null;

        if ($id_obra && $inicio && $fim) {
            $diarioModel = new Diario();
            $obraModel   = new Obra();
            $obra        = $obraModel->buscarPorIdGlobal($id_obra);
            $nivel       = $_SESSION['nivel_acesso'] ?? '';
            $statusFiltro = ($nivel === 'cliente') ? 'assinado' : null;
            $diarios     = $diarioModel->buscarPorPeriodo($id_obra, $inicio, $fim, $statusFiltro);

            foreach ($diarios as &$d) {
                $d['atividades']  = $diarioModel->buscarAtividades($d['id']);
                $d['mao_obra']    = $diarioModel->buscarMaoDeObra($d['id']);
                $d['equipamentos']= $diarioModel->buscarEquipamentos($d['id']);
                $d['fotos']       = $diarioModel->buscarMidias($d['id']);
            }

            require_once __DIR__ . '/../../visoes/diario/relatorio.php';
        } else {
            header("Location: index.php?acao=ver_diario&id_obra=$id_obra");
        }
    }

    public function assinar() {
        Autenticacao::verificarLogin();
        $id = $_GET['id'];
        $diarioModel = new Diario();
        $diario = $diarioModel->buscarPorId($id);
        if (!$diario) die('Diário não encontrado.');
        $idObra = $diario['id_obra'];
        require_once __DIR__ . '/../../visoes/diario/assinar.php';
    }

    public function salvar_assinatura() {
        Autenticacao::verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_GET['id'] ?? $_POST['id'];
            $diarioModel = new Diario();
            $diario = $diarioModel->buscarPorId($id);

            $diretorio = __DIR__ . '/../../publico/uploads/assinaturas/';
            if (!is_dir($diretorio)) mkdir($diretorio, 0755, true);

            $nomeArquivo     = null;
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!empty($_FILES['imagem_assinatura']['name'])) {
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $_FILES['imagem_assinatura']['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, $tiposPermitidos)) {
                    header('Location: index.php?acao=assinar&id=' . $id . '&erro=tipo_invalido');
                    exit;
                }

                $ext = pathinfo($_FILES['imagem_assinatura']['name'], PATHINFO_EXTENSION);
                $nomeArquivo = 'assinatura_rdo_' . $id . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['imagem_assinatura']['tmp_name'], $diretorio . $nomeArquivo);
            } elseif (!empty($_POST['assinatura_base64'])) {
                $data = explode(',', $_POST['assinatura_base64']);
                $conteudo = base64_decode(end($data));
                $nomeArquivo = 'assinatura_rdo_' . $id . '_' . uniqid() . '.png';
                file_put_contents($diretorio . $nomeArquivo, $conteudo);
            }

            if ($nomeArquivo) {
                $hash = md5($nomeArquivo . date('Y-m-d H:i:s'));
                $diarioModel->assinar($id, $hash, $nomeArquivo, $_SESSION['usuario_id']);
                header('Location: index.php?acao=ver_diario&id_obra=' . $diario['id_obra'] . '&msg=assinado');
            } else {
                header('Location: index.php?acao=assinar&id=' . $id . '&erro=vazio');
            }
            exit;
        }
    }

    public function reabrir() {
        Autenticacao::verificarLogin();
        CsrfHelper::verificarOuAbortar();
        if (!in_array($_SESSION['nivel_acesso'], ['admin', 'engenheiro'])) die("Acesso negado.");
        $id = $_GET['id'];
        $diarioModel = new Diario();
        $diario = $diarioModel->buscarPorId($id);
        $diarioModel->reverterParaRascunho($id);
        header('Location: index.php?acao=ver_diario&id_obra=' . $diario['id_obra'] . '&msg=reaberto');
        exit;
    }

    public function assinarLote() {
        Autenticacao::verificarLogin();
        if (!in_array($_SESSION['nivel_acesso'] ?? '', ['admin', 'engenheiro'])) die("Acesso negado.");

        $idObra = (int)($_GET['id_obra'] ?? 0);
        $obraModel = new Obra();
        $obra = $obraModel->buscarPorIdGlobal($idObra);
        if (!$obra) die('Obra não encontrada.');

        $diarioModel = new Diario();
        $diariosAbertos = $diarioModel->listarPorObraEStatus($idObra, 'rascunho');

        if (empty($diariosAbertos)) {
            header('Location: index.php?acao=ver_diario&id_obra=' . $idObra . '&msg=sem_pendentes');
            exit;
        }

        require_once __DIR__ . '/../../visoes/diario/assinar_lote.php';
    }

    public function salvarAssinaturaLote() {
        Autenticacao::verificarLogin();
        if (!in_array($_SESSION['nivel_acesso'] ?? '', ['admin', 'engenheiro'])) die("Acesso negado.");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idObra = (int)($_POST['id_obra'] ?? 0);
            $diarioModel = new Diario();
            $diariosAbertos = $diarioModel->listarPorObraEStatus($idObra, 'rascunho');

            if (empty($diariosAbertos)) {
                header('Location: index.php?acao=ver_diario&id_obra=' . $idObra . '&msg=sem_pendentes');
                exit;
            }

            $diretorio = __DIR__ . '/../../publico/uploads/assinaturas/';
            if (!is_dir($diretorio)) mkdir($diretorio, 0755, true);

            $nomeArquivo     = null;
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!empty($_FILES['imagem_assinatura']['name'])) {
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $_FILES['imagem_assinatura']['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, $tiposPermitidos)) {
                    header('Location: index.php?acao=assinar_lote&id_obra=' . $idObra . '&erro=tipo_invalido');
                    exit;
                }

                $ext = pathinfo($_FILES['imagem_assinatura']['name'], PATHINFO_EXTENSION);
                $nomeArquivo = 'assinatura_lote_' . $idObra . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['imagem_assinatura']['tmp_name'], $diretorio . $nomeArquivo);
            } elseif (!empty($_POST['assinatura_base64'])) {
                $data = explode(',', $_POST['assinatura_base64']);
                $conteudo = base64_decode(end($data));
                $nomeArquivo = 'assinatura_lote_' . $idObra . '_' . uniqid() . '.png';
                file_put_contents($diretorio . $nomeArquivo, $conteudo);
            }

            if (!$nomeArquivo) {
                header('Location: index.php?acao=assinar_lote&id_obra=' . $idObra . '&erro=vazio');
                exit;
            }

            foreach ($diariosAbertos as $diario) {
                $hash = md5($nomeArquivo . $diario['id'] . microtime());
                $diarioModel->assinar($diario['id'], $hash, $nomeArquivo, $_SESSION['usuario_id']);
            }

            header('Location: index.php?acao=ver_diario&id_obra=' . $idObra . '&msg=assinado_lote');
            exit;
        }
    }

    private function processarUploadFotos($idDiario) {
        if (!empty($_FILES['fotos']['name'][0])) {
            $diarioModel     = new Diario();
            $diretorio       = __DIR__ . '/../../publico/uploads/';
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!is_dir($diretorio)) mkdir($diretorio, 0755, true);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            foreach ($_FILES['fotos']['name'] as $key => $nome) {
                if ($_FILES['fotos']['error'][$key] !== 0) continue;

                $mimeType = finfo_file($finfo, $_FILES['fotos']['tmp_name'][$key]);
                if (!in_array($mimeType, $tiposPermitidos)) continue;

                // Salva sempre como JPEG comprimido para reduzir tamanho
                $novoNome = 'rdo_' . $idDiario . '_' . uniqid() . '.jpg';
                $destino  = $diretorio . $novoNome;

                if (move_uploaded_file($_FILES['fotos']['tmp_name'][$key], $destino)) {
                    $this->redimensionarImagem($destino, $mimeType);
                    $diarioModel->adicionarMidia($idDiario, $novoNome, 'imagem');
                }
            }
            finfo_close($finfo);
        }
    }

    private function redimensionarImagem(string $caminho, string $mimeOriginal): void {
        if (!function_exists('imagecreatefromjpeg')) return;

        $maxLargura = 1280;
        $maxAltura  = 720;
        $qualidade  = 75;

        [$largOrig, $altOrig] = getimagesize($caminho);

        // Só processa se for maior que o limite
        if ($largOrig <= $maxLargura && $altOrig <= $maxAltura) {
            // Apenas re-salva como JPEG comprimido sem redimensionar
            $src = $this->criarImagemDe($caminho, $mimeOriginal);
            if ($src) {
                imagejpeg($src, $caminho, $qualidade);
                imagedestroy($src);
            }
            return;
        }

        // Calcula novas dimensões mantendo proporção
        $ratio    = min($maxLargura / $largOrig, $maxAltura / $altOrig);
        $novLarg  = (int)round($largOrig * $ratio);
        $novAlt   = (int)round($altOrig  * $ratio);

        $src = $this->criarImagemDe($caminho, $mimeOriginal);
        if (!$src) return;

        $dst = imagecreatetruecolor($novLarg, $novAlt);

        // Preserva transparência para PNG/WebP
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $novLarg, $novAlt, $largOrig, $altOrig);
        imagejpeg($dst, $caminho, $qualidade);

        imagedestroy($src);
        imagedestroy($dst);
    }

    private function criarImagemDe(string $caminho, string $mime): \GdImage|false {
        return match($mime) {
            'image/jpeg' => imagecreatefromjpeg($caminho),
            'image/png'  => imagecreatefrompng($caminho),
            'image/gif'  => imagecreatefromgif($caminho),
            'image/webp' => imagecreatefromwebp($caminho),
            default      => false,
        };
    }

    public function excluir_foto() {
        Autenticacao::verificarLogin();
        $idFoto   = $_GET['id_foto'];
        $idDiario = $_GET['id_diario'];
        $diarioModel = new Diario();
        $diarioModel->removerMidia($idFoto);
        header("Location: index.php?acao=editar_diario&id=$idDiario");
        exit;
    }

    /**
     * Remove das atividades os itens que já têm medição manual
     * no mês/ano do diário, para não sobrescrever o lançamento manual.
     */
    private function filtrarAtividadesBloqueadas(array $atividades, int $idObra, string $dataAtividade): array {
        if (empty($atividades['id_item_orcamento'] ?? [])) return $atividades;

        $mes = (int) date('n', strtotime($dataAtividade));
        $ano = (int) date('Y', strtotime($dataAtividade));

        try {
            $conn = \Aplicacao\Configuracao\BancoDeDados::getConexao();
            // Verifica se a tabela existe antes de consultar
            $tabelas = $conn->query("SHOW TABLES LIKE 'medicoes_manuais'")->fetchAll();
            if (empty($tabelas)) return $atividades;

            $stmt = $conn->prepare(
                "SELECT id_item_orcamento FROM medicoes_manuais
                 WHERE id_obra = :id_obra AND mes = :mes AND ano = :ano"
            );
            $stmt->execute([':id_obra' => $idObra, ':mes' => $mes, ':ano' => $ano]);
            $bloqueados = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'id_item_orcamento');

            if (empty($bloqueados)) return $atividades;

            $ids    = $atividades['id_item_orcamento'] ?? [];
            $qtds   = $atividades['quantidade']        ?? [];
            $descs  = $atividades['descricao_manual']  ?? [];

            $filtrados = ['id_item_orcamento' => [], 'quantidade' => [], 'descricao_manual' => []];
            foreach ($ids as $i => $idItem) {
                if (in_array($idItem, $bloqueados)) continue; // ignora item bloqueado
                $filtrados['id_item_orcamento'][] = $idItem;
                $filtrados['quantidade'][]         = $qtds[$i]  ?? 0;
                $filtrados['descricao_manual'][]   = $descs[$i] ?? '';
            }
            return $filtrados;

        } catch (\Exception) {
            return $atividades;
        }
    }
}