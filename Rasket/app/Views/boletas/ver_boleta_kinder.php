<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta Preescolar - <?= esc($alumno['Nombre'] ?? '') ?></title>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; }
        .boleta-paper {
            background: white; width: 100%; max-width: 1100px; margin: 30px auto; padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); min-height: 800px;
        }
        
        .tabla-kinder { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 15px; border: 1px solid #99bce8; }
        .tabla-kinder th, .tabla-kinder td { border: 1px solid #99bce8; padding: 4px; color: #000; }
        
        .header-main th { background-color: #fff; font-weight: bold; text-align: center; font-size: 11px; }
        .header-sub { background-color: #fff; font-weight: bold; text-align: center; font-size: 11px; text-transform: uppercase; }
        .header-title { background-color: #fff; font-weight: bold; text-align: center; font-size: 12px; text-transform: uppercase; }
        
        .col-materia { width: 55%; text-align: left; }
        .col-nota { width: 11%; text-align: center; font-weight: bold; height: 26px; vertical-align: middle; }

        .datos-alumno-table { width:100%; margin-bottom: 20px; font-size: 11px; border:none; }
        .datos-alumno-table td { border:none; vertical-align: top; padding: 5px; }
        .dato-label { font-weight: bold; }

        .bg-azul-suave { background-color: #f0f5fa; }
        .bg-blanco { background-color: #ffffff; }

        .leyenda-container { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; font-size: 12px; border: 1px solid #99bce8; }
        .leyenda-header { background-color: #dce4f3; text-align: center; font-weight: bold; padding: 6px; border-bottom: 1px solid #99bce8; color: #000; }
        .leyenda-body { text-align: center; padding: 15px; background-color: #fff; }
        .leyenda-item { display: inline-block; margin-right: 15px; vertical-align: middle; }
        
        .leyenda-square { display: inline-block; width: 14px; height: 14px; vertical-align: middle; margin-right: 5px; }
        .cuadro-calif { width: 16px; height: 16px; margin: 0 auto; display: block; border: 1px solid rgba(0,0,0,0.1); }

        @media print {
            .no-print { display: none !important; }
            .boleta-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
            body { background-color: white; }
            .col-md-6 { width: 50% !important; float: left; }
            .bg-azul-suave { background-color: #f0f5fa !important; -webkit-print-color-adjust: exact; }
            .cuadro-calif, .leyenda-square { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<?php
    function renderNotaCuadro($val, $es_porcentaje = false) {
        if ($val === '' || $val === null) return '';
        
        $n = floatval($val);

        // INASISTENCIAS: 1 decimal siempre + %
        if ($es_porcentaje) {
            return number_format($n, 1) . '%'; 
        }

        // MATERIAS: Cuadro de color
        $color = '';
        if ($n >= 9.0) $color = '#800000';      // Alcanzado (Guinda)
        elseif ($n >= 7.0) $color = '#000080';  // Proceso (Azul)
        elseif ($n >= 5.0) $color = '#006400';  // Requiere Apoyo (Verde)

        if ($color !== '') {
            return '<div class="cuadro-calif" style="background-color: ' . $color . ';"></div>';
        }
        
        return '';
    }

    function renderGrupoBoleta($grupo, $momentos) {
        if (empty($grupo['materias'])) return '';
        $html = '';
        
        if (!empty($grupo['titulo'])) {
            $html .= '<tr><td colspan="5" class="header-sub">' . esc($grupo['titulo']) . '</td></tr>';
        }

        $contador_fila = 1; 
        foreach ($grupo['materias'] as $m) {
            $clase_fondo = ($contador_fila % 2 != 0) ? 'bg-azul-suave' : 'bg-blanco';
            $html .= '<tr class="' . $clase_fondo . '">';
            $html .= '<td class="col-materia">' . esc($m['nombre']) . '</td>';
            
            $es_porcent = $m['isPercentage'] ?? ($m['es_porcentaje'] ?? false);
            $suma = 0; $count = 0;

            foreach ($momentos as $id_mes) {
                $nota = $m['notas'][$id_mes] ?? '';
                $html .= '<td class="col-nota">' . renderNotaCuadro($nota, $es_porcent) . '</td>';
                
                if ($nota !== '' && is_numeric($nota)) {
                    $val = floatval($nota);
                    
                    // FILTRO ANTI-CEROS: Ignorar meses no evaluados en materias
                    if (!$es_porcent && $val <= 0) {
                        continue; 
                    }
                    
                    $suma += $val; 
                    $count++;
                }
            }
            
            // Promedio Final Dinámico
            if ($count > 0) {
                $promedioVal = $suma / $count;
                
                // Si NO es asistencia, redondeamos el promedio (ej. 8.5 sube a 9)
                if (!$es_porcent) {
                    $promedioVal = round($promedioVal); 
                }
                
                $html .= '<td class="col-nota">' . renderNotaCuadro($promedioVal, $es_porcent) . '</td>';
            } else {
                $html .= '<td class="col-nota"></td>';
            }
            $html .= '</tr>';
            $contador_fila++;
        }
        return $html;
    }
?>

    <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <?php $rutaRegreso = (session('nivel') == 7) ? base_url('alumno/dashboard') : base_url('boleta/lista/' . $id_grado); ?>
            <a href="<?= $rutaRegreso ?>" class="btn btn-secondary btn-sm"><i class='bx bx-arrow-back'></i> Regresar</a>
            
            <div class="d-flex align-items-center justify-content-center gap-2">
                <?php if($id_anterior): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_anterior) ?>" class="btn btn-light border btn-sm"><i class="bx bx-chevron-left"></i></a>
                <?php endif; ?>
                <span style="font-weight: bold; font-size: 13px; color: #555; width: 60px; text-align: center;">KINDER</span>
                <?php if($id_siguiente): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_siguiente) ?>" class="btn btn-light border btn-sm"><i class="bx bx-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            
            <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bx bx-printer"></i> Imprimir</button>
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
                <td><span class="dato-label">ALUMNO:</span> <?= strtoupper(($alumno['ap_Alumno']??'') . ' ' . ($alumno['am_Alumno']??'') . ' ' . ($alumno['Nombre']??'')) ?></td>
                <td style="text-align: right;"><span class="dato-label">MATRÍCULA:</span> <?= $alumno['matricula'] ?></td>
            </tr>
            <tr>
                <td><span class="dato-label">GRADO:</span> <?= $alumno['nombreGrado'] ?? ($grado['nombreGrado'] ?? 'Kinder') ?></td>
                <td style="text-align: right;"><span class="dato-label">CICLO ESCOLAR:</span> <?= $ciclo['nombreCicloEscolar'] ?></td>
            </tr>
        </table>

        <div class="row">
            <div class="col-md-6" style="padding-right: 5px;">
                <table class="tabla-kinder">
                    <thead>
                        <tr class="header-main">
                            <th rowspan="2" class="col-materia">ASPECTOS A EVALUAR</th>
                            <th class="col-nota">1ER</th><th class="col-nota">2DO</th><th class="col-nota">3ER</th><th rowspan="2" class="col-nota">PROM</th>
                        </tr>
                        <tr class="header-main"><th colspan="3" class="col-nota">MOMENTO</th></tr>
                        <tr><th colspan="5" class="header-title"><?= esc($left_title ?? 'DESARROLLO PERSONAL Y SOCIAL') ?></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($left_groups as $g): ?>
                            <?= renderGrupoBoleta($g, $momentos ?? [1, 2, 3]) ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <table class="leyenda-container no-print">
                    <tr><td class="leyenda-header">NIVEL DE DESEMPEÑO:</td></tr>
                    <tr>
                        <td class="leyenda-body">
                            <div class="leyenda-item"><div class="leyenda-square" style="background-color: #800000;"></div><span style="color: #800000; font-weight: bold;">Alcanzado</span>,</div>
                            <div class="leyenda-item"><div class="leyenda-square" style="background-color: #000080;"></div><span style="color: #000080; font-weight: bold;">Proceso</span>,</div>
                            <div class="leyenda-item"><div class="leyenda-square" style="background-color: #006400;"></div><span style="color: #006400; font-weight: bold;">Requiere apoyo</span></div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6" style="padding-left: 5px;">
                <table class="tabla-kinder">
                    <thead>
                        <tr class="header-main">
                            <th rowspan="2" class="col-materia">ASPECTOS A EVALUAR</th>
                            <th class="col-nota">1ER</th><th class="col-nota">2DO</th><th class="col-nota">3ER</th><th rowspan="2" class="col-nota">PROM</th>
                        </tr>
                        <tr class="header-main"><th colspan="3" class="col-nota">MOMENTO</th></tr>
                        <tr><th colspan="5" class="header-title"><?= esc($right_title ?? 'PENSAMIENTO MATEMÁTICO') ?></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($right_groups as $g): ?>
                            <?= renderGrupoBoleta($g, $momentos ?? [1, 2, 3]) ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 50px; text-align: center; font-size: 11px; color: #000;">
            <div style="display: inline-block; width: 40%; border-top: 1px solid #000; padding-top: 5px;">Firma de Padre o Tutor</div>
            <div style="display: inline-block; width: 10%;"></div>
            <div style="display: inline-block; width: 40%; border-top: 1px solid #000; padding-top: 5px;">Firma del Director</div>
        </div>
        <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #888;">Este documento no es una boleta oficial</div>
    </div>
</body>
</html>