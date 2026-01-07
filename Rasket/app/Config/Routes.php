<?php

namespace Config;

$routes = Services::routes();

if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// =============================================================================
// RUTAS PÚBLICAS (ACCESO LIBRE)
// =============================================================================
$routes->get('login', 'Auth::index');
$routes->post('auth/attemptLogin', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');
$routes->get('/', 'Home::index');

// =============================================================================
// RUTAS GENERALES (Cualquier usuario logueado: Admin, Profe, Alumno)
// =============================================================================
$routes->group('', ['filter' => 'auth'], function ($routes) {
    
    // Dashboard General
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/obtenerGrados/(:num)', 'Dashboard::obtenerGrados/$1'); // AJAX compartido

    // Módulo de Correo (Todos lo usan)
    $routes->get('correo', 'Correo::index');          
    $routes->get('correo/redactar', 'Correo::redactar'); 
    $routes->post('correo/enviar', 'Correo::enviar');    
    $routes->get('correo/ver/(:num)', 'Correo::ver/$1'); 
    $routes->get('correo/ajax_ver/(:num)', 'Correo::ajax_ver/$1'); 
    $routes->post('correo/acciones', 'Correo::acciones_masivas');

    // Aquí van las rutas futuras exclusivas del alumno ("alumno/mis-calificaciones")
    $routes->get('alumno/boleta', 'AlumnoViewController::verBoleta');

    // --- RUTAS DE TITULAR DE GRUPO ---
    // No llevan ID en la URL, usan la sesión.
    $routes->get('titular/mi-grupo', 'TitularViewController::verGrupo');
    $routes->get('titular/calificar', 'TitularViewController::calificarGrupo');
    $routes->get('titular/ver-boleta/(:num)', 'TitularViewController::verBoletaAlumno/$1');

    // Calificaciones (Edición Celda por Celda AJAX)
    $routes->post('calificaciones/actualizar', 'Calificaciones::actualizar');

});


// =============================================================================
// ⛔ ZONA ADMINISTRATIVA Y DOCENTE (PROTEGIDA POR 'adminAuth')
// =============================================================================
// Aquí metemos TODO lo que un alumno NO DEBE VER.
// El filtro 'adminAuth' revisa que session('nivel') sea 1 o 2.

$routes->group('', ['filter' => 'adminAuth'], function ($routes) {

    // Test BD
    $routes->get('testdb', 'TestDB::index');

    // Profesores
    $routes->get('lista-profesores', 'ProfesorLista::index');
    $routes->get('profesor/ver/(:num)', 'ProfesorLista::ver/$1');
    $routes->get('profesor/eliminar/(:num)', 'ProfesorLista::eliminar/$1');
    $routes->get('profesor/asignar/(:num)', 'ProfesorLista::asignar/$1');
    $routes->post('profesor/guardar_materia', 'ProfesorLista::guardar_materia');
    $routes->post('profesor/guardar_carga_grado', 'ProfesorLista::guardar_carga_grado');

    // Gestión de Alumnos (Admin gestiona alumnos)
    $routes->match(['get', 'post'], 'alumno', 'Alumno::index'); 
    $routes->get('alumnos/registro', 'Alumnos::registro'); 
    $routes->post('alumnos/guardar', 'Alumnos::guardar'); 
    $routes->get('alumnos/preinscripciones', 'Alumnos::preinscripciones'); 
    // Nota: preinscripciones podría ser pública si es externa, pero si es interna va aquí.
    
    // Lista grupos
    $routes->get('grupos/lista', 'Grupos::index');   
    $routes->post('grupos/filtrar', 'Grupos::filtrar');

    // Usuarios
    $routes->get('crear-usuario', 'Dashboard::crearUsuario');

    // Verificar Pagos
    $routes->get('verificar-pagos', 'VerificarPagos::index');
    $routes->post('verificar-pagos/validar', 'VerificarPagos::validar');

    // Boletas (Admin y Profes)
    $routes->get('boleta/lista/(:num)', 'Boleta::lista_alumnos/$1');
    $routes->get('boleta/ver/(:num)/(:num)', 'Boleta::ver/$1/$2');
    $routes->get('boleta/calificar/(:num)', 'Calificaciones::editar/$1');
    $routes->get('calificaciones/editar/(:num)', 'Calificaciones::editar/$1');

    // Calificaciones Bimestrales
    $routes->get('calificaciones_bimestre/lista/(:num)', 'CalificacionesBimestre::lista/$1');
    $routes->get('calificaciones_bimestre/alumno/(:num)/(:num)', 'CalificacionesBimestre::alumno_completo/$1/$2');
    $routes->get('calificaciones_bimestre/alumno_completo/(:num)/(:num)', 'CalificacionesBimestre::alumno_completo/$1/$2');
    $routes->post('calificaciones_bimestre/actualizar', 'CalificacionesBimestre::actualizar');

    // Asignaciones Especiales
    $routes->get('asignar-area', 'AsignarArea::index'); 
    $routes->post('asignar-area/actualizar', 'AsignarArea::actualizar'); 
    $routes->get('asignar-titulares', 'AsignarTitulares::index'); 
    $routes->post('asignar-titulares/guardar', 'AsignarTitulares::guardar'); 

    // Registro Profesor y Grados
    $routes->get('registro-profesor', 'RegistroProfesor::nuevo');         
    $routes->post('registro-profesor/guardar', 'RegistroProfesor::guardar');
    $routes->get('registro-profesor/municipios/(:segment)', 'RegistroProfesor::getMunicipios/$1'); 
    $routes->get('registro-profesor/localidades/(:segment)', 'RegistroProfesor::getLocalidades/$1');
    
    $routes->get('registro/grados', 'RegistroProfesor::grados');              
    $routes->post('registro/grados/guardar', 'RegistroProfesor::guardarGrado'); 
    $routes->get('registro/grados/eliminar/(:num)', 'RegistroProfesor::eliminarGrado/$1'); 

    // Niveles
    $routes->get('niveles', 'Niveles::index');           
    $routes->get('niveles/fetch', 'Niveles::fetch');    
    // Ruta para cambiar contraseña rápida
    $routes->post('niveles/actualizar-pass', 'Niveles::actualizarPassword');

    // Cambio de Grado
    $routes->get('cambio-grado', 'CambioGradoController::index');
    $routes->post('cambio-grado/baja', 'CambioGradoController::darBaja');
    $routes->get('cambio-grado/get-datos', 'CambioGradoController::getDatosModal'); 
    $routes->post('cambio-grado/activar', 'CambioGradoController::activar');
});