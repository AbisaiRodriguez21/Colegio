<?php
// Usamos el modelo dedicado en lugar de consultas SQL crudas
use App\Models\BoletaModel;

$boletaModel = new BoletaModel();
$grados_menu = $boletaModel->getGradosMenu();
?>

<div class="main-nav">

    <div class="logo-box text-center" style="padding: 15px 10px;">
        <img src="<?= base_url('images/LogoST.png') ?>" alt="Logo ST" style="width: 130px; height: auto; display: block; margin: 0 auto;">
    </div>
    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <iconify-icon icon="solar:hamburger-menu-broken" class="button-sm-hover-icon"></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title">Menú</li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('dashboard') ?>">
                    <span class="nav-icon"><iconify-icon icon="solar:home-2-broken"></iconify-icon></span>
                    <span class="nav-text"> Dashboard </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('lista-profesores') ?>">
                    <span class="nav-icon"><iconify-icon icon="solar:users-group-rounded-broken"></iconify-icon></span>
                    <span class="nav-text"> Lista de profesores </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#submenu-alumnos" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenu-alumnos">
                    <span class="nav-icon"><iconify-icon icon="solar:user-rounded-broken"></iconify-icon></span>
                    <span class="nav-text"> Alumnos </span>
                    <span class="nav-arrow ms-auto"><iconify-icon icon="solar:alt-arrow-right-broken"></iconify-icon></span>
                </a>
                <div class="collapse" id="submenu-alumnos">
                    <ul class="navbar-nav" style="padding-left: 25px;">
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('alumnos/registro') ?>"><span class="nav-text"> Registro de Alumnos </span></a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('alumnos/preinscripciones') ?>"><span class="nav-text"> Preinscripciones </span></a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('grupos/lista') ?>"><span class="nav-text"> Lista de Grupos </span></a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#submenu-boleta" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenu-boleta">
                    <span class="nav-icon"><iconify-icon icon="solar:notebook-broken"></iconify-icon></span>
                    <span class="nav-text"> Boleta </span>
                    <span class="nav-arrow ms-auto"><iconify-icon icon="solar:alt-arrow-right-broken"></iconify-icon></span>
                </a>

                <div class="collapse" id="submenu-boleta">
                    <ul class="navbar-nav" style="padding-left: 20px;">

                        <li class="nav-item">
                            <a class="nav-link" href="#submenu-boleta-ver" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenu-boleta-ver">
                                <span class="nav-text"> Ver Boleta/ Imprimir </span>
                                <span class="nav-arrow ms-auto"><iconify-icon icon="solar:alt-arrow-right-broken"></iconify-icon></span>
                            </a>
                            <div class="collapse" id="submenu-boleta-ver">
                                <ul class="navbar-nav" style="padding-left: 15px; border-left: 1px solid #eee;">
                                    <?php foreach($grados_menu as $grado): ?>
    <li class="nav-item">
        <a class="nav-link" href="<?= base_url('boleta/lista/' . $grado['id_grado']) ?>">
            <span class="nav-text"><?= esc($grado['nombreGrado']) ?></span>
        </a>
    </li>
<?php endforeach; ?>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#submenu-boleta-calificar" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenu-boleta-calificar">
                                <span class="nav-text"> Calificar Boleta </span>
                                <span class="nav-arrow ms-auto"><iconify-icon icon="solar:alt-arrow-right-broken"></iconify-icon></span>
                            </a>
                            <div class="collapse" id="submenu-boleta-calificar">
                                <ul class="navbar-nav" style="padding-left: 15px; border-left: 1px solid #eee;">
                                    <?php foreach ($grados_menu as $grado): ?>
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?= base_url('boleta/calificar/' . $grado['id_grado']) ?>">
                                                <span class="nav-text"><?= esc($grado['nombreGrado']) ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#submenu-boleta-todo" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenu-boleta-todo">
                                <span class="nav-text"> Calificar Boleta Todo </span>
                                <span class="nav-arrow ms-auto"><iconify-icon icon="solar:alt-arrow-right-broken"></iconify-icon></span>
                            </a>
                            <div class="collapse" id="submenu-boleta-todo">
                                <ul class="navbar-nav" style="padding-left: 15px; border-left: 1px solid #eee;">
                                    <?php foreach ($grados_menu as $grado): ?>
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?= base_url('boleta/calificar_todo/' . $grado['id_grado']) ?>">
                                                <span class="nav-text"><?= esc($grado['nombreGrado']) ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('verificar-pagos') ?>">
                    <span class="nav-icon"><iconify-icon icon="solar:bill-list-broken"></iconify-icon></span>
                    <span class="nav-text"> Verificar pagos </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('correo') ?>">
                    <span class="nav-icon"><iconify-icon icon="solar:letter-broken"></iconify-icon></span>
                    <span class="nav-text"> Correos / Mensajes </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('area/asignar') ?>">
                    <span class="nav-icon"><iconify-icon icon="solar:clipboard-list-broken"></iconify-icon></span>
                    <span class="nav-text"> Asignar área </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('grado/cambio') ?>">
                    <span class="nav-icon"><iconify-icon icon="solar:refresh-circle-broken"></iconify-icon></span>
                    <span class="nav-text"> Cambio de grado </span>
                </a>
            </li>

        </ul>
    </div>
</div>