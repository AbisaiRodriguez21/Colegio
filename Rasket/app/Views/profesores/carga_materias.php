<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?php 
        $nombreProfesor = isset($profesor['Nombre']) ? $profesor['Nombre'] . ' ' . $profesor['ap_Alumno'] : 'Profesor';
        echo view("partials/title-meta", array("title" => "Materias Asignadas: " . $nombreProfesor)); 
    ?>
    <?= $this->include("partials/head-css") ?>

    <style>
        /* Estilo del botón "block" gris */
        .btn-materia-block {
            background-color: #f8f9fa; /* Color gris muy claro */
            border: 1px solid #e9ecef;
            color: #6c757d;
            transition: all 0.2s;
            display: block;
            padding: 15px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        /* Efecto al pasar el mouse */
        .btn-materia-block:hover {
            background-color: #e2e6ea;
            color: #495057;
            cursor: pointer;
        }

        /* Título de los niveles (1 Primaria, etc) */
        .level-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057; /* Gris oscuro suave */
            margin-bottom: 1rem;
        }

        /* Tarjeta limpia */
        .card-clean {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            height: 100%;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>
        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="mb-0" style="font-size: 1.5rem; color: #495057;">
                                Materias Asignadas a: <span class="text-primary"><?= esc($nombreProfesor) ?></span>
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card card-clean">
                            <div class="card-body">
                                <h5 class="level-title">1 Primaria</h5>
                                <a href="#" class="btn-materia-block">
                                    Ver materias
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card card-clean">
                            <div class="card-body">
                                <h5 class="level-title">2 Primaria</h5>
                                <a href="#" class="btn-materia-block">
                                    Ver materias
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card card-clean">
                            <div class="card-body">
                                <h5 class="level-title">3 Primaria</h5>
                                <a href="#" class="btn-materia-block">
                                    Ver materias
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card card-clean">
                            <div class="card-body">
                                <h5 class="level-title">4 Primaria</h5>
                                <a href="#" class="btn-materia-block">
                                    Ver materias
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card card-clean">
                            <div class="card-body">
                                <h5 class="level-title">5 Primaria</h5>
                                <a href="#" class="btn-materia-block">
                                    Ver materias
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card card-clean">
                            <div class="card-body">
                                <h5 class="level-title">6 Primaria</h5>
                                <a href="#" class="btn-materia-block">
                                    Ver materias
                                </a>
                            </div>
                        </div>
                    </div>

                     <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card card-clean">
                            <div class="card-body">
                                <h5 class="level-title">1 A Secundaria</h5>
                                <a href="#" class="btn-materia-block">
                                    Ver materias
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card card-clean">
                            <div class="card-body">
                                <h5 class="level-title">2 A Secundaria</h5>
                                <a href="#" class="btn-materia-block">
                                    Ver materias
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <a href="<?= base_url('profesores') ?>" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back"></i> Regresar a la lista
                        </a>
                    </div>
                </div>

            </div>
            <?= $this->include("partials/footer") ?>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
</body>
</html>