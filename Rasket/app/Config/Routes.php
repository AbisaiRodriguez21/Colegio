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