<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use App\Models\Huesped;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\Configuracion;

class ReservaController extends Controller
{
    private Reserva $reservaModel;
    private Habitacion $habModel;
    private TipoHabitacion $tipoModel;
    private Huesped $huespedModel;
    private Servicio $servicioModel;
    private Configuracion $configModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->reservaModel = new Reserva();
        $this->habModel = new Habitacion();
        $this->tipoModel = new TipoHabitacion();
        $this->huespedModel = new Huesped();
        $this->servicioModel = new Servicio();
        $this->configModel = new Configuracion();
    }

    /**
     * Listar reservas
     */
    public function index(): void
    {
        $estado = $_GET['estado'] ?? null;
        $desde = $_GET['desde'] ?? null;
        $hasta = $_GET['hasta'] ?? null;

        $reservas = $this->reservaModel->listarTodos($estado, $desde, $hasta);

        $this->render('reservas/index', [
            'reservas' => $reservas,
            'filtroEstado' => $estado,
            'filtroDesde'  => $desde,
            'filtroHasta'  => $hasta
        ], 'Listado de Reservas - Hotel Manager');
    }

    /**
     * Formulario para registrar una reserva
     */
    public function crear(): void
    {
        $huespedes = $this->huespedModel->listarTodos();
        $tipos = $this->tipoModel->listarActivos();
        $habitaciones = $this->habModel->listarTodos();
        $servicios = $this->servicioModel->listarActivos();

        $this->render('reservas/crear', [
            'huespedes'    => $huespedes,
            'tipos'        => $tipos,
            'habitaciones' => $habitaciones,
            'servicios'    => $servicios
        ], 'Registrar Nueva Reserva - Hotel Manager');
    }

    /**
     * API JSON: Comprueba disponibilidad de habitaciones en tiempo real
     */
    public function apiDisponibilidad(): void
    {
        header('Content-Type: application/json');
        $entrada = $_GET['entrada'] ?? date('Y-m-d');
        $salida = $_GET['salida'] ?? date('Y-m-d', strtotime('+1 day'));

        if (empty($entrada) || empty($salida) || $salida <= $entrada) {
            echo json_encode(['success' => false, 'error' => 'Fechas no válidas']);
            exit;
        }

        $disponibles = $this->reservaModel->obtenerHabitacionesDisponibles($entrada, $salida);
        $disponiblesIds = array_map('intval', array_column($disponibles, 'id'));

        echo json_encode([
            'success'         => true,
            'disponibles_ids' => $disponiblesIds,
            'total'           => count($disponiblesIds)
        ]);
        exit;
    }

    /**
     * Guardar reserva
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('reservas');
        }

        $huespedId = (int)($_POST['huesped_id'] ?? 0);
        $fechaEntrada = $_POST['fecha_entrada'] ?? '';
        $fechaSalida = $_POST['fecha_salida'] ?? '';
        $habitacionesIds = $_POST['habitaciones'] ?? [];

        if (!$huespedId || empty($fechaEntrada) || empty($fechaSalida) || empty($habitacionesIds)) {
            $this->setFlash('danger', 'Debe seleccionar un huésped, rango de fechas y al menos una habitación.');
            redirect('reservas/crear');
        }

        if (strtotime($fechaSalida) <= strtotime($fechaEntrada)) {
            $this->setFlash('danger', 'La fecha de salida debe ser posterior a la fecha de entrada.');
            redirect('reservas/crear');
        }

        $id = $this->reservaModel->crear($_POST, $habitacionesIds);

        if ($id) {
            $this->setFlash('success', '¡Reserva confirmada exitosamente! Aquí tienes el voucher oficial de confirmación.');
            redirect('reservas/voucher/' . $id . '?nuevo=1');
        } else {
            $this->setFlash('danger', 'Error al registrar: Una o más habitaciones seleccionadas ya están ocupadas o reservadas en ese rango de fechas.');
            redirect('reservas/crear');
        }
    }

    /**
     * Voucher oficial de reserva (para imprimir y enviar por WhatsApp)
     */
    public function voucher(?string $id = null): void
    {
        $id = (int)$id;
        $reserva = $this->reservaModel->obtenerPorId($id);

        if (!$reserva) {
            $this->setFlash('danger', 'La reserva no fue encontrada.');
            redirect('reservas');
        }

        $hotel = $this->configModel->obtenerDatosHotel();

        $this->render('reservas/voucher', [
            'reserva' => $reserva,
            'hotel'   => $hotel,
            'esNuevo' => isset($_GET['nuevo'])
        ], 'Voucher de Reserva ' . $reserva['codigo_reserva']);
    }

    /**
     * Modificar reserva
     */
    public function editar(?string $id = null): void
    {
        $id = (int)$id;
        $reserva = $this->reservaModel->obtenerPorId($id);

        if (!$reserva) {
            $this->setFlash('danger', 'La reserva no fue encontrada.');
            redirect('reservas');
        }

        $huespedes = $this->huespedModel->listarTodos();
        $tipos = $this->tipoModel->listarActivos();
        $habitaciones = $this->habModel->listarTodos();

        $this->render('reservas/editar', [
            'reserva'      => $reserva,
            'huespedes'    => $huespedes,
            'tipos'        => $tipos,
            'habitaciones' => $habitaciones
        ], 'Modificar Reserva ' . $reserva['codigo_reserva']);
    }

    /**
     * Procesar actualización
     */
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('reservas');
        }

        $id = (int)($_POST['id'] ?? 0);
        $habitacionesIds = $_POST['habitaciones'] ?? [];

        $ok = $this->reservaModel->actualizar($id, $_POST, $habitacionesIds);

        if ($ok) {
            $this->setFlash('success', 'Reserva actualizada correctamente.');
        } else {
            $this->setFlash('danger', 'No se pudo actualizar: Conflicto de disponibilidad de habitaciones en las nuevas fechas.');
        }

        redirect('reservas/editar/' . $id);
    }

    /**
     * Cancelar reserva
     */
    public function cancelar(?string $id = null): void
    {
        $id = (int)($id ?: ($_POST['id'] ?? 0));
        $motivo = $_POST['motivo_cancelacion'] ?? 'Cancelado a solicitud del cliente';

        $ok = $this->reservaModel->cancelar($id, $motivo);

        if ($ok) {
            $this->setFlash('warning', 'La reserva ha sido cancelada exitosamente.');
        } else {
            $this->setFlash('danger', 'No se pudo cancelar la reserva.');
        }

        redirect('reservas');
    }

    /**
     * Consultar disponibilidad (verifica superposición de fechas)
     */
    public function disponibilidad(): void
    {
        $entrada = $_GET['entrada'] ?? date('Y-m-d');
        $salida = $_GET['salida'] ?? date('Y-m-d', strtotime('+1 day'));
        $tipoId = !empty($_GET['tipo_id']) ? (int)$_GET['tipo_id'] : null;

        $habitacionesDisponibles = [];
        if (!empty($entrada) && !empty($salida)) {
            $habitacionesDisponibles = $this->reservaModel->obtenerHabitacionesDisponibles($entrada, $salida, $tipoId);
        }

        $tipos = $this->tipoModel->listarActivos();
        $todasHabitaciones = $this->habModel->listarTodos();

        $this->render('reservas/disponibilidad', [
            'entrada'                 => $entrada,
            'salida'                  => $salida,
            'tipoId'                  => $tipoId,
            'tipos'                   => $tipos,
            'habitacionesDisponibles' => $habitacionesDisponibles,
            'totalHabitaciones'       => count($todasHabitaciones)
        ], 'Consultar Disponibilidad de Habitaciones');
    }

    /**
     * Vista de Calendario de Reservas
     */
    public function calendario(): void
    {
        $eventos = $this->reservaModel->obtenerEventosCalendario();
        $this->render('reservas/calendario', [
            'eventos' => $eventos
        ], 'Calendario de Reservas - Hotel Manager');
    }

    /**
     * Feed JSON para FullCalendar
     */
    public function eventosJson(): void
    {
        $eventos = $this->reservaModel->obtenerEventosCalendario();
        $this->json($eventos);
    }
}
