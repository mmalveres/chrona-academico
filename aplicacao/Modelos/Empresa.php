<?php //aplicacao/Modelos/Empresa.php
namespace Aplicacao\Modelos;

use Aplicacao\Configuracao\BancoDeDados;
use PDO;
use Exception;

class Empresa {
    private $conn;

    public function __construct() {
        $this->conn = BancoDeDados::getConexao();
    }

    public function listar() {
        $stmt = $this->conn->query("SELECT * FROM empresas ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- UPDATED: Fetches company data + Admin Email ---
    public function buscarPorId($id) {
        $sql = "SELECT e.*, u.email as usuario_email 
                FROM empresas e 
                LEFT JOIN usuarios u ON u.id_empresa = e.id AND u.nivel_acesso = 'admin' 
                WHERE e.id = :id 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- NEW: Updates Admin Credentials (Email/Password) ---
    public function atualizarCredenciaisAdmin($idEmpresa, $novoEmail, $novaSenha) {
        // 1. Find the admin user for this company
        $stmtFind = $this->conn->prepare("SELECT id FROM usuarios WHERE id_empresa = ? AND nivel_acesso = 'admin' LIMIT 1");
        $stmtFind->execute([$idEmpresa]);
        $usuario = $stmtFind->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) return false; // No admin found to update

        $campos = [];
        $params = [];

        // Update email if provided
        if (!empty($novoEmail)) {
            $campos[] = "email = ?";
            $params[] = $novoEmail;
        }

        // Update password if provided
        if (!empty($novaSenha)) {
            $campos[] = "senha = ?";
            $params[] = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        if (empty($campos)) return true; // Nothing to update

        // Add ID to params for the WHERE clause
        $params[] = $usuario['id'];

        $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function atualizar($dados) {
        $sql = "UPDATE empresas SET 
                nome_fantasia = :nome, 
                razao_social = :razao, 
                cnpj = :cnpj, 
                ativo = :ativo, 
                plano = :plano, 
                data_vencimento = :vencimento 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nome' => $dados['nome_fantasia'],
            ':razao' => $dados['razao_social'],
            ':cnpj' => $dados['cnpj'],
            ':ativo' => $dados['ativo'],
            ':plano' => $dados['plano'],
            ':vencimento' => $dados['data_vencimento'],
            ':id' => $dados['id']
        ]);
    }

    public function excluir($id) {
        if ($id == 1) return false;

        try {
            $this->conn->beginTransaction();

            $this->conn->prepare("DELETE FROM usuarios WHERE id_empresa = :id")->execute([':id' => $id]);
            $this->conn->prepare("DELETE FROM obras WHERE id_empresa = :id")->execute([':id' => $id]);
            $this->conn->prepare("DELETE FROM empresas WHERE id = :id")->execute([':id' => $id]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function criarComAdmin($dadosEmpresa, $dadosUsuario) {
        try {
            $this->conn->beginTransaction();

            // 1. Calculate Expiration
            $plano = $dadosEmpresa['plano'];
            $dataVencimento = date('Y-m-d'); 

            if ($plano == 'mensal') {
                $dataVencimento = date('Y-m-d', strtotime('+30 days'));
            } elseif ($plano == 'anual') {
                $dataVencimento = date('Y-m-d', strtotime('+1 year'));
            } elseif ($plano == 'vitalicio') {
                $dataVencimento = '2099-12-31'; 
            }

            // 2. Insert Company
            $sqlEmp = "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, ativo, plano, data_vencimento) 
                       VALUES (:nome, :razao, :cnpj, 1, :plano, :vencimento)";
            
            $stmtEmp = $this->conn->prepare($sqlEmp);
            $stmtEmp->execute([
                ':nome' => $dadosEmpresa['nome_fantasia'],
                ':razao' => $dadosEmpresa['razao_social'],
                ':cnpj' => $dadosEmpresa['cnpj'],
                ':plano' => $plano,
                ':vencimento' => $dataVencimento
            ]);
            
            $idEmpresaNova = $this->conn->lastInsertId();

            // 3. Insert User
            $sqlUser = "INSERT INTO usuarios (id_empresa, nome, email, senha, nivel_acesso) 
                        VALUES (:id_empresa, :nome, :email, :senha, 'admin')";
            $stmtUser = $this->conn->prepare($sqlUser);
            $senhaHash = password_hash($dadosUsuario['senha'], PASSWORD_DEFAULT);
            
            $stmtUser->execute([
                ':id_empresa' => $idEmpresaNova,
                ':nome' => $dadosUsuario['nome'],
                ':email' => $dadosUsuario['email'],
                ':senha' => $senhaHash
            ]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}