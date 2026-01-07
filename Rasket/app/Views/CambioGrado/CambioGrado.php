<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Cambio de Grado"]); ?>
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
                            <h4 class="page-title">Lista de alumnos para pagos y reinscripciones</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="row mb-3">
                                    <div class="col-md-6"></div>
                                    <div class="col-md-6">
                                        <form action="<?= base_url('cambio-grado') ?>" method="get" id="form-busqueda">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-search-alt"></i></span>
                                                <input type="text" class="form-control" name="q" 
                                                       placeholder="Buscar por nombre, apellidos o email..." 
                                                       value="<?= esc($busqueda) ?>" autocomplete="off">
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped dt-responsive nowrap w-100 align-middle">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th class="hidden-xs">Email / Contraseña</th>
                                                <th class="hidden-xs">Estatus</th>
                                                <th class="hidden-xs">Grado</th>
                                                <th class="text-center" style="width: 100px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($alumnos)): ?>
                                                <?php foreach ($alumnos as $alum): ?>
                                                    <tr id="fila-alumno-<?= $alum['id'] ?>">
                                                        <td>
                                                            <strong><?= esc($alum['ap_Alumno'] . ' ' . $alum['am_Alumno'] . ' ' . $alum['Nombre']) ?></strong>
                                                        </td>
                                                        <td class="hidden-xs text-muted">
                                                            <?= esc($alum['email']) ?> <br>
                                                            <small><i class="bx bx-key"></i> <?= esc($alum['pass']) ?></small>
                                                        </td>
                                                        
                                                        <td class="hidden-xs">
                                                            <?php if($alum['estatus'] == 1): ?>
                                                                <span class="badge bg-success bg-opacity-10 text-success">Activo</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning bg-opacity-10 text-warning"><?= esc($alum['nombre_estatus']) ?></span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="hidden-xs">
                                                            <span class="badge bg-info"><?= esc($alum['nombreGrado'] ?? 'Sin Grado') ?></span>
                                                        </td>

                                                        <td class="text-center">
    <div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bx bx-dots-horizontal-rounded font-size-16"></i> Opciones
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            
            <?php if($alum['estatus'] == 2): ?>
                <li>
                    <a class="dropdown-item text-success btn-activar" href="javascript:void(0);" data-id="<?= $alum['id'] ?>">
                        <i class="bx bx-check-circle me-2"></i> Activar alumno
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a class="dropdown-item text-danger btn-baja" href="javascript:void(0);" data-id="<?= $alum['id'] ?>">
                        <i class="bx bx-x-circle me-2"></i> Baja de alumno
                    </a>
                </li>
            <?php endif; ?>

            <li><hr class="dropdown-divider"></li>
            
            <li><a class="dropdown-item disabled" href="#"><i class="bx bx-printer me-2"></i> Imprimir</a></li>
            <li><a class="dropdown-item disabled" href="#"><i class="bx bx-dollar-circle me-2"></i> Ver Pagos</a></li>
            <li><a class="dropdown-item disabled" href="#"><i class="bx bx-id-card me-2"></i> Editar Ficha</a></li>
        </ul>
    </div>
