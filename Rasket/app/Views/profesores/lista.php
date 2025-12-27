<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", array("title" => "Lista de Profesores")) ?>
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
                            <h4 class="page-title">Lista de Profesores</h4>
                            <p class="text-muted mb-4">Gestión de la plantilla docente.</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                
                                <?php if (session()->getFlashdata('success')): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="bx bx-check-circle"></i> <?= session()->getFlashdata('success') ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>

                                <?php if (session()->getFlashdata('error')): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="bx bx-error"></i> <?= session()->getFlashdata('error') ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped dt-responsive nowrap w-100" id="profesores-table">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Estatus</th>
                                                <th>Contraseña</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($profesores as $p): ?>
                                                <tr>
                                                    <td>
                                                        <h5 class="font-size-14 mb-1">
                                                            <?= esc($p['Nombre']) ?> <?= esc($p['ap_Alumno']) ?> <?= esc($p['am_Alumno']) ?>
                                                        </h5>
                                                    </td>
                                                    <td><?= esc($p['email']) ?></td>
                                                    <td>
                                                        <span class="badge badge-soft-success font-size-11 m-1"><?= esc($p['nombre_nivel']) ?></span>
                                                    </td>
                                                    <td class="text-muted"><?= esc($p['pass']) ?></td>
                                                    
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            
                                                            <a href="<?= base_url('profesor/asignar/'.$p['id']) ?>" 
                                                               class="btn btn-sm btn-outline-primary" 
                                                               data-bs-toggle="tooltip" 
                                                               title="Gestionar Carga Académica">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </a>

                                                            <a href="<?= base_url('profesor/ver/'.$p['id']) ?>" 
                                                               class="btn btn-sm btn-outline-info" 
                                                               data-bs-toggle="tooltip" 
                                                               title="Ver Materias Asignadas">
                                                                <i class="bx bx-show"></i>
                                                            </a>

                                                            <?php if (!$p['tiene_materias']): ?>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-danger" 
                                                                        onclick="confirmarEliminarProfesor(<?= $p['id'] ?>)"
                                                                        data-bs-toggle="tooltip" 
                                                                        title="Dar de baja">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-secondary" 
                                                                        disabled 
                                                                        title="No se puede eliminar (Tiene materias)">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            <?php endif; ?>

                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div> </div> </div> </div> </div> </div> <?= $this->include("partials/footer") ?>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>

    <script src="<?= base_url('assets/js/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/plugins/datatables/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#profesores-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                }
            });
            
            // Inicializar Tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

        function confirmarEliminarProfesor(id) {
        Swal.fire({
            title: '¿Dar de baja?',
            text: "El profesor será dado de baja.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33', // Rojo para advertencia
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, dar de baja',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirigimos a la ruta del controlador
                window.location.href = "<?= base_url('profesor/eliminar/') ?>" + id;
            }
        });
    }
    </script>
</body>
</html>