<?php
namespace Aplicacao\Controladores;

use Aplicacao\Controladores\Autenticacao;
use Aplicacao\Configuracao\BancoDeDados;
use Aplicacao\Servicos\ClimaService;
use PDO;

class PainelControlador {

    public function index() {
        Autenticacao::verificarLogin();

        $conn = BancoDeDados::getConexao();

        if (session_status() === PHP_SESSION_NONE) session_start();

        $id_empresa = $_SESSION['id_empresa']   ?? 0;
        $nivel      = $_SESSION['nivel_acesso'] ?? '';
        $idUsuario  = $_SESSION['usuario_id']   ?? 0;

        // ── FISCAL: redireciona direto para os relatórios de visita ──
        if ($nivel === 'fiscal') {
            header('Location: index.php?acao=relatorio_fiscal');
            exit;
        }
        // ─────────────────────────────────────────────────────────────

        // ── CLIENTE: mostra obras vinculadas via nova tabela ─────────
        if ($nivel === 'cliente') {
            $stmt = $conn->prepare(
                "SELECT o.id, o.nome, o.cidade, o.status,
                        e.nome_fantasia as empresa
                 FROM usuario_obras_acesso uoa
                 INNER JOIN obras o    ON uoa.id_obra    = o.id
                 INNER JOIN empresas e ON uoa.id_empresa = e.id
                 WHERE uoa.id_usuario = ?
                 ORDER BY e.nome_fantasia ASC, o.nome ASC"
            );
            $stmt->execute([$idUsuario]);
            $obrasCliente = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Nenhuma obra vinculada
            if (empty($obrasCliente)) {
                $totalObras     = 0;
                $obrasAndamento = 0;
                $diariosHoje    = 0;
                $ultimasObras   = [];
                $clima          = null;
                $semObraVinculada = true;
                require_once __DIR__ . '/../../visoes/painel/inicio.php';
                return;
            }

            // Apenas uma obra → redireciona direto para o diário
            if (count($obrasCliente) === 1) {
                header("Location: index.php?acao=ver_diario&id_obra={$obrasCliente[0]['id']}");
                exit;
            }

            // Várias obras → tela de seleção
            require_once __DIR__ . '/../../visoes/painel/painel_cliente.php';
            return;
        }
        // ─────────────────────────────────────────────────────────────

        // 1. Total de Obras da Empresa
        $stmt = $conn->prepare(
            "SELECT COUNT(*) as total FROM obras WHERE id_empresa = :id"
        );
        $stmt->execute([':id' => $id_empresa]);
        $totalObras = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // 2. Obras em Andamento
        $stmt = $conn->prepare(
            "SELECT COUNT(*) as total FROM obras
             WHERE id_empresa = :id
             AND status NOT LIKE '%Concluída%'
             AND status NOT LIKE '%concluida%'
             AND status NOT LIKE '%Inativa%'
             AND status != '0'"
        );
        $stmt->execute([':id' => $id_empresa]);
        $obrasAndamento = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // 3. Diários feitos HOJE
        $hoje = date('Y-m-d');
        $stmt = $conn->prepare(
            "SELECT COUNT(d.id) as total
             FROM diario_obras d
             INNER JOIN obras o ON d.id_obra = o.id
             WHERE o.id_empresa = :id AND d.data_diario = :hoje"
        );
        $stmt->execute([':id' => $id_empresa, ':hoje' => $hoje]);
        $diariosHoje = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // 4. Últimas Obras
        $stmt = $conn->prepare(
            "SELECT * FROM obras WHERE id_empresa = :id ORDER BY id DESC LIMIT 5"
        );
        $stmt->execute([':id' => $id_empresa]);
        $ultimasObras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Clima (com cache de sessão de 10 minutos)
        $clima     = null;
        $cacheKey  = 'clima_cache_' . $id_empresa;
        $cacheTtl  = 600;

        if (!empty($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['ts']) < $cacheTtl) {
            $clima = $_SESSION[$cacheKey]['dados'];
        } elseif (count($ultimasObras) > 0 && class_exists('Aplicacao\Servicos\ClimaService')) {
            $cidadeAlvo = !empty($ultimasObras[0]['cidade']) ? $ultimasObras[0]['cidade'] : 'São Paulo';
            try {
                $climaService = new ClimaService();
                $coords = $climaService->buscarCoordsPorCidade($cidadeAlvo);
                if ($coords) {
                    $clima = $climaService->obterClimaAtual($coords['lat'], $coords['lon']);
                }
            } catch (\Exception $e) {
                $clima = null;
            }
            $_SESSION[$cacheKey] = ['dados' => $clima, 'ts' => time()];
        }

        require_once __DIR__ . '/../../visoes/painel/inicio.php';
    }

    // ── Painel de seleção de obras para o cliente ────────────────
    public function painelCliente() {
        Autenticacao::verificarLogin();

        if (($_SESSION['nivel_acesso'] ?? '') !== 'cliente') {
            header('Location: index.php?acao=painel');
            exit;
        }

        $conn = BancoDeDados::getConexao();
        $stmt = $conn->prepare(
            "SELECT o.id, o.nome, o.cidade, o.status,
                    e.nome_fantasia as empresa
             FROM usuario_obras_acesso uoa
             INNER JOIN obras o    ON uoa.id_obra    = o.id
             INNER JOIN empresas e ON uoa.id_empresa = e.id
             WHERE uoa.id_usuario = ?
             ORDER BY e.nome_fantasia ASC, o.nome ASC"
        );
        $stmt->execute([$_SESSION['usuario_id']]);
        $obrasCliente = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../visoes/painel/painel_cliente.php';
    }
}