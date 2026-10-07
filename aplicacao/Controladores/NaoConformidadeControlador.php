<?php
// aplicacao/Controladores/NaoConformidadeControlador.php

namespace Aplicacao\Controladores;

use Aplicacao\Modelos\NaoConformidade;
use Aplicacao\Modelos\Obra;
use Aplicacao\Modelos\Usuario;
use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Helpers\PlanoHelper;

class NaoConformidadeControlador {

    private function verificarAcesso(): void {
        Autenticacao::verificarLogin();
        if (!PlanoHelper::podeUsarNC()) {
            require_once __DIR__ . '/../../visoes/estruturas/cabecalho.php';
            echo PlanoHelper::htmlBloqueio('Não Conformidades', 'index.php?acao=listar_obras');
            require_once __DIR__ . '/../../visoes/estruturas/rodape.php';
            exit;
        }
    }

    public function listar() {
        $this->verificarAcesso();

        $id_obra = (int)($_GET['id_obra'] ?? 0);
        if ($id_obra <= 0) { header('Location: index.php?acao=listar_obras'); exit; }

        $obraModel = new Obra();
        $ncModel   = new NaoConformidade();

        $obra = $obraModel->buscarPorId($id_obra);
        if (!$obra) { header('Location: index.php?acao=listar_obras'); exit; }

        $filtros = array_filter([
            'status'     => $_GET['status']     ?? '',
            'prioridade' => $_GET['prioridade'] ?? '',
        ]);

        $ncs    = $ncModel->listarPorObra($id_obra, $filtros);
        $resumo = $ncModel->buscarResumo($id_obra);

        $nivel       = $_SESSION['nivel_acesso'] ?? '';
        $podeCriar   = in_array($nivel, ['admin', 'engenheiro', 'mestre']);
        $podeFechar  = in_array($nivel, ['admin', 'engenheiro']);
        $podeExcluir = $nivel === 'admin';

        $usuarios = (new Usuario())->listar();

        require_once __DIR__ . '/../../visoes/nao_conformidades/listar.php';
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

        // Upload de fotos
        $fotos = [];
        if (!empty($_FILES['fotos']['name'][0])) {
            $dir = __DIR__ . '/../../publico/uploads/nc/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp) {
                if ($_FILES['fotos']['error'][$i] !== 0) continue;
                $ext    = pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION);
                $nome   = 'nc_' . time() . '_' . $i . '.' . $ext;
                if (move_uploaded_file($tmp, $dir . $nome)) {
                    $fotos[] = $nome;
                }
            }
        }

        $dados = [
            'id_obra'        => $id_obra,
            'titulo'         => trim($_POST['titulo']),
            'descricao'      => trim($_POST['descricao'] ?? ''),
            'local'          => trim($_POST['local'] ?? ''),
            'prioridade'     => $_POST['prioridade'] ?? 'media',
            'id_responsavel' => !empty($_POST['id_responsavel']) ? (int)$_POST['id_responsavel'] : null,
            'prazo'          => $_POST['prazo'] ?: null,
            'id_usuario'     => $_SESSION['usuario_id'],
        ];

        $ncModel = new NaoConformidade();
        if ($ncModel->cadastrar($dados, $fotos)) {
            header("Location: index.php?acao=nao_conformidades&id_obra={$id_obra}&msg=sucesso");
        } else {
            header("Location: index.php?acao=nao_conformidades&id_obra={$id_obra}&msg=erro");
        }
        exit;
    }

    public function atualizarStatus() {
        Autenticacao::verificarLogin();

        $nivel = $_SESSION['nivel_acesso'] ?? '';
        if (!in_array($nivel, ['admin', 'engenheiro'])) {
            header('Location: index.php?acao=listar_obras'); exit;
        }

        $id      = (int)($_GET['id']      ?? 0);
        $id_obra = (int)($_GET['id_obra'] ?? 0);
        $status  = $_GET['status'] ?? '';

        $validos = ['aberta', 'em_tratamento', 'fechada'];
        if ($id > 0 && in_array($status, $validos)) {
            (new NaoConformidade())->atualizarStatus($id, $status);
        }

        header("Location: index.php?acao=nao_conformidades&id_obra={$id_obra}&msg=status_atualizado");
        exit;
    }

    public function excluir() {
        Autenticacao::verificarLogin();

        if (($_SESSION['nivel_acesso'] ?? '') !== 'admin') {
            header('Location: index.php?acao=listar_obras'); exit;
        }

        $id      = (int)($_GET['id']      ?? 0);
        $id_obra = (int)($_GET['id_obra'] ?? 0);

        (new NaoConformidade())->excluir($id);
        header("Location: index.php?acao=nao_conformidades&id_obra={$id_obra}&msg=excluido");
        exit;
    }
}
