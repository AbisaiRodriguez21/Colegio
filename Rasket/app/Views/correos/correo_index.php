<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">
<head>
    <?= view("partials/title-meta", ["title" => $titulo]) ?>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        body.modal-open .wrapper { filter: blur(5px); transition: filter 0.3s; }
        .email-modal-content { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border-radius: 12px; overflow: hidden; }
        .email-modal-header { padding: 1.2rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
        #modalAsunto { color: #000; font-weight: 700; font-size: 1.1rem; }
        .btn-close-custom { cursor: pointer; font-size: 1.4rem; opacity: 0.6; transition: all 0.2s; }
        .btn-close-custom:hover { opacity: 1; color: #dc3545; transform: scale(1.1); }

        /* Dark Mode */
        [data-bs-theme="dark"] .email-modal-content { background-color: #2a3042; color: #e9ecef; }
        [data-bs-theme="dark"] .email-modal-header { background-color: #32394e; border-bottom: 1px solid #3b4257; }
        [data-bs-theme="dark"] #modalAsunto { color: #fff !important; }
        [data-bs-theme="dark"] .btn-close-custom { color: #fff; }
        
        /* Columnas de la tabla */
        .check-col { width: 40px; text-align: center; vertical-align: middle; }
        .star-col { width: 40px; text-align: center; vertical-align: middle; }
        .action-bar { background: #f8f9fa; border-bottom: 1px solid #eff2f7; padding: 10px 15px; border-radius: 5px 5px 0 0; }
        [data-bs-theme="dark"] .action-bar { background: #32394e; border-bottom: 1px solid #3b4257; }
    </style>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>
        <div class="page-content">
            <div class="container-fluid">
                
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="page-title-box"><h4 class="page-title"><?= esc($titulo) ?></h4></div>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?= session()->getFlashdata('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= session()->getFlashdata('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-grid">
                                    <a href="<?= base_url('correo/redactar') ?>" class="btn btn-danger btn-block text-white"><i class="bx bx-plus me-1"></i> Redactar Nuevo</a>
                                </div>
                                <div class="mail-list mt-4">
                                    <a href="<?= base_url('correo?filtro=recibidos') ?>" class="list-group-item border-0 <?= ($filtro_actual == 'recibidos') ? 'text-danger fw-bold active bg-light' : '' ?>">
                                        <i class="bx bxs-inbox me-2"></i> Recibidos
                                    </a>
                                    <a href="<?= base_url('correo?filtro=enviados') ?>" class="list-group-item border-0 <?= ($filtro_actual == 'enviados') ? 'text-danger fw-bold active bg-light' : '' ?>">
                                        <i class="bx bx-paper-plane me-2"></i> Enviados
                                    </a>
                                    <a href="<?= base_url('correo?filtro=archivados') ?>" class="list-group-item border-0 <?= ($filtro_actual == 'archivados') ? 'text-danger fw-bold active bg-light' : '' ?>">
                                        <i class="bx bx-archive-in me-2"></i> Archivados
                                    </a>
                                    <a href="<?= base_url('correo?filtro=destacados') ?>" class="list-group-item border-0 <?= ($filtro_actual == 'destacados') ? 'text-danger fw-bold active bg-light' : '' ?>">
                                        <i class="bx bx-star me-2"></i> Destacados
                                    </a>
                                    <a href="<?= base_url('correo?filtro=papelera') ?>" class="list-group-item border-0 <?= ($filtro_actual == 'papelera') ? 'text-danger fw-bold active bg-light' : '' ?>">
                                        <i class="bx bx-trash me-2"></i> Papelera
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="card">
                            <div class="card-body p-0">
                                
                                <form action="<?= base_url('correo/acciones') ?>" method="post" id="formAcciones">
                                    
                                    <div class="action-bar d-flex align-items-center gap-3">
                                        
                                        <div class="form-check font-size-16 ms-1">
                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                        </div>

                                        <div class="btn-group">
                                            
                                            <?php if($filtro_actual == 'papelera'): ?>
                                                <button type="submit" name="accion" value="restaurar" class="btn btn-light" title="Restaurar a Bandeja">
                                                    <i class="bx bx-undo text-success"></i>
                                                </button>
                                                <button type="submit" name="accion" value="eliminar_total" class="btn btn-light" title="Eliminar para siempre" onclick="return confirm('¿Seguro que deseas eliminar permanentemente?')">
                                                    <i class="bx bx-x text-danger"></i>
                                                </button>

                                            <?php elseif($filtro_actual == 'archivados'): ?>
                                                <button type="submit" name="accion" value="desarchivar" class="btn btn-light" title="Desarchivar (Mover a Bandeja)">
                                                    <i class="bx bx-archive-out text-primary"></i>
                                                </button>
                                                <button type="submit" name="accion" value="papelera" class="btn btn-light" title="Mover a Papelera">
                                                    <i class="bx bx-trash"></i>
                                                </button>

                                            <?php else: ?>
                                                <button type="submit" name="accion" value="papelera" class="btn btn-light" title="Mover a Papelera">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <button type="submit" name="accion" value="archivar" class="btn btn-light" title="Archivar">
                                                    <i class="bx bx-archive-in"></i>
                                                </button>

                                            <?php endif; ?>

                                            <button type="submit" name="accion" value="destacar" class="btn btn-light text-warning" title="Destacar seleccionados">
                                                <i class="bx bxs-star"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <tbody>
                                                <?php if (!empty($correos)): ?>
                                                    <?php foreach ($correos as $c): ?>
                                                    
                                                    <tr style="cursor: pointer;" onclick="abrirCorreo(<?= $c['id'] ?>)">
                                                        
                                                        <td class="check-col" onclick="event.stopPropagation()">
                                                            <div class="form-check font-size-16">
                                                                <input type="checkbox" class="form-check-input check-item" name="ids[]" value="<?= $c['id'] ?>">
                                                            </div>
                                                        </td>

                                                        <td class="star-col" onclick="event.stopPropagation()">
                                                            <?php if($c['destacado']): ?>
                                                                <button type="submit" name="accion" value="no_destacar" onclick="setSingleId(<?= $c['id'] ?>)" class="btn btn-link p-0 border-0">
                                                                    <i class="bx bxs-star text-warning font-size-16"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="submit" name="accion" value="destacar" onclick="setSingleId(<?= $c['id'] ?>)" class="btn btn-link p-0 border-0">
                                                                    <i class="bx bx-star font-size-16 text-muted"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="fw-bold text-dark w-25">
                                                            <?= esc($c['para']) ?>
                                                        </td>
                                                        <td>
                                                            <?php if($c['estado'] == 'error_envio'): ?>
                                                                <span class="badge bg-danger me-1">Falló</span>
                                                            <?php endif; ?>
                                                            <?= esc($c['asunto']) ?>
                                                            <span class="text-muted small"> - <?= substr(esc($c['mensaje']), 0, 35) ?>...</span>
                                                        </td>
                                                        <td class="text-end text-muted">
                                                            <?php if($c['adjunto']): ?><i class="bx bx-paperclip me-1"></i><?php endif; ?>
                                                            <?= date('d M', strtotime($c['fecha_envio'])) ?>
                                                        </td>
                                                    </tr>
                                                    
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center py-5">
                                                            <div class="text-muted">
                                                                <i class="bx bx-folder-open display-4"></i><br>
                                                                <h5>Bandeja vacía</h5>
                                                                <p>No hay correos en esta sección.</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    </form> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= $this->include("partials/footer") ?>
        </div>
    </div>

    <div class="modal fade" id="modalLeerCorreo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content email-modal-content">
                <div class="email-modal-header">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-x btn-close-custom me-3" data-bs-dismiss="modal"></i>
                        <h5 class="m-0" id="modalAsunto">...</h5>
                    </div>
                    <small class="text-muted" id="modalFecha"></small>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex mb-4 align-items-center">
                        <div class="avatar-sm me-3"><span class="avatar-title rounded-circle bg-soft-primary text-primary fs-5"><i class="bx bx-user"></i></span></div>
                        <div class="flex-grow-1"><h6 class="m-0">Para: <span id="modalPara" class="text-primary"></span></h6><small>Estado: <span id="modalEstado"></span></small></div>
                    </div>
                    <div class="email-body text-secondary" style="min-height: 150px; line-height: 1.6;"><p id="modalMensaje"></p></div>
                    <div id="seccionAdjunto" class="mt-4" style="display: none;">
                        <a href="#" id="linkAdjunto" target="_blank" class="btn btn-sm btn-light border"><i class="bx bxs-download me-1"></i> Descargar Adjunto</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>

    <script>
        // Checkbox Maestro
        document.getElementById('checkAll').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.check-item');
            for (var checkbox of checkboxes) checkbox.checked = this.checked;
        });

        // Marcar uno solo para acciones rápidas (ej: estrella)
        function setSingleId(id) {
            var checkboxes = document.querySelectorAll('.check-item');
            for (var checkbox of checkboxes) checkbox.checked = false;
            var target = document.querySelector('.check-item[value="'+id+'"]');
            if(target) target.checked = true;
        }

        // Abrir Modal
        function abrirCorreo(id) {
            var myModal = new bootstrap.Modal(document.getElementById('modalLeerCorreo'));
            document.getElementById('modalAsunto').innerText = 'Cargando...';
            document.getElementById('modalMensaje').innerText = '';
            document.getElementById('seccionAdjunto').style.display = 'none';
            myModal.show();

            fetch('<?= base_url("correo/ajax_ver/") ?>' + id)
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        document.getElementById('modalAsunto').innerText = data.asunto;
                        document.getElementById('modalFecha').innerText = data.fecha_formateada;
                        document.getElementById('modalPara').innerText = data.para;
                        document.getElementById('modalEstado').innerText = data.estado;
                        document.getElementById('modalMensaje').innerHTML = data.mensaje.replace(/\n/g, "<br>");
                        if (data.url_adjunto) {
                            document.getElementById('seccionAdjunto').style.display = 'block';
                            document.getElementById('linkAdjunto').href = data.url_adjunto;
                        }
                    }
                });
        }
    </script>
</body>
</html>