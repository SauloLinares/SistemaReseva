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

        foreach ($campos as $c) {
            if (isset($_POST[$c])) {
                $this->configModel->guardar($c, trim($_POST[$c]), 'general');
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
     * Métodos de pago aceptados
     */
    public function metodosPago(): void
    {
        $metodos = [
            ['nombre' => 'Efectivo', 'tipo' => 'Moneda Local / Dólares', 'comision' => '0%', 'estado' => 'Habilitado'],
            ['nombre' => 'Tarjeta de Crédito', 'tipo' => 'Visa / Mastercard / Amex', 'comision' => '3.5%', 'estado' => 'Habilitado'],
            ['nombre' => 'Tarjeta de Débito', 'tipo' => 'Redes bancarias', 'comision' => '2.0%', 'estado' => 'Habilitado'],
            ['nombre' => 'Transferencia Bancaria', 'tipo' => 'BCP / BBVA / Interbank', 'comision' => '0%', 'estado' => 'Habilitado'],
            ['nombre' => 'Yape / Plin', 'tipo' => 'Billeteras digitales QR', 'comision' => '0%', 'estado' => 'Habilitado']
        ];

        $this->render('configuracion/metodos_pago', [
            'metodos' => $metodos
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
}
