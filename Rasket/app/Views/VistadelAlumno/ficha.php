<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">
<head>
    <?= view("partials/title-meta", ["title" => "Ficha del Alumno"]); ?>
    <?= $this->include("partials/head-css") ?>
</head>

<body>
    <div class="wrapper">
        
        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <h4 class="page-title mb-0 font-size-18">Ficha del Alumno</h4>
                            <div class="page-title-right">
                                <a href="<?= base_url('alumno/dashboard') ?>" class="btn btn-secondary btn-sm">
                                    <i class='bx bx-arrow-back'></i> Volver al Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (session()->getFlashdata('mensaje')): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class='bx bx-check-circle'></i> <?= session()->getFlashdata('mensaje') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                
                                <div class="mb-4 pb-2 border-bottom">
                                    <h5 class="card-title text-primary"><i class='bx bx-user-circle'></i> Datos Oficiales</h5>
                                </div>

                                <form action="<?= base_url('alumno/actualizar-ficha') ?>" method="POST">
                                    
                                    <div class="row bg-light p-3 mb-4 rounded">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Matrícula</label>
                                            <input type="text" class="form-control" value="<?= esc($alumno['matricula']) ?>" readonly>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Grado</label>
                                            <input type="text" class="form-control" value="<?= esc($alumno['nombreGrado']) ?>" readonly>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Email Institucional</label>
                                            <input type="text" class="form-control" value="<?= esc($alumno['email']) ?>" readonly>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">NIA</label>
                                            <input type="text" name="nia" class="form-control" value="<?= esc($alumno['nia']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Nombre(s)</label>
                                            <input type="text" name="Nombre" class="form-control" value="<?= esc($alumno['Nombre']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Apellido Paterno</label>
                                            <input type="text" name="ap_Alumno" class="form-control" value="<?= esc($alumno['ap_Alumno']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Apellido Materno</label>
                                            <input type="text" name="am_Alumno" class="form-control" value="<?= esc($alumno['am_Alumno']) ?>">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CURP</label>
                                            <input type="text" name="curp" class="form-control" value="<?= esc($alumno['curp']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">RFC</label>
                                            <input type="text" name="rfc" class="form-control" value="<?= esc($alumno['rfc']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Fecha de Nacimiento</label>
                                            <input type="date" name="fechaNacAlumno" class="form-control" value="<?= esc($alumno['fechaNacAlumno']) ?>">
                                        </div>
                                    </div>

                                    <h5 class="card-title text-primary mt-4 mb-3"><i class='bx bx-home'></i> Información de Contacto</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Dirección (Calle y número - Fracc. y/o colonia)</label>
                                            <input type="text" name="direccion_alum" class="form-control" value="<?= esc($alumno['direccion']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Código Postal</label>
                                            <input type="text" name="cp_alum" class="form-control" value="<?= esc($alumno['cp_alum']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Estado</label>
                                            <input type="text" name="estado" class="form-control" value="<?= esc($alumno['estado']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Teléfono Particular</label>
                                            <input type="text" name="telefono_alum" class="form-control" value="<?= esc($alumno['telefono_alum']) ?>">
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <h5 class="card-title text-primary mb-3"><i class='bx bx-male'></i> Datos del Padre o Tutor</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombre Completo</label>
                                            <input type="text" name="p_nombre" class="form-control" value="<?= esc($alumno['p_nombre']) ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Parentesco</label>
                                            <input type="text" name="p_parentesco" class="form-control" value="<?= esc($alumno['p_parentesco']) ?>">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Domicilio</label>
                                            <input type="text" name="p_domicilio" class="form-control" value="<?= esc($alumno['p_domicilio']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Teléfono Celular</label>
                                            <input type="text" name="p_celular" class="form-control" value="<?= esc($alumno['p_celular']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Teléfono Particular</label>
                                            <input type="text" name="p_tel_particular" class="form-control" value="<?= esc($alumno['p_tel_particular']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Email Particular</label>
                                            <input type="email" name="p_mail" class="form-control" value="<?= esc($alumno['p_mail']) ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Empresa en la que labora</label>
                                            <input type="text" name="p_empresa" class="form-control" value="<?= esc($alumno['p_empresa']) ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cargo</label>
                                            <input type="text" name="p_cargo" class="form-control" value="<?= esc($alumno['p_cargo']) ?>">
                                        </div>
                                        
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Último grado de estudios</label>
                                            <input type="text" name="p_ultimogradoestudios" class="form-control" value="<?= esc($alumno['p_ultimogradoestudios']) ?>">
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <h5 class="card-title text-primary mb-3"><i class='bx bx-female'></i> Datos de la Madre</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombre Completo</label>
                                            <input type="text" name="m_nombre" class="form-control" value="<?= esc($alumno['m_nombre']) ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Parentesco</label>
                                            <input type="text" name="m_parentesco" class="form-control" value="<?= esc($alumno['m_parentesco']) ?>">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Domicilio</label>
                                            <input type="text" name="m_domicilio" class="form-control" value="<?= esc($alumno['m_domicilio']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Teléfono Celular</label>
                                            <input type="text" name="m_celular" class="form-control" value="<?= esc($alumno['m_celular']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Teléfono Particular</label>
                                            <input type="text" name="m_tel_particular" class="form-control" value="<?= esc($alumno['m_tel_particular']) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Email Personal</label>
                                            <input type="email" name="m_mail" class="form-control" value="<?= esc($alumno['m_mail']) ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Empresa en la que labora</label>
                                            <input type="text" name="m_empresa" class="form-control" value="<?= esc($alumno['m_empresa']) ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cargo</label>
                                            <input type="text" name="m_cargo" class="form-control" value="<?= esc($alumno['m_cargo']) ?>">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Último grado de estudios</label>
                                            <input type="text" name="m_ultimogradoestudios" class="form-control" value="<?= esc($alumno['m_ultimogradoestudios']) ?>">
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="p-3 mb-4 rounded border border-danger bg-soft-danger">
                                        <h5 class="card-title text-danger mb-3"><i class='bx bx-plus-medical'></i> En caso de emergencia contactar a:</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-danger">Nombre del Contacto</label>
                                                <input type="text" name="e_nombre" class="form-control" value="<?= esc($alumno['e_nombre']) ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-danger">Teléfono Celular</label>
                                                <input type="text" name="e_telefono" class="form-control" value="<?= esc($alumno['e_telefono']) ?>">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label text-danger">Notas Extra (Alergias, tipo de sangre, etc.)</label>
                                                <textarea name="extra" class="form-control" rows="3"><?= esc($alumno['extra']) ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary px-5 py-2">
                                            <i class='bx bx-save'></i> Guardar Cambios
                                        </button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div> 
        </div> 

        <?= $this->include("partials/footer") ?>
    </div> 

    <?= $this->include("partials/vendor-scripts") ?>
</body>
</html>