<?php
// autoload.php na raiz do projeto

// Carrega variáveis do .env
$_envFile = __DIR__ . '/.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_linha) {
        if (str_starts_with(trim($_linha), '#') || !str_contains($_linha, '=')) continue;
        [$_chave, $_valor] = explode('=', $_linha, 2);
        $_ENV[trim($_chave)] = trim($_valor);
        putenv(trim($_chave) . '=' . trim($_valor));
    }
}
unset($_envFile, $_linha, $_chave, $_valor);

spl_autoload_register(function ($classe) {
    // Prefixo do namespace base
    $prefixo = 'Aplicacao\\';
    
    // Diretório base onde estão as classes (pasta aplicacao/)
    $diretorio_base = __DIR__ . '/aplicacao/';

    // Verifica se a classe usa o prefixo correto
    $tamanho = strlen($prefixo);
    if (strncmp($prefixo, $classe, $tamanho) !== 0) {
        return;
    }

    // Remove o prefixo e troca as barras invertidas por normais
    $classe_relativa = substr($classe, $tamanho);
    $arquivo = $diretorio_base . str_replace('\\', '/', $classe_relativa) . '.php';

    // Se o arquivo existir, carrega-o
    if (file_exists($arquivo)) {
        require $arquivo;
    }
});