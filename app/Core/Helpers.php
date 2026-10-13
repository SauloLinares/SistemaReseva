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
        return base_url($route ? 'index.php?url=' . $route : '');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $route = ''): void
    {
        header('Location: ' . url($route));
        exit;
    }
}
