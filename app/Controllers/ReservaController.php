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

        // Fechas iniciales desde la URL (Disponibilidad, Planificación); si no son válidas, hoy y mañana
        $hoy = date('Y-m-d');
        $entrada = $_GET['entrada'] ?? '';
        $salida = $_GET['salida'] ?? '';
        $esFecha = fn($f) => (bool) \DateTime::createFromFormat('Y-m-d', $f);
        if (!$esFecha($entrada) || $entrada < $hoy) {
            $entrada = $hoy;
        }
        if (!$esFecha($salida) || $salida <= $entrada) {
            $salida = date('Y-m-d', strtotime($entrada . ' +1 day'));
        }

        $this->render('reservas/crear', [
            'huespedes'      => $huespedes,
            'tipos'          => $tipos,
            'habitaciones'   => $habitaciones,
            'servicios'      => $servicios,
            'entradaInicial' => $entrada,
            'salidaInicial'  => $salida,
        ], 'Registrar Nueva Reserva - Hotel Manager');
    }

    /**
     * Planificación: habitaciones (filas) × días (columnas) con las estadías como barras
     */
    public function planificacion(): void
    {
        $diasPermitidos = [7, 14, 30];
        $diasPedidos = (int)($_GET['dias'] ?? 14);
        $dias = in_array($diasPedidos, $diasPermitidos, true) ? $diasPedidos : 14;

        $desde = $_GET['desde'] ?? '';
        if (!\DateTime::createFromFormat('Y-m-d', $desde)) {
            // Por defecto empieza ayer, para ver también quién sale hoy
            $desde = date('Y-m-d', strtotime('-1 day'));
        }
        $hasta = date('Y-m-d', strtotime($desde . " +{$dias} days"));

        $this->render('reservas/planificacion', [
            'desde'        => $desde,
            'hasta'        => $hasta,
            'dias'         => $dias,
            'habitaciones' => $this->habModel->listarTodos(),
            'ocupacion'    => $this->reservaModel->obtenerOcupacionPorHabitacion($desde, $hasta),
        ], 'Planificación por Habitación - Hotel Manager');
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

        $habitacionesIds = $_POST['habitaciones'] ?? [];

        $errores = $this->validarReserva($_POST, true);
        if ($errores) {
            $this->volverConErrores('reservas/crear', $errores);
        }

        $id = $this->reservaModel->crear($_POST, $habitacionesIds);

        if ($id) {
            $this->setFlash('success', '¡Reserva confirmada exitosamente! Aquí tienes el voucher oficial de confirmación.');
            redirect('reservas/voucher/' . $id . '?nuevo=1');
        }
        // El modelo rechaza la reserva si alguna habitación ya está ocupada en esas fechas
        $this->volverConErrores('reservas/crear', [
            'habitaciones' => 'Una o más habitaciones elegidas ya están ocupadas o reservadas en esas fechas. Elige otras o cambia las fechas.',
        ], 'No se pudo registrar la reserva.');
    }

    /**
     * Reglas del formulario de reserva. Devuelve ['campo' => 'mensaje'] (vacío si todo está bien).
     *
     * @param bool $exigirHabitaciones En el POS de creación se eligen habitaciones; al editar no
     */
    private function validarReserva(array $datos, bool $exigirHabitaciones): array
    {
        $errores = [];
        $entrada = $datos['fecha_entrada'] ?? '';
        $salida = $datos['fecha_salida'] ?? '';
        $esFecha = fn($f) => (bool) \DateTime::createFromFormat('Y-m-d', $f);

        $huespedId = (int)($datos['huesped_id'] ?? 0);
        if (!$huespedId || !$this->huespedModel->obtenerPorId($huespedId)) {
            $errores['huesped_id'] = 'Busca y elige al huésped titular (o regístralo con "+ Nuevo Huésped").';
        }
        if (!$esFecha($entrada)) {
            $errores['fecha_entrada'] = 'Elige la fecha de check-in.';
        }
        if (!$esFecha($salida)) {
            $errores['fecha_salida'] = 'Elige la fecha de check-out.';
        } elseif ($esFecha($entrada) && $salida <= $entrada) {
            $errores['fecha_salida'] = 'El check-out debe ser posterior al check-in.';
        }
        if ((int)($datos['adultos'] ?? 0) < 1) {
            $errores['adultos'] = 'Debe haber al menos 1 adulto.';
        }
        if ((int)($datos['ninos'] ?? 0) < 0) {
            $errores['ninos'] = 'La cantidad de niños no puede ser negativa.';
        }
        if (isset($datos['anticipo']) && $datos['anticipo'] !== '' && (!is_numeric($datos['anticipo']) || (float)$datos['anticipo'] < 0)) {
            $errores['anticipo'] = 'El adelanto debe ser un monto de 0 o más.';
        }
        if ($exigirHabitaciones && empty($datos['habitaciones'])) {
            $errores['habitaciones'] = 'Elige al menos una habitación haciendo clic sobre su tarjeta.';
        }

        return $errores;
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

        $errores = $this->validarReserva($_POST, false);
        if ($errores) {
            $this->volverConErrores('reservas/editar/' . $id, $errores);
        }

        if ($this->reservaModel->actualizar($id, $_POST, $habitacionesIds)) {
            $this->setFlash('success', 'Reserva actualizada correctamente.');
            redirect('reservas/editar/' . $id);
        }
        $this->volverConErrores('reservas/editar/' . $id, [
            'fecha_entrada' => 'Las habitaciones de esta reserva no están libres en las nuevas fechas.',
        ], 'No se pudo actualizar la reserva.');
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
        // Solo fechas AAAA-MM-DD válidas: estos valores se vuelven a imprimir en la página
        $esFecha = fn($f) => is_string($f) && (bool) \DateTime::createFromFormat('Y-m-d', $f);
        $entrada = $esFecha($_GET['entrada'] ?? null) ? $_GET['entrada'] : date('Y-m-d');
        $salida = $esFecha($_GET['salida'] ?? null) && $_GET['salida'] > $entrada
            ? $_GET['salida']
            : date('Y-m-d', strtotime($entrada . ' +1 day'));
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
