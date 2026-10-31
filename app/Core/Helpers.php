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

/*
 * Protección CSRF
 * Cada sesión tiene un token aleatorio. Todo POST debe enviarlo (campo _csrf o
 * cabecera X-CSRF-Token); el Router lo valida antes de llamar al controlador.
 * Así otra página no puede enviar formularios en nombre del usuario conectado.
 */
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_valido')) {
    /**
     * Compara el token recibido con el de la sesión en tiempo constante
     */
    function csrf_valido(?string $token): bool
    {
        $esperado = $_SESSION['_csrf'] ?? '';
        return $esperado !== '' && is_string($token) && hash_equals($esperado, $token);
    }
}

if (!function_exists('csrf_inyectar')) {
    /**
     * Agrega el campo _csrf a cada <form method="post"> del HTML generado,
     * para no tener que escribirlo a mano en cada vista.
     */
    function csrf_inyectar(string $html): string
    {
        $campo = csrf_field();
        return preg_replace_callback(
            '/<form\b[^>]*\bmethod\s*=\s*["\']?post["\']?[^>]*>/i',
            fn(array $m) => $m[0] . $campo,
            $html
        ) ?? $html;
    }
}

if (!function_exists('script_js')) {
    /**
     * Etiqueta <script src> de un archivo de public/assets/js con su versión (fecha de
     * modificación) para que el navegador no use una copia vieja de la caché.
     * El JavaScript vive en archivos: la CSP no permite <script> inline.
     */
    function script_js(string $ruta): string
    {
        $archivo = dirname(__DIR__, 2) . '/public/assets/js/' . $ruta;
        $version = is_file($archivo) ? filemtime($archivo) : 0;
        return '<script src="' . e(asset('js/' . $ruta)) . '?v=' . $version . '"></script>';
    }
}

if (!function_exists('foto_habitacion')) {
    /**
     * URL de la foto de una habitación. Con $miniatura usa la versión reducida de
     * uploads/habitaciones/miniaturas/ (para listas y tarjetas) si existe.
     * Si la foto no existe, devuelve la imagen por defecto.
     */
    function foto_habitacion(?string $foto, bool $miniatura = false): string
    {
        $dir = dirname(__DIR__, 2) . '/public/uploads/habitaciones/';
        $foto = basename((string)$foto);
        if ($foto === '' || !is_file($dir . $foto)) {
            $foto = 'simple.jpg';
        }
        if ($miniatura && is_file($dir . 'miniaturas/' . $foto)) {
            return base_url('uploads/habitaciones/miniaturas/' . $foto);
        }
        return base_url('uploads/habitaciones/' . $foto);
    }
}

if (!function_exists('opciones_metodo_pago')) {
    /**
     * <option> de los métodos de pago que acepta la base de datos (Pago::METODOS).
     * Usar siempre esta lista: un método escrito a mano que no esté en ella hace fallar el cobro.
     */
    function opciones_metodo_pago(?string $seleccionado = 'Efectivo'): string
    {
        $html = '';
        foreach (\App\Models\Pago::METODOS as $metodo) {
            $html .= '<option value="' . e($metodo) . '"' . ($metodo === $seleccionado ? ' selected' : '') . '>' . e($metodo) . '</option>';
        }
        return $html;
    }
}

if (!function_exists('avatar_usuario')) {
    /**
     * Foto del usuario (Mi perfil) o, si no tiene, un círculo con sus iniciales.
     * $clase: clases extra (tamaño), ej. 'avatar-sm' o 'avatar-xl'.
     */
    function avatar_usuario(?string $foto, string $nombre, string $clase = ''): string
    {
        $archivo = $foto ? dirname(__DIR__, 2) . '/public/uploads/usuarios/' . basename($foto) : '';
        if ($archivo !== '' && is_file($archivo)) {
            return '<img src="' . e(base_url('uploads/usuarios/' . basename($foto))) . '" class="avatar-usuario ' . e($clase)
                . '" alt="Foto de ' . e($nombre) . '" />';
        }
        $partes = preg_split('/\s+/', trim($nombre)) ?: [];
        $iniciales = mb_strtoupper(mb_substr($partes[0] ?? '?', 0, 1) . (count($partes) > 1 ? mb_substr(end($partes), 0, 1) : ''));
        return '<span class="avatar-usuario avatar-iniciales ' . e($clase) . '" aria-hidden="true">' . e($iniciales) . '</span>';
    }
}

if (!function_exists('rutas_en_formularios_get')) {
    /**
     * Un <form method="GET"> descarta la consulta de su action al enviarse: con
     * action="index.php?url=huespedes" el navegador va a index.php?q=... y se pierde la
     * ruta (se llegaba al Panel en vez del listado filtrado). Se agrega la ruta y los demás
     * parámetros del action como campos ocultos.
     */
    function rutas_en_formularios_get(string $html): string
    {
        return preg_replace_callback(
            '/<form\b[^>]*\bmethod\s*=\s*["\']?get["\']?[^>]*>/i',
            function (array $m) use ($html) {
                if (!preg_match('/\baction\s*=\s*"([^"]*)"/i', $m[0], $accion)) {
                    return $m[0];
                }
                $consulta = parse_url(html_entity_decode($accion[1]), PHP_URL_QUERY);
                if (!$consulta) {
                    return $m[0];
                }
                parse_str($consulta, $parametros);
                $ocultos = '';
                foreach ($parametros as $nombre => $valor) {
                    if (is_string($valor)) {
                        $ocultos .= '<input type="hidden" name="' . e($nombre) . '" value="' . e($valor) . '">';
                    }
                }
                return $m[0] . $ocultos;
            },
            $html
        ) ?? $html;
    }
}

if (!function_exists('quitar_librerias_sin_uso')) {
    /**
     * Quita del HTML final las librerías opcionales (marcadas con data-lib="...")
     * que la página no usa. Así TomSelect y Simple-DataTables solo se descargan
     * donde hay un selector con búsqueda o una tabla interactiva.
     */
    function quitar_librerias_sin_uso(string $html): string
    {
        // Señales de uso en la página (clases o llamadas en el HTML/JS de la vista)
        $usos = [
            'tomselect'  => ['select-search', 'select-huesped', 'new TomSelect', 'initTomSelectHuesped'],
            'datatables' => ['datatable-init', 'table-datatable', 'simpleDatatables'],
        ];

        foreach ($usos as $lib => $senales) {
            // Las propias etiquetas data-lib no cuentan como uso
            $sinEtiquetas = preg_replace('/<(?:script|link)\b[^>]*data-lib="' . $lib . '"[^>]*>(?:<\/script>)?/i', '', $html);
            $seUsa = false;
            foreach ($senales as $senal) {
                if (str_contains($sinEtiquetas, $senal)) {
                    $seUsa = true;
                    break;
                }
            }
            if (!$seUsa) {
                $html = $sinEtiquetas;
            }
        }
        return $html;
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
        // Si no es una fecha se devuelve escapada: el resultado se imprime en HTML sin e()
        if (!$timestamp) return e($fecha);
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


