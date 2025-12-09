<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Calificaciones</title>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        /* Estilos específicos para impresión y visualización de boleta */
        body { background-color: #f5f5f5; font-family: 'Arial', sans-serif; }
        .boleta-container {
            background: white;
            width: 100%;
            max-width: 1100px;
            margin: 20px auto;
            padding: 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .header-table td { padding: 5px; vertical-align: top; }
        .label-dato { font-size: 10px; color: #666; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .valor-dato { font-size: 14px; font-weight: bold; color: #000; }
        
        /* Tabla de Calificaciones */
        .tabla-calif { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        .tabla-calif th, .tabla-calif td { border: 1px solid #999; padding: 4px; text-align: center; }
        
        /* Encabezados */
        .th-main { background-color: #e9ecef; font-weight: bold; }
        .th-prom { background-color: #dbeafe; color: #1e40af; font-weight: bold; } /* Azulito */
        
        /* Celdas */
        .td-materia { text-align: left; padding-left: 10px; font-weight: 600; }
        .td-prom { background-color: #eff6ff; font-weight: bold; color: #000; }
        .td-final { background-color: #bfdbfe; font-weight: bold; color: #000; }
        
        /* Calificaciones reprobatorias */
        .reprobado { color: red; font-weight: bold; }

        /* Firmas */
        .firmas-container { margin-top: 60px; display: flex; justify-content: space-between; }
        .firma-box { width: 40%; text-align: center; border-top: 1px solid #000; padding-top: 10px; font-size: 12px; }

        /* Botones de acción (No imprimir) */
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .boleta-container { box-shadow: none; margin: 0; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary btn-sm"><i class="bx bx-arrow-back"></i> Volver</a>
            <div>
                <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bx bx-printer"></i> Imprimir</button>
            </div>
        </div>
    </div>

    <div class="boleta-container">
        
        <div class="row mb-4">
            <div class="col-6">
                <img src="<?= base_url('images/LogoST.png') ?>" alt="Logo" style="height: 60px;">
            </div>
            <div class="col-6 text-end">
                <small class="text-muted">FECHA: <?= date('d/m/Y') ?></small>
            </div>
        </div>

        <table class="header-table" width="100%">
            <tr>
                <td width="40%">
                    <span class="label-dato">NOMBRE:</span>
                    <span class="valor-dato"><?= esc($alumno['Nombre']) ?> <?= esc($alumno['ap_Alumno']) ?> <?= esc($alumno['am_Alumno']) ?></span>
                </td>
                <td width="20%">
                    <span class="label-dato">GRADO:</span>
                    <span class="valor-dato"><?= esc($alumno['nombreGrado']) ?></span>
                </td>
                <td width="20%">
                    <span class="label-dato">MATRÍCULA:</span>
                    <span class="valor-dato"><?= esc($alumno['matricula']) ?></span>
                </td>
                <td width="20%">
                    <span class="label-dato">CICLO ESCOLAR:</span>
                    <span class="valor-dato"><?= esc($ciclo['nombreCicloEscolar']) ?></span>
                </td>
            </tr>
        </table>

        <table class="tabla-calif">
            <thead>
                <tr>
                    <th rowspan="2" class="th-main" width="30%">DESARROLLO</th>
                    <th colspan="4" class="th-main">Trimestre 1</th>
                    <th colspan="4" class="th-main">Trimestre 2</th>
                    <th colspan="4" class="th-main">Trimestre 3</th>
                    <th rowspan="2" class="th-prom">Prom Final</th>
                </tr>
                <tr>
                    <th>Sep</th> <th>Oct</th> <th>Nov</th> <th class="th-prom">Prom</th>
                    <th>Dic</th> <th>Ene</th> <th>Feb</th> <th class="th-prom">Prom</th>
                    <th>Mar</th> <th>Abr</th> <th>May</th> <th class="th-prom">Prom</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($boleta as $m): ?>
                <tr>
                    <td class="td-materia"><?= esc($m['nombre']) ?></td>

                    <td><?= $m['notas'][1] ?? '-' ?></td> <td><?= $m['notas'][2] ?? '-' ?></td> <td><?= $m['notas'][3] ?? '-' ?></td> <td class="td-prom"><?= $m['p_t1'] > 0 ? $m['p_t1'] : '-' ?></td>

                    <td><?= $m['notas'][4] ?? '-' ?></td> <td><?= $m['notas'][5] ?? '-' ?></td> <td><?= $m['notas'][6] ?? '-' ?></td> <td class="td-prom"><?= $m['p_t2'] > 0 ? $m['p_t2'] : '-' ?></td>

                    <td><?= $m['notas'][7] ?? '-' ?></td> <td><?= $m['notas'][8] ?? '-' ?></td> <td><?= $m['notas'][9] ?? '-' ?></td> <td class="td-prom"><?= $m['p_t3'] > 0 ? $m['p_t3'] : '-' ?></td>

                    <td class="td-final"><?= $m['final'] > 0 ? $m['final'] : '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="firmas-container">
            <div class="firma-box">
                Firma de Padre o Tutor
            </div>
            <div class="firma-box">
                Firma del Director
            </div>
        </div>

        <div class="text-center mt-5 text-muted">
            <small>Este documento no es una boleta oficial</small>
        </div>

    </div>

</body>
</html>