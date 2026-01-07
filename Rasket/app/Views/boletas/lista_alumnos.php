<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Lista de Alumnos"]) ?>
    <?= $this->include("partials/head-css") ?>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>
        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title">
                                Alumnos de <?= esc($grado['nombreGrado']) ?>
                                <span class="badge bg-primary ms-2" style="font-size: 0.8em;">
                                    Total: <?= !empty($alumnos) ? count($alumnos) : 0 ?>
                                </span>
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Matrícula</th>
                                        <th>Nombre Completo</th>
                                        <th>Estatus</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($alumnos)): ?>
                                        <?php
                                        // Inicializamos contador
                                        $contador = 1;
                                        foreach ($alumnos as $a):
                                        ?>
                                            <tr>
                                                <td class="text-muted fw-bold"><?= $contador++ ?></td>

                                                <td><?= esc($a['matricula']) ?></td>
                                                <td class="fw-bold">
                                                    <?= esc($a['ap_Alumno']) ?> <?= esc($a['am_Alumno']) ?> <?= esc($a['Nombre']) ?>
                                                </td>
                                                <td><span class="badge bg-success">Activo</span></td>
                                                <td class="text-center">
                                                    <?php
                                                    // Lógica para decidir a dónde apunta el link
                                                    if (isset($is_titular) && $is_titular) {
                                                        // Ruta para Titular (Solo lleva ID Alumno, el grado lo sabe por sesión)
                                                        $urlBoleta = base_url('titular/ver-boleta/' . $a['id']);
                                                    } else {
                                                        // Ruta para Admin (Lleva ID Grado y ID Alumno)
                                                        $urlBoleta = base_url('boleta/ver/' . $id_grado . '/' . $a['id']);
                                                    }
                                                    ?>

                                                    <a href="<?= $urlBoleta ?>" class="btn btn-sm btn-primary">
                                                        <i class="bx bx-file"></i> Ver Boleta
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No hay alumnos.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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