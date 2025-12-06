<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?php echo view("partials/title-meta", array("title" => "Asignar Materias")) ?>
    <?= $this->include("partials/head-css") ?>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>
        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-3">
                    <div class="col-12">
                        <h4 class="page-title">
                            Materias Asignadas a: <span class="text-primary"><?= esc($profesor['Nombre']) ?> <?= esc($profesor['ap_Alumno']) ?></span>
                        </h4>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>¡Correcto!</strong> <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="accordion" id="accordionGrados">
                    
                    <?php foreach ($grados_completos as $index => $grado): ?>
                        
                        <div class="accordion-item mb-2 border rounded shadow-sm">
                            <h2 class="accordion-header" id="heading<?= $grado['id_grado'] ?>">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse<?= $grado['id_grado'] ?>" aria-expanded="false" 
                                        aria-controls="collapse<?= $grado['id_grado'] ?>">
                                    <?= esc($grado['nombreGrado']) ?>
                                </button>
                            </h2>
                            
                            <div id="collapse<?= $grado['id_grado'] ?>" class="accordion-collapse collapse" 
                                 aria-labelledby="heading<?= $grado['id_grado'] ?>" data-bs-parent="#accordionGrados">
                                <div class="accordion-body bg-light">
                                    
                                    <div class="table-responsive bg-white rounded p-3">
                                        <table class="table table-sm table-striped table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Materia</th>
                                                    <th class="text-center" style="width: 200px;">Estado / Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($grado['lista_materias'] as $materia): ?>
                                                <tr>
                                                    <td class="fw-medium"><?= esc($materia['nombre_materia']) ?></td>
                                                    <td class="text-center">
                                                        
                                                        <?php if ($materia['estado_asignacion'] == 'ocupada'): ?>
                                                            
                                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                                                <i class="bx bx-lock-alt me-1"></i> Materia Asignada
                                                            </span>

                                                        <?php else: ?>

                                                            <form action="<?= base_url('profesor/guardar_materia') ?>" method="post">
                                                                
                                                                <input type="hidden" name="id_profesor" value="<?= $profesor['id'] ?>">
                                                                <input type="hidden" name="id_materia" value="<?= $materia['id_materia'] ?>">
                                                                <input type="hidden" name="id_grado" value="<?= $grado['id_grado'] ?>">

                                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" role="switch" 
                                                                               name="activo" value="1" 
                                                                               id="switch<?= $materia['id_materia'] ?>"
                                                                               <?= ($materia['estado_asignacion'] == 'propia') ? 'checked' : '' ?>
                                                                               onchange="this.form.submit()"> <label class="form-check-label" for="switch<?= $materia['id_materia'] ?>">
                                                                            <?= ($materia['estado_asignacion'] == 'propia') ? 'Asignada' : 'Asignar' ?>
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                            </form>

                                                        <?php endif; ?>

                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                
                                                <?php if(empty($grado['lista_materias'])): ?>
                                                    <tr><td colspan="2" class="text-center text-muted">No hay materias registradas en este grado.</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <a href="<?= base_url('lista-profesores') ?>" class="btn btn-secondary">
                            <i class="bx bx-arrow-back"></i> Regresar
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