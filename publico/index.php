<?php
// publico/index.php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(7200);
    session_start();
}

require_once __DIR__ . '/../autoload.php';

use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Controladores\ObraControlador;
use Aplicacao\Controladores\DiarioControlador;
use Aplicacao\Controladores\PainelControlador;
use Aplicacao\Controladores\FinanceiroControlador;
use Aplicacao\Controladores\UsuarioControlador;
use Aplicacao\Controladores\DocumentoControlador;
use Aplicacao\Controladores\RelatorioControlador;
use Aplicacao\Controladores\CronogramaControlador;
use Aplicacao\Controladores\EtapaControlador;
use Aplicacao\Controladores\ConfigControlador;
use Aplicacao\Controladores\LogControlador;
use Aplicacao\Controladores\SuperAdminControlador;
use Aplicacao\Controladores\AlmoxarifadoControlador;
use Aplicacao\Controladores\DdsControlador;
use Aplicacao\Controladores\NaoConformidadeControlador;
use Aplicacao\Controladores\RelatorioFiscalControlador;

$acao = $_GET['acao'] ?? 'login';
$auth = new Autenticacao();

$rotas = [
    // Autenticação
    'login'                    => fn() => $auth->login(),
    'fazer_login'              => fn() => $auth->login(),
    'sair'                     => fn() => $auth->sair(),
    'licenca_expirada'         => fn() => $auth->licencaExpirada(),
    'esqueci_senha'            => fn() => $auth->esqueciSenha(),
    'enviar_reset'             => fn() => $auth->enviarReset(),
    'redefinir_senha'          => fn() => $auth->redefinirSenha(),
    'salvar_nova_senha'        => fn() => $auth->salvarNovaSenha(),
    'alterar_senha'            => fn() => $auth->formAlterarSenha(),
    'salvar_nova_senha_logado' => fn() => $auth->salvarNovaSenhaLogado(),

    // Dashboard
    'painel'         => fn() => (new PainelControlador())->index(),
    'painel_cliente' => fn() => (new PainelControlador())->painelCliente(),

    // Obras
    'listar_obras'      => fn() => (new ObraControlador())->listar(),
    'nova_obra'         => fn() => (new ObraControlador())->criar(),
    'editar_obra'       => fn() => (new ObraControlador())->editar(),
    'excluir_obra'      => fn() => (new ObraControlador())->excluir(),
    'importar_orcamento'=> fn() => (new ObraControlador())->importarOrcamento(),
    'curva_s'           => fn() => (new ObraControlador())->curvaS(),
    'almoxarifado'         => fn() => (new AlmoxarifadoControlador())->listar(),
    'salvar_almoxarifado'  => fn() => (new AlmoxarifadoControlador())->salvar(),
    'excluir_almoxarifado' => fn() => (new AlmoxarifadoControlador())->excluir(),

    // DDS
    'dds'                  => fn() => (new DdsControlador())->listar(),
    'ver_dds'              => fn() => (new DdsControlador())->ver(),
    'salvar_dds'           => fn() => (new DdsControlador())->salvar(),
    'excluir_dds'          => fn() => (new DdsControlador())->excluir(),

    // Não Conformidades
    'nao_conformidades'    => fn() => (new NaoConformidadeControlador())->listar(),
    'salvar_nc'            => fn() => (new NaoConformidadeControlador())->salvar(),
    'status_nc'            => fn() => (new NaoConformidadeControlador())->atualizarStatus(),
    'excluir_nc'           => fn() => (new NaoConformidadeControlador())->excluir(),
    'medicao_obra'          => fn() => (new ObraControlador())->medicao(),
    'imprimir_medicao'      => fn() => (new ObraControlador())->imprimirMedicao(),
    'salvar_medicao_manual' => fn() => (new ObraControlador())->salvarMedicaoManual(),

    // Cronograma
    'cronograma'        => fn() => (new CronogramaControlador())->ver(),
    'ver_cronograma'    => fn() => (new CronogramaControlador())->ver(),
    'salvar_cronograma' => fn() => (new CronogramaControlador())->salvar(),
    'imprimir_cronograma'=> fn() => (new CronogramaControlador())->imprimir(),

    // Diários
    'ver_diario'          => fn() => (new DiarioControlador())->listar(),
    'preencher_diario'    => fn() => (new DiarioControlador())->novo(),
    'novo_diario'         => fn() => (new DiarioControlador())->salvar_novo(),
    'editar_diario'       => fn() => (new DiarioControlador())->editar(),
    'atualizar_diario'    => fn() => (new DiarioControlador())->atualizar(),
    'excluir_diario'      => fn() => (new DiarioControlador())->excluir(),
    'excluir_foto'        => fn() => (new DiarioControlador())->excluir_foto(),
    'assinar_diario'      => fn() => (new DiarioControlador())->assinar(),
    'assinar'             => fn() => (new DiarioControlador())->assinar(),
    'salvar_assinatura'   => fn() => (new DiarioControlador())->salvar_assinatura(),
    'assinar_lote'        => fn() => (new DiarioControlador())->assinarLote(),
    'salvar_assinatura_lote'=> fn() => (new DiarioControlador())->salvarAssinaturaLote(),
    'reabrir_diario'      => fn() => (new DiarioControlador())->reabrir(),
    'imprimir_diario'     => fn() => (new DiarioControlador())->imprimir(),
    'imprimir_diarios_lote'=> fn() => (new DiarioControlador())->imprimirLote(),

    // Financeiro
    'financeiro'        => fn() => (new FinanceiroControlador())->listar(),
    'salvar_financeiro' => fn() => (new FinanceiroControlador())->criar(),
    'editar_financeiro' => fn() => (new FinanceiroControlador())->editar(),
    'excluir_financeiro'=> fn() => (new FinanceiroControlador())->excluir(),

    // Usuários
    'listar_usuarios'          => fn() => (new UsuarioControlador())->listar(),
    'novo_usuario'             => fn() => (new UsuarioControlador())->criar(),
    'editar_usuario'           => fn() => (new UsuarioControlador())->editar(),
    'excluir_usuario'          => fn() => (new UsuarioControlador())->excluir(),
    'verificar_responsavel'    => fn() => (new UsuarioControlador())->verificarResponsavel(),
    'excluir_usuario_confirmar'=> fn() => (new UsuarioControlador())->excluirComSubstituicao(),

    // Documentos
    'listar_documentos' => fn() => (new DocumentoControlador())->listar(),
    'salvar_documento'  => fn() => (new DocumentoControlador())->criar(),
    'excluir_documento' => fn() => (new DocumentoControlador())->excluir(),

    // Relatório
    'filtro_relatorio' => fn() => (new RelatorioControlador())->filtro(),
    'gerar_relatorio'  => fn() => (new RelatorioControlador())->gerar(),

    // Etapas (módulo legado)
    'salvar_etapa'   => fn() => (new EtapaControlador())->criar(),
    'atualizar_etapa'=> fn() => (new EtapaControlador())->atualizar(),
    'excluir_etapa'  => fn() => (new EtapaControlador())->excluir(),

    // Configurações
    'configuracoes'       => fn() => (new ConfigControlador())->index(),
    'salvar_logo'         => fn() => (new ConfigControlador())->salvarLogo(),
    'remover_logo'        => fn() => (new ConfigControlador())->removerLogo(),
    'gerenciar_catalogo'  => fn() => (new ConfigControlador())->gerenciarCatalogo(),
    'salvar_dados_empresa'=> fn() => (new ConfigControlador())->salvarDadosEmpresa(),

    // Logs
    'logs' => fn() => (new LogControlador())->index(),

    // Relatório Fiscal
    'relatorio_fiscal'          => fn() => (new RelatorioFiscalControlador())->listar(),
    'relatorio_fiscal_novo'     => fn() => (new RelatorioFiscalControlador())->novo(),
    'relatorio_fiscal_salvar'   => fn() => (new RelatorioFiscalControlador())->salvar(),
    'relatorio_fiscal_ver'      => fn() => (new RelatorioFiscalControlador())->ver(),
    'relatorio_fiscal_imprimir' => fn() => (new RelatorioFiscalControlador())->imprimir(),
    'relatorio_fiscal_excluir'  => fn() => (new RelatorioFiscalControlador())->excluir(),

    // Super Admin
    'listar_empresas'   => fn() => (new SuperAdminControlador())->index(),
    'nova_empresa'      => fn() => (new SuperAdminControlador())->nova(),
    'editar_empresa'    => fn() => (new SuperAdminControlador())->editar(),
    'excluir_empresa'   => fn() => (new SuperAdminControlador())->excluir(),
    'sa_editar_usuario' => fn() => (new SuperAdminControlador())->editarUsuario(),
    'sa_excluir_usuario'=> fn() => (new SuperAdminControlador())->excluirUsuario(),
    'precos_planos'        => fn() => (new SuperAdminControlador())->precosPlanos(),
    'salvar_precos'        => fn() => (new SuperAdminControlador())->salvarPrecos(),
    'sa_enviar_teste_gratis'=> fn() => (new SuperAdminControlador())->enviarTesteGratis(),
    'sa_selecionar_empresa'=> fn() => (new SuperAdminControlador())->selecionarEmpresa(),
    'sa_sair_empresa'      => fn() => (new SuperAdminControlador())->sairEmpresa(),
    'logs_sistema'         => fn() => (new SuperAdminControlador())->logsSistema(),

    // Tour guiado substituiu o manual
    'manual' => fn() => header('Location: index.php?acao=painel'),

    // Páginas de venda/checkout (acessíveis sem auth)
    'planos'               => fn() => require __DIR__ . '/planos.php',
    'checkout'             => fn() => require __DIR__ . '/checkout.php',
    'processar_checkout'   => fn() => require __DIR__ . '/processar_checkout.php',
    'verificar_pagamento'  => fn() => require __DIR__ . '/verificar_pagamento.php',
];

try {
    if (isset($rotas[$acao])) {
        ($rotas[$acao])();
    } else {
        header('Location: index.php?acao=login');
    }
} catch (Exception $e) {
    error_log('[CHRONA] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    require_once __DIR__ . '/../visoes/estruturas/erro.php';
}
