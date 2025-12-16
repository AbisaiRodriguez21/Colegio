<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Boleta de Secundaria</title>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; }
        .boleta-paper {
            background: white;
            width: 100%;
            max-width: 1250px;
            margin: 30px auto;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 4px;
            min-height: 800px;
        }
        
        /* TABLA PRINCIPAL */
        .tabla-notas { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            font-size: 11px; 
            min-width: 1000px; /* Ancho mínimo forzado para scroll */
        }
        .tabla-notas th, .tabla-notas td { border: 1px solid #999; padding: 4px; text-align: center; color: #000; }
        
        /* ENCABEZADOS VERTICALES */
        .vertical-text { writing-mode: vertical-lr; transform: rotate(180deg); font-size: 10px; margin: 0 auto; white-space: nowrap; }
        
        /* COLORES */
        .bg-header-pink { background-color: #fce4d6; font-weight: bold; }
        .bg-prom-periodo { background-color: #b4c6e7; font-weight: bold; }
        .bg-prom-final { background-color: #9cc2e5; font-weight: bold; }
        
        .materia-col { text-align: left !important; padding-left: 5px !important; font-weight: 700; width: 250px; font-size: 11px; text-transform: uppercase; min-width: 200px; }
        .text-danger { color: red !important; font-weight: bold; }
        
        /* --- RESPONSIVIDAD --- */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 15px;
        }

        @media screen and (max-width: 768px) {
            .boleta-paper {
                margin: 10px auto;
                padding: 15px;
                width: 95%;
                min-height: auto;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 15px;
            }
            .btn-group {
                width: 100%;
                display: flex;
                justify-content: center;
            }
        }

        @media print {
            .no-print { display: none !important; }
            .boleta-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
            .bg-header-pink { background-color: #fce4d6 !important; -webkit-print-color-adjust: exact; }
            .bg-prom-periodo { background-color: #b4c6e7 !important; -webkit-print-color-adjust: exact; }
            .bg-prom-final { background-color: #9cc2e5 !important; -webkit-print-color-adjust: exact; }
            /* Quitar scroll en impresión */
            .table-responsive { overflow: visible !important; }
        }
    </style>
</head>
<body>

<?php
    // Helper visual para calificaciones
    function pNota($n) {
        if($n == '' || $n == 0 || $n == '-') return '';
        $val = floatval($n);
        $color = ($val < 6) ? 'text-danger' : '';
        return "<span class='$color'>$n</span>";
    }
?>

    <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= base_url('boleta/lista/' . $id_grado) ?>" class="btn btn-secondary btn-sm"><i class="bx bx-arrow-back"></i> Regresar</a>
            
            <div class="btn-group shadow-sm">
                <?php if($id_anterior): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_anterior) ?>" class="btn btn-light border btn-nav"><i class="bx bx-chevron-left"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border btn-nav" disabled><i class="bx bx-chevron-left"></i></button>
                <?php endif; ?>

                <span class="btn btn-light border disabled" style="font-weight:bold; min-width:150px; background:#fff;">
                   <?= $alumno['nombreGrado'] ?>
                </span>

                <?php if($id_siguiente): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_siguiente) ?>" class="btn btn-light border btn-nav"><i class="bx bx-chevron-right"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border btn-nav" disabled><i class="bx bx-chevron-right"></i></button>
                <?php endif; ?>
            </div>
            
            <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bx bx-printer"></i> Imprimir</button>
        </div>
    </div>

    <div class="boleta-paper">
        <div class="text-center mb-4">
            <img src="<?= base_url('images/LogoST.png') ?>" style="height: 80px; margin-bottom:10px;">
            <h3 style="margin:0; font-weight: bold; color: #000;">SAINT JOSEPH SCHOOL</h3>
            <h4 style="margin:5px 0 0 0; font-weight: normal;">SECUNDARIA</h4>
        </div>
        
        <div class="table-responsive">
            <table style="width:100%; margin-bottom: 20px; font-size: 11px; border:none; min-width: 800px;">
                <tr>
                    <td style="width:30%; border:none;">
                        <strong>NOMBRE:</strong><br>
                        <span style="font-size:13px; font-weight:bold;">
                        <?php 
                            $nombreCompleto = esc($alumno['Nombre']);
                            if(!empty($alumno['ap_Alumno'])) $nombreCompleto .= ' ' . esc($alumno['ap_Alumno']);
                            if(!empty($alumno['am_Alumno'])) $nombreCompleto .= ' ' . esc($alumno['am_Alumno']);
                            elseif(!empty($alumno['Apellidos'])) $nombreCompleto = esc($alumno['Nombre']) . ' ' . esc($alumno['Apellidos']);
                            echo strtoupper($nombreCompleto);
                        ?>
                        </span>
                    </td>
                    <td style="width:20%; border:none; text-align:center;">
                        <strong>GRADO:</strong><br>
                        <span style="font-size:12px; font-weight:bold;"><?= $alumno['nombreGrado'] ?></span>
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
                    // Función de renderizado interna 
                    function renderSeccionDinamica($seccion) {
                        $materias = $seccion['materias'];
                        $proms = $seccion['promedios'];
                        
                        

                        foreach($materias as $m): ?>
                        <tr>
                            <td class="materia-col"><?= esc($m['nombre']) ?></td>
                            <td><?= pNota($m['notas'][1]??'') ?></td> <td><?= pNota($m['notas'][2]??'') ?></td> <td><?= pNota($m['notas'][3]??'') ?></td>
                            <td class="bg-prom-periodo"><?= pNota($m['p_t1']) ?></td>
                            <td><?= pNota($m['notas'][4]??'') ?></td> <td><?= pNota($m['notas'][5]??'') ?></td> <td><?= pNota($m['notas'][6]??'') ?></td> <td><?= pNota($m['notas'][7]??'') ?></td>
                            <td class="bg-prom-periodo"><?= pNota($m['p_t2']) ?></td>
                            <td><?= pNota($m['notas'][8]??'') ?></td> <td><?= pNota($m['notas'][9]??'') ?></td> <td><?= pNota($m['notas'][10]??'') ?></td>
                            <td class="bg-prom-periodo"><?= pNota($m['p_t3']) ?></td>
                            <td></td> <td class="bg-prom-final"><?= pNota($m['final']) ?></td>
                        </tr>
                        <?php endforeach; 

                        // FILA PROMEDIOS DE LA SECCIÓN
                        ?>
                        <tr class="bg-prom-periodo">
                            <td style="text-align:left; padding-left:5px;">PROMEDIO</td>
                            <td><?= pNota($proms[1]) ?></td> <td><?= pNota($proms[2]) ?></td> <td><?= pNota($proms[3]) ?></td>
                            <td><?= pNota($proms['p_t1']) ?></td>
                            <td><?= pNota($proms[4]) ?></td> <td><?= pNota($proms[5]) ?></td> <td><?= pNota($proms[6]) ?></td> <td><?= pNota($proms[7]) ?></td>
                            <td><?= pNota($proms['p_t2']) ?></td>
                            <td><?= pNota($proms[8]) ?></td> <td><?= pNota($proms[9]) ?></td> <td><?= pNota($proms[10]) ?></td>
                            <td><?= pNota($proms['p_t3']) ?></td>
                            <td></td> <td class="bg-prom-final"><?= pNota($proms['final']) ?></td>
                        </tr>
                    <?php } ?>

                    <?php 
                    // ITERAR TODAS LAS SECCIONES DINÁMICAMENTE
                    if(!empty($secciones_espanol)) {
                        foreach($secciones_espanol as $seccion) {
                            renderSeccionDinamica($seccion);
                        }
                    }
                    if(!empty($secciones_ingles)) {
                        foreach($secciones_ingles as $seccion) {
                            renderSeccionDinamica($seccion);
                        }
                    }
                    ?>

                </tbody>
            </table>
        </div>

        <div style="margin-top: 80px; display: flex; justify-content: space-around;">
            <div style="width: 40%; border-top: 1px solid #000; text-align: center; font-size: 11px; padding-top: 5px;">
                Firma de Padre o Tutor
            </div>
            <div style="width: 40%; border-top: 1px solid #000; text-align: center; font-size: 11px; padding-top: 5px;">
                Firma del Director
            </div>
        </div>
        
        <p style="text-align: center; font-size: 10px; color: #999; margin-top: 40px;">
            Este documento no es una boleta oficial
        </p>
    </div>

</body>
</html>