<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cliente;

/**
 * Controlador de la página de inicio (Dashboard)
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $clienteModel = new Cliente();

        $totalClientes = $clienteModel->contarTotal();
        $clientesActivos = $clienteModel->contarActivos();
        $clientesInactivos = $totalClientes - $clientesActivos;

        $this->render('home/index', [
            'totalClientes'    => $totalClientes,
            'clientesActivos'  => $clientesActivos,
            'clientesInactivos'=> $clientesInactivos
        ], 'Panel de Control - Dashboard');
    }
}
