<?php

namespace App\Core;

/**
 * Autoloader PSR-4 básico para cargar automáticamente las clases del namespace App\
 * Permite a los alumnos crear clases sin preocuparse por hacer require de cada archivo.
 */
class Autoloader
{
    public static function registrar(): void
    {
        spl_autoload_register(function ($clase) {
            $prefijo = 'App\\';
            $directorioBase = dirname(__DIR__) . DIRECTORY_SEPARATOR;

            $longitudPrefijo = strlen($prefijo);
            if (strncmp($prefijo, $clase, $longitudPrefijo) !== 0) {
                return;
            }

            $claseRelativa = substr($clase, $longitudPrefijo);
            $archivo = $directorioBase . str_replace('\\', DIRECTORY_SEPARATOR, $claseRelativa) . '.php';

            if (file_exists($archivo)) {
                require_once $archivo;
            }
        });
    }
}
