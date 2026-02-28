<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Dashboard Administrador"]); ?>
    <?= $this->include("partials/head-css") ?>

    <style>
        .control-card {
            border: 1px solid #e6e6e6;
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            margin-bottom: 30px;
        }

        .control-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .control-title {
            font-size: 13px;
            font-weight: 700;
            color: #555;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .nav-tabs .nav-link {
            color: #666;
            font-weight: 500;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 12px 20px;
        }

        .nav-tabs .nav-link:hover {
            color: #5c6bc0;
        }

        .nav-tabs .nav-link.active {
            color: #5c6bc0;
            border-bottom: 2px solid #5c6bc0;
            background: transparent;
        }

        .grupo-lista {
            list-style-type: disc;
            padding-left: 25px;
            margin-top: 15px;
        }

        .grupo-lista li {
            margin-bottom: 8px;
            color: #5c6bc0;
        }

        .grupo-lista a {
            text-decoration: none;
            color: #5c6bc0;
            font-size: 14px;
            transition: color 0.2s;
        }

        .grupo-lista a:hover {
            color: #3b488e;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-4 mt-2">
                    <div class="col-12">
                        <div style="background-image: url('<?= base_url('images/photos/photo3@2xz.jpg') ?>'); background-size: cover; background-position: center; border-radius: 8px; position: relative; min-height: 160px; display: flex; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            <div style="background-color: rgba(30, 35, 40, 0.65); position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 8px;"></div>
                            <div style="position: relative; z-index: 1; padding: 30px;">
                                <h1 class="text-white mb-1" style="font-size: 26px; font-weight: 700;">Bienvenido(a) Director(a)</h1>
                                <h2 class="text-white-50 mb-2" style="font-size: 16px;"><?= esc($nombre) ?> <?= esc($apellidos) ?></h2>
                                
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 col-xl-7">

                        <div class="control-card">
                            <div class="control-header">
                                <h5 class="control-title">VER BOLETA/IMPRIMIR</h5>
                                <i class="bx bx-printer" style="color:#5c6bc0; font-size: 18px;"></i>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-tabs nav-justified" id="tabsBoleta" role="tablist">
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bol-kin">Kinder</a></li>
                                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#bol-pri">Primaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bol-sec">Secundaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bol-bac">Bachiller</a></li>
                                </ul>
                                <div class="tab-content p-3">
                                    <div class="tab-pane fade" id="bol-kin">
                                        <ul class="grupo-lista"><?php foreach ($kinder as $g): ?><li><a href="<?= base_url('boleta/lista/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                    <div class="tab-pane fade show active" id="bol-pri">
                                        <ul class="grupo-lista"><?php foreach ($primaria as $g): ?><li><a href="<?= base_url('boleta/lista/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                    <div class="tab-pane fade" id="bol-sec">
                                        <ul class="grupo-lista"><?php foreach ($secundaria as $g): ?><li><a href="<?= base_url('boleta/lista/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                    <div class="tab-pane fade" id="bol-bac">
                                        <ul class="grupo-lista"><?php foreach ($bachillerato as $g): ?><li><a href="<?= base_url('boleta/lista/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="control-card">
                            <div class="control-header">
                                <h5 class="control-title">CALIFICAR BOLETA BIMESTRE (SÁBANA)</h5>
                                <i class="bx bx-table" style="color:#5c6bc0; font-size: 18px;"></i>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-tabs nav-justified" id="tabsSabana" role="tablist">
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sab-kin">Kinder</a></li>
                                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#sab-pri">Primaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sab-sec">Secundaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sab-bac">Bachiller</a></li>
                                </ul>
                                <div class="tab-content p-3">
                                    <div class="tab-pane fade" id="sab-kin">
                                        <ul class="grupo-lista"><?php foreach ($kinder as $g): ?><li><a href="<?= base_url('boleta/calificar/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                    <div class="tab-pane fade show active" id="sab-pri">
                                        <ul class="grupo-lista"><?php foreach ($primaria as $g): ?><li><a href="<?= base_url('boleta/calificar/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                    <div class="tab-pane fade" id="sab-sec">
                                        <ul class="grupo-lista"><?php foreach ($secundaria as $g): ?><li><a href="<?= base_url('boleta/calificar/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                    <div class="tab-pane fade" id="sab-bac">
                                        <ul class="grupo-lista"><?php foreach ($bachillerato as $g): ?><li><a href="<?= base_url('boleta/calificar/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="control-card">
                            <div class="control-header">
                                <h5 class="control-title">CALIFICAR BOLETA TODO BIMESTRE</h5>
                                <i class="bx bx-list-ul" style="color:#5c6bc0; font-size: 18px;"></i>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-tabs nav-justified" id="tabsLista" role="tablist">
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#lis-kin">Kinder</a></li>
                                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#lis-pri">Primaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#lis-sec">Secundaria</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#lis-bac">Bachiller</a></li>
                                </ul>
                                <div class="tab-content p-3">
                                    <div class="tab-pane fade" id="lis-kin">
                                        <ul class="grupo-lista"><?php foreach ($kinder as $g): ?><li><a href="<?= base_url('calificaciones_bimestre/lista/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                    <div class="tab-pane fade show active" id="lis-pri">
                                        <ul class="grupo-lista"><?php foreach ($primaria as $g): ?><li><a href="<?= base_url('calificaciones_bimestre/lista/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                    <div class="tab-pane fade" id="lis-sec">
                                        <ul class="grupo-lista"><?php foreach ($secundaria as $g): ?><li><a href="<?= base_url('calificaciones_bimestre/lista/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                    <div class="tab-pane fade" id="lis-bac">
                                        <ul class="grupo-lista"><?php foreach ($bachillerato as $g): ?><li><a href="<?= base_url('calificaciones_bimestre/lista/' . $g['id_grado']) ?>"><?= esc($g['nombreGrado']) ?></a></li><?php endforeach; ?></ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <?= $this->include("partials/footer") ?>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
</body>

</html>