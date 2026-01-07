<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
    <?= view("partials/title-meta", ["title" => "Gestión de Usuarios"]) ?>
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
                            <h4 class="page-title">Gestión de Usuarios (Niveles)</h4>
                            <p class="text-muted mb-4">Administración de usuarios y niveles de acceso.</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                
                                <div class="row mb-4">
                                    <div class="col-md-6"></div> <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-search-alt"></i></span>
                                            <input type="text" 
                                                   id="input-busqueda" 
                                                   class="form-control" 
                                                   placeholder="Buscar por nombre, email o nivel..." 
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                </div>

                                <div id="loading" class="text-center py-5" style="display:none;">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </div>

                                <div id="contenedorTabla"></div>

                            </div>
                        </div>
                    </div>
                </div>

            </div> <?= $this->include("partials/footer") ?>
        </div> </div> <?= $this->include("partials/vendor-scripts") ?>

    <script>
        // Variables de estado
        let ordenDireccion = 'ASC';
        let ordenColumna = 'nombre'; // Columna inicial
        let paginaActual = 1;
        let busquedaActual = '';
        let timeoutBusqueda = null;

        document.addEventListener("DOMContentLoaded", function() {
            cargarDatos(1);

            // 1. Buscador con Debounce
            const inputBusqueda = document.getElementById('input-busqueda');
            inputBusqueda.addEventListener('input', function(e) {
                clearTimeout(timeoutBusqueda);
                busquedaActual = e.target.value.trim();
                timeoutBusqueda = setTimeout(() => {
                    cargarDatos(1);
                }, 300);
            });

            // 2. Clic en Paginación (Delegación)
            document.getElementById('contenedorTabla').addEventListener('click', function(e) {
                let link = e.target.closest('.page-link');
                if (link) {
                    e.preventDefault();
                    let href = link.getAttribute('href');
                    if (href && href !== '#') {
                        let urlParams = new URLSearchParams(href.split('?')[1]);
                        let page = urlParams.get('page') || 1;
                        cargarDatos(page);
                    }
                }
            });
        });

        // Función Principal AJAX
        function cargarDatos(page) {
            paginaActual = page;
            const contenedor = document.getElementById('contenedorTabla');
            
            contenedor.style.opacity = '0.5';

            let url = `<?= base_url('niveles/fetch') ?>?page=${page}&columna=${ordenColumna}&orden=${ordenDireccion}&q=${encodeURIComponent(busquedaActual)}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    contenedor.innerHTML = data.html;
                    contenedor.style.opacity = '1';
                })
                .catch(err => {
                    console.error(err);
                    contenedor.style.opacity = '1';
                });
        }

        // Función para cambiar orden (se llama desde los th onclick)
        function cambiarOrden(columna) {
            if (ordenColumna === columna) {
                // Si es la misma columna, invertimos orden
                ordenDireccion = (ordenDireccion === 'ASC') ? 'DESC' : 'ASC';
            } else {
                // Si es nueva columna, reseteamos a ASC
                ordenColumna = columna;
                ordenDireccion = 'ASC';
            }
            cargarDatos(paginaActual);
        }

        function guardarPass(input, idUsuario) {
            const nuevaPass = input.value.trim();
            const original = input.dataset.original;

            // Si no hubo cambios, no hacemos nada
            if (nuevaPass === original) return;

            if (nuevaPass === '') {
                alert("La contraseña no puede estar vacía");
                input.value = original;
                return;
            }

            // UI: Indicador de carga (Amarillo)
            input.style.backgroundColor = "#fff3cd"; 
            input.disabled = true; // Bloquear mientras guarda

            const formData = new FormData();
            formData.append('id', idUsuario);
            formData.append('pass', nuevaPass);

            fetch('<?= base_url('niveles/actualizar-pass') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                input.disabled = false;
                if(data.status === 'success') {
                    // ÉXITO: Verde
                    input.style.backgroundColor = "#e5faeaff";
                    input.style.borderColor = "#28a745";
                    input.dataset.original = nuevaPass; // Actualizamos referencia
                    
                    // Regresar a la normalidad en 1.5 seg
                    setTimeout(() => {
                        input.style.backgroundColor = "";
                        input.style.borderColor = "#ffc107"; // Regresa al borde amarillo original (warning)
                    }, 1500);
                } else {
                    // ERROR: Rojo y revertir
                    alert("Error: " + data.msg);
                    input.style.backgroundColor = "#f8d7da";
                    input.value = original;
                }
            })
            .catch(err => {
                console.error(err);
                input.disabled = false;
                input.style.backgroundColor = "#f8d7da";
                alert("Error de conexión");
            });
        }
    </script>
</body>
</html>