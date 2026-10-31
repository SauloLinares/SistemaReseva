<?php

namespace App\Core;

use App\Config\Database;
use PDO;

/**
 * Evita registrar dos veces la misma operación (cobros, consumos, reservas...).
 *
 * El navegador manda una clave única por cada intento de envío (campo _operacion o
 * cabecera X-Operacion) y la repite si reintenta lo mismo: un doble clic, un reintento
 * tras un corte de red o un F5 sobre el envío. La primera vez la operación se ejecuta;
 * las siguientes reciben la misma respuesta que la primera, sin volver a ejecutarla.
 *
 * Solo se recuerda una operación que terminó bien. Si volvió con errores de validación
 * o falló, la clave se libera y el mismo intento se puede reenviar corregido.
 */
class Operacion
{
    /** Minutos tras los que una operación "en curso" se da por abandonada (el proceso murió). */
    private const ABANDONADA_MIN = 2;
    /** Días que se guardan las claves completadas. */
    private const RETENCION_DIAS = 2;

    private static ?string $clave = null;

    /**
     * Se llama antes de ejecutar el controlador. Si la operación ya se registró,
     * responde como la primera vez y termina la petición.
     */
    public static function iniciar(): void
    {
        $clave = $_POST['_operacion'] ?? $_SERVER['HTTP_X_OPERACION'] ?? '';
        if (!is_string($clave) || !preg_match('/^[A-Za-z0-9-]{16,64}$/', $clave)) {
            return; // Sin clave: la petición sigue como siempre
        }

        $db = Database::conectar();
        self::crearTabla($db);
        $db->exec("DELETE FROM operaciones WHERE creado < NOW() - INTERVAL " . self::RETENCION_DIAS . " DAY");

        $usuarioId = (int) (auth_user('id') ?? 0);
        $ruta = substr(trim($_GET['url'] ?? '', '/'), 0, 150);

        try {
            $db->prepare("INSERT INTO operaciones (clave, usuario_id, ruta, estado) VALUES (?, ?, ?, 'en_curso')")
               ->execute([$clave, $usuarioId, $ruta]);
        } catch (\PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            // La clave ya existe: es un reintento
            $previa = $db->prepare("SELECT *, creado < NOW() - INTERVAL " . self::ABANDONADA_MIN . " MINUTE AS abandonada
                                      FROM operaciones WHERE clave = ?");
            $previa->execute([$clave]);
            $fila = $previa->fetch(PDO::FETCH_ASSOC);

            if (!$fila || (int) $fila['usuario_id'] !== $usuarioId) {
                return; // Clave de otro usuario: se ignora y la petición sigue sin protección
            }
            if ($fila['estado'] === 'completada') {
                self::repetirRespuesta($fila);
            }
            if (!$fila['abandonada']) {
                self::responderEnCurso();
            }
            // En curso pero abandonada: se retoma
            $db->prepare("UPDATE operaciones SET creado = NOW() WHERE clave = ?")->execute([$clave]);
        }

        self::$clave = $clave;
        ob_start(); // Para leer la respuesta JSON al terminar
        register_shutdown_function([self::class, 'finalizar']);
    }

    /**
     * Al terminar la petición: si salió bien se guarda la respuesta; si no, se libera la clave.
     */
    public static function finalizar(): void
    {
        if (self::$clave === null) {
            return;
        }
        $db = Database::conectar();
        $clave = self::$clave;
        self::$clave = null;

        $ubicacion = self::cabecera('Location');
        $esJson = str_contains((string) self::cabecera('Content-Type'), 'application/json');
        $fatal = error_get_last();
        $fatal = $fatal && in_array($fatal['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true);
        $flash = $_SESSION['flash']['tipo'] ?? '';

        $tipo = null;
        $respuesta = null;
        if (!$fatal && http_response_code() < 400) {
            if ($esJson) {
                $cuerpo = (string) ob_get_contents();
                $datos = json_decode($cuerpo, true);
                if (is_array($datos) && ($datos['success'] ?? false) === true) {
                    [$tipo, $respuesta] = ['json', $cuerpo];
                }
            } elseif ($ubicacion !== null && !in_array($flash, ['danger', 'warning', 'error'], true)) {
                [$tipo, $respuesta] = ['redireccion', $ubicacion];
            }
        }

        if ($tipo === null) {
            $db->prepare("DELETE FROM operaciones WHERE clave = ?")->execute([$clave]);
            return;
        }
        $db->prepare("UPDATE operaciones SET estado = 'completada', tipo = ?, respuesta = ? WHERE clave = ?")
           ->execute([$tipo, $respuesta, $clave]);

        // El navegador borra su intento pendiente al ver la clave en la página siguiente
        if ($tipo === 'redireccion') {
            $_SESSION['operaciones_completadas'][] = $clave;
        }
    }

    private static function repetirRespuesta(array $fila): never
    {
        if ($fila['tipo'] === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            $datos = json_decode($fila['respuesta'], true) ?: [];
            $datos['repetida'] = true;
            echo json_encode($datos, JSON_UNESCAPED_UNICODE);
            exit;
        }
        $_SESSION['flash'] = ['tipo' => 'info', 'mensaje' => 'Esta operación ya se había registrado; no se volvió a registrar.'];
        $_SESSION['operaciones_completadas'][] = $fila['clave'];
        header('Location: ' . $fila['respuesta']);
        exit;
    }

    private static function responderEnCurso(): never
    {
        $mensaje = 'Esta operación todavía se está procesando. Espera unos segundos antes de reintentar.';
        if (isset($_SERVER['HTTP_X_OPERACION'])) {
            http_response_code(409);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'mensaje' => $mensaje, 'en_curso' => true], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $_SESSION['flash'] = ['tipo' => 'warning', 'mensaje' => $mensaje];
        $anterior = $_SERVER['HTTP_REFERER'] ?? '';
        header('Location: ' . (str_starts_with($anterior, base_url()) ? $anterior : url()));
        exit;
    }

    private static function cabecera(string $nombre): ?string
    {
        foreach (headers_list() as $linea) {
            [$n, $v] = array_map('trim', explode(':', $linea, 2)) + [1 => ''];
            if (strcasecmp($n, $nombre) === 0) {
                return $v;
            }
        }
        return null;
    }

    private static function crearTabla(PDO $db): void
    {
        $db->exec("CREATE TABLE IF NOT EXISTS operaciones (
            clave VARCHAR(64) NOT NULL PRIMARY KEY,
            usuario_id INT NOT NULL DEFAULT 0,
            ruta VARCHAR(150) NOT NULL DEFAULT '',
            estado ENUM('en_curso','completada') NOT NULL DEFAULT 'en_curso',
            tipo ENUM('json','redireccion') NULL,
            respuesta TEXT NULL,
            creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_creado (creado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
