<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Captura Primaria - <?= esc($alumno['nombre_completo']) ?></title>
    <?= $this->include("partials/head-css") ?>

    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; }
        
        .boleta-paper { 
            background: white; width: 100%; max-width: 1200px; margin: 30px auto; padding: 40px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border-radius: 4px; min-height: 800px; 
        }
        
        .tabla-notas { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; min-width: 800px; }
        .tabla-notas th, .tabla-notas td { border: 1px solid #999; padding: 0; text-align: center; color: #000; height: 26px; }
        
        .header-cell { background-color: #fff; font-weight: bold; }
        
        /* COLORES Y ESTILOS */
        .bg-prom { background-color: #b4c6e7 !important; color: #000; font-weight: bold; }
        .bg-final { background-color: #9cc2e5 !important; color: #000; font-weight: bold; }
        .categoria-row { background-color: #5b9bd5 !important; color: white; font-weight: bold; text-transform: uppercase; text-align: center !important; font-size: 12px; }
        
        /* Fila de Promedio Específica */
        tr.fila-promedio { background-color: #bfbfbf !important; font-weight: bold; color: #000; }
        
        .promedio-label { text-align: left !important; padding-left: 10px !important; font-weight: 800; }
        .materia-col { text-align: left !important; padding-left: 5px !important; font-weight: 600; width: 250px; min-width: 200px; }
        
        .hidden { display: none !important; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 15px; }
        
        /* INPUTS */
        .celda-editable { padding: 0 !important; transition: background-color 0.2s; background-color: #fff; }
        .celda-editable:focus-within { background-color: #e8f0fe !important; border: 2px solid #4a90e2 !important; }
        
        .input-calif { 
            width: 100%; height: 100%; display: block; border: none; background: transparent; 
            text-align: center; font-size: 11px; font-family: inherit; color: #000; 
            outline: none; cursor: text; margin: 0; line-height: 26px;
        }
        .input-calif.text-danger { color: red !important; font-weight: bold; }
        
        .saving { background-color: #fff3cd !important; }
        .saved { background-color: #d4edda !important; transition: background 0.5s ease; }
        .error-cell { background-color: #f8d7da !important; }

        @media print {
            .no-print { display: none !important; }
            .input-calif { border: none; background: transparent !important; }
            .bg-prom { background-color: #b4c6e7 !important; -webkit-print-color-adjust: exact; }
            .bg-final { background-color: #9cc2e5 !important; -webkit-print-color-adjust: exact; }
            .categoria-row { background-color: #5b9bd5 !important; color: white !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>

<body>

<?php
    function pNota($n) {
        if ($n == '' || $n == 0 || $n == '-') return '';
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

    $gradoNombre = strtolower($alumno['nombreGrado']);
    $txtPeriodo = "Bimestres"; 
?>

    <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= base_url('calificaciones_bimestre/lista/' . $id_grado) ?>" class="btn btn-secondary btn-sm"><i class="bx bx-arrow-back"></i> Regresar</a>
            
            <div class="btn-group shadow-sm" role="group">
                <?php if ($id_anterior): ?>
                    <a href="<?= base_url('calificaciones_bimestre/alumno_completo/' . $id_anterior . '/' . $id_grado) ?>" class="btn btn-light border"><i class="bx bx-chevron-left" style="font-size: 1.2rem;"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border" disabled style="opacity: 0.6;"><i class="bx bx-chevron-left" style="font-size: 1.2rem;"></i></button>
                <?php endif; ?>

                <button id="btn-show-english" class="btn btn-primary" onclick="mostrarIngles()" style="min-width: 160px;">Ver boleta de inglés</button>
                <button id="btn-show-spanish" class="btn btn-success hidden" onclick="mostrarEspanol()" style="min-width: 160px;">Ver boleta principal</button>

                <?php if ($id_siguiente): ?>
                    <a href="<?= base_url('calificaciones_bimestre/alumno_completo/' . $id_siguiente . '/' . $id_grado) ?>" class="btn btn-light border"><i class="bx bx-chevron-right" style="font-size: 1.2rem;"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border" disabled style="opacity: 0.6;"><i class="bx bx-chevron-right" style="font-size: 1.2rem;"></i></button>
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
        <table style="width: 100%; border: none; margin-bottom: 20px;">
            <tr>
                <td style="width: 20%; border: none;"><img src="<?= base_url('images/LogoST.png') ?>" style="height: 60px;"></td>
                <td style="width: 80%; border: none; text-align: right;">
                    <h4 style="margin:0;">
                        <?= ($user_level == 7) ? 'BOLETA DE CALIFICACIONES' : 'CAPTURA DE CALIFICACIONES' ?>
                    </h4>
                    <small>Fecha: <?= date('d/m/Y') ?></small>
                </td>
            </tr>
        </table>

        <div class="table-responsive">
            <table style="width:100%; margin-bottom: 15px; font-size: 13px; border:none; min-width: 800px;">
                <tr>
                    <td style="border:none;"><strong>ALUMNO:</strong> <?= mb_strtoupper($alumno['nombre_completo']) ?></td>
                    <td style="border:none;"><strong>GRADO:</strong> <?= $grado['nombreGrado'] ?></td>
                    <td style="border:none;"><strong>MATRÍCULA:</strong> <?= $alumno['matricula'] ?></td>
                    <td style="border:none;"><strong>CICLO ESCOLAR:</strong> <?= $ciclo['nombreCicloEscolar'] ?></td>
                </tr>
            </table>
        </div>

        <div id="contenedor-espanol">
            <div class="table-responsive">
                <table class="tabla-notas">
                    <thead>
                        <tr>
                            <th rowspan="2" class="header-cell" style="width: 250px;">DESARROLLO</th>
                            <th colspan="4" class="header-cell"><?= $txtPeriodo ?></th>
                            <th colspan="5" class="header-cell"><?= $txtPeriodo ?></th>
                            <th colspan="4" class="header-cell"><?= $txtPeriodo ?></th>
                            <th rowspan="2" class="bg-final">Prom Final</th>
                        </tr>
                        <tr style="font-size: 10px;">
                            <th>Sep</th> <th>Oct</th> <th>Nov</th> <th class="bg-prom">Prom</th>
                            <th>Dic</th> <th>Ene</th> <th>Feb</th> <th>Mar</th> <th class="bg-prom">Prom</th>
                            <th>Abr</th> <th>May</th> <th>Jun</th> <th class="bg-prom">Prom</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $todas_secciones = array_merge($secciones_espanol, $secciones_extra);
                        foreach ($todas_secciones as $seccion): 
                            $prom = $seccion['promedios'];
                        ?>
                            <tr><td colspan="15" class="categoria-row"><?= $seccion['titulo'] ?></td></tr>
                            
                            <?php foreach ($seccion['materias'] as $m): $mid = $m['id_materia']; ?>
                            <tr class="fila-materia">
                                <td class="materia-col"><?= esc($m['nombre']) ?></td>
                                <?= renderInput($mid, 1, $m['notas'][1]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 2, $m['notas'][2]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 3, $m['notas'][3]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom"><?= pNota($m['p_t1']) ?></td>

                                <?= renderInput($mid, 4, $m['notas'][4]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 5, $m['notas'][5]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 6, $m['notas'][6]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 7, $m['notas'][7]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom"><?= pNota($m['p_t2']) ?></td>

                                <?= renderInput($mid, 8, $m['notas'][8]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 9, $m['notas'][9]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 10, $m['notas'][10]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom"><?= pNota($m['p_t3']) ?></td>

                                <td class="bg-final"><?= pNota($m['final']) ?></td>
                            </tr>
                            <?php endforeach; ?>

                            <tr class="promedio-row fila-promedio">
                                <td class="promedio-label">PROMEDIO</td>
                                <td><?= pNota($prom[1]) ?></td> <td><?= pNota($prom[2]) ?></td> <td><?= pNota($prom[3]) ?></td> 
                                <td class="bg-prom" style="border:1px solid #999"><?= pNota($prom['p_t1']) ?></td>
                                <td><?= pNota($prom[4]) ?></td> <td><?= pNota($prom[5]) ?></td> <td><?= pNota($prom[6]) ?></td> <td><?= pNota($prom[7]) ?></td> 
                                <td class="bg-prom" style="border:1px solid #999"><?= pNota($prom['p_t2']) ?></td>
                                <td><?= pNota($prom[8]) ?></td> <td><?= pNota($prom[9]) ?></td> <td><?= pNota($prom[10]) ?></td> 
                                <td class="bg-prom" style="border:1px solid #999"><?= pNota($prom['p_t3']) ?></td>
                                <td class="bg-final" style="border:1px solid #999"><?= pNota($prom['final']) ?></td>
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
                        <tr>
                            <th rowspan="2" class="header-cell" style="width: 250px;">DESARROLLO</th>
                            <th colspan="4" class="header-cell"><?= $txtPeriodo ?></th>
                            <th colspan="5" class="header-cell"><?= $txtPeriodo ?></th>
                            <th colspan="4" class="header-cell"><?= $txtPeriodo ?></th>
                            <th rowspan="2" class="bg-final">Prom Final</th>
                        </tr>
                        <tr style="font-size: 10px;">
                            <th>Sep</th> <th>Oct</th> <th>Nov</th> <th class="bg-prom">Prom</th>
                            <th>Dic</th> <th>Ene</th> <th>Feb</th> <th>Mar</th> <th class="bg-prom">Prom</th>
                            <th>Abr</th> <th>May</th> <th>Jun</th> <th class="bg-prom">Prom</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($secciones_ingles as $seccion): 
                            $prom = $seccion['promedios'];
                        ?>
                            <tr><td colspan="15" class="categoria-row"><?= $seccion['titulo'] ?></td></tr>
                            <?php foreach ($seccion['materias'] as $m): $mid = $m['id_materia']; ?>
                            <tr class="fila-materia">
                                <td class="materia-col"><?= esc($m['nombre']) ?></td>
                                <?= renderInput($mid, 1, $m['notas'][1]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 2, $m['notas'][2]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 3, $m['notas'][3]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom"><?= pNota($m['p_t1']) ?></td>

                                <?= renderInput($mid, 4, $m['notas'][4]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 5, $m['notas'][5]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 6, $m['notas'][6]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 7, $m['notas'][7]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom"><?= pNota($m['p_t2']) ?></td>

                                <?= renderInput($mid, 8, $m['notas'][8]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 9, $m['notas'][9]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <?= renderInput($mid, 10, $m['notas'][10]??'', $alumno['id'], $id_grado, $user_level) ?>
                                <td class="bg-prom"><?= pNota($m['p_t3']) ?></td>

                                <td class="bg-final"><?= pNota($m['final']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <tr class="promedio-row fila-promedio">
                                <td class="promedio-label">PROMEDIO</td>
                                <td><?= pNota($prom[1]) ?></td> <td><?= pNota($prom[2]) ?></td> <td><?= pNota($prom[3]) ?></td> 
                                <td class="bg-prom" style="border:1px solid #999"><?= pNota($prom['p_t1']) ?></td>
                                <td><?= pNota($prom[4]) ?></td> <td><?= pNota($prom[5]) ?></td> <td><?= pNota($prom[6]) ?></td> <td><?= pNota($prom[7]) ?></td> 
                                <td class="bg-prom" style="border:1px solid #999"><?= pNota($prom['p_t2']) ?></td>
                                <td><?= pNota($prom[8]) ?></td> <td><?= pNota($prom[9]) ?></td> <td><?= pNota($prom[10]) ?></td> 
                                <td class="bg-prom" style="border:1px solid #999"><?= pNota($prom['p_t3']) ?></td>
                                <td class="bg-final" style="border:1px solid #999"><?= pNota($prom['final']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <p style="text-align: center; font-size: 10px; color: #999; margin-top: 30px;">
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
                
                // CALCULAR EN TIEMPO REAL
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

            // ---------------------------------------------------------
            // LÓGICA MATEMÁTICA DE PRIMARIA (DIVISORES 3, 4, 3)
            // ---------------------------------------------------------
            
            function getVal(elm) {
                var val = 0;
                if(elm.find('input').length > 0) val = elm.find('input').val();
                else val = elm.text();
                return ($.isNumeric(val) && val !== '') ? parseFloat(val) : 0;
            }
            
            function hasData(elm) {
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

            function calcularFila(tr) {
                var tds = tr.find('td');
                // Indices Primaria: 
                // Sep(1), Oct(2), Nov(3) -> P1(4)
                // Dic(5), Ene(6), Feb(7), Mar(8) -> P2(9)
                // Abr(10), May(11), Jun(12) -> P3(13)
                // Final(14)

                // Periodo 1: Divisor 3
                var v1=getVal(tds.eq(1)), v2=getVal(tds.eq(2)), v3=getVal(tds.eq(3));
                var h1=hasData(tds.eq(1)), h2=hasData(tds.eq(2)), h3=hasData(tds.eq(3));
                var p1 = (h1||h2||h3) ? (v1+v2+v3)/3 : null;
                setVal(tds.eq(4), p1);

                // Periodo 2: Divisor 4
                var v4=getVal(tds.eq(5)), v5=getVal(tds.eq(6)), v6=getVal(tds.eq(7)), v7=getVal(tds.eq(8));
                var h4=hasData(tds.eq(5)), h5=hasData(tds.eq(6)), h6=hasData(tds.eq(7)), h7=hasData(tds.eq(8));
                var p2 = (h4||h5||h6||h7) ? (v4+v5+v6+v7)/4 : null;
                setVal(tds.eq(9), p2);

                // Periodo 3: Divisor 3
                var v8=getVal(tds.eq(10)), v9=getVal(tds.eq(11)), v10=getVal(tds.eq(12));
                var h8=hasData(tds.eq(10)), h9=hasData(tds.eq(11)), h10=hasData(tds.eq(12));
                var p3 = (h8||h9||h10) ? (v8+v9+v10)/3 : null;
                setVal(tds.eq(13), p3);

                // FINAL: Promedio de los existentes
                var sumF=0, divF=0;
                if(p1 !== null) { sumF+=p1; divF++; }
                if(p2 !== null) { sumF+=p2; divF++; }
                if(p3 !== null) { sumF+=p3; divF++; }
                var final = (divF > 0) ? sumF/divF : null;
                setVal(tds.eq(14), final);
                
                // Recalcular verticales
                calcularColumna(tds.eq(4));
                calcularColumna(tds.eq(9));
                calcularColumna(tds.eq(13));
                calcularColumna(tds.eq(14));
            }

            function calcularColumna(elementoRef) {
                var td = (elementoRef.is('input')) ? elementoRef.closest('td') : elementoRef;
                var colIndex = td.index();
                var trActual = td.closest('tr');

                // Buscar fila de promedio
                var filaPromedio = trActual.nextAll('tr.fila-promedio').first();
                if(filaPromedio.length === 0) return;

                var sum = 0;
                var countMaterias = 0;
                var filaIter = filaPromedio.prev('tr');
                
                while(filaIter.length > 0) {
                    if(filaIter.hasClass('fila-promedio') || filaIter.find('th').length > 0) break;
                    if(filaIter.hasClass('fila-materia')) {
                        countMaterias++;
                        sum += getVal(filaIter.find('td').eq(colIndex));
                    }
                    filaIter = filaIter.prev('tr');
                }

                // Escribir en la celda de abajo
                var celdaDestino = filaPromedio.find('td').eq(colIndex);
                if(countMaterias > 0) {
                    // Si hay suma > 0 mostramos, o si la celda editada tenía datos
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