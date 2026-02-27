<?php

use App\Models\BoletaModel;

$nivelUsuario = session()->get('nivel'); // Obtenemos el nivel (1=Admin, 7=Alumno)

// Solo cargamos el modelo de boletas si es Admin, para no gastar recursos con alumnos
$grados_menu = [];
if ($nivelUsuario == 1 || $nivelUsuario == 2) {
    $boletaModel = new BoletaModel();
    $grados_menu = $boletaModel->getGradosMenu();
}
?>

<style>
    .nav-link.multiline-link {
        height: auto !important;
        min-height: 45px;
        align-items: center;
        padding-top: 8px;
        padding-bottom: 8px;
    }

    .nav-text.multiline-text {
        white-space: normal !important;
        line-height: 1.2 !important;
        display: block;
        max-width: 170px;
    }

    @media (max-width: 991.98px) {
        .main-nav {
            position: fixed !important;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100vh !important;
            width: 260px !important;
            z-index: 9999 !important;
            background: #fff;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        body .main-content,
        body .page-content,
        div.main-content {
            margin-left: 0 !important;
            width: 100% !important;
            min-width: 100vw !important;
            max-width: 100% !important;
            padding-left: 15px !important;
            padding-right: 15px !important;
        }

        footer.footer {
            left: 0 !important;
            width: 100% !important;
            margin-left: 0 !important;
        }

        body.sidebar-enable .main-nav {
            transform: translateX(0) !important;
        }

        body.sidebar-enable::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            backdrop-filter: blur(2px);
        }

        body.dark-mode-active .main-nav {
            background-color: #222736 !important;
            border-right: 1px solid #383e50;
        }
    }

    @media (min-width: 992px) {
        .main-nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            z-index: 1002;
            background: #fff;
            border-right: 1px solid #f1f1f1;
            transition: all 0.3s ease;
        }

        body .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
        }

        html[data-sidebar-size="sm"] .main-nav {
            width: 70px;
        }

        html[data-sidebar-size="sm"] body .main-content {
            margin-left: 70px;
        }

        html[data-sidebar-size="sm"] .main-nav .nav-text,
        html[data-sidebar-size="sm"] .main-nav .menu-title,
        html[data-sidebar-size="sm"] .main-nav .nav-arrow,
        html[data-sidebar-size="sm"] .logo-box img {
            display: none !important;
        }

        body.dark-mode-active .main-nav {
            background-color: #222736;
            border-right: 1px solid #32394e;
        }
    }
</style>

