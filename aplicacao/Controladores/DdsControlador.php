<?php
// aplicacao/Controladores/DdsControlador.php

namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Dds;
use Aplicacao\Modelos\Obra;
use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Helpers\PlanoHelper;

class DdsControlador {

    private function verificarAcesso(): void {
        Autenticacao::verificarLogin();
        if (!PlanoHelper::podeUsarDDS()) {
            require_once __DIR__ . '/../../visoes/estruturas/cabecalho.php';
            echo PlanoHelper::htmlBloqueio('DDS — Diálogo Diário de Segurança', 'index.php?acao=listar_obras');
            require_once __DIR__ . '/../../visoes/estruturas/rodape.php';
            exit;
        }
    }

    public function listar() {
        $this->verificarAcesso();

        $id_obra = (int)($_GET['id_obra'] ?? 0);
        if ($id_obra <= 0) { header('Location: index.php?acao=listar_obras'); exit; }

        $obraModel = new Obra();
        $ddsModel  = new Dds();

        $obra    = $obraModel->buscarPorId($id_obra);
        if (!$obra) { header('Location: index.php?acao=listar_obras'); exit; }

        $registros = $ddsModel->listarPorObra($id_obra);
        $resumo    = $ddsModel->buscarResumo($id_obra);

        $nivel      = $_SESSION['nivel_acesso'] ?? '';
        $podeCriar  = in_array($nivel, ['admin', 'engenheiro', 'mestre']);
        $podeExcluir= in_array($nivel, ['admin', 'engenheiro']);

        require_once __DIR__ . '/../../visoes/dds/listar.php';
    }

    public function ver() {
        $this->verificarAcesso();

        $id      = (int)($_GET['id']      ?? 0);
        $id_obra = (int)($_GET['id_obra'] ?? 0);

        $ddsModel = new Dds();
        $registro = $ddsModel->buscarComPresenca($id);
        if (!$registro) { header('Location: index.php?acao=dds&id_obra=' . $id_obra); exit; }

        $obra = (new Obra())->buscarPorId($id_obra);

        require_once __DIR__ . '/../../visoes/dds/ver.php';
    }

    public function salvar() {
        Autenticacao::verificarLogin();

        $nivel = $_SESSION['nivel_acesso'] ?? '';
        if (!in_array($nivel, ['admin', 'engenheiro', 'mestre'])) {
            header('Location: index.php?acao=listar_obras'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id_obra = (int)($_POST['id_obra'] ?? 0);
        if ($id_obra <= 0) die('Erro: obra inválida.');

        $dados = [
            'id_obra'    => $id_obra,
            'data'       => $_POST['data'],
            'tema'       => trim($_POST['tema']),
            'conteudo'   => trim($_POST['conteudo'] ?? ''),
            'id_usuario' => $_SESSION['usuario_id'],
        ];

        $listaPresenca = $_POST['presenca'] ?? [];

        $ddsModel = new Dds();
        if ($ddsModel->cadastrar($dados, $listaPresenca)) {
            header("Location: index.php?acao=dds&id_obra={$id_obra}&msg=sucesso");
        } else {
            header("Location: index.php?acao=dds&id_obra={$id_obra}&msg=erro");
        }
        exit;
    }

    public function excluir() {
        Autenticacao::verificarLogin();

        $nivel = $_SESSION['nivel_acesso'] ?? '';
        if (!in_array($nivel, ['admin', 'engenheiro'])) {
            header('Location: index.php?acao=listar_obras'); exit;
        }

        $id      = (int)($_GET['id']      ?? 0);
        $id_obra = (int)($_GET['id_obra'] ?? 0);

        (new Dds())->excluir($id);
        header("Location: index.php?acao=dds&id_obra={$id_obra}&msg=excluido");
        exit;
    }
}
