<div>
    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas">
        <div class="d-flex align-items-center bg-primary p-3 offcanvas-header">
            <h5 class="text-white m-0">Configuración del Usuario</h5>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-0">
            <div data-simplebar class="h-100">
                <div class="p-4 settings-bar">

                    <!-- Cambiar foto de perfil -->
                    <div class="mb-4 text-center">
                        <h6 class="fw-semibold mb-3">Foto de perfil</h6>

                        <!-- Imagen actual -->
                        <?php
                        $foto = session('foto');
                        $fotoUsuario = $foto ? base_url($foto) : base_url('images/avatar_placeholder.jpg');
                        ?>
                        <img src="<?= esc($fotoUsuario) ?>" alt="Foto de perfil"
                             class="rounded-circle mb-3" width="90" height="90">

                        <!-- Input (aún no funcional) -->
                        <input type="file" class="form-control mb-2" accept="image/*">
                        <small class="text-muted d-block mb-3">Formatos: JPG o PNG (máx. 2 MB)</small>

                        <!-- Botón simulado -->
                        <button class="btn btn-primary w-100" disabled>
                            Guardar nueva foto
                        </button>
                    </div>

                    <hr>

                    <!-- Cambiar contraseña -->
                    <div class="mt-4">
                        <h6 class="fw-semibold mb-3">Cambiar contraseña</h6>

                        <div class="mb-3">
                            <label class="form-label">Contraseña actual</label>
                            <input type="password" class="form-control" placeholder="********" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" placeholder="********" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" placeholder="********" disabled>
                        </div>

                        <!-- Botón simulado -->
                        <button class="btn btn-primary w-100" disabled>
                            Actualizar contraseña
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div class="offcanvas-footer border-top p-3 text-center">
            <small class="text-muted">Opciones disponibles próximamente</small>
        </div>
    </div>
</div>
