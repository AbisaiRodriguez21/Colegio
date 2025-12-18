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

// Login
$routes->get('login', 'Auth::index');
$routes->post('auth/attemptLogin', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// Dashboard
$routes->get('/', 'Home::index');
$routes->get('dashboard', 'Dashboard::index');

// Test BD
$routes->get('testdb', 'TestDB::index');

// Profesores
$routes->get('lista-profesores', 'ProfesorLista::index');
$routes->get('profesor/ver/(:num)', 'ProfesorLista::ver/$1');
$routes->get('profesor/eliminar/(:num)', 'ProfesorLista::eliminar/$1');
// Asignar carga de materias
$routes->get('profesor/asignar/(:num)', 'ProfesorLista::asignar/$1');
// Ruta para procesar el formulario del switch
$routes->post('profesor/guardar_materia', 'ProfesorLista::guardar_materia');

// Alumnos
$routes->match(['get', 'post'], 'alumno', 'Alumno::index'); 
$routes->get('alumnos/registro', 'Alumnos::registro'); 
$routes->post('alumnos/guardar', 'Alumnos::guardar'); 
// Rutas de Alumnos (Preinscripciones) 
$routes->get('alumnos/preinscripciones', 'Alumnos::preinscripciones'); 
$routes->post('alumnos/guardar_preinscripcion', 'Alumnos::guardar_preinscripcion'); 

//Lista grupos
$routes->get('grupos/lista', 'Grupos::index');   
$routes->post('grupos/filtrar', 'Grupos::filtrar');

// Usuarios
$routes->get('crear-usuario', 'Dashboard::crearUsuario');

// Protegidas
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->get('dashboard/obtenerGrados/(:num)', 'Dashboard::obtenerGrados/$1');
});

//pagos
$routes->get('verificar-pagos', 'VerificarPagos::index'); 
$routes->post('verificar-pagos/validar', 'VerificarPagos::validar');
$routes->get('verificar-pagos/ajaxList', 'VerificarPagos::ajaxList');

// Rutas de Correo
$routes->get('correo', 'Correo::index');          // Dashboard Principal (Inbox)
$routes->get('correo/redactar', 'Correo::redactar'); // Formulario de Redacción
$routes->post('correo/enviar', 'Correo::enviar');    // Acción de enviar
$routes->get('correo/ver/(:num)', 'Correo::ver/$1'); // Ver detalle de un correo
$routes->get('correo/ajax_ver/(:num)', 'Correo::ajax_ver/$1'); // Ver detalle vía AJAX
$routes->post('correo/acciones', 'Correo::acciones_masivas'); // Acciones masivas

// =============================================================================
// Rutas para el Módulo de Boletas (VER E IMPRIMIR)
// =============================================================================

$routes->get('boleta/lista/(:num)', 'Boleta::lista_alumnos/$1'); // Ruta para la Lista de Alumnos por Grado
$routes->get('boleta/ver/(:num)/(:num)', 'Boleta::ver/$1/$2'); // Ruta para ver la boleta individual. recibe grado y alumno

// =============================================================================
// RUTAS PARA CALIFICAR BOLETA (PROFESORES)
// =============================================================================

// 1. La pantalla de la sábana (GET)
$routes->get('calificaciones/editar/(:num)', 'Calificaciones::editar/$1');

// 2. Ruta Alias 
$routes->get('boleta/calificar/(:num)', 'Calificaciones::editar/$1');

// 3. La ruta oculta para guardar los datos por AJAX (POST)
$routes->post('calificaciones/actualizar', 'Calificaciones::actualizar');

// =============================================================================
// RUTAS PARA CALIFICAR BOLETA BIMESTRE (TERCER MODULO/APARTADO)
// =============================================================================

// 1. URL para ver la lista
$routes->get('calificaciones_bimestre/lista/(:num)', 'CalificacionesBimestre::lista/$1');

// 2. URL para ver la boleta completa
$routes->get('calificaciones_bimestre/alumno/(:num)/(:num)', 'CalificacionesBimestre::alumno_completo/$1/$2');

// 3. URL interna para guardar (AJAX)
$routes->post('calificaciones_bimestre/actualizar', 'CalificacionesBimestre::actualizar');

// =============================================================================
// --- Rutas para el Módulo de Calificaciones Bimestrales ---
// =============================================================================

// 1. Ruta para ver/editar la boleta completa (Esto arregla el error 404 al navegar)
$routes->get('calificaciones_bimestre/alumno_completo/(:num)/(:num)', 'CalificacionesBimestre::alumno_completo/$1/$2');

// 2. Ruta para el guardado AJAX (Para que guarde los cambios)
$routes->post('calificaciones_bimestre/actualizar', 'CalificacionesBimestre::actualizar');