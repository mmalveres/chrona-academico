<?php
// aplicacao/Controladores/DocumentoControlador.php

namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Documento;
use Aplicacao\Modelos\Obra;
use Aplicacao\Controladores\Autenticacao;

class DocumentoControlador {

    public function listar() {
        Autenticacao::verificarLogin();
        $id_obra = $_GET['id_obra'];

        $docModel = new Documento();
        $obraModel = new Obra();

        $obra = $obraModel->buscarPorId($id_obra);
        $documentos = $docModel->listarPorObra($id_obra);

        require_once __DIR__ . '/../../visoes/documentos/listar.php';
    }

    public function criar() {
        Autenticacao::verificarLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_obra = $_POST['id_obra'];
            
            // Processamento do Upload
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                
                // Cria pasta específica para documentos se não existir
                $diretorio = __DIR__ . '/../../publico/uploads/documentos/';
                if (!is_dir($diretorio)) {
                    mkdir($diretorio, 0755, true);
                }

                $nomeOriginal = $_FILES['arquivo']['name'];
                $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                // Nome único: doc_IDOBRA_TIMESTAMP.ext
                $novoNome = "doc_" . $id_obra . "_" . time() . "." . $extensao;

                if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $diretorio . $novoNome)) {
                    
                    $dados = [
                        'id_obra' => $id_obra,
                        'titulo' => $_POST['titulo'],
                        'tipo' => $_POST['tipo'],
                        'caminho' => $novoNome,
                        'validade' => !empty($_POST['validade']) ? $_POST['validade'] : null,
                        'descricao' => $_POST['descricao']
                    ];

                    $docModel = new Documento();
                    if ($docModel->cadastrar($dados)) {
                        header('Location: index.php?acao=listar_documentos&id_obra=' . $id_obra . '&msg=sucesso');
                        exit;
                    }
                }
            }
            // Se falhar
            header('Location: index.php?acao=listar_documentos&id_obra=' . $id_obra . '&msg=erro_upload');
        }
    }

    public function excluir() {
        Autenticacao::verificarLogin();
        $id = $_GET['id'];
        $id_obra = $_GET['id_obra'];

        $docModel = new Documento();
        $docModel->excluir($id);

        header('Location: index.php?acao=listar_documentos&id_obra=' . $id_obra . '&msg=excluido');
        exit;
    }
}