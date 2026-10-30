<?php

/**
 * Front Controller (Punto de Entrada Principal)
 * Todas las peticiones web se canalizan a través de este archivo.
 */

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