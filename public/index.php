<?php

/**
 * Front Controller (Punto de Entrada Principal)
 * Todas las peticiones web se canalizan a través de este archivo.
 */

// 0. Cabeceras de seguridad. Todas las librerías se sirven desde el proyecto
//    (public/assets/vendor), así que solo se permite el mismo origen. 'unsafe-inline'
//    sigue siendo necesario mientras haya <script> y onclick dentro de las vistas.
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; "
     . "style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; "
     . "connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

// 1. Iniciar sesión para mensajes flash y autenticación
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Registrar el Autoloader PSR-4 para cargar automáticamente todas las clases de App\
require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::registrar();

// 3. Cargar configuración general y funciones de ayuda globales (helpers)
require_once __DIR__ . '/../app/Config/App.php';
require_once __DIR__ . '/../app/Core/Helpers.php';

// 4. Iniciar el Enrutador y despachar la petición
use App\Core\Router;

$router = new Router();
$router->dispatch();