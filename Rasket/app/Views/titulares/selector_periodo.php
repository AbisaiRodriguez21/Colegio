<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Seleccionar Periodo"]); ?>
    <?= $this->include("partials/head-css") ?>
    
    <style>
        .card-periodo {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid transparent;
            cursor: pointer;
            /* Forzamos un diseño de tarjeta que se adapte al modo oscuro/claro */
            background-color: var(--bs-card-bg);
            border-radius: 8px;
        }

        .card-periodo:hover {
            transform: translateY(-5px);
            border-color: #5c6bc0;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        /* Estilo cuando está seleccionado */
        .card-periodo.selected {
            background-color: #5c6bc0 !important;
            border-color: #5c6bc0 !important;
        }

        .card-periodo.selected h4 {
            color: #ffffff !important;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        
        <?= $this->include("partials/menu") ?>

        <div class="page-content">
            <div class="container-fluid" style="padding-top: 40px;">

                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold text-body">Hola, Titular de <?= esc($grado ?? 'Grupo') ?></h1>
                    <p class="lead text-muted">Selecciona el <?= strtolower($titulo ?? 'periodo') ?> que deseas calificar hoy.</p>
                </div>

                <form action="<?= base_url('titular/abrir-sabana') ?>" method="post" id="formPeriodo">

                    <div class="row justify-content-center">
                        <?php if (!empty($periodos)): ?>
                            <?php foreach ($periodos as $p): ?>
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <label class="w-100" style="cursor: pointer;">
                                        
                                        <input type="radio" name="id_periodo" value="<?= esc($p['id']) ?>" class="d-none" required>
                                        
                                        <div class="card p-4 text-center card-periodo" onclick="seleccionarTarjeta(this)">
                                            <h4 class="mb-0 fw-bold" style="color: #5c6bc0;"><?= esc($p['nombre']) ?></h4>
                                        </div>
                                        
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center">
                                <p class="text-muted">No hay periodos disponibles para calificar en este momento.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="text-center mt-5">
                        <a href="<?= base_url('titular/dashboard') ?>" class="btn btn-outline-secondary me-3 btn-lg">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-lg px-5" style="background-color: #5c6bc0; border-color: #5c6bc0;">Continuar &rarr;</button>
                    </div>

                </form>

            </div> </div> <?= $this->include("partials/footer") ?>

    </div> <?= $this->include("partials/vendor-scripts") ?>

    <script>
        function seleccionarTarjeta(elemento) {
            // 1. Quitar clase 'selected' a todas las tarjetas
            document.querySelectorAll('.card-periodo').forEach(el => el.classList.remove('selected'));
            
            // 2. Poner clase 'selected' solo a la tarjeta clickeada
            elemento.classList.add('selected');
        }
    </script>

</body>
</html>