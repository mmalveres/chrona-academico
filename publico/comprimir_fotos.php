<?php
// Script de migração — comprime fotos existentes para 720p
// Execute UMA VEZ e depois APAGUE este arquivo do servidor.

// Proteção simples: acesse com ?token=comprimir2024
$tokenEsperado = 'comprimir2024';
if (($_GET['token'] ?? '') !== $tokenEsperado) {
    http_response_code(403);
    die('<h3>Acesso negado.</h3><p>Acesse com <code>?token=' . $tokenEsperado . '</code></p>');
}

set_time_limit(0);
ini_set('memory_limit', '256M');

$maxLargura = 1280;
$maxAltura  = 720;
$qualidade  = 75;
$diretorio  = __DIR__ . '/uploads/';
$extensoes  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// ── Funções auxiliares ────────────────────────────────────────────

function criarImagemDe(string $caminho, string $mime) {
    return match($mime) {
        'image/jpeg' => imagecreatefromjpeg($caminho),
        'image/png'  => imagecreatefrompng($caminho),
        'image/gif'  => imagecreatefromgif($caminho),
        'image/webp' => imagecreatefromwebp($caminho),
        default      => false,
    };
}

function comprimirArquivo(string $caminho, int $maxLarg, int $maxAlt, int $qual): string {
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mime     = finfo_file($finfo, $caminho);
    finfo_close($finfo);

    $tiposOk = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $tiposOk)) return 'ignorado (não é imagem)';

    [$largOrig, $altOrig] = getimagesize($caminho);
    if (!$largOrig || !$altOrig) return 'ignorado (inválida)';

    $src = criarImagemDe($caminho, $mime);
    if (!$src) return 'erro ao abrir';

    if ($largOrig > $maxLarg || $altOrig > $maxAlt) {
        $ratio   = min($maxLarg / $largOrig, $maxAlt / $altOrig);
        $novLarg = (int)round($largOrig * $ratio);
        $novAlt  = (int)round($altOrig  * $ratio);

        $dst = imagecreatetruecolor($novLarg, $novAlt);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $novLarg, $novAlt, $largOrig, $altOrig);
        imagejpeg($dst, $caminho, $qual);
        imagedestroy($dst);
        $resultado = "redimensionada ({$largOrig}×{$altOrig} → {$novLarg}×{$novAlt})";
    } else {
        imagejpeg($src, $caminho, $qual);
        $resultado = "recomprimida (dimensões ok: {$largOrig}×{$altOrig})";
    }

    imagedestroy($src);
    return $resultado;
}

// ── Coleta arquivos ───────────────────────────────────────────────

$arquivos = [];
foreach (new DirectoryIterator($diretorio) as $f) {
    if ($f->isDot() || $f->isDir()) continue;
    $ext = strtolower($f->getExtension());
    if (in_array($ext, $extensoes)) {
        $arquivos[] = $f->getPathname();
    }
}

$total     = count($arquivos);
$tamanhoAntes = 0;
$tamanhoDepois = 0;

// ── Processa ─────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Compressão de Fotos</title>
<style>
  body { font-family: monospace; background: #111; color: #eee; padding: 24px; }
  h2   { color: #4ade80; }
  .ok  { color: #4ade80; }
  .err { color: #f87171; }
  .ig  { color: #9ca3af; }
  table { border-collapse: collapse; width: 100%; margin-top: 16px; }
  th, td { padding: 6px 10px; text-align: left; border-bottom: 1px solid #333; font-size: .85rem; }
  th { color: #facc15; }
  .resumo { margin-top: 24px; background: #1f2937; padding: 16px; border-radius: 8px; }
</style>
</head>
<body>
<h2>Compressão de Fotos Existentes</h2>
<p>Encontrados: <strong><?= $total ?></strong> arquivo(s) em <code>publico/uploads/</code></p>

<?php if ($total === 0): ?>
<p class="ig">Nenhuma foto encontrada.</p>
<?php else: ?>

<table>
<thead><tr><th>#</th><th>Arquivo</th><th>Antes</th><th>Depois</th><th>Resultado</th></tr></thead>
<tbody>
<?php
$i = 0;
foreach ($arquivos as $caminho) {
    $i++;
    $nome         = basename($caminho);
    $tamAntes     = filesize($caminho);
    $tamanhoAntes += $tamAntes;

    $resultado = comprimirArquivo($caminho, $maxLargura, $maxAltura, $qualidade);
    clearstatcache(true, $caminho);
    $tamDepois     = filesize($caminho);
    $tamanhoDepois += $tamDepois;

    $classe = str_starts_with($resultado, 'ignorado') ? 'ig'
            : (str_starts_with($resultado, 'erro')    ? 'err' : 'ok');

    $economia = $tamAntes > 0 ? round((1 - $tamDepois / $tamAntes) * 100) : 0;

    echo "<tr>
        <td>{$i}</td>
        <td>{$nome}</td>
        <td>" . number_format($tamAntes / 1024, 1) . " KB</td>
        <td>" . number_format($tamDepois / 1024, 1) . " KB</td>
        <td class='{$classe}'>{$resultado}" . ($economia > 0 ? " <em>(-{$economia}%)</em>" : "") . "</td>
    </tr>";
    flush();
}
?>
</tbody>
</table>

<div class="resumo">
    <strong>Resumo:</strong><br>
    Tamanho total antes: <strong><?= number_format($tamanhoAntes / 1024 / 1024, 2) ?> MB</strong><br>
    Tamanho total depois: <strong><?= number_format($tamanhoDepois / 1024 / 1024, 2) ?> MB</strong><br>
    <?php $economiaTotal = $tamanhoAntes > 0 ? round((1 - $tamanhoDepois / $tamanhoAntes) * 100) : 0; ?>
    Espaço liberado: <strong class="ok"><?= number_format(($tamanhoAntes - $tamanhoDepois) / 1024 / 1024, 2) ?> MB (<?= $economiaTotal ?>%)</strong><br><br>
    <span style="color:#f87171">⚠️ Apague este arquivo do servidor agora: <code>publico/comprimir_fotos.php</code></span>
</div>

<?php endif; ?>
</body>
</html>
