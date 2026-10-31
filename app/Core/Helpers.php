<?php

use App\Config\App;

/**
 * Funciones de ayuda globales para los alumnos (Helpers)
 */

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        return App::baseUrl() . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('asset')) {
    function asset(string $path = ''): string
    {
        return App::baseUrl() . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $route = ''): string
    {
        $route = ltrim($route, '/');
        if ($route === '') {
            return base_url();
        }

        // Si la ruta contiene parámetros GET (ej: "recepcion/checkin?reserva_id=3")
        if (str_contains($route, '?')) {
            [$path, $query] = explode('?', $route, 2);
            return base_url('index.php?url=' . $path . '&' . $query);
        }

        return base_url('index.php?url=' . $route);
    }
}

if (!function_exists('e')) {
    /**
     * Escapa un texto para imprimirlo de forma segura en HTML
     */
    function e(mixed $valor): string
    {
        return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('partial')) {
    /**
     * Incluye un fragmento reutilizable de app/Views/partials (ver docs/GUIA_UI.md)
     */
    function partial(string $nombre, array $datos = []): void
    {
        $__ruta = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . $nombre . '.php';
        extract($datos, EXTR_SKIP);
        require $__ruta;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $route = ''): void
    {
        header('Location: ' . url($route));
        exit;
    }
}

if (!function_exists('auth_check')) {
    /**
     * Verifica si el usuario actual ha iniciado sesión
     */
    function auth_check(): bool
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        return !empty($_SESSION['usuario']);
    }
}

if (!function_exists('auth_user')) {
    /**
     * Obtiene los datos del usuario en sesión o un campo específico
     */
    function auth_user(?string $campo = null): mixed
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $usuario = $_SESSION['usuario'] ?? null;
        if ($campo === null) {
            return $usuario;
        }
        return is_array($usuario) ? ($usuario[$campo] ?? null) : null;
    }
}

if (!function_exists('old')) {
    /**
     * Recupera un valor previamente enviado en formulario (para repoblar tras error)
     */
    function old(string $campo, string $default = ''): string
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $valor = $_SESSION['old'][$campo] ?? $default;
        unset($_SESSION['old'][$campo]);
        return (string)$valor;
    }
}

if (!function_exists('dinero')) {
    function dinero(float|int|string|null $monto): string
    {
        $num = (float)($monto ?? 0);
        return 'S/. ' . number_format($num, 2, '.', ',');
    }
}

if (!function_exists('fecha_es')) {
    function fecha_es(?string $fecha, bool $conHora = false): string
    {
        if (empty($fecha)) return '-';
        $timestamp = strtotime($fecha);
        if (!$timestamp) return $fecha;
        return $conHora ? date('d/m/Y H:i', $timestamp) : date('d/m/Y', $timestamp);
    }
}

if (!function_exists('badge_estado_habitacion')) {
    function badge_estado_habitacion(string $estado): string
    {
        return match ($estado) {
            'disponible'    => '<span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>Disponible</span>',
            'ocupada'       => '<span class="badge badge-soft-danger"><i class="bi bi-person-fill me-1"></i>Ocupada</span>',
            'limpieza'      => '<span class="badge badge-soft-warning"><i class="bi bi-stars me-1"></i>Limpieza</span>',
            'mantenimiento' => '<span class="badge badge-soft-secondary"><i class="bi bi-tools me-1"></i>Mantenimiento</span>',
            default         => '<span class="badge badge-soft-secondary">' . htmlspecialchars(ucfirst($estado)) . '</span>',
        };
    }
}

if (!function_exists('badge_estado_reserva')) {
    function badge_estado_reserva(string $estado): string
    {
        return match ($estado) {
            'confirmada' => '<span class="badge badge-soft-primary"><i class="bi bi-calendar-check me-1"></i>Confirmada</span>',
            'pendiente'  => '<span class="badge badge-soft-warning"><i class="bi bi-clock-history me-1"></i>Pendiente</span>',
            'en_estadia' => '<span class="badge badge-soft-success"><i class="bi bi-door-open-fill me-1"></i>En Estadía</span>',
            'finalizada' => '<span class="badge badge-soft-secondary"><i class="bi bi-check2-all me-1"></i>Finalizada</span>',
            'cancelada'  => '<span class="badge badge-soft-danger"><i class="bi bi-x-circle me-1"></i>Cancelada</span>',
            default      => '<span class="badge badge-soft-secondary">' . htmlspecialchars(ucfirst($estado)) . '</span>',
        };
    }
}


