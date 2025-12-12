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

// Rutas para el Módulo de Boletas
// Ruta para la Lista de Alumnos por Grado
$routes->get('boleta/lista/(:num)', 'Boleta::lista_alumnos/$1');

// Ruta para ver la boleta individual
$routes->get('boleta/ver/(:num)/(:num)', 'Boleta::ver/$1/$2'); // Ahora recibe grado y alumno