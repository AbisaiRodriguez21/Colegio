<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .email-container { background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { background-color: #2c3e50; color: #ffffff; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; color: #333; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
        .highlight { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2>💰 Nuevo Pago Recibido</h2>
        </div>
        <div class="content">
            <p>Hola Administración,</p>
            <p>El alumno <strong><?= esc($datos['qrp']) ?></strong> ha reportado un nuevo pago a través de la plataforma.</p>
            
            <table class="table">
                <tr>
                    <th>Folio:</th>
                    <td class="highlight">#<?= esc($folio) ?></td>
                </tr>
                <tr>
                    <th>Concepto:</th>
                    <td><?= esc($datos['concepto']) ?> (<?= esc($datos['mes']) ?>)</td>
                </tr>
                <tr>
                    <th>Monto Total:</th>
                    <td>$<?= number_format($datos['cantidad'] + $datos['recargos'], 2) ?></td>
                </tr>
                <tr>
                    <th>Modo de Pago:</th>
                    <td><?= esc($datos['modoPago']) ?></td>
                </tr>
                <tr>
                    <th>Fecha Reporte:</th>
                    <td><?= date('d/m/Y H:i') ?></td>
                </tr>
            </table>

            <p style="margin-top: 20px;">
                <em>Por favor ingresa al panel administrativo para validar la ficha adjunta.</em>
            </p>
        </div>
        <div class="footer">
            <p>Sistema Escolar St Joseph School &copy; <?= date('Y') ?></p>
        </div>
    </div>
</body>
</html>