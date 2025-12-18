<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta Preescolar</title>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; }
        .boleta-paper {
            background: white; width: 100%; max-width: 1100px; margin: 30px auto; padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); min-height: 800px;
        }
        
        /* TABLA KINDER */
        .tabla-kinder { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 15px; }
        .tabla-kinder th, .tabla-kinder td { border: 1px solid #333; padding: 4px; }
        
        .header-main { background-color: #eaeaea; font-weight: bold; text-align: center; font-size: 11px; }
        .header-sub { background-color: #fff; font-weight: bold; text-align: left; padding-left: 10px; font-size: 10px; text-transform: uppercase; }
        
        .col-materia { width: 60%; text-align: left; }
        .col-nota { width: 13%; text-align: center; font-weight: bold; }
        
        /* TITULOS DE COLUMNAS */
        .column-title {
            background-color: #444; color: #fff; text-align: center; padding: 5px; 
            font-weight: bold; margin-bottom: 0; border: 1px solid #333;
        }

        /* Datos Alumno */
        .datos-alumno-table { width:100%; margin-bottom: 20px; font-size: 11px; border:none; }
        .datos-alumno-table td { border:none; vertical-align: top; padding: 5px; }
        .dato-label { font-weight: bold; }
        
        @media print {
            .no-print { display: none !important; }
            .boleta-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
            body { background-color: white; }
            .col-md-6 { width: 50% !important; float: left; }
        }
    </style>
</head>
<body>

<?php
    // HELPER PARA TRADUCIR CALIFICACIONES KINDER
    function renderNota($val, $tipo, $es_porcentaje = false) {
        if ($val === '' || $val === null) return '';
        
        // Si es asistencia (porcentaje)
        if ($es_porcentaje) {
            return round($val) . '%';
        }

        // Si el JSON pide letras (scoreTranslateType: "letter")
        if ($tipo === 'letter') {
            $n = floatval($val);
            if ($n >= 4) return 'IV';  // Sobresaliente
            if ($n >= 3) return 'III'; // Satisfactorio
            if ($n >= 2) return 'II';  // Básico
            if ($n >= 1) return 'I';   // Insuficiente
            return $val; 
        }
        
        return $val; 
    }

    // Helper para renderizar una tabla de grupo
    function renderGrupo($grupo, $momentos, $translateType) {
        if (empty($grupo['materias'])) return '';
        
        $html = '';
        // Título del subgrupo )
        if (!empty($grupo['titulo'])) {
            $html .= '<tr><td colspan="4" class="header-sub">' . esc($grupo['titulo']) . '</td></tr>';
        }

        foreach ($grupo['materias'] as $m) {
            $html .= '<tr>';
            $html .= '<td class="col-materia">' . esc($m['nombre']) . '</td>';
            
            // Determinar si es porcentaje (para inasistencias)
            $es_porcent = $m['es_porcentaje'] ?? false;

            foreach ($momentos as $id_mes) {
                $nota = $m['notas'][$id_mes] ?? '';
                $html .= '<td class="col-nota">' . renderNota($nota, $translateType, $es_porcent) . '</td>';
            }
            $html .= '</tr>';
        }
        return $html;
    }
?>

    <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= base_url('boleta/lista/' . $id_grado) ?>" class="btn btn-secondary btn-sm"><i class="bx bx-arrow-back"></i> Regresar</a>
            <div>
                <?php if($id_anterior): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_anterior) ?>" class="btn btn-light border"><i class="bx bx-chevron-left"></i></a>
                <?php endif; ?>
                <?php if($id_siguiente): ?>
                    <a href="<?= base_url('boleta/ver/' . $id_grado . '/' . $id_siguiente) ?>" class="btn btn-light border"><i class="bx bx-chevron-right"></i></a>
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
                <td><span class="dato-label">ALUMNO:</span> <?= strtoupper($alumno['ap_Alumno'] . ' ' . $alumno['am_Alumno'] . ' ' . $alumno['Nombre']) ?></td>
                <td style="text-align: right;"><span class="dato-label">GRADO:</span> <?= $alumno['nombreGrado'] ?></td>
            </tr>
            <tr>
                <td><span class="dato-label">CICLO ESCOLAR:</span> <?= $ciclo['nombreCicloEscolar'] ?></td>
                <td style="text-align: right;"><span class="dato-label">MATRÍCULA:</span> <?= $alumno['matricula'] ?></td>
            </tr>
        </table>

        <div class="row">
            
            <div class="col-md-6" style="padding-right: 5px;">
                <div class="column-title">CAMPOS DE FORMACIÓN</div>
                <table class="tabla-kinder">
                    <thead>
                        <tr class="header-main">
                            <td class="col-materia">ASIGNATURA / ÁREA</td>
                            <td class="col-nota">1º</td>
                            <td class="col-nota">2º</td>
                            <td class="col-nota">3º</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($left_groups as $g): ?>
                            <?= renderGrupo($g, $momentos, $translateType) ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6" style="padding-left: 5px;">
                <div class="column-title">ÁREAS DE DESARROLLO</div>
                <table class="tabla-kinder">
                    <thead>
                        <tr class="header-main">
                            <td class="col-materia">ASIGNATURA / ÁREA</td>
                            <td class="col-nota">1º</td>
                            <td class="col-nota">2º</td>
                            <td class="col-nota">3º</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($right_groups as $g): ?>
                            <?= renderGrupo($g, $momentos, $translateType) ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div style="margin-top: 50px; text-align: center;">
            <div style="display: inline-block; width: 40%; border-top: 1px solid #000; padding-top: 5px;">
                DIRECTORA DEL NIVEL
            </div>
            <div style="display: inline-block; width: 10%;"></div>
            <div style="display: inline-block; width: 40%; border-top: 1px solid #000; padding-top: 5px;">
                MAESTRA DE GRUPO
            </div>
        </div>

    </div>

</body>
</html>