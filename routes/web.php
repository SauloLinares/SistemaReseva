<?php

/**
 * Mapa de Rutas del Sistema - Hotel Manager
 *
 * Mapeo de rutas a Controladores y Métodos (Patrón Front Controller).
 * Estructura de URL:
 *   [url_base]/index.php?url={controlador}/{metodo}/{parametro}
 *   o con mod_rewrite: [url_base]/{controlador}/{metodo}/{parametro}
 */

return [
    // 1. Dashboard / Panel de control
    '/'                              => 'HomeController@index',
    'home'                           => 'HomeController@index',

    // Autenticación (Login / Logout)
    'login'                          => 'LoginController@index',
    'login/autenticar'               => 'LoginController@autenticar',
    'login/logout'                   => 'LoginController@logout',
    'auth/login'                     => 'AuthController@login',
    'auth/logout'                    => 'AuthController@logout',

    // 2. Módulo Reservas
    'reservas'                       => 'ReservaController@index',
    'reservas/crear'                 => 'ReservaController@crear',
    'reservas/guardar'               => 'ReservaController@guardar',
    'reservas/editar'                => 'ReservaController@editar',
    'reservas/actualizar'            => 'ReservaController@actualizar',
    'reservas/cancelar'              => 'ReservaController@cancelar',
    'reservas/disponibilidad'        => 'ReservaController@disponibilidad',
    'reservas/calendario'            => 'ReservaController@calendario',
    'reservas/eventosJson'           => 'ReservaController@eventosJson',
    'disponibilidad'                 => 'ReservaController@disponibilidad',
    'calendario'                     => 'ReservaController@calendario',

    // 3. Módulo Huéspedes
    'huespedes'                      => 'HuespedController@index',
    'huespedes/crear'                => 'HuespedController@crear',
    'huespedes/guardar'              => 'HuespedController@guardar',
    'huespedes/editar'               => 'HuespedController@editar',
    'huespedes/actualizar'           => 'HuespedController@actualizar',
    'huespedes/eliminar'             => 'HuespedController@eliminar',
    'huespedes/buscar'               => 'HuespedController@buscar',
    'huespedes/historial'            => 'HuespedController@historial',

    // 4. Módulo Habitaciones
    'habitaciones'                   => 'HabitacionController@index',
    'habitaciones/crear'             => 'HabitacionController@crear',
    'habitaciones/guardar'           => 'HabitacionController@guardar',
    'habitaciones/editar'            => 'HabitacionController@editar',
    'habitaciones/actualizar'        => 'HabitacionController@actualizar',
    'habitaciones/eliminar'          => 'HabitacionController@eliminar',
    'habitaciones/tipos'             => 'HabitacionController@tipos',
    'habitaciones/guardarTipo'       => 'HabitacionController@guardarTipo',
    'habitaciones/eliminarTipo'      => 'HabitacionController@eliminarTipo',
    'habitaciones/tarifas'           => 'HabitacionController@tarifas',
    'habitaciones/guardarTarifa'     => 'HabitacionController@guardarTarifa',
    'habitaciones/estados'           => 'HabitacionController@estados',
    'habitaciones/cambiarEstado'     => 'HabitacionController@cambiarEstado',
    'habitaciones/mantenimiento'     => 'HabitacionController@mantenimiento',
    'habitaciones/guardarMantenimiento' => 'HabitacionController@guardarMantenimiento',
    'habitaciones/finalizarMantenimiento' => 'HabitacionController@finalizarMantenimiento',

    // 5. Módulo Recepción
    'recepcion'                      => 'RecepcionController@index',
    'recepcion/checkin'              => 'RecepcionController@checkin',
    'recepcion/procesarCheckin'      => 'RecepcionController@procesarCheckin',
    'recepcion/checkout'             => 'RecepcionController@checkout',
    'recepcion/procesarCheckout'     => 'RecepcionController@procesarCheckout',
    'recepcion/asignacion'           => 'RecepcionController@asignacion',
    'recepcion/procesarCambio'       => 'RecepcionController@procesarCambio',
    'recepcion/estadias'             => 'RecepcionController@estadias',
    'checkin'                        => 'RecepcionController@checkin',
    'checkout'                       => 'RecepcionController@checkout',

    // 6. Módulo Pagos y Facturación
    'pagos'                          => 'PagoController@index',
    'pagos/crear'                    => 'PagoController@crear',
    'pagos/guardar'                  => 'PagoController@guardar',
    'pagos/anticipos'                => 'PagoController@anticipos',
    'pagos/pendientes'               => 'PagoController@pendientes',
    'pagos/comprobantes'             => 'PagoController@comprobantes',
    'pagos/comprobante'              => 'PagoController@comprobante',
    'pagos/devolucion'               => 'PagoController@devolucion',

    // 7. Módulo Servicios Adicionales
    'servicios'                      => 'ServicioController@index',
    'servicios/guardar'              => 'ServicioController@guardar',
    'servicios/eliminar'             => 'ServicioController@eliminar',
    'servicios/consumo'              => 'ServicioController@consumo',
    'servicios/guardarConsumo'       => 'ServicioController@guardarConsumo',
    'servicios/porHuesped'           => 'ServicioController@porHuesped',

    // 8. Módulo Reportes
    'reportes'                       => 'ReporteController@index',
    'reportes/reservas'              => 'ReporteController@reservas',
    'reportes/ocupacion'             => 'ReporteController@ocupacion',
    'reportes/ingresos'              => 'ReporteController@ingresos',
    'reportes/huespedes'             => 'ReporteController@huespedes',
    'reportes/cancelaciones'         => 'ReporteController@cancelaciones',

    // 9. Módulo Seguridad
    'seguridad'                      => 'SeguridadController@index',
    'seguridad/usuarios'             => 'SeguridadController@usuarios',
    'seguridad/guardarUsuario'       => 'SeguridadController@guardarUsuario',
    'seguridad/eliminarUsuario'      => 'SeguridadController@eliminarUsuario',
    'seguridad/roles'                => 'SeguridadController@roles',
    'seguridad/guardarRol'           => 'SeguridadController@guardarRol',
    'seguridad/permisos'             => 'SeguridadController@permisos',
    'seguridad/guardarPermisos'      => 'SeguridadController@guardarPermisos',
    'seguridad/auditoria'            => 'SeguridadController@auditoria',

    // 10. Módulo Configuración
    'configuracion'                  => 'ConfiguracionController@index',
    'configuracion/hotel'            => 'ConfiguracionController@hotel',
    'configuracion/guardarHotel'     => 'ConfiguracionController@guardarHotel',
    'configuracion/documentos'       => 'ConfiguracionController@documentos',
    'configuracion/metodosPago'      => 'ConfiguracionController@metodosPago',
    'configuracion/parametros'       => 'ConfiguracionController@parametros',
    'configuracion/guardarParametros'=> 'ConfiguracionController@guardarParametros',

    // Compatibilidad módulos educativos anteriores
    'clientes'                       => 'ClienteController@index',
    'clientes/crear'                 => 'ClienteController@crear',
    'clientes/guardar'               => 'ClienteController@guardar',
    'clientes/editar'                => 'ClienteController@editar',
    'clientes/actualizar'            => 'ClienteController@actualizar',
    'clientes/eliminar'              => 'ClienteController@eliminar',
    'productos'                      => 'ProductoController@index',
];
