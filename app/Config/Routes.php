<?php

if (defined('ROUTES_ALREADY_INCLUDED')) {
    return;
}
define('ROUTES_ALREADY_INCLUDED', true);

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Página de inicio
$routes->get('/', 'Home::index');

// LOGIN
$routes->match(['get', 'post'], 'auth/login', 'Auth::login');
$routes->get('auth/logout', 'Auth::logout');

/* =========================================================
   ADMIN (solo rol 1)
   ========================================================= */
$routes->group('admin', ['filter' => 'auth'], static function ($routes) {
    $routes->group('', ['filter' => 'role:1'], static function ($routes) {
        // Usuarios
        $routes->get('usuarios', 'Admin::usuarios');
        $routes->get('usuarios/crear', 'Admin::crearUsuario');
        $routes->post('usuarios/guardar', 'Admin::guardarUsuario');
        $routes->get('usuarios/editar/(:num)', 'Admin::editarUsuario/$1');
        $routes->post('usuarios/actualizar/(:num)', 'Admin::actualizarUsuario/$1');
        $routes->get('usuarios/eliminar/(:num)', 'Admin::eliminar/$1');
        $routes->get('usuarios/eliminados', 'Admin::usuariosEliminados');
        $routes->post('usuarios/reactivar/(:num)', 'Admin::reactivar/$1');

        // Otras secciones
        $routes->get('dashboard', 'Admin::dashboard');
        $routes->get('reportes', 'Admin::reportes');
        $routes->get('configuracion', 'Admin::configuracion');
    });
});

/* =========================================================
   VETERINARIO (roles 1,2)
   ========================================================= */
$routes->group('veterinario', ['filter' => 'auth'], static function ($routes) {
    $routes->group('', ['filter' => 'role:1,2'], static function ($routes) {
        $routes->get('/',      'Veterinario::inicio', ['as' => 'vet.inicio']);
        $routes->get('inicio', 'Veterinario::inicio');

        // Citas
        $routes->get('citas',  'Veterinario::ver_citas', ['as' => 'vet.citas']);
        $routes->post('citas/estado/(:num)/en-consulta', 'Veterinario::iniciar_consulta/$1',  ['as' => 'vet.citas.estado.iniciar']);
        $routes->post('citas/estado/(:num)/completada',  'Veterinario::completar_consulta/$1', ['as' => 'vet.citas.estado.completar']);
        $routes->post('citas/estado/(:num)/no-show',     'Veterinario::marcar_no_show/$1',    ['as' => 'vet.citas.estado.no_show']);
        $routes->post('citas/estado/(:num)/(:alpha)',    'Veterinario::cambiar_estado/$1/$2', ['as' => 'vet.citas.estado']);
        

        // Tratamientos
        $routes->get('tratamientos/(:num)',          'Veterinario::ver_tratamientos/$1', ['as' => 'vet.tratamientos']);
        $routes->get('tratamientos/crear/(:num)',    'Veterinario::crear_tratamiento/$1', ['as' => 'vet.tratamientos.crear']);
        $routes->post('tratamientos/guardar/(:num)', 'Veterinario::guardar_tratamiento/$1', ['as' => 'vet.tratamientos.guardar']);

        // Pacientes
        $routes->get('pacientes', 'Veterinario::ver_pacientes', ['as' => 'vet.pacientes']);
    });
});

/* =========================================================
   RECEPCIÓN (roles 1,3)
   ========================================================= */
$routes->group('recepcion', ['filter' => 'auth'], static function ($routes) {
    $routes->group('', ['filter' => 'role:1,3'], static function ($routes) {
        $routes->get('inicio', 'Recepcion::inicio');

        // Citas
        $routes->get('citas', 'Recepcion::citas');
        $routes->get('citas/crear', 'Recepcion::agendar_cita');
        $routes->post('citas/guardar', 'Recepcion::guardar_cita');
        $routes->get('citas/editar/(:num)', 'Recepcion::editar/$1');
        $routes->post('citas/actualizar/(:num)', 'Recepcion::actualizar/$1');
        $routes->get('citas/cancelar/(:num)', 'Recepcion::cancelar_cita/$1');

        // Usuarios básicos desde recepción
        $routes->get('usuarios/crear', 'Recepcion::crearUsuario');
        $routes->post('usuarios/guardar', 'Recepcion::guardarUsuario');

        // Clientes
        $routes->get('clientes', 'Recepcion::listarClientes');
        $routes->get('clientes/desactivar/(:num)', 'Recepcion::desactivarCliente/$1');
        $routes->get('clientes/activar/(:num)', 'Recepcion::activarCliente/$1');

        // Mascotas
        $routes->get('mascotas/crear', 'Recepcion::crear');
        $routes->post('mascotas/guardar', 'Recepcion::guardar');
        $routes->get('mascotas', 'Recepcion::listarMascotas');

        // Facturas
        $routes->get('facturas', 'Recepcion::listarFacturas');
        $routes->get('facturas/crear', 'Recepcion::crearFactura');
        $routes->post('facturas/guardar', 'Recepcion::guardarFactura');
        $routes->get('facturas/detalle/(:num)', 'Recepcion::detalleFactura/$1');
        $routes->get('facturas/descargar_pdf/(:num)', 'Recepcion::descargar_pdf/$1');
    });
});

/* =========================================================
   CLIENTE (roles 1,4)
   ========================================================= */
$routes->group('cliente', ['filter' => 'auth'], static function ($routes) {
    $routes->group('', ['filter' => 'role:1,4'], static function ($routes) {
        $routes->get('/', 'Cliente::inicio');
        $routes->get('inicio', 'Cliente::inicio');

        // Mascotas
        $routes->get('mascotas', 'Cliente::mascotas');

        // Citas
        $routes->get('citas', 'Cliente::citas');
        $routes->get('solicitar-cita', 'Cliente::solicitarCita'); 
        $routes->post('citas/guardar', 'Cliente::guardarSolicitudCita');

        // Facturas
        $routes->get('facturas', 'Cliente::facturas');
        $routes->get('facturas/(:num)', 'Cliente::detalleFactura/$1');
        $routes->get('facturas/detalle/(:num)', 'Cliente::detalleFactura/$1');
        $routes->get('facturas/descargar_pdf/(:num)', 'Cliente::descargar_pdf/$1');

        // Perfil
        $routes->get('perfil', 'Cliente::perfil');
        $routes->post('perfil/actualizar', 'Cliente::actualizarPerfil');
    });
});
