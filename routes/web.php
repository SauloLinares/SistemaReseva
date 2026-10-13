<?php

/**
 * Mapa de Rutas del Sistema
 * Este archivo sirve de referencia didáctica para los alumnos sobre las rutas disponibles
 * y cómo se mapean a los Controladores y Métodos (Patrón Front Controller).
 *
 * Estructura de URL:
 *   [url_base]/index.php?url={controlador}/{metodo}/{parametro}
 *   o con mod_rewrite: [url_base]/{controlador}/{metodo}/{parametro}
 *
 * Módulo Dashboard:
 *   GET  /                        => HomeController@index       (Panel principal)
 *   GET  /home                    => HomeController@index
 *
 * Módulo Clientes (CRUD Completo):
 *   GET  /clientes                => ClienteController@index    (Listar clientes)
 *   GET  /clientes/crear          => ClienteController@crear    (Formulario de registro)
 *   POST /clientes/guardar        => ClienteController@guardar  (Procesar nuevo cliente)
 *   GET  /clientes/editar/{id}    => ClienteController@editar   (Formulario de edición)
 *   POST /clientes/actualizar     => ClienteController@actualizar (Guardar cambios)
 *   GET  /clientes/eliminar/{id}  => ClienteController@eliminar (Eliminar/Cambiar estado)
 */

return [
    '/' => 'HomeController@index',
    'home' => 'HomeController@index',
    'clientes' => 'ClienteController@index',
    'clientes/crear' => 'ClienteController@crear',
    'clientes/guardar' => 'ClienteController@guardar',
    'clientes/editar' => 'ClienteController@editar',
    'clientes/actualizar' => 'ClienteController@actualizar',
    'clientes/eliminar' => 'ClienteController@eliminar',
];
