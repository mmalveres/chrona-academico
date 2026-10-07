<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Manual do Usuário - CHRONA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .accordion-button:not(.collapsed) {
            background-color: #e7f1ff;
            color: #0d6efd;
        }
        .step-number {
            background-color: #0d6efd;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: inline-block;
            text-align: center;
            line-height: 24px;
            font-size: 14px;
            margin-right: 8px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../estruturas/cabecalho.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-primary"><i class="bi bi-book-half"></i> Manual do Sistema CHRONA</h2>
                    <p class="text-muted">Guia completo para gestão de obras, diários e financeiro.</p>
                </div>

                <div class="accordion shadow-sm border-0" id="manualAccordion">
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                <i class="bi bi-buildings me-2"></i> <strong>1. Cadastro e Gestão de Obras</strong>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#manualAccordion">
                            <div class="accordion-body">
                                <p>O cadastro da obra é o primeiro passo para utilizar o sistema.</p>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><span class="step-number">1</span>Vá ao menu <strong>"Gerenciar Obras"</strong> e clique em <strong>"Nova Obra"</strong>.</li>
                                    <li class="mb-2"><span class="step-number">2</span>Preencha os dados básicos (Nome, Endereço).</li>
                                    <li class="mb-2"><span class="step-number">3</span>Defina o <strong>Orçamento Previsto</strong> (importante para o módulo financeiro).</li>
                                    <li class="mb-2"><span class="step-number">4</span>Selecione o <strong>Responsável Técnico</strong> (Engenheiro/Arquiteto).</li>
                                </ul>
                                <div class="alert alert-info py-2 small">
                                    <i class="bi bi-info-circle-fill"></i> <strong>Dica:</strong> O status da obra muda automaticamente. Se precisar encerrar, edite a obra e mude o status para "Concluída".
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                <i class="bi bi-journal-text me-2"></i> <strong>2. Relatório Diário de Obra (RDO)</strong>
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                            <div class="accordion-body">
                                <p>O preenchimento do RDO é dividido em etapas para facilitar a organização no canteiro:</p>
                                <ul>
                                    <li><strong>Clima:</strong> Registre as condições da manhã e tarde (Sol, Chuva, Nublado).</li>
                                    <li><strong>Mão de Obra:</strong> Adicione os colaboradores (Próprios ou Terceirizados) e as quantidades.</li>
                                    <li><strong>Equipamentos:</strong> Liste maquinários e ferramentas utilizadas no dia (Betoneiras, Caminhões, etc).</li>
                                    <li><strong>Atividades:</strong> Descreva o que foi executado e o status (Em andamento, Concluído).</li>
                                    <li><strong>Fotos:</strong> Anexe fotos do progresso. No celular, você pode tirar a foto na hora.</li>
                                </ul>
                                <div class="alert alert-warning py-2 small">
                                    <i class="bi bi-pen-fill"></i> <strong>Assinatura:</strong> O diário fica como "Rascunho" até você clicar em <strong>"Assinar Digitalmente"</strong>. Após assinado, ele não pode ser mais editado (apenas reaberto pelo admin).
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                <i class="bi bi-cash-stack me-2"></i> <strong>3. Controle Financeiro</strong>
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                            <div class="accordion-body">
                                <p>Controle as entradas e saídas de cada obra individualmente.</p>
                                <ol>
                                    <li>Na lista de obras, clique no botão verde <i class="bi bi-cash-stack"></i> (Financeiro).</li>
                                    <li>Clique em <strong>"Nova Movimentação"</strong>.</li>
                                    <li>Escolha o tipo:
                                        <ul>
                                            <li><span class="text-success fw-bold">Receita:</span> Pagamentos recebidos do cliente (Medições).</li>
                                            <li><span class="text-danger fw-bold">Despesa:</span> Gastos com material, mão de obra, etc.</li>
                                        </ul>
                                    </li>
                                    <li>O sistema calcula automaticamente o <strong>Saldo Atual</strong> da obra.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                <i class="bi bi-calendar-range me-2"></i> <strong>4. Cronograma e Documentos</strong>
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="bi bi-calendar3"></i> Cronograma Físico</h6>
                                        <p class="small text-muted">Cadastre as etapas da obra (ex: Fundação, Alvenaria) e defina as datas de início e fim para acompanhar os prazos.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="bi bi-folder2-open"></i> Gestão de Documentos</h6>
                                        <p class="small text-muted">Armazene projetos (PDF), alvarás, contratos e plantas diretamente na obra para acesso rápido da equipe.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                                <i class="bi bi-printer me-2"></i> <strong>5. Relatórios e Impressão</strong>
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                            <div class="accordion-body">
                                <p>O CHRONA oferece diferentes tipos de relatórios para impressão ou PDF:</p>
                                <ul>
                                    <li><strong>Impressão de RDO Individual:</strong> Dentro do diário, clique no botão de imprimir para gerar o relatório daquele dia específico.</li>
                                    <li><strong>Relatório Geral (Período):</strong> Vá na lista de obras > Botão Preto (Relatórios). Escolha "Geral" para ver um compilado dos diários, clima e atividades.</li>
                                    <li><strong>Relatório Financeiro:</strong> Na mesma tela de relatórios, escolha "Financeiro" para ver o balanço de receitas e despesas do período.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix">
                                <i class="bi bi-people me-2"></i> <strong>6. Gestão de Equipe</strong>
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                            <div class="accordion-body">
                                <p>Gerencie quem tem acesso ao sistema:</p>
                                <ul>
                                    <li><strong>Admin:</strong> Acesso total (Criar usuários, apagar obras, ver financeiro).</li>
                                    <li><strong>Engenheiro/Técnico:</strong> Cria obras, preenche diários, mas não vê o financeiro (opcional).</li>
                                    <li><strong>Visitante/Cliente:</strong> Acesso apenas de leitura para acompanhar a obra.</li>
                                </ul>
                                <p class="small">Para adicionar alguém, vá em "Ações Rápidas" no painel > "Gerenciar Equipes".</p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="text-center mt-5 p-4 bg-white rounded border shadow-sm">
                    <h5 class="fw-bold">Ainda com dúvidas?</h5>
                    <p class="text-muted mb-3">Nossa equipe de suporte está pronta para ajudar.</p>
                    <a href="mailto:suporte@chrona.com.br" class="btn btn-outline-primary px-4">
                        <i class="bi bi-envelope-fill me-2"></i> Enviar E-mail para o Suporte
                    </a>
                </div>

            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../estruturas/rodape.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>