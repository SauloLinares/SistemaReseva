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

        echo quitar_librerias_sin_uso(rutas_en_formularios_get(csrf_inyectar(ob_get_clean())));
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

        // Contraseña asignada por el administrador (o la inicial): no se usa el sistema hasta cambiarla
        $ruta = strtolower(trim(explode('?', $_GET['url'] ?? '')[0], '/'));
        if (auth_user('debe_cambiar_clave') && !in_array($ruta, ['perfil/clave', 'perfil/guardarclave'], true)) {
            $this->setFlash('warning', 'Antes de continuar, crea tu propia contraseña.');
            redirect('perfil/clave');
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
     * Regresa al formulario conservando lo que el usuario escribió y marcando los campos con error.
     * La vista no necesita cambios: footer.php entrega los datos y custom.js rellena el formulario
     * cuyo action coincide con la ruta que se está procesando.
     *
     * @param string $ruta     Ruta del formulario a la que se vuelve (ej. 'huespedes/crear')
     * @param array  $errores  ['nombre_del_campo' => 'Mensaje para ese campo']
     * @param string $mensaje  Aviso general (toast); si se omite se usa uno genérico
     */
    protected function volverConErrores(string $ruta, array $errores, string $mensaje = ''): never
    {
        // Nunca se devuelven contraseñas ni el token al navegador
        $datos = array_filter(
            $_POST,
            fn($clave) => !in_array($clave, ['_csrf', '_operacion'], true) && stripos((string)$clave, 'password') === false && stripos((string)$clave, 'clave') === false,
            ARRAY_FILTER_USE_KEY
        );

        $_SESSION['form_previo'] = [
            'accion'  => trim($_GET['url'] ?? '', '/'),
            'datos'   => $datos,
            'errores' => $errores,
        ];

        $this->setFlash('danger', $mensaje !== '' ? $mensaje : 'Revisa los campos marcados en rojo.');
        redirect($ruta);
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
