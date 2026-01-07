<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Gestión de Grados"]); ?>
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
                            <h4 class="page-title">Grados Escolares</h4>
                        </div>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-4">Añadir Grado</h4>
                                
                                <form action="<?= base_url('registro/grados/guardar') ?>" method="post" class="row align-items-end">
                                    <div class="col-sm-9">
                                        <div class="mb-3 mb-sm-0">
                                            <label class="form-label">Nombre del Grado</label>
                                            <input type="text" class="form-control" name="nombreGrado" placeholder="Ej. 1 Primaria" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <button type="submit" class="btn btn-primary w-100">
                                            Enviar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-3">Lista de Grados Existentes</h4>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th class="text-center" style="width: 150px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($grados)): ?>
                                                <?php foreach ($grados as $grado): ?>
                                                    <tr>
                                                        <td class="align-middle"><?= esc($grado['nombreGrado']) ?></td>
                                                        <td class="text-center">
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-danger" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#modalBorrar"
                                                                    data-href="<?= base_url('registro/grados/eliminar/' . $grado['id_grado']) ?>">
                                                                <i class="fas fa-trash-alt me-1"></i> Eliminar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">No hay grados registrados.</td>
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
            <?= $this->include("partials/footer") ?>
        </div>
    </div>

    <div class="modal fade" id="modalBorrar" tabindex="-1" aria-labelledby="modalBorrarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"> <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalBorrarLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                    <p class="mb-0 fs-5">¿Estás seguro de que deseas eliminar este grado?</p>
                    <p class="text-muted small">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="btnConfirmar" class="btn btn-danger">Sí, Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalBorrar = document.getElementById('modalBorrar');
            
            // Verificamos que el modal exista para evitar errores
            if (modalBorrar) {
                modalBorrar.addEventListener('show.bs.modal', function (event) {
                    // 1. Identificar el botón que disparó el modal
                    var button = event.relatedTarget;
                    
                    // 2. Extraer la URL del atributo data-href
                    var urlEliminar = button.getAttribute('data-href');
                    
                    // 3. Encontrar el botón de confirmar dentro del modal
                    var btnConfirmar = modalBorrar.querySelector('#btnConfirmar');
                    
                    // 4. Asignar la URL al href del botón
                    btnConfirmar.setAttribute('href', urlEliminar);
                    
                    // (Opcional) Debug para ver en consola si funciona
                    console.log("URL de eliminación asignada:", urlEliminar);
                });
            }
        });
    </script>
</body>
</html>