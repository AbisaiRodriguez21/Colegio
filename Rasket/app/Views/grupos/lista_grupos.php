<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?php echo view("partials/title-meta", array("title" => "Lista de Grupos")); ?>
    
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
                            <h4 class="page-title">Grupos</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Alumnos</a></li>
                                    <li class="breadcrumb-item active">Lista de Grupos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="row mb-4">
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label">Filtrar por grado:</label>
                                        <select class="form-control form-select" id="filtroGrado" name="filtroGrado">
                                            <option value="">Mostrar Todos</option>
                                            <?php foreach($lista_grados as $grado): ?>
                                                <option value="<?= $grado['id_grado'] ?>">
                                                    <?= $grado['nombreGrado'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div id="contenedorTabla">
                                    <?= $this->include('grupos/tabla_parcial') ?>
                                </div>

                            </div> </div> </div> </div> </div> <?= $this->include("partials/footer") ?>

        </div> </div> <?= $this->include("partials/vendor-scripts") ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const selectGrado = document.getElementById('filtroGrado');
            const contenedor = document.getElementById('contenedorTabla');

            // Detectar cambio en el Select
            selectGrado.addEventListener('change', function() {
                const gradoId = this.value;

                // Crear los datos a enviar
                const formData = new FormData();
                formData.append('id_grado', gradoId);

                // Petición AJAX al controlador
                // Asegúrate que la URL coincida con tu ruta en Routes.php
                fetch('<?= base_url("grupos/filtrar") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    contenedor.innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>

</body>
</html>