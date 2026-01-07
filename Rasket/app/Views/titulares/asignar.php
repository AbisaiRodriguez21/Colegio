<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Asignar Titulares"]); ?>
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
                            <h4 class="page-title">Asignar Titulares</h4>
                        </div>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-4">Datos del Nuevo Titular</h4>

                                <form action="<?= base_url('asignar-titulares/guardar') ?>" method="post">
                                    
                                    <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-circle me-1"></i> Información Personal</h5>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Nombre </label>
                                            <input type="text" class="form-control" name="nombre" value="<?= old('nombre') ?>" >
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Apellido Paterno </label>
                                            <input type="text" class="form-control" name="paterno" value="<?= old('paterno') ?>" >
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Apellido Materno</label>
                                            <input type="text" class="form-control" name="materno" value="<?= old('materno') ?>">
                                        </div>
                                    </div>

                                    <h5 class="mb-3 text-uppercase bg-light p-2 mt-2"><i class="mdi mdi-school me-1"></i> Asignación Académica</h5>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Grado / Nivel a Asignar <span class="text-danger">*</span></label>
                                            <select class="form-select" name="nivelT" id="selectGrado" required>
                                                <option value="">Seleccione...</option>
                                                
                                                <optgroup label="Coordinaciones Generales">
                                                    <?php 
                                                        $esp = [
                                                            50 => 'Todo Primaria', 
                                                            51 => 'Todo Secundaria', 
                                                            52 => 'Todo Preparatoria'
                                                        ];
                                                        foreach ($esp as $val => $txt): 
                                                            // Verificamos si está ocupado
                                                            $bloqueado = in_array((string)$val, $ocupados);
                                                    ?>
                                                        <option value="<?= $val ?>" <?= $bloqueado ? 'disabled style="background-color:#e9ecef;"' : '' ?>>
                                                            <?= $txt ?> <?= $bloqueado ? '(Asignado)' : '' ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>

                                                <optgroup label="Grados Escolares">
                                                    <?php if (!empty($grados)): ?>
                                                        <?php foreach ($grados as $grado): 
                                                            $bloqueado = in_array((string)$grado['id_grado'], $ocupados);
                                                        ?>
                                                            <option value="<?= $grado['id_grado'] ?>" <?= $bloqueado ? 'disabled style="background-color:#e9ecef;"' : '' ?>>
                                                                <?= $grado['nombreGrado'] ?> <?= $bloqueado ? '(Asignado)' : '' ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </optgroup>
                                            </select>
                                            <div class="form-text text-muted">Los grados en gris ya tienen un titular activo.</div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Email Institucional</label>
                                            <input type="email" class="form-control bg-light" name="email" id="inputEmail" readonly required>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="password" required>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Guardar Titular
                                        </button>
                                    </div>

                                </form>

                            </div> </div> </div> </div> </div> <?= $this->include("partials/footer") ?>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectGrado = document.getElementById('selectGrado');
            const inputEmail = document.getElementById('inputEmail');

            selectGrado.addEventListener('change', function() {
                let opcion = selectGrado.options[selectGrado.selectedIndex];
                // Quitamos el texto "(Asignado)" si existiera para limpiar el nombre
                let textoOriginal = opcion.text.replace('(Asignado)', '').trim();
                let valor = selectGrado.value;

                if (valor) {
                    // Quitamos espacios y pasamos a minúsculas para el correo
                    // Ej: "1° Primaria" -> "1°primaria"
                    let limpio = textoOriginal.replace(/\s+/g, '').toLowerCase();
                    // Quitamos caracteres especiales para el correo
                    limpio = limpio.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); 
                    
                    inputEmail.value = `t-${limpio}@sjs.edu.mx`;
                } else {
                    inputEmail.value = '';
                }
            });
        });
    </script>
</body>
</html>