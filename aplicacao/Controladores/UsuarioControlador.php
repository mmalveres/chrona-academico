<?php
namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Usuario;
use Aplicacao\Modelos\Obra;
use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Helpers\PlanoHelper;
use Aplicacao\Helpers\CsrfHelper;
use Aplicacao\Configuracao\BancoDeDados;

class UsuarioControlador {

    private function verificarAcessoAdmin(): void {
        Autenticacao::verificarLogin();
        if ($_SESSION['nivel_acesso'] !== 'admin' && !($_SESSION['is_superadmin'] ?? false)) {
            header('Location: index.php?acao=painel');
            exit;
        }
    }

    public function listar() {
        $this->verificarAcessoAdmin();
        $usuarioModelo = new Usuario();
        $pagina        = max(1, (int)($_GET['pagina'] ?? 1));
        $porPagina     = 15;
        $totalUsuarios = $usuarioModelo->contarTotal();
        $totalPaginas  = (int)ceil($totalUsuarios / $porPagina);
        $usuarios      = $usuarioModelo->listarPaginado($pagina, $porPagina);
        require_once __DIR__ . '/../../visoes/usuarios/listar.php';
    }

    public function criar() {
        $this->verificarAcessoAdmin();

        // Verifica limite do plano
        $usuarioModelo = new Usuario();
        $totalUsuarios = count($usuarioModelo->listar());
        if (!PlanoHelper::podeAdicionarUsuario($totalUsuarios)) {
            $max = PlanoHelper::getMaxUsuarios();
            require_once __DIR__ . '/../../visoes/estruturas/cabecalho.php';
            echo PlanoHelper::htmlBloqueio(
                "criação de mais de {$max} usuário(s)",
                'index.php?acao=listar_usuarios'
            );
            require_once __DIR__ . '/../../visoes/estruturas/rodape.php';
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nivel = $_POST['nivel_acesso'] ?? '';

            // Somente SA pode criar usuário fiscal
            if ($nivel === 'fiscal' && !($_SESSION['is_superadmin'] ?? false)) {
                header('Location: index.php?acao=listar_usuarios&msg=sem_permissao');
                exit;
            }

            $dados = [
                'nome'           => trim($_POST['nome']),
                'email'          => trim($_POST['email']),
                'senha'          => $_POST['senha'],
                'nivel_acesso'   => $nivel,
                'id_obra_acesso' => null,
            ];

            try {
                if ($usuarioModelo->cadastrar($dados)) {
                    $conn          = BancoDeDados::getConexao();
                    $idNovoUsuario = (int)$conn->lastInsertId();

                    // Salva campos do fiscal
                    if ($nivel === 'fiscal') {
                        $orgao     = trim($_POST['orgao_fiscalizador'] ?? '');
                        $logoOrgao = null;
                        if (!empty($_FILES['logo_orgao']['tmp_name'])) {
                            $logoOrgao = RelatorioFiscalControlador::salvarLogo($idNovoUsuario, $_FILES['logo_orgao']);
                        }
                        $conn->prepare("UPDATE usuarios SET orgao_fiscalizador=?, logo_orgao=? WHERE id=?")
                             ->execute([$orgao ?: null, $logoOrgao, $idNovoUsuario]);
                    }

                    // Vincula obras para cliente
                    if ($nivel === 'cliente' && !empty($_POST['obras_acesso'])) {
                        $stmt = $conn->prepare(
                            "INSERT IGNORE INTO usuario_obras_acesso (id_usuario, id_obra, id_empresa)
                             SELECT ?, o.id, o.id_empresa FROM obras o WHERE o.id = ?"
                        );
                        foreach ($_POST['obras_acesso'] as $idObra) {
                            $stmt->execute([$idNovoUsuario, (int)$idObra]);
                        }
                    }

                    header('Location: index.php?acao=listar_usuarios&msg=usuario_criado');
                    exit;

                } else {
                    $erro = "Erro ao cadastrar. Tente novamente.";
                }

            } catch (\Exception $e) {

                // E-mail duplicado — vincula obras ao usuário já existente
                if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate entry')) {

                    $conn = BancoDeDados::getConexao();
                    $stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
                    $stmt->execute([trim($_POST['email'])]);
                    $usuarioExistente = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ($usuarioExistente && $nivel === 'cliente' && !empty($_POST['obras_acesso'])) {
                        $stmtVinculo = $conn->prepare(
                            "INSERT IGNORE INTO usuario_obras_acesso (id_usuario, id_obra, id_empresa)
                             SELECT ?, o.id, o.id_empresa FROM obras o WHERE o.id = ?"
                        );
                        foreach ($_POST['obras_acesso'] as $idObra) {
                            $stmtVinculo->execute([$usuarioExistente['id'], (int)$idObra]);
                        }

                        header('Location: index.php?acao=listar_usuarios&msg=obras_vinculadas');
                        exit;
                    }

                    $erro = "Este e-mail já está cadastrado. Para dar acesso a este usuário às suas obras, selecione o perfil <strong>Cliente</strong> e marque as obras desejadas — o sistema vinculará automaticamente.";

                } else {
                    $erro = "Erro ao cadastrar: " . $e->getMessage();
                }
            }
        }

