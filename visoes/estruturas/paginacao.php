<?php
/**
 * @var int $pagina        Página atual
 * @var int $totalPaginas  Total de páginas
 * @var string $urlBase    URL base sem o parâmetro pagina (ex: "index.php?acao=listar_obras")
 */
if (!isset($pagina, $totalPaginas) || $totalPaginas <= 1) return;
?>
<nav class="d-flex justify-content-center mt-4">
    <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $urlBase ?>&pagina=<?= $pagina - 1 ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
            <?php
            // Mostra sempre: primeira, última, atual e ±2 ao redor da atual
            $mostrar = ($p === 1 || $p === $totalPaginas || abs($p - $pagina) <= 2);
            $reticencias = (!$mostrar && ($p === 2 || $p === $totalPaginas - 1));
            if ($reticencias): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php elseif ($mostrar): ?>
                <li class="page-item <?= $p === $pagina ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $urlBase ?>&pagina=<?= $p ?>"><?= $p ?></a>
                </li>
            <?php endif; ?>
        <?php endfor; ?>

        <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $urlBase ?>&pagina=<?= $pagina + 1 ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
