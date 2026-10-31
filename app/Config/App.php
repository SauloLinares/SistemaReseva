<?php

namespace App\Config;

/**
 * Configuración general del proyecto
 */
class App
{
    public const APP_NAME = 'Sistema de Reserva';
    public const APP_VERSION = '1.0.0';

    /**
     * Indica si el sistema corre en un equipo de desarrollo.
     * Se fija con la variable de entorno APP_ENV (desarrollo / produccion); sin ella,
     * solo se considera desarrollo una visita desde el propio equipo a un host local.
     */
    public static function esDesarrollo(): bool
    {
        $entorno = strtolower((string) getenv('APP_ENV'));
        if ($entorno !== '') {
            return in_array($entorno, ['desarrollo', 'development', 'dev', 'local'], true);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
        $ipLocal = in_array($ip, ['127.0.0.1', '::1'], true);
        $hostLocal = in_array($host, ['localhost', '127.0.0.1', '[::1]'], true)
            || preg_match('/\.(test|localhost)$/', $host) === 1;

        return $ipLocal && $hostLocal;
    }

    /**
     * Obtiene la URL base automáticamente (funciona con VirtualHosts o carpetas de Laragon)
     */
    public static function baseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        
        // Normalizar directorio base asegurando slash inicial
        if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') {
            $baseFolder = '';
        } else {
            $baseFolder = '/' . trim($scriptDir, '/');
        }

        return rtrim($protocol . $host . $baseFolder, '/');
    }
}