        // Carrega obras para o select do cliente
        $obraModelo = new Obra();
        $obras = $obraModelo->listar();
        require_once __DIR__ . '/../../visoes/usuarios/criar.php';
    }

    public function editar() {
        $this->verificarAcessoAdmin();

        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nivel = $_POST['nivel_acesso'] ?? 'engenheiro';

            // Somente SA pode definir nível fiscal
            if ($nivel === 'fiscal' && !($_SESSION['is_superadmin'] ?? false)) {
                $nivel = 'engenheiro';
            }

            try {
                $conn = BancoDeDados::getConexao();

                if (!empty($_POST['senha'])) {
                    $sql = "UPDATE usuarios SET nome=?, email=?, nivel_acesso=?, ativo=?, senha=? WHERE id=?";
                    $params = [
                        trim($_POST['nome']),
                        trim($_POST['email']),
                        $nivel,
                        isset($_POST['ativo']) ? 1 : 0,
                        password_hash($_POST['senha'], PASSWORD_DEFAULT),
                        $id,
                    ];
                } else {
                    $sql = "UPDATE usuarios SET nome=?, email=?, nivel_acesso=?, ativo=? WHERE id=?";
                    $params = [
                        trim($_POST['nome']),
                        trim($_POST['email']),
                        $nivel,
                        isset($_POST['ativo']) ? 1 : 0,
                        $id,
                    ];
                }

                $conn->prepare($sql)->execute($params);

                // Atualiza campos do fiscal
                if ($nivel === 'fiscal') {
                    $orgao = trim($_POST['orgao_fiscalizador'] ?? '');
                    if (!empty($_FILES['logo_orgao']['tmp_name'])) {
                        $logoOrgao = RelatorioFiscalControlador::salvarLogo((int)$id, $_FILES['logo_orgao']);
                        $conn->prepare("UPDATE usuarios SET orgao_fiscalizador=?, logo_orgao=? WHERE id=?")
                             ->execute([$orgao ?: null, $logoOrgao, (int)$id]);
                    } else {
                        $conn->prepare("UPDATE usuarios SET orgao_fiscalizador=? WHERE id=?")
                             ->execute([$orgao ?: null, (int)$id]);
                    }
                }

                // Atualiza vínculos de obras para cliente
 // COLOQUE:
if ($nivel === 'cliente') {
    $idEmpresaAtual = $_SESSION['id_empresa'];

    // Remove apenas vínculos da empresa atual
    $conn->prepare(
        "DELETE uoa FROM usuario_obras_acesso uoa
         INNER JOIN obras o ON uoa.id_obra = o.id
         WHERE uoa.id_usuario = ? AND o.id_empresa = ?"
    )->execute([$id, $idEmpresaAtual]);

    // Reinsere apenas as obras marcadas (da empresa atual)
    if (!empty($_POST['obras_acesso'])) {
        $stmt = $conn->prepare(
            "INSERT IGNORE INTO usuario_obras_acesso (id_usuario, id_obra, id_empresa)
             SELECT ?, o.id, o.id_empresa FROM obras o
             WHERE o.id = ? AND o.id_empresa = ?"
        );
        foreach ($_POST['obras_acesso'] as $idObra) {
            $stmt->execute([$id, (int)$idObra, $idEmpresaAtual]);
        }
    }
}

                header('Location: index.php?acao=listar_usuarios&msg=usuario_atualizado');
                exit;

            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate entry')) {
                    $erro = "Este e-mail já está em uso por outro usuário.";
                } else {
                    $erro = "Erro ao salvar: " . $e->getMessage();
                }
            }
        }

        $usuarioModelo = new Usuario();
        $usuario       = $usuarioModelo->buscarPorId($id);

        // Carrega obras já vinculadas a este usuário
        $conn = BancoDeDados::getConexao();
    $stmt = $conn->prepare(
    "SELECT uoa.id_obra FROM usuario_obras_acesso uoa
     INNER JOIN obras o ON uoa.id_obra = o.id
     WHERE uoa.id_usuario = ? AND o.id_empresa = ?"
);
$stmt->execute([$id, $_SESSION['id_empresa']]);
$obrasVinculadas = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $obraModelo = new Obra();
        $obras      = $obraModelo->listar();

        require_once __DIR__ . '/../../visoes/usuarios/editar.php';
    }

    public function excluir() {
        $this->verificarAcessoAdmin();
        CsrfHelper::verificarOuAbortar();

        $id = $_GET['id'] ?? 0;

        try {
            $conn = BancoDeDados::getConexao();

            // Verifica se é responsável técnico de alguma obra
            $stmt = $conn->prepare(
                "SELECT id, nome FROM obras WHERE id_responsavel_tecnico = ?"
            );
            $stmt->execute([$id]);
            $obras = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($obras)) {
                $obrasJson = urlencode(json_encode($obras));
                header("Location: index.php?acao=listar_usuarios&modal=substituir&uid=$id&obras=$obrasJson");
                exit;
            }

            // Remove vínculos e exclui
            $conn->prepare("DELETE FROM usuario_obras_acesso WHERE id_usuario = ?")->execute([$id]);
            $conn->prepare("UPDATE diario_obras SET id_usuario = NULL WHERE id_usuario = ?")->execute([$id]);
            $conn->prepare("UPDATE orcamentos SET id_usuario = NULL WHERE id_usuario = ?")->execute([$id]);
            $conn->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);

            header('Location: index.php?acao=listar_usuarios&msg=usuario_excluido');

        } catch (\Exception) {
            header('Location: index.php?acao=listar_usuarios&msg=erro_exclusao');
        }
        exit;
    }

    public function excluirComSubstituicao() {
        $this->verificarAcessoAdmin();
        CsrfHelper::verificarOuAbortar();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?acao=listar_usuarios');
            exit;
        }

        $idExcluir    = (int)($_POST['id_usuario']    ?? 0);
        $idSubstituto = (int)($_POST['id_substituto'] ?? 0);

        if (!$idExcluir || !$idSubstituto || $idExcluir === $idSubstituto) {
            header('Location: index.php?acao=listar_usuarios&msg=erro_exclusao');
            exit;
        }

        try {
            $conn = BancoDeDados::getConexao();

            $conn->prepare(
                "UPDATE obras SET id_responsavel_tecnico = ? WHERE id_responsavel_tecnico = ?"
            )->execute([$idSubstituto, $idExcluir]);

            $conn->prepare("DELETE FROM usuario_obras_acesso WHERE id_usuario = ?")->execute([$idExcluir]);
            $conn->prepare("UPDATE diario_obras SET id_usuario = NULL WHERE id_usuario = ?")->execute([$idExcluir]);
            $conn->prepare("UPDATE orcamentos SET id_usuario = NULL WHERE id_usuario = ?")->execute([$idExcluir]);
            $conn->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$idExcluir]);

            header('Location: index.php?acao=listar_usuarios&msg=usuario_excluido');

        } catch (\Exception) {
            header('Location: index.php?acao=listar_usuarios&msg=erro_exclusao');
        }
        exit;
    }

    public function verificarResponsavel() {
        Autenticacao::verificarLogin();
        header('Content-Type: application/json');

        $id   = (int)($_GET['id'] ?? 0);
        $conn = BancoDeDados::getConexao();

        $stmt = $conn->prepare("SELECT id, nome FROM obras WHERE id_responsavel_tecnico = ?");
        $stmt->execute([$id]);
        $obras = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode(['obras' => $obras]);
        exit;
    }
}