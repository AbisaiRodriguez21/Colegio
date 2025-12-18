<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Captura Secundaria - <?= esc($alumno['nombre_completo']) ?></title>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; }
        
        .boleta-paper {
            background: white; width: 100%; max-width: 1250px; margin: 30px auto; padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-radius: 4px; min-height: 800px;
        }
        
        /* TABLA */
        .tabla-notas { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; min-width: 1000px; }
        .tabla-notas th, .tabla-notas td { border: 1px solid #999; padding: 4px; text-align: center; color: #000; height: 26px; }
        .vertical-text { writing-mode: vertical-lr; transform: rotate(180deg); font-size: 10px; margin: 0 auto; white-space: nowrap; }
        
        /* COLORES Y ESTILOS */
        /* Usamos !important para asegurar que se pinten sobre cualquier otro estilo */
        .bg-header-pink { background-color: #fce4d6 !important; font-weight: bold; }
        .bg-prom-periodo { background-color: #b4c6e7 !important; font-weight: bold; color: #000; }
        .bg-prom-final { background-color: #9cc2e5 !important; font-weight: bold; color: #000; }
        
        /* Fila de Promedio Específica (Azul Completo) */
        tr.fila-promedio { background-color: #b4c6e7 !important; font-weight: bold; }
        
        .materia-col { text-align: left !important; padding-left: 5px !important; font-weight: 700; width: 250px; font-size: 11px; text-transform: uppercase; min-width: 200px; background-color: #fff; }
        
        /* En la fila de promedio, la celda de nombre también debe ser azul */
        tr.fila-promedio td.materia-col { background-color: #b4c6e7 !important; }

        .text-danger { color: red !important; font-weight: bold; }
        
        /* INPUTS */
        .celda-editable { padding: 0 !important; transition: background-color 0.2s; background-color: #fff; }
        .celda-editable:focus-within { background-color: #e8f0fe !important; border: 2px solid #4a90e2 !important; }
        .input-calif { width: 100%; height: 100%; display: block; border: none; background: transparent; text-align: center; font-size: 11px; font-family: inherit; color: #000; outline: none; cursor: text; margin: 0; line-height: 26px; }
        
        .saving { background-color: #fff3cd !important; }
        .saved { background-color: #d4edda !important; transition: background 0.5s ease; }
        .error-cell { background-color: #f8d7da !important; }
        .hidden { display: none !important; }
        .table-responsive { width: 100%; overflow-x: auto; margin-bottom: 15px; }

        @media print {
            .no-print { display: none !important; }
            .boleta-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
            .bg-header-pink { background-color: #fce4d6 !important; -webkit-print-color-adjust: exact; }
            .bg-prom-periodo, tr.fila-promedio { background-color: #b4c6e7 !important; -webkit-print-color-adjust: exact; }
            .bg-prom-final { background-color: #9cc2e5 !important; -webkit-print-color-adjust: exact; }
            .input-calif { border: none; }
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
            
            <div class="btn-group shadow-sm">
                <?php if($id_anterior): ?>
                    <a href="<?= base_url('calificaciones_bimestre/alumno_completo/' . $id_anterior . '/' . $id_grado) ?>" class="btn btn-light border btn-nav"><i class="bx bx-chevron-left"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border btn-nav" disabled><i class="bx bx-chevron-left"></i></button>
                <?php endif; ?>

                <span class="btn btn-light border disabled" style="font-weight:bold; min-width:150px; background:#fff;">
                   <?= $alumno['nombreGrado'] ?>
                </span>

                <?php if($id_siguiente): ?>
                    <a href="<?= base_url('calificaciones_bimestre/alumno_completo/' . $id_siguiente . '/' . $id_grado) ?>" class="btn btn-light border btn-nav"><i class="bx bx-chevron-right"></i></a>
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
            <h4 style="margin:5px 0 0 0; font-weight: normal;">SECUNDARIA - CAPTURA</h4>
        </div>
        
        <div class="table-responsive">
            <table style="width:100%; margin-bottom: 20px; font-size: 11px; border:none; min-width: 800px;">
                <tr>
                    <td style="width:30%; border:none;">
                        <strong>NOMBRE:</strong><br>
                        <span style="font-size:13px; font-weight:bold;"><?= strtoupper($alumno['nombre_completo']) ?></span>
                    </td>
                    <td style="width:20%; border:none; text-align:center;">
                        <strong>GRADO:</strong><br>
                        <span style="font-size:12px; font-weight:bold;"><?= $grado['nombreGrado'] ?></span>
                    </td>
                    <td style="width:20%; border:none; text-align:center;">
                        <strong>MATRÍCULA:</strong><br>
                        <span style="font-size:12px; font-weight:bold;"><?= $alumno['matricula'] ?></span>
                    </td>
                    <td style="width:20%; border:none; text-align:center;">
                        <strong>CICLO ESCOLAR:</strong><br>
                        <span style="font-size:12px; font-weight:bold;"><?= $ciclo['nombreCicloEscolar'] ?></span>
                    </td>
                </tr>
            </table>
        </div>

        <div id="contenedor-espanol">
            <div class="table-responsive">
                <table class="tabla-notas">
                    <thead>
                        <tr class="bg-header-pink">
                            <td rowspan="2" class="header-cell" style="background:white; border:none; border-right:1px solid #999;">&nbsp;</td> 
                            <td style="width:30px;"><div class="vertical-text">SEP</div></td>
                            <td style="width:30px;"><div class="vertical-text">OCT</div></td>
                            <td style="width:30px;"><div class="vertical-text">NOV</div></td>
                            <td class="bg-prom-periodo" rowspan="2" style="width:35px;"><div class="vertical-text">PROM<br>1er</div></td>
                            <td style="width:30px;"><div class="vertical-text">DIC</div></td>
                            <td style="width:30px;"><div class="vertical-text">ENE</div></td>
                            <td style="width:30px;"><div class="vertical-text">FEB</div></td>
                            <td style="width:30px;"><div class="vertical-text">MAR</div></td>
                            <td class="bg-prom-periodo" rowspan="2" style="width:35px;"><div class="vertical-text">PROM<br>2do</div></td>
                            <td style="width:30px;"><div class="vertical-text">ABR</div></td>
                            <td style="width:30px;"><div class="vertical-text">MAY</div></td>
                            <td style="width:30px;"><div class="vertical-text">JUN</div></td>
                            <td class="bg-prom-periodo" rowspan="2" style="width:35px;"><div class="vertical-text">PROM<br>3er</div></td>
                            <td rowspan="2" style="width:35px;"><div class="vertical-text">Total<br>faltas</div></td>
                            <td class="bg-prom-final" rowspan="2" style="width:40px;"><div class="vertical-text">PROM<br>FINAL</div></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach($secciones_espanol as $seccion): 
                            $materias = $seccion['materias'];
                            $proms = $seccion['promedios'];
                            if(!empty($seccion['titulo'])): ?>
                                <tr><td colspan="16" style="background:#eee; font-weight:bold; text-align:left; padding-left:10px;"><?= strtoupper($seccion['titulo']) ?></td></tr>
                            <?php endif;

                            foreach($materias as $m): 
                                $mid = $m['id_materia'];
                            ?>
                            <tr class="fila-materia"> <td class="materia-col"><?= esc($m['nombre']) ?></td>
                                <?= renderInput($mid, 1, $m['notas'][1]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 2, $m['notas'][2]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 3, $m['notas'][3]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom-periodo"><?= pNota($m['p_t1']) ?></td>
                                
                                <?= renderInput($mid, 4, $m['notas'][4]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 5, $m['notas'][5]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 6, $m['notas'][6]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 7, $m['notas'][7]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom-periodo"><?= pNota($m['p_t2']) ?></td>
                                
                                <?= renderInput($mid, 8, $m['notas'][8]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 9, $m['notas'][9]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 10, $m['notas'][10]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom-periodo"><?= pNota($m['p_t3']) ?></td>
                                
                                <td></td>
                                <td class="bg-prom-final"><?= pNota($m['final']) ?></td>
                            </tr>
                            <?php endforeach; ?>

                            <tr class="fila-promedio">
                                <td class="materia-col">PROMEDIO</td>
                                <td><?= pNota($proms[1]) ?></td> <td><?= pNota($proms[2]) ?></td> <td><?= pNota($proms[3]) ?></td>
                                <td class="bg-prom-periodo"><?= pNota($proms['p_t1']) ?></td>
                                
                                <td><?= pNota($proms[4]) ?></td> <td><?= pNota($proms[5]) ?></td> <td><?= pNota($proms[6]) ?></td> <td><?= pNota($proms[7]) ?></td>
                                <td class="bg-prom-periodo"><?= pNota($proms['p_t2']) ?></td>
                                
                                <td><?= pNota($proms[8]) ?></td> <td><?= pNota($proms[9]) ?></td> <td><?= pNota($proms[10]) ?></td>
                                <td class="bg-prom-periodo"><?= pNota($proms['p_t3']) ?></td>
                                
                                <td></td>
                                <td class="bg-prom-final"><?= pNota($proms['final']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="contenedor-ingles" class="hidden">
            <div class="table-responsive">
                <table class="tabla-notas">
                    <thead>
                        <tr class="bg-header-pink">
                            <td rowspan="2" class="header-cell" style="background:white; border:none; border-right:1px solid #999;">&nbsp;</td> 
                            <td style="width:30px;"><div class="vertical-text">SEP</div></td>
                            <td style="width:30px;"><div class="vertical-text">OCT</div></td>
                            <td style="width:30px;"><div class="vertical-text">NOV</div></td>
                            <td class="bg-prom-periodo" rowspan="2" style="width:35px;"><div class="vertical-text">PROM<br>1er</div></td>
                            <td style="width:30px;"><div class="vertical-text">DIC</div></td>
                            <td style="width:30px;"><div class="vertical-text">ENE</div></td>
                            <td style="width:30px;"><div class="vertical-text">FEB</div></td>
                            <td style="width:30px;"><div class="vertical-text">MAR</div></td>
                            <td class="bg-prom-periodo" rowspan="2" style="width:35px;"><div class="vertical-text">PROM<br>2do</div></td>
                            <td style="width:30px;"><div class="vertical-text">ABR</div></td>
                            <td style="width:30px;"><div class="vertical-text">MAY</div></td>
                            <td style="width:30px;"><div class="vertical-text">JUN</div></td>
                            <td class="bg-prom-periodo" rowspan="2" style="width:35px;"><div class="vertical-text">PROM<br>3er</div></td>
                            <td rowspan="2" style="width:35px;"><div class="vertical-text">Total<br>faltas</div></td>
                            <td class="bg-prom-final" rowspan="2" style="width:40px;"><div class="vertical-text">PROM<br>FINAL</div></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($secciones_ingles as $seccion): 
                            $materias = $seccion['materias'];
                            $proms = $seccion['promedios'];
                            if(!empty($seccion['titulo'])): ?>
                                <tr><td colspan="16" style="background:#eee; font-weight:bold; text-align:left; padding-left:10px;"><?= strtoupper($seccion['titulo']) ?></td></tr>
                            <?php endif;

                            foreach($materias as $m): 
                                $mid = $m['id_materia'];
                            ?>
                            <tr class="fila-materia">
                                <td class="materia-col"><?= esc($m['nombre']) ?></td>
                                <?= renderInput($mid, 1, $m['notas'][1]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 2, $m['notas'][2]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 3, $m['notas'][3]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom-periodo"><?= pNota($m['p_t1']) ?></td>
                                
                                <?= renderInput($mid, 4, $m['notas'][4]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 5, $m['notas'][5]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 6, $m['notas'][6]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 7, $m['notas'][7]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom-periodo"><?= pNota($m['p_t2']) ?></td>
                                
                                <?= renderInput($mid, 8, $m['notas'][8]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 9, $m['notas'][9]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 10, $m['notas'][10]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom-periodo"><?= pNota($m['p_t3']) ?></td>
                                
                                <td></td>
                                <td class="bg-prom-final"><?= pNota($m['final']) ?></td>
                            </tr>
                            <?php endforeach; ?>

                            <tr class="fila-promedio">
                                <td class="materia-col">PROMEDIO</td>
                                <td><?= pNota($proms[1]) ?></td> <td><?= pNota($proms[2]) ?></td> <td><?= pNota($proms[3]) ?></td>
                                <td class="bg-prom-periodo"><?= pNota($proms['p_t1']) ?></td>
                                <td><?= pNota($proms[4]) ?></td> <td><?= pNota($proms[5]) ?></td> <td><?= pNota($proms[6]) ?></td> <td><?= pNota($proms[7]) ?></td>
                                <td class="bg-prom-periodo"><?= pNota($proms['p_t2']) ?></td>
                                <td><?= pNota($proms[8]) ?></td> <td><?= pNota($proms[9]) ?></td> <td><?= pNota($proms[10]) ?></td>
                                <td class="bg-prom-periodo"><?= pNota($proms['p_t3']) ?></td>
                                <td></td>
                                <td class="bg-prom-final"><?= pNota($proms['final']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        
        <p style="text-align: center; font-size: 10px; color: #999; margin-top: 40px;">
            <?= ($user_level == 7) ? 'Este documento no es una boleta oficial' : 'Este documento no es una boleta oficial' ?>
        </p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function mostrarIngles() {
            $('#contenedor-espanol').addClass('hidden');
            $('#contenedor-ingles').removeClass('hidden');
            $('#btn-show-english').addClass('hidden');
            $('#btn-show-spanish').removeClass('hidden');
        }
        function mostrarEspanol() {
            $('#contenedor-ingles').addClass('hidden');
            $('#contenedor-espanol').removeClass('hidden');
            $('#btn-show-spanish').addClass('hidden');
            $('#btn-show-english').removeClass('hidden');
        }

        $(document).ready(function(){
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '<?= csrf_hash() ?>' } });

            $('.input-calif').on('input', function(){
                var v = $(this).val();
                if(v != '' && !isNaN(v) && parseFloat(v) < 6) { $(this).addClass('text-danger'); } 
                else { $(this).removeClass('text-danger'); }
                
                calcularFila($(this).closest('tr'));
                calcularColumna($(this)); 
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
                            
                            calcularFila(input.closest('tr'));
                            calcularColumna(input);

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

            // --- LÓGICA MATEMÁTICA CORREGIDA (PROMEDIOS ESTRICTOS) ---
            
            function getVal(elm) {
                var val = 0;
                if(elm.find('input').length > 0) val = elm.find('input').val();
                else val = elm.text();
                // Si es vacío devuelve 0 (para sumar), pero diferenciamos null para saber si mostrarlo
                return ($.isNumeric(val) && val !== '') ? parseFloat(val) : 0;
            }
            
            function hasData(elm) {
                // Función auxiliar para saber si la celda tiene datos reales
                var val = (elm.find('input').length > 0) ? elm.find('input').val() : elm.text();
                return ($.isNumeric(val) && val !== '');
            }

            function setVal(celda, val) {
                if(val === null) { celda.text(''); return; }
                var fixed = val.toFixed(1);
                if(fixed.endsWith('.0')) fixed = fixed.replace('.0', '');
                celda.text(fixed);
                if(val < 6) celda.addClass('text-danger'); else celda.removeClass('text-danger');
            }

            // PROMEDIO ESTRICTO: Suma todo (0 si vacío) y divide entre el divisor fijo
            function promEstricto(valores, divisor) {
                // Si TODAS las celdas están vacías, devolvemos null para no llenar de ceros
                var hayDatos = false;
                var suma = 0;
                valores.forEach(v => {
                    suma += v; 
                    if(v > 0) hayDatos = true; // Si hay al menos un valor > 0 asumimos data
                });
                
                // Opción visual: si la suma es 0, mostramos vacío o 0 según prefieras.
                // Aquí: Si suma es 0 y no hubo datos explicitos, devolvemos null.
                // Pero si el usuario puso un 0 explícito, debería contar.
                // Simplificación: Si divisor > 0, calculamos.
                
                // Ajuste para UX: Si todo el periodo está vacío, mostramos vacío.
                // Pero si hay al menos una nota (aunque sea 0), mostramos promedio.
                // Para simplificar según tu petición: División estricta.
                
                return suma / divisor; 
            }

            function calcularFila(tr) {
                var tds = tr.find('td');
                
                // Periodo 1: Divisor 3 (Sep, Oct, Nov)
                var v1=getVal(tds.eq(1)), v2=getVal(tds.eq(2)), v3=getVal(tds.eq(3));
                var h1=hasData(tds.eq(1)), h2=hasData(tds.eq(2)), h3=hasData(tds.eq(3));
                var p1 = (h1||h2||h3) ? (v1+v2+v3)/3 : null;
                setVal(tds.eq(4), p1);

                // Periodo 2: Divisor 4 (Dic, Ene, Feb, Mar)
                var v4=getVal(tds.eq(5)), v5=getVal(tds.eq(6)), v6=getVal(tds.eq(7)), v7=getVal(tds.eq(8));
                var h4=hasData(tds.eq(5)), h5=hasData(tds.eq(6)), h6=hasData(tds.eq(7)), h7=hasData(tds.eq(8));
                var p2 = (h4||h5||h6||h7) ? (v4+v5+v6+v7)/4 : null;
                setVal(tds.eq(9), p2);

                // Periodo 3: Divisor 3 (Abr, May, Jun)
                var v8=getVal(tds.eq(10)), v9=getVal(tds.eq(11)), v10=getVal(tds.eq(12));
                var h8=hasData(tds.eq(10)), h9=hasData(tds.eq(11)), h10=hasData(tds.eq(12));
                var p3 = (h8||h9||h10) ? (v8+v9+v10)/3 : null;
                setVal(tds.eq(13), p3);

                // FINAL: Promedio de los promedios de periodo existentes
                // OJO: Tu lógica original en el controlador decía:
                // if($t1_prom){ $sum+=$t1_prom; $div++; } ...
                // Si quieres que el final TAMBIÉN sea estricto (dividir siempre entre 3), cambia aquí.
                // Usualmente el final se calcula sobre lo cursado. Lo dejaré "cursado" para no reprobar gente a mitad de año.
                var sumF=0, divF=0;
                if(p1 !== null) { sumF+=p1; divF++; }
                if(p2 !== null) { sumF+=p2; divF++; }
                if(p3 !== null) { sumF+=p3; divF++; }
                var final = (divF > 0) ? sumF/divF : null;
                setVal(tds.eq(15), final);
                
                // Recalcular verticales de los promedios
                calcularColumna(tds.eq(4));
                calcularColumna(tds.eq(9));
                calcularColumna(tds.eq(13));
                calcularColumna(tds.eq(15));
            }

            function calcularColumna(elementoRef) {
                var td = (elementoRef.is('input')) ? elementoRef.closest('td') : elementoRef;
                var colIndex = td.index();
                var trActual = td.closest('tr');

                // Buscar fila de promedio de esta sección
                var filaPromedio = trActual.nextAll('tr.fila-promedio').first();
                if(filaPromedio.length === 0) return;

                var sum = 0;
                var countMaterias = 0; // Total materias en la sección
                
                var filaIter = filaPromedio.prev('tr');
                
                // Iterar hacia arriba para sumar y CONTAR materias
                while(filaIter.length > 0) {
                    if(filaIter.hasClass('fila-promedio') || filaIter.find('th').length > 0) break;
                    
                    // Solo contamos si es una fila de materia (tiene clase fila-materia o detectamos input/tds)
                    // Usamos la clase que agregué en el PHP
                    if(filaIter.hasClass('fila-materia')) {
                        countMaterias++; // Contamos la materia exista nota o no
                        sum += getVal(filaIter.find('td').eq(colIndex));
                    }
                    filaIter = filaIter.prev('tr');
                }

                // CALCULO VERTICAL ESTRICTO: Suma / Total Materias
                var celdaDestino = filaPromedio.find('td').eq(colIndex);
                if(countMaterias > 0) {
                    // Solo mostramos si la suma es > 0 para no llenar de ceros columnas vacías
                    if(sum > 0 || hasData(td)) {
                        setVal(celdaDestino, sum / countMaterias);
                    } else {
                        celdaDestino.text('');
                    }
                }
            }
        });
    </script>

</body>
</html>