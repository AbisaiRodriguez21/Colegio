<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Calificar Materia"]); ?>
    <?= $this->include("partials/head-css") ?>
</head>

<body>
    <div class="wrapper">

        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <!-- Encabezado con Botón de Regreso -->
                <div class="row mb-3">
                    <div class="col-12 d-sm-flex align-items-center justify-content-between">
                        <div class="page-title-box">
                            <h4 class="page-title">
                                <span class="text-primary"><?= esc($materia['nombre_materia']) ?></span> 
                                <span class="text-muted fw-normal" style="font-size: 14px;">| <?= esc($grado['nombreGrado']) ?></span>
                            </h4>
                        </div>
                        <a href="<?= base_url('profesor/dashboard') ?>" class="btn btn-sm btn-secondary shadow-sm">
                            <i class="mdi mdi-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>

                <!-- Tabla de Calificaciones -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0" id="tablaCalificaciones">
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>Matrícula</th>
                                                <th class="text-start">Nombre Completo</th>
                                                <!-- Columnas Dinámicas -->
                                                <?php foreach ($periodos as $p): ?>
                                                    <th><?= esc($p['nombre']) ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <?php if(!empty($alumnos)): ?>
                                                <?php $i = 1; foreach ($alumnos as $al): ?>
                                                    <tr>
                                                        <td class="text-muted"><?= $i++ ?></td>
                                                        <td><span class="badge bg-light text-dark border"><?= esc($al['matricula']) ?></span></td>
                                                        <td class="text-start fw-medium">
                                                            <?= esc($al['ap_Alumno'] . ' ' . $al['am_Alumno'] . ' ' . $al['Nombre']) ?>
                                                        </td>
                                                        
                                                        <!-- Inputs Dinámicos -->
                                                        <?php foreach ($periodos as $p): ?>
                                                            <td>
                                                                <input type="number" 
                                                                    class="form-control form-control-sm text-center mx-auto" 
                                                                    step="0.1" min="0" max="10"
                                                                    style="width: 70px; font-weight: bold;"
                                                                    value="<?= isset($notasMap[$al['id']][$p['id']]) ? esc($notasMap[$al['id']][$p['id']]) : '' ?>"
                                                                    onblur="guardarNota(<?= $al['id'] ?>, <?= $grado['id_grado'] ?>, <?= $materia['Id_materia'] ?>, <?= $p['id'] ?>, this.value, this)">
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="<?= count($periodos) + 3 ?>" class="text-center text-muted py-4">
                                                        <i class="mdi mdi-account-off fs-3 d-block mb-2"></i>
                                                        No hay alumnos activos en este grupo.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> 
        </div> 

        <?= $this->include("partials/footer") ?>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
    
    <!-- Script AJAX para Guardar Calificaciones -->
    <script>
    function guardarNota(id_alumno, id_grado, id_materia, id_mes, valor, inputElement) {
        if (valor === '') return;

        let formData = new FormData();
        formData.append('id_alumno', id_alumno);
        formData.append('id_grado', id_grado);
        formData.append('id_materia', id_materia);
        formData.append('id_mes', id_mes);
        formData.append('valor', valor);

        inputElement.disabled = true;

        fetch('<?= base_url('profesor/guardar-nota') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            inputElement.disabled = false;
            
            if (data.status === 'success') {
                inputElement.style.backgroundColor = '#d4edda';
                inputElement.style.borderColor = '#c3e6cb';
                inputElement.style.color = '#155724';
                setTimeout(() => {
                    inputElement.style.backgroundColor = '';
                    inputElement.style.borderColor = '';
                    inputElement.style.color = '';
                }, 1500);
            } else {
                alert('Error: ' + (data.msg || 'No se pudo guardar la calificación.'));
                inputElement.style.backgroundColor = '#f8d7da';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            inputElement.disabled = false;
            alert('Ocurrió un error de conexión al servidor.');
        });
    }
    </script>
</body>
</html>