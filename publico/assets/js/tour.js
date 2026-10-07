/* CHRONA — Tour guiado com Driver.js v1.x */

const TOUR_VERSION = 'v12';

function tourJaVisto() {
    try { return localStorage.getItem('chrona_tour_' + TOUR_VERSION) === '1'; }
    catch(e) { return false; }
}

function marcarTourVisto() {
    try { localStorage.setItem('chrona_tour_' + TOUR_VERSION, '1'); }
    catch(e) {}
}

function obterDriverFn() {
    // Driver.js v1.x IIFE expõe window.driver.js.driver
    if (window.driver && window.driver.js && typeof window.driver.js.driver === 'function') {
        return window.driver.js.driver;
    }
    // Fallback para outros formatos de exportação
    if (window.driver && typeof window.driver === 'function') {
        return window.driver;
    }
    if (window.driver && typeof window.driver.driver === 'function') {
        return window.driver.driver;
    }
    return null;
}

function iniciarTour() {
    const driverFn = obterDriverFn();
    if (!driverFn) {
        console.warn('CHRONA Tour: Driver.js não carregou ainda.');
        return;
    }

    const pagina = document.body.getAttribute('data-pagina') || '';

    const passosPainel = [
        {
            popover: {
                title: '👋 Bem-vindo ao CHRONA!',
                description: 'Este é seu painel de controle. Vamos fazer um tour rápido para você conhecer o sistema. Use as setas para navegar.',
            }
        },
        {
            element: '#sidebar-wrapper',
            popover: {
                title: '📋 Menu de Navegação',
                description: 'Aqui ficam os módulos: <strong>Obras, Equipe, Financeiro e Configurações</strong>. No celular o menu aparece na parte inferior da tela.',
                side: 'right', align: 'start',
            }
        },
        {
            element: 'a[href*="listar_obras"]',
            popover: {
                title: '🏗️ Obras',
                description: 'Acesse todas as suas obras aqui. Cada obra tem Diário, Cronograma, Medição Física e Documentos.',
                side: 'right',
            }
        },
        {
            element: '.topbar',
            popover: {
                title: '🔔 Barra Superior',
                description: 'Veja seu plano atual e acesse configurações de conta. O botão <strong>?</strong> reabre este tour quando quiser.',
                side: 'bottom',
            }
        },
        {
            popover: {
                title: '✅ Pronto!',
                description: 'Comece cadastrando uma obra em <strong>Obras → Nova Obra</strong>.<br>Clique em <strong>?</strong> sempre que precisar rever o tour.',
            }
        },
    ];

    const passosObras = [
        {
            popover: {
                title: '🏗️ Suas Obras',
                description: 'Aqui ficam listadas todas as suas obras. Veja como usar as funcionalidades.',
            }
        },
        {
            element: '#campoBuscaObras',
            popover: {
                title: '🔍 Busca Rápida',
                description: 'Digite o nome ou cidade para filtrar as obras na hora.',
                side: 'bottom',
            }
        },
        {
            element: 'table tbody tr:first-child td:last-child, .card-obra-mobile:first-child',
            popover: {
                title: '📝 Ações da Obra',
                description: 'Clique em <strong>Diário</strong> para registrar o dia a dia da obra. Em <strong>Mais Opções</strong> estão Cronograma, Medição, Documentos e Importar Orçamento.',
                side: 'left',
            }
        },
        {
            element: '[data-bs-target="#modalImportarOrcamento"]',
            popover: {
                title: '📊 Importar Orçamento',
                description: 'Importe seu orçamento em <strong>Excel (.xlsx)</strong> ou CSV. Os itens são organizados automaticamente por hierarquia.',
                side: 'left',
            }
        },
    ];

    const passosDiario = [
        {
            popover: {
                title: '📓 Diário de Obras (RDO)',
                description: 'Cada registro representa um dia de trabalho com fotos, atividades, mão de obra e equipamentos.',
            }
        },
        {
            element: 'a[href*="preencher_diario"], .fab-add',
            popover: {
                title: '✏️ Novo Registro',
                description: 'Clique aqui para lançar o diário de hoje: clima, atividades, fotos e mão de obra.',
                side: 'left',
            }
        },
        {
            element: '[data-bs-target="#modalImprimirLote"]',
            popover: {
                title: '📄 Relatório PDF',
                description: 'Gere um PDF com todos os diários de um período — ideal para enviar ao cliente ou fiscal.',
                side: 'bottom',
            }
        },
        {
            element: 'a[href*="acao=assinar_lote"]',
            popover: {
                title: '✍️ Assinar Todos',
                description: 'Assine de uma vez todos os diários em aberto desta obra, usando a mesma assinatura para todos.',
                side: 'bottom',
            }
        },
        {
            element: '.feed-card',
            popover: {
                title: '📋 Registro do Dia',
                description: 'Cada card é um diário. Clique nos <strong>três pontinhos</strong> para editar, assinar digitalmente ou gerar o PDF individual.',
                side: 'top',
            }
        },
    ];

    const passosCronograma = [
        {
            popover: {
                title: '📅 Cronograma Físico-Financeiro',
                description: 'Aqui você define as datas de execução de cada etapa da obra e acompanha a distribuição financeira ao longo do tempo.',
            }
        },
        {
            element: 'table',
            popover: {
                title: '📋 Etapas e Sub-etapas',
                description: 'As linhas em cinza claro são as <strong>etapas raiz</strong> (1, 2, 3…). Abaixo de cada uma ficam as sub-etapas (1.1, 1.2…), que possuem campos de data individuais.',
                side: 'top',
            }
        },
        {
            element: '.js-inicio',
            popover: {
                title: '📆 Datas de Execução',
                description: 'Defina o <strong>início</strong> e o <strong>fim</strong> de cada sub-etapa. A duração em dias é calculada automaticamente.',
                side: 'top',
            }
        },
        {
            element: '[data-bs-target="#modalAutoPreencher"]',
            popover: {
                title: '✨ Preenchimento Automático',
                description: 'Distribui as datas automaticamente entre todas as etapas. O modo <strong>Por Custo</strong> aloca mais dias para as etapas de maior valor financeiro.',
                side: 'bottom',
            }
        },
        {
            element: 'a[href*="imprimir_cronograma"]',
            popover: {
                title: '🖨️ PDF do Cronograma',
                description: 'Gera o <strong>Cronograma Físico-Financeiro</strong> com barras de Gantt, distribuição mensal de valores, totais e % acumulado — pronto para assinar.',
                side: 'bottom',
            }
        },
        {
            element: 'button[type="submit"]',
            popover: {
                title: '💾 Salvar',
                description: 'Salva todas as datas definidas. Elas ficam vinculadas à obra e são usadas no PDF e nos relatórios.',
                side: 'bottom',
            }
        },
    ];

    const passosFinanceiro = [
        {
            popover: {
                title: '💰 Fluxo de Caixa',
                description: 'Aqui você controla todas as entradas e saídas financeiras da obra — receitas, despesas, comprovantes e saldo em tempo real.',
            }
        },
        {
            element: 'form[action="index.php"] .row',
            popover: {
                title: '🔍 Filtro por Período',
                description: 'Filtre os lançamentos por <strong>data, tipo</strong> (receita ou despesa) e <strong>status</strong> (pago ou pendente). Os cards de resumo também se atualizam com o filtro.',
                side: 'bottom',
            }
        },
        {
            element: '.card.border-start.border-success',
            popover: {
                title: '📊 Cards de Resumo',
                description: 'Veja o total de <strong>Receitas</strong>, <strong>Despesas</strong>, valores <strong>Pendentes</strong> e o <strong>Saldo</strong> atual da obra — sempre atualizados conforme o filtro.',
                side: 'bottom',
            }
        },
        {
            element: '.card-alerta',
            popover: {
                title: '⚠️ Alertas de Faturamento',
                description: 'Etapas do cronograma com prazo vencido mas sem receita lançada aparecem aqui. Clique em <strong>+</strong> para registrar o recebimento diretamente.',
                side: 'bottom',
            }
        },
        {
            element: '#graficoMensal',
            popover: {
                title: '📈 Gráfico Mensal',
                description: 'Visualize a evolução de receitas e despesas dos últimos 12 meses. Útil para identificar meses de maior custo ou queda de faturamento.',
                side: 'top',
            }
        },
        {
            element: 'table',
            popover: {
                title: '📋 Extrato de Lançamentos',
                description: 'Todos os lançamentos filtrados aparecem aqui com data, descrição, categoria, valor e status.',
                side: 'top',
            }
        },
        {
            element: '.btn-editar',
            popover: {
                title: '✏️ Editar Lançamento',
                description: 'Clique no <strong>lápis</strong> para corrigir qualquer dado de um lançamento — valor, data, categoria ou status.',
                side: 'left',
            }
        },
        {
            element: '[data-bs-target="#modalLancamento"]',
            popover: {
                title: '➕ Novo Lançamento',
                description: 'Registre uma receita ou despesa informando tipo, valor, data, categoria e status. Você pode anexar o <strong>comprovante</strong> e vincular ao item do orçamento.',
                side: 'bottom',
            }
        },
    ];

    const passosCurvaS = [
        {
            popover: {
                title: '📈 Curva S — Planejado × Realizado',
                description: 'A Curva S compara o que foi <strong>planejado no cronograma</strong> com o que foi <strong>efetivamente executado</strong> nos diários de obra, mês a mês.',
            }
        },
        {
            element: '.border-start.border-primary',
            popover: {
                title: '🔵 % Planejado',
                description: 'Percentual do contrato que deveria estar executado até hoje, conforme o cronograma definido.',
                side: 'bottom',
            }
        },
        {
            element: '.border-start.border-success',
            popover: {
                title: '🟢 % Realizado',
                description: 'Percentual efetivamente executado, calculado a partir das quantidades lançadas nos diários de obra.',
                side: 'bottom',
            }
        },
        {
            element: '.border-start.border-danger, .border-start.border-success:last-child',
            popover: {
                title: '⚡ Desvio',
                description: '<strong>Verde</strong> = obra adiantada em relação ao plano. <strong>Vermelho</strong> = obra atrasada. O desvio é a diferença entre realizado e planejado.',
                side: 'bottom',
            }
        },
        {
            element: '#graficoCurvaS',
            popover: {
                title: '📊 Gráfico da Curva S',
                description: 'A linha <strong>azul tracejada</strong> é a curva planejada. A linha <strong>verde sólida</strong> é a realizada. Quando o verde está acima do azul, a obra está adiantada.',
                side: 'top',
            }
        },
        {
            element: 'table',
            popover: {
                title: '📋 Detalhamento Mensal',
                description: 'Tabela com o valor planejado e realizado de cada mês, percentual acumulado e o desvio linha a linha. O mês atual é destacado em azul.',
                side: 'top',
            }
        },
    ];

    const passosAlmoxarifado = [
        {
            popover: {
                title: '📦 Almoxarifado',
                description: 'Controle a entrada e saída de materiais em obra. Veja o saldo atual de cada item e rastreie o consumo por serviço.',
            }
        },
        {
            element: '#secaoEstoque',
            popover: {
                title: '🗄️ Estoque Atual',
                description: 'Tabela com o saldo de cada material: entradas menos saídas. Itens em <strong>vermelho</strong> estão esgotados ou negativos — atenção!',
                side: 'top',
            }
        },
        {
            element: '[data-bs-target="#modalEntrada"]',
            popover: {
                title: '🟢 Registrar Entrada',
                description: 'Registre a chegada de material na obra informando material, unidade, quantidade, preço, fornecedor e nota fiscal.',
                side: 'bottom',
            }
        },
        {
            element: '[data-bs-target="#modalSaida"]',
            popover: {
                title: '🔴 Registrar Saída',
                description: 'Registre o consumo de material. Você pode vincular a saída ao item do orçamento para rastrear quanto de cada material foi usado em cada serviço.',
                side: 'bottom',
            }
        },
        {
            element: '#secaoMovimentacoes',
            popover: {
                title: '📋 Movimentações',
                description: 'Histórico completo de entradas e saídas com data, quantidade, fornecedor e serviço vinculado. Use os filtros acima para buscar por período ou material.',
                side: 'top',
            }
        },
    ];

    const passosDDS = [
        {
            popover: {
                title: '🛡️ DDS — Diálogo Diário de Segurança',
                description: 'Registre os temas de segurança discutidos com a equipe a cada dia de trabalho. O DDS é obrigatório pela <strong>NR-18</strong> e comprova a ação preventiva da obra.',
            }
        },
        {
            element: '#listaDDS',
            popover: {
                title: '📋 Registros de DDS',
                description: 'Cada card é um DDS realizado. Veja a data, o tema e quantos trabalhadores assinaram a lista de presença.',
                side: 'top',
            }
        },
        {
            element: '[data-bs-target="#modalNovoDDS"]',
            popover: {
                title: '➕ Novo DDS',
                description: 'Registre a data, o tema abordado, o conteúdo e adicione todos os presentes na lista de presença. Você pode imprimir a lista depois.',
                side: 'bottom',
            }
        },
    ];

    const passosNC = [
        {
            popover: {
                title: '⚠️ Não Conformidades',
                description: 'Registre e acompanhe problemas de qualidade encontrados na obra. Cada NC tem prioridade, responsável e prazo para resolução.',
            }
        },
        {
            element: '#listaNCs',
            popover: {
                title: '📋 Lista de NCs',
                description: 'As NCs são ordenadas por prioridade (Alta → Média → Baixa). Veja o status de cada uma: <strong>Aberta</strong>, <strong>Em Tratamento</strong> ou <strong>Fechada</strong>.',
                side: 'top',
            }
        },
        {
            element: '.btn-outline-warning, .btn-outline-success',
            popover: {
                title: '🔄 Avançar Status',
                description: 'Engenheiro e Admin podem avançar o status da NC: Aberta → Em Tratamento → Fechada. Isso cria um histórico de acompanhamento.',
                side: 'left',
            }
        },
        {
            element: '[data-bs-target="#modalNovaNC"]',
            popover: {
                title: '➕ Registrar NC',
                description: 'Informe o título, local, prioridade, responsável, prazo e adicione fotos da ocorrência. Qualquer membro da equipe de campo pode registrar.',
                side: 'bottom',
            }
        },
    ];

    const passosLogs = [
        {
            popover: {
                title: '📋 Logs do Sistema',
                description: 'Esta página registra todas as ações realizadas no sistema: logins, criação e exclusão de obras, edições de usuários e muito mais.',
            }
        },
        {
            element: 'form.row',
            popover: {
                title: '🔍 Filtros',
                description: 'Filtre os registros por <strong>empresa</strong> ou por <strong>palavra-chave</strong> (ação, usuário ou detalhe). Útil para auditar ações específicas.',
                side: 'bottom',
            }
        },
        {
            element: '.table-responsive',
            popover: {
                title: '📊 Tabela de Logs',
                description: 'Cada linha mostra: data/hora, nome do usuário, empresa, a ação realizada, detalhes adicionais e o IP de onde a ação partiu.',
                side: 'top',
            }
        },
        {
            element: 'nav .pagination',
            popover: {
                title: '📄 Paginação',
                description: 'Os registros são exibidos em páginas de 50 por vez. Use a paginação para navegar pelo histórico completo.',
                side: 'top',
            }
        },
    ];

    const passosEmpresas = [
        {
            popover: {
                title: '🏢 Painel Super Admin',
                description: 'Aqui você gerencia todas as empresas clientes do CHRONA: planos, vencimento, acesso e usuários.',
            }
        },
        {
            element: 'button[data-bs-target="#modalTesteGratis"]',
            popover: {
                title: '🎁 Enviar Teste Grátis',
                description: 'Envia um e-mail de boas-vindas convidando a empresa a testar o CHRONA gratuitamente por 30 dias. Informe o nome do contato e o e-mail de destino.',
                side: 'left',
            }
        },
    ];

    const passosRelatorioFiscal = [
        {
            popover: {
                title: '📋 Relatórios de Visita Fiscal',
                description: 'Aqui ficam registrados todos os relatórios de visita de fiscalização da obra — emitidos pelo fiscal do órgão externo (ex: Aeronáutica).',
            }
        },
        {
            element: 'a[href*="relatorio_fiscal_novo"]',
            popover: {
                title: '➕ Novo Relatório',
                description: 'Clique aqui para registrar uma nova visita de fiscalização. Informe a obra inspecionada, a data, o órgão e o resultado da inspeção.',
                side: 'left',
            }
        },
        {
            element: '.table-responsive, .card-custom',
            popover: {
                title: '📊 Lista de Relatórios',
                description: 'Cada linha mostra a data, a obra inspecionada, o órgão fiscalizador e o resultado (Conforme ou Não Conforme). Clique no ícone <strong>👁</strong> para visualizar ou em 🖨 para imprimir o relatório em PDF.',
                side: 'top',
            }
        },
    ];

    let passos;
    if      (pagina === 'painel')                                    passos = passosPainel;
    else if (pagina === 'listar_obras')                              passos = passosObras;
    else if (pagina === 'ver_diario')                                passos = passosDiario;
    else if (pagina === 'cronograma' || pagina === 'ver_cronograma') passos = passosCronograma;
    else if (pagina === 'financeiro')                                passos = passosFinanceiro;
    else if (pagina === 'curva_s')                                   passos = passosCurvaS;
    else if (pagina === 'almoxarifado')                              passos = passosAlmoxarifado;
    else if (pagina === 'dds')                                       passos = passosDDS;
    else if (pagina === 'nao_conformidades')                         passos = passosNC;
    else if (pagina === 'logs_sistema')                              passos = passosLogs;
    else if (pagina === 'listar_empresas')                           passos = passosEmpresas;
    else if (pagina === 'relatorio_fiscal')                          passos = passosRelatorioFiscal;
    else                                                             passos = passosPainel;

    passos = passos.filter(p => !p.element || !!document.querySelector(p.element));

    const drv = driverFn({
        animate: true,
        showProgress: true,
        showButtons: ['next', 'previous', 'close'],
        nextBtnText: 'Próximo →',
        prevBtnText: '← Voltar',
        doneBtnText: '✓ Entendi!',
        progressText: '{{current}} de {{total}}',
        onDestroyed: () => marcarTourVisto(),
        steps: passos,
    });

    drv.drive();
}

window.addEventListener('load', function () {
    const btn = document.getElementById('btnTourAjuda');
    if (btn) {
        btn.addEventListener('click', function() {
            localStorage.removeItem('chrona_tour_' + TOUR_VERSION);
            iniciarTour();
        });
    }

    if (!tourJaVisto()) {
        setTimeout(iniciarTour, 1000);
    }
});
