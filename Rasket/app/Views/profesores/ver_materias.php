<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Ver Carga Académica"]) ?>
    <?= $this->include("partials/head-css") ?>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-3">
                    <div class="col-12">
                        <a href="<?= base_url('lista-profesores') ?>" class="btn btn-secondary">
                            <i class="bx bx-arrow-back"></i> Regresar a la lista
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-info text-white border-0"> <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md profile-user-wid mb-4 mb-xl-0 me-3">
                                        <span class="avatar-title rounded-circle bg-light text-info font-size-24">
                                            <i class="bx bx-user"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="font-size-16 text-white mb-1">
                                            <?= esc($profesor['Nombre'] . ' ' . $profesor['ap_Alumno'] . ' ' . $profesor['am_Alumno']) ?>
                                        </h5>
                                        <p class="text-white-50 mb-0">Resumen de carga académica asignada.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Materias Asignadas</h4>

                                <?php if (empty($carga)): ?>
                                    <div class="alert alert-warning text-center" role="alert">
                                        <i class="bx bx-info-circle me-1"></i> Este profesor no tiene ninguna materia asignada actualmente.
                                    </div>
                                <?php else: ?>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Materia</th>
                                                    <th>Grado</th>
                                                    <th class="text-center" style="width: 150px;">Estatus</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($carga as $materia): ?>
                                                    <tr>
                                                        <td class="fw-bold text-primary">
                                                            <i class="bx bx-book-open me-2"></i> <?= esc($materia['nombre_materia']) ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary font-size-12">
                                                                <?= esc($materia['nombreGrado']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-success"> <i class="bx bx-check"></i> Activa</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <?= $this->include("partials/footer") ?>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
</body>
</html>