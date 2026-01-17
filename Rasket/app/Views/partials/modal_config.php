<div class="modal fade" id="modalConfigGlobal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalTitleConfig">Configuración</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <div id="loaderConfig" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando opciones...</p>
                </div>

                <form id="formGlobalConfig" style="display:none;">
                    <input type="hidden" id="conf_id_config" name="id_config">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mes Activo</label>
                        <select class="form-select" id="conf_id_mes" name="id_mes"></select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ciclo Escolar</label>
                        <select class="form-select" id="conf_id_ciclo" name="id_ciclo"></select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    // Función para abrir el modal desde el Sidebar
    function abrirConfiguracion(idConfig, titulo) {
        // 1. UI Inicial
        const modalEl = document.getElementById('modalConfigGlobal');
        const modal = new bootstrap.Modal(modalEl);
        document.getElementById('modalTitleConfig').innerText = titulo;
        
        // Reset UI
        document.getElementById('loaderConfig').style.display = 'block';
        document.getElementById('formGlobalConfig').style.display = 'none';
        document.getElementById('conf_id_config').value = idConfig;

        modal.show();

        // 2. Construir URL dinámica (Asegura que base_url no tenga doble slash)
        // OJO: Ajusta si tu proyecto está en una subcarpeta
        const url = '<?= base_url('globalconfig/getDatos/') ?>' + idConfig;
        
        console.log("Pidiendo datos a:", url); // Para que lo veas en F12 -> Console

        // 3. Petición AJAX
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error HTTP: " + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("Datos recibidos:", data); // Ver qué llegó

                if(data.error) { 
                    alert("Error del servidor: " + data.error); 
                    return; 
                }

                const selMes = document.getElementById('conf_id_mes');
                const selCiclo = document.getElementById('conf_id_ciclo');

                // Llenar Select Meses
                selMes.innerHTML = '';
                if(data.meses){
                    data.meses.forEach(m => {
                        // OJO: Verifica si en tu BD es 'id' o 'Id_mes'
                        // El modelo que te di usa 'id' como alias.
                        let selected = (data.actual && m.id == data.actual.id_mes) ? 'selected' : '';
                        selMes.innerHTML += `<option value="${m.id}" ${selected}>${m.nombre}</option>`;
                    });
                }

                // Llenar Select Ciclos
                selCiclo.innerHTML = '';
                if(data.ciclos){
                    data.ciclos.forEach(c => {
                        let selected = (data.actual && c.id == data.actual.id_ciclo) ? 'selected' : '';
                        selCiclo.innerHTML += `<option value="${c.id}" ${selected}>${c.nombre}</option>`;
                    });
                }

                // Mostrar Formulario y ocultar loader
                document.getElementById('loaderConfig').style.display = 'none';
                document.getElementById('formGlobalConfig').style.display = 'block';
            })
            .catch(err => {
                console.error("Fallo AJAX:", err);
                document.getElementById('loaderConfig').innerHTML = 
                    `<div class="text-danger"><i class="bx bx-error"></i> Error al cargar.<br><small>${err.message}</small></div>`;
            });
    }


    // 3. Guardar
    document.getElementById('formGlobalConfig').addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);

        fetch('<?= base_url('globalconfig/update') ?>', {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success'){
                location.reload(); // Recargar para ver los cambios en el sidebar
            } else {
                alert('Error: ' + data.msg);
            }
        });
    });
</script>