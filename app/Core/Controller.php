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

        // Se captura la página completa para insertar el token CSRF en sus formularios
        csrf_token();
        ob_start();

        require_once $layoutsPath . 'header.php';
        require_once $layoutsPath . 'navbar.php';
        require_once $layoutsPath . 'sidebar.php';

        echo "<!--begin::App Main-->\n<main class=\"app-main\">\n";
        require_once $viewPath;
        echo "\n</main>\n<!--end::App Main-->\n";

        require_once $layoutsPath . 'footer.php';

        echo csrf_inyectar(ob_get_clean());
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
     * Renderiza una vista con el layout de autenticación (Login, Registro, etc.)
     *
     * @param string $vista Ruta de la vista relativa a app/Views (ej: 'auth/login')
     * @param array $datos Datos que se pasarán a la vista como variables
     * @param string $titulo Título de la página
     */
    protected function renderAuth(string $vista, array $datos = [], string $titulo = 'Iniciar Sesión'): void
    {
        extract($datos);

        $pageTitle = $titulo;
        $viewPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $vista) . '.php';

        if (!file_exists($viewPath)) {
            die("La vista <code>{$vista}</code> no existe en: <strong>{$viewPath}</strong>");
        }

        csrf_token();
        ob_start();
        require_once $viewPath;
        echo csrf_inyectar(ob_get_clean());
    }

    /**
     * Exige que el usuario esté autenticado para acceder al recurso.
     * Si no ha iniciado sesión, lo redirige al formulario de login.
     */
    protected function requireAuth(): void
    {
        if (!auth_check()) {
            $this->setFlash('warning', 'Debes iniciar sesión para acceder al sistema.');
            redirect('login');
        }
    }

    /**
     * Exige que el usuario NO haya iniciado sesión (visitante).
     * Si ya tiene sesión activa, lo redirige al panel principal.
     */
    protected function requireGuest(): void
    {
        if (auth_check()) {
            redirect('');
        }
    }

    /**
     * Guarda un mensaje de alerta temporal en sesión (success, danger, warning, info)
     */
    protected function setFlash(string $tipo, string $mensaje): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $_SESSION['flash'] = [
            'tipo' => $tipo,
            'mensaje' => $mensaje
        ];
    }
}
