<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Dashboard Titular"]); ?>
    <?= $this->include("partials/head-css") ?>

    <style>
        .widget-card {
            border: 1px solid #e6e6e6;
            background: #fff;
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 5px;
        }

        .widget-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .widget-header {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
        }

        .widget-body {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100px;
        }

        .widget-title {
            font-size: 13px;
            color: #999;
            text-transform: uppercase;
            font-weight: 500;
        }

        .widget-icon {
            font-size: 40px;
            color: #5c6bc0;
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title">Panel del Titular</h4>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div style="background-image: url('<?= base_url('images/photos/photo3@2xz.jpg') ?>'); background-size: cover; background-position: center; border-radius: 8px; position: relative; overflow: hidden; min-height: 160px; display: flex; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            
                            <div style="background-color: rgba(30, 35, 40, 0.65); position: absolute; top: 0; left: 0; right: 0; bottom: 0;"></div>
                            
                            <div style="position: relative; z-index: 1; padding: 30px;">
                                <h1 class="text-white mb-1" style="font-size: 26px; font-weight: 700; letter-spacing: 0.5px;">Bienvenido(a) Profesor(a)</h1>
                                <h2 class="text-white-50 mb-2" style="font-size: 16px; font-weight: 500;">
                                    <?= esc($nombre) ?> <?= esc($apellidos) ?>
                                </h2>
                                
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row">
                    
                    <div class="col-md-6 col-lg-3">
                        <a href="<?= base_url('titular/mi-grupo') ?>" class="text-decoration-none">
                            <div class="widget-card">
                                <div class="widget-header">
                                    <span>Ver Boleta</span>
                                    <i class="mdi mdi-eye-outline"></i>
                                </div>
                                <div class="widget-body">
                                    <span class="widget-title" style="color:#5c6bc0; font-weight:bold;"><?= esc($grupoAsignado) ?></span>
                                    <i class='bx bx-table widget-icon'></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <a href="<?= base_url('titular/calificar') ?>" class="text-decoration-none">
                            <div class="widget-card">
                                <div class="widget-header">
                                    <span>Calificar Boleta</span>
                                    <i class="mdi mdi-pencil-outline"></i>
                                </div>
                                <div class="widget-body">
                                    <span class="widget-title" style="color:#5c6bc0; font-weight:bold;"><?= esc($grupoAsignado) ?></span>
                                    <i class='bx bx-edit widget-icon' style="color:#5c6bc0;"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalPassword" class="text-decoration-none">
                            <div class="widget-card">
                                <div class="widget-header">
                                    <span>Cambiar Contraseña</span>
                                    <i class="mdi mdi-lock-reset"></i>
                                </div>
                                <div class="widget-body">
                                    <span class="widget-title">Cambiar Contraseña</span>
                                    <i class='bx bx-lock-open-alt widget-icon'></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <a href="https://classroom.google.com" target="_blank" class="text-decoration-none">
                            <div class="widget-card">
                                <div class="widget-header">
                                    <span>Classroom</span>
                                    <i class="mdi mdi-google-classroom"></i>
                                </div>
                                <div class="widget-body">
                                    <span class="widget-title">Classroom</span>
                                    <i class='bx bxl-google widget-icon'></i>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>

            </div> 
        </div> 

        <?= $this->include("partials/footer") ?>
    </div>

    <div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center" style="background-color: #252b31; border-bottom: 1px solid #323940; padding: 15px 20px;">
                    <h5 class="modal-title text-white m-0" style="font-size: 15px; font-weight: 600; letter-spacing: 0.5px;">Cambiar Contraseña</h5>
                    <button type="button" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; padding: 0; margin-left: auto; display: flex; align-items: center; cursor: pointer;">
                        <i class='bx bx-x' style="color: #f46a6a; font-size: 24px;"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="alertPass" class="alert alert-success d-none text-center" role="alert" style="font-size: 12px; padding: 5px;"></div>

                    <form id="formChangePass">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Contraseña Actual / Nueva</label>
                            <input type="text" class="form-control text-center font-weight-bold" id="inputNewPass" name="new_password" value="<?= esc($passwordActual ?? '') ?>" autocomplete="off">
                            <div class="text-danger mt-1" style="font-size: 11px;">
                                <i class="mdi mdi-alert-circle-outline"></i> Prohibido usar espacios
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-sm" id="btnGuardarPass">
                            <i class="mdi mdi-content-save"></i> Guardar Cambio
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('formChangePass');
            const inputPass = document.getElementById('inputNewPass');
            const btn = document.getElementById('btnGuardarPass');
            const alertBox = document.getElementById('alertPass');

            // 1. Bloqueo de la tecla espacio
            inputPass.addEventListener('keydown', function(e) {
                if (e.key === ' ' || e.code === 'Space') {
                    e.preventDefault(); 
                }
            });

            // 2. Respaldo por si pegan texto con espacios
            inputPass.addEventListener('input', function() {
                if (this.value.includes(' ')) {
                    this.value = this.value.replace(/\s/g, '');
                }
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
                alertBox.classList.add('d-none');

                const formData = new FormData(form);

                fetch('<?= base_url("titular/actualizar-password") ?>', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger');

                        if (data.status === 'success') {
                            alertBox.classList.add('alert-success');
                            alertBox.innerHTML = '<i class="mdi mdi-check-circle"></i> ' + data.msg;

                            setTimeout(() => {
                                const modalEl = document.getElementById('modalPassword');
                                const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                modalInstance.hide();

                                btn.disabled = false;
                                btn.innerHTML = '<i class="mdi mdi-content-save"></i> Guardar Cambio';
                                alertBox.classList.add('d-none');
                            }, 1500);

                        } else {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="mdi mdi-content-save"></i> Guardar Cambio';
                            alertBox.classList.add('alert-danger');
                            alertBox.textContent = data.msg;
                        }
                    })
                    .catch(error => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="mdi mdi-content-save"></i> Guardar Cambio';
                        alertBox.classList.remove('d-none');
                        alertBox.classList.add('alert-danger');
                        alertBox.textContent = "Error de conexión.";
                    });
            });
        });
    </script>
</body>
</html>