<?php

namespace App\Core;

/**
 * Enrutador didáctico Front-Controller
 * Interpreta las URLs amigables o parámetros GET y despacha al controlador correspondiente.
 * 
 * Ejemplos:
 *   /                     => HomeController@index
 *   /clientes             => ClienteController@index
 *   /clientes/crear       => ClienteController@crear
 *   /clientes/guardar     => ClienteController@guardar
 *   /clientes/editar/1    => ClienteController@editar(1)
 *   /clientes/eliminar/1  => ClienteController@eliminar(1)
 */
class Router
{
    public function dispatch(): void
    {
        // 0. Todo POST debe traer el token CSRF de la sesión (formulario o cabecera AJAX)
        $esPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
        if ($esPost && !csrf_valido($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            $this->rechazarPeticion('La sesión del formulario expiró o la petición no es válida. Vuelve a intentarlo.');
            return;
        }

        // 0.1 Un envío repetido (doble clic, reintento tras un corte) no se registra dos veces
        if ($esPost) {
            Operacion::iniciar();
        }

        // 1. Obtener la ruta solicitada
        $url = $_GET['url'] ?? '';

        // Soporte alternativo por parámetros directos ?c=cliente&a=crear
        if (empty($url) && isset($_GET['c'])) {
            $url = $_GET['c'] . (isset($_GET['a']) ? '/' . $_GET['a'] : '');
        }

        // Si la URL arrastra parámetros query con '?' (ej: "recepcion/checkin?reserva_id=3")
        if (str_contains($url, '?')) {
            [$url, $queryString] = explode('?', $url, 2);
            parse_str($queryString, $extraGet);
            $_GET = array_merge($_GET, $extraGet);
            $_REQUEST = array_merge($_REQUEST, $extraGet);
        }

        $url = trim($url, '/');
        $partes = $url !== '' ? explode('/', $url) : [];

        // 2. Determinar el controlador y método (por defecto: HomeController@index)
        $nombreControlador = !empty($partes[0]) ? strtolower($partes[0]) : 'home';
        $metodo = !empty($partes[1]) ? $partes[1] : 'index';
        $parametros = array_slice($partes, 2);

        // Rutas directas / alias amigables
        $alias = [
            'checkin'        => ['RecepcionController', 'checkin'],
            'checkout'       => ['RecepcionController', 'checkout'],
            'disponibilidad' => ['ReservaController', 'disponibilidad'],
            'calendario'     => ['ReservaController', 'calendario'],
            'estadias'       => ['RecepcionController', 'estadias'],
        ];

        if (isset($alias[$nombreControlador])) {
            $nombreClase = $alias[$nombreControlador][0];
            $metodo = !empty($partes[1]) ? $this->formatearMetodo($partes[1]) : $alias[$nombreControlador][1];
            $controladorCompleto = "App\\Controllers\\{$nombreClase}";
        } else {
            $nombreClase = $this->formatearNombreControlador($nombreControlador);
            $controladorCompleto = "App\\Controllers\\{$nombreClase}";
            $metodo = $this->formatearMetodo($metodo);
        }

        // Si se envió un parámetro ?id=X por GET, también tenerlo en cuenta
        if (empty($parametros) && isset($_GET['id'])) {
            $parametros[] = $_GET['id'];
        }

        // 5. Verificar existencia del controlador
        if (!class_exists($controladorCompleto)) {
            // Los nombres salen de la URL: se escapan para que no se pueda inyectar HTML (XSS)
            $this->mostrarError404('El controlador <code>' . e($nombreClase) . '</code> no existe en <strong>app/Controllers/</strong>.');
            return;
        }

        $instanciaControlador = new $controladorCompleto();

        // 6. Verificar existencia del método (con fallback insensible a mayúsculas/minúsculas)
        if (!method_exists($instanciaControlador, $metodo)) {
            $metodoEncontrado = false;
            foreach (get_class_methods($instanciaControlador) as $m) {
                if (strcasecmp($m, $metodo) === 0) {
                    $metodo = $m;
                    $metodoEncontrado = true;
                    break;
                }
            }
            if (!$metodoEncontrado) {
                $this->mostrarError404('El método <code>' . e($metodo) . '()</code> no existe en el controlador <strong>' . e($nombreClase) . '</strong>.');
                return;
            }
        }

        // 7. Las acciones de borrado solo se aceptan por POST (con token): un simple
        //    enlace o <img src="..."> de otra página no puede eliminar registros.
        if (!$esPost && stripos($metodo, 'eliminar') === 0) {
            $this->rechazarPeticion('Esta acción solo puede ejecutarse desde el botón del sistema.');
            return;
        }

        // 8. Ejecutar el método pasando los parámetros
        call_user_func_array([$instanciaControlador, $metodo], $parametros);
    }

    /**
     * Responde a una petición rechazada por seguridad:
     * JSON 403 para AJAX, o mensaje flash y regreso a la página anterior.
     */
    private function rechazarPeticion(string $mensaje): void
    {
        $esAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || isset($_SERVER['HTTP_X_CSRF_TOKEN'])
            || !empty($_POST['ajax']);

        if ($esAjax) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            // sesion_vencida: el navegador (envios.js) lleva al login en lugar de mostrar el error
            echo json_encode(['success' => false, 'message' => $mensaje, 'mensaje' => $mensaje, 'sesion_vencida' => !auth_check()], JSON_UNESCAPED_UNICODE);
            return;
        }

        $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => $mensaje];

