<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Huesped;
use App\Models\Pago;
use App\Models\Estancia;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    public function index(): void
    {
        $habModel = new Habitacion();
        $reservaModel = new Reserva();
        $huespedModel = new Huesped();
        $pagoModel = new Pago();
        $estanciaModel = new Estancia();

        $conteoHabitaciones = $habModel->contarPorEstado();
        $reservasHoy = $reservaModel->obtenerReservasDeHoy();
        $ingresosHoy = $pagoModel->ingresosHoy();
        $ingresosMes = $pagoModel->ingresosMes();
        $totalHuespedes = $huespedModel->contarTotal();
        $estadiasActivas = $estanciaModel->listarActivas();
        $ultimasReservas = $reservaModel->listarTodos();
        $ultimasReservas = array_slice($ultimasReservas, 0, 6);
        $habitaciones = $habModel->listarTodos();

        $this->render('home/index', [
            'conteoHab'       => $conteoHabitaciones,
            'reservasHoy'     => $reservasHoy,
            'ingresosHoy'     => $ingresosHoy,
            'ingresosMes'     => $ingresosMes,
            'totalHuespedes'  => $totalHuespedes,
            'estadiasActivas' => $estadiasActivas,
            'ultimasReservas' => $ultimasReservas,
            'habitaciones'    => $habitaciones
        ], 'Panel de Control - Hotel Manager');
    }
}
