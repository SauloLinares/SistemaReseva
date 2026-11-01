<?php

/**
 * Front Controller (Punto de Entrada Principal)
 * Todas las peticiones web se canalizan a través de este archivo.
 */

// 0. Cabeceras de seguridad. Todas las librerías se sirven desde el proyecto
//    (public/assets/vendor) y el JavaScript vive en archivos (public/assets/js), así que
//    los scripts solo pueden venir del mismo origen: un HTML inyectado no se ejecuta.
//    Los estilos inline siguen permitidos (style="" y los <style> que inyectan FullCalendar
//    y SweetAlert2).
header("Content-Security-Policy: default-src 'self'; script-src 'self'; "
     . "style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; "
     . "connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

// 1. Registrar el Autoloader PSR-4 para cargar automáticamente todas las clases de App\
require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::registrar();

// 2. Cargar configuración general y funciones de ayuda globales (helpers)
require_once __DIR__ . '/../app/Config/App.php';
require_once __DIR__ . '/../app/Core/Helpers.php';

// 3. Hora del hotel para date(), "hoy" y las fechas que se guardan (MySQL usa la misma: Database.php)
date_default_timezone_set(\App\Config\App::zonaHoraria());

// 3.1 Sesión (mensajes flash y autenticación). La cookie no se puede leer desde JavaScript
//      (HttpOnly), no viaja en envíos que salen de otros sitios (SameSite=Lax) y, con HTTPS,
//      solo viaja cifrada (Secure). En modo estricto PHP no acepta un id de sesión inventado.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    // El cierre por inactividad lo controla Acceso; PHP no debe borrar la sesión antes
    ini_set('session.gc_maxlifetime', (string) max(1440, \App\Config\App::sesionMinutos() * 60 + 300));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => ($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// 3.2 Un error inesperado (excepción sin atrapar) no deja una pantalla en blanco ni muestra
//     detalles internos: se registra en el log de PHP y el usuario ve un aviso claro.
set_exception_handler([\App\Core\Errores::class, 'excepcion']);

// 4. Iniciar el Enrutador y despachar la petición
use App\Core\Router;

$router = new Router();
$router->dispatch();