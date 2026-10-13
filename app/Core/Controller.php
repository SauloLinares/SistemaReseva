<?php

namespace App\Core;

/**
 * Clase base Controller
 * Proporciona métodos para renderizar vistas con la plantilla AdminLTE,
 * gestionar respuestas JSON y mensajes flash de alerta.
 */
abstract class Controller
{
    /**
     * Renderiza una vista dentro del layout principal de AdminLTE
     *
     * @param string $vista Ruta de la vista relativa a app/Views (ej: 'clientes/index' o 'home/index')
     * @param array $datos Datos que se pasarán a la vista como variables
     * @param string $titulo Título de la página que se mostrará en el navegador y en el encabezado
     */
    protected function render(string $vista, array $datos = [], string $titulo = 'Inicio'): void
    {
        // Convertir el array asociativo en variables individuales para la vista
        extract($datos);

        $pageTitle = $titulo;
        $viewPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $vista) . '.php';

        if (!file_exists($viewPath)) {
            die("La vista <code>{$vista}</code> no existe en: <strong>{$viewPath}</strong>");
        }

        // Estructura modular de AdminLTE
        $layoutsPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR;

        require_once $layoutsPath . 'header.php';
        require_once $layoutsPath . 'navbar.php';
        require_once $layoutsPath . 'sidebar.php';

        echo "<!--begin::App Main-->\n<main class=\"app-main\">\n";
        require_once $viewPath;
        echo "\n</main>\n<!--end::App Main-->\n";

        require_once $layoutsPath . 'footer.php';
    }

    /**
     * Devuelve una respuesta en formato JSON (útil para peticiones AJAX o APIs)
     */
    protected function json(mixed $datos, int $codigoEstado = 200): void
    {
        http_response_code($codigoEstado);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Guarda un mensaje de alerta temporal en sesión (success, error, warning, info)
     */
    protected function setFlash(string $tipo, string $mensaje): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = [
            'tipo' => $tipo,
            'mensaje' => $mensaje
        ];
    }
}
