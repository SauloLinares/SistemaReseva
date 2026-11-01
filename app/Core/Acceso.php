<?php

namespace App\Core;

use App\Config\Database;

/**
 * Control de acceso por rol (Seguridad → Matriz de Permisos).
 *
 * Cada pantalla o acción del sistema pertenece a un módulo de la matriz y pide una de sus
 * acciones: ver, crear, editar o eliminar. El Router consulta aquí antes de ejecutar el
 * controlador, así un permiso quitado en la matriz se respeta aunque se escriba la URL a mano.
 *
 * El rol Administrador (id 1) tiene siempre todos los permisos y su fila de la matriz no se
 * puede cambiar: así nadie deja el sistema sin un usuario que pueda administrarlo.
 */
class Acceso
{
    public const ROL_ADMIN = 1;
    public const ACCIONES = ['ver', 'crear', 'editar', 'eliminar'];
    public const MODULOS = [
        'reservas'      => 'Gestión de Reservas',
        'huespedes'     => 'Directorio de Huéspedes',
        'habitaciones'  => 'Administración de Habitaciones',
        'recepcion'     => 'Check-in y Check-out',
        'pagos'         => 'Caja y Facturación',
        'servicios'     => 'Servicios Adicionales',
        'reportes'      => 'Reportes y Estadísticas',
        'seguridad'     => 'Seguridad y Usuarios',
        'configuracion' => 'Configuración del Hotel',
    ];

    /** Controlador → módulo. Los que no están (Home, Perfil, Login) los usa cualquier usuario con sesión. */
    private const MODULO_DE = [
        'ReservaController'       => 'reservas',
        'HuespedController'       => 'huespedes',
        'ClienteController'       => 'huespedes',
        'HabitacionController'    => 'habitaciones',
        'RecepcionController'     => 'recepcion',
        'PagoController'          => 'pagos',
        'CajaController'          => 'pagos',
        'ServicioController'      => 'servicios',
        'ProductoController'      => 'servicios',
        'ReporteController'       => 'reportes',
        'SeguridadController'     => 'seguridad',
        'ConfiguracionController' => 'configuracion',
    ];

    /**
     * Métodos que no siguen la regla general de nombres (ver accionDe()).
     * Cargar consumos a una estadía es trabajo de recepción, aunque viva en ServicioController.
     */
    private const EXCEPCIONES = [
        'ServicioController@consumo'           => ['recepcion', 'ver'],
        'ServicioController@porHuesped'        => ['recepcion', 'ver'],
        'ServicioController@guardarConsumo'    => ['recepcion', 'crear'],
        'ServicioController@apiGuardarConsumo' => ['recepcion', 'crear'],
        'CajaController@abrir'                 => ['pagos', 'crear'],
        'CajaController@cerrar'                => ['pagos', 'editar'],
        'RecepcionController@procesarCheckin'  => ['recepcion', 'crear'],
        'RecepcionController@procesarCheckout' => ['recepcion', 'crear'],
        'RecepcionController@procesarCambio'   => ['recepcion', 'editar'],
        'ReservaController@cancelar'           => ['reservas', 'eliminar'],
        'PagoController@devolucion'            => ['pagos', 'eliminar'],
        'HabitacionController@finalizarMantenimiento' => ['habitaciones', 'editar'],
        'HabitacionController@guardarTarifas'  => ['habitaciones', 'editar'],
        'ConfiguracionController@guardarHotel' => ['configuracion', 'editar'],
        'ConfiguracionController@guardarParametros' => ['configuracion', 'editar'],
        'SeguridadController@guardarPermisos'  => ['seguridad', 'editar'],
    ];

    /**
     * Lo que solo hace el rol Administrador, aunque la matriz dé permisos de Configuración:
     * el respaldo lleva los datos de todos los huéspedes y las contraseñas cifradas.
     */
    public const SOLO_ADMINISTRADOR = 'administrador';
    private const SOLO_ADMIN = ['ConfiguracionController@respaldo', 'ConfiguracionController@descargarRespaldo'];

    private static ?array $permisos = null;

    /**
     * Módulo y acción que pide un método de controlador, o null si basta con tener sesión.
     */
    public static function requerido(string $controlador, string $metodo): ?array
    {
        $clase = substr(strrchr('\\' . $controlador, '\\'), 1);
        if (in_array("{$clase}@{$metodo}", self::SOLO_ADMIN, true)) {
            return [self::SOLO_ADMINISTRADOR, 'ver'];
        }
        if (isset(self::EXCEPCIONES["{$clase}@{$metodo}"])) {
            return self::EXCEPCIONES["{$clase}@{$metodo}"];
        }
        $modulo = self::MODULO_DE[$clase] ?? null;
        return $modulo === null ? null : [$modulo, self::accionDe($metodo)];
    }

    /**
     * ¿El usuario puede abrir esta ruta del sistema ('reservas/crear', 'caja')? Para el menú.
     */
    public static function puedeRuta(string $ruta): bool
    {
        $partes = explode('/', trim(explode('?', $ruta)[0], '/'));
        $clase = self::CONTROLADOR_DE_RUTA[strtolower($partes[0] ?? '')] ?? null;
        if ($clase === null) {
            return auth_check();
        }
        $requerido = self::requerido($clase, $partes[1] ?? 'index');
        return $requerido === null ? auth_check() : self::puede(...$requerido);
    }

