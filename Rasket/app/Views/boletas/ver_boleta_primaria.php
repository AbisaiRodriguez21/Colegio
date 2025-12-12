<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Calificaciones</title>
    <?= $this->include("partials/head-css") ?>

    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; }
        .boleta-paper { background: white; width: 100%; max-width: 1200px; margin: 30px auto; padding: 40px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border-radius: 4px; min-height: 800px; }
        .tabla-notas { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; min-width: 800px; }
        .tabla-notas th, .tabla-notas td { border: 1px solid #999; padding: 4px; text-align: center; color: #000; }
        .header-cell { background-color: #fff; font-weight: bold; }
        .bg-prom { background-color: #b4c6e7; color: #000; font-weight: bold; }
        .bg-final { background-color: #9cc2e5; color: #000; font-weight: bold; }
        .categoria-row { background-color: #5b9bd5; color: white; font-weight: bold; text-transform: uppercase; text-align: center !important; font-size: 12px; }
        .promedio-row { background-color: #bfbfbf; font-weight: bold; color: #000; }
        .promedio-label { text-align: left !important; padding-left: 10px !important; font-weight: 800; }
        .materia-col { text-align: left !important; padding-left: 5px !important; font-weight: 600; width: 250px; min-width: 200px; }
        .text-danger { color: red !important; font-weight: bold; }
        .hidden { display: none !important; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 15px; }
        
        @media screen and (max-width: 768px) {
            .boleta-paper { margin: 10px auto; padding: 15px; width: 95%; min-height: auto; }
            .d-flex.justify-content-between { flex-direction: column; gap: 15px; }
            .btn-group { width: 100%; display: flex; flex-wrap: wrap; justify-content: center; }
            .btn-group .btn { flex: 1 1 auto; margin: 2px; }
        }
        @media print {
            .no-print { display: none !important; }
            .boleta-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; }
            .bg-prom { background-color: #b4c6e7 !important; -webkit-print-color-adjust: exact; }
            .bg-final { background-color: #9cc2e5 !important; -webkit-print-color-adjust: exact; }
            .categoria-row { background-color: #5b9bd5 !important; color: white !important; -webkit-print-color-adjust: exact; }
            .promedio-row { background-color: #bfbfbf !important; -webkit-print-color-adjust: exact; }
            .table-responsive { overflow: visible !important; border: none; }
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

    $gradoNombre = strtolower($alumno['nombreGrado']);
    $txtPeriodo = "Bimestres";
    if (strpos($gradoNombre, 'kinder') !== false) $txtPeriodo = "Periodos";
    if (strpos($gradoNombre, 'bachiller') !== false) $txtPeriodo = "Trimestres";
?>

    <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= base_url('boleta/lista/' . $id_grado) ?>" class="btn btn-secondary btn-sm"><i class="bx bx-arrow-back"></i> Regresar</a>
            <div class="btn-group shadow-sm" role="group">
                <?php if ($id_anterior): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_anterior) ?>" class="btn btn-light border"><i class="bx bx-chevron-left" style="font-size: 1.2rem;"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border" disabled style="opacity: 0.6;"><i class="bx bx-chevron-left" style="font-size: 1.2rem;"></i></button>
                <?php endif; ?>

                <button id="btn-show-english" class="btn btn-primary" onclick="mostrarIngles()" style="min-width: 160px;">Ver boleta de inglés</button>
                <button id="btn-show-spanish" class="btn btn-success hidden" onclick="mostrarEspanol()" style="min-width: 160px;">Ver boleta principal</button>

                <?php if ($id_siguiente): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_siguiente) ?>" class="btn btn-light border"><i class="bx bx-chevron-right" style="font-size: 1.2rem;"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border" disabled style="opacity: 0.6;"><i class="bx bx-chevron-right" style="font-size: 1.2rem;"></i></button>
                <?php endif; ?>
            </div>
            <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bx bx-printer"></i> Imprimir</button>
        </div>
    </div>

    <div class="boleta-paper">
        <table style="width: 100%; border: none; margin-bottom: 20px;">
            <tr>
                <td style="width: 20%; border: none;"><img src="<?= base_url('images/LogoST.png') ?>" style="height: 60px;"></td>
                <td style="width: 80%; border: none; text-align: right;">
                    <h4 style="margin:0;">CALIFICACIONES</h4>
                    <small>Fecha: <?= date('d/m/Y') ?></small>
                </td>
            </tr>
        </table>

        <div class="table-responsive">
            <table style="width:100%; margin-bottom: 15px; font-size: 13px; border:none; min-width: 800px;">
                <tr>
                    <td style="border:none;"><strong>ALUMNO:</strong> <?= $alumno['Nombre'] . ' ' . $alumno['ap_Alumno'] . ' ' . ($alumno['am_Alumno'] ?? '') ?></td>
                    <td style="border:none;"><strong>GRADO:</strong> <?= $alumno['nombreGrado'] ?></td>
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
                            
                            <?php foreach ($seccion['materias'] as $m): ?>
                            <tr>
                                <td class="materia-col"><?= esc($m['nombre']) ?></td>
                                <td><?= pNota($m['notas'][1]??'') ?></td> <td><?= pNota($m['notas'][2]??'') ?></td> <td><?= pNota($m['notas'][3]??'') ?></td> 
                                <td class="bg-prom"><?= pNota($m['p_t1']) ?></td>
                                <td><?= pNota($m['notas'][4]??'') ?></td> <td><?= pNota($m['notas'][5]??'') ?></td> <td><?= pNota($m['notas'][6]??'') ?></td> <td><?= pNota($m['notas'][7]??'') ?></td> 
                                <td class="bg-prom"><?= pNota($m['p_t2']) ?></td>
                                <td><?= pNota($m['notas'][8]??'') ?></td> <td><?= pNota($m['notas'][9]??'') ?></td> <td><?= pNota($m['notas'][10]??'') ?></td> 
                                <td class="bg-prom"><?= pNota($m['p_t3']) ?></td>
                                <td class="bg-final"><?= pNota($m['final']) ?></td>
                            </tr>
                            <?php endforeach; ?>

                            <tr class="promedio-row">
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
                            <?php foreach ($seccion['materias'] as $m): ?>
                            <tr>
                                <td class="materia-col"><?= esc($m['nombre']) ?></td>
                                <td><?= pNota($m['notas'][1]??'') ?></td> <td><?= pNota($m['notas'][2]??'') ?></td> <td><?= pNota($m['notas'][3]??'') ?></td> 
                                <td class="bg-prom"><?= pNota($m['p_t1']) ?></td>
                                <td><?= pNota($m['notas'][4]??'') ?></td> <td><?= pNota($m['notas'][5]??'') ?></td> <td><?= pNota($m['notas'][6]??'') ?></td> <td><?= pNota($m['notas'][7]??'') ?></td> 
                                <td class="bg-prom"><?= pNota($m['p_t2']) ?></td>
                                <td><?= pNota($m['notas'][8]??'') ?></td> <td><?= pNota($m['notas'][9]??'') ?></td> <td><?= pNota($m['notas'][10]??'') ?></td> 
                                <td class="bg-prom"><?= pNota($m['p_t3']) ?></td>
                                <td class="bg-final"><?= pNota($m['final']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="promedio-row">
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

        <div style="margin-top: 60px; display: flex; justify-content: space-around;">
            <div style="width: 35%; border-top: 1px solid #000; text-align: center; font-size: 12px; padding-top: 5px;">Firma de Padre o Tutor</div>
            <div style="width: 35%; border-top: 1px solid #000; text-align: center; font-size: 12px; padding-top: 5px;">Firma del Director</div>
        </div>

        <p style="text-align: center; font-size: 10px; color: #999; margin-top: 30px;">Este documento no es una boleta oficial</p>
    </div>

    <script>
        function mostrarIngles() {
            document.getElementById('contenedor-espanol').classList.add('hidden');
            document.getElementById('contenedor-ingles').classList.remove('hidden');
            document.getElementById('btn-show-english').classList.add('hidden');
            document.getElementById('btn-show-spanish').classList.remove('hidden');
        }
        function mostrarEspanol() {
            document.getElementById('contenedor-ingles').classList.add('hidden');
            document.getElementById('contenedor-espanol').classList.remove('hidden');
            document.getElementById('btn-show-spanish').classList.add('hidden');
            document.getElementById('btn-show-english').classList.remove('hidden');
        }
    </script>

</body>
</html>