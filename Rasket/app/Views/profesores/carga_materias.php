<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Asignar Materias"]) ?>
    <?= $this->include("partials/head-css") ?>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <a href="<?= base_url('lista-profesores') ?>" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back"></i> Regresar
                                </a>
                            </div>
                            <h4 class="page-title">Gestión de Carga Académica</h4>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title text-white">
                                    <i class="bx bx-user-circle"></i> 
                                    <?= esc($profesor['Nombre'] . ' ' . $profesor['ap_Alumno'] . ' ' . $profesor['am_Alumno']) ?>
                                </h5>
                                <p class="card-text">Seleccione las materias que impartirá este docente. Recuerde guardar los cambios por cada grado.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bx bx-check-circle"></i> <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="accordion" id="accordionGrados">
                            
                            <?php foreach ($grados_completos as $index => $grado): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?= $grado['id_grado'] ?>">
                                        <button class="accordion-button collapsed fw-bold" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?= $grado['id_grado'] ?>" 
                                                aria-expanded="false" 
                                                aria-controls="collapse<?= $grado['id_grado'] ?>">
                                            <?= esc($grado['nombreGrado']) ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $grado['id_grado'] ?>" 
                                         class="accordion-collapse collapse" 
                                         aria-labelledby="heading<?= $grado['id_grado'] ?>" 
                                         data-bs-parent="#accordionGrados">
                                        
                                        <div class="accordion-body">
                                            
                                            <form action="<?= base_url('profesor/guardar_carga_grado') ?>" method="POST" class="form-carga-grado">
                                                
                                                <input type="hidden" name="id_profesor" value="<?= $profesor['id'] ?>">
                                                <input type="hidden" name="id_grado" value="<?= $grado['id_grado'] ?>">

                                                <div class="d-flex justify-content-end mb-3">
                                                    <button type="button" class="btn btn-success btn-guardar-grado">
                                                        <i class="bx bx-save"></i> Guardar Cambios de <?= esc($grado['nombreGrado']) ?>
                                                    </button>
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-hover table-centered mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Materia</th>
                                                                <th class="text-center" style="width: 150px;">Estado</th>
                                                                <th class="text-center" style="width: 100px;">Asignar</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($grado['lista_materias'] as $mat): ?>
                                                                <tr>
                                                                    <td><?= esc($mat['nombre_materia']) ?></td>
                                                                    
                                                                    <td class="text-center">
                                                                        <?php if($mat['estado_asignacion'] == 'propia'): ?>
                                                                            <span class="badge bg-success">Asignada</span>
                                                                        <?php else: ?>
                                                                            <span class="badge bg-light text-dark">Disponible</span>
                                                                        <?php endif; ?>
                                                                    </td>

                                                                    <td class="text-center">
                                                                        <div class="form-check form-switch d-flex justify-content-center">
                                                                            <input class="form-check-input" type="checkbox" 
                                                                                   name="materias[]" 
                                                                                   value="<?= $mat['Id_materia'] ?>" // O 'Id_materia' según tu BD
                                                                                   <?= ($mat['estado_asignacion'] == 'propia') ? 'checked' : '' ?>>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            
                                                            <?php if(empty($grado['lista_materias'])): ?>
                                                                <tr><td colspan="3" class="text-center text-muted">No hay materias registradas para este grado.</td></tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </form> </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </div>
                </div>

            </div>
            <?= $this->include("partials/footer") ?>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const botonesGuardar = document.querySelectorAll('.btn-guardar-grado');

            botonesGuardar.forEach(btn => {
                btn.addEventListener('click', function() {
                    const form = this.closest('form'); // Buscar el formulario padre
                    
                    Swal.fire({
                        title: '¿Guardar cambios?',
                        text: "Se actualizará la carga académica de este grado para el profesor.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

        });
    </script>

</body>
</html>