<!DOCTYPE html>
<html>
<head>
    <title>Comprobante de Pago</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 200px; }
        .info-box { width: 100%; margin-bottom: 20px; }
        .info-col { width: 48%; display: inline-block; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="http://appse.sjs.edu.mx/assets/logosjs.png" class="logo" alt="Logo SJS">
            <h2>St Joseph School</h2>
            <p>Plantel Periférico<br>Ciclo Escolar: 2025 - 2026</p>
            <h3>#Folio: <?= esc($folio) ?></h3>
        </div>

        <div class="info-box">
            <div class="info-col">
                <strong>Datos del Alumno:</strong><br>
                <?= esc($alumno['NombreCompleto']) ?><br>
                <strong>Grado Nuevo:</strong> <?= esc($nuevo_grado_nombre) ?><br>
                <strong>Matrícula/Email:</strong> <?= esc($alumno['email']) ?>
            </div>
            <div class="info-col text-right">
                <strong>Fecha:</strong> <?= date('d-m-Y H:i') ?><br>
                <strong>Atendió:</strong> <?= esc($qrp) ?>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Método</th>
                    <th>Nota</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= esc($pago['concepto']) ?> (<?= esc($pago['mes']) ?>)</td>
                    <td><?= esc($pago['modoPago']) ?></td>
                    <td><?= esc($pago['nota']) ?></td>
                    <td class="text-right">$ <?= number_format($pago['cantidad'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>$ <?= number_format($pago['cantidad'], 2) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Gracias por su pago. Este no es un comprobante con valor fiscal. Para uso interno de la institución.</p>
        </div>
    </div>
</body>
</html>