<?php

$pager->setSurroundCount(2);

// 1. OBTENEMOS LOS NÚMEROS REALES
// Usamos el servicio global para asegurar que tenemos el número exacto
$pagerService = \Config\Services::pager();
$paginaActual = $pagerService->getCurrentPage();
$totalPaginas = $pagerService->getPageCount();

// 2. PREPARAMOS LA URL
// Esto asegura que no se pierdan filtros como ?q=busqueda&orden=DESC
$uri = service('request')->getUri();
$uri->setQuery(service('request')->getServer('QUERY_STRING'));
?>

<nav aria-label="Navegación">
    <ul class="pagination justify-content-end m-0">

        <?php if ($paginaActual > 1) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="Primera">
                    <span aria-hidden="true">&laquo;&laquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link">&laquo;&laquo;</span>
            </li>
        <?php endif ?>

        <?php if ($paginaActual > 1) : ?>
            <?php 
                $uriAnterior = clone $uri; 
                // Restamos 1 matemáticamente
                $linkAnterior = $uriAnterior->addQuery('page', $paginaActual - 1);
            ?>
            <li class="page-item">
                <a class="page-link" href="<?= (string)$linkAnterior ?>" aria-label="Anterior">
                    <span aria-hidden="true">&laquo; Anterior</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link">&laquo; Anterior</span>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= $link['uri'] ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($paginaActual < $totalPaginas) : ?>
            <?php 
                $uriSiguiente = clone $uri;
                // Sumamos 1 matemáticamente
                $linkSiguiente = $uriSiguiente->addQuery('page', $paginaActual + 1);
            ?>
            <li class="page-item">
                <a class="page-link" href="<?= (string)$linkSiguiente ?>" aria-label="Siguiente">
                    <span aria-hidden="true">Siguiente &raquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link">Siguiente &raquo;</span>
            </li>
        <?php endif ?>

        <?php if ($paginaActual < $totalPaginas) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="Última">
                    <span aria-hidden="true">&raquo;&raquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link">&raquo;&raquo;</span>
            </li>
        <?php endif ?>
    </ul>
</nav>