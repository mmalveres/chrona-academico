<?php
namespace Aplicacao\Controladores;

use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Configuracao\BancoDeDados; // Necessário para salvar os dados
use PDO;

class ConfigControlador {

    // Função auxiliar para mostrar tela de erro formatada
    private function exibirErroPermissao() {
        // Carrega o cabeçalho padrão (Menu + Topo)
        include __DIR__ . '/../../visoes/estruturas/cabecalho.php';
        
        // Exibe o conteúdo de erro bonito
        echo '
        <div class="container-fluid mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 20px;">
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="bi bi-shield-lock-fill" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-dark mb-2">Acesso Restrito</h3>
                            <p class="text-muted mb-4 px-4">
                                Seu perfil de usuário não possui permissão para acessar as configurações do sistema. 
                                <br>Entre em contato com o administrador.
                            </p>
                            <a href="index.php?acao=painel" class="btn btn-primary px-4 fw-bold rounded-pill">
                                <i class="bi bi-arrow-left me-2"></i> Voltar ao Início
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        // Carrega o rodapé para fechar o HTML corretamente
        include __DIR__ . '/../../visoes/estruturas/rodape.php';
        exit; // Para a execução aqui
    }

public function index() {
        Autenticacao::verificarLogin();
        
        if ($_SESSION['nivel_acesso'] !== 'admin') {
            $this->exibirErroPermissao();
        }
        
        $idEmpresa = $_SESSION['id_empresa'] ?? 0;
        
        // --- CORREÇÃO AQUI: Usando nome_fantasia ---
        $conn = BancoDeDados::getConexao();
        $stmt = $conn->prepare("SELECT nome_fantasia, cnpj FROM empresas WHERE id = :id");
        $stmt->bindValue(':id', $idEmpresa);
        $stmt->execute();
        $dadosEmpresa = $stmt->fetch(PDO::FETCH_ASSOC);

        $catalogo = new \Aplicacao\Modelos\Catalogo();
        
        require_once __DIR__ . '/../../visoes/config/index.php';
    }

    // --- NOVO MÉTODO: Salvar Nome e CNPJ ---
 public function salvarDadosEmpresa() {
        Autenticacao::verificarLogin();
        if ($_SESSION['nivel_acesso'] !== 'admin') $this->exibirErroPermissao();

        $idEmpresa = $_SESSION['id_empresa'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome_empresa'] ?? '';
            $cnpj = $_POST['cnpj_empresa'] ?? '';

            // Limpa o CNPJ
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

            try {
                $conn = BancoDeDados::getConexao();
                // AQUI ESTAVA O ERRO: Mudamos 'nome' para 'nome_fantasia'
                $stmt = $conn->prepare("UPDATE empresas SET nome_fantasia = :nome, cnpj = :cnpj WHERE id = :id");
                $stmt->bindValue(':nome', $nome);
                $stmt->bindValue(':cnpj', $cnpjLimpo);
                $stmt->bindValue(':id', $idEmpresa);
                $stmt->execute();

                // Atualiza a sessão
                $_SESSION['empresa_nome'] = $nome;

                header('Location: index.php?acao=configuracoes&msg=dados_salvos');
                exit;
            } catch (\Exception $e) {
                // Se der erro, mostra na tela para debug (remova em produção)
                die("Erro SQL: " . $e->getMessage());
            }
        }
    }

    public function salvarLogo() {
        Autenticacao::verificarLogin();
        if ($_SESSION['nivel_acesso'] !== 'admin') $this->exibirErroPermissao();

        $idEmpresa = $_SESSION['id_empresa'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                
                $diretorio = __DIR__ . '/../../publico/assets/img/';
                if (!is_dir($diretorio)) mkdir($diretorio, 0755, true);

                $nomeArquivo = "logo_empresa_{$idEmpresa}.png";
                $caminhoFinal = $diretorio . $nomeArquivo;
                
                $extensao = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                if (!in_array($extensao, ['png', 'jpg', 'jpeg'])) {
                    header('Location: index.php?acao=configuracoes&msg=erro_extensao');
                    exit;
                }

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $caminhoFinal)) {
                    header('Location: index.php?acao=configuracoes&msg=sucesso_logo');
                    exit;
                }
            }
            header('Location: index.php?acao=configuracoes&msg=erro_upload');
        }
    }

    public function removerLogo() {
        Autenticacao::verificarLogin();
        if ($_SESSION['nivel_acesso'] !== 'admin') $this->exibirErroPermissao();

        $idEmpresa = $_SESSION['id_empresa'] ?? 0;
        $arquivo = __DIR__ . '/../../publico/assets/img/logo_empresa_' . $idEmpresa . '.png';
        
        if (file_exists($arquivo)) {
            unlink($arquivo);
        }
        header('Location: index.php?acao=configuracoes&msg=logo_removida');
    }

    public function gerenciarCatalogo() {
        Autenticacao::verificarLogin();
        if ($_SESSION['nivel_acesso'] !== 'admin') $this->exibirErroPermissao();

        $tipo = $_POST['tipo']; 
        $acao = $_POST['acao_catalogo']; 
        $nome = $_POST['nome_item'] ?? '';
        $id = $_POST['id_item'] ?? 0;

        $catalogo = new \Aplicacao\Modelos\Catalogo();

        if ($tipo === 'mao_obra') {
            if ($acao === 'adicionar' && !empty($nome)) {
                $catalogo->adicionarMaoObra($nome);
            } elseif ($acao === 'remover') {
                $catalogo->removerMaoObra($id);
            }
        } elseif ($tipo === 'equipamento') {
            if ($acao === 'adicionar' && !empty($nome)) {
                $catalogo->adicionarEquipamento($nome);
            } elseif ($acao === 'remover') {
                $catalogo->removerEquipamento($id);
            }
        }

        header('Location: index.php?acao=configuracoes');
        exit;
    }
}