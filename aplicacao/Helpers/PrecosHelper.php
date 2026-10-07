<?php
// aplicacao/Helpers/PrecosHelper.php
namespace Aplicacao\Helpers;

use Aplicacao\Configuracao\BancoDeDados;

/**
 * Centraliza os preços dos planos.
 * Lê da tabela `planos_precos`; se não existir, usa os defaults hardcoded.
 * preco_anual = preco_mensal × (1 - desconto_anual_pct / 100)
 */
class PrecosHelper
{
    const DEFAULTS = [
        'autonomo'    => ['mensal' => 49.00,  'desc_pct' => 20],
        'construtora' => ['mensal' => 149.00, 'desc_pct' => 20],
        'empresarial' => ['mensal' => 250.00, 'desc_pct' => 20],
    ];

    /** Retorna array [plano => [mensal, anual, desc_pct, anual_total]] */
    public static function getPrecos(): array
    {
        try {
            $conn = BancoDeDados::getConexao();
            $st   = $conn->query(
                "SELECT plano, preco_mensal, desconto_anual_pct FROM planos_precos ORDER BY FIELD(plano,'autonomo','construtora','empresarial')"
            );
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($rows)) return self::defaults();
            $result = [];
            foreach ($rows as $r) {
                $result[$r['plano']] = self::calcular((float)$r['preco_mensal'], (int)$r['desconto_anual_pct']);
            }
            return $result;
        } catch (\Exception $e) {
            return self::defaults();
        }
    }

    /** Garante que a tabela existe e insere os defaults se estiver vazia. */
    public static function criarTabelaSeNecessario(): void
    {
        $conn = BancoDeDados::getConexao();
        $conn->exec("CREATE TABLE IF NOT EXISTS planos_precos (
            plano              VARCHAR(20)          NOT NULL PRIMARY KEY,
            preco_mensal       DECIMAL(8,2)         NOT NULL,
            desconto_anual_pct TINYINT UNSIGNED     NOT NULL DEFAULT 20,
            updated_at         TIMESTAMP            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        foreach (self::DEFAULTS as $plano => $d) {
            $conn->prepare(
                "INSERT IGNORE INTO planos_precos (plano, preco_mensal, desconto_anual_pct) VALUES (?, ?, ?)"
            )->execute([$plano, $d['mensal'], $d['desc_pct']]);
        }
    }

    /** Salva um plano. */
    public static function salvar(string $plano, float $mensal, int $descPct): void
    {
        self::criarTabelaSeNecessario();
        $conn = BancoDeDados::getConexao();
        $conn->prepare(
            "INSERT INTO planos_precos (plano, preco_mensal, desconto_anual_pct) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE preco_mensal = VALUES(preco_mensal), desconto_anual_pct = VALUES(desconto_anual_pct)"
        )->execute([$plano, $mensal, $descPct]);
    }

    // ── private ──────────────────────────────────────

    private static function calcular(float $mensal, int $descPct): array
    {
        $anual       = round($mensal * (1 - $descPct / 100), 2);
        $anualTotal  = round($anual * 12, 2);
        $economia    = round(($mensal - $anual) * 12, 2);
        return [
            'mensal'      => $mensal,
            'anual'       => $anual,
            'anual_total' => $anualTotal,
            'economia'    => $economia,
            'desc_pct'    => $descPct,
        ];
    }

    private static function defaults(): array
    {
        $r = [];
        foreach (self::DEFAULTS as $plano => $d) {
            $r[$plano] = self::calcular($d['mensal'], $d['desc_pct']);
        }
        return $r;
    }
}
