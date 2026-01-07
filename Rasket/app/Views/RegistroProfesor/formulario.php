<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Registro de Profesores"]); ?>
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
                            <h4 class="page-title">Registro de Profesores</h4>
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
                                <h4 class="header-title mb-4">Datos del Nuevo Profesor</h4>

                                <form action="<?= base_url('registro-profesor/guardar') ?>" method="post">
                                    
                                    <input type="hidden" name="nivel" value="5">

                                    <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-circle me-1"></i> Información Personal</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Nombre </label>
                                            <input type="text" class="form-control" name="Nombre" >
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Apellido Paterno </label>
                                            <input type="text" class="form-control" name="ap_Alumno" >
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Apellido Materno</label>
                                            <input type="text" class="form-control" name="am_Alumno">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CURP </label>
                                            <input type="text" class="form-control" name="curp" id="curp" >
                                            <div class="form-text text-muted">La contraseña se generará automáticamente con la CURP.</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">RFC</label>
                                            <input type="text" class="form-control" name="rfc">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Fecha de Nacimiento </label>
                                            <input type="date" class="form-control" name="fechaNacAlumno" >
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Sexo <span class="text-danger">*</span></label>
                                            <select class="form-select" name="sexo_alum" required>
                                                <option value="">Seleccione...</option>
                                                <option value="1">Hombre</option>
                                                <option value="2">Mujer</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" name="telefono">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Email </label>
                                            <input type="email" class="form-control" name="email" >
                                        </div>
                                    </div>

                                    <h5 class="mb-3 text-uppercase bg-light p-2 mt-2"><i class="mdi mdi-map-marker me-1"></i> Domicilio</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Estado </label>
                                            <select class="form-select" name="Estado" id="Estado" >
                                                <option value="">Seleccione Estado</option>
                                                <?php foreach ($estados as $edo): ?>
                                                    <option value="<?= $edo['id'] ?>"><?= $edo['nombre'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Municipio </label>
                                            <select class="form-select" name="Municipio" id="Municipio" disabled >
                                                <option value="">Primero seleccione Estado...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Localidad </label>
                                            <select class="form-select" name="localidad" id="localidad" disabled >
                                                <option value="">Primero seleccione Municipio...</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label">Dirección (Calle y Número)</label>
                                            <input type="text" class="form-control" name="direccion_alum">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Código Postal</label>
                                            <input type="text" class="form-control" name="cp_alum">
                                        </div>
                                    </div>

                                    <h5 class="mb-3 text-uppercase bg-light p-2 mt-2"><i class="mdi mdi-laptop me-1"></i> Datos del Sistema</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Matrícula (Generada Automáticamente)</label>
                                            <input type="text" class="form-control bg-light" name="matricula" value="<?= $matricula ?>">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Ciclo Escolar Activo </label>
                                            <select class="form-select" name="cescolar" >
                                                <option value="">Seleccione Ciclo...</option>
                                                <?php foreach ($ciclos as $ciclo): ?>
                                                    <option value="<?= $ciclo['id_cicloEscolar'] ?>"><?= $ciclo['nombreCicloEscolar'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Contraseña</label>
                                            <input type="text" class="form-control bg-light" name="pass" id="pass" value="123456789" readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Notas Extra</label>
                                            <textarea class="form-control" name="extra" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Guardar Profesor
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // Referencias DOM
            const curpInput = document.getElementById('curp');
            const passInput = document.getElementById('pass');
            const estadoSelect = document.getElementById('Estado');
            const muniSelect = document.getElementById('Municipio');
            const localSelect = document.getElementById('localidad');

            // 1. Contraseña Automática
            curpInput.addEventListener('blur', function() {
                if(this.value.trim() !== '') {
                    passInput.value = this.value;
                } else {
                    passInput.value = '123456789';
                }
            });

            // =========================================================
            // LÓGICA DE CARGA DINÁMICA
            // =========================================================

            // Función auxiliar para llenar cualquier select
            // Detecta automáticamente id/ID/Id y nombre/Nombre
            function llenarSelect(selectElement, data, defaultText) {
                selectElement.innerHTML = `<option value="">${defaultText}</option>`;
                
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(item => {
                        // BÚSQUEDA INTELIGENTE DE COLUMNAS
                        // Intenta encontrar el ID en varias variantes comunes
                        let valId = item.id || item.ID || item.Id || item.id_municipio || item.id_localidad;
                        // Intenta encontrar el NOMBRE
                        let valNombre = item.nombre || item.Nombre || item.municipio || item.localidad;

                        if (valId && valNombre) {
                            selectElement.innerHTML += `<option value="${valId}">${valNombre}</option>`;
                        }
                    });
                    selectElement.disabled = false; // Desbloquear
                } else {
                    selectElement.innerHTML = '<option value="">No hay datos disponibles</option>';
                    selectElement.disabled = true;
                }
            }

            // 2. CAMBIO DE ESTADO -> CARGAR MUNICIPIOS
            estadoSelect.addEventListener('change', function() {
                let idEstado = this.value;

                // Resetear hijos
                muniSelect.innerHTML = '<option value="">Cargando...</option>';
                muniSelect.disabled = true;
                localSelect.innerHTML = '<option value="">Primero seleccione Municipio...</option>';
                localSelect.disabled = true;

                if (idEstado) {
                    fetch('<?= base_url("registro-profesor/municipios") ?>/' + idEstado)
                        .then(response => {
                            if (!response.ok) throw new Error("Error en red");
                            return response.json();
                        })
                        .then(data => {
                            console.log("Municipios cargados:", data); // DEBUG EN CONSOLA
                            llenarSelect(muniSelect, data, "Seleccione Municipio");
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            muniSelect.innerHTML = '<option value="">Error al cargar</option>';
                        });
                } else {
                    muniSelect.innerHTML = '<option value="">Primero seleccione Estado...</option>';
                }
            });

            // 3. CAMBIO DE MUNICIPIO -> CARGAR LOCALIDADES
            muniSelect.addEventListener('change', function() {
                let idMuni = this.value;

                // Resetear hijo
                localSelect.innerHTML = '<option value="">Cargando...</option>';
                localSelect.disabled = true;

                if (idMuni) {
                    fetch('<?= base_url("registro-profesor/localidades") ?>/' + idMuni)
                        .then(response => {
                            if (!response.ok) throw new Error("Error en red");
                            return response.json();
                        })
                        .then(data => {
                            console.log("Localidades cargadas:", data); // DEBUG EN CONSOLA
                            llenarSelect(localSelect, data, "Seleccione Localidad");
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            localSelect.innerHTML = '<option value="">Error al cargar</option>';
                        });
                } else {
                    localSelect.innerHTML = '<option value="">Primero seleccione Municipio...</option>';
                }
            });
        });
    </script>
</body>
</html>