<?php
namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Empresa;
use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Configuracao\BancoDeDados;
use Aplicacao\Helpers\PrecosHelper;
use Aplicacao\Helpers\LogHelper;
use Aplicacao\Helpers\EmailHelper;

class SuperAdminControlador {

    public function index() {
        $this->verificarSuperAdmin();
        $empresaModel = new Empresa();
        $empresas = $empresaModel->listar();
        // Busca todos os usuarios para a aba
        $conn = BancoDeDados::getConexao();
        $stmt = $conn->query(
            "SELECT u.*, e.nome_fantasia as nome_empresa, e.plano
             FROM usuarios u
             LEFT JOIN empresas e ON u.id_empresa = e.id
             ORDER BY e.nome_fantasia ASC, u.nome ASC"
        );
        $todosUsuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . "/../../visoes/superadmin/listar_empresas.php";
    }

    public function nova() {
        $this->verificarSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dadosEmpresa = [
                'nome_fantasia' => $_POST['nome_fantasia'],
                'razao_social'  => $_POST['razao_social'],
                'cnpj'          => $_POST['cnpj'],
                'plano'         => $_POST['plano'] ?? 'autonomo',
            ];
            $dadosUsuario = [
                'usuario_nome' => $_POST['usuario_nome'],
                'nome'         => $_POST['usuario_nome'],
                'email'        => $_POST['usuario_email'],
                'senha'        => $_POST['usuario_senha'],
            ];
            $empresaModel = new Empresa();
            if ($empresaModel->criarComAdmin($dadosEmpresa, $dadosUsuario)) {
                LogHelper::registrar('Empresa criada', $dadosEmpresa['nome_fantasia']);
                header('Location: index.php?acao=listar_empresas&msg=sucesso');
                exit;
            } else {
                $erro = "Erro ao criar empresa. Verifique se o e-mail já existe.";
            }
        }
        require_once __DIR__ . '/../../visoes/superadmin/nova_empresa.php';
    }

    public function editar() {
        $this->verificarSuperAdmin();
        $id = $_GET['id'] ?? 0;
        $empresaModel = new Empresa();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'id'              => $id,
                'nome_fantasia'   => $_POST['nome_fantasia'],
                'razao_social'    => $_POST['razao_social'],
                'cnpj'            => $_POST['cnpj'],
                'ativo'           => isset($_POST['ativo']) ? 1 : 0,
                'plano'           => $_POST['plano'],
                'data_vencimento' => $_POST['data_vencimento'],
            ];
            $atualizouEmpresa = $empresaModel->atualizar($dados);
            $novoEmail = $_POST['usuario_email'] ?? null;
            $novaSenha = $_POST['usuario_senha'] ?? null;
            if (!empty($novoEmail) || !empty($novaSenha)) {
                $empresaModel->atualizarCredenciaisAdmin($id, $novoEmail, $novaSenha);
            }
            if ($atualizouEmpresa) {
                LogHelper::registrar('Empresa editada', $dados['nome_fantasia']);
                header('Location: index.php?acao=listar_empresas&msg=editado');
                exit;
            }
        }
        $empresa = $empresaModel->buscarPorId($id);
        require_once __DIR__ . '/../../visoes/superadmin/editar_empresa.php';
    }

    public function excluir() {
        $this->verificarSuperAdmin();
        $id = $_GET['id'] ?? 0;
        if ($id == 1) {
            header('Location: index.php?acao=listar_empresas&msg=erro_admin');
            exit;
        }
        $empresaModel = new Empresa();
        if ($empresaModel->excluir($id)) {
            LogHelper::registrar('Empresa excluída', "ID $id");
            header('Location: index.php?acao=listar_empresas&msg=excluido');
        } else {
            header('Location: index.php?acao=listar_empresas&msg=erro_vinculo');
        }
        exit;
    }

    // ════════════════════════════════════════════════════════════
    // ENVIO DE E-MAIL DE TESTE GRÁTIS — convite para empresa testar
    // ════════════════════════════════════════════════════════════

    public function enviarTesteGratis(): void
    {
        $this->verificarSuperAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?acao=listar_empresas');
            exit;
        }

        $idEmpresa = (int)($_POST['id_empresa'] ?? 0);
        $nome      = trim($_POST['nome'] ?? '');
        $email     = trim($_POST['email'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: index.php?acao=listar_empresas&msg=email_invalido');
            exit;
        }

        $enviado = $this->enviarEmailConviteTeste($email, $nome ?: 'Tudo bem');

        if ($enviado) {
            LogHelper::registrar('E-mail de teste grátis enviado', "{$email} (empresa #{$idEmpresa})");
            header('Location: index.php?acao=listar_empresas&msg=email_enviado');
        } else {
            header('Location: index.php?acao=listar_empresas&msg=email_falhou');
        }
        exit;
    }

    private function enviarEmailConviteTeste(string $email, string $nome): bool
    {
        require_once __DIR__ . '/../../config/mercadopago.php';
        $url = defined('APP_URL') ? APP_URL . '/publico/index.php' : '';

        $assunto = "Convite: teste gratuito do CHRONA por 30 dias";

        $corpoTexto  = "Ola, {$nome},\n\n";
        $corpoTexto .= "Voce foi convidado(a) a conhecer o CHRONA, o sistema completo de gestao de obras: diario, cronograma, medicoes e financeiro em um so lugar.\n\n";
        $corpoTexto .= "Seu acesso gratuito de 30 dias ja esta liberado. Para comecar, acesse:\n";
        $corpoTexto .= "{$url}\n\n";
        $corpoTexto .= "Use o e-mail {$email} para entrar. Caso ainda nao tenha senha cadastrada, clique em \"Esqueci minha senha\" na tela de login.\n\n";
        $corpoTexto .= "Qualquer duvida, e so responder este e-mail que teremos prazer em ajudar.\n\n";
        $corpoTexto .= "Atenciosamente,\n";
        $corpoTexto .= "Equipe CHRONA\n";
        $corpoTexto .= "https://engmarcosalveres.com.br";

        $nomeSeguro  = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $urlSegura   = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $emailSeguro = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

        $corpoHtml = <<<HTML
<p style="margin:0 0 16px;">Ola, <strong>{$nomeSeguro}</strong>,</p>
<p style="margin:0 0 16px;">Voce foi convidado(a) a conhecer o <strong>CHRONA</strong>, o sistema completo de gestao de obras: diario, cronograma, medicoes e financeiro em um so lugar.</p>
<p style="margin:0 0 24px;">Seu acesso gratuito de <strong>30 dias</strong> ja esta liberado.</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
<tr><td style="background-color:#0066ff;border-radius:8px;">
<a href="{$urlSegura}" style="display:inline-block;padding:12px 28px;color:#ffffff;font-weight:bold;font-size:14px;text-decoration:none;">Acessar o CHRONA</a>
</td></tr>
</table>
<p style="margin:0 0 16px;">Use o e-mail <strong>{$emailSeguro}</strong> para entrar. Caso ainda nao tenha senha cadastrada, clique em "Esqueci minha senha" na tela de login.</p>
<p style="margin:0;">Qualquer duvida, e so responder este e-mail que teremos prazer em ajudar.</p>
HTML;

        return EmailHelper::enviarHtml($email, $nome, $assunto, $corpoHtml, $corpoTexto);
    }

    // ════════════════════════════════════════════════════════════
    // GESTÃO GLOBAL DE USUÁRIOS — todos os clientes/empresas
    // ════════════════════════════════════════════════════════════

   
    public function editarUsuario() {
        $this->verificarSuperAdmin();
        $id   = (int)($_GET['id'] ?? 0);
        $conn = BancoDeDados::getConexao();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nivel = $_POST['nivel_acesso'] ?? 'engenheiro';
                if (!empty($_POST['senha'])) {
                    $sql = "UPDATE usuarios SET nome=?,email=?,nivel_acesso=?,ativo=?,id_empresa=?,senha=? WHERE id=?";
                    $params = [
                        trim($_POST['nome']), trim($_POST['email']), $nivel,
                        isset($_POST['ativo']) ? 1 : 0,
                        (int)$_POST['id_empresa'],
                        password_hash($_POST['senha'], PASSWORD_DEFAULT), $id,
                    ];
                } else {
                    $sql = "UPDATE usuarios SET nome=?,email=?,nivel_acesso=?,ativo=?,id_empresa=? WHERE id=?";
                    $params = [
                        trim($_POST['nome']), trim($_POST['email']), $nivel,
                        isset($_POST['ativo']) ? 1 : 0,
                        (int)$_POST['id_empresa'], $id,
                    ];
                }
                $conn->prepare($sql)->execute($params);
                LogHelper::registrar('Usuário editado', trim($_POST['nome']) . ' (' . trim($_POST['email']) . ')');
                header('Location: index.php?acao=listar_empresas&aba=usuarios&msg=usuario_atualizado');
                exit;
            } catch (\Exception $e) {
                $erro = "Erro ao salvar: " . $e->getMessage();
            }
        }

        $stmt = $conn->prepare(
            "SELECT u.*, e.nome_fantasia as nome_empresa
             FROM usuarios u LEFT JOIN empresas e ON u.id_empresa = e.id
             WHERE u.id = ?"
        );
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmtEmp = $conn->query("SELECT id, nome_fantasia FROM empresas ORDER BY nome_fantasia");
        $empresas = $stmtEmp->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../visoes/superadmin/editar_usuario.php';
    }

    public function excluirUsuario() {
    $this->verificarSuperAdmin();
    $id   = (int)($_GET['id'] ?? 0);
    $conn = BancoDeDados::getConexao();

    if ($id == $_SESSION['usuario_id']) {
        header('Location: index.php?acao=listar_empresas&aba=usuarios&msg=erro_proprio');
        exit;
    }

    try {
        $conn->prepare(
            "UPDATE obras SET id_responsavel_tecnico = NULL 
             WHERE id_responsavel_tecnico = ?"
        )->execute([$id]);

        $conn->prepare(
            "UPDATE diario_obras SET id_usuario = NULL 
             WHERE id_usuario = ?"
        )->execute([$id]);

        $conn->prepare(
            "UPDATE orcamentos SET id_usuario = NULL 
             WHERE id_usuario = ?"
        )->execute([$id]);

        $conn->prepare(
            "DELETE FROM usuarios WHERE id = ?"
        )->execute([$id]);

        LogHelper::registrar('Usuário excluído', "ID $id");
        header('Location: index.php?acao=listar_empresas&aba=usuarios&msg=usuario_excluido');

    } catch (\Exception $e) {
        header('Location: index.php?acao=listar_empresas&aba=usuarios&msg=erro');
        
    }
    exit;
}

    public function precosPlanos(): void
    {
        $this->verificarSuperAdmin();
        PrecosHelper::criarTabelaSeNecessario();
        $precos     = PrecosHelper::getPrecos();
        $descontoPct= reset($precos)['desc_pct'] ?? 20;
        $sucesso    = ($_GET['msg'] ?? '') === 'salvo' ? 'Preços atualizados com sucesso!' : '';
        $erro       = '';
        require_once __DIR__ . '/../../visoes/superadmin/precos_planos.php';
    }

    public function salvarPrecos(): void
    {
        $this->verificarSuperAdmin();
        try {
            PrecosHelper::criarTabelaSeNecessario();
            $descPct = max(0, min(80, (int)($_POST['desconto_global'] ?? 20)));
            foreach (['autonomo', 'construtora', 'empresarial'] as $plano) {
                $mensal = (float)($_POST["mensal_{$plano}"] ?? 0);
                if ($mensal <= 0) throw new \Exception("Valor inválido para {$plano}.");
                PrecosHelper::salvar($plano, $mensal, $descPct);
            }
            header('Location: index.php?acao=precos_planos&msg=salvo');
        } catch (\Exception $e) {
            $precos     = PrecosHelper::getPrecos();
            $descontoPct= reset($precos)['desc_pct'] ?? 20;
            $sucesso    = '';
            $erro       = $e->getMessage();
            require_once __DIR__ . '/../../visoes/superadmin/precos_planos.php';
        }
        exit;
    }

    // ════════════════════════════════════════════════════════════
    // SELETOR DE EMPRESA — SuperAdmin visualiza como empresa X
    // ════════════════════════════════════════════════════════════

    public function selecionarEmpresa(): void
    {
        $this->verificarSuperAdmin();
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 2) {
            header('Location: index.php?acao=listar_empresas');
            exit;
        }
        $conn = BancoDeDados::getConexao();
        $stmt = $conn->prepare("SELECT nome_fantasia FROM empresas WHERE id = ?");
        $stmt->execute([$id]);
        $empresa = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$empresa) {
            header('Location: index.php?acao=listar_empresas');
            exit;
        }
        $_SESSION['id_empresa']      = $id;
        $_SESSION['sa_empresa_nome'] = $empresa['nome_fantasia'];
        $_SESSION['plano']           = 'empresarial';
        LogHelper::registrar('SA: visualizando empresa', $empresa['nome_fantasia']);
        header('Location: index.php?acao=painel');
        exit;
    }

    public function sairEmpresa(): void
    {
        if (!($_SESSION['is_superadmin'] ?? false)) {
            header('Location: index.php?acao=painel');
            exit;
        }
        LogHelper::registrar('SA: saiu da visualização', $_SESSION['sa_empresa_nome'] ?? '');
        $_SESSION['id_empresa'] = 1;
        $_SESSION['plano']      = 'empresarial';
        unset($_SESSION['sa_empresa_nome']);
        header('Location: index.php?acao=listar_empresas');
        exit;
    }

    // ════════════════════════════════════════════════════════════
    // LOGS DO SISTEMA
    // ════════════════════════════════════════════════════════════

    public function logsSistema(): void
    {
        $this->verificarSuperAdmin();
        LogHelper::criarTabelaSeNecessario();

        $conn          = BancoDeDados::getConexao();
        $filtroEmpresa = (int)($_GET['empresa'] ?? 0);
        $filtroBusca   = trim($_GET['busca'] ?? '');
        $pagina        = max(1, (int)($_GET['pagina'] ?? 1));
        $porPagina     = 50;
        $offset        = ($pagina - 1) * $porPagina;

        $where  = [];
        $params = [];
        if ($filtroEmpresa) {
            $where[]  = 'id_empresa = ?';
            $params[] = $filtroEmpresa;
        }
        if ($filtroBusca !== '') {
            $where[]  = '(acao LIKE ? OR nome_usuario LIKE ? OR detalhes LIKE ?)';
            $params[] = "%$filtroBusca%";
            $params[] = "%$filtroBusca%";
            $params[] = "%$filtroBusca%";
        }
        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $totalStmt = $conn->prepare("SELECT COUNT(*) FROM logs_sistema $whereStr");
        $totalStmt->execute($params);
        $totalRegistros = (int)$totalStmt->fetchColumn();
        $totalPaginas   = max(1, (int)ceil($totalRegistros / $porPagina));

        $stmt = $conn->prepare("SELECT * FROM logs_sistema $whereStr ORDER BY created_at DESC LIMIT $porPagina OFFSET $offset");
        $stmt->execute($params);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $empresas = $conn->query("SELECT id, nome_fantasia FROM empresas ORDER BY nome_fantasia")->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../visoes/superadmin/logs_sistema.php';
    }

    private function verificarSuperAdmin(): void
    {
        Autenticacao::verificarLogin();
        if (!($_SESSION['is_superadmin'] ?? false)) {
            die("ACESSO NEGADO: Apenas o Super Administrador pode acessar esta área.");
        }
    }
}