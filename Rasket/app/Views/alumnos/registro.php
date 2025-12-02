<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?php
        echo view("partials/title-meta", array("title" => $title ?? 'Registro de Alumno')); 
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
                        <h4 class="page-title">Registro de Alumnos</h4>
                        <p class="text-muted mb-4">Formulario para registrar un nuevo alumno en el sistema.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <?php if (session()->getFlashdata('success')): ?>
                                    <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
                                <?php endif; ?>

                                <form method="post" action="<?= base_url('alumnos/guardar') ?>">
                            
                                    <!-- (Nombre, Apellidos) -->
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" name="Nombre" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Apellido Paterno</label>
                                                <input type="text" name="ap_Alumno" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Apellido Materno</label>
                                                <input type="text" name="am_Alumno" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- (CURP, RFC, NIA) -->
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

                                    <!-- (Datos Personales) -->
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

                                    <!-- (Matrícula, Email, Contraseña) -->
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Matrícula</label>
                                                <input type="text" name="matricula" id="matricula" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Email institucional</label>
                                                <input type="email" name="email" id="email" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Contraseña</label>
                                                <input type="text" name="pass" id="pass" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- (Grados y Ciclos) -->
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Grado inicial del alumno</label>
                                                <select name="grado" class="form-control" required>
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
                                                <label class="form-label">Ciclo escolar activo</label>
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

                                    <!--  (Notas Extra) -->
                                    <div class="row">
                                        <div class="col-12 mb-4">
                                            <div class="form-group">
                                                <label class="form-label">Notas Extra</label>
                                                <textarea name="extra" rows="3" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botón de Guardar -->
                                    <div class="text-start mt-3">
                                        <button type="submit" class="btn btn-primary px-4 py-2">Guardar Registro</button>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const matriculaInput = document.getElementById('matricula');
            const emailInput = document.getElementById('email');
            const curpInput = document.getElementById('curp');
            const passInput = document.getElementById('pass');

            if (matriculaInput && emailInput) {
                matriculaInput.addEventListener('input', function() {
                    if (this.value.trim() === '') {
                        emailInput.value = '';
                    } else {
                        emailInput.value = this.value + '@sjs.edu.mx';
                    }
                });
            }

            if (curpInput && passInput) {
                curpInput.addEventListener('blur', function() {
                    passInput.value = this.value;
                });
            }

        });
    </script>

</body>
</html>