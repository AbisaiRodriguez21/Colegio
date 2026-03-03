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
                        <a href="<?= isset($es_admin) ? base_url('cambio-grado') : base_url('alumno/dashboard') ?>" class="btn btn-secondary btn-sm"><i class='bx bx-arrow-back'></i> Regresar</a>
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
                                                        <a href="<?= base_url('pagos/' . $p['ficha']) ?>" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm">
                                                            <i class='bx bx-image-alt fs-6 align-middle me-1'></i> Ver archivo
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border"><i class='bx bx-block'></i> Sin archivo</span>
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
                    <form action="<?= isset($es_admin) ? base_url('alumnos/guardar-pago-admin') : base_url('alumno/pagos/guardar') ?>" method="post" enctype="multipart/form-data" id="formPagosMultiples">
                        <input type="hidden" name="ciclo" value="<?= $ciclo ?>">
                        <?php if(isset($es_admin)): ?>
                            <input type="hidden" name="id_alumno" value="<?= esc($alumno['id']) ?>">
                        <?php endif; ?>

                        <div class="p-3 bg-light rounded border mb-3">
                            <h6 class="text-primary mb-3"><i class="bx bx-list-plus"></i> Añadir un pago a la lista</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Concepto</label>
                                    <select id="tmp_concepto" class="form-select">
                                        <option value="">Selecciona un concepto</option>
                                        <option value="Inscripción">Inscripción</option>
                                        <option value="Mensualidad">Mensualidad</option>
                                        <option value="Seguro Escolar">Seguro Escolar</option>
                                        <option value="Foto">Foto</option>
                                        <option value="Pago parcial mensualidad">Pago parcial mensualidad</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Mes a pagar</label>
                                    <select id="tmp_mes" class="form-select">
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
                                    <input type="number" step="any" id="tmp_cantidad" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Modo de pago</label>
                                    <select id="tmp_modoPago" class="form-select">
                                        <option value="">Selecciona un método</option>
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                        <option value="Tarjeta de débito">Tarjeta de débito</option>
                                        <option value="Depósito">Depósito</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Transferencia">Transferencia</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label text-muted">Nota</label>
                                    <input type="text" id="tmp_nota" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted">Comprobante de Pago</label>
                                <div id="contenedorArchivo">
                                    <input type="file" id="tmp_archivo" class="form-control" accept="image/*" onchange="mostrarPreview(this)">
                                </div>
                                <img id="previewFoto" src="" style="width: 100%; max-width: 250px; display: none; margin: 10px auto; border-radius: 8px;">
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-success" onclick="agregarAlCarrito()">
                                    <i class="bx bx-plus-circle"></i> Agregar Pago a la lista
                                </button>
                            </div>
                        </div>

                        <h6 class="text-dark"><i class="bx bx-receipt"></i> Pagos en este Folio</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Concepto / Mes</th>
                                        <th>Monto</th>
                                        <th>Modo</th>
                                        <th>Archivo</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="listaPagosBody">
                                    <tr id="filaVacia"><td colspan="5" class="text-center text-muted">Aún no hay pagos agregados</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" id="btnRegistrarFinal" disabled>Registrar Todo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>

    <script>
        // 1. Mostrar preview de la imagen
        function mostrarPreview(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewFoto').src = e.target.result;
                    document.getElementById('previewFoto').style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 2. Agregar el pago temporal a la tabla final
        let contadorPagos = 0;

        function agregarAlCarrito() {
            // Recoger valores
            let concepto = document.getElementById('tmp_concepto').value;
            let mes = document.getElementById('tmp_mes').value;
            let cantidad = document.getElementById('tmp_cantidad').value;
            let modo = document.getElementById('tmp_modoPago').value;
            let nota = document.getElementById('tmp_nota').value;
            let archivoInput = document.getElementById('tmp_archivo');

            // Validar que llenen lo necesario
            if (!concepto || !cantidad || !modo || archivoInput.files.length === 0) {
                alert("Por favor llena el Concepto, Cantidad, Método de pago y selecciona una Imagen.");
                return;
            }

            contadorPagos++;
            let total = parseFloat(cantidad);
            let nombreArchivo = archivoInput.files[0].name;
            let rowId = 'pago_row_' + contadorPagos;

            // Quitar la fila de "Vacío"
            let filaVacia = document.getElementById('filaVacia');
            if(filaVacia) filaVacia.remove();

            // Crear la fila visual y los inputs Ocultos que SÍ viajarán a PHP como arrays ([])
            let htmlRow = `
                <tr id="${rowId}">
                    <td>
                        <strong>${concepto}</strong><br><small class="text-muted">${mes}</small>
                        <input type="hidden" name="conceptos[]" value="${concepto}">
                        <input type="hidden" name="meses[]" value="${mes}">
                        <input type="hidden" name="notas[]" value="${nota}">
                    </td>
                    <td>
                        $${total.toFixed(2)}
                        <input type="hidden" name="cantidades[]" value="${cantidad}">
                    </td>
                    <td>
                        ${modo}
                        <input type="hidden" name="modos[]" value="${modo}">
                    </td>
                    <td id="celda_archivo_${rowId}">
                        <span class="badge bg-secondary"><i class='bx bx-image'></i> ${nombreArchivo}</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger text-white" onclick="eliminarFila('${rowId}')"><i class="bx bx-trash"></i></button>
                    </td>
                </tr>
            `;

            // Insertar en la tabla
            document.getElementById('listaPagosBody').insertAdjacentHTML('beforeend', htmlRow);

            // Truco maestro: Mover el input de archivo a la tabla y ocultarlo (para que viaje en el form)
            archivoInput.name = "archivos_comprobantes[]"; 
            archivoInput.style.display = "none";
            document.getElementById('celda_archivo_' + rowId).appendChild(archivoInput);

            // Crear un NUEVO input de archivo limpio para el siguiente pago
            let nuevoInputArchivo = document.createElement('input');
            nuevoInputArchivo.type = 'file';
            nuevoInputArchivo.id = 'tmp_archivo';
            nuevoInputArchivo.className = 'form-control';
            nuevoInputArchivo.accept = 'image/*';
            nuevoInputArchivo.onchange = function() { mostrarPreview(this); };
            document.getElementById('contenedorArchivo').appendChild(nuevoInputArchivo);

            // Limpiar formulario temporal
            document.getElementById('tmp_cantidad').value = '';
            document.getElementById('tmp_nota').value = '';
            document.getElementById('previewFoto').style.display = 'none';
            document.getElementById('previewFoto').src = '';

            // Activar botón de enviar
            document.getElementById('btnRegistrarFinal').disabled = false;
        }

        // Eliminar fila del carrito
        function eliminarFila(rowId) {
            document.getElementById(rowId).remove();
            let body = document.getElementById('listaPagosBody');
            if (body.children.length === 0) {
                body.innerHTML = '<tr id="filaVacia"><td colspan="5" class="text-center text-muted">Aún no hay pagos agregados</td></tr>';
                document.getElementById('btnRegistrarFinal').disabled = true;
            }
        }
    </script>
</body>
</html>