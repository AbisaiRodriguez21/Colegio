<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-topbar-color="light" data-menu-color="light">

<head>
     <?php echo view("partials/title-meta", array("title" =>  "Lista de Profesores")) ?>
     <?= $this->include("partials/head-css") ?>
</head>

<body>
     <div class="wrapper">
          <?= $this->include("partials/menu") ?>
          <div class="page-content">
               <div class="container-fluid">

                    <div class="row">
                         <div class="col-12">
                              <h4 class="page-title">Lista de Profesores</h4>
                              <p class="text-muted mb-4">Gestión de profesores.</p>
                         </div>
                    </div>
                         <div class="col-12">
                              <div class="card">
                                   <div class="card-body">
                                        <?php if (session()->getFlashdata('success')): ?>
                                        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
                                        <?php endif; ?>

                                        <?php if (session()->getFlashdata('error')): ?>
                                        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
                                        <?php endif; ?>

                                        <div class="table-responsive">
                                             <table class="table table-bordered table-striped js-dataTable-full" id="profesores-table">
                                                  <thead>
                                                       <tr>
                                                            <th>Nombre</th>
                                                            <th>Email</th>
                                                            <th>Estatus</th>
                                                            <th>Contraseña</th>
                                                            <th class="text-center">Acciones</th>
                                                       </tr>
                                                  </thead>
                                                  <tbody>
                                                       <?php foreach ($profesores as $p): ?>
                                                            <tr>
                                                                 <td><?= esc($p['Nombre']) ?> <?= esc($p['ap_Alumno']) ?> <?= esc($p['am_Alumno']) ?></td>
                                                                 <td><?= esc($p['email']) ?></td>
                                                                 <td><?= esc($p['nombre_nivel']) ?></td>
                                                                 <td><?= esc($p['pass']) ?></td>
                                                                 <td class="text-center">
                                                                      <div class="btn-group">
                                                                           <!-- Asignar -->
                                                                           <button class="btn btn-sm btn-outline-info" type="button"
                                                                                onclick="window.location='<?= base_url('profesor/asignar/'.$p['id']) ?>'"
                                                                                title="Asignar carga de materias">
                                                                                <i class="bx bx-list-ul"></i>
                                                                           </button>

                                                                           <!-- Eliminar Carga -->
                                                                           <button class="btn btn-sm btn-outline-warning" type="button"
                                                                                onclick="window.location='<?= base_url('profesor/reset/'.$p['id']) ?>'"
                                                                                title="Eliminar carga de materias">
                                                                                <i class="bx bx-pencil"></i>
                                                                           </button>

                                                                           <!-- Ver Carga -->
                                                                           <button class="btn btn-sm btn-outline-success" type="button"
                                                                                onclick="window.location='<?= base_url('profesor/ver/'.$p['id']) ?>'"
                                                                                title="Ver carga de materias">
                                                                                <i class="bx bx-show-alt"></i>
                                                                           </button>

                                                                           <!-- Eliminar Profesor -->
                                                                           <?php if (!$p['tiene_materias']): ?>
                                                                                <button class="btn btn-sm btn-outline-danger" type="button"
                                                                                     onclick="confirmarEliminarProfesor(<?= $p['id'] ?>)"
                                                                                     title="Eliminar profesor">
                                                                                     <i class="bx bx-trash"></i>
                                                                                </button>
                                                                           <?php endif; ?>
                                                                      </div>
                                                                 </td>
                                                            </tr>
                                                       <?php endforeach; ?>
                                                  </tbody>
                                             </table>
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

     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <script>
          $(document).ready(function() {
               if (typeof App !== 'undefined' && typeof App.initHelpers !== 'undefined') {
                    App.initHelpers(['datatables']);
               } else {
                    $('#profesores-table').DataTable(); 
               }
          });
          
          function confirmarEliminarProfesor(id) {
               Swal.fire({
                    title: "¿Eliminar profesor?",
                    text: "Esta acción eliminará definitivamente al profesor de la base de datos.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar"
               }).then((result) => {
                    if (result.isConfirmed) {
                         window.location = "<?= base_url('profesor/eliminar/') ?>" + id;
                    }
               });
          }
     </script>
</body>
</html>