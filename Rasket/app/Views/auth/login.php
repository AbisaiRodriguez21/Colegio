<!DOCTYPE html>
<html lang="es">

<head>
    <?php echo view("partials/title-meta", array("title" =>  "Inicio de sesión")) ?>
    <?= $this->include("partials/head-css") ?>
    <style>
        body {
            background: url("<?= base_url('assets/img/bg-school.jpg') ?>") no-repeat center center fixed;
            background-size: cover;
            font-family: 'Open Sans', sans-serif;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(6px);
        }

        .auth-logo img {
            width: 110px;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .btn-primary {
            background-color: #2a4d8f;
            border: none;
        }

        .btn-primary:hover {
            background-color: #1f3a70;
        }

        footer {
            text-align: center;
            color: #888;
            margin-top: 30px;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <div class="account-pages pt-4 pb-5">
        <div class="container">
            <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
                <div class="col-md-6 col-lg-5">
                    <div class="card auth-card">
                        <div class="card-body px-4 py-5">
                            <div class="text-center auth-logo">
                            </div>

                            <h2 class="fw-bold text-center fs-18">Bienvenido</h2>
                            <p class="text-muted text-center mt-1 mb-4">Inicia sesión para acceder al sistema.</p>

                            <form action="<?= base_url('auth/attemptLogin') ?>" method="POST">
                                <?php if (session()->getFlashdata('error')) : ?>
                                    <div class="alert alert-danger text-center py-2">
                                        <?= session()->getFlashdata('error') ?>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label" for="usuario">Usuario</label>
                                    <input type="text" id="usuario" name="usuario" class="form-control"
                                        placeholder="Ingresa tu usuario" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="pass">Contraseña</label>
                                    <input type="password" id="pass" name="pass" class="form-control"
                                        placeholder="Ingresa tu contraseña" required>
                                </div>

                                <div class="text-center d-grid">
                                    <button class="btn btn-primary" type="submit">Entrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <footer>
                        © Fénix Consultores — Sistema Escolar
                    </footer>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include("partials/vendor-scripts") ?>
</body>

</html>