    /** Primer tramo de la URL → controlador (como lo resuelve el Router, alias incluidos) */
    private const CONTROLADOR_DE_RUTA = [
        'reservas' => 'ReservaController', 'disponibilidad' => 'ReservaController', 'calendario' => 'ReservaController',
        'huespedes' => 'HuespedController', 'clientes' => 'ClienteController',
        'habitaciones' => 'HabitacionController',
        'recepcion' => 'RecepcionController', 'checkin' => 'RecepcionController', 'checkout' => 'RecepcionController', 'estadias' => 'RecepcionController',
        'pagos' => 'PagoController', 'caja' => 'CajaController',
        'servicios' => 'ServicioController', 'productos' => 'ProductoController',
        'reportes' => 'ReporteController', 'seguridad' => 'SeguridadController', 'configuracion' => 'ConfiguracionController',
    ];

    /**
     * Regla general por el nombre del método: eliminar* → eliminar; editar, actualizar y
     * cambiar* → editar; crear, guardar* y api* de alta → crear (guardar con un id ya
     * existente es una edición); el resto (listados, búsquedas, detalles) → ver.
     */
    private static function accionDe(string $metodo): string
    {
        $m = strtolower($metodo);
        if (str_starts_with($m, 'eliminar') || str_starts_with($m, 'anular')) {
            return 'eliminar';
        }
        if (in_array($m, ['editar', 'actualizar'], true) || str_starts_with($m, 'cambiar')) {
            return 'editar';
        }
        if ($m === 'crear' || str_starts_with($m, 'guardar') || str_starts_with($m, 'apiguardar')) {
            return !empty($_POST['id']) ? 'editar' : 'crear';
        }
        return 'ver';
    }

    /**
     * ¿El usuario en sesión puede hacer $accion en $modulo?
     */
    public static function puede(string $modulo, string $accion = 'ver'): bool
    {
        if (!auth_check()) {
            return false;
        }
        if ((int) auth_user('rol_id') === self::ROL_ADMIN) {
            return true;
        }
        if ($modulo === self::SOLO_ADMINISTRADOR) {
            return false;
        }
        if (self::$permisos === null) {
            self::$permisos = self::cargarPermisos((int) auth_user('rol_id'));
        }
        return isset(self::$permisos['todos']) || isset(self::$permisos["{$modulo}.{$accion}"]);
    }

    /**
     * Permisos del rol como ['modulo.accion' => true]. Entiende los valores que dejó la
     * instalación inicial: "todos", "gestionar" (ver, crear y editar) y "mantenimiento".
     */
    private static function cargarPermisos(int $rolId): array
    {
        $stmt = Database::conectar()->prepare('SELECT modulo, accion FROM permisos WHERE rol_id = :r');
        $stmt->execute([':r' => $rolId]);
        return self::expandir($stmt->fetchAll());
    }

    /**
     * Filas de la tabla permisos → ['modulo.accion' => true] (o ['todos' => true]).
     * También la usa la Matriz de Permisos para marcar lo que el rol puede hacer de verdad.
     */
    public static function expandir(array $filas): array
    {
        $permisos = [];
        foreach ($filas as $p) {
            if ($p['modulo'] === 'todos') {
                $permisos['todos'] = true;
                continue;
            }
            $acciones = match ($p['accion']) {
                'gestionar'     => ['ver', 'crear', 'editar'],
                'mantenimiento' => ['ver', 'editar'],
                default         => [$p['accion']],
            };
            foreach ($acciones as $a) {
                $permisos["{$p['modulo']}.{$a}"] = true;
            }
        }
        return $permisos;
    }

    /**
     * Al empezar cada petición. Si pasó más de App::sesionMinutos() sin uso, la sesión se
     * cierra (una PC de recepción que quedó abierta no queda con acceso). Vuelve a leer el
     * usuario de la base: si el administrador lo desactivó o lo borró, la sesión se cierra; si
     * le cambió el rol, rige el nuevo desde ya.
     */
    public static function sincronizarSesion(): void
    {
        if (!auth_check()) {
            return;
        }
        $minutos = \App\Config\App::sesionMinutos();
        if (time() - (int) ($_SESSION['ultima_actividad'] ?? time()) > $minutos * 60) {
            self::cerrarSesion("Tu sesión se cerró tras {$minutos} minutos sin uso. Inicia sesión otra vez para continuar.");
            return;
        }
        $_SESSION['ultima_actividad'] = time();

        // rol_id vacío (usuarios creados antes de que se guardara): el rol se reconoce por su nombre
        $stmt = Database::conectar()->prepare(
            'SELECT u.estado, r.id AS rol_id, r.nombre AS rol FROM usuarios u
             LEFT JOIN roles r ON r.id = COALESCE(u.rol_id, (SELECT id FROM roles WHERE nombre = u.rol LIMIT 1))
             WHERE u.id = :id'
        );
        $stmt->execute([':id' => (int) auth_user('id')]);
        $fila = $stmt->fetch();

        if (!$fila || (int) $fila['estado'] !== 1) {
            self::cerrarSesion('Tu cuenta fue desactivada. Si crees que es un error, habla con el administrador.');
            return;
        }
        $_SESSION['usuario']['rol_id'] = (int) $fila['rol_id'];
        $_SESSION['usuario']['rol'] = $fila['rol'] ?? '';
        self::$permisos = null;
    }

    private static function cerrarSesion(string $aviso): void
    {
        unset($_SESSION['usuario'], $_SESSION['ultima_actividad']);
        session_regenerate_id(true);
        $_SESSION['flash'] = ['tipo' => 'warning', 'mensaje' => $aviso];
        self::$permisos = null;
    }
}
