<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Mis Materias"]); ?>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        .materia-card {
            transition: transform 0.2s;
            border-left: 4px solid #5c6bc0; /* Detalle de color */
        }
        .materia-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(92, 107, 192, 0.2);
        }
        .materia-icon {
            background-color: rgba(92, 107, 192, 0.1);
            color: #5c6bc0;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 24px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-3">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <h4 class="page-title">Contenido Académico</h4>
                        <!-- <a href="<?= base_url('alumno/dashboard') ?>" class="btn btn-secondary btn-sm">
                            <i class='bx bx-arrow-back'></i> Regresar
                        </a> -->
                    </div>
                </div>

                <div class="alert alert-info border-0" role="alert">
                    <i class="mdi mdi-school me-2"></i> 
                    Estás viendo las materias de: <strong><?= esc($nombreGrado) ?></strong>
                </div>

                <div class="row">
                    <?php if (!empty($materias)): ?>
                        <?php foreach ($materias as $mat): ?>
                            <div class="col-md-6 col-xl-4">
                                <a href="#" class="text-decoration-none">
                                    <div class="card materia-card h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="materia-icon me-3">
                                                <i class="bx bx-book-bookmark"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title text-dark mb-1">
                                                    <?= esc($mat['nombre_materia']) ?>
                                                </h5>
                                                <small class="text-muted">Ver contenido</small>
                                            </div>
                                            <div class="ms-auto">
                                                <i class="bx bx-chevron-right text-muted fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                No se encontraron materias asignadas a este grado.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div> <?= $this->include("partials/footer") ?>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
</body>
</html>