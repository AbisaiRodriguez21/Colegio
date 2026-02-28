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
    $routes->get('dashboard/obtenerGrados/(:num)', 'Dashboard::obtenerGrados/$1');

    // Módulo de Correo
    $routes->get('correo', 'Correo::index');          
    $routes->get('correo/redactar', 'Correo::redactar'); 
    $routes->post('correo/enviar', 'Correo::enviar');    
    $routes->get('correo/ver/(:num)', 'Correo::ver/$1'); 
    $routes->get('correo/ajax_ver/(:num)', 'Correo::ajax_ver/$1'); 
    $routes->post('correo/acciones', 'Correo::acciones_masivas');

    // Rutas del Alumno
    $routes->get('alumno/boleta', 'AlumnoViewController::verBoleta');

    // ⚠️ NOTA: Dejamos 'actualizar' aquí porque si mueves esto al grupo de Titular,
    // los maestros de materia (que no son titulares) no podrán guardar calificaciones.
    // A menos que SOLO los titulares califiquen en tu escuela.
    $routes->post('calificaciones/actualizar', 'Calificaciones::actualizar');
});

// =============================================================================
// Rutas BLINDADAS para el Maestro Titular (nivel 9 + grupo asignado)
// =============================================================================
$routes->group('titular', ['filter' => 'titularAuth'], function($routes) {
    
    // Dashboard del Titular
    $routes->get('dashboard', 'Titular\DashboardTitular::index');

    // Ruta para actualizar contraseña vía AJAX 
    $routes->post('actualizar-password', 'Alumno\Dashboard::actualizarPassword');

    // Rutas para gestión de grupo y calificaciones 
    $routes->get('calificar', 'Titular\SabanaController::index'); 
    
    // Ruta para cargar la sábana de calificaciones
    $routes->post('abrir-sabana', 'Titular\SabanaController::cargarSabana');
 
    // Rutas para ver grupo y boletas
    $routes->get('mi-grupo', 'TitularViewController::verGrupo'); 

    // Ruta para ver la boleta de un alumno específico
    $routes->get('ver-boleta/(:num)', 'TitularViewController::verBoletaAlumno/$1'); 

    $routes->get('hoja-evaluacion', 'TitularViewController::calificarGrupo');

    // Descargar Plantilla de Calificaciones
    $routes->get('calificaciones/exportarPlantilla/(:num)', 'Calificaciones::exportarPlantilla/$1');
    // Subir Plantilla de Calificaciones
    $routes->post('calificaciones/importar', 'Calificaciones::importar');

});


// =============================================================================
// 1. RUTAS DE ALUMNOS (Protegidas por 'studentAuth')
// =============================================================================
// Nadie que no sea nivel 7 puede entrar aquí.
$routes->group('alumno', ['filter' => 'studentAuth'], function($routes) {
    
    // Al entrar a /alumno/dashboard, carga el controlador nuevo
    $routes->get('dashboard', 'Alumno\Dashboard::index');
    
    // Futura ruta de boletas
    $routes->get('boleta', 'Alumno\Boleta::index'); 

    $routes->post('actualizar-password', 'Alumno\Dashboard::actualizarPassword'); // Actualizar contraseña vía AJAX

    $routes->get('contenido', 'Alumno\Contenido::index'); // Contenido de Materias

    $routes->get('pagos', 'Alumno\Pagos::index');
    $routes->post('pagos/guardar', 'Alumno\Pagos::guardar');
    $routes->get('pagos/recibo/(:num)', 'Alumno\Pagos::verRecibo/$1');

    $routes->get('ficha', 'AlumnoViewController::ficha'); // Vista para que el alumno vea su ficha 
    $routes->post('actualizar-ficha', 'AlumnoViewController::actualizarFicha'); // Acción para actualizar ficha
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
    // Descargar Plantilla de Calificaciones
    $routes->get('calificaciones/exportarPlantilla/(:num)', 'Calificaciones::exportarPlantilla/$1');
    // Subir Plantilla de Calificaciones
    $routes->post('calificaciones/importar', 'Calificaciones::importar');

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

    // Configuración Global (del Mes y Ciclo Activo)
    $routes->get('globalconfig/getDatos/(:num)', 'GlobalConfig::getDatos/$1');
$routes->post('globalconfig/update', 'GlobalConfig::update');
});