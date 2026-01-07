<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?php
        echo view("partials/title-meta", array("title" => $title ?? 'Preinscripciones')); 
    ?>
    <?= $this->include("partials/head-css") ?>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <h4 class="page-title">Módulo de Preinscripciones</h4>
                        <p class="text-muted mb-4">Formulario para realizar una nueva preinscripción.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <?php if (session()->getFlashdata('success')): ?>
                                    <div class="alert alert-success text-center">
                                        <i class="mdi mdi-check-circle"></i> <?= nl2br(esc(session()->getFlashdata('success'))) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (session()->getFlashdata('error')): ?>
                                    <div class="alert alert-danger text-center">
                                        <i class="mdi mdi-alert-circle"></i> <?= session()->getFlashdata('error') ?>
                                    </div>
                                <?php endif; ?>

                                <form method="post" action="<?= base_url('alumnos/guardar') ?>">
                                    
                                    <input type="hidden" name="estatus" value="2">
                            
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" name="Nombre" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Apellido Paterno</label>
                                                <input type="text" name="ap_Alumno" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Apellido Materno</label>
                                                <input type="text" name="am_Alumno" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">CURP</label>
                                                <input type="text" name="curp" id="curp" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">RFC</label>
                                                <input type="text" name="rfc" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">NIA</label>
                                                <input type="text" name="nia" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Fecha de Nacimiento</label>
                                                <input type="date" name="fechaNacAlumno" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Sexo del alumno</label>
                                                <select name="sexo_alum" class="form-control">
                                                    <option value="">Seleccionar</option>
                                                    <option value="Masculino">Masculino</option>
                                                    <option value="Femenino">Femenino</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Email Tutor</label>
                                                <input type="email" name="email_tutor" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Dirección</label>
                                                <input type="text" name="direccion" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Código Postal</label>
                                                <input type="text" name="cp_alum" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Teléfono</label>
                                                <input type="text" name="telefono_alum" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-3 rounded mb-3 border">
                                        <div class="col-12 mb-2">
                                            <h5 class="text-primary"><i class="mdi mdi-robot"></i> Asignación de Matrícula</h5>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label fw-bold">Matrícula</label>
                                                <input type="text" class="form-control" disabled 
                                                       placeholder="Se generará al guardar" 
                                                       style="background-color: #e9ecef; font-weight: bold; color: #555;">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label fw-bold">Email institucional</label>
                                                <input type="text" class="form-control" disabled 
                                                       placeholder="matricula@sjs.edu.mx"
                                                       style="background-color: #e9ecef;">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label fw-bold">Contraseña por defecto</label>
                                                <input type="text" class="form-control" value="123456789" readonly 
                                                       style="background-color: #e9ecef;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Grado de Preinscripción</label>
                                                <select name="grado" class="form-control">
                                                    <option value="">Seleccionar Grado</option>
                                                    <?php foreach ($grados as $grado): ?>
                                                        <option value="<?= $grado['id_grado'] ?>">
                                                            <?= esc($grado['nombreGrado']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Ciclo escolar</label>
                                                <select name="cicloEscolar" class="form-control" required>
                                                    <option value="">Seleccionar Ciclo Escolar</option>
                                                    <?php foreach ($ciclos as $ciclo): ?>
                                                        <option value="<?= $ciclo['id_cicloEscolar'] ?>">
                                                            <?= esc($ciclo['nombreCicloEscolar']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Notas Extra</label>
                                                <textarea name="extra" rows="3" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-start mt-3">
                                        <button type="submit" class="btn btn-success px-4 py-2">
                                            <i class="mdi mdi-content-save"></i> Guardar Preinscripción
                                        </button>
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
    
    <?= $this->include("partials/vendor-scripts") ?>

</body>
</html>