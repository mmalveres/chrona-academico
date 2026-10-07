/* CHRONA — Tour guiado com Driver.js v1.x */

const TOUR_VERSION = 'v3';

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

    // ── Passos por página ──────────────────────────────────────

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
            element: '.feed-card',
            popover: {
                title: '📋 Registro do Dia',
                description: 'Cada card é um diário. Clique nos <strong>três pontinhos</strong> para editar, assinar digitalmente ou gerar o PDF individual.',
                side: 'top',
            }
        },
    ];

    // Seleciona passos pela página atual
    let passos;
    if      (pagina === 'painel')      passos = passosPainel;
    else if (pagina === 'listar_obras') passos = passosObras;
    else if (pagina === 'ver_diario')   passos = passosDiario;
    else                                passos = passosPainel;

    // Remove passos cujo elemento não existe na página atual
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

// ── Inicialização (aguarda a página estar totalmente carregada) ─
window.addEventListener('load', function () {
    // Botão ? na topbar
    const btn = document.getElementById('btnTourAjuda');
    if (btn) {
        btn.addEventListener('click', function() {
            marcarTourVisto(); // reseta para poder rever
            localStorage.removeItem('chrona_tour_' + TOUR_VERSION);
            iniciarTour();
        });
    }

    // Exibe automaticamente no primeiro acesso
    if (!tourJaVisto()) {
        setTimeout(iniciarTour, 1000);
    }
});
