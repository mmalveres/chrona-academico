<?php
// aplicacao/Controladores/FinanceiroControlador.php

namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Financeiro;
use Aplicacao\Modelos\Obra;
use Aplicacao\Controladores\Autenticacao;

class FinanceiroControlador {

    public function listar() {
        Autenticacao::verificarLogin();

        $id_obra = isset($_GET['id_obra']) ? (int)$_GET['id_obra'] : 0;
        if ($id_obra == 0) { $this->geral(); return; }

        $filtros = array_filter([
            'periodo_inicio' => $_GET['periodo_inicio'] ?? '',
            'periodo_fim'    => $_GET['periodo_fim']    ?? '',
            'tipo'           => $_GET['tipo']           ?? '',
            'status'         => $_GET['status']         ?? '',
        ]);

        $obraModel = new Obra();
        $finModel  = new Financeiro();

        $obra = $obraModel->buscarPorId($id_obra);
        if (!$obra) die("Erro: Obra não encontrada.");

        $movimentacoes     = $finModel->listarPorObra($id_obra, $filtros);
        $resumo            = $finModel->buscarResumo($id_obra, $filtros);
        $dadosMensais      = $finModel->buscarMensal($id_obra);
        $alertasCronograma = $finModel->buscarAlertasCronograma($id_obra);

        $itensOrcamento = [];
        if (method_exists($obraModel, 'listarItensOrcamento')) {
            $itensOrcamento = $obraModel->listarItensOrcamento($id_obra);
        }

        require_once __DIR__ . '/../../visoes/financeiro/listar.php';
    }

    public function geral() {
        $obraModel = new Obra();
        $finModel  = new Financeiro();

        $obras = $obraModel->listar();
        foreach ($obras as &$o) {
            $res          = $finModel->buscarResumo($o['id']);
            $o['saldo']   = $res['saldo'];
            $o['receitas']= $res['receitas'];
            $o['despesas']= $res['despesas'];
        }
        require_once __DIR__ . '/../../visoes/financeiro/geral.php';
    }

    public function criar() {
        Autenticacao::verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id_obra = isset($_POST['id_obra']) ? (int)$_POST['id_obra'] : 0;
        if ($id_obra <= 0) die("Erro: ID da Obra inválido.");

        $nomeArquivo = null;
        if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === 0) {
            $dir = __DIR__ . '/../../publico/uploads/financeiro/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $ext         = pathinfo($_FILES['comprovante']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = 'fin_' . time() . '_' . rand(100, 999) . '.' . $ext;
            move_uploaded_file($_FILES['comprovante']['tmp_name'], $dir . $nomeArquivo);
        }

        $dados = [
            'id_obra'          => $id_obra,
            'tipo'             => $_POST['tipo'],
            'categoria'        => $_POST['categoria'],
            'id_item_orcamento'=> !empty($_POST['id_item_orcamento']) ? $_POST['id_item_orcamento'] : null,
            'descricao'        => $_POST['descricao'],
            'data'             => $_POST['data'],
            'status'           => $_POST['status'],
            'valor'            => $this->formatarMoeda($_POST['valor']),
            'caminho_arquivo'  => $nomeArquivo,
        ];

        $finModel = new Financeiro();
        if ($finModel->cadastrar($dados)) {
            header("Location: index.php?acao=financeiro&id_obra={$id_obra}&msg=sucesso");
        } else {
            header("Location: index.php?acao=financeiro&id_obra={$id_obra}&msg=erro");
        }
        exit;
    }

    public function editar() {
        Autenticacao::verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id      = (int)$_POST['id'];
        $id_obra = (int)$_POST['id_obra'];

        if ($id <= 0 || $id_obra <= 0) die("Erro: dados inválidos.");

        $dados = [
            'tipo'             => $_POST['tipo'],
            'categoria'        => $_POST['categoria'],
            'id_item_orcamento'=> !empty($_POST['id_item_orcamento']) ? $_POST['id_item_orcamento'] : null,
            'descricao'        => $_POST['descricao'],
            'data'             => $_POST['data'],
            'status'           => $_POST['status'],
            'valor'            => $this->formatarMoeda($_POST['valor']),
        ];

        $finModel = new Financeiro();
        if ($finModel->atualizar($id, $dados)) {
            header("Location: index.php?acao=financeiro&id_obra={$id_obra}&msg=editado");
        } else {
            header("Location: index.php?acao=financeiro&id_obra={$id_obra}&msg=erro");
        }
        exit;
    }

    public function excluir() {
        Autenticacao::verificarLogin();

        $id      = (int)$_GET['id'];
        $id_obra = (int)$_GET['id_obra'];

        (new Financeiro())->excluir($id);

        header("Location: index.php?acao=financeiro&id_obra={$id_obra}&msg=excluido");
        exit;
    }

    private function formatarMoeda($valor) {
        if (empty($valor)) return 0;
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        return (float)$valor;
    }
}
