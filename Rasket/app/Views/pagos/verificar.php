<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Verificar Pagos"]); ?>
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
                            <h4 class="page-title">Verificar Pagos</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h4 class="header-title">Listado de Pagos Pendientes</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <form action="<?= base_url('verificar-pagos') ?>" method="get" id="form-busqueda">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="text" 
                                                       name="q" 
                                                       id="input-busqueda"
                                                       class="form-control" 
                                                       placeholder="Buscar por nombre o email..." 
                                                       value="<?= esc($busqueda) ?>" 
                                                       autocomplete="off">
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Nombre</th>
                                                <th class="hidden-xs">Email</th>
                                                <th class="hidden-xs">Monto</th>
                                                <th class="hidden-xs">Grado</th>
                                                <th class="hidden-xs">Ticket</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($pagos)): ?>
                                                <?php foreach ($pagos as $pago): ?>
                                                    <tr id="fila-pago-<?= $pago['id_pago'] ?>">
                                                        <td><?= esc($pago['fechaEnvio']); ?></td>
                                                        <td>
                                                            <strong><?= strtoupper(esc($pago['Nombre'] . ' ' . $pago['ap_Alumno'])); ?></strong>
                                                        </td>
                                                        <td class="hidden-xs"><?= esc($pago['email']); ?></td>
                                                        <td class="hidden-xs">
                                                            $<?= number_format($pago['cantidad'], 2); ?>
                                                            <br><small class="text-muted"><?= esc($pago['concepto']); ?></small>
                                                        </td>
                                                        <td class="hidden-xs">
                                                            <span class="badge bg-info"><?= esc($pago['nombreGrado'] ?? 'Sin Grado'); ?></span>
                                                        </td>
                                                        <td class="hidden-xs text-center">
                                                            <?php if (!empty($pago['ficha'])): ?>
                                                                <button class="btn btn-sm btn-soft-secondary btn-ver-ticket" 
                                                                        type="button" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modal-ticket" 
                                                                        data-src="pagos/<?= esc($pago['ficha']); ?>">
                                                                    <i class="fas fa-image"></i> Ver
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="text-muted"><small>Sin ticket</small></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-primary btn-validar-pago" data-id="<?= $pago['id_pago']; ?>">
                                                                <i class="bx bx-check-double"></i> Validar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">No hay pagos pendientes de validación.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($info_paginacion['total'] > 0): ?>
                                    <div class="row mt-3 align-items-center">
                                        <div class="col-sm-12 col-md-5">
                                            <div class="dataTables_info">
                                                Mostrando <strong><?= $info_paginacion['inicio'] ?></strong> a <strong><?= $info_paginacion['fin'] ?></strong> de <strong><?= number_format($info_paginacion['total']) ?></strong> registros
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-7">
                                            <div class="d-flex justify-content-end">
                                                <?= $pager->links('default', 'bootstrap_pagos') ?>
                                            </div>
                                        </div>
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

    <!-- MODAL -->
    <div class="modal fade" id="modal-ticket" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> <div class="modal-content">
                <div class="modal-header">
                     <h5 class="modal-title">Comprobante</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center bg-light p-0" id="contenedor-visualizador">
                </div>
            </div>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. Buscador automático
            const inputBusqueda = document.getElementById('input-busqueda');
            const formBusqueda = document.getElementById('form-busqueda');
            let timeout = null;

            if(inputBusqueda){
                inputBusqueda.addEventListener('input', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => { formBusqueda.submit(); }, 600);
                });
                
                const valorActual = inputBusqueda.value;
                if(valorActual) {
                    inputBusqueda.focus();
                    inputBusqueda.setSelectionRange(valorActual.length, valorActual.length);
                }
            }

            // 2. LÓGICA DEL MODAL (PDF vs IMAGEN)
            const ticketModal = document.getElementById('modal-ticket');
            if (ticketModal) {
                ticketModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const srcArchivo = button.getAttribute('data-src'); // Ejemplo: pagos/archivo.pdf
                    
                    // Construimos la ruta completa
                    const urlCompleta = '<?= base_url() ?>/' + srcArchivo;
                    
                    // Obtenemos el contenedor
                    const contenedor = document.getElementById('contenedor-visualizador');
                    
                    // Obtenemos la extensión del archivo
                    const extension = srcArchivo.split('.').pop().toLowerCase();
                    
                    // DECISIÓN: ¿Es PDF o Imagen?
                    if (extension === 'pdf') {
                        // Insertamos un IFRAME para leer el PDF
                        contenedor.innerHTML = `
                            <iframe src="${urlCompleta}" width="100%" height="600px" style="border:none;">
                                Tu navegador no soporta la visualización de PDFs.
                                <a href="${urlCompleta}" target="_blank">Descargar PDF</a>
                            </iframe>`;
                    } else {
                        // Insertamos una IMAGEN normal
                        contenedor.innerHTML = `
                            <div class="p-3">
                                <img src="${urlCompleta}" class="img-fluid border rounded shadow-sm" alt="Comprobante">
                            </div>`;
                    }
                });
                
                // Limpiar el modal al cerrar para que no se quede el archivo anterior cargado
                ticketModal.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('contenedor-visualizador').innerHTML = '';
                });
            }

            // 3. Validación AJAX
            const botonesValidar = document.querySelectorAll('.btn-validar-pago');
            botonesValidar.forEach(btn => {
                btn.addEventListener('click', function() {
                    let idPago = this.getAttribute('data-id');
                    
                    Swal.fire({
                        title: '¿Validar este pago?',
                        text: "El pago se marcará como verificado.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#34c38f',
                        cancelButtonColor: '#f46a6a',
                        confirmButtonText: 'Sí, validar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            realizarValidacion(idPago);
                        }
                    });
                });
            });

            function realizarValidacion(idPago) {
                const formData = new FormData();
                formData.append('id_pago', idPago);

                fetch('<?= base_url("verificar-pagos/validar") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('¡Validado!', data.msg, 'success');
                        const fila = document.getElementById('fila-pago-' + idPago);
                        if(fila) {
                            fila.style.transition = "all 0.5s";
                            fila.style.opacity = "0";
                            setTimeout(() => fila.remove(), 500);
                        }
                    } else {
                        Swal.fire('Error', data.msg, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Problema de conexión.', 'error');
                });
            }
        });
    </script>

</body>
</html>