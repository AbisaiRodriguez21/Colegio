<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Leer Correo"]) ?>
    <?= $this->include("partials/head-css") ?>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>
        <div class="page-content">
            <div class="container-fluid">

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <h4 class="page-title">Lectura de Correo</h4>
                            <div>
                                <a href="<?= base_url('correo') ?>" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back me-1"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-grid">
                                    <a href="<?= base_url('correo/redactar') ?>" class="btn btn-danger btn-block text-white">Redactar Nuevo</a>
                                </div>
                                <div class="mail-list mt-4">
                                    <a href="<?= base_url('correo') ?>" class="list-group-item border-0 text-danger fw-bold">
                                        <i class="bx bx-paper-plane me-2"></i> Enviados / Historial
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="card">
                            <div class="card-body">
                                
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm">
                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary fs-5">
                                                <?= strtoupper(substr(session()->get('nombre') ?? 'U', 0, 1)) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="font-size-15 mb-1"><?= esc($c['asunto']) ?></h5>
                                        <p class="text-muted mb-0">
                                            Enviado a: <strong><?= esc($c['para']) ?></strong>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><?= date('d M Y, h:i A', strtotime($c['fecha_envio'])) ?></small>
                                        <div class="mt-1">
                                            <?php if($c['estado'] == 'enviado'): ?>
                                                <span class="badge bg-success">Enviado</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Error de Envío</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <hr/>

                                <div class="mt-4">
                                    <div class="text-muted font-size-14">
                                        <?= nl2br(esc($c['mensaje'])) ?>
                                    </div>
                                </div>

                                <hr/>

                                <?php if(!empty($c['adjunto'])): ?>
                                    <div class="mt-4">
                                        <h6 class="font-size-14 mb-3">Archivos Adjuntos</h6>
                                        <div class="row">
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card border shadow-none mb-0">
                                                    <div class="p-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 avatar-sm me-3">
                                                                <span class="avatar-title bg-soft-primary text-primary rounded">
                                                                    <i class="bx bx-file font-size-20"></i>
                                                                </span>
                                                            </div>
                                                            <div class="flex-grow-1 overflow-hidden">
                                                                <h6 class="font-size-14 mb-1 text-truncate">Archivo Adjunto</h6>
                                                                <small class="text-muted">Clic para descargar</small>
                                                            </div>
                                                            <div class="flex-shrink-0 ms-2">
                                                                <a href="<?= base_url($c['adjunto']) ?>" target="_blank" class="text-reset font-size-18">
                                                                    <i class="bx bxs-download"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <?= $this->include("partials/footer") ?>
        </div>
    </div>
    <?= $this->include("partials/vendor-scripts") ?>
</body>
</html>