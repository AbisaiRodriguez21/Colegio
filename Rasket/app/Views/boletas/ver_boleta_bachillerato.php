<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Boleta Bachillerato</title>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; }
        
        .boleta-paper {
            background: white;
            width: 100%;
            max-width: 1000px;
            margin: 30px auto;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            min-height: 800px;
        }
        
        /* TABLA ESTILO BACHILLERATO */
        .tabla-bach { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; min-width: 600px; /* Ancho mínimo para forzar estructura */ }
        .tabla-bach th, .tabla-bach td { border: 1px solid #000; padding: 4px 2px; text-align: center; color: #000; }
        
        .tabla-bach td.no-border-bg { border: none; background: transparent; }

        /* Encabezados verticales */
        .header-rotate {
            writing-mode: vertical-lr; 
            transform: rotate(180deg);
            white-space: nowrap;
            height: 100px;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin: 0 auto;
        }
        
        .materia-col { text-align: left !important; padding-left: 10px !important; font-weight: bold; width: 40%; text-transform: uppercase; min-width: 200px; }
        .gray-bg { background-color: #e9e9e9; font-weight: bold; }
        .taller-row td { background-color: #b4c6e7; font-weight: bold; }
        .text-danger { color: red !important; font-weight: bold; }

        /* --- BOTONES --- */
        .btn-semestre-azul {
            background-color: #007bff; border: 1px solid #0069d9; color: #fff;
            padding: 8px 20px; text-decoration: none; font-size: 13px; font-weight: bold;
            border-radius: 4px; display: inline-block; white-space: nowrap;
        }
        .btn-semestre-verde {
            background-color: #00a65a; border: 1px solid #008d4c; color: #fff;
            padding: 8px 20px; text-decoration: none; font-size: 13px; font-weight: bold;
            border-radius: 4px; display: inline-block; white-space: nowrap;
        }

        /* Datos del Alumno */
        .datos-alumno-table { width:100%; margin-bottom: 20px; font-size: 11px; border:none; min-width: 500px; }
        .datos-alumno-table td { border:none; vertical-align: top; }
        .dato-label { font-weight: bold; display: block; margin-bottom: 2px; }
        .dato-valor { font-size:12px; font-weight:bold; }

        /* Navegación central */
        .nav-center-group { display: flex; align-items: center; justify-content: center; gap: 10px; }
        
        /* --- RESPONSIVIDAD (SOLUCIÓN MÓVIL) --- */
        
        /* Contenedor para hacer scroll horizontal en tablas */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch; /* Scroll suave en iOS */
            margin-bottom: 15px;
        }

        @media screen and (max-width: 768px) {
            .boleta-paper {
                margin: 10px auto;
                padding: 15px; /* Menos padding */
                width: 95%; /* Ocupar casi todo el ancho */
                min-height: auto;
            }
            
            /* Ajustar barra de navegación */
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 15px;
            }
            .nav-center-group {
                order: -1; /* Poner la navegación principal arriba */
                width: 100%;
                justify-content: space-between;
            }
            .btn-secondary, .btn-dark {
                width: 100%; /* Botones anchos para tocar fácil */
                margin-bottom: 5px;
            }
        }

        @media print {
            .no-print { display: none !important; }
            .boleta-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
            .gray-bg { background-color: #e9e9e9 !important; -webkit-print-color-adjust: exact; }
            .taller-row td { background-color: #b4c6e7 !important; -webkit-print-color-adjust: exact; }
            .btn-semestre-azul, .btn-semestre-verde { display: none; }
            
            /* En impresión, quitamos el scroll para que salga todo */
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
?>

    <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center">
            
            <a href="<?= base_url('boleta/lista/' . $id_grado) ?>" class="btn btn-secondary btn-sm"><i class="bx bx-arrow-back"></i> Regresar</a>
            
            <div class="nav-center-group">
                <?php if($id_anterior): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_anterior . '?semestre=' . $semestre_actual) ?>" class="btn btn-light border btn-nav"><i class="bx bx-chevron-left"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border btn-nav" disabled><i class="bx bx-chevron-left"></i></button>
                <?php endif; ?>

                <a href="<?= $link_otro_semestre ?>" class="<?= $clase_boton ?>">
                    <?= $texto_boton ?>
                </a>

                <?php if($id_siguiente): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_siguiente . '?semestre=' . $semestre_actual) ?>" class="btn btn-light border btn-nav"><i class="bx bx-chevron-right"></i></a>
                <?php else: ?>
                    <button class="btn btn-light border btn-nav" disabled><i class="bx bx-chevron-right"></i></button>
                <?php endif; ?>
            </div>
            
            <div>
                <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bx bx-printer"></i> Imprimir</button>
            </div>
        </div>
    </div>

    <div class="boleta-paper">
        
        <div class="text-center mb-4">
            <img src="<?= base_url('images/LogoST.png') ?>" style="height: 80px; margin-bottom:10px;">
            <h3 style="margin:0; font-weight: bold; color: #000;">SAINT JOSEPH SCHOOL</h3>
            <h4 style="margin:5px 0 0 0; font-weight: normal;">BACHILLERATO</h4>
        </div>
        
        <div class="table-responsive">
            <table class="datos-alumno-table">
                <tr>
                    <td style="width:35%;">
                        <span class="dato-label">NOMBRE:</span>
                        <span class="dato-valor">
                        <?php 
                            $nombreCompleto = esc($alumno['Nombre']);
                            if(!empty($alumno['ap_Alumno'])) $nombreCompleto .= ' ' . esc($alumno['ap_Alumno']);
                            if(!empty($alumno['am_Alumno'])) $nombreCompleto .= ' ' . esc($alumno['am_Alumno']);
                            elseif(!empty($alumno['Apellidos'])) $nombreCompleto = esc($alumno['Nombre']) . ' ' . esc($alumno['Apellidos']);
                            echo strtoupper($nombreCompleto);
                        ?>
                        </span>
                    </td>
                    <td style="width:25%; text-align:center;">
                        <span class="dato-label">GRADO:</span>
                        <span class="dato-valor"><?= $alumno['nombreGrado'] ?></span>
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
                            <th class="gray-bg" style="width: 40px;"><div class="header-rotate"><?= $h ?></div></th>
                        <?php endforeach; ?>
                        <th class="gray-bg" style="width: 40px;"><div class="header-rotate">SEMESTRAL</div></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($boleta)): ?>
                        <?php foreach($boleta as $m): ?>
                            <tr class="<?= ($m['es_taller'] ?? false) ? 'taller-row' : '' ?>">
                                <td class="materia-col"><?= $m['nombre'] ?></td>
                                <?php foreach($m['notas'] as $nota): ?>
                                    <td><?= pNota($nota) ?></td>
                                <?php endforeach; ?>
                                <td><?= pNota($m['promedio']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <tr>
                            <td class="no-border-bg" colspan="<?= count($headers) ?>"></td>
                            <td class="gray-bg" style="font-weight: bold; font-size: 10px; padding: 2px;">
                                PROMEDIO<br>FINAL
                            </td>
                            <td class="gray-bg" style="font-weight: bold;"><?= pNota($prom_gral) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="<?= count($headers) + 2 ?>">No hay materias registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 100px; text-align: center;">
            <div style="width: 40%; border-top: 1px solid #000; margin: 0 auto; padding-top: 5px; font-size: 11px;">
                PROF. JOSÉ ANTONIO TORAL<br>
                DIRECTOR DEL BACHILLERATO
            </div>
        </div>
        
        <p style="text-align: center; font-size: 10px; color: #999; margin-top: 40px;">
            Este documento no es una boleta oficial
        </p>
        
    </div>

</body>
</html>