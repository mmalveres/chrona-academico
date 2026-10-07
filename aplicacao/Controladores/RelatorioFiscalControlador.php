<?php
namespace Aplicacao\Controladores;

use Aplicacao\Configuracao\BancoDeDados;
use Aplicacao\Helpers\CsrfHelper;
use Aplicacao\Helpers\LogHelper;

class RelatorioFiscalControlador
{
    public function __construct()
    {
        self::criarTabelasSeNecessario();
    }

    private function verificarAcesso(): void
    {
        Autenticacao::verificarLogin();
        $nivel = $_SESSION['nivel_acesso'] ?? '';
        $isSA  = $_SESSION['is_superadmin'] ?? false;
        if ($nivel !== 'fiscal' && !$isSA) {
            header('Location: index.php?acao=painel');
            exit;
        }
    }

    private function conn(): \PDO
    {
        return BancoDeDados::getConexao();
    }

    private function idEmpresa(): int
    {
        return (int)($_SESSION['id_empresa'] ?? 0);
    }

    private function idUsuario(): int
    {
        return (int)($_SESSION['usuario_id'] ?? 0);
    }

    private function isSA(): bool
    {
        return (bool)($_SESSION['is_superadmin'] ?? false);
    }

    public function listar(): void
    {
        $this->verificarAcesso();
        $conn      = $this->conn();
        $idEmpresa = $this->idEmpresa();
        $idUsuario = $this->idUsuario();

        if ($this->isSA()) {
            $stmt = $conn->prepare("
                SELECT rf.*, o.nome AS nome_obra, u.nome AS nome_fiscal, u.orgao_fiscalizador
                FROM relatorios_fiscais rf
                LEFT JOIN obras o ON rf.id_obra = o.id
                LEFT JOIN usuarios u ON rf.id_usuario = u.id
                WHERE rf.id_empresa = ?
                ORDER BY rf.data_visita DESC, rf.id DESC
            ");
            $stmt->execute([$idEmpresa]);
        } else {
            $stmt = $conn->prepare("
                SELECT rf.*, o.nome AS nome_obra, u.nome AS nome_fiscal, u.orgao_fiscalizador
                FROM relatorios_fiscais rf
                LEFT JOIN obras o ON rf.id_obra = o.id
                LEFT JOIN usuarios u ON rf.id_usuario = u.id
                WHERE rf.id_empresa = ? AND rf.id_usuario = ?
                ORDER BY rf.data_visita DESC, rf.id DESC
            ");
            $stmt->execute([$idEmpresa, $idUsuario]);
        }

        $relatorios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../../visoes/fiscal/lista.php';
    }

    public function novo(): void
    {
        $this->verificarAcesso();
        $conn      = $this->conn();
        $idEmpresa = $this->idEmpresa();
        $idUsuario = $this->idUsuario();

        $stmt = $conn->prepare("SELECT id, nome, cidade FROM obras WHERE id_empresa = ? ORDER BY nome");
        $stmt->execute([$idEmpresa]);
        $obras = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT orgao_fiscalizador, logo_orgao FROM usuarios WHERE id = ?");
        $stmt->execute([$idUsuario]);
        $fiscal = $stmt->fetch(\PDO::FETCH_ASSOC);
        $orgaoPadrao = $fiscal['orgao_fiscalizador'] ?? '';

        require_once __DIR__ . '/../../visoes/fiscal/formulario.php';
    }

    public function salvar(): void
    {
        $this->verificarAcesso();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?acao=relatorio_fiscal');
            exit;
        }

        $conn      = $this->conn();
        $idEmpresa = $this->idEmpresa();
        $idUsuario = $this->idUsuario();

        $idObra      = (int)($_POST['id_obra']     ?? 0);
        $dataVisita  = $_POST['data_visita']        ?? date('Y-m-d');
        $orgao       = trim($_POST['orgao']         ?? '');
        $status      = in_array($_POST['status'] ?? '', ['conforme', 'nao_conforme'])
                        ? $_POST['status'] : 'conforme';
        $observacoes = trim($_POST['observacoes']   ?? '');

        if (!$idObra || !$orgao) {
            header('Location: index.php?acao=relatorio_fiscal_novo&erro=campos');
            exit;
        }

        $conn->prepare("
            INSERT INTO relatorios_fiscais
                (id_empresa, id_obra, id_usuario, data_visita, orgao, status, observacoes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([$idEmpresa, $idObra, $idUsuario, $dataVisita, $orgao, $status, $observacoes ?: null]);

        $idRelatorio = (int)$conn->lastInsertId();

        // Upload de fotos
        if (!empty($_FILES['fotos']['name'][0])) {
            $dir = __DIR__ . '/../../publico/uploads/relatorios_fiscais/' . $idEmpresa . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $stmtFoto = $conn->prepare(
                "INSERT INTO fotos_relatorio_fiscal (id_relatorio, caminho, legenda) VALUES (?, ?, ?)"
            );
            foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp) {
                if (!is_uploaded_file($tmp)) continue;
                $ext = strtolower(pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) continue;
                $nome = 'rf_' . $idRelatorio . '_' . time() . '_' . $i . '.' . $ext;
                if (move_uploaded_file($tmp, $dir . $nome)) {
                    $legenda = trim($_POST['legenda'][$i] ?? '');
                    $stmtFoto->execute([
                        $idRelatorio,
                        'uploads/relatorios_fiscais/' . $idEmpresa . '/' . $nome,
                        $legenda ?: null,
                    ]);
                }
            }
        }

        LogHelper::registrar('Relatório fiscal criado', 'Relatório #' . $idRelatorio);
        header("Location: index.php?acao=relatorio_fiscal_ver&id={$idRelatorio}&msg=criado");
        exit;
    }

    public function ver(): void
    {
        $this->verificarAcesso();
        [$relatorio, $fotos] = $this->buscarRelatorio((int)($_GET['id'] ?? 0));
        if (!$relatorio) {
            header('Location: index.php?acao=relatorio_fiscal');
            exit;
        }
        require_once __DIR__ . '/../../visoes/fiscal/ver.php';
    }

    public function imprimir(): void
    {
        $this->verificarAcesso();
        [$relatorio, $fotos] = $this->buscarRelatorio((int)($_GET['id'] ?? 0));
        if (!$relatorio) {
            header('Location: index.php?acao=relatorio_fiscal');
            exit;
        }
        require_once __DIR__ . '/../../visoes/fiscal/imprimir.php';
    }

    public function excluir(): void
    {
        $this->verificarAcesso();
        CsrfHelper::verificarOuAbortar();
        $conn      = $this->conn();
        $idEmpresa = $this->idEmpresa();
        $idUsuario = $this->idUsuario();
        $id        = (int)($_GET['id'] ?? 0);

        if ($this->isSA()) {
            $stmt = $conn->prepare("SELECT id FROM relatorios_fiscais WHERE id = ? AND id_empresa = ?");
            $stmt->execute([$id, $idEmpresa]);
        } else {
            $stmt = $conn->prepare("SELECT id FROM relatorios_fiscais WHERE id = ? AND id_empresa = ? AND id_usuario = ?");
            $stmt->execute([$id, $idEmpresa, $idUsuario]);
        }

        if (!$stmt->fetch()) {
            header('Location: index.php?acao=relatorio_fiscal');
            exit;
        }

        // Remove fotos do filesystem
        $fotos = $conn->prepare("SELECT caminho FROM fotos_relatorio_fiscal WHERE id_relatorio = ?");
        $fotos->execute([$id]);
        foreach ($fotos->fetchAll(\PDO::FETCH_COLUMN) as $caminho) {
            $path = __DIR__ . '/../../publico/' . $caminho;
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $conn->prepare("DELETE FROM fotos_relatorio_fiscal WHERE id_relatorio = ?")->execute([$id]);
        $conn->prepare("DELETE FROM relatorios_fiscais WHERE id = ?")->execute([$id]);

        LogHelper::registrar('Relatório fiscal excluído', 'Relatório #' . $id);
        header('Location: index.php?acao=relatorio_fiscal&msg=excluido');
        exit;
    }

    // Salva logo do órgão para o fiscal (chamado pelo UsuarioControlador)
    public static function salvarLogo(int $idUsuario, array $file): ?string
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
            return null;
        }
        $dir  = __DIR__ . '/../../publico/assets/img/';
        $nome = 'logo_fiscal_' . $idUsuario . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $dir . $nome)) {
            return $nome;
        }
        return null;
    }

    private function buscarRelatorio(int $id): array
    {
        $conn      = $this->conn();
        $idEmpresa = $this->idEmpresa();
        $idUsuario = $this->idUsuario();

        if ($this->isSA()) {
            $stmt = $conn->prepare("
                SELECT rf.*, o.nome AS nome_obra, o.cidade, o.endereco,
                       u.nome AS nome_fiscal, u.logo_orgao, u.orgao_fiscalizador
                FROM relatorios_fiscais rf
                LEFT JOIN obras o ON rf.id_obra = o.id
                LEFT JOIN usuarios u ON rf.id_usuario = u.id
                WHERE rf.id = ? AND rf.id_empresa = ?
            ");
            $stmt->execute([$id, $idEmpresa]);
        } else {
            $stmt = $conn->prepare("
                SELECT rf.*, o.nome AS nome_obra, o.cidade, o.endereco,
                       u.nome AS nome_fiscal, u.logo_orgao, u.orgao_fiscalizador
                FROM relatorios_fiscais rf
                LEFT JOIN obras o ON rf.id_obra = o.id
                LEFT JOIN usuarios u ON rf.id_usuario = u.id
                WHERE rf.id = ? AND rf.id_empresa = ? AND rf.id_usuario = ?
            ");
            $stmt->execute([$id, $idEmpresa, $idUsuario]);
        }

        $relatorio = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$relatorio) {
            return [null, []];
        }

        $stmtFotos = $conn->prepare("SELECT * FROM fotos_relatorio_fiscal WHERE id_relatorio = ? ORDER BY id");
        $stmtFotos->execute([$id]);
        $fotos = $stmtFotos->fetchAll(\PDO::FETCH_ASSOC);

        return [$relatorio, $fotos];
    }

    public static function criarTabelasSeNecessario(): void
    {
        try {
            $conn = BancoDeDados::getConexao();
            $conn->exec("
                CREATE TABLE IF NOT EXISTS relatorios_fiscais (
                    id           INT          AUTO_INCREMENT PRIMARY KEY,
                    id_empresa   INT          NOT NULL,
                    id_obra      INT          NOT NULL,
                    id_usuario   INT          NOT NULL,
                    data_visita  DATE         NOT NULL,
                    orgao        VARCHAR(255) NOT NULL DEFAULT '',
                    status       ENUM('conforme','nao_conforme') NOT NULL DEFAULT 'conforme',
                    observacoes  TEXT         NULL,
                    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                    updated_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_empresa (id_empresa),
                    INDEX idx_obra    (id_obra),
                    INDEX idx_usuario (id_usuario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            $conn->exec("
                CREATE TABLE IF NOT EXISTS fotos_relatorio_fiscal (
                    id           INT          AUTO_INCREMENT PRIMARY KEY,
                    id_relatorio INT          NOT NULL,
                    caminho      VARCHAR(500) NOT NULL,
                    legenda      VARCHAR(255) NULL,
                    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_relatorio (id_relatorio)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (\Exception) {}
    }
}
