<?php 
// 1. Configuración visual
$pager->setSurroundCount(2); 

// 2. CORRECCIÓN: Llamamos al Servicio Global para saber la página real
$pagerService = \Config\Services::pager();
$paginaActual = $pagerService->getCurrentPage(); 

// 3. Preparamos la URL actual para modificarle el ?page=X
$uri = current_url(true);
?>

<nav aria-label="Navegación">
    <ul class="pagination justify-content-center m-0">

        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="Primera">
                    <span aria-hidden="true">&laquo;&laquo;</span>
                </a>
            </li>
            
            <li class="page-item">
                <a class="page-link" href="<?= (string) $uri->addQuery('page', $paginaActual - 1) ?>" aria-label="Anterior">
                    <span aria-hidden="true">&laquo; Anterior</span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">&laquo;&laquo;</span>
            </li>
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

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= (string) $uri->addQuery('page', $paginaActual + 1) ?>" aria-label="Siguiente">
                    <span aria-hidden="true">Siguiente &raquo;</span>
                </a>
            </li>
            
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="Última">
                    <span aria-hidden="true">&raquo;&raquo;</span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">Siguiente &raquo;</span>
            </li>
            <li class="page-item disabled">
                <span class="page-link">&raquo;&raquo;</span>
            </li>
        <?php endif ?>
    </ul>
</nav>