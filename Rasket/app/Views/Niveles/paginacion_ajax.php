<?php
/**
 * Archivo: app/Views/paginacion_ajax.php
 * Plantilla exclusiva para tablas AJAX (Lógica Matemática Pura)
 */
$pager->setSurroundCount(2);

$pagerService = \Config\Services::pager();
$paginaActual = $pagerService->getCurrentPage(); 
$totalPaginas = $pagerService->getPageCount();

// Cálculos manuales
$anterior  = ($paginaActual > 1) ? $paginaActual - 1 : 1;
$siguiente = ($paginaActual < $totalPaginas) ? $paginaActual + 1 : $totalPaginas;
?>

<nav aria-label="Navegación">
    <ul class="pagination justify-content-center m-0">
        
        <?php if ($paginaActual > 1) : ?>
            <li class="page-item">
                <a class="page-link" href="?page=1" aria-label="Primera">
                    <span aria-hidden="true">&laquo;&laquo;</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $anterior ?>" aria-label="Anterior">
                    <span aria-hidden="true">&laquo; Anterior</span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
            <li class="page-item disabled"><span class="page-link">&laquo; Anterior</span></li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <?php $numPagina = is_numeric($link['title']) ? $link['title'] : 1; ?>
                <a class="page-link" href="?page=<?= $numPagina ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($paginaActual < $totalPaginas) : ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $siguiente ?>" aria-label="Siguiente">
                    <span aria-hidden="true">Siguiente &raquo;</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $totalPaginas ?>" aria-label="Última">
                    <span aria-hidden="true">&raquo;&raquo;</span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link">Siguiente &raquo;</span></li>
            <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
        <?php endif ?>
    </ul>
</nav>