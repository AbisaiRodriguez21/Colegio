<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">
<head>
    <?= view("partials/title-meta", ["title" => "Lista de Alumnos - Historial"]) ?>
    <?= $this->include("partials/head-css") ?>
</head>
<body>
    <div class="wrapper">
        
        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="page-title">
                                Alumnos de <?= esc($grado['nombreGrado']) ?>
                                <span class="badge bg-primary ms-2" style="font-size: 0.8em;">
                                    Total: <?= !empty($alumnos) ? count($alumnos) : 0 ?>
                                </span>
                            </h4>
                            
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle mb-0">
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
                                            <?php if(!empty($alumnos)): ?>
                                                <?php 
                                                // Inicializamos contador
                                                $contador = 1; 
                                                foreach($alumnos as $al): 
                                                    // Concatenamos el nombre
                                                    $nombreCompleto = $al['ap_Alumno'] . ' ' . $al['am_Alumno'] . ' ' . $al['Nombre'];
                                                ?>
                                                <tr>
                                                    <td class="text-muted fw-bold"><?= $contador++ ?></td>
                                                    
                                                    <td><?= esc($al['matricula']) ?></td>
                                                    <td class="fw-bold">
                                                        <?= esc(mb_strtoupper($nombreCompleto)) ?>
                                                    </td>
                                                    <td><span class="badge bg-success">Activo</span></td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('calificaciones_bimestre/alumno/' . $al['id'] . '/' . $grado['id_grado']) ?>" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="bx bx-pencil"></i> Ver Boleta
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No hay alumnos registrados en este grado.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> 
        </div> 
        <?= $this->include("partials/footer") ?>
    </div> 
    <?= $this->include("partials/vendor-scripts") ?>
</body>
</html>