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
