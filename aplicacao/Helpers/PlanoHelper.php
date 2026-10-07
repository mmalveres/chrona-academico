<?php
// aplicacao/Helpers/PlanoHelper.php

namespace Aplicacao\Helpers;

/**
 * PlanoHelper — Controla as permissões e limites de cada plano contratado.
 *
 * Planos:
 *   autonomo    → 1 obra, 1 usuário, sem cronograma, sem medição
 *   construtora → 3 obras, 3 usuários, cronograma + medição
 *   empresarial → ilimitado, tudo liberado
 *
 * COMO USAR em qualquer controlador ou visão:
 *   use Aplicacao\Helpers\PlanoHelper;
 *   if (!PlanoHelper::podeUsarMedicao()) { ... bloquear ... }
 */
class PlanoHelper
{
    // -----------------------------------------------------------------
    // Tabela de regras por plano
    // -----------------------------------------------------------------
    const REGRAS = [
        'autonomo' => [
            'label'          => 'Autônomo',
            'max_obras'      => 1,
            'max_usuarios'   => 1,
            'cronograma'     => false,
            'medicao'        => false,
            'importar_orc'   => true,
            'rdo'            => true,
            'dds'            => true,   // obrigação NR-18 — todos os planos
            'nc'             => false,
            'relatorios_plus'=> false,
            'suporte_wp'     => false,
        ],
        'construtora' => [
            'label'          => 'Construtora',
            'max_obras'      => 3,
            'max_usuarios'   => 3,
            'cronograma'     => true,
            'medicao'        => true,
            'importar_orc'   => true,
            'rdo'            => true,
            'dds'            => true,
            'nc'             => true,
            'relatorios_plus'=> false,
            'suporte_wp'     => false,
        ],
        'empresarial' => [
            'label'          => 'Empresarial',
            'max_obras'      => -1,
            'max_usuarios'   => -1,
            'cronograma'     => true,
            'medicao'        => true,
            'importar_orc'   => true,
            'rdo'            => true,
            'dds'            => true,
            'nc'             => true,
            'relatorios_plus'=> true,
            'suporte_wp'     => true,
        ],
        'mensal' => [
            'label'          => 'Construtora',
            'max_obras'      => 3,
            'max_usuarios'   => 3,
            'cronograma'     => true,
            'medicao'        => true,
            'importar_orc'   => true,
            'rdo'            => true,
            'dds'            => true,
            'nc'             => true,
            'relatorios_plus'=> false,
            'suporte_wp'     => false,
        ],
    ];

    // -----------------------------------------------------------------
    // Helpers internos
    // -----------------------------------------------------------------

    /** Retorna o plano da empresa logada (lido da sessão). */
    public static function getPlano(): string
    {
        return $_SESSION['plano'] ?? 'autonomo';
    }

    /** Retorna as regras do plano atual. */
    public static function getRegras(): array
    {
        $plano = self::getPlano();
        return self::REGRAS[$plano] ?? self::REGRAS['autonomo'];
    }

    /** Retorna o label legível do plano (ex: "Construtora"). */
    public static function getLabelPlano(): string
    {
        return self::getRegras()['label'];
    }

    // -----------------------------------------------------------------
    // Verificações de funcionalidade
    // -----------------------------------------------------------------

    public static function podeUsarCronograma(): bool
    {
        return (bool) self::getRegras()['cronograma'];
    }

    public static function podeUsarMedicao(): bool
    {
        return (bool) self::getRegras()['medicao'];
    }

    public static function podeImportarOrcamento(): bool
    {
        return (bool) self::getRegras()['importar_orc'];
    }

    public static function podeUsarDDS(): bool
    {
        return (bool)(self::getRegras()['dds'] ?? true);
    }

    public static function podeUsarNC(): bool
    {
        return (bool)(self::getRegras()['nc'] ?? false);
    }

    // -----------------------------------------------------------------
    // Verificações de limite numérico
    // -----------------------------------------------------------------

    public static function getMaxObras(): int
    {
        return (int) self::getRegras()['max_obras'];
    }

    public static function getMaxUsuarios(): int
    {
        return (int) self::getRegras()['max_usuarios'];
    }

    /**
     * Retorna true se ainda for permitido criar mais uma obra.
     * @param int $totalAtual Número de obras já cadastradas.
     */
    public static function podeAdicionarObra(int $totalAtual): bool
    {
        $max = self::getMaxObras();
        return $max === -1 || $totalAtual < $max;
    }

    /**
     * Retorna true se ainda for permitido criar mais um usuário.
     * @param int $totalAtual Número de usuários já cadastrados.
     */
    public static function podeAdicionarUsuario(int $totalAtual): bool
    {
        $max = self::getMaxUsuarios();
        return $max === -1 || $totalAtual < $max;
    }

    // -----------------------------------------------------------------
    // Helpers para exibição nas views
    // -----------------------------------------------------------------

    /**
     * Gera o HTML do alerta de upgrade para colar nas views bloqueadas.
     * @param string $recurso  Texto do que está bloqueado (ex: "Cronograma Físico-Financeiro").
     * @param string $urlVoltar Link do botão Voltar.
     */
    public static function htmlBloqueio(string $recurso, string $urlVoltar = 'index.php?acao=painel'): string
    {
        $plano  = self::getLabelPlano();
        $url    = self::getUrlUpgrade();

        return <<<HTML
        <div class="container py-5 text-center">
            <div class="card-custom mx-auto" style="max-width:520px; padding: 40px 30px;">
                <div class="mb-3">
                    <span style="font-size:3rem;">🔒</span>
                </div>
                <h4 class="fw-bold mb-2" style="color:var(--text-dark);">
                    Recurso não disponível no seu plano
                </h4>
                <p class="text-muted mb-1">
                    O <strong>{$recurso}</strong> está disponível nos planos
                    <strong>Construtora</strong> e <strong>Empresarial</strong>.
                </p>
                <p class="text-muted mb-4 small">
                    Seu plano atual: <span class="badge bg-secondary">{$plano}</span>
                </p>
                <a href="{$url}" target="_blank" class="btn btn-warning fw-bold px-4 py-2 me-2" style="color:#003366;">
                    <i class="bi bi-arrow-up-circle-fill me-1"></i> Fazer Upgrade
                </a>
                <a href="{$urlVoltar}" class="btn btn-outline-secondary px-4 py-2">
                    Voltar
                </a>
            </div>
        </div>
        HTML;
    }

    /**
     * Gera um badge colorido com o nome do plano (para usar em tabelas/cabeçalhos).
     */
    public static function htmlBadgePlano(string $planoRaw = ''): string
    {
        $plano = empty($planoRaw) ? self::getPlano() : $planoRaw;
        $cores = [
            'autonomo'    => 'secondary',
            'construtora' => 'primary',
            'empresarial' => 'warning text-dark',
            'mensal'      => 'primary',
        ];
        $label = self::REGRAS[$plano]['label'] ?? ucfirst($plano);
        $cor   = $cores[$plano] ?? 'secondary';
        return "<span class=\"badge bg-{$cor}\">{$label}</span>";
    }

    /** URL da página de planos/upgrade (interna ao sistema). */
    public static function getUrlUpgrade(): string
    {
        return 'index.php?acao=planos';
    }
}