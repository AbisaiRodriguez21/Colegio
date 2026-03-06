<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Captura Preescolar - <?= esc($alumno['nombre_completo']) ?></title>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; }
        .boleta-paper {
            background: white; width: 100%; max-width: 1100px; margin: 30px auto; padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); min-height: 800px;
        }
        
        /* TABLA KINDER (CLON EXACTO DEL SITIO VIEJO) */
        .tabla-kinder { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 15px; border: 1px solid #99bce8; }
        .tabla-kinder th, .tabla-kinder td { border: 1px solid #99bce8; padding: 4px; color: #000; }
        
        .header-main th { background-color: #fff; font-weight: bold; text-align: center; font-size: 11px; }
        .header-sub { background-color: #fff; font-weight: bold; text-align: center; font-size: 11px; text-transform: uppercase; }
        .header-title { background-color: #fff; font-weight: bold; text-align: center; font-size: 12px; text-transform: uppercase; }
        
        .col-materia { width: 55%; text-align: left; }
        .col-nota { width: 11%; text-align: center; font-weight: bold; }

        .datos-alumno-table { width:100%; margin-bottom: 20px; font-size: 11px; border:none; }
        .datos-alumno-table td { border:none; vertical-align: top; padding: 5px; }
        .dato-label { font-weight: bold; }

        /* FILAS CEBRA (AZULITO Y BLANCO) */
        .bg-azul-suave { background-color: #f0f5fa; }
        .bg-blanco { background-color: #ffffff; }

        /* LEYENDA NIVEL DE DESEMPEÑO (CLON EXACTO DEL SITIO VIEJO) */
        .leyenda-container { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; font-size: 12px; border: 1px solid #99bce8; }
        .leyenda-header { background-color: #dce4f3; text-align: center; font-weight: bold; padding: 6px; border-bottom: 1px solid #99bce8; color: #000; }
        .leyenda-body { text-align: center; padding: 15px; background-color: #fff; }
        .leyenda-item { display: inline-block; margin-right: 15px; vertical-align: middle; }
        .leyenda-square { display: inline-block; width: 14px; height: 14px; vertical-align: middle; margin-right: 5px; }

        /* EDICIÓN Y COLORES */
        .celda-editable { padding: 0 !important; transition: background-color 0.2s; position: relative;}
        .celda-editable:focus-within { background-color: #e8f0fe !important; border: 2px solid #4a90e2 !important; }
        .input-calif { width: 100%; height: 100%; display: block; border: none; background: transparent; text-align: center; font-size: 11px; font-weight: bold; outline: none; padding: 4px 0; }
        
        .saving { background-color: #fff3cd !important; }
        .saved { background-color: #d4edda !important; transition: background 0.5s ease; }
        .error-cell { background-color: #f8d7da !important; }
        .hidden { display: none !important; }

        /* LOS COLORES DE TUS CUADRITOS */
        .text-alcanzado { color: #800000 !important; font-weight: bold; } /* Rojo Oscuro / Guinda */
        .text-proceso   { color: #000080 !important; font-weight: bold; } /* Azul Marino */
        .text-apoyo     { color: #006400 !important; font-weight: bold; } /* Verde Oscuro */

        @media print {
            .no-print { display: none !important; }
            .boleta-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
            body { background-color: white; }
            .col-md-6 { width: 50% !important; float: left; }
            .input-calif { border: none; }
            .bg-azul-suave { background-color: #f0f5fa !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<?php
    function renderInputEditable($materia_id, $mes_id, $val, $alumno_id, $grado_id, $user_level, $es_porcentaje = false) {
        $puedeEditar = ($user_level == 1 || $user_level == 2 || $user_level == 9);
        $colorClass = '';
        
        if ($val !== '' && $val !== null && !$es_porcentaje) {
            $n = round(floatval($val));
            if ($n >= 9) $colorClass = 'text-alcanzado';
            elseif ($n >= 7) $colorClass = 'text-proceso';
            elseif ($n > 0) $colorClass = 'text-apoyo';
        }

        $mostrar = $val;
        if ($val !== '' && $es_porcentaje) {
            $mostrar = round($val) . '%';
        }

        if ($puedeEditar) {
            return sprintf(
                '<td class="col-nota celda-editable"><input type="text" class="input-calif %s" value="%s" data-al="%s" data-gr="%s" data-mat="%s" data-mes="%s" data-porc="%s" autocomplete="off"></td>',
                $colorClass, $mostrar, $alumno_id, $grado_id, $materia_id, $mes_id, ($es_porcentaje ? '1' : '0')
            );
        } else {
            return sprintf('<td class="col-nota"><span class="%s">%s</span></td>', $colorClass, $mostrar);
        }
    }

    function renderGrupoEditable($grupo, $momentos, $alumno_id, $grado_id, $user_level) {
        $html = '';
        if (!empty($grupo['titulo'])) {
            $html .= '<tr><td colspan="5" class="header-sub">' . esc($grupo['titulo']) . '</td></tr>';
        }
        if (empty($grupo['materias'])) {
            return $html;
        }
        $contador_fila = 1; 
        foreach ($grupo['materias'] as $m) {
            $clase_fondo = ($contador_fila % 2 != 0) ? 'bg-azul-suave' : 'bg-blanco';
            
            $html .= '<tr class="fila-materia ' . $clase_fondo . '">';
            $html .= '<td class="col-materia">' . esc($m['nombre']) . '</td>';
            $es_porcent = $m['isPercentage'] ?? false;

            foreach ($momentos as $id_mes) {
                $nota = $m['notas'][$id_mes] ?? '';
                $html .= renderInputEditable($m['id_materia'], $id_mes, $nota, $alumno_id, $grado_id, $user_level, $es_porcent);
            }
            $html .= '<td class="col-nota celda-promedio"></td>';
            $html .= '</tr>';
            
            $contador_fila++;
        }
        return $html;
    }
?>

    <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <?php $rutaRegreso = ($user_level == 7) ? base_url('alumno/dashboard') : base_url('calificaciones_bimestre/lista/' . $id_grado); ?>
            <a href="<?= $rutaRegreso ?>" class="btn btn-secondary btn-sm"><i class='bx bx-arrow-back'></i> Regresar</a>
            
            <div class="d-flex align-items-center justify-content-center gap-2">
                <?php if($id_anterior): ?>
                    <a href="<?= base_url('calificaciones_bimestre/alumno_completo/' . $id_anterior . '/' . $id_grado) ?>" class="btn btn-light border btn-sm">
                        <i class="bx bx-chevron-left" style="font-size: 1.2rem;"></i>
                    </a>
                <?php else: ?>
                    <button class="btn btn-light border btn-sm" disabled>
                        <i class="bx bx-chevron-left" style="font-size: 1.2rem;"></i>
                    </button>
                <?php endif; ?>

                <span style="font-weight: bold; font-size: 13px; color: #555; width: 60px; text-align: center;">KINDER</span>

                <?php if($id_siguiente): ?>
                    <a href="<?= base_url('calificaciones_bimestre/alumno_completo/' . $id_siguiente . '/' . $id_grado) ?>" class="btn btn-light border btn-sm">
                        <i class="bx bx-chevron-right" style="font-size: 1.2rem;"></i>
                    </a>
                <?php else: ?>
                    <button class="btn btn-light border btn-sm" disabled>
                        <i class="bx bx-chevron-right" style="font-size: 1.2rem;"></i>
                    </button>
                <?php endif; ?>
            </div>
            
            <div>
                <span id="save-msg" class="badge bg-warning text-dark me-2 hidden"><i class="bx bx-loader bx-spin"></i> Guardando...</span>
                <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bx bx-printer"></i> Imprimir</button>
            </div>
        </div>
    </div>

    <div class="boleta-paper">
        <div class="text-center mb-4">
            <img src="<?= base_url('images/LogoST.png') ?>" style="height: 70px;">
            <h4 style="margin: 5px 0 0 0; font-weight: bold;">SAINT JOSEPH SCHOOL</h4>
            <h5 style="margin: 0; font-weight: normal;">PREESCOLAR</h5>
        </div>
        <table class="datos-alumno-table">
            <tr>
                <td><span class="dato-label">NOMBRE:</span> <?= strtoupper($alumno['nombre_completo']) ?></td>
                <td style="text-align: right;"><span class="dato-label">MATRÍCULA:</span> <?= $alumno['matricula'] ?></td>
            </tr>
            <tr>
                <td><span class="dato-label">GRADO:</span> <?= $grado['nombreGrado'] ?></td>
                <td style="text-align: right;"><span class="dato-label">CICLO ESCOLAR:</span> <?= $ciclo['nombreCicloEscolar'] ?></td>
            </tr>
        </table>
        
        <div class="row">
            <div class="col-md-6" style="padding-right: 5px;">
                <table class="tabla-kinder">
                    <thead>
                        <tr class="header-main">
                            <th rowspan="2" class="col-materia">ASPECTOS A EVALUAR</th>
                            <th class="col-nota">1ER</th>
                            <th class="col-nota">2DO</th>
                            <th class="col-nota">3ER</th>
                            <th rowspan="2" class="col-nota">PROM</th>
                        </tr>
                        <tr class="header-main">
                            <th colspan="3" class="col-nota">MOMENTO</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="header-title"><?= esc($left_title) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($left_groups as $g): ?>
                            <?= renderGrupoEditable($g, $momentos, $alumno['id'], $id_grado, $user_level) ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <table class="leyenda-container no-print">
                    <tr>
                        <td class="leyenda-header">NIVEL DE DESEMPEÑO:</td>
                    </tr>
                    <tr>
                        <td class="leyenda-body">
                            <div class="leyenda-item">
                                <div class="leyenda-square" style="background-color: #800000;"></div>
                                <span style="color: #800000; font-weight: bold;">Alcanzado</span>
                                <span style="color: #000; font-weight: bold;"> ,</span>
                            </div>
                            <div class="leyenda-item">
                                <div class="leyenda-square" style="background-color: #000080;"></div>
                                <span style="color: #000080; font-weight: bold;">Proceso</span>
                                <span style="color: #000; font-weight: bold;"> ,</span>
                            </div>
                            <div class="leyenda-item" style="margin-right: 0;">
                                <div class="leyenda-square" style="background-color: #006400;"></div>
                                <span style="color: #006400; font-weight: bold;">Requiere apoyo</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6" style="padding-left: 5px;">
                <table class="tabla-kinder">
                    <thead>
                        <tr class="header-main">
                            <th rowspan="2" class="col-materia">ASPECTOS A EVALUAR</th>
                            <th class="col-nota">1ER</th>
                            <th class="col-nota">2DO</th>
                            <th class="col-nota">3ER</th>
                            <th rowspan="2" class="col-nota">PROM</th>
                        </tr>
                        <tr class="header-main">
                            <th colspan="3" class="col-nota">MOMENTO</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="header-title"><?= esc($right_title) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($right_groups as $g): ?>
                            <?= renderGrupoEditable($g, $momentos, $alumno['id'], $id_grado, $user_level) ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 70px; text-align: center; font-size: 11px; color: #000;">
            <div style="display: inline-block; width: 40%; border-top: 1px solid #000; padding-top: 5px;">
                Firma de Padre o Tutor
            </div>
            <div style="display: inline-block; width: 10%;"></div>
            <div style="display: inline-block; width: 40%; border-top: 1px solid #000; padding-top: 5px;">
                Firma del Director
            </div>
        </div>
        <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #888;">
            Este documento no es una boleta oficial
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            function colorizeCell(element, val) {
                element.removeClass('text-alcanzado text-proceso text-apoyo');
                if(val !== '' && !isNaN(val)) {
                    var n = Math.round(parseFloat(val)); 
                    if(n >= 9) element.addClass('text-alcanzado');
                    else if(n >= 7) element.addClass('text-proceso');
                    else if(n > 0) element.addClass('text-apoyo');
                }
            }
            function calcularFila(tr) {
                var inputs = tr.find('.input-calif');
                var suma = 0;
                var count = 0;
                inputs.each(function(){
                    var val = parseFloat($(this).val());
                    if($(this).data('porc') != '1' && $.isNumeric(val) && val > 0) {
                        suma += val;
                        count++;
                    }
                });
                var celdaFinal = tr.find('.celda-promedio');
                if(count > 0) {
                    var promedioCerrado = Math.round(suma / count);
                    celdaFinal.text(promedioCerrado);
                    colorizeCell(celdaFinal, promedioCerrado);
                } else {
                    celdaFinal.text('');
                    celdaFinal.removeClass('text-alcanzado text-proceso text-apoyo');
                }
            }
            $('.fila-materia').each(function(){
                calcularFila($(this));
            });
            $('.input-calif').on('input', function(){
                var val = $(this).val().replace('%', '');
                colorizeCell($(this), val);
                calcularFila($(this).closest('tr'));
            });
            $('.input-calif').on('blur', function(){
                var input = $(this);
                var rawValue = input.val().replace('%', '').trim();
                if(rawValue === input.prop('defaultValue')) return;
                var celda = input.parent();
                celda.addClass('saving');
                $('#save-msg').removeClass('hidden bg-success text-white').addClass('bg-warning text-dark').html('<i class="bx bx-loader bx-spin"></i> Guardando...');
                $.ajax({
                    url: '<?= base_url('calificaciones_bimestre/actualizar') ?>',
                    method: 'POST',
                    data: {
                        id_alumno: input.data('al'),
                        id_grado: input.data('gr'),
                        id_materia: input.data('mat'),
                        id_mes: input.data('mes'),
                        valor: rawValue,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(res){
                        celda.removeClass('saving');
                        if(res.status === 'success'){
                            celda.addClass('saved');
                            if(input.data('porc') == '1' && rawValue !== '') {
                                input.val(rawValue + '%');
                            }
                            input.prop('defaultValue', rawValue);
                            $('#save-msg').removeClass('bg-warning text-dark').addClass('bg-success text-white').html('<i class="bx bx-check"></i> Guardado');
                            setTimeout(() => { celda.removeClass('saved'); $('#save-msg').addClass('hidden'); }, 2000);
                        } else {
                            celda.addClass('error-cell');
                            alert('Error: ' + (res.msg || 'No se pudo guardar la calificación'));
                        }
                    },
                    error: function() {
                        celda.removeClass('saving').addClass('error-cell');
                        alert('Error de conexión.');
                    }
                });
            });
            $('.input-calif').on('keydown', function(e){ 
                if(e.key === 'Enter') $(this).blur(); 
            });
        });
    </script>
</body>
</html>