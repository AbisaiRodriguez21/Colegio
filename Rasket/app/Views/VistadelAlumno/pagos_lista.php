<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">
<head>
    <?= view("partials/title-meta", ["title" => "Mis Pagos"]); ?>
    <?= $this->include("partials/head-css") ?>
</head>
<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>
        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-3">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <h4 class="page-title">Historial de Pagos</h4>
                        <a href="<?= base_url('alumno/dashboard') ?>" class="btn btn-secondary btn-sm"><i class='bx bx-arrow-back'></i> Regresar</a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalPago">
                            <i class="mdi mdi-cash-plus me-1"></i> Reportar Nuevo Pago
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>Comprobante</th>
                                        <th>Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pagos)): ?>
                                        <?php foreach ($pagos as $p): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($p['fechaPago'])) ?></td>
                                                <td><?= esc($p['concepto']) ?> <br> <small class="text-muted"><?= esc($p['mes']) ?></small></td>
                                                <td>$<?= number_format($p['cantidad'] + $p['recargos'], 2) ?></td>
                                                <td>
                                                    <?php if (!empty($p['ficha'])): ?>
                                                        <a href="<?= base_url('pagos/' . $p['ficha']) ?>" target="_blank" class="btn btn-sm btn-info text-white"><i class="mdi mdi-eye"></i></a>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($p['validar_ficha'] == 49): ?>
                                                        <span class="badge bg-success">Verificado</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">En Revisión</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center">No hay pagos registrados.</td></tr>
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

    <div class="modal fade" id="modalPago" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <?= esc($alumno['Nombre'] . ' ' . $alumno['ap_Alumno'] . ' ' . $alumno['am_Alumno']) ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('alumno/pagos/guardar') ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="ciclo" value="<?= $ciclo ?>">
                        <input type="hidden" id="conceptoAnterior" value="<?= esc($alumno['permiso'] ?? '') ?>">

                        <div class="mb-3">
                            <label class="form-label text-muted">Fecha de reporte de pago</label>
                            <input type="date" name="fechaPago" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Concepto</label>
                                <select name="concepto" id="concepto" class="form-select" required onchange="verificarRecargos()">
                                    <option value="">Selecciona un concepto</option>
                                    <option value="Inscripción">Inscripción</option>
                                    <option value="Mensualidad">Mensualidad</option>
                                    <option value="Seguro Escolar">Seguro Escolar</option>
                                    <option value="Foto">Foto</option>
                                    <option value="Pago parcial mensualidad">Pago parcial mensualidad</option>
                                    <option value="Mensualidad con recargo">Mensualidad con recargo</option>
                                    <option value="Seguro escolar con recargo">Seguro escolar con recargo</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Mes a pagar</label>
                                <select name="mes" class="form-select">
                                    <option value="">Selecciona un mes</option>
                                    <option value="Enero">Enero</option>
                                    <option value="Febrero">Febrero</option>
                                    <option value="Marzo">Marzo</option>
                                    <option value="Abril">Abril</option>
                                    <option value="Mayo">Mayo</option>
                                    <option value="Junio">Junio</option>
                                    <option value="Julio">Julio</option>
                                    <option value="Agosto">Agosto</option>
                                    <option value="Septiembre">Septiembre</option>
                                    <option value="Octubre">Octubre</option>
                                    <option value="Noviembre">Noviembre</option>
                                    <option value="Diciembre">Diciembre</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Cantidad</label>
                                <input type="number" step="any" name="cantidad" id="cantidad" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3" id="divRecargos">
                                <label class="form-label text-muted">Recargos</label>
                                <input type="number" step="any" name="recargos" id="recargos" class="form-control" onblur="validarPorcentaje()">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Modo de pago</label>
                                <select name="modoPago" class="form-select" required>
                                    <option value="">Selecciona un concepto</option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                    <option value="Tarjeta de débito">Tarjeta de débito</option>
                                    <option value="Depósito">Depósito</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Transferencia">Transferencia</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Nota</label>
                                <input type="text" name="nota" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="file" name="archivo_comprobante" id="inputFoto" class="form-control" accept="image/*" required>
                            <div class="mt-2 text-center text-muted" id="previewContainer">
                                Espera a que muestre la imágen
                            </div>
                            <img id="previewFoto" src="" style="width: 100%; max-width: 350px; display: none; margin: 10px auto;">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-success"><i class="bx bx-plus"></i> Registrar Pago</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?= $this->include("partials/vendor-scripts") ?>

    <script>
        // 1. Ocultar recargos si coincide con el permiso (getComboA)
        function verificarRecargos() {
            var concepto = document.getElementById('concepto').value;
            var permiso = document.getElementById('conceptoAnterior').value;
            var divRecargos = document.getElementById('divRecargos');
            var inputRecargos = document.getElementById('recargos');

            if (concepto == permiso) {
                divRecargos.style.display = 'none';
                inputRecargos.value = 0; // Limpiar valor
                inputRecargos.removeAttribute('required');
            } else {
                divRecargos.style.display = 'block';
                // En el legacy tenía 'required', lo ponemos de nuevo si es necesario
                inputRecargos.setAttribute('required', 'required');
            }
        }

        // 2. Validar el 10% (porcentaje)
        function validarPorcentaje() {
            var recargo = parseFloat(document.getElementById('recargos').value) || 0;
            var cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
            var minimo = cantidad * 0.10;

            if (recargo < minimo && recargo > 0) {
                alert('El pago no se hará valido si no se aplica el valor del recargo');
                document.getElementById('recargos').focus();
                document.getElementById('recargos').value = minimo.toFixed(2);
            }
        }

        // 3. Previsualización de Imagen (seleccionado_principal)
        document.getElementById('inputFoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewFoto').src = e.target.result;
                    document.getElementById('previewFoto').style.display = 'block';
                    document.getElementById('previewContainer').innerHTML = "Listo";
                }
                reader.readAsDataURL(file);
            }
        });

        // Inicializar estado de recargos al cargar (por si acaso)
        document.addEventListener("DOMContentLoaded", function() {
            // Lógica inicial para recargos basada en la fecha (legacy: if dia <= 4 ocultar)
            // En tu código viejo tenías esto comentado/activo:
            var dia = new Date().getDate();
            if (dia <= 4) {
                document.getElementById('divRecargos').style.display = 'none';
                document.getElementById('recargos').removeAttribute('required');
            }
        });
    </script>
</body>
</html>