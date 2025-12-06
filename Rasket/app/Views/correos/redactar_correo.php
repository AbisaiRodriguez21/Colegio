<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">
<head>
    <?= view("partials/title-meta", ["title" => "Redactar Correo"]) ?>
    <?= $this->include("partials/head-css") ?>
    <style>.hidden-field { display: none; }</style>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>
        <div class="page-content">
            <div class="container-fluid">
                
                <div class="row mb-3">
                    <div class="col-12">
                         <div class="page-title-box">
                              <h4 class="page-title">Redactar Mensaje</h4>
                         </div>
                    </div>
                </div>
                
                <form action="<?= base_url('correo/enviar') ?>" method="post" enctype="multipart/form-data">
                    
                    <div class="card">
                        <div class="card-body">
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Enviar a:</label>
                                    <select class="form-select" name="tipo_destinatario" id="tipo_destinatario" required>
                                        <option value="" selected disabled>-- Selecciona --</option>
                                        <option value="individual">Individual</option>
                                        <option value="grado">Por Grado</option>
                                        <option value="nivel">Por Nivel Educativo</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-8 hidden-field" id="div_individual">
                                    <label class="form-label fw-bold">Correo:</label>
                                    <input type="email" class="form-control" name="email_individual">
                                </div>

                                <div class="col-md-8 hidden-field" id="div_grado">
                                    <label class="form-label fw-bold">Grado:</label>
                                    <select class="form-select" name="id_grado">
                                        <option value="" selected disabled>Selecciona...</option>
                                        <?php foreach ($grados as $g): ?>
                                            <option value="<?= $g['id_grado'] ?>"><?= esc($g['nombreGrado']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-8 hidden-field" id="div_nivel">
                                    <label class="form-label fw-bold">Nivel:</label>
                                    <select class="form-select" name="id_nivel">
                                        <option value="" selected disabled>Selecciona...</option>
                                        <?php foreach ($niveles_educativos as $id => $nombre): ?>
                                            <option value="<?= $id ?>"><?= esc($nombre) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Asunto</label>
                                <input type="text" class="form-control" name="asunto" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Archivo Adjunto (Opcional)</label>
                                <input class="form-control" type="file" name="adjunto">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mensaje</label>
                                <textarea class="form-control" name="mensaje" rows="8" required></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('correo') ?>" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>

                        </div>
                    </div>
                </form>

            </div>
            <?= $this->include("partials/footer") ?>
        </div>
    </div>
    <?= $this->include("partials/vendor-scripts") ?>
    
    <script>
        // Script para mostrar/ocultar campos (Mismo de antes)
        document.addEventListener('DOMContentLoaded', function() {
            const selector = document.getElementById('tipo_destinatario');
            const divs = {
                individual: document.getElementById('div_individual'),
                grado: document.getElementById('div_grado'),
                nivel: document.getElementById('div_nivel')
            };

            selector.addEventListener('change', function() {
                // Ocultar todos
                Object.values(divs).forEach(el => {
                    el.style.display = 'none';
                    const inputs = el.querySelectorAll('input, select');
                    inputs.forEach(i => i.required = false);
                });

                // Mostrar seleccionado
                if (divs[this.value]) {
                    divs[this.value].style.display = 'block';
                    const inputs = divs[this.value].querySelectorAll('input, select');
                    inputs.forEach(i => i.required = true);
                }
            });
        });
    </script>
</body>
</html>