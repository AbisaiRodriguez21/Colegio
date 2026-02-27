<!DOCTYPE html>
<html lang="es" data-bs-theme="light">

<head>
    <?= view("partials/title-meta", ["title" => "Seleccionar Periodo"]); ?>
    <?= $this->include("partials/head-css") ?>
    <style>
        .zen-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }

        .card-periodo {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .card-periodo:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-color: #3b7ddd;
        }

        .card-periodo input {
            display: none;
            /* Ocultamos el radio button real */
        }

        /* Estilo cuando está seleccionado (Click) */
        .card-periodo.selected {
            background-color: #3b7ddd;
            color: white;
            border-color: #3b7ddd;
        }

        .card-periodo.selected h4 {
            color: white !important;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?= $this->include("partials/menu") ?>

<br>
<br>
<br>
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-dark">Hola, Titular de <?= esc($grado) ?></h1>
                <p class="lead text-muted">Selecciona el <?= strtolower($titulo) ?> que deseas calificar hoy.</p>
            </div>

            <form action="<?= base_url('titular/abrir-sabana') ?>" method="post" id="formPeriodo">

                <div class="container">
                    <div class="row justify-content-center">
                        <?php foreach ($periodos as $p): ?>
                            <div class="col-md-4 col-lg-3 mb-4">
                                <label class="w-100">
                                    <input type="radio" name="id_periodo" value="<?= $p['id'] ?>" required>
                                    <div class="card p-4 text-center card-periodo" onclick="seleccionarTarjeta(this)">
                                        <h4 class="mb-0 text-primary"><?= esc($p['nombre']) ?></h4>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary me-3 btn-lg">Cancelar</a>
                    <button type="submit" class="btn btn-primary btn-lg px-5">Continuar &rarr;</button>
                </div>

            </form>

        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>

    <script>
        function seleccionarTarjeta(elemento) {
            // Quitar clase 'selected' a todas
            document.querySelectorAll('.card-periodo').forEach(el => el.classList.remove('selected'));
            // Poner clase 'selected' a la clickeada
            elemento.classList.add('selected');
        }
    </script>

</body>

</html>