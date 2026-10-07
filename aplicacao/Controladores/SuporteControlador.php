<?php
namespace Aplicacao\Controladores;

use Aplicacao\Controladores\Autenticacao;

class SuporteControlador {

    public function manual() {
        Autenticacao::verificarLogin();
        require_once __DIR__ . '/../../visoes/suporte/manual.php';
    }
}