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
        // 1. Obtener la ruta solicitada
        $url = $_GET['url'] ?? '';

        // Soporte alternativo por parámetros directos ?c=cliente&a=crear
        if (empty($url) && isset($_GET['c'])) {
            $url = $_GET['c'] . (isset($_GET['a']) ? '/' . $_GET['a'] : '');
        }

        $url = trim($url, '/');
        $partes = $url !== '' ? explode('/', $url) : [];

        // 2. Determinar el controlador (por defecto: HomeController)
        $nombreControlador = !empty($partes[0]) ? $partes[0] : 'home';
        
        // Mapear nombres comunes a nombres de clase (ej: "clientes" -> "ClienteController")
        $nombreClase = $this->formatearNombreControlador($nombreControlador);
        $controladorCompleto = "App\\Controllers\\{$nombreClase}";

        // 3. Determinar el método/acción (por defecto: index)
        $metodo = !empty($partes[1]) ? $this->formatearMetodo($partes[1]) : 'index';

        // 4. Parámetros restantes
        $parametros = array_slice($partes, 2);

        // Si se envió un parámetro ?id=X por GET, también tenerlo en cuenta
        if (empty($parametros) && isset($_GET['id'])) {
            $parametros[] = $_GET['id'];
        }

        // 5. Verificar existencia del controlador
        if (!class_exists($controladorCompleto)) {
            $this->mostrarError404("El controlador <code>{$nombreClase}</code> no existe en <strong>app/Controllers/</strong>.");
            return;
        }

        $instanciaControlador = new $controladorCompleto();

        // 6. Verificar existencia del método
        if (!method_exists($instanciaControlador, $metodo)) {
            $this->mostrarError404("El método <code>{$metodo}()</code> no existe en el controlador <strong>{$nombreClase}</strong>.");
            return;
        }

        // 7. Ejecutar el método pasando los parámetros
        call_user_func_array([$instanciaControlador, $metodo], $parametros);
    }

    /**
     * Convierte 'clientes' en 'ClienteController' o 'home' en 'HomeController'
     */
    private function formatearNombreControlador(string $nombre): string
    {
        // Normalizar guiones o barras bajas a CamelCase
        $nombre = str_replace(['-', '_'], ' ', $nombre);
        $nombre = ucwords($nombre);
        $nombre = str_replace(' ', '', $nombre);

        // Si termina en 's' y no termina en 'Controller', convertir plural simple a singular común
        if (str_ends_with($nombre, 's') && !str_ends_with($nombre, 'Controller')) {
            $singular = substr($nombre, 0, -1);
            $candidato = $singular . 'Controller';
            if (class_exists("App\\Controllers\\{$candidato}")) {
                return $candidato;
            }
        }

        if (!str_ends_with($nombre, 'Controller')) {
            $nombre .= 'Controller';
        }

        return $nombre;
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
            <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css'>
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
