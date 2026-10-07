<?php
// aplicacao/Controladores/EtapaControlador.php

namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Etapa;
use Aplicacao\Modelos\Obra;
use Aplicacao\Controladores\Autenticacao;

class EtapaControlador {

    public function listar() {
        Autenticacao::verificarLogin();
        $id_obra = $_GET['id_obra'];

        $etapaModel = new Etapa();
        $obraModel = new Obra();

        $obra = $obraModel->buscarPorId($id_obra);
        $etapas = $etapaModel->listarPorObra($id_obra);
        
        // Cálculo do progresso global da obra (Média das etapas)
        $progressoGeral = 0;
        if(count($etapas) > 0) {
            $soma = 0;
            foreach($etapas as $e) $soma += $e['progresso'];
            $progressoGeral = round($soma / count($etapas));
        }

        require_once __DIR__ . '/../../visoes/etapas/listar.php';
    }

    public function criar() {
        Autenticacao::verificarLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'id_obra' => $_POST['id_obra'],
                'nome' => $_POST['nome'],
                'ordem' => $_POST['ordem'],
                'inicio' => $_POST['inicio'],
                'fim' => $_POST['fim'],
                'progresso' => 0,
                'status' => 'pendente'
            ];

            $etapaModel = new Etapa();
            if ($etapaModel->cadastrar($dados)) {
                header('Location: index.php?acao=cronograma&id_obra=' . $_POST['id_obra']);
                exit;
            }
        }
    }

    public function atualizar() {
        Autenticacao::verificarLogin();
        
        $id = $_POST['id_etapa'];
        $id_obra = $_POST['id_obra'];
        
        // Garante que seja um número inteiro entre 0 e 100
        $progresso = intval($_POST['progresso']);
        if ($progresso < 0) $progresso = 0;
        if ($progresso > 100) $progresso = 100;
        
        // Define status automático
        $status = 'em_dia';
        if ($progresso == 0) $status = 'pendente';
        if ($progresso == 100) $status = 'concluida';

        $etapaModel = new Etapa();
        $etapaModel->atualizarProgresso($id, $progresso, $status);

        header('Location: index.php?acao=cronograma&id_obra=' . $id_obra);
        exit;
    }

    public function excluir() {
        Autenticacao::verificarLogin();
        $id = $_GET['id'];
        $id_obra = $_GET['id_obra'];

        $etapaModel = new Etapa();
        $etapaModel->excluir($id);

        header('Location: index.php?acao=cronograma&id_obra=' . $id_obra);
        exit;
    }
}