</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="5" class="text-center py-4">No se encontraron registros.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row align-items-center mt-3">
                                    <div class="col-sm-12 col-md-5">
                                        <p class="text-muted mb-0">Mostrando <span class="fw-bold"><?= $info['inicio'] ?></span> a <span class="fw-bold"><?= $info['fin'] ?></span> de <span class="fw-bold"><?= $info['total'] ?></span> registros</p>
                                    </div>
                                    <div class="col-sm-12 col-md-7">
                                        <div class="d-flex justify-content-end">
                                            <?= $pager->links('default', 'bootstrap_pagos') ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <?= $this->include("partials/footer") ?>
            <!-- MODAL ACTIVAR -->
            <?= $this->include("CambioGrado/modal_activar") ?>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // 1. Buscador (Código que ya tenías)
        const inputBusqueda = document.querySelector('input[name="q"]');
        let timeout = null;
        if(inputBusqueda){
            const val = inputBusqueda.value; 
            if(val) { inputBusqueda.focus(); inputBusqueda.value = ''; inputBusqueda.value = val; }
            inputBusqueda.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => { document.getElementById('form-busqueda').submit(); }, 600);
            });
        }

        // =======================================================
        // 2. LÓGICA DE BAJA (Simple SweetAlert)
        // =======================================================
        document.body.addEventListener('click', function(e) {
            if (e.target.closest('.btn-baja')) {
                const btn = e.target.closest('.btn-baja');
                const id = btn.getAttribute('data-id');
                
                Swal.fire({
                    title: '¿Dar de baja?',
                    text: "Pasará a estatus 'En proceso' (2).",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f46a6a',
                    confirmButtonText: 'Sí, baja temporal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        realizarPeticion('<?= base_url("cambio-grado/baja") ?>', { id: id });
                    }
                });
            }
        });

        // =======================================================
        // 3. LÓGICA DE ACTIVACIÓN (ABRIR MODAL)
        // =======================================================
        const modalActivar = new bootstrap.Modal(document.getElementById('modal-activar'));
        const selectNuevoGrado = document.getElementById('activar_nuevo_grado');
        const alertaRepetidor = document.getElementById('alerta-repetidor');
        let gradoActualId = 0;

        // Al hacer clic en "Activar alumno"
        document.body.addEventListener('click', function(e) {
            if (e.target.closest('.btn-activar')) {
                const btn = e.target.closest('.btn-activar');
                const id = btn.getAttribute('data-id');
                
                // 1. Limpiar formulario previo
                document.getElementById('form-activar').reset();
                alertaRepetidor.classList.add('d-none');
                
                // 2. Cargar datos del alumno (Grado actual y lista de grados)
                fetch(`<?= base_url("cambio-grado/get-datos") ?>?id=${id}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        // Llenar datos ocultos y visibles
                        document.getElementById('activar_id_alumno').value = id;
                        document.getElementById('titulo-modal-activar').innerText = 'Reactivar a: ' + data.alumno.NombreCompleto;
                        document.getElementById('activar_email').value = data.alumno.email;
                        
                        document.getElementById('activar_grado_actual_texto').value = data.alumno.nombreGrado;
                        document.getElementById('activar_grado_actual_id').value = data.alumno.grado;
                        gradoActualId = data.alumno.grado;

                        
                        
                        // Checar repetidor inicial
                        verificarRepetidor();

                        // Mostrar modal
                        modalActivar.show();
                    } else {
                        Swal.fire('Error', 'No se pudieron cargar los datos.', 'error');
                    }
                });
            }
        });

        // Detectar cambio de grado para mostrar aviso "Repetidor"
        selectNuevoGrado.addEventListener('change', verificarRepetidor);

        function verificarRepetidor() {
            if (selectNuevoGrado.value == gradoActualId) {
                alertaRepetidor.classList.remove('d-none');
            } else {
                alertaRepetidor.classList.add('d-none');
            }
        }

        // =======================================================
        // 4. GUARDAR ACTIVACIÓN (Confirmar botón verde)
        // =======================================================
        document.getElementById('btn-confirmar-activacion').addEventListener('click', function() {
            const form = document.getElementById('form-activar');
            
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const formData = new FormData(form);

            fetch('<?= base_url("cambio-grado/activar") ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    modalActivar.hide();
                    Swal.fire({
                        icon: 'success', title: '¡Alumno Activado!', text: data.msg, timer: 2000, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.msg, 'error');
                }
            });
        });

        // Helper para petición simple
        function realizarPeticion(url, data) {
            const formData = new FormData();
            for (const key in data) formData.append(key, data[key]);
            
            fetch(url, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => {
                if(d.status === 'success') { Swal.fire('Éxito', d.msg, 'success').then(() => location.reload()); }
                else { Swal.fire('Error', d.msg, 'error'); }
            });
        }
    });
</script>
</body>
</html>