<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago - Folio <?= esc($folio ?? '000000') ?></title>
    
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            background-color: #fff;
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #555;
            margin-bottom: 30px;
        }

        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .info-logo {
            width: 25%;
        }
        .info-logo img {
            max-width: 100%;
            height: auto;
        }

        .info-school {
            width: 40%;
            padding-left: 20px;
        }
        .info-school h1 {
            font-size: 26px;
            color: #0c335e;  
            margin: 0 0 10px 0;
            font-weight: 500;
        }
        .info-school p {
            margin: 3px 0;
            font-size: 14px;
        }
        .info-school .folio-number {
            font-weight: bold;
            color: #0c335e;
            margin-top: 15px;
            display: block;
        }

        .info-student {
            width: 35%;
        }
        .info-student h2 {
            font-size: 26px;
            color: #0c335e;
            margin: 0 0 10px 0;
            font-weight: 500;
        }
        .info-student p {
            margin: 4px 0;
            font-size: 13px;
        }
        .info-student .text-uppercase {
            text-transform: uppercase;
        }

        /* Tabla de pagos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #dcdcdc;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #fff;
            font-weight: bold;
        }
        td.desc-col {
            text-align: left;
        }
        .desc-col p {
            margin: 5px 0;
        }
        .desc-col strong {
            display: inline-block;
            font-weight: bold;
            color: #000;
        }
        .img-proof {
            max-width: 80px;
            max-height: 50px;
            border: 1px solid #eee;
        }
        
        .totals-row td {
            font-weight: bold;
            font-size: 14px;
        }
        .totals-row .label-right {
            text-align: right;
            padding-right: 20px;
        }

        .receipt-footer {
            margin-top: 40px;
            text-align: center;
        }
        .btn-back {
            display: inline-block;
            padding: 8px 20px;
            border: 2px solid #000;
            background-color: #fff;
            color: #000;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            float: left;
            transition: all 0.2s;
        }
        .btn-back:hover {
            background-color: #f0f0f0;
        }
        .disclaimer {
            clear: both;
            padding-top: 15px;
            font-size: 12px;
            color: #333;
        }

        @media print {
            .btn-back {
                display: none !important;  
            }
            body {
                padding: 0;
            }
            .receipt-container {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        
        <div class="top-bar">
            <span><?= date('d/m/y, g:i a') ?></span>
            <span>St. Joseph School | App Fxn-Escolar</span>
        </div>

        <div class="info-header">
            <div class="info-logo">
                <img src="<?= base_url('images/LogoST.png') ?>" alt="St Joseph School Logo">
            </div>
            
            <div class="info-school">
                <h1>St Joseph<br>School</h1>
                <p>Saint Joseph School A.C.</p>
                <p>Plantel Periférico</p>
                <p>Ciclo Escolar: <?= esc($cicloEscolar ?? '') ?></p>
                <span class="folio-number">#FOLIO: <?= esc($folio ?? '000000') ?></span>
            </div>

            <div class="info-student">
                <h2>Alumno</h2>
                <p class="text-uppercase"><?= esc($alumno['ap_Alumno'] ?? '') ?> <?= esc($alumno['am_Alumno'] ?? '') ?> <?= esc($alumno['Nombre'] ?? '') ?></p>
                <p><strong>Grado:</strong> <?= esc($alumno['nombreGrado'] ?? '') ?></p>
                <p><strong>Matricula:</strong> <?= esc($alumno['matricula'] ?? '') ?></p>
                <p>Lo realizó: <?= esc($realizadoPor ?? 'Administración') ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%"></th> <th width="40%">DESCRIPCIÓN</th>
                    <th width="20%">IMAGEN</th>
                    <th width="20%">FORMA PAGO</th>
                    <th width="15%">MONTO</th>
                </tr>
            </thead>
            <?php 
                $subtotal = 0;
                $contador = 1;
                
                if(!empty($pagos) && is_array($pagos)):
                    foreach ($pagos as $pago): 
                        $subtotal += $pago['total'];  
                ?>
                <tr>
                    <td><?= $contador++ ?></td>
                    <td class="desc-col">
                        <p><strong>Mes</strong><br> <?= esc($pago['mes'] ?? 'N/A') ?></p>
                        <p><strong>Operación</strong><br> <?= esc($pago['concepto'] ?? 'N/A') ?></p>
                        <p><strong>Nota</strong><br> <?= esc($pago['nota'] ?? '') ?></p>
                    </td>
                    <td>
                        <?php if(!empty($pago['ficha'])): ?>
                            <img src="<?= base_url('pagos/' . $pago['ficha']) ?>" class="img-proof" alt="Comprobante">
                        <?php else: ?>
                            <span style="color:#ccc; font-size:10px;">Sin imagen</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($pago['modoPago'] ?? 'N/A') ?></td>
                    <td>$ <?= number_format($pago['total'] ?? 0, 2) ?></td>
                </tr>
                <?php 
                    endforeach; 
                endif; 
                ?>
                <tr class="totals-row">
                    <td colspan="4" class="label-right">Subtotal</td>
                    <td>$ <?= number_format($subtotal, 2) ?></td>
                </tr>
                <tr class="totals-row">
                    <td colspan="4" class="label-right">TOTAL</td>
                    <td>$ <?= number_format($subtotal, 2) ?></td>
                </tr>
        </table>

        <div class="receipt-footer">
            <a href="<?= base_url('alumno/pagos') ?>" class="btn-back">Volver a la lista</a>
            <div class="disclaimer">
                Gracias por su pago, este no es un comprobante con valor fiscal. Para uso interno de la institución
            </div>
        </div>

    </div>

</body>
</html>