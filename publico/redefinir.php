<?php
// publico/redefinir.php
require_once __DIR__ . '/../autoload.php';
use Aplicacao\Configuracao\BancoDeDados;

try {
    $db = BancoDeDados::getConexao();
    
    // Gera o hash real e seguro para a senha 'admin123'
    $nova_senha = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Atualiza o usuário ID 1
    $sql = "UPDATE usuarios SET senha = :senha WHERE id = 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':senha', $nova_senha);
    $stmt->execute();
    
    echo "<h2 style='color:green'>✅ Senha redefinida com sucesso!</h2>";
    echo "<p>Agora você pode logar com: <strong>admin@sistema.com</strong> / <strong>admin123</strong></p>";
    echo "<a href='index.php'>Ir para Login</a>";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}