        // Volver a la página anterior solo si es de este mismo sitio
        $anterior = $_SERVER['HTTP_REFERER'] ?? '';
        $destino = str_starts_with($anterior, base_url()) ? $anterior : url();
        header('Location: ' . $destino);
    }

    /**
     * Convierte 'huespedes' en 'HuespedController', 'reservas' en 'ReservaController', etc.
     */
    private function formatearNombreControlador(string $nombre): string
    {
        // Normalizar guiones o barras bajas a CamelCase
        $nombreLimpio = str_replace(['-', '_'], ' ', $nombre);
        $nombreLimpio = ucwords($nombreLimpio);
        $nombreLimpio = str_replace(' ', '', $nombreLimpio);

        // Si ya termina en Controller
        if (str_ends_with($nombreLimpio, 'Controller') && class_exists("App\\Controllers\\{$nombreLimpio}")) {
            return $nombreLimpio;
        }

        $candidatos = [
            $nombreLimpio . 'Controller',
        ];

        // Plural en español terminando en 'es' (ej: huespedes -> Huesped, reportes -> Reporte)
        if (str_ends_with(strtolower($nombreLimpio), 'es')) {
            $candidatos[] = substr($nombreLimpio, 0, -2) . 'Controller';
            $candidatos[] = substr($nombreLimpio, 0, -1) . 'Controller';
        }

        // Plural en español terminando en 's' (ej: reservas -> Reserva, habitaciones -> Habitacion)
        if (str_ends_with(strtolower($nombreLimpio), 's')) {
            $candidatos[] = substr($nombreLimpio, 0, -1) . 'Controller';
        }

        foreach ($candidatos as $candidato) {
            if (class_exists("App\\Controllers\\{$candidato}")) {
                return $candidato;
            }
        }

        return $candidatos[0];
    }

    /**
     * Convierte 'guardar-cliente' a 'guardarCliente'
     */
    private function formatearMetodo(string $nombre): string
    {
        $partes = explode('-', $nombre);
        $metodo = array_shift($partes);
        foreach ($partes as $parte) {
            $metodo .= ucfirst($parte);
        }
        return lcfirst($metodo);
    }

    /**
     * Muestra una pantalla de error 404 clara y pedagógica para los alumnos
     */
    private function mostrarError404(string $mensaje): void
    {
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Error 404 - Ruta no encontrada</title>
            <link rel='stylesheet' href='" . asset('adminlte/css/adminlte.min.css') . "'>
        </head>
        <body class='bg-light d-flex align-items-center' style='min-height: 100vh;'>
            <div class='container text-center py-5'>
                <div class='card shadow-sm mx-auto' style='max-width: 600px;'>
                    <div class='card-body p-4'>
                        <h1 class='display-1 text-danger fw-bold'>404</h1>
                        <h4 class='mb-3'>Ruta o recurso no encontrado</h4>
                        <div class='alert alert-warning text-start mb-4'>
                            {$mensaje}
                        </div>
                        <p class='text-muted'>Verifica que el nombre del controlador y método coincidan con la URL solicitada.</p>
                        <a href='" . base_url() . "' class='btn btn-primary'>
                            <i class='bi bi-house'></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}
