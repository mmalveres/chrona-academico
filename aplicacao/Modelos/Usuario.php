<?php
namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;

class Usuario {
    private $conn;
    private $id_empresa;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->id_empresa = $_SESSION['id_empresa'] ?? 0;
    }

    public function autenticar($email, $senha) {
        $usuario = $this->buscarPorEmail($email);
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }
        return false;
    }

    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvarTokenRecuperacao($id, $token, $validade) {
        $sql = "UPDATE usuarios SET token_recuperacao = :token, token_validade = :validade WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':validade', $validade);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function buscarPorToken($token) {
        $sql = "SELECT * FROM usuarios WHERE token_recuperacao = :token AND token_validade > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarSenhaPorToken($id, $novaSenha) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET senha = :senha, token_recuperacao = NULL, token_validade = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':senha', $senhaHash);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    // ── Lista usuários da empresa + clientes vinculados às obras dela ──
    public function listar(): array {
        $sql = "
            SELECT DISTINCT u.*, 'proprio' as origem
            FROM usuarios u
            WHERE u.id_empresa = :id1

            UNION

            SELECT DISTINCT u.*, 'vinculado' as origem
            FROM usuarios u
            INNER JOIN usuario_obras_acesso uoa ON uoa.id_usuario = u.id
            INNER JOIN obras o ON uoa.id_obra = o.id
            WHERE o.id_empresa = :id2
              AND u.id_empresa != :id3
              AND u.nivel_acesso = 'cliente'

            ORDER BY nome ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id1', $this->id_empresa);
        $stmt->bindParam(':id2', $this->id_empresa);
        $stmt->bindParam(':id3', $this->id_empresa);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPaginado(int $pagina = 1, int $porPagina = 15): array {
        $offset = ($pagina - 1) * $porPagina;
        $sql = "
            SELECT DISTINCT u.*, 'proprio' as origem
            FROM usuarios u
            WHERE u.id_empresa = :id1

            UNION

            SELECT DISTINCT u.*, 'vinculado' as origem
            FROM usuarios u
            INNER JOIN usuario_obras_acesso uoa ON uoa.id_usuario = u.id
            INNER JOIN obras o ON uoa.id_obra = o.id
            WHERE o.id_empresa = :id2
              AND u.id_empresa != :id3
              AND u.nivel_acesso = 'cliente'

            ORDER BY nome ASC
            LIMIT :limite OFFSET :offset
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id1', $this->id_empresa);
        $stmt->bindParam(':id2', $this->id_empresa);
        $stmt->bindParam(':id3', $this->id_empresa);
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarTotal(): int {
        $sql = "
            SELECT COUNT(*) FROM (
                SELECT DISTINCT u.id
                FROM usuarios u
                WHERE u.id_empresa = :id1

                UNION

                SELECT DISTINCT u.id
                FROM usuarios u
                INNER JOIN usuario_obras_acesso uoa ON uoa.id_usuario = u.id
                INNER JOIN obras o ON uoa.id_obra = o.id
                WHERE o.id_empresa = :id2
                  AND u.id_empresa != :id3
                  AND u.nivel_acesso = 'cliente'
            ) AS total
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id1', $this->id_empresa);
        $stmt->bindParam(':id2', $this->id_empresa);
        $stmt->bindParam(':id3', $this->id_empresa);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // ── Busca por ID sem filtro de empresa ──
    // (necessário para editar clientes vinculados de outras empresas)
    public function buscarPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrar($dados) {
        $sql = "INSERT INTO usuarios (id_empresa, nome, email, senha, nivel_acesso)
                VALUES (:id_empresa, :nome, :email, :senha, :nivel)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->bindParam(':nome',       $dados['nome']);
        $stmt->bindParam(':email',      $dados['email']);

        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $stmt->bindParam(':senha', $senhaHash);
        $stmt->bindParam(':nivel', $dados['nivel_acesso']);

        return $stmt->execute();
    }

    public function atualizar($dados) {
        $sql = "UPDATE usuarios SET nome = :nome, email = :email, nivel_acesso = :nivel
                WHERE id = :id AND id_empresa = :id_empresa";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome',       $dados['nome']);
        $stmt->bindParam(':email',      $dados['email']);
        $stmt->bindParam(':nivel',      $dados['nivel_acesso']);
        $stmt->bindParam(':id',         $dados['id']);
        $stmt->bindParam(':id_empresa', $this->id_empresa);

        if ($stmt->execute()) {
            if (!empty($dados['senha'])) {
                $sqlSenha = "UPDATE usuarios SET senha = :senha WHERE id = :id";
                $stmtSenha = $this->conn->prepare($sqlSenha);
                $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
                $stmtSenha->execute([':senha' => $senhaHash, ':id' => $dados['id']]);
            }
            return true;
        }
        return false;
    }

    public function atualizarSenhaDireta($id, $novaSenha) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET senha = :senha WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':senha', $senhaHash);
        $stmt->bindValue(':id',    $id);
        return $stmt->execute();
    }

    public function excluir($id) {
        if ($id == $_SESSION['usuario_id']) return false;

        $stmt = $this->conn->prepare(
            "DELETE FROM usuarios WHERE id = :id AND id_empresa = :id_empresa"
        );
        $stmt->bindParam(':id',         $id);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        return $stmt->execute();
    }
}