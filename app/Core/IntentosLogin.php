<?php

namespace App\Core;

use App\Config\Database;
use PDO;

/**
 * Límite de intentos de ingreso. Tras 5 contraseñas equivocadas para un mismo correo (o 20
 * desde una misma conexión, que podría ir probando varios correos) en 15 minutos, el ingreso
 * se bloquea hasta que pasen 15 minutos del último intento. Así nadie puede probar
 * contraseñas sin parar. Un ingreso correcto borra los intentos de ese correo.
 */
class IntentosLogin
{
    public const MAX_POR_CORREO = 5;
    public const MAX_POR_IP = 20;
    public const MINUTOS = 15;

    /**
     * Minutos que faltan para poder volver a intentar, o 0 si no está bloqueado.
     */
    public static function minutosBloqueado(string $email): int
    {
        $db = self::db();
        $restante = 0;
        $reglas = [['email', mb_strtolower($email), self::MAX_POR_CORREO]];
        // Desde la propia máquina (o detrás de un proxy, donde todas las conexiones llegan como
        // 127.0.0.1) no se cuenta por conexión: se bloquearía a todo el hotel a la vez
        if (!in_array(self::ip(), ['127.0.0.1', '::1', ''], true)) {
            $reglas[] = ['ip', self::ip(), self::MAX_POR_IP];
        }
        foreach ($reglas as [$campo, $valor, $maximo]) {
            $stmt = $db->prepare("SELECT COUNT(*) AS cantidad, MAX(creado) AS ultimo FROM intentos_login
                                  WHERE {$campo} = :v AND creado > NOW() - INTERVAL " . self::MINUTOS . " MINUTE");
            $stmt->execute([':v' => $valor]);
            $fila = $stmt->fetch();
            if ((int) $fila['cantidad'] >= $maximo) {
                $libre = strtotime($fila['ultimo']) + self::MINUTOS * 60;
                $restante = max($restante, (int) ceil(($libre - time()) / 60));
            }
        }
        return max(0, $restante);
    }

    /** Anota una contraseña equivocada (o un correo que no existe). */
    public static function registrarFallo(string $email): void
    {
        $db = self::db();
        $db->prepare("INSERT INTO intentos_login (email, ip) VALUES (:e, :ip)")
           ->execute([':e' => mb_substr(mb_strtolower($email), 0, 150), ':ip' => self::ip()]);
        // Limpieza: lo de más de un día ya no cuenta
        $db->exec("DELETE FROM intentos_login WHERE creado < NOW() - INTERVAL 1 DAY");
    }

    /** ¿Cuántos intentos le quedan a este correo antes del bloqueo? */
    public static function restantes(string $email): int
    {
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM intentos_login
                                     WHERE email = :e AND creado > NOW() - INTERVAL " . self::MINUTOS . " MINUTE");
        $stmt->execute([':e' => mb_strtolower($email)]);
        return max(0, self::MAX_POR_CORREO - (int) $stmt->fetchColumn());
    }

    /** Ingreso correcto: se olvidan los intentos de ese correo. */
    public static function limpiar(string $email): void
    {
        self::db()->prepare("DELETE FROM intentos_login WHERE email = :e")->execute([':e' => mb_strtolower($email)]);
    }

    private static function ip(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    }

    private static function db(): PDO
    {
        $db = Database::conectar();
        static $lista = false;
        if (!$lista) {
            $db->exec("CREATE TABLE IF NOT EXISTS intentos_login (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(150) NOT NULL,
                ip VARCHAR(45) NOT NULL DEFAULT '',
                creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email, creado),
                INDEX idx_ip (ip, creado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $lista = true;
        }
        return $db;
    }
}