<div class="main-nav">

    <div class="logo-box text-center" style="padding: 15px 10px;">
        <img src="<?= base_url('images/LogoST.png') ?>" alt="Logo ST" style="width: 130px; height: auto; display: block; margin: 0 auto;">
    </div>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title d-flex justify-content-between align-items-center pe-3">
                <span>Menú</span>
                <div class="d-lg-none" style="cursor: pointer;" onclick="document.body.classList.remove('sidebar-enable')">
                    <iconify-icon icon="solar:double-alt-arrow-left-bold-duotone" class="fs-20 text-primary"></iconify-icon>
                </div>
            </li>

            <?php if ($nivelUsuario == 1 || $nivelUsuario == 2): ?>

                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="abrirConfiguracion(1, 'Configurar Primaria/Secundaria'); return false;">
                        <span class="nav-text">PRIMARIA - SECUNDARIA</span>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="abrirConfiguracion(2, 'Configurar Bachillerato'); return false;">
                        <span class="nav-text">BACHILLERATO</span>
                    </a>
                </li>

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
                    <a class="nav-link" href="#submenu-registro" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenu-registro">
                        <span class="nav-icon"><iconify-icon icon="solar:pen-new-square-broken"></iconify-icon></span>
                        <span class="nav-text"> Registro </span>
                        <span class="nav-arrow ms-auto"><iconify-icon icon="solar:alt-arrow-right-broken"></iconify-icon></span>
                    </a>
                    <div class="collapse" id="submenu-registro">
                        <ul class="navbar-nav" style="padding-left: 25px;">
                            <li class="nav-item"><a class="nav-link" href="<?= base_url('registro-profesor') ?>"><span class="nav-text"> Registrar Profesor </span></a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= base_url('registro/grados') ?>"><span class="nav-text"> Grados </span></a></li>
                        </ul>
                    </div>
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
                                        <?php foreach ($grados_menu as $grado): ?>
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
                                    <span class="nav-text multiline-text"> Calificar Boleta Bimestre</span>
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
                                <a class="nav-link multiline-link" href="#submenu-boleta-todo" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenu-boleta-todo">
                                    <span class="nav-text multiline-text"> Calificar Boleta Todo Bimestre</span>
                                    <span class="nav-arrow ms-auto"><iconify-icon icon="solar:alt-arrow-right-broken"></iconify-icon></span>
                                </a>
                                <div class="collapse" id="submenu-boleta-todo">
                                    <ul class="navbar-nav" style="padding-left: 15px; border-left: 1px solid #eee;">
                                        <?php foreach ($grados_menu as $grado): ?>
                                            <li class="nav-item">
                                                <a class="nav-link" href="<?= base_url('calificaciones_bimestre/lista/' . $grado['id_grado']) ?>">
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
                    <a class="nav-link" href="<?= base_url('asignar-area') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:clipboard-list-broken"></iconify-icon></span>
                        <span class="nav-text multiline-text"> Asignar área 3° Bachillerato</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('asignar-titulares') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:user-id-broken"></iconify-icon></span>
                        <span class="nav-text"> Asignar titulares </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('niveles') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:users-group-rounded-broken"></iconify-icon></span>
                        <span class="nav-text"> Niveles </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('cambio-grado') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:users-group-rounded-broken"></iconify-icon></span>
                        <span class="nav-text"> Cambio de Grado </span>
                    </a>
                </li>

            <?php elseif ($nivelUsuario == 7): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('alumno/dashboard') ?>">
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('alumno/boleta') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:document-text-broken"></iconify-icon></span>
                        <span class="nav-text"> Ver Boleta </span>
                    </a>
                </li>

                <!-- <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('alumno/contenido') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:folder-with-files-broken"></iconify-icon></span>
                        <span class="nav-text"> Ver Contenidos </span>
                    </a>
                </li> -->

                <li class="nav-item">
                    <a class="nav-link" href="http://www.sjs.edu.mx/administracion/convenios-internos/" target="_blank">
                        <span class="nav-icon"><iconify-icon icon="solar:diploma-broken"></iconify-icon></span>
                        <span class="nav-text multiline-text"> Convenios universitarios </span>
                    </a>
                </li>

                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="<?= base_url('logout') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:logout-broken"></iconify-icon></span>
                        <span class="nav-text"> Salir </span>
                    </a>
                </li>


            <?php elseif ($nivelUsuario == 9): ?>

                <?php
                // Pequeña lógica visual: Obtener nombre del grado asignado
                $idGradoT = session()->get('nivelT');
                $nombreGradoT = "Mi Grupo";

                if ($idGradoT) {
                    $db = \Config\Database::connect();
                    $rowG = $db->table('grados')->select('nombreGrado')->where('id_grado', $idGradoT)->get()->getRow();
                    if ($rowG) $nombreGradoT = $rowG->nombreGrado;
                }
                ?>
                <!-- Dashboard -->

                <!-- Grado Titular -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('titular/mi-grupo') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:users-group-two-rounded-broken"></iconify-icon></span>
                        <span class="nav-text"> <?= esc($nombreGradoT) ?> </span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('titular/calificar') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:pen-new-square-broken"></iconify-icon></span>
                        <span class="nav-text"> Calificar Boleta </span>
                    </a>
                </li>

                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="<?= base_url('logout') ?>">
                        <span class="nav-icon"><iconify-icon icon="solar:logout-broken"></iconify-icon></span>
                        <span class="nav-text"> Salir </span>
                    </a>
                </li>

            <?php endif; ?>
        </ul>
    </div>
</div>

<script>
    document.addEventListener('click', function(event) {
        if (document.body.classList.contains('sidebar-enable')) {
            const menu = document.querySelector('.main-nav');
            const toggleBtn = document.querySelector('.button-toggle-menu');
            if (menu && !menu.contains(event.target) && (!toggleBtn || !toggleBtn.contains(event.target))) {
                document.body.classList.remove('sidebar-enable');
            }
        }
    });
</script>