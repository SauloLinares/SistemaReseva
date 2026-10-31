<?php

namespace App\Core;

use App\Config\App;

/**
 * Respuesta ante un error inesperado del servidor (excepción sin atrapar).
 * El detalle va al log de PHP; al usuario se le muestra un aviso entendible
 * (JSON para las peticiones fetch, una página para la navegación normal).
 * En desarrollo también se muestra el detalle, para depurar sin abrir el log.
 */
class Errores
{
    public static function excepcion(\Throwable $e): void
    {
        $referencia = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        error_log(sprintf('[SistemaReserva %s] %s: %s en %s:%d', $referencia, get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));

        // Descartar lo que la página alcanzó a generar antes del error
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (headers_sent()) {
            return;
        }
        http_response_code(500);
        header_remove('Location');

        $mensaje = 'Ocurrió un error inesperado y la operación no se completó. Vuelve a intentarlo; si se repite, avisa al administrador indicando el código ' . $referencia . '.';
        $detalle = App::esDesarrollo() ? get_class($e) . ': ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')' : '';

        $esAjax = isset($_SERVER['HTTP_X_CSRF_TOKEN']) || isset($_SERVER['HTTP_X_OPERACION'])
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        if ($esAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'mensaje' => $mensaje, 'message' => $mensaje, 'detalle' => $detalle], JSON_UNESCAPED_UNICODE);
            return;
        }

        header('Content-Type: text/html; charset=utf-8');
        $css = e(asset('adminlte/css/adminlte.min.css'));
        $inicio = e(base_url());
        $anterior = $_SERVER['HTTP_REFERER'] ?? '';
        $volver = str_starts_with($anterior, base_url()) ? "<a href='" . e($anterior) . "' class='btn btn-outline-secondary me-2'>Volver</a>" : '';
        $detalleHtml = $detalle !== '' ? "<pre class='text-start small bg-body-tertiary p-2 rounded'>" . e($detalle) . '</pre>' : '';
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Error del sistema · Hotel</title>
    <link rel='stylesheet' href='{$css}'>
</head>
<body class='bg-light d-flex align-items-center' style='min-height: 100vh;'>
    <main class='container text-center py-5'>
        <div class='card shadow-sm mx-auto' style='max-width: 560px;'>
            <div class='card-body p-4'>
                <h1 class='h3 mb-3'>No se pudo completar la operación</h1>
                <p class='text-muted'>" . e($mensaje) . "</p>
                {$detalleHtml}
                {$volver}
                <a href='{$inicio}' class='btn btn-primary'>Ir al inicio</a>
            </div>
        </div>
    </main>
</body>
</html>";
    }
}
