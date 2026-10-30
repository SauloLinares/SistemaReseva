<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reporte;

class ReporteController extends Controller
{
    private Reporte $reporteModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->reporteModel = new Reporte();
    }

    public function index(): void
    {
        $this->reservas();
    }

    /**
     * Reporte de reservas
     */
    public function reservas(): void
    {
        $desde = $_GET['desde'] ?? null;
        $hasta = $_GET['hasta'] ?? null;
        $estado = $_GET['estado'] ?? null;

        $datos = $this->reporteModel->reporteReservas($desde, $hasta, $estado);

        $this->render('reportes/reservas', [
            'reservas' => $datos,
            'desde'    => $desde,
            'hasta'    => $hasta,
            'estado'   => $estado
        ], 'Reporte de Reservas - Hotel Manager');
    }

    /**
     * Reporte de ocupación
     */
    public function ocupacion(): void
    {
        $mes = $_GET['mes'] ?? date('Y-m');
        $datos = $this->reporteModel->reporteOcupacion($mes);
        $populares = $this->reporteModel->habitacionesMasSolicitadas();

        $this->render('reportes/ocupacion', [
            'ocupacion' => $datos,
            'populares' => $populares,
            'mes'       => $mes
        ], 'Reporte de Ocupación Hotelera');
    }

    /**
     * Reporte de ingresos
     */
    public function ingresos(): void
    {
        $desde = $_GET['desde'] ?? date('Y-m-01');
        $hasta = $_GET['hasta'] ?? date('Y-m-t');

        $datos = $this->reporteModel->reporteIngresos($desde, $hasta);

        $this->render('reportes/ingresos', [
            'pagos'         => $datos['pagos'],
            'porMetodo'     => $datos['porMetodo'],
            'totalIngresos' => $datos['totalIngresos'],
            'desde'         => $desde,
            'hasta'         => $hasta
        ], 'Reporte de Ingresos y Cobros');
    }

    /**
     * Reporte de huéspedes
     */
    public function huespedes(): void
    {
        $datos = $this->reporteModel->reporteHuespedes();

        $this->render('reportes/huespedes', [
            'huespedes' => $datos
        ], 'Reporte de Huéspedes Frecuentes y Fidelización');
    }

    /**
     * Reporte de cancelaciones
     */
    public function cancelaciones(): void
    {
        $desde = $_GET['desde'] ?? null;
        $hasta = $_GET['hasta'] ?? null;

        $datos = $this->reporteModel->reporteCancelaciones($desde, $hasta);

        $this->render('reportes/cancelaciones', [
            'cancelaciones' => $datos,
            'desde'         => $desde,
            'hasta'         => $hasta
        ], 'Reporte de Reservas Canceladas');
    }
}
