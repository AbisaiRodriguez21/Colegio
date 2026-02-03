<!DOCTYPE html>
<html lang="es">
<head>
    <?= view("partials/title-meta", ["title" => "Recibo"]); ?>
    <?= $this->include("partials/head-css") ?>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card p-5" style="max-width: 800px; margin: 0 auto;">
            <div class="text-center mb-4">
                <h3>St Joseph School</h3>
                <h5 class="text-muted">Comprobante de Recepción de Pago</h5>
            </div>
            <div class="row">
                <div class="col-6">
                    <strong>Alumno:</strong> <?= esc($nombre) ?><br>
                    <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($pago['fechaPago'])) ?>
                </div>
                <div class="col-6 text-end">
                    <h4 class="text-danger">FOLIO: <?= esc($folioVisual) ?></h4>
                </div>
            </div>
            <hr>
            <table class="table">
                <thead><tr><th>Concepto</th><th>Mes</th><th class="text-end">Importe</th></tr></thead>
                <tbody>
                    <tr>
                        <td><?= esc($pago['concepto']) ?></td>
                        <td><?= esc($pago['mes']) ?></td>
                        <td class="text-end">$<?= number_format($pago['cantidad'], 2) ?></td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center mt-5">
                <button onclick="window.print()" class="btn btn-primary no-print">Imprimir</button>
                <a href="<?= base_url('alumno/pagos') ?>" class="btn btn-secondary no-print">Volver</a>
            </div>
        </div>
    </div>
</body>
</html>