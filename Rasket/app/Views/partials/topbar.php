<header class="topbar">
     <div class="container-fluid">
          <div class="navbar-header">
               <div class="d-flex align-items-center gap-2">
                    <div class="topbar-item">
                         <button type="button" class="button-toggle-menu topbar-button">
                              <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <form class="app-search d-none d-md-block me-auto">
                         <div class="position-relative">
                              <input type="search" class="form-control" placeholder="Search..." autocomplete="off" value="">
                              <iconify-icon icon="solar:magnifer-broken" class="search-widget-icon"></iconify-icon>
                         </div>
                    </form>
               </div>

               <div class="d-flex align-items-center gap-1">
                    <div class="topbar-item">
                         <button type="button" class="topbar-button" data-toggle="theme">
                              <iconify-icon icon="solar:moon-broken" class="fs-24 align-middle light-mode"></iconify-icon>
                              <iconify-icon icon="solar:sun-broken" class="fs-24 align-middle dark-mode"></iconify-icon>
                         </button>
                    </div>

                    <div class="dropdown topbar-item d-none d-lg-flex">
                         <button type="button" class="topbar-button" data-toggle="fullscreen">
                              <iconify-icon icon="solar:full-screen-broken" class="fs-24 align-middle fullscreen"></iconify-icon>
                              <iconify-icon icon="solar:quit-full-screen-broken" class="fs-24 align-middle quit-fullscreen"></iconify-icon>
                         </button>
                    </div>

                    <div class="dropdown topbar-item">
                         <button type="button" class="topbar-button position-relative" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <iconify-icon icon="solar:bell-bing-broken" class="fs-24 align-middle"></iconify-icon>
                              <span class="position-absolute topbar-badge fs-10 translate-middle badge bg-danger rounded-pill">3<span class="visually-hidden">unread messages</span></span>
                         </button>
                         <div class="dropdown-menu py-0 dropdown-lg dropdown-menu-end" aria-labelledby="page-header-notifications-dropdown">
                              <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                   <div class="row align-items-center">
                                        <div class="col">
                                             <h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
                                        </div>
                                        <div class="col-auto">
                                             <a href="javascript: void(0);" class="text-dark text-decoration-underline">
                                                  <small>Clear All</small>
                                             </a>
                                        </div>
                                   </div>
                              </div>
                              <div data-simplebar style="max-height: 280px;">
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom text-wrap">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <img src="/images/users/avatar-1.jpg" class="img-fluid me-2 avatar-sm rounded-circle" alt="avatar-1" />
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0"><span class="fw-medium">Josephine Thompson </span>commented on admin panel <span>" Wow 😍! this admin looks good and awesome design"</span></p>
                                             </div>
                                        </div>
                                   </a>
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <div class="avatar-sm me-2">
                                                       <span class="avatar-title bg-soft-info text-info fs-20 rounded-circle">
                                                            D
                                                       </span>
                                                  </div>
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 fw-semibold">Donoghue Susan</p>
                                                  <p class="mb-0 text-wrap">
                                                       Hi, How are you? What about our next meeting
                                                  </p>
                                             </div>
                                        </div>
                                   </a>
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <img src="/images/users/avatar-3.jpg" class="img-fluid me-2 avatar-sm rounded-circle" alt="avatar-3" />
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 fw-semibold">Jacob Gines</p>
                                                  <p class="mb-0 text-wrap">
                                                       Answered to your comment on the cash flow forecast's graph 🔔.
                                                  </p>
                                             </div>
                                        </div>
                                   </a>
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <div class="avatar-sm me-2">
                                                       <span class="avatar-title bg-soft-warning text-warning fs-20 rounded-circle">
                                                            <iconify-icon icon="solar:leaf-bold-duotone"></iconify-icon>
                                                       </span>
                                                  </div>
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 fw-semibold text-wrap">You have received <b>20</b> new messages in the
                                                       conversation</p>
                                             </div>
                                        </div>
                                   </a>
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <img src="/images/users/avatar-5.jpg" class="img-fluid me-2 avatar-sm rounded-circle" alt="avatar-5" />
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 fw-semibold">Shawn Bunch</p>
                                                  <p class="mb-0 text-wrap">
                                                       Commented on Admin
                                                  </p>
                                             </div>
                                        </div>
                                   </a>
                              </div>
                              <div class="text-center py-3">
                                   <a href="javascript:void(0);" class="btn btn-primary btn-sm">View All Notification <i class="bx bx-right-arrow-alt ms-1"></i></a>
                              </div>
                         </div>
                    </div>

                    <div class="topbar-item d-none d-md-flex">
                         <button type="button" class="topbar-button" id="theme-settings-btn" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
                              <iconify-icon icon="solar:settings-broken" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <?php
                    $foto = session('foto');
                    if ($foto) {
                    $fotoUsuario = base_url($foto);
                    } else {
                    $fotoUsuario = base_url('images/avatar_placeholder.jpg');
                    }
                    $nombreUsuario = session('nombre') ?? 'Usuario';
                    ?>

                    <div class="dropdown topbar-item">
                    <a type="button"
                         class="topbar-button d-flex align-items-center gap-2"
                         id="page-header-user-dropdown"
                         data-bs-toggle="dropdown"
                         aria-haspopup="true"
                         aria-expanded="false">

                         <img class="rounded-circle" width="32" height="32"
                              src="<?= esc($fotoUsuario) ?>" alt="avatar">

                         <span class="fw-semibold text-dark">
                              <?= esc($nombreUsuario) ?>
                         </span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end">
                         <h6 class="dropdown-header">Bienvenido <?= esc($nombreUsuario) ?>!</h6>

                         <a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                              <i class="bx bx-log-out fs-18 align-middle me-1"></i>
                              <span class="align-middle">Logout</span>
                         </a>
                    </div>
                    </div>

               </div>
          </div>
     </div>
</header>

<?= $this->include('partials/right-sidebar') ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Buscamos el botón por su CLASE, no por ID, porque así viene en tu HTML
    const toggleBtn = document.querySelector('.button-toggle-menu');
    
    if(toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const width = window.innerWidth;
            const html = document.documentElement;
            const body = document.body;
            
            if (width >= 992) {
                // ESCRITORIO: Cambiar entre menú grande y pequeño
                const currentSize = html.getAttribute('data-sidebar-size');
                if (currentSize === 'sm') {
                    html.setAttribute('data-sidebar-size', 'lg');
                } else {
                    html.setAttribute('data-sidebar-size', 'sm');
                }
            } else {
                // MÓVIL: Mostrar u ocultar el menú
                // 'sidebar-enable' es la clase estándar para mostrar el menú móvil en Rasket
                body.classList.toggle('sidebar-enable');
            }
        });
    }
});
</script>