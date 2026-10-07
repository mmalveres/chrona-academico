<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Relatório de Visita Fiscal — <?= htmlspecialchars($relatorio['nome_obra'] ?? '') ?></title>
<style>
@page { size: A4 portrait; margin: 20mm 18mm 20mm 18mm; }
*, *::before, *::after { box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #1a1a1a; margin: 0; padding: 0; background: #fff; }

/* CABEÇALHO */
.header { display: flex; justify-content: space-between; align-items: center;
          border-bottom: 3px solid #0066ff; padding-bottom: 10px; margin-bottom: 16px; }
.header-left { flex: 1; }
.orgao-nome { font-size: 14pt; font-weight: 900; color: #0066ff; margin: 0 0 2px; }
.doc-tipo { font-size: 9pt; color: #666; text-transform: uppercase; letter-spacing: 1px; margin: 0; }
.header-logo { max-height: 60px; max-width: 130px; object-fit: contain; }

/* TÍTULO DO DOC */
.doc-title { text-align: center; background: #0066ff; color: #fff;
             padding: 8px 12px; border-radius: 6px; margin-bottom: 16px; }
.doc-title h1 { font-size: 13pt; font-weight: 900; margin: 0 0 2px; }
.doc-title p  { font-size: 9pt; margin: 0; opacity: .85; }

/* SEÇÃO */
.section { margin-bottom: 14px; }
.section-title { font-size: 9pt; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
                 color: #0066ff; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 8px; }

/* GRID DE INFOS */
.info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 12px; }
.info-block { background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 10px; }
.info-label { font-size: 7.5pt; font-weight: 700; text-transform: uppercase; color: #666; margin-bottom: 3px; }
.info-value { font-size: 11pt; font-weight: 700; color: #111; }
.info-sub   { font-size: 9pt; color: #555; margin-top: 2px; }

/* STATUS */
.status-badge { display: inline-flex; align-items: center; gap: 6px;
                padding: 6px 14px; border-radius: 20px; font-size: 11pt; font-weight: 900; }
.status-conforme     { background: #d1fae5; color: #065f46; border: 1.5px solid #6ee7b7; }
.status-nao_conforme { background: #fee2e2; color: #991b1b; border: 1.5px solid #fca5a5; }

/* OBSERVAÇÕES */
.obs-box { background: #f9fafb; border: 1px solid #e5e7eb; border-left: 4px solid #0066ff;
           border-radius: 0 6px 6px 0; padding: 12px; white-space: pre-wrap;
           line-height: 1.75; font-size: 10.5pt; }

/* FOTOS */
.fotos-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 8px; }
.foto-item  { text-align: center; }
.foto-item img { width: 100%; height: 130px; object-fit: cover; border-radius: 5px;
                 border: 1px solid #ddd; }
.foto-legenda { font-size: 7.5pt; color: #666; margin-top: 4px; }

/* ASSINATURA */
.assinatura-area { margin-top: 32px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
.ass-box { text-align: center; }
.ass-linha { border-top: 1.5px solid #333; padding-top: 6px; margin-top: 40px; font-size: 9.5pt; }
.ass-nome  { font-weight: 700; }
.ass-cargo { font-size: 8.5pt; color: #555; }

/* RODAPÉ */
.footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8pt; color: #aaa;
          border-top: 1px solid #e5e7eb; padding: 6px 18mm; display: flex;
          justify-content: space-between; background: #fff; }

@media print {
    .no-print { display: none !important; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>

<!-- BOTÃO IMPRIMIR (não imprime) -->
<div class="no-print" style="text-align:center;padding:12px;background:#f3f4f6;margin-bottom:0;">
    <button onclick="window.print()" style="background:#0066ff;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">
        <span style="margin-right:6px;">🖨</span>Imprimir / Salvar PDF
    </button>
    <a href="javascript:history.back()" style="margin-left:16px;color:#555;font-size:13px;text-decoration:none;">← Voltar</a>
</div>

<!-- CABEÇALHO -->
<div class="header">
    <div class="header-left">
        <p class="doc-tipo">Relatório de Visita de Fiscalização</p>
        <p class="orgao-nome"><?= htmlspecialchars($relatorio['orgao']) ?></p>
    </div>
    <?php if (!empty($relatorio['logo_orgao'])): ?>
    <img src="assets/img/<?= htmlspecialchars($relatorio['logo_orgao']) ?>"
         alt="Logo" class="header-logo">
    <?php endif; ?>
</div>

<!-- TÍTULO -->
<div class="doc-title">
    <h1>RELATÓRIO DE VISITA DE FISCALIZAÇÃO</h1>
    <p><?= htmlspecialchars($relatorio['orgao']) ?> &mdash; <?= date('d/m/Y', strtotime($relatorio['data_visita'])) ?></p>
</div>

<!-- INFORMAÇÕES GERAIS -->
<div class="section">
    <div class="section-title">Identificação</div>
    <div class="info-grid">
        <div class="info-block">
            <div class="info-label">Obra Inspecionada</div>
            <div class="info-value" style="font-size:10pt;"><?= htmlspecialchars($relatorio['nome_obra'] ?? '—') ?></div>
            <?php if (!empty($relatorio['cidade'])): ?>
            <div class="info-sub"><?= htmlspecialchars($relatorio['cidade']) ?></div>
            <?php endif; ?>
        </div>
        <div class="info-block">
            <div class="info-label">Data da Visita</div>
            <div class="info-value"><?= date('d/m/Y', strtotime($relatorio['data_visita'])) ?></div>
            <div class="info-sub"><?= date('l', strtotime($relatorio['data_visita'])) ?></div>
        </div>
        <div class="info-block">
            <div class="info-label">Resultado</div>
            <?php if ($relatorio['status'] === 'conforme'): ?>
            <span class="status-badge status-conforme">&#10003; Conforme</span>
            <?php else: ?>
            <span class="status-badge status-nao_conforme">&#9888; Não Conforme</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="info-grid" style="grid-template-columns:1fr 1fr;">
        <div class="info-block">
            <div class="info-label">Fiscal Responsável</div>
            <div class="info-value" style="font-size:10.5pt;"><?= htmlspecialchars($relatorio['nome_fiscal'] ?? '—') ?></div>
        </div>
        <div class="info-block">
            <div class="info-label">Órgão Fiscalizador</div>
            <div class="info-value" style="font-size:10pt;"><?= htmlspecialchars($relatorio['orgao_fiscalizador'] ?? $relatorio['orgao']) ?></div>
        </div>
    </div>
</div>

<!-- OBSERVAÇÕES -->
<?php if (!empty($relatorio['observacoes'])): ?>
<div class="section">
    <div class="section-title">Observações e Apontamentos</div>
    <div class="obs-box"><?= htmlspecialchars($relatorio['observacoes']) ?></div>
</div>
<?php endif; ?>

<!-- FOTOS -->
<?php if (!empty($fotos)): ?>
<div class="section">
    <div class="section-title">Registro Fotográfico (<?= count($fotos) ?> foto<?= count($fotos) > 1 ? 's' : '' ?>)</div>
    <div class="fotos-grid">
        <?php foreach ($fotos as $foto): ?>
        <div class="foto-item">
            <img src="<?= htmlspecialchars($foto['caminho']) ?>" alt="Foto">
            <?php if (!empty($foto['legenda'])): ?>
            <div class="foto-legenda"><?= htmlspecialchars($foto['legenda']) ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ASSINATURA -->
<div class="assinatura-area">
    <div class="ass-box">
        <div class="ass-linha">
            <div class="ass-nome"><?= htmlspecialchars($relatorio['nome_fiscal'] ?? '') ?></div>
            <div class="ass-cargo"><?= htmlspecialchars($relatorio['orgao_fiscalizador'] ?? $relatorio['orgao']) ?></div>
        </div>
    </div>
    <div class="ass-box">
        <div class="ass-linha">
            <div class="ass-nome">Responsável pela Obra</div>
            <div class="ass-cargo"><?= htmlspecialchars($relatorio['nome_obra'] ?? '') ?></div>
        </div>
    </div>
</div>

<!-- RODAPÉ -->
<div class="footer">
    <span>Documento gerado pelo sistema CHRONA &mdash; engmarcosalveres.com.br/CHRONA</span>
    <span>Emitido em <?= date('d/m/Y \à\s H:i') ?></span>
</div>

</body>
</html>
