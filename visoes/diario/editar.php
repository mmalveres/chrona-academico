<?php 
// visoes/diario/editar.php
include __DIR__ . '/../estruturas/cabecalho.php'; 
?>

<style>
    .upload-area { border: 2px dashed #cbd5e1; border-radius: 16px; background-color: #f8f9fa; transition: all 0.2s; cursor: pointer; text-align: center; padding: 30px; }
    .upload-area:hover { background-color: #eef2ff; border-color: var(--primary-color); color: var(--primary-color); }
    .upload-area.processing { pointer-events: none; opacity: 0.6; cursor: wait; }
    .upload-area.dragover { background-color: #eef2ff; border-color: var(--primary-color); border-style: solid; }
    
    .preview-container { position: relative; width: 100%; padding-top: 100%; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; background: #fff; }
    .preview-img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
    .btn-remove-img { position: absolute; top: 5px; right: 5px; background: rgba(255,255,255,0.9); color: #dc3545; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 10; }

    /* Estilo Autocomplete */
    .input-wrapper { position: relative; }
    .autocomplete-items {
        position: absolute; border: 1px solid #d4d4d4; border-bottom: none; border-top: none; z-index: 99;
        top: 100%; left: 0; right: 0; max-height: 250px; overflow-y: auto; background-color: #fff;
        border-radius: 0 0 8px 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .autocomplete-items div { padding: 10px; cursor: pointer; background-color: #fff; border-bottom: 1px solid #d4d4d4; font-size: 0.9rem; }
    .autocomplete-items div:hover { background-color: #f0f7ff; color: var(--primary-color); }
</style>

<div id="loadingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-90 d-flex flex-column justify-content-center align-items-center" style="z-index: 10000; backdrop-filter: blur(5px);">
    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
    <h5 class="fw-bold text-dark">Salvando Alterações...</h5>
    <p class="text-muted small">Processando dados e fotos...</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="index.php?acao=ver_diario&id_obra=<?= $obra['id']; ?>" class="text-decoration-none text-muted">Voltar</a></li>
                <li class="breadcrumb-item active fw-bold text-primary">Editar Registro #<?= $diario['id']; ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0" style="color: var(--primary-color);">Editar RDO</h4>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <form id="formDiario" action="index.php?acao=atualizar_diario&id=<?= $diario['id']; ?>" method="POST" enctype="multipart/form-data">
            
            <div class="card-custom mb-4">
                <h6 class="fw-bold text-primary mb-3 text-uppercase small"><i class="bi bi-calendar-event me-2"></i> Dados Gerais</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">Data</label>
                        <input type="date" name="data_diario" class="form-control py-2" value="<?= $diario['data_diario']; ?>" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-bold text-muted">Clima Manhã</label>
                        <select name="clima_manha" class="form-select py-2">
                            <option value="ensolarado" <?= $diario['clima_manha']=='ensolarado'?'selected':''; ?>>☀️ Ensolarado</option>
                            <option value="nublado" <?= $diario['clima_manha']=='nublado'?'selected':''; ?>>⛅ Nublado</option>
                            <option value="chuva_leve" <?= $diario['clima_manha']=='chuva_leve'?'selected':''; ?>>🌧️ Chuva Leve</option>
                            <option value="chuva_forte" <?= $diario['clima_manha']=='chuva_forte'?'selected':''; ?>>⛈️ Chuva Forte</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-bold text-muted">Clima Tarde</label>
                        <select name="clima_tarde" class="form-select py-2">
                            <option value="ensolarado" <?= $diario['clima_tarde']=='ensolarado'?'selected':''; ?>>☀️ Ensolarado</option>
                            <option value="nublado" <?= $diario['clima_tarde']=='nublado'?'selected':''; ?>>⛅ Nublado</option>
                            <option value="chuva_leve" <?= $diario['clima_tarde']=='chuva_leve'?'selected':''; ?>>🌧️ Chuva Leve</option>
                            <option value="chuva_forte" <?= $diario['clima_tarde']=='chuva_forte'?'selected':''; ?>>⛈️ Chuva Forte</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-custom mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-primary mb-0 text-uppercase small"><i class="bi bi-list-check me-2"></i> Atividades</h6>
                    <button type="button" class="btn btn-sm btn-success fw-bold" onclick="addAtividadeRow()"><i class="bi bi-plus-circle me-1"></i> Adicionar</button>
                </div>
                
                <div class="table-responsive" style="overflow: visible;">
                    <table class="table table-hover align-middle table-responsive-custom" id="tabelaAtividades">
                        <thead class="table-light">
                            <tr>
                                <th width="50%">Serviço / Atividade</th>
                                <th width="15%">Qtd.</th>
                                <th width="30%">Obs / Descrição Manual</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="listaAtividadesBody">
                            <?php 
                            // PREENCHE ATIVIDADES EXISTENTES
                            $contadorJS = 0;
                            if (!empty($listaAtividades)): foreach($listaAtividades as $ativ): 
                                $isManual = empty($ativ['id_item_orcamento']);
                                $idRow = "row_ativ_" . $contadorJS;
                                
                                // Se for item de orçamento, prepara o texto pro input
                                $textoInput = "";
                                if(!$isManual) {
                                    // Busca dados do item no array original para exibir o nome
                                    foreach($itensOrcamento as $io) {
                                        if($io['id'] == $ativ['id_item_orcamento']) {
                                            $cod = $io['itemizacao'] ?? $io['codigo_referencia'];
                                            $saldo = $io['quantidade_total'] - ($io['quantidade_acumulada'] ?? 0);
                                            // Adiciona a quantidade atual ao saldo, pois estamos editando
                                            $saldoReal = $saldo + $ativ['quantidade'];
                                            
                                            $textoInput = "[{$cod}] " . mb_strimwidth($io['descricao'], 0, 60, "...") . " (Saldo: " . number_format($saldoReal, 2, ',', '.') . " {$io['unidade']})";
                                            break;
                                        }
                                    }
                                }
                            ?>
                            <tr id="<?= $idRow; ?>">
                                <td style="vertical-align:top; position: relative;">
                                    <div class="d-flex gap-2 mb-1">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="tipo_ativ_<?= $contadorJS; ?>" id="rb_orc_<?= $contadorJS; ?>" value="orcamento" <?= !$isManual ? 'checked' : ''; ?> onchange="toggleInputAtividade(<?= $contadorJS; ?>)">
                                            <label class="form-check-label small" for="rb_orc_<?= $contadorJS; ?>">Orçamento</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="tipo_ativ_<?= $contadorJS; ?>" id="rb_man_<?= $contadorJS; ?>" value="manual" <?= $isManual ? 'checked' : ''; ?> onchange="toggleInputAtividade(<?= $contadorJS; ?>)">
                                            <label class="form-check-label small text-primary" for="rb_man_<?= $contadorJS; ?>">Manual</label>
                                        </div>
                                    </div>
                                    
                                    <div id="box_select_<?= $contadorJS; ?>" class="input-wrapper" style="display: <?= $isManual ? 'none' : 'block'; ?>;">
                                        <input type="text" class="form-control form-control-sm fw-bold border-primary" 
                                               id="input_busca_<?= $contadorJS; ?>" 
                                               placeholder="Digite para buscar..." 
                                               oninput="filtrarAutocomplete(this, <?= $contadorJS; ?>)"
                                               autocomplete="off"
                                               value="<?= htmlspecialchars($textoInput); ?>">
                                        
                                        <input type="hidden" name="atividades[id_item_orcamento][]" id="hidden_id_<?= $contadorJS; ?>" class="select-servico" value="<?= $ativ['id_item_orcamento']; ?>">
                                        
                                        <div id="lista_busca_<?= $contadorJS; ?>" class="autocomplete-items"></div>
                                        <div class="alerta-saldo small text-danger mt-1" style="display:none"></div>
                                    </div>

                                    <div id="box_manual_<?= $contadorJS; ?>" style="display: <?= $isManual ? 'block' : 'none'; ?>;">
                                        <input type="text" name="atividades[descricao_manual][]" class="form-control form-control-sm fw-bold border-warning" placeholder="Nome da atividade..." value="<?= htmlspecialchars($ativ['descricao_manual'] ?? ''); ?>">
                                    </div>
                                </td>

                                <td style="vertical-align:top">
                                    <input type="number" name="atividades[quantidade][]" class="form-control fw-bold input-qtd" step="0.01" value="<?= $ativ['quantidade']; ?>" oninput="verificarSaldo(this)" required>
                                </td>
                                
                                <td style="vertical-align:top">
                                    <input type="text" name="atividades[descricao][]" class="form-control form-control-sm" placeholder="Obs" value="<?= htmlspecialchars($ativ['descricao'] ?? ''); ?>">
                                </td>
                                
                                <td style="vertical-align:top">
                                    <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-trash-fill"></i></button>
                                </td>
                            </tr>
                            <?php $contadorJS++; endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <div id="msgVazioAtividades" class="text-center text-muted py-4 small fst-italic border rounded bg-light" style="<?= !empty($listaAtividades) ? 'display:none' : '' ?>">Clique em "Adicionar" para inserir atividades.</div>
            </div>

            <div class="card-custom mb-4">
                <h6 class="fw-bold text-primary mb-3 text-uppercase small"><i class="bi bi-people-fill me-2"></i> Mão de Obra</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Profissionais</span>
                    <button type="button" class="btn btn-sm btn-light border text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalMaoObra"><i class="bi bi-plus-lg"></i> Selecionar</button>
                </div>
                <div id="containerMaoObra" class="row g-2 mb-2">
                    <?php if (!empty($maoObra)): foreach ($maoObra as $mo): $id=uniqid(); ?>
                        <div class="col-12 col-md-6 col-lg-4" id="mo_<?= $id; ?>">
                            <div class="bg-white border rounded p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="fw-bold"><?= $mo['funcao']; ?></div>
                                    <button type="button" class="btn-close small" onclick="document.getElementById('mo_<?= $id; ?>').remove()"></button>
                                </div>
                                <input type="hidden" name="mao_obra[funcao][]" value="<?= $mo['funcao']; ?>">
                                <input type="hidden" name="mao_obra[tipo][]" value="<?= $mo['tipo']??'direta'; ?>">
                                <div class="input-group input-group-sm">
                                    <button type="button" class="btn btn-light border" onclick="alterarQtd(this, -1)">-</button>
                                    <input type="number" name="mao_obra[quantidade][]" class="form-control text-center fw-bold" value="<?= $mo['quantidade']; ?>">
                                    <button type="button" class="btn btn-light border" onclick="alterarQtd(this, 1)">+</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="card-custom mb-4">
                <h6 class="fw-bold text-primary mb-3 text-uppercase small"><i class="bi bi-truck me-2"></i> Equipamentos</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Maquinário</span>
                    <button type="button" class="btn btn-sm btn-light border text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalEquipamentos"><i class="bi bi-plus-lg"></i> Selecionar</button>
                </div>
                <div id="containerEquipamentos" class="row g-2">
                    <?php if (!empty($equipamentos)): foreach ($equipamentos as $eq): $id=uniqid(); $nome = $eq['nome'] ?? $eq['nome_equipamento']; ?>
                        <div class="col-12 col-md-6 col-lg-4" id="eq_<?= $id; ?>">
                            <div class="bg-white border rounded p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="fw-bold"><?= $nome; ?></div>
                                    <button type="button" class="btn-close small" onclick="document.getElementById('eq_<?= $id; ?>').remove()"></button>
                                </div>
                                <input type="hidden" name="equipamentos[nome][]" value="<?= $nome; ?>">
                                <div class="input-group input-group-sm">
                                    <button type="button" class="btn btn-light border" onclick="alterarQtd(this, -1)">-</button>
                                    <input type="number" name="equipamentos[quantidade][]" class="form-control text-center fw-bold" value="<?= $eq['quantidade']; ?>">
                                    <button type="button" class="btn btn-light border" onclick="alterarQtd(this, 1)">+</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="card-custom mb-4">
                <h6 class="fw-bold text-primary mb-3 text-uppercase small"><i class="bi bi-camera-fill me-2"></i> Fotos</h6>
                
                <div id="galeriaFotosAntigas" class="row g-2 mb-3">
                    <?php if (!empty($fotos)): foreach($fotos as $foto): ?>
                        <div class="col-4 col-sm-3 position-relative">
                            <div class="preview-container">
                                <img src="uploads/<?= $foto['caminho_arquivo']; ?>" class="preview-img">
                                <a href="index.php?acao=excluir_foto&id_foto=<?= $foto['id']; ?>&id_diario=<?= $diario['id']; ?>" class="btn-remove-img" onclick="return confirm('Excluir esta foto permanentemente?')"><i class="bi bi-trash"></i></a>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>

                <div class="mb-4">
                    <div id="uploadArea" class="upload-area" onclick="document.getElementById('inputFotos').click()">
                        <i class="bi bi-cloud-arrow-up-fill fs-1 text-primary opacity-50" id="iconUpload"></i>
                        <div id="textUpload" class="fw-bold text-dark mt-2">Adicionar novas fotos<br><small class="fw-normal text-muted">Toque para escolher ou arraste os arquivos aqui</small></div>
                        <div id="statusUpload" class="text-muted small d-none"><span class="spinner-border spinner-border-sm me-2"></span>Otimizando...</div>
                    </div>
                    <input type="file" id="inputFotos" name="fotos[]" class="d-none" multiple accept="image/*">
                </div>
                
                <div id="galeriaFotos" class="row g-2 mb-3"></div>
                
                <div class="mt-3">
                    <label class="form-label small fw-bold text-muted">Observações Gerais / Ocorrências</label>
                    <textarea name="observacoes" class="form-control" rows="3"><?= htmlspecialchars($diario['observacoes_gerais'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
                <a href="index.php?acao=ver_diario&id_obra=<?= $obra['id']; ?>" class="btn btn-light btn-lg fw-bold border">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow-sm" id="btnSalvar">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalMaoObra" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header"><h5 class="modal-title fw-bold">Mão de Obra</h5><button type="button" class="btn-close" id="closeModalMaoObraBtn" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3 bg-light border-0" placeholder="Buscar..." onkeyup="filtrarLista('listaMO', this.value)">
                <div class="mb-3"><select class="form-select form-select-sm" id="tipoPadraoMO"><option value="direta">Própria</option><option value="indireta">Empreiteira</option></select></div>
                <div class="list-group list-group-flush" id="listaMO">
                    <?php if(isset($listaMaoObra)): foreach($listaMaoObra as $mo): ?>
                        <label class="list-group-item px-0 border-bottom"><input class="form-check-input me-2 chk-mo" type="checkbox" value="<?= htmlspecialchars($mo['nome']); ?>"> <span class="fw-bold text-dark"><?= htmlspecialchars($mo['nome']); ?></span></label>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="modal-footer border-top-0"><button type="button" class="btn btn-primary w-100 fw-bold" onclick="confirmarMaoObra()">Confirmar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEquipamentos" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header"><h5 class="modal-title fw-bold">Equipamentos</h5><button type="button" class="btn-close" id="closeModalEqBtn" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3 bg-light border-0" placeholder="Buscar..." onkeyup="filtrarLista('listaEQ', this.value)">
                <div class="list-group list-group-flush" id="listaEQ">
                    <?php if(isset($listaEquipamentos)): foreach($listaEquipamentos as $eq): ?>
                        <label class="list-group-item px-0 border-bottom"><input class="form-check-input me-2 chk-eq" type="checkbox" value="<?= htmlspecialchars($eq['nome']); ?>"> <span class="fw-bold text-dark"><?= htmlspecialchars($eq['nome']); ?></span></label>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="modal-footer border-top-0"><button type="button" class="btn btn-primary w-100 fw-bold" onclick="confirmarEquipamentos()">Confirmar</button></div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../estruturas/rodape.php'; ?>

<script>
    // --- PREPARAÇÃO DOS DADOS (PHP -> JS) ---
    const dadosOrcamento = {};
    const listaAtividadesArray = [];

    <?php 
    $orcamentoMap = [];
    $orcamentoLista = [];

    if(!empty($itensOrcamento)) {
        foreach($itensOrcamento as $item) {
            $saldo = $item['quantidade_total'] - ($item['quantidade_acumulada'] ?? 0);
            
            $orcamentoMap[$item['id']] = [
                'saldo' => (float)$saldo,
                'unidade' => $item['unidade']
            ];

            if($item['tipo'] == 'composicao' || $item['tipo'] == 'insumo') {
                $cod = $item['itemizacao'] ?? $item['codigo_referencia'];
                $desc = $item['descricao'];
                $descLimpa = str_replace(['"', "'", "\n", "\r"], ['&quot;', '', ' ', ' '], $desc);
                
                $textoExibicao = "[{$cod}] {$descLimpa} (Saldo: " . number_format($saldo, 2, ',', '.') . " {$item['unidade']})";
                $textoBusca = mb_strtolower($cod . ' ' . $descLimpa);

                $orcamentoLista[] = [
                    'id' => $item['id'],
                    'texto' => $textoExibicao,
                    'busca' => $textoBusca
                ];
            }
        }
    }
    ?>

    const dadosMapRaw = <?php echo json_encode($orcamentoMap); ?>;
    const dadosListaRaw = <?php echo json_encode($orcamentoLista); ?>;

    if (dadosMapRaw) Object.assign(dadosOrcamento, dadosMapRaw);
    if (dadosListaRaw) listaAtividadesArray.push(...dadosListaRaw);

    // Inicializa contador com base no PHP (se houver itens)
    let contadorAtividades = <?= isset($contadorJS) ? $contadorJS : 0; ?>;

    function addAtividadeRow() {
        document.getElementById('msgVazioAtividades').style.display = 'none';
        let tbody = document.getElementById('listaAtividadesBody');
        let row = document.createElement('tr');
        row.id = 'row_ativ_' + contadorAtividades;

        row.innerHTML = `
            <td style="vertical-align:top; position: relative;">
                <div class="d-flex gap-2 mb-1">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_ativ_${contadorAtividades}" id="rb_orc_${contadorAtividades}" value="orcamento" checked onchange="toggleInputAtividade(${contadorAtividades})">
                        <label class="form-check-label small" for="rb_orc_${contadorAtividades}">Orçamento</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_ativ_${contadorAtividades}" id="rb_man_${contadorAtividades}" value="manual" onchange="toggleInputAtividade(${contadorAtividades})">
                        <label class="form-check-label small text-primary" for="rb_man_${contadorAtividades}">Manual</label>
                    </div>
                </div>
                
                <div id="box_select_${contadorAtividades}" class="input-wrapper">
                    <input type="text" class="form-control form-control-sm fw-bold border-primary" 
                           id="input_busca_${contadorAtividades}" 
                           placeholder="Digite para buscar..." 
                           oninput="filtrarAutocomplete(this, ${contadorAtividades})"
                           autocomplete="off">
                    
                    <input type="hidden" name="atividades[id_item_orcamento][]" id="hidden_id_${contadorAtividades}" class="select-servico">
                    <div id="lista_busca_${contadorAtividades}" class="autocomplete-items"></div>
                    <div class="alerta-saldo small text-danger mt-1" style="display:none"></div>
                </div>

                <div id="box_manual_${contadorAtividades}" style="display:none;">
                    <input type="text" name="atividades[descricao_manual][]" class="form-control form-control-sm fw-bold border-warning" placeholder="Nome da atividade...">
                </div>
            </td>

            <td style="vertical-align:top">
                <input type="number" name="atividades[quantidade][]" class="form-control fw-bold input-qtd" step="0.01" oninput="verificarSaldo(this)" required>
            </td>
            
            <td style="vertical-align:top">
                <input type="text" name="atividades[descricao][]" class="form-control form-control-sm" placeholder="Obs">
            </td>
            
            <td style="vertical-align:top">
                <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-trash-fill"></i></button>
            </td>
        `;

        tbody.appendChild(row);
        contadorAtividades++;
    }

    function filtrarAutocomplete(input, idRow) {
        let val = input.value.toLowerCase();
        let listDiv = document.getElementById("lista_busca_" + idRow);
        
        listDiv.innerHTML = "";
        if (!val || val.length < 2) return false;

        let count = 0;
        for (let i = 0; i < listaAtividadesArray.length; i++) {
            if (listaAtividadesArray[i].busca.indexOf(val) > -1) {
                let itemDiv = document.createElement("DIV");
                itemDiv.innerHTML = listaAtividadesArray[i].texto;
                itemDiv.dataset.id = listaAtividadesArray[i].id;
                itemDiv.dataset.texto = listaAtividadesArray[i].texto;

                itemDiv.addEventListener("click", function() {
                    input.value = this.dataset.texto;
                    document.getElementById("hidden_id_" + idRow).value = this.dataset.id;
                    listDiv.innerHTML = "";
                    verificarSaldo(document.getElementById("hidden_id_" + idRow));
                });
                
                listDiv.appendChild(itemDiv);
                count++;
                if(count > 15) break; 
            }
        }
    }

    document.addEventListener("click", function (e) {
        let items = document.getElementsByClassName("autocomplete-items");
        for (let i = 0; i < items.length; i++) {
            if (e.target.type !== 'text') { items[i].innerHTML = ""; }
        }
    });

    function toggleInputAtividade(id) {
        const isManual = document.getElementById(`rb_man_${id}`).checked;
        const boxSelect = document.getElementById(`box_select_${id}`);
        const boxManual = document.getElementById(`box_manual_${id}`);
        const hiddenInput = document.getElementById(`hidden_id_${id}`);
        const searchInput = document.getElementById(`input_busca_${id}`);
        const manualInput = boxManual.querySelector('input');

        if (isManual) {
            boxSelect.style.display = 'none';
            boxManual.style.display = 'block';
            hiddenInput.value = ""; 
            searchInput.value = ""; 
            searchInput.required = false;
        } else {
            boxSelect.style.display = 'block';
            boxManual.style.display = 'none';
            manualInput.value = ""; 
        }
    }

    function verificarSaldo(el) {
        let row = el.closest('tr');
        if(!row) return;

        let idItemInput = row.querySelector('.select-servico');
        let idItem = idItemInput ? idItemInput.value : null;
        let qtdInput = row.querySelector('.input-qtd');
        let qtd = parseFloat(qtdInput.value);
        let alerta = row.querySelector('.alerta-saldo');

        // Nota: Ao editar, o saldo já considera o realizado. O ideal seria somar o valor atual do banco ao saldo para validar corretamente, 
        // mas aqui mantemos o alerta simples.
        if(idItem && dadosOrcamento[idItem] && !isNaN(qtd)) {
            let saldo = dadosOrcamento[idItem].saldo;
            // Tolerância simples para edição
            if(qtd > (saldo * 1.5)) { 
                alerta.innerText = `Atenção: Saldo de aprox. ${saldo.toFixed(2)}`;
                alerta.style.display = 'block';
            } else { 
                alerta.style.display = 'none'; 
            }
        } else if(alerta) {
            alerta.style.display = 'none';
        }
    }

    function confirmarMaoObra() {
        const container = document.getElementById('containerMaoObra');
        const tipoSelect = document.getElementById('tipoPadraoMO');
        const tipoVal = tipoSelect.value;
        const tipoTxt = tipoSelect.options[tipoSelect.selectedIndex].text;
        
        document.querySelectorAll('.chk-mo:checked').forEach(chk => {
            const nome = chk.value;
            const id = 'mo_' + Math.floor(Math.random() * 100000);
            const html = `<div class="col-12 col-md-6 col-lg-4" id="${id}"><div class="bg-white border rounded p-3"><div class="d-flex justify-content-between mb-2"><div class="fw-bold text-truncate" title="${nome}">${nome}</div><button type="button" class="btn-close small" onclick="document.getElementById('${id}').remove()"></button></div><input type="hidden" name="mao_obra[funcao][]" value="${nome}"><input type="hidden" name="mao_obra[tipo][]" value="${tipoVal}"><div class="input-group input-group-sm"><button type="button" class="btn btn-light border" onclick="alterarQtd(this, -1)">-</button><input type="number" name="mao_obra[quantidade][]" class="form-control text-center fw-bold" value="1" min="0"><button type="button" class="btn btn-light border" onclick="alterarQtd(this, 1)">+</button></div><div class="small text-muted mt-1 text-end">${tipoTxt}</div></div></div>`;
            container.insertAdjacentHTML('beforeend', html);
            chk.checked = false;
        });
        document.getElementById('closeModalMaoObraBtn').click();
    }

    function confirmarEquipamentos() {
        const container = document.getElementById('containerEquipamentos');
        document.querySelectorAll('.chk-eq:checked').forEach(chk => {
            const nome = chk.value;
            const id = 'eq_' + Math.floor(Math.random() * 100000);
            const html = `<div class="col-12 col-md-6 col-lg-4" id="${id}"><div class="bg-white border rounded p-3"><div class="d-flex justify-content-between mb-2"><div class="fw-bold text-truncate" title="${nome}">${nome}</div><button type="button" class="btn-close small" onclick="document.getElementById('${id}').remove()"></button></div><input type="hidden" name="equipamentos[nome][]" value="${nome}"><div class="input-group input-group-sm"><button type="button" class="btn btn-light border" onclick="alterarQtd(this, -1)">-</button><input type="number" name="equipamentos[quantidade][]" class="form-control text-center fw-bold" value="1" min="0"><button type="button" class="btn btn-light border" onclick="alterarQtd(this, 1)">+</button></div></div></div>`;
            container.insertAdjacentHTML('beforeend', html);
            chk.checked = false;
        });
        document.getElementById('closeModalEqBtn').click();
    }

    function alterarQtd(btn, val) {
        const input = btn.parentElement.querySelector('input');
        let novo = parseInt(input.value) + val;
        if(novo >= 0) input.value = novo;
    }
    
    function filtrarLista(id, txt) {
        const labels = document.getElementById(id).getElementsByTagName('label');
        const termo = txt.toLowerCase();
        for(let l of labels) {
            l.style.display = l.innerText.toLowerCase().includes(termo) ? '' : 'none';
        }
    }

    // UPLOAD E COMPRESSÃO
    const inputFotos = document.getElementById('inputFotos');
    const galeriaFotos = document.getElementById('galeriaFotos');
    const uploadArea = document.getElementById('uploadArea');
    const iconUpload = document.getElementById('iconUpload');
    const textUpload = document.getElementById('textUpload');
    const statusUpload = document.getElementById('statusUpload');
    const dt = new DataTransfer(); 

    async function comprimirImagem(file) {
        if (!file.type.match(/image.*/)) return file;
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (e) => {
                const img = new Image();
                img.src = e.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width, height = img.height;
                    const max = 1600;
                    if (width > max) { height *= max/width; width = max; }
                    canvas.width = width; canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob((blob) => {
                        resolve(new File([blob], file.name, {type: 'image/jpeg', lastModified: Date.now()}));
                    }, 'image/jpeg', 0.7);
                }
            }
        });
    }

    async function processarArquivos(fileList) {
        const files = Array.from(fileList).filter(f => f.type.match(/image.*/));
        if(files.length === 0) return;
        uploadArea.classList.add('processing');
        iconUpload.classList.add('d-none'); textUpload.classList.add('d-none'); statusUpload.classList.remove('d-none');

        for(const file of files) {
            try {
                const comp = await comprimirImagem(file);
                dt.items.add(comp);
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'col-4 col-sm-3 position-relative';
                    div.innerHTML = `<div class="preview-container"><img src="${e.target.result}" class="preview-img"><button type="button" class="btn-remove-img" onclick="removerFoto(this, '${comp.name}')"><i class="bi bi-x"></i></button></div>`;
                    galeriaFotos.appendChild(div);
                }
                reader.readAsDataURL(comp);
            } catch(e) {}
        }
        inputFotos.files = dt.files;
        uploadArea.classList.remove('processing');
        iconUpload.classList.remove('d-none'); textUpload.classList.remove('d-none'); statusUpload.classList.add('d-none');
    }

    inputFotos.addEventListener('change', async function() {
        const files = this.files;
        this.value = '';
        await processarArquivos(files);
    });

    ['dragenter', 'dragover'].forEach(evt => {
        uploadArea.addEventListener(evt, function(e) {
            e.preventDefault(); e.stopPropagation();
            uploadArea.classList.add('dragover');
        });
    });
    ['dragleave', 'dragend'].forEach(evt => {
        uploadArea.addEventListener(evt, function(e) {
            e.preventDefault(); e.stopPropagation();
            uploadArea.classList.remove('dragover');
        });
    });
    uploadArea.addEventListener('drop', async function(e) {
        e.preventDefault(); e.stopPropagation();
        uploadArea.classList.remove('dragover');
        if(e.dataTransfer.files.length > 0) await processarArquivos(e.dataTransfer.files);
    });

    function removerFoto(btn, name) {
        btn.closest('.col-4').remove();
        for(let i=0; i<dt.items.length; i++) {
            if(dt.items[i].getAsFile().name === name) { dt.items.remove(i); break; }
        }
        inputFotos.files = dt.files;
    }

    document.getElementById('formDiario').addEventListener('submit', function() {
        document.getElementById('loadingOverlay').classList.remove('d-none');
        document.getElementById('btnSalvar').disabled = true;
    });
</script>