<?php
// Arquivo: CHRONA/index.php
// Função: Redirecionar o tráfego da raiz para a pasta 'publico' onde está o sistema real

// Opção 1: Redirecionamento direto (Mais simples)
// header('Location: publico/index.php');
// exit;

// Opção 2: Inclusão (Mantém a URL mais bonita, sem mostrar /publico/)
// Ajuste o caminho conforme necessário
if (file_exists(__DIR__ . '/publico/index.php')) {
    require_once __DIR__ . '/publico/index.php';
} else {
    // Caso o arquivo principal já esteja na raiz e não na pasta publico
    // Isso evita o erro se você já moveu os arquivos
    echo "Erro: O sistema não encontrou o arquivo inicial em /publico/index.php";
}