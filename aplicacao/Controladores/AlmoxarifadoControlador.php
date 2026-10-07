<?php
// aplicacao/Controladores/AlmoxarifadoControlador.php

namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Almoxarifado;
use Aplicacao\Modelos\Obra;
use Aplicacao\Controladores\Autenticacao;

class AlmoxarifadoControlador {

    public function listar() {
        Autenticacao::verificarLogin();

        $id_obra = (int)($_GET['id_obra'] ?? 0);
        if ($id_obra <= 0) {
            header('Location: index.php?acao=listar_obras');
            exit;
        }

        $obraModel  = new Obra();
        $almoModel  = new Almoxarifado();

        $obra = $obraModel->buscarPorId($id_obra);
        if (!$obra) {
            header('Location: index.php?acao=listar_obras');
            exit;
        }

        $filtros = array_filter([
            'tipo'           => $_GET['tipo']           ?? '',
            'material'       => $_GET['material']       ?? '',
            'periodo_inicio' => $_GET['periodo_inicio'] ?? '',
            'periodo_fim'    => $_GET['periodo_fim']    ?? '',
        ]);

        $movimentacoes    = $almoModel->listarMovimentacoes($id_obra, $filtros);
        $saldo            = $almoModel->buscarSaldo($id_obra);
        $resumo           = $almoModel->buscarResumo($id_obra);
        $nomesMateriais   = $almoModel->buscarNomesMateriais($id_obra);

        $itensOrcamento = [];
        if (method_exists($obraModel, 'listarItensOrcamento')) {
            $itensOrcamento = $obraModel->listarItensOrcamento($id_obra);
        }

        require_once __DIR__ . '/../../visoes/almoxarifado/listar.php';
    }

    public function salvar() {
        Autenticacao::verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id_obra = (int)($_POST['id_obra'] ?? 0);
        if ($id_obra <= 0) die('Erro: ID da obra inválido.');

        $dados = [
            'id_obra'           => $id_obra,
            'tipo'              => $_POST['tipo'],
            'descricao'         => trim($_POST['descricao']),
            'unidade'           => trim($_POST['unidade']),
            'quantidade'        => $this->converterNumero($_POST['quantidade']),
            'preco_unitario'    => $this->converterNumero($_POST['preco_unitario'] ?? '0'),
            'id_item_orcamento' => !empty($_POST['id_item_orcamento']) ? (int)$_POST['id_item_orcamento'] : null,
            'fornecedor'        => trim($_POST['fornecedor'] ?? ''),
            'nota_fiscal'       => trim($_POST['nota_fiscal'] ?? ''),
            'data'              => $_POST['data'],
            'observacao'        => trim($_POST['observacao'] ?? ''),
        ];

        $model = new Almoxarifado();
        if ($model->cadastrar($dados)) {
            header("Location: index.php?acao=almoxarifado&id_obra={$id_obra}&msg=sucesso");
        } else {
            header("Location: index.php?acao=almoxarifado&id_obra={$id_obra}&msg=erro");
        }
        exit;
    }

    public function excluir() {
        Autenticacao::verificarLogin();

        $id      = (int)($_GET['id']      ?? 0);
        $id_obra = (int)($_GET['id_obra'] ?? 0);

        if ($id > 0) {
            (new Almoxarifado())->excluir($id);
        }

        header("Location: index.php?acao=almoxarifado&id_obra={$id_obra}&msg=excluido");
        exit;
    }

    private function converterNumero($valor): float {
        $valor = trim((string)$valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        return (float)preg_replace('/[^0-9.]/', '', $valor);
    }
}
