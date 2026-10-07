<?php
// aplicacao/Controladores/Autenticacao.php

namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Usuario;
use Aplicacao\Modelos\Empresa;
use Aplicacao\Helpers\LogHelper;
use Aplicacao\Helpers\EmailHelper;

class Autenticacao {

    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['usuario_id'])) {
            header('Location: index.php?acao=painel');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $senha = $_POST['senha'];

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->autenticar($email, $senha);

            if ($usuario) {
                $empresaModel = new Empresa();
                $empresa = $empresaModel->buscarPorId($usuario['id_empresa']);

                // 1. Verifica se está bloqueada manualmente
                if ($empresa && $empresa['ativo'] == 0 && $empresa['id'] != 1) {
                    $erro = "🚫 <strong>Acesso Suspenso.</strong><br>Sua conta foi desativada pelo administrador.";

                // 2. Verifica se a licença venceu → redireciona para a parede persuasiva
                } elseif (
                    $empresa &&
                    $empresa['id'] != 1 &&
                    !empty($empresa['data_vencimento']) &&
                    strtotime($empresa['data_vencimento']) < strtotime(date('Y-m-d'))
                ) {
                    // Inicia sessão mínima para a tela de expiração poder exibir os dados
                    $_SESSION['usuario_id']      = $usuario['id'];
                    $_SESSION['usuario_nome']    = $usuario['nome'];
                    $_SESSION['nivel_acesso']    = $usuario['nivel_acesso'];
                    $_SESSION['id_empresa']      = $usuario['id_empresa'];
                    $_SESSION['is_superadmin']   = false;
                    $_SESSION['plano']           = $empresa['plano'] ?? 'autonomo';
                    $_SESSION['data_vencimento'] = $empresa['data_vencimento'];
                    header('Location: index.php?acao=licenca_expirada');
                    exit;

                } else {
                    // ── TUDO CERTO — INICIA A SESSÃO ────────────────────────
                    $_SESSION['usuario_id']    = $usuario['id'];
                    $_SESSION['usuario_nome']  = $usuario['nome'];
                    $_SESSION['nivel_acesso']  = $usuario['nivel_acesso'];
                    $_SESSION['id_empresa']    = $usuario['id_empresa'] ?? 1;
                    $_SESSION['is_superadmin'] = ($usuario['id_empresa'] == 1);

                    if ($usuario['id_empresa'] == 1) {
                        $_SESSION['plano']            = 'empresarial';
                        $_SESSION['data_vencimento']  = null;
                    } else {
                        $_SESSION['plano']           = $empresa['plano'] ?? 'autonomo';
                        $_SESSION['data_vencimento'] = $empresa['data_vencimento'] ?? null;
                    }

                    LogHelper::criarTabelaSeNecessario();
                    LogHelper::registrar('Login', $usuario['email']);

                    header('Location: index.php?acao=painel');
                    exit;
                }
            } else {
                $erro = "E-mail ou senha incorretos.";
            }
        }

        require_once __DIR__ . '/../../visoes/autenticacao/login.php';
    }

    // --- ESQUECI SENHA: exibir formulário ---
    public function esqueciSenha() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['usuario_id'])) {
            header('Location: index.php?acao=painel');
            exit;
        }

        require_once __DIR__ . '/../../visoes/autenticacao/esqueci_senha.php';
    }

    // --- ESQUECI SENHA: processar envio do reset ---
    public function enviarReset() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->buscarPorEmail($email);

            // Mensagem padrão de segurança (não revela se o e-mail existe)
            $msg_sucesso = "Se o e-mail estiver cadastrado, você receberá as instruções em instantes.";

            if ($usuario) {
                try {
                    $token    = bin2hex(random_bytes(32));
                    $validade = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    $usuarioModel->salvarTokenRecuperacao($usuario['id'], $token, $validade);

                    $baseUrl  = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                    $link     = $baseUrl . "/index.php?acao=redefinir_senha&token=" . $token;

                    $assunto  = "Redefinicao de senha - CHRONA";
                    $mensagem = "Ola, " . $usuario['nome'] . ",\n\n";
                    $mensagem .= "Recebemos uma solicitacao para redefinir sua senha no CHRONA.\n\n";
                    $mensagem .= "Clique no link abaixo para criar uma nova senha (valido por 1 hora):\n";
                    $mensagem .= $link . "\n\n";
                    $mensagem .= "Caso nao tenha solicitado, ignore este e-mail e sua senha atual permanecera a mesma.\n\n";
                    $mensagem .= "Atenciosamente,\n";
                    $mensagem .= "Equipe CHRONA\n";
                    $mensagem .= "https://engmarcosalveres.com.br";

                    $nomeSeguro = htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8');
                    $linkSeguro = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

                    $corpoHtml = <<<HTML
<p style="margin:0 0 16px;">Ola, <strong>{$nomeSeguro}</strong>,</p>
<p style="margin:0 0 20px;">Recebemos uma solicitacao para redefinir sua senha no CHRONA.</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
<tr><td style="background-color:#0066ff;border-radius:8px;">
<a href="{$linkSeguro}" style="display:inline-block;padding:12px 28px;color:#ffffff;font-weight:bold;font-size:14px;text-decoration:none;">Redefinir minha senha</a>
</td></tr>
</table>
<p style="margin:0 0 16px;color:#6b7280;font-size:13px;">O link e valido por 1 hora.</p>
<p style="margin:0;">Caso nao tenha solicitado, ignore este e-mail e sua senha atual permanecera a mesma.</p>
HTML;

                    EmailHelper::enviarHtml($email, $usuario['nome'], $assunto, $corpoHtml, $mensagem);

                } catch (\Exception $e) {
                    $erro = "Erro interno ao processar solicitação.";
                }
            }
        }

        require_once __DIR__ . '/../../visoes/autenticacao/esqueci_senha.php';
    }

    // --- REDEFINIR SENHA: validar token e exibir formulário ---
    public function redefinirSenha() {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            header('Location: index.php?acao=login');
            exit;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorToken($token);

        if (!$usuario) {
            $erro = "Link inválido ou expirado. Solicite uma nova recuperação.";
            require_once __DIR__ . '/../../visoes/autenticacao/login.php';
            return;
        }

        require_once __DIR__ . '/../../visoes/autenticacao/redefinir_senha.php';
    }

    // --- REDEFINIR SENHA: salvar nova senha via token ---
    public function salvarNovaSenha() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token         = $_POST['token']           ?? '';
            $novaSenha     = $_POST['nova_senha']      ?? '';
            $confirmaSenha = $_POST['confirmar_senha'] ?? '';

            if ($novaSenha !== $confirmaSenha) {
                $erro = "As senhas não conferem.";
                require_once __DIR__ . '/../../visoes/autenticacao/redefinir_senha.php';
                return;
            }

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->buscarPorToken($token);

            if ($usuario) {
                $usuarioModel->atualizarSenhaPorToken($usuario['id'], $novaSenha);
                $sucesso = "Senha alterada com sucesso! Faça login.";
                require_once __DIR__ . '/../../visoes/autenticacao/login.php';
                return;
            } else {
                $erro = "Link expirado. Tente novamente.";
            }
        }

        require_once __DIR__ . '/../../visoes/autenticacao/redefinir_senha.php';
    }

    // --- LOGOUT ---
    public function sair() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        LogHelper::registrar('Logout');
        session_destroy();
        header('Location: index.php?acao=login');
        exit;
    }

    // --- MIDDLEWARE: verifica se está logado e se a licença é válida ---
    public static function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?acao=login');
            exit;
        }

        // SA (empresa 1) nunca é bloqueado por vencimento
        if (!($_SESSION['is_superadmin'] ?? false)) {
            $venc = $_SESSION['data_vencimento'] ?? null;
            if ($venc && strtotime($venc) < strtotime(date('Y-m-d'))) {
                header('Location: index.php?acao=licenca_expirada');
                exit;
            }
        }
    }

    // --- TELA DE LICENÇA EXPIRADA ---
    public function licencaExpirada() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?acao=login');
            exit;
        }

        if ($_SESSION['is_superadmin'] ?? false) {
            header('Location: index.php?acao=painel');
            exit;
        }

        // Re-consulta o banco para pegar a data mais recente
        // (caso o SA tenha renovado a licença desde o último login)
        try {
            $conn = \Aplicacao\Configuracao\BancoDeDados::getConexao();
            $stmt = $conn->prepare("SELECT data_vencimento FROM empresas WHERE id = ?");
            $stmt->execute([$_SESSION['id_empresa']]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $_SESSION['data_vencimento'] = $row['data_vencimento'];
            }
        } catch (\Exception) {}

        $venc = $_SESSION['data_vencimento'] ?? null;
        if (!$venc || strtotime($venc) >= strtotime(date('Y-m-d'))) {
            // Licença foi renovada — libera acesso
            header('Location: index.php?acao=painel');
            exit;
        }

        require_once __DIR__ . '/../../visoes/autenticacao/licenca_expirada.php';
    }

    // --- ALTERAR SENHA (usuário logado): exibir formulário ---
    public function formAlterarSenha() {
        self::verificarLogin();
        require_once __DIR__ . '/../../visoes/autenticacao/alterar_senha.php';
    }

    // --- ALTERAR SENHA (usuário logado): processar ---
    public function salvarNovaSenhaLogado() {
        self::verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senhaAtual    = $_POST['senha_atual']    ?? '';
            $novaSenha     = $_POST['nova_senha']     ?? '';
            $confirmaSenha = $_POST['confirma_senha'] ?? '';
            $idUsuario     = $_SESSION['usuario_id'];

            if ($novaSenha !== $confirmaSenha) {
                $erro = "A nova senha e a confirmação não conferem.";
                require_once __DIR__ . '/../../visoes/autenticacao/alterar_senha.php';
                return;
            }

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->buscarPorId($idUsuario);

            if (!$usuario || !password_verify($senhaAtual, $usuario['senha'])) {
                $erro = "A senha atual informada está incorreta.";
                require_once __DIR__ . '/../../visoes/autenticacao/alterar_senha.php';
                return;
            }

            if ($usuarioModel->atualizarSenhaDireta($idUsuario, $novaSenha)) {
                $sucesso = "Senha alterada com sucesso!";
            } else {
                $erro = "Erro ao atualizar senha no banco de dados.";
            }
        }

        require_once __DIR__ . '/../../visoes/autenticacao/alterar_senha.php';
    }

    // --- MIDDLEWARE: verifica nível de acesso ---
    public static function verificarPermissao($perfisPermitidos) {
        self::verificarLogin();

        if (!in_array($_SESSION['nivel_acesso'], $perfisPermitidos)) {
            if (
                !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
            ) {
                echo json_encode(['status' => 'erro', 'msg' => 'Acesso Negado']);
                exit;
            }

            header('Location: index.php?acao=painel&msg=sem_permissao');
            exit;
        }
    }
}