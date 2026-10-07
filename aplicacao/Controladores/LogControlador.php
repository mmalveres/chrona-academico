<?php
namespace Aplicacao\Controladores;

use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Modelos\Log;

class LogControlador {

    public function index() {
        Autenticacao::verificarLogin();

        // Segurança: Só ADMIN pode ver logs
        if ($_SESSION['nivel_acesso'] !== 'admin') {
            die('<div class="alert alert-danger">Acesso Negado. Apenas administradores podem ver logs.</div>');
        }

        $logModel = new Log();
        // Busca os últimos 200 registros
        $logs = $logModel->listarTodos(200);

        require_once __DIR__ . '/../../visoes/admin/logs.php';
    }
}