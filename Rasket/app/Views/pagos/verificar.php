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
                                        <h4 class="header-title">Listado de Pagos</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <form action="<?= base_url('verificar-pagos') ?>" method="get" id="form-busqueda">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="text" 
                                                       name="q" 
                                                       id="input-busqueda"
                                                       class="form-control" 
                                                       placeholder="Escribe para buscar..." 
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
                                                    <tr>
                                                        <td><?= $pago['fechaEnvio']; ?></td>
                                                        <td>
                                                            <strong><?= strtoupper($pago['Nombre'] . ' ' . $pago['ap_Alumno']); ?></strong>
                                                        </td>
                                                        <td class="hidden-xs"><?= $pago['email']; ?></td>
                                                        <td class="hidden-xs">
                                                            $<?= number_format($pago['cantidad'], 2); ?>
                                                            <br><small class="text-muted"><?= $pago['concepto']; ?></small>
                                                        </td>
                                                        <td class="hidden-xs">
                                                            <span class="badge bg-info"><?= $pago['nombreGrado'] ?? 'Sin Grado'; ?></span>
                                                        </td>
                                                        <td class="hidden-xs text-center">
                                                            <?php if (!empty($pago['ficha'])): ?>
                                                                <button class="btn btn-sm btn-soft-secondary btn-ver-ticket" 
                                                                        type="button" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modal-ticket" 
                                                                        data-src="pagos/<?= $pago['ficha']; ?>">
                                                                    <i class="fas fa-image"></i> Ver
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="text-muted"><small>Sin ticket</small></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-primary btn-validar-pago" data-id="<?= $pago['id_pago']; ?>">
                                                                Validar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">No se encontraron resultados.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-center mt-3">
                                    <?= $pager->links('default', 'bootstrap_pagination') ?>
                                </div>

                            </div> </div> </div> </div> </div> </div> <?= $this->include("partials/footer") ?>

    </div> <div class="modal fade" id="modal-ticket" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                     <h5 class="modal-title">Comprobante</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="imagen-ticket-modal" src="" class="img-fluid border rounded">
                </div>
            </div>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // Lógica para Búsqueda Automática (Sin botón)
            const inputBusqueda = document.getElementById('input-busqueda');
            const formBusqueda = document.getElementById('form-busqueda');
            let timeout = null;

            inputBusqueda.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    formBusqueda.submit(); 
                }, 600);
            });
            
            const valorActual = inputBusqueda.value;
            if(valorActual) {
                inputBusqueda.focus();
                inputBusqueda.setSelectionRange(valorActual.length, valorActual.length);
            }

            const ticketModal = document.getElementById('modal-ticket');
            if (ticketModal) {
                ticketModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const srcImagen = button.getAttribute('data-src');
                    document.getElementById('imagen-ticket-modal').src = srcImagen;
                });
            }

            const botonesValidar = document.querySelectorAll('.btn-validar-pago');
            botonesValidar.forEach(btn => {
                btn.addEventListener('click', function() {
                    let idPago = this.getAttribute('data-id');
                    alert("ID a validar: " + idPago);
                });
            });
        });
    </script>

</body>
</html>