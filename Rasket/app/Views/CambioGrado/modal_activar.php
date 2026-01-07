<div class="modal fade" id="modal-activar" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="titulo-modal-activar">Reactivar Alumno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <form id="form-activar" class="needs-validation" novalidate>
                    <input type="hidden" id="activar_id_alumno" name="id_alumno">
                    
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h5 class="text-success mb-3"><i class="bx bx-book-bookmark"></i> Datos Académicos</h5>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Grado Actual (Registrado)</label>
                                <input type="text" class="form-control bg-light" id="activar_grado_actual_texto" readonly>
                                <input type="hidden" id="activar_grado_actual_id" name="grado_actual_id">
                            </div>

                            <div class="mb-3">
    <label class="form-label fw-bold">Grado Siguiente (Reinscripción) <span class="text-danger">*</span></label>
    <select class="form-select" id="activar_nuevo_grado" name="nuevo_grado" required>
        <option value="">Seleccione el nuevo grado...</option>
        <?php if(!empty($grados)): ?>
            <?php foreach($grados as $g): ?>
                <option value="<?= $g['id_grado'] ?>"><?= esc($g['nombreGrado']) ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
    
    <div id="alerta-repetidor" class="alert alert-warning mt-2 d-none py-1 px-2 font-size-12">
        <i class="bx bx-error"></i> <strong>Aviso:</strong> El alumno está repitiendo curso.
    </div>
</div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-primary mb-3"><i class="bx bx-money"></i> Registro de Pago</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Concepto</label>
                                <select class="form-select" name="concepto" required>
                                    <option value="Reinscripción" selected>Reinscripción</option>
                                    <option value="Inscripción">Inscripción</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Monto <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" name="cantidad" required min="1">
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" class="form-control" name="fecha_pago" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Método de Pago</label>
                                <select class="form-select" name="modo_pago" required>
                                    <option value="Efectivo" selected>Efectivo</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Depósito">Depósito</option>
                                    <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                    <option value="Tarjeta de débito">Tarjeta de débito</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nota (Opcional)</label>
                                <input type="text" class="form-control form-control-sm" name="nota" placeholder="Referencia o comentario">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Correo Confirmación</label>
                                <input type="email" class="form-control form-control-sm" id="activar_email" name="email">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-activacion">
                    <i class="bx bx-check-double"></i> Activar y Registrar Pago
                </button>
            </div>
        </div>
    </div>
</div>