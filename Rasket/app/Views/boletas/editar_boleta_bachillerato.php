<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Captura Bachillerato - <?= esc($alumno['nombre_completo']) ?></title>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; }
        
        .boleta-paper {
            background: white; width: 100%; max-width: 1000px; margin: 30px auto; padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); min-height: 800px;
        }
        
        /* TABLA ESTILO BACHILLERATO */
        .tabla-bach { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; min-width: 600px; }
        .tabla-bach th, .tabla-bach td { border: 1px solid #000; padding: 2px; text-align: center; color: #000; height: 30px; }
        .tabla-bach td.no-border-bg { border: none; background: transparent; }

        /* Encabezados verticales */
        .header-rotate {
            writing-mode: vertical-lr; transform: rotate(180deg); white-space: nowrap;
            height: 100px; font-weight: bold; color: #555; text-transform: uppercase; margin: 0 auto;
        }
        
        .materia-col { text-align: left !important; padding-left: 10px !important; font-weight: bold; width: 40%; text-transform: uppercase; min-width: 200px; }
        .gray-bg { background-color: #e9e9e9; font-weight: bold; }
        
        /* TALLERES (!important para asegurar el color) */
        .taller-row td { background-color: #b4c6e7 !important; font-weight: bold; }
        /* Excepto los inputs que deben verse blancos al editar o transparentes */
        .taller-row .celda-editable { background-color: #b4c6e7; }
        
        .text-danger { color: red !important; font-weight: bold; }

        /* BOTONES */
        .btn-semestre-azul { background-color: #007bff; border: 1px solid #0069d9; color: #fff; padding: 8px 20px; text-decoration: none; font-size: 13px; font-weight: bold; border-radius: 4px; display: inline-block; }
        .btn-semestre-verde { background-color: #00a65a; border: 1px solid #008d4c; color: #fff; padding: 8px 20px; text-decoration: none; font-size: 13px; font-weight: bold; border-radius: 4px; display: inline-block; }

        /* Datos */
        .datos-alumno-table { width:100%; margin-bottom: 20px; font-size: 11px; border:none; min-width: 500px; }
        .datos-alumno-table td { border:none; vertical-align: top; }
        .dato-label { font-weight: bold; display: block; margin-bottom: 2px; }
        .dato-valor { font-size:12px; font-weight:bold; }

        .nav-center-group { display: flex; align-items: center; justify-content: center; gap: 10px; }
        
        /* EDICIÓN */
        .celda-editable { padding: 0 !important; transition: background-color 0.2s; }
        .celda-editable:focus-within { background-color: #e8f0fe !important; border: 2px solid #4a90e2 !important; }
        
        .input-calif { 
            width: 100%; height: 100%; display: block; border: none; background: transparent; 
            text-align: center; font-size: 12px; font-family: inherit; color: #000; 
            outline: none; cursor: text; margin: 0; line-height: 30px;
        }
        
        .saving { background-color: #fff3cd !important; }
        .saved { background-color: #d4edda !important; transition: background 0.5s ease; }
        .error-cell { background-color: #f8d7da !important; }
        .hidden { display: none !important; }
        .table-responsive { width: 100%; overflow-x: auto; margin-bottom: 15px; }

        @media print {
            .no-print { display: none !important; }
            .boleta-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
            .gray-bg { background-color: #e9e9e9 !important; -webkit-print-color-adjust: exact; }
            .taller-row td { background-color: #b4c6e7 !important; -webkit-print-color-adjust: exact; }
            .input-calif { border: none; }
            .table-responsive { overflow: visible !important; }
        }
    </style>
</head>
<body>

<?php
    function pNota($n) {
        if($n == '' || $n == 0 || $n == '-') return '';
        $val = floatval($n);
        $color = ($val < 6) ? 'text-danger' : '';
        return "<span class='$color'>$n</span>";
    }

    function renderInput($materia_id, $mes_id, $val, $alumno_id, $grado_id, $user_level) {
        $colorClass = ($val != '' && $val < 6 && $val > 0) ? 'text-danger' : '';
        $puedeEditar = ($user_level == 1 || $user_level == 2 || $user_level == 9);

        if ($puedeEditar) {
            return sprintf(
                '<td class="celda-editable"><input type="text" class="input-calif %s" value="%s" data-al="%s" data-gr="%s" data-mat="%s" data-mes="%s" autocomplete="off"></td>',
                $colorClass, $val, $alumno_id, $grado_id, $materia_id, $mes_id
            );
        } else {
            return sprintf('<td><span class="%s">%s</span></td>', $colorClass, $val);
        }
    }
?>

    <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center">
            
            <a href="<?= base_url('calificaciones_bimestre/lista/' . $id_grado) ?>" class="btn btn-secondary btn-sm"><i class="bx bx-arrow-back"></i> Regresar</a>
            
            <div class="nav-center-group">
                <?php if($id_anterior): ?>
                    <a href="<?= base_url('calificaciones_bimestre/alumno_completo/' . $id_anterior . '/' . $id_grado . '?semestre=' . $semestre_actual) ?>" class="btn btn-light border btn-nav"><i class="bx bx-chevron-left"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border btn-nav" disabled><i class="bx bx-chevron-left"></i></button>
                <?php endif; ?>

                <a href="<?= $link_otro_semestre ?>" class="<?= $clase_boton ?>">
                    <?= $texto_boton ?>
                </a>

                <?php if($id_siguiente): ?>
                    <a href="<?= base_url('calificaciones_bimestre/alumno_completo/' . $id_siguiente . '/' . $id_grado . '?semestre=' . $semestre_actual) ?>" class="btn btn-light border btn-nav"><i class="bx bx-chevron-right"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border btn-nav" disabled><i class="bx bx-chevron-right"></i></button>
                <?php endif; ?>
            </div>
            
            <div>
                <span id="save-msg" class="badge bg-warning text-dark me-2 hidden" style="font-size: 1em; padding: 8px 12px;">
                    <i class="bx bx-loader bx-spin"></i> Guardando...
                </span>
                <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bx bx-printer"></i> Imprimir</button>
            </div>
        </div>
    </div>

    <div class="boleta-paper">
        
        <div class="text-center mb-4">
            <img src="<?= base_url('images/LogoST.png') ?>" style="height: 80px; margin-bottom:10px;">
            <h3 style="margin:0; font-weight: bold; color: #000;">SAINT JOSEPH SCHOOL</h3>
            <h4 style="margin:5px 0 0 0; font-weight: normal;">BACHILLERATO - CAPTURA</h4>
        </div>
        
        <div class="table-responsive">
            <table class="datos-alumno-table">
                <tr>
                    <td style="width:35%;">
                        <span class="dato-label">NOMBRE:</span>
                        <span class="dato-valor"><?= strtoupper($alumno['nombre_completo']) ?></span>
                    </td>
                    <td style="width:25%; text-align:center;">
                        <span class="dato-label">GRADO:</span>
                        <span class="dato-valor"><?= $grado['nombreGrado'] ?></span>
                    </td>
                    <td style="width:20%; text-align:center;">
                        <span class="dato-label">MATRÍCULA:</span>
                        <span class="dato-valor"><?= $alumno['matricula'] ?></span>
                    </td>
                    <td style="width:20%; text-align:center;">
                        <span class="dato-label">CICLO ESCOLAR:</span>
                        <span class="dato-valor"><?= $ciclo['nombreCicloEscolar'] ?></span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="table-responsive">
            <table class="tabla-bach">
                <thead>
                    <tr>
                        <th class="gray-bg">MATERIA</th>
                        <?php foreach($headers as $h): ?>
                            <th class="gray-bg" style="width: 60px;"><div class="header-rotate"><?= $h ?></div></th>
                        <?php endforeach; ?>
                        <th class="gray-bg" style="width: 60px;"><div class="header-rotate">SEMESTRAL</div></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($secciones)): ?>
                        <?php foreach($secciones as $seccion): ?>
                            
                            <?php if(!empty($seccion['titulo'])): ?>
                                <tr>
                                    <td colspan="<?= count($headers) + 2 ?>" style="background:#e0e0e0; font-weight:bold; text-align:left; padding-left:10px;">
                                        <?= strtoupper($seccion['titulo']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach($seccion['materias'] as $m): 
                                $mid = $m['id_materia'];
                            ?>
                                <tr class="fila-materia <?= ($m['es_taller'] ?? false) ? 'taller-row' : '' ?>">
                                    <td class="materia-col"><?= $m['nombre'] ?></td>
                                    
                                    <?php foreach($col_ids as $cid): ?>
                                        <?= renderInput($mid, $cid, $m['notas'][$cid]??'', $alumno['id'], $id_grado, $user_level) ?>
                                    <?php endforeach; ?>
                                    
                                    <td class="celda-promedio" style="font-weight:bold; background-color:#f9f9f9;"><?= pNota($m['promedio']) ?></td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endforeach; ?>
                        
                        <tr>
                            <td class="no-border-bg" colspan="<?= count($headers) ?>"></td>
                            <td class="gray-bg" style="font-weight: bold; font-size: 10px; padding: 2px;">
                                PROMEDIO<br>FINAL
                            </td>
                            <td class="gray-bg" style="font-weight: bold;" id="promedio-general-global"><?= pNota($prom_gral) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="<?= count($headers) + 2 ?>">No hay materias configuradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        
        <p style="text-align: center; font-size: 10px; color: #999; margin-top: 40px;">
            <?= ($user_level == 7) ? 'Este documento no es una boleta oficial' : 'Este documento no es una boleta oficial' ?>
        </p>
        
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '<?= csrf_hash() ?>' } });

            $('.input-calif').on('input', function(){
                var v = $(this).val();
                if(v != '' && !isNaN(v) && parseFloat(v) < 6) { $(this).addClass('text-danger'); } 
                else { $(this).removeClass('text-danger'); }
                
                calcularFila($(this).closest('tr'));
            });

            $('.input-calif').on('blur', function(){
                var input = $(this);
                if(input.val() === input.prop('defaultValue')) return;

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
                        valor: input.val(),
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(res){
                        celda.removeClass('saving');
                        if(res.status === 'success'){
                            celda.addClass('saved');
                            input.prop('defaultValue', input.val());
                            $('#save-msg').removeClass('bg-warning text-dark').addClass('bg-success text-white').html('<i class="bx bx-check"></i> Guardado');
                            
                            // Recalcular
                            calcularFila(input.closest('tr'));

                            setTimeout(() => { celda.removeClass('saved'); $('#save-msg').addClass('hidden'); }, 2000);
                        } else {
                            celda.addClass('error-cell');
                            alert('Error: ' + (res.msg || 'No guardado'));
                        }
                    },
                    error: function() {
                        celda.removeClass('saving').addClass('error-cell');
                        alert('Error de conexión.');
                    }
                });
            });

            $('.input-calif').on('keydown', function(e){ if(e.key === 'Enter') $(this).blur(); });

            // --- LÓGICA DE CÁLCULO BACHILLERATO ---
            
            function getVal(elm) {
                var val = 0;
                if(elm.find('input').length > 0) val = elm.find('input').val();
                else val = elm.text();
                return ($.isNumeric(val) && val > 0) ? parseFloat(val) : null;
            }

            function setVal(celda, val) {
                if(val === null) { celda.text(''); return; }
                var fixed = val.toFixed(1);
                if(fixed.endsWith('.0')) fixed = fixed.replace('.0', '');
                celda.text(fixed);
                if(val < 6) celda.addClass('text-danger'); else celda.removeClass('text-danger');
            }

            function calcularFila(tr) {
                var inputs = tr.find('.input-calif');
                var suma = 0;
                var count = 0;
                
                // Regla del Controlador: Sumar solo las notas existentes (> 0)
                inputs.each(function(){
                    var val = parseFloat($(this).val());
                    if($.isNumeric(val) && val > 0) {
                        suma += val;
                        count++;
                    }
                });

                // Promedio = Suma / Cantidad de notas capturadas
                var celdaFinal = tr.find('.celda-promedio');
                if(count > 0) {
                    var promedio = suma / count;
                    setVal(celdaFinal, promedio);
                } else {
                    celdaFinal.text('');
                }

                calcularPromedioGeneral();
            }

            function calcularPromedioGeneral() {
                var sumaTotal = 0;
                var countTotal = 0;

                $('.fila-materia').each(function(){
                    var promTexto = $(this).find('.celda-promedio').text();
                    if($.isNumeric(promTexto) && promTexto !== '') {
                        sumaTotal += parseFloat(promTexto);
                        countTotal++;
                    }
                });

                var celdaGlobal = $('#promedio-general-global');
                if(countTotal > 0) {
                    setVal(celdaGlobal, sumaTotal / countTotal);
                } else {
                    celdaGlobal.text('');
                }
            }
        });
    </script>

</body>
</html>