<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Asignar Área Bachiller"]); ?>
    <?= $this->include("partials/head-css") ?>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title">Lista de alumnos de 3° Bachiller para área</h4>
                        </div>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-all me-2"></i>
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-block-helper me-2"></i>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <form action="<?= base_url('asignar-area/actualizar') ?>" method="post">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Actualizar Área
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped dt-responsive nowrap w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 40%;">Nombre</th>
                                                    <th style="width: 15%;">Estatus</th>
                                                    <th style="width: 20%;">Grado</th>
                                                    <th style="width: 25%;" class="text-center">Área (Siglas)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($alumnos)): ?>
                                                    <?php foreach ($alumnos as $alumno): ?>
                                                        <tr>
                                                            <td class="align-middle">
                                                                <input type="hidden" name="id[]" value="<?= $alumno['id'] ?>">
                                                                
                                                                <strong>
                                                                    <?= esc($alumno['ap_Alumno'] . ' ' . $alumno['am_Alumno'] . ' ' . $alumno['Nombre']) ?>
                                                                </strong>
                                                            </td>
                                                            <td class="align-middle">
                                                                <span class="badge bg-success">Activo</span>
                                                            </td>
                                                            <td class="align-middle">
                                                                <?= esc($alumno['nombreGrado']) ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <select name="area[]" class="form-select form-select-sm text-center fw-bold">
                                                                    <?php foreach ($areas as $valor => $sigla): ?>
                                                                        <option value="<?= $valor ?>" 
                                                                            <?= ($alumno['area3B'] == $valor) ? 'selected' : '' ?>>
                                                                            <?= $sigla ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">No se encontraron alumnos en 3° de Bachillerato.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Actualizar Área
                                        </button>
                                    </div>

                                </form>
                                <div class="row mt-4 align-items-center">
                                    
                                    <div class="col-sm-12 col-md-5">
                                        <?php if ($info_paginacion['total'] > 0): ?>
                                            <div class="dataTables_info" role="status" aria-live="polite">
                                                Mostrando <strong><?= $info_paginacion['inicio'] ?></strong> a <strong><?= $info_paginacion['fin'] ?></strong> de <strong><?= number_format($info_paginacion['total']) ?></strong> registros
                                            </div>
                                        <?php else: ?>
                                            <div class="dataTables_info">No hay registros para mostrar.</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-sm-12 col-md-7">
                                        <div class="d-flex justify-content-end">
                                            <?= $pager->links('default', 'bootstrap_pagos') ?>
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
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
</body>
</html>