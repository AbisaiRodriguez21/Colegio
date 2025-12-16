<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sábana de Calificaciones</title>
    <?= $this->include("partials/head-css") ?>

    <style>
    /* --- ESTILOS GENERALES --- */
    body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

    .sabana-container {
        max-width: 100%;
        height: 85vh;
        overflow: auto;
        position: relative;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        margin: 10px;
        border: 1px solid #ddd;
    }

    table.tabla-sabana {
        border-collapse: separate;
        border-spacing: 0;
        width: max-content;
        font-size: 12px;
    }

    .tabla-sabana th, .tabla-sabana td {
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        padding: 0;
        text-align: center;
        vertical-align: middle;
    }

    /* --- CABECERA AUTOMÁTICA --- */
    
    .tabla-sabana thead tr {
        /* Esto es vital: permite que la fila crezca según el contenido */
        height: auto;
    }

    .tabla-sabana thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        height: auto; /* IMPORTANTE: Ya no usamos pixeles fijos */
        border-bottom: 2px solid #000;
        vertical-align: bottom; /* Todo el texto pegado al fondo */
        padding: 5px 0;
    }

    /* EL CONTENEDOR MÁGICO */
    .vertical-wrapper {
        /* Esta propiedad hace que el texto ocupe altura real */
        writing-mode: vertical-rl; 
        
        /* Rotamos 180 grados para que se lea de abajo hacia arriba */
        transform: rotate(180deg);
        
        /* Ajustes de alineación */
        text-align: left; /* Al rotar, left es 'abajo' */
        margin: 0 auto;   /* Centrado horizontal */
        padding: 10px 4px; /* Aire para que no se pegue a los bordes */
        width: 100%;
        display: block;
        box-sizing: border-box;
    }

    /* CLASES DE COLOR */
    .header-category {
        background: #2d3436;
        color: white;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-weight: bold;
        font-size: 13px;
        text-transform: uppercase;
    }

    .header-subject {
        background: #f8f9fa;
        color: #333;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
    }

    /* ESQUINA ALUMNO */
    .th-corner {
        background: #fff;
        color: #000;
        position: sticky;
        left: 0;
        z-index: 20 !important;
        border-right: 2px solid #000;
        vertical-align: middle !important;
        padding: 10px !important;
        width: 300px;
        min-width: 300px;
        /* La esquina se adaptará a la altura que definan las materias */
    }

    /* COLUMNA NOMBRES */
    .tabla-sabana tbody td.col-nombre {
        position: sticky;
        left: 0;
        z-index: 5;
        background: #fff;
        text-align: left;
        font-weight: bold;
        padding: 5px 10px;
        border-right: 2px solid #000;
        width: 300px;
        min-width: 300px;
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* CELDAS VACÍAS (TBODY) */
    .col-sep { background: #e0e0e0; border-right: 1px solid #ccc; }

    /* EDITABLES */
    .editable-cell { padding: 5px !important; cursor: pointer; transition: background 0.2s; }
    .editable-cell:hover { background-color: #e2e6ea; }
    .editable-cell:focus { background-color: #fff3cd; outline: 2px solid #ffc107; }
    .saving-cell { background-color: #fff3cd !important; color: #856404; }
    .saved-cell { background-color: #d4edda !important; color: #155724; }
    .error-cell { background-color: #f8d7da !important; color: #721c24; }
    
    #toast-status {
        position: fixed; top: 20px; right: 20px; padding: 10px 20px; border-radius: 5px; color: white; font-weight: bold; display: none; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }
    .bg-success-toast { background-color: #28a745; }
    .bg-danger-toast { background-color: #dc3545; }
    .bg-warning-toast { background-color: #ffc107; color: black !important; }
</style>
</head>

<body>

    <div id="toast-status">Guardando...</div>

    <div class="container-fluid py-2 bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="m-0 text-dark font-weight-bold">
                    <i class='bx bx-edit'></i> Captura de Calificaciones
                </h4>
                <small class="text-muted">
                    Grado: <strong><?= $grado_info['nombreGrado'] ?></strong> |
                    Ciclo: <strong><?= $ciclo_info['nombreCicloEscolar'] ?></strong> |
                    Mes Activo: <strong><?= $ciclo_info['nombre_mes'] ?></strong>
                </small>
            </div>
            <div>
                <button onclick="if(window.history.length > 1){ window.history.back(); } else { window.location.href='<?= base_url('dashboard') ?>'; }" class="btn btn-outline-secondary btn-sm">
                    <i class='bx bx-arrow-back'></i> Salir
                </button>
                <button onclick="location.reload()" class="btn btn-primary btn-sm"><i class='bx bx-refresh'></i> Actualizar Tabla</button>
            </div>
        </div>
    </div>

    <div class="sabana-container">
        <table class="tabla-sabana" align="center">
            <thead>
    <tr>
        <th class="th-corner">
            <div class="d-flex flex-column justify-content-center align-items-center h-100">
                <div class="mb-1 text-primary font-weight-bold text-uppercase" style="font-size: 14px; line-height: 1.2;">
                    <?= $grado_info['nombreGrado'] ?>
                </div>
                <div class="badge badge-warning text-dark mb-3" style="font-size: 11px;">
                    MES: <?= strtoupper($ciclo_info['nombre_mes']) ?>
                </div>
                <div class="text-muted" style="font-size:9px; border-top: 1px solid #ddd; width: 100%; padding-top: 5px;">
                    APELLIDOS Y NOMBRES <i class='bx bx-down-arrow-alt'></i>
                </div>
            </div>
        </th>

        <?php 
        $globalScoreAbsences = $config_json['scoreAbsences'] ?? false;
        
        foreach($config_json['groups'] as $grupo): 
            if(empty($grupo['subjects'])) continue;
        ?>
            <th class="header-category">
                <div class="vertical-wrapper">
                    <?= mb_strtoupper($grupo['title'] ?? 'MATERIAS', 'UTF-8') ?>
                </div>
            </th>

            <?php foreach($grupo['subjects'] as $id_mat):
                $real_id = is_array($id_mat) ? ($id_mat['id'] ?? 0) : $id_mat;
                $nombre_mat = $materias_map[$real_id] ?? 'Materia '.$real_id;
                $nombre_mat = mb_strtoupper($nombre_mat, 'UTF-8');
            ?>
                <th class="header-subject">
                    <div class="vertical-wrapper">
                        <?= $nombre_mat ?>
                    </div>
                </th>
                
                <?php if($globalScoreAbsences): ?>
                    <th class="header-subject text-danger" style="background-color: #fff0f0;">
                         <div class="vertical-wrapper">FALTAS</div>
                    </th>
                <?php endif; ?>

            <?php endforeach; ?>
        <?php endforeach; ?>
    </tr>
</thead>

            <tbody>
                <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td class="col-nombre" title="<?= $alumno['nombre'] ?>">
                            <?= $alumno['nombre'] ?>
                        </td>

                        <?php foreach ($config_json['groups'] as $grupo):
                            if (empty($grupo['subjects'])) continue;
                        ?>
                            <td style="background: #f4f4f4; border-right: 1px solid #ccc;"></td>
                            <?php

                            foreach ($grupo['subjects'] as $id_mat):
                                $real_id = is_array($id_mat) ? ($id_mat['id'] ?? 0) : $id_mat;

                                // Buscamos si existe la nota para este alumno y materia
                                $dataNota = $alumno['notas'][$real_id] ?? null;

                                // Determinamos si es editable según permisos y bandera
                                // Nivel 9=Profe (bandera 1), Nivel 2=Director (bandera 1 o 2), Nivel 1=Admin (Siempre)
                                $editable = false;
                                $bandera = $dataNota['bandera'] ?? 0;

                                if ($user_level == 1) $editable = true;
                                elseif ($user_level == 2 && ($bandera == 1 || $bandera == 2)) $editable = true;
                                elseif ($user_level == 9 && $bandera == 1) $editable = true;

                                // Si no hay registro (no se corrió el CSV), no se puede editar (o mostrar vacío)
                                if (!$dataNota) $editable = false;
                            ?>
                                <td
                                    class="<?= $editable ? 'editable-cell' : 'bg-light text-muted' ?>"
                                    <?= $editable ? 'contenteditable="true"' : '' ?>
                                    data-id-cal="<?= $dataNota['id_cal'] ?? '' ?>"
                                    data-type="score"
                                    data-original="<?= $dataNota['calificacion'] ?? '' ?>">
                                    <?= $dataNota['calificacion'] ?? '-' ?>
                                </td>

                                <?php if ($globalScoreAbsences): ?>
                                    <td
                                        class="<?= $editable ? 'editable-cell' : 'bg-light text-muted' ?>"
                                        style="background-color: #fff5f5;"
                                        <?= $editable ? 'contenteditable="true"' : '' ?>
                                        data-id-cal="<?= $dataNota['id_cal'] ?? '' ?>"
                                        data-type="absence"
                                        data-original="<?= $dataNota['faltas'] ?? '' ?>">
                                        <?= $dataNota['faltas'] ?? '-' ?>
                                    </td>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cells = document.querySelectorAll('.editable-cell');
            const toast = document.getElementById('toast-status');

            function showToast(msg, type) {
                toast.textContent = msg;
                toast.className = ''; // Reset classes
                toast.classList.add(type === 'success' ? 'bg-success-toast' : (type === 'error' ? 'bg-danger-toast' : 'bg-warning-toast'));
                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 2000);
            }

            cells.forEach(cell => {
                // Al entrar a la celda (Focus)
                cell.addEventListener('focus', function() {
                    // Seleccionar todo el texto para facilitar edición rápida
                    // document.execCommand('selectAll', false, null); // Opcional
                });

                // Al salir de la celda (Blur) -> GUARDAR
                cell.addEventListener('blur', function() {
                    const idCal = this.dataset.idCal;
                    const type = this.dataset.type;
                    const originalVal = this.dataset.original;
                    let newVal = this.innerText.trim();

                    // Validaciones simples
                    if (newVal === '') newVal = 0; // Si borran, poner 0
                    if (isNaN(newVal)) {
                        this.innerText = originalVal; // Revertir si no es número
                        return;
                    }

                    // Si no hubo cambios, no hacer nada
                    if (newVal == originalVal) return;

                    // 1. UI: Indicar que se está guardando
                    this.classList.add('saving-cell');
                    showToast('Guardando...', 'warning');

                    // 2. Preparar Datos
                    const formData = new FormData();
                    formData.append('scoreId', idCal);
                    formData.append('value', newVal);
                    formData.append('type', type);

                    // 3. Enviar AJAX (Fetch)
                    fetch('<?= base_url('calificaciones/actualizar') ?>', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.classList.remove('saving-cell');

                            if (data.status === 'success') {
                                // Éxito
                                this.classList.add('saved-cell');
                                this.dataset.original = newVal; // Actualizar referencia
                                showToast('Guardado', 'success');

                                // Quitar color verde después de un rato
                                setTimeout(() => this.classList.remove('saved-cell'), 1500);
                            } else {
                                // Error del servidor
                                this.classList.add('error-cell');
                                showToast('Error: ' + data.msg, 'error');
                            }
                        })
                        .catch(error => {
                            this.classList.remove('saving-cell');
                            this.classList.add('error-cell');
                            showToast('Error de conexión', 'error');
                            console.error(error);
                        });
                });

                // Prevenir Enter (para que actúe como Tab o Blur)
                cell.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.blur();
                    }
                });
            });
        });
    </script>

</body>

</html>