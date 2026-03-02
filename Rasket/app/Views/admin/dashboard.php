<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Dashboard Administrador"]); ?>
    <?= $this->include("partials/head-css") ?>

    <style>
        /* -------------------------------------------------------------
           ESTILOS PARA LAS TARJETAS DE ACCESO RÁPIDO (FILA SUPERIOR)
           ------------------------------------------------------------- */
        .widget-card {
            border: 1px solid #e6e6e6;
            background: #fff;
            margin-bottom: 25px;
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 6px;
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
            height: 90px;
        }

        .widget-title {
            font-size: 13px;
            color: #5c6bc0;
            text-transform: uppercase;
            font-weight: 700;
        }

        .widget-icon {
            font-size: 38px;
            color: #5c6bc0;
            opacity: 0.9;
        }

        /* -------------------------------------------------------------
           ESTILOS PARA LAS TARJETAS DE CONTROL ACADÉMICO (LAS 3 GRANDES)
           ------------------------------------------------------------- */
        .control-card {
            border: 1px solid #e6e6e6;
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            margin-bottom: 30px;
        }

        .control-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .control-title {
            font-size: 13px;
            font-weight: 700;
            color: #555;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .nav-tabs .nav-link {
            color: #666;
            font-weight: 500;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 12px 20px;
        }

        .nav-tabs .nav-link:hover {
            color: #5c6bc0;
        }

        .nav-tabs .nav-link.active {
            color: #5c6bc0;
            border-bottom: 2px solid #5c6bc0;
            background: transparent;
        }

        .grupo-lista {
            list-style-type: disc;
            padding-left: 25px;
            margin-top: 15px;
        }

        .grupo-lista li {
            margin-bottom: 8px;
            color: #5c6bc0;
        }

        .grupo-lista a {
            text-decoration: none;
            color: #5c6bc0;
            font-size: 14px;
            transition: color 0.2s;
        }

        .grupo-lista a:hover {
            color: #3b488e;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-4 mt-2">
                    <div class="col-12">
                        <div style="background-image: url('<?= base_url('images/photos/photo3@2xz.jpg') ?>'); background-size: cover; background-position: center; border-radius: 8px; position: relative; min-height: 160px; display: flex; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            <div style="background-color: rgba(30, 35, 40, 0.65); position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 8px;"></div>
                            <div style="position: relative; z-index: 1; padding: 30px;">
                                <h1 class="text-white mb-1" style="font-size: 26px; font-weight: 700;">Bienvenido(a) Director(a)</h1>
                                <h2 class="text-white-50 mb-2" style="font-size: 16px;"><?= esc($nombre) ?> <?= esc($apellidos) ?></h2>
                                <span style="background-color: #5c6bc0; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                    Ciclo Escolar <?= esc($cicloEscolar) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <a href="<?= base_url('alumnos/registro') ?>" class="text-decoration-none">
                            <div class="widget-card">
                                <div class="widget-header">
                                    <span>Alta de Usuario</span>
                                    <i class="mdi mdi-account-plus-outline"></i>
                                </div>
                                <div class="widget-body">
                                    <span class="widget-title">Alta de Usuario</span>
                                    <i class='bx bx-user-plus widget-icon'></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <a href="<?= base_url('lista-profesores') ?>" class="text-decoration-none">
                            <div class="widget-card">
                                <div class="widget-header">
                                    <span>Lista De Profesores</span>
                                    <i class="mdi mdi-teach"></i>
                                </div>
                                <div class="widget-body">
                                    <span class="widget-title">Lista De Profesores</span>
                                    <i class='bx bx-group widget-icon'></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <a href="#" class="text-decoration-none">
                            <div class="widget-card">
                                <div class="widget-header">
                                    <span>Pagos</span>
                                    <i class="mdi mdi-cash-register"></i>
                                </div>
                                <div class="widget-body">
                                    <span class="widget-title">Pagos</span>
                                    <i class='bx bx-wallet widget-icon'></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalPassword" class="text-decoration-none">
                            <div class="widget-card">
                                <div class="widget-header">
                                    <span>Cambiar Contraseña</span>
                                    <i class="mdi mdi-shield-key-outline"></i>
                                </div>
                                <div class="widget-body">
                                    <span class="widget-title">Contraseña</span>
                                    <i class='bx bx-lock-open-alt widget-icon'></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <br>

                <div class="row">
                    
                    <div class="col-lg-6 col-xl-4">
                        <div class="control-card">
                            <div class="control-header">
                                <h5 class="control-title text-uppercase">VER BOLETA/IMPRIMIR</h5>
                                <i class="bx bx-expand" style="color:#ccc;"></i>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-tabs nav-justified" id="tabsBoleta" role="tablist">
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bol-kin"><i class="bx bx-book"></i> Kinder</a></li>
                                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#bol-pri"><i class="bx bx-book"></i> Primaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bol-sec"><i class="bx bx-book"></i> Secundaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bol-bac"><i class="bx bx-book"></i> Bachillerato</a></li>
                                </ul>
                                <div class="tab-content p-3">
                                    <div class="tab-pane fade" id="bol-kin">
                                        <ul class="grupo-lista">
                                            <?php foreach($kinder as $g): ?><li><a href="<?= base_url('boleta/lista/'.$g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade show active" id="bol-pri">
                                        <ul class="grupo-lista">
                                            <?php foreach($primaria as $g): ?><li><a href="<?= base_url('boleta/lista/'.$g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade" id="bol-sec">
                                        <ul class="grupo-lista">
                                            <?php foreach($secundaria as $g): ?><li><a href="<?= base_url('boleta/lista/'.$g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade" id="bol-bac">
                                        <ul class="grupo-lista">
                                            <?php foreach($bachillerato as $g): ?><li><a href="<?= base_url('boleta/lista/'.$g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-xl-4">
                        <div class="control-card">
                            <div class="control-header">
                                <h5 class="control-title text-uppercase">CALIFICAR BOLETA BIMESTRE</h5>
                                <i class="bx bx-table" style="color:#5c6bc0; font-size: 18px;"></i>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-tabs nav-justified" id="tabsSabana" role="tablist">
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sab-kin">Kinder</a></li>
                                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#sab-pri">Primaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sab-sec">Secundaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sab-bac">Bachillerato</a></li>
                                </ul>
                                <div class="tab-content p-3">
                                    <div class="tab-pane fade" id="sab-kin">
                                        <ul class="grupo-lista">
                                            <?php foreach ($kinder as $g): ?><li><a href="<?= base_url('boleta/calificar/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade show active" id="sab-pri">
                                        <ul class="grupo-lista">
                                            <?php foreach ($primaria as $g): ?><li><a href="<?= base_url('boleta/calificar/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade" id="sab-sec">
                                        <ul class="grupo-lista">
                                            <?php foreach ($secundaria as $g): ?><li><a href="<?= base_url('boleta/calificar/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade" id="sab-bac">
                                        <ul class="grupo-lista">
                                            <?php foreach ($bachillerato as $g): ?><li><a href="<?= base_url('boleta/calificar/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-xl-4">
                        <div class="control-card">
                            <div class="control-header">
                                <h5 class="control-title text-uppercase">CALIFICAR BOLETA TODO BIMESTRE</h5>
                                <i class="bx bx-expand" style="color:#ccc;"></i>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-tabs nav-justified" id="tabsLista" role="tablist">
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#lis-kin"><i class="bx bx-book"></i> Kinder</a></li>
                                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#lis-pri"><i class="bx bx-book"></i> Primaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#lis-sec"><i class="bx bx-book"></i> Secundaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#lis-bac"><i class="bx bx-book"></i> Bachillerato</a></li>
                                </ul>
                                <div class="tab-content p-3">
                                    <div class="tab-pane fade" id="lis-kin">
                                        <ul class="grupo-lista">
                                            <?php foreach($kinder as $g): ?><li><a href="<?= base_url('calificaciones_bimestre/lista/'.$g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade show active" id="lis-pri">
                                        <ul class="grupo-lista">
                                            <?php foreach($primaria as $g): ?><li><a href="<?= base_url('calificaciones_bimestre/lista/'.$g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade" id="lis-sec">
                                        <ul class="grupo-lista">
                                            <?php foreach($secundaria as $g): ?><li><a href="<?= base_url('calificaciones_bimestre/lista/'.$g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade" id="lis-bac">
                                        <ul class="grupo-lista">
                                            <?php foreach($bachillerato as $g): ?><li><a href="<?= base_url('calificaciones_bimestre/lista/'.$g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> </div> 
        </div> 

        <?= $this->include("partials/footer") ?>
    </div>

    <div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center" style="background-color: #252b31; border-bottom: 1px solid #323940; padding: 15px 20px;">
                    <h5 class="modal-title text-white m-0" style="font-size: 15px; font-weight: 600;">Cambiar Contraseña</h5>
                    <button type="button" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; padding: 0; margin-left: auto; display: flex; align-items: center; cursor: pointer;">
                        <i class='bx bx-x' style="color: #f46a6a; font-size: 24px;"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="alertPass" class="alert d-none text-center" role="alert" style="font-size: 12px; padding: 5px;"></div>

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

            inputPass.addEventListener('keydown', function(e) {
                if (e.key === ' ' || e.code === 'Space') e.preventDefault();
            });

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

                fetch('<?= base_url("actualizar-password") ?>', {
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
                                location.reload(); 
                            }, 400);
                        } else {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="mdi mdi-content-save"></i> Guardar Cambio';
                            alertBox.classList.add('alert-danger');
                            alertBox.textContent = data.msg;
                        }
                    })
                    .catch(error => {
                        btn.disabled = false;
                        btn.innerHTML = 'Guardar Cambio';
                        alertBox.classList.remove('d-none');
                        alertBox.classList.add('alert-danger');
                        alertBox.textContent = "Error de conexión.";
                    });
            });
        });
    </script>
</body>
</html>