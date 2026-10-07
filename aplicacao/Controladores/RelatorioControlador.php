<?php
// aplicacao/Controladores/RelatorioControlador.php

namespace Aplicacao\Controladores;

use Aplicacao\Modelos\Obra;
use Aplicacao\Configuracao\BancoDeDados;
use Aplicacao\Controladores\Autenticacao;
use PDO;

class RelatorioControlador {

    public function filtro() {
        Autenticacao::verificarLogin();
        
        $id_obra = $_GET['id_obra'] ?? null;
        if (!$id_obra) {
            header('Location: index.php?acao=listar_obras');
            exit;
        }
        
        $obraModel = new Obra();
        $obra = $obraModel->buscarPorId($id_obra);

        require_once __DIR__ . '/../../visoes/relatorios/filtro.php';
    }

    public function gerar() {
        Autenticacao::verificarLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?acao=listar_obras');
            exit;
        }
        
        $id_obra = $_POST['id_obra'];
        $inicio = $_POST['data_inicio'];
        $fim = $_POST['data_fim'];
        $tipo = $_POST['tipo'] ?? 'geral'; 

        $obraModel = new Obra();
        $obra = $obraModel->buscarPorId($id_obra);
        $conn = BancoDeDados::getConexao();

        // --- RELATÓRIO FINANCEIRO ---
        if ($tipo === 'financeiro') {
            $sqlTotais = "SELECT 
                    COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) as total_receitas,
                    COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) as total_despesas
                   FROM financeiro_movimentacoes 
                   WHERE id_obra = :id AND data_movimentacao BETWEEN :inicio AND :fim";
            
            $stmtTotais = $conn->prepare($sqlTotais);
            $stmtTotais->execute([':id' => $id_obra, ':inicio' => $inicio, ':fim' => $fim]);
            $totais = $stmtTotais->fetch(PDO::FETCH_ASSOC);
            $totais['saldo'] = $totais['total_receitas'] - $totais['total_despesas'];

            $sqlLista = "SELECT * FROM financeiro_movimentacoes 
                         WHERE id_obra = :id AND data_movimentacao BETWEEN :inicio AND :fim 
                         ORDER BY data_movimentacao ASC, id ASC";
            
            $stmtLista = $conn->prepare($sqlLista);
            $stmtLista->execute([':id' => $id_obra, ':inicio' => $inicio, ':fim' => $fim]);
            $movimentacoes = $stmtLista->fetchAll(PDO::FETCH_ASSOC);

            require_once __DIR__ . '/../../visoes/relatorios/financeiro.php';
            return; 
        }

        // --- RELATÓRIO GERAL ---
        
        // 1. Dados Financeiros
        $sqlFin = "SELECT 
                    COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) as receitas,
                    COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) as despesas
                   FROM financeiro_movimentacoes 
                   WHERE id_obra = :id AND data_movimentacao BETWEEN :inicio AND :fim";
        $stmtFin = $conn->prepare($sqlFin);
        $stmtFin->execute([':id' => $id_obra, ':inicio' => $inicio, ':fim' => $fim]);
        $financeiro = $stmtFin->fetch();
        $financeiro['saldo'] = $financeiro['receitas'] - $financeiro['despesas'];

        // 2. Buscar Diários
        $sqlDiario = "SELECT * FROM diario_obras 
                      WHERE id_obra = :id AND data_diario BETWEEN :inicio AND :fim 
                      ORDER BY data_diario ASC";
        $stmtDiario = $conn->prepare($sqlDiario);
        $stmtDiario->execute([':id' => $id_obra, ':inicio' => $inicio, ':fim' => $fim]);
        $dados = $stmtDiario->fetchAll(PDO::FETCH_ASSOC);

        // 3. Calcular Estatísticas (REGRA DE PRIORIDADE CORRIGIDA)
        $statsClima = ['Sol' => 0, 'Nublado' => 0, 'Chuva' => 0];
        
        foreach ($dados as $d) {
            $manha = strtolower($d['clima_manha']);
            $tarde = strtolower($d['clima_tarde']);
            
            // Auxiliar para detectar chuva
            $isChuva = function($clima) {
                return (strpos($clima, 'chuva') !== false) || $clima == 'chuvoso' || $clima == 'impraticavel';
            };

            // Regra 1: CHUVA tem prioridade máxima (se choveu em qualquer momento)
            if ($isChuva($manha) || $isChuva($tarde)) {
                $key = 'Chuva';
            } 
            // Regra 2: SOL tem prioridade sobre Nublado 
            // (Se não choveu, mas fez sol em algum momento, conta como dia de Sol)
            elseif ($manha == 'ensolarado' || $tarde == 'ensolarado') {
                $key = 'Sol';
            } 
            // Regra 3: NUBLADO só se o dia ficou fechado o tempo todo (sem sol e sem chuva)
            else {
                $key = 'Nublado';
            }

            if (isset($statsClima[$key])) {
                $statsClima[$key]++;
            }
        }

        require_once __DIR__ . '/../../visoes/relatorios/gerado.php';
    }
}