<?php
namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Cronograma;
use Aplicacao\Modelos\Obra;
use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Helpers\PlanoHelper;

class CronogramaControlador {

    private function verificarPlano(): void
    {
        if (!PlanoHelper::podeUsarCronograma()) {
            require_once __DIR__ . '/../../estruturas/cabecalho.php';
            echo PlanoHelper::htmlBloqueio(
                'Cronograma Físico-Financeiro',
                'index.php?acao=listar_obras'
            );
            require_once __DIR__ . '/../../estruturas/rodape.php';
            exit;
        }
    }

    public function ver() {
        Autenticacao::verificarLogin();
        $this->verificarPlano();
        $id_obra = $_GET['id_obra'] ?? 0;

        $obraModel = new Obra();
        $obra = $obraModel->buscarPorId($id_obra);
        
        if (!$obra) {
            header("Location: index.php?acao=listar_obras");
            exit;
        }

        $cronogramaModel = new Cronograma();
        $itensCronograma = $cronogramaModel->listarCronogramaDaObra($id_obra);

        require_once __DIR__ . '/../../visoes/cronograma/ver.php';
    }
// Adicione este método dentro da classe CronogramaControlador

    public function imprimir() {
        Autenticacao::verificarLogin();
        $this->verificarPlano();
        $id_obra = $_GET['id_obra'] ?? 0;

        $obraModel = new Obra();
        $obra = $obraModel->buscarPorId($id_obra);
        
        if (!$obra) die("Obra não encontrada.");

        $cronogramaModel = new Cronograma();
        $itensCronograma = $cronogramaModel->listarCronogramaDaObra($id_obra);

        // Carrega a visão específica de impressão (sem cabeçalho/menu do site)
        require_once __DIR__ . '/../../visoes/cronograma/imprimir.php';
    }
    public function salvar() {
        Autenticacao::verificarLogin();
        $this->verificarPlano();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_obra = $_POST['id_obra'];
            
            // Verifica se o array cronograma chegou
            $dadosPlanejamento = $_POST['cronograma'] ?? [];

            if (empty($dadosPlanejamento)) {
                // Se estiver vazio, volta com aviso (talvez formulário não enviado corretamente)
                header("Location: index.php?acao=cronograma&id_obra=$id_obra&msg=vazio");
                exit;
            }

            $cronogramaModel = new Cronograma();
            
            if ($cronogramaModel->salvarPlanejamento($id_obra, $dadosPlanejamento)) {
                header("Location: index.php?acao=cronograma&id_obra=$id_obra&msg=sucesso");
            } else {
                header("Location: index.php?acao=cronograma&id_obra=$id_obra&msg=erro");
            }
            exit;
        }
    }
}