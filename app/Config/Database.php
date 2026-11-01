<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Clase Database
 * Encargada de gestionar la conexión a la base de datos MySQL usando PDO.
 * Implementa el patrón Singleton para reutilizar la misma conexión en toda la petición.
 */
class Database
{
    private static string $host = 'localhost';
    private static string $database = 'sistema_reserva';
    private static string $username = 'root';
    private static string $password = '';
    private static ?PDO $conexion = null;

    /**
     * Obtiene la instancia activa de PDO.
     */
    public static function conectar(): PDO
    {
        if (self::$conexion === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$database . ";charset=utf8mb4";

            try {
                self::$conexion = new PDO(
                    $dsn,
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                // NOW() y CURRENT_TIMESTAMP en la misma hora que PHP, sin depender de cómo esté
                // configurado el servidor MySQL
                self::$conexion->exec("SET time_zone = '" . (new \DateTime())->format('P') . "'");
            } catch (PDOException $e) {
                // Mensaje didáctico y visual para guiar a los alumnos ante fallos de conexión
                echo "<!DOCTYPE html>
                <html lang='es'>
                <head>
                    <meta charset='UTF-8'>
                    <title>Error de Conexión - MySQL</title>
                    <style>
                        body { font-family: system-ui, -apple-system, sans-serif; background-color: #f4f6f9; padding: 40px 20px; }
                        .card { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e2e8f0; }
                        .header { background: #dc3545; color: #fff; padding: 20px; }
                        .header h2 { margin: 0; font-size: 1.25rem; }
                        .content { padding: 25px; color: #334155; }
                        .error-box { background: #fff1f2; border: 1px solid #fecdd3; border-radius: 6px; padding: 12px; font-family: monospace; font-size: 0.85rem; color: #be123c; margin: 15px 0; word-break: break-all; }
                        ol { padding-left: 20px; line-height: 1.6; }
                        code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
                    </style>
                </head>
                <body>
                    <div class='card'>
                        <div class='header'>
                            <h2>⚠️ Error de Conexión a la Base de Datos</h2>
                        </div>
                        <div class='content'>
                            <p>No se pudo conectar a la base de datos <strong>" . htmlspecialchars(self::$database) . "</strong> en <code>" . htmlspecialchars(self::$host) . "</code>.</p>
                            
                            <div class='error-box'>
                                <strong>Detalle técnico:</strong> " . htmlspecialchars($e->getMessage()) . "
                            </div>

                            <h4 style='margin-bottom: 8px;'>Guía de solución para alumnos:</h4>
                            <ol>
                                <li>Abre <strong>Laragon</strong> y haz clic en <strong>Start All</strong> para encender Apache y MySQL.</li>
                                <li>Asegúrate de importar el script <code>database/database.sql</code> en <em>phpMyAdmin</em> o <em>HeidiSQL</em>.</li>
                                <li>Si tus credenciales son distintas a <code>root</code> y contraseña vacía, cámbialas en <code>app/Config/Database.php</code>.</li>
                            </ol>
                        </div>
                    </div>
                </body>
                </html>";
                exit;
            }
        }

        return self::$conexion;
    }

    /**
     * Permite usar $db = new Database(); $db->getConexion(); de manera didáctica
     */
    public function getConexion(): PDO
    {
        return self::conectar();
    }
}