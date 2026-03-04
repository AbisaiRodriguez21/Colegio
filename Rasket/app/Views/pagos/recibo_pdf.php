<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago - Folio <?= $pago['id_folio'] ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            background-color: #fff;
            margin: 0;
            padding: 20px;
            font-size: 13px;
        }

        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Top Bar con tabla para DomPDF */
        .top-bar {
            width: 100%;
            font-size: 11px;
            color: #555;
            margin-bottom: 20px;
        }

        /* Cabecera con tabla en lugar de flexbox */
        .header-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            border: none;
            padding: 0;
        }

        .info-logo { width: 25%; }
        .info-logo img { width: 140px; height: auto; }

        .info-school { width: 40%; padding-left: 20px; }
        .info-school h1 { font-size: 26px; color: #0c335e; margin: 0 0 10px 0; font-weight: 500; }
        .info-school p { margin: 3px 0; font-size: 14px; }
        .folio-number { font-weight: bold; color: #0c335e; margin-top: 15px; display: block; }

        .info-student { width: 35%; }
        .info-student h2 { font-size: 26px; color: #0c335e; margin: 0 0 10px 0; font-weight: 500; }
        .info-student p { margin: 4px 0; font-size: 13px; }
        .text-uppercase { text-transform: uppercase; }

        /* Tabla de pagos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }
        .data-table th, .data-table td {
            border: 1px solid #dcdcdc;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }
        .data-table th { background-color: #fff; font-weight: bold; }
        
        .desc-col { text-align: left; }
        .desc-col p { margin: 5px 0; }
        .desc-col strong { display: inline-block; font-weight: bold; color: #000; }
        
        .img-proof { max-width: 80px; max-height: 50px; border: 1px solid #eee; }
        
        .totals-row td { font-weight: bold; font-size: 14px; background-color: #f9f9f9; }
        .label-right { text-align: right; padding-right: 20px; }

        .disclaimer {
            text-align: center;
            padding-top: 15px;
            font-size: 12px;
            color: #333;
            border-top: 1px solid #eee;
            margin-top: 40px;
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        
        <table class="top-bar">
            <tr>
                <td align="left"><?= date('d/m/Y, g:i a') ?></td>
                <td align="right">St. Joseph School | App Fxn-Escolar</td>
            </tr>
        </table>

        <table class="header-table">
            <tr>
                <td class="info-logo">
                    <img src="<?= base_url('images/LogoST.png') ?>" alt="Logo">
                </td>
                
                <td class="info-school">
                    <h1>St Joseph<br>School</h1>
                    <p>Saint Joseph School A.C.</p>
                    <p>Plantel Periférico</p>
                    <span class="folio-number">#FOLIO: <?= $pago['id_folio'] ?></span>
                </td>

                <td class="info-student">
                    <h2>Alumno</h2>
                    <p class="text-uppercase"><?= $alumno['ap_Alumno'] ?> <?= $alumno['am_Alumno'] ?> <?= $alumno['Nombre'] ?></p>
                    <p><strong>Matricula:</strong> <?= $alumno['matricula'] ?? 'N/A' ?></p>
                    <p><strong>Lo realizó:</strong> Administrador</p>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="45%">DESCRIPCIÓN</th>
                    <th width="30%">IMAGEN / COMPROBANTE</th>
                    <th width="25%">MONTO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="desc-col">
                        <p><strong>Mes:</strong> <?= !empty($pago['mes']) ? $pago['mes'] : 'N/A' ?></p>
                        <p><strong>Operación:</strong> <?= $pago['concepto'] ?></p>
                    </td>
                    <td>
                        <?php if(!empty($pago['ficha'])): ?>
                            <img src="<?= base_url('pagos/' . $pago['ficha']) ?>" class="img-proof">
                        <?php else: ?>
                            <span style="color:#ccc; font-size:10px;">Sin imagen</span>
                        <?php endif; ?>
                    </td>
                    <td>$ <?= $montoFormateado ?></td>
                </tr>
                <tr class="totals-row">
                    <td colspan="2" class="label-right">TOTAL APROBADO</td>
                    <td>$ <?= $montoFormateado ?></td>
                </tr>
            </tbody>
        </table>

        <div class="disclaimer">
            Gracias por su pago, este no es un comprobante con valor fiscal. Para uso interno de la institución.
        </div>

    </div>

</body>
</html>