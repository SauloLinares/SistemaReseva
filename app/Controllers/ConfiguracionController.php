<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Configuracion;
use App\Models\Auditoria;

class ConfiguracionController extends Controller
{
    private Configuracion $configModel;
    private Auditoria $auditoriaModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->configModel = new Configuracion();
        $this->auditoriaModel = new Auditoria();
    }

    public function index(): void
    {
        $this->hotel();
    }

    /**
     * Datos del hotel
     */
    public function hotel(): void
    {
        $config = $this->configModel->obtenerTodos();

        $this->render('configuracion/hotel', [
            'config' => $config
        ], 'Datos del Hotel - Configuración');
    }

    /** Tipos de imagen aceptados para el logo (constante IMAGETYPE_* => extensión) */
    private const TIPOS_LOGO = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];

    /**
     * Comprueba que el archivo subido sea una imagen PNG, JPG o WEBP de verdad
     * (no basta la extensión). Devuelve el mensaje de error o null si es válido.
     */
    private function validarLogo(array $archivo): ?string
    {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return 'No se pudo subir el logo (¿pesa demasiado?). Inténtalo con otra imagen.';
        }
        if ($archivo['size'] > 2 * 1024 * 1024) {
            return 'El logo no debe pesar más de 2 MB.';
        }
        $info = @getimagesize($archivo['tmp_name']);
        if ($info === false || !isset(self::TIPOS_LOGO[$info[2]])) {
            return 'El logo debe ser una imagen PNG, JPG o WEBP.';
        }
        return null;
    }

    /**
     * Guardar datos del hotel
     */
    public function guardarHotel(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('configuracion/hotel');
        }

        $campos = [
            'hotel_nombre',
            'hotel_ruc',
            'hotel_telefono',
            'hotel_correo',
            'hotel_direccion',
            'hotel_checkin_hora',
            'hotel_checkout_hora'
        ];

        // El logo se valida antes de guardar nada: solo imágenes PNG, JPG o WEBP reales
        // (SVG no: puede contener código que se ejecuta si se abre su dirección directamente)
        $logoSubido = !empty($_FILES['hotel_logo']['name']);
        if ($logoSubido) {
            $error = $this->validarLogo($_FILES['hotel_logo']);
            if ($error !== null) {
                $this->volverConErrores('configuracion/hotel', ['hotel_logo' => $error]);
            }
        }

        foreach ($campos as $c) {
            if (isset($_POST[$c])) {
                $this->configModel->guardar($c, trim($_POST[$c]), 'general');
            }
        }

        // Guardar el logotipo (ya validado arriba); la extensión sale del tipo real de la imagen
        if ($logoSubido) {
            $ext = self::TIPOS_LOGO[getimagesize($_FILES['hotel_logo']['tmp_name'])[2]];
            $dirUploads = dirname(__DIR__, 2) . '/public/uploads/hotel/';
            if (!is_dir($dirUploads)) {
                mkdir($dirUploads, 0775, true);
            }
            $nombreArchivo = 'logo_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['hotel_logo']['tmp_name'], $dirUploads . $nombreArchivo)) {
                $this->configModel->guardar('hotel_logo', $nombreArchivo, 'general', 'Logotipo oficial del establecimiento');
            }
        }

        $this->auditoriaModel->registrar('Configuración', 'Datos Hotel', 'Actualización de datos generales del hotel');
        $this->setFlash('success', 'Datos del hotel actualizados correctamente.');
        redirect('configuracion/hotel');
    }

    /**
     * Tipos de documento
     */
    public function documentos(): void
    {
        $documentos = [
            ['codigo' => 'DNI', 'nombre' => 'Documento Nacional de Identidad', 'longitud' => '8 dígitos', 'pais' => 'Perú'],
            ['codigo' => 'Pasaporte', 'nombre' => 'Pasaporte Internacional', 'longitud' => 'Variable', 'pais' => 'Internacional'],
            ['codigo' => 'Carnet Extranjería', 'nombre' => 'Carnet de Extranjería (CE)', 'longitud' => '9-12 dígitos', 'pais' => 'Extranjeros residentes'],
            ['codigo' => 'RUC', 'nombre' => 'Registro Único de Contribuyentes (Facturación)', 'longitud' => '11 dígitos', 'pais' => 'Empresas / Negocios'],
            ['codigo' => 'Otro', 'nombre' => 'Otro Documento de Identificación', 'longitud' => 'Variable', 'pais' => 'Cualquiera']
        ];

        $this->render('configuracion/documentos', [
            'documentos' => $documentos
        ], 'Tipos de Documento de Identidad');
    }

    /**
     * Métodos de pago que acepta el sistema (Pago::METODOS) y cuánto se cobró con cada uno
     * en el mes elegido. Antes mostraba una lista fija con comisiones de ejemplo.
     */
    public function metodosPago(): void
    {
        $mes = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['mes'] ?? '') ? $_GET['mes'] : date('Y-m');
        $desde = $mes . '-01';
        $hasta = date('Y-m-t', strtotime($desde));

        $this->render('configuracion/metodos_pago', [
            'mes'     => $mes,
            'resumen' => (new \App\Models\Pago())->resumenPorMetodo($desde, $hasta),
        ], 'Métodos de Pago');
    }

    /**
     * Parámetros generales y tarifas base
     */
    public function parametros(): void
    {
        $config = $this->configModel->obtenerTodos();

        $this->render('configuracion/parametros', [
            'config' => $config
        ], 'Parámetros Generales del Sistema');
    }

    /**
     * Guardar parámetros generales
     */
    public function guardarParametros(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('configuracion/parametros');
        }

        $campos = [
            'hotel_moneda_simbolo',
            'hotel_igv_porcentaje',
            'hotel_politica_cancelacion'
        ];

        foreach ($campos as $c) {
            if (isset($_POST[$c])) {
                $this->configModel->guardar($c, trim($_POST[$c]), 'parametros');
            }
        }

        $this->auditoriaModel->registrar('Configuración', 'Parámetros', 'Parámetros comerciales e impuestos actualizados');
        $this->setFlash('success', 'Parámetros generales guardados correctamente.');
        redirect('configuracion/parametros');
    }

    /** Días tras los que se avisa que conviene un respaldo nuevo */
    public const DIAS_RESPALDO = 7;

    /**
     * Respaldo de la base de datos y las fotos (solo el Administrador: lleva los datos de
     * todos los huéspedes). Muestra cuándo fue el último y quién lo descargó.
     */
    public function respaldo(): void
    {
        $ultimo = json_decode($this->configModel->obtener('ultimo_respaldo', ''), true) ?: null;
        $db = \App\Config\Database::conectar();
        $tamano = (int) $db->query("SELECT SUM(data_length + index_length) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
        $tablas = (int) $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'")->fetchColumn();

        $this->render('configuracion/respaldo', [
            'ultimo'   => $ultimo,
            'dias'     => $ultimo ? (int) floor((time() - strtotime($ultimo['fecha'])) / 86400) : null,
            'tamanoKb' => (int) ceil($tamano / 1024),
            'tablas'   => $tablas,
            'fotosKb'  => (int) ceil(self::tamanoCarpeta(__DIR__ . '/../../public/uploads') / 1024),
        ], 'Respaldo de Datos');
    }

    /**
     * Genera el .zip (base de datos + fotos) y lo descarga. Por POST con token.
     */
    public function descargarRespaldo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('configuracion/respaldo');
        }
        if (!class_exists(\ZipArchive::class)) {
            $this->setFlash('danger', 'El servidor no tiene la extensión zip de PHP: no se puede armar el respaldo.');
            redirect('configuracion/respaldo');
        }

        @set_time_limit(300);
        $ruta = \App\Core\Respaldo::generar();
        $bytes = filesize($ruta);

        $this->configModel->guardar('ultimo_respaldo', json_encode([
            'fecha'   => date('Y-m-d H:i:s'),
            'usuario' => auth_user('nombre'),
            'kb'      => (int) ceil($bytes / 1024),
        ], JSON_UNESCAPED_UNICODE), 'sistema', 'Último respaldo descargado (Configuración → Respaldo)');
        $this->auditoriaModel->registrar('Configuración', 'Respaldo', 'Descargó un respaldo completo (' . number_format($bytes / 1024, 0) . ' KB)');

        $hotel = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', config_hotel('hotel_nombre', 'hotel')) ?: 'hotel')), '-');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="respaldo-' . ($hotel ?: 'hotel') . '-' . date('Y-m-d-Hi') . '.zip"');
        header('Content-Length: ' . $bytes);
        header('Cache-Control: no-store');
        readfile($ruta);
        unlink($ruta);
        exit;
    }

    private static function tamanoCarpeta(string $carpeta): int
    {
        if (!is_dir($carpeta)) {
            return 0;
        }
        $total = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($carpeta, \FilesystemIterator::SKIP_DOTS)) as $archivo) {
            $total += $archivo->getSize();
        }
        return $total;
    }
}
