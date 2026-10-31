<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Estancia;
use App\Models\Reserva;
use App\Models\Habitacion;

class RecepcionController extends Controller
{
    private Estancia $estanciaModel;
    private Reserva $reservaModel;
    private Habitacion $habModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->estanciaModel = new Estancia();
        $this->reservaModel = new Reserva();
        $this->habModel = new Habitacion();
    }

    /**
     * Vista principal de recepción (Control de Estadías Activas)
     */
    public function index(): void
    {
        $this->estadias();
    }

    /**
     * Control de estadías activas y finalizadas
     */
    public function estadias(): void
    {
        $activas = $this->estanciaModel->listarActivas();
        $historial = $this->estanciaModel->listarHistorial();
        $servicios = (new \App\Models\Servicio())->listarActivos();

        $this->render('recepcion/estadias', [
            'activas'   => $activas,
            'historial' => $historial,
            'servicios' => $servicios
        ], 'Control de Estadías - Recepción');
    }

    /**
     * Módulo de Check-in (Registrar ingreso)
     */
    public function checkin(): void
    {
        // Reservas que están listas para check-in (confirmadas o pendientes)
        $reservasPendientes = $this->reservaModel->listarTodos('confirmada');
        $reservasPendientesPago = $this->reservaModel->listarTodos('pendiente');
        $disponiblesParaCheckin = array_merge($reservasPendientes, $reservasPendientesPago);

        $reservaSeleccionada = null;
        if (!empty($_GET['reserva_id'])) {
            $reservaSeleccionada = $this->reservaModel->obtenerPorId((int)$_GET['reserva_id']);
        }

        $this->render('recepcion/checkin', [
            'reservas'            => $disponiblesParaCheckin,
            'reservaSeleccionada' => $reservaSeleccionada
        ], 'Procesar Check-in de Huéspedes');
    }

    /**
     * Ejecutar registro de Check-in
     */
    public function procesarCheckin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('recepcion/checkin');
        }

        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $obs = trim($_POST['observaciones'] ?? '');

        if (!$reservaId) {
            $this->setFlash('danger', 'Debe seleccionar una reserva válida.');
            redirect('recepcion/checkin');
        }

        $ok = $this->estanciaModel->registrarCheckin($reservaId, null, $obs);

        if ($ok) {
            $this->setFlash('success', '¡Check-in realizado con éxito! La habitación se encuentra ahora en estado OCUPADA.');
            redirect('recepcion/estadias');
        } else {
            $this->setFlash('danger', 'Error al procesar Check-in. Verifique los datos de la reserva.');
            redirect('recepcion/checkin');
        }
    }

    /**
     * Módulo de Check-out (Registrar salida)
     */
    public function checkout(): void
    {
        $activas = $this->estanciaModel->listarActivas();

        $estanciaSeleccionada = null;
        if (!empty($_GET['id'])) {
            $estanciaSeleccionada = $this->estanciaModel->obtenerPorId((int)$_GET['id']);
        }

        $this->render('recepcion/checkout', [
            'activas'              => $activas,
            'estanciaSeleccionada' => $estanciaSeleccionada,
            'cajaAbierta'          => (new \App\Models\Caja())->obtenerTurnoActivo() !== null,
        ], 'Procesar Check-out de Huéspedes');
    }

    /**
     * Ejecutar Check-out
     */
    public function procesarCheckout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('recepcion/checkout');
        }

        $estanciaId = (int)($_POST['estancia_id'] ?? 0);
        $obs = trim($_POST['observaciones'] ?? '');

        if (!$estanciaId) {
            $this->setFlash('danger', 'Debe seleccionar una estadía activa.');
            redirect('recepcion/checkout');
        }

        $estancia = $this->estanciaModel->obtenerPorId($estanciaId);
        if ($estancia && (float)$estancia['reserva_saldo'] > 0 && empty($_POST['confirmar_saldo'])) {
            $this->setFlash('warning', 'La reserva aún tiene un saldo pendiente de ' . dinero($estancia['reserva_saldo']) . '. Por favor registre el pago o marque la confirmación.');
            redirect('recepcion/checkout?id=' . $estanciaId);
        }

        $ok = $this->estanciaModel->registrarCheckout($estanciaId, $obs);

        if ($ok) {
            $this->setFlash('success', '¡Check-out completado exitosamente! La habitación ha pasado a estado LIMPIEZA.');
            redirect('recepcion/estadias');
        } else {
            $this->setFlash('danger', 'Error al procesar Check-out.');
            redirect('recepcion/checkout');
        }
    }

    /**
     * Asignación / Cambio de habitación durante la estadía
     */
    public function asignacion(): void
    {
        $activas = $this->estanciaModel->listarActivas();
        $disponibles = $this->habModel->listarTodos('disponible');

        $this->render('recepcion/asignacion', [
            'activas'     => $activas,
            'disponibles' => $disponibles
        ], 'Asignación y Cambio de Habitación');
    }

    /**
     * Procesar cambio de habitación
     */
    public function procesarCambio(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('recepcion/asignacion');
        }

        $estanciaId = (int)($_POST['estancia_id'] ?? 0);
        $nuevaHabId = (int)($_POST['nueva_habitacion_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Solicitud del huésped');

        if (!$estanciaId || !$nuevaHabId) {
            $this->setFlash('danger', 'Debe seleccionar una estadía activa y una nueva habitación disponible.');
            redirect('recepcion/asignacion');
        }

        $ok = $this->estanciaModel->cambiarHabitacion($estanciaId, $nuevaHabId, $motivo);

        if ($ok) {
            $this->setFlash('success', '¡Habitación reasignada correctamente! La habitación anterior ha pasado a limpieza.');
        } else {
            $this->setFlash('danger', 'No se pudo completar el cambio de habitación.');
        }

        redirect('recepcion/estadias');
    }
}
