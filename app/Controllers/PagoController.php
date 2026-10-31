<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Huesped;
use App\Models\Configuracion;

class PagoController extends Controller
{
    private Pago $pagoModel;
    private Reserva $reservaModel;
    private Huesped $huespedModel;
    private Configuracion $configModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->pagoModel = new Pago();
        $this->reservaModel = new Reserva();
        $this->huespedModel = new Huesped();
        $this->configModel = new Configuracion();
    }

    /**
     * Historial de pagos (todos los cobros y movimientos)
     */
    public function index(): void
    {
        $pagos = $this->pagoModel->listarTodos();
        $ingresosHoy = $this->pagoModel->ingresosHoy();
        $ingresosMes = $this->pagoModel->ingresosMes();

        $this->render('pagos/index', [
            'pagos'       => $pagos,
            'ingresosHoy' => $ingresosHoy,
            'ingresosMes' => $ingresosMes
        ], 'Historial de Pagos y Caja - Hotel Manager');
    }

    /**
     * Registrar nuevo pago o anticipo
     */
    public function crear(): void
    {
        $reservaId = !empty($_GET['reserva_id']) ? (int)$_GET['reserva_id'] : null;
        $reservaSeleccionada = null;

        if ($reservaId) {
            $reservaSeleccionada = $this->reservaModel->obtenerPorId($reservaId);
        }

        $saldosPendientes = $this->pagoModel->obtenerSaldosPendientes();

        $this->render('pagos/crear', [
            'saldosPendientes'    => $saldosPendientes,
            'reservaSeleccionada' => $reservaSeleccionada,
            'numBoleta'           => $this->pagoModel->generarNumeroComprobante('Boleta'),
            'numFactura'          => $this->pagoModel->generarNumeroComprobante('Factura'),
            'numRecibo'           => $this->pagoModel->generarNumeroComprobante('Recibo de Caja')
        ], 'Registrar Pago / Cobro');
    }

    /**
     * Guardar pago
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('pagos');
        }

        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $reserva = $reservaId ? $this->reservaModel->obtenerPorId($reservaId) : null;

        $errores = [];
        if (!$reserva) {
            $errores['reserva_id'] = $reservaId ? 'La reserva elegida ya no existe.' : 'Elige la reserva a la que corresponde el pago.';
        }
        if (!is_numeric($_POST['monto'] ?? '') || (float)$_POST['monto'] <= 0) {
            $errores['monto'] = 'Ingresa un monto mayor a cero.';
        }
        if (trim($_POST['metodo_pago'] ?? '') === '') {
            $errores['metodo_pago'] = 'Elige el método de pago.';
        }
        if ($errores) {
            $this->volverConErrores('pagos/crear', $errores);
        }

        $_POST['huesped_id'] = $reserva['huesped_id'];
        $pagoId = $this->pagoModel->registrarPago($_POST);

        if ($pagoId) {
            $this->setFlash('success', '¡Pago registrado exitosamente!');
            redirect('pagos/comprobante/' . $pagoId);
        }
        $this->volverConErrores('pagos/crear', [], 'Error al procesar el pago. Inténtalo de nuevo.');
    }

    /**
     * Endpoint API para registrar pago vía AJAX (Modal Cobro Rápido)
     */
    public function apiGuardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $monto = (float)($_POST['monto'] ?? 0);

        if (!$reservaId || $monto <= 0) {
            $this->json(['success' => false, 'mensaje' => 'Debe ingresar un monto válido mayor a cero.']);
            return;
        }

        $reserva = $this->reservaModel->obtenerPorId($reservaId);
        if (!$reserva) {
            $this->json(['success' => false, 'mensaje' => 'Reserva no encontrada.']);
            return;
        }

        $_POST['huesped_id'] = $reserva['huesped_id'];
        if (empty($_POST['comprobante_tipo'])) {
            $_POST['comprobante_tipo'] = 'Boleta';
        }
        if (empty($_POST['comprobante_numero'])) {
            $_POST['comprobante_numero'] = $this->pagoModel->generarNumeroComprobante($_POST['comprobante_tipo']);
        }

        $pagoId = $this->pagoModel->registrarPago($_POST);

        if ($pagoId) {
            $reservaActualizada = $this->reservaModel->obtenerPorId($reservaId);
            $this->json([
                'success' => true,
                'mensaje' => '¡Pago registrado exitosamente!',
                'pago_id' => $pagoId,
                'nuevo_saldo' => (float)$reservaActualizada['saldo'],
                'comprobante_numero' => $_POST['comprobante_numero'],
                'url_comprobante' => url('pagos/comprobante/' . $pagoId)
            ]);
        } else {
            $this->json(['success' => false, 'mensaje' => 'Error al procesar el pago en la base de datos.']);
        }
    }

    /**
     * Listar anticipos exclusivamente
     */
    public function anticipos(): void
    {
        $anticipos = $this->pagoModel->obtenerAnticipos();
        $this->render('pagos/anticipos', [
            'anticipos' => $anticipos
        ], 'Anticipos y Adelantos de Reservas');
    }

    /**
     * Listar saldos pendientes de cobro
     */
    public function pendientes(): void
    {
        $pendientes = $this->pagoModel->obtenerSaldosPendientes();
        $this->render('pagos/pendientes', [
            'pendientes' => $pendientes
        ], 'Saldos Pendientes de Cobro');
    }

    /**
     * Listado de Comprobantes emitidos
     */
    public function comprobantes(): void
    {
        $pagos = $this->pagoModel->listarTodos();
        $this->render('pagos/comprobantes', [
            'pagos' => $pagos
        ], 'Comprobantes de Pago Emitidos');
    }

    /**
     * Ver e imprimir comprobante formal (Boleta / Factura / Recibo)
     */
    public function comprobante(?string $id = null): void
    {
        $id = (int)$id;
        $pago = $this->pagoModel->obtenerPorId($id);

        if (!$pago) {
            $this->setFlash('danger', 'Comprobante no encontrado.');
            redirect('pagos');
        }

        $hotelConfig = $this->configModel->obtenerTodos();

        $this->render('pagos/comprobante_detalle', [
            'pago'   => $pago,
            'config' => $hotelConfig
        ], 'Comprobante ' . $pago['comprobante_tipo'] . ' ' . $pago['comprobante_numero']);
    }

    /**
     * Registrar devolución / reembolso
     */
    public function devolucion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('pagos');
        }

        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $monto = (float)($_POST['monto'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Devolución acordada');
        $metodo = $_POST['metodo_pago'] ?? 'Efectivo';

        $ok = $this->pagoModel->registrarDevolucion($reservaId, $monto, $motivo, $metodo);

        if ($ok) {
            $this->setFlash('warning', 'Devolución registrada correctamente.');
        } else {
            $this->setFlash('danger', 'Error al procesar devolución.');
        }

        redirect('pagos');
    }
}
