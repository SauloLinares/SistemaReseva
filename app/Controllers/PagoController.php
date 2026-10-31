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
            'cajaAbierta'         => (new \App\Models\Caja())->obtenerTurnoActivo() !== null,
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
        [$lineas, $erroresCobro, $notaEfectivo] = $this->leerCobro($reserva);
        $errores += $erroresCobro;
        if ($errores) {
            $this->volverConErrores('pagos/crear', $errores);
        }

        $_POST['huesped_id'] = $reserva['huesped_id'];
        $_POST['notas'] = trim(trim($_POST['notas'] ?? '') . ' ' . $notaEfectivo);
        $pagoId = $this->pagoModel->registrarCobro($_POST, $lineas);

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
        $reserva = $reservaId ? $this->reservaModel->obtenerPorId($reservaId) : null;
        if (!$reserva) {
            $this->json(['success' => false, 'mensaje' => 'Reserva no encontrada.']);
            return;
        }

        [$lineas, $errores, $notaEfectivo] = $this->leerCobro($reserva);
        if ($errores) {
            $this->json(['success' => false, 'mensaje' => reset($errores), 'errores' => $errores]);
            return;
        }

        $_POST['huesped_id'] = $reserva['huesped_id'];
        $_POST['notas'] = trim(trim($_POST['notas'] ?? '') . ' ' . $notaEfectivo);
        if (empty($_POST['comprobante_tipo'])) {
            $_POST['comprobante_tipo'] = 'Boleta';
        }
        if (empty($_POST['comprobante_numero'])) {
            $_POST['comprobante_numero'] = $this->pagoModel->generarNumeroComprobante($_POST['comprobante_tipo']);
        }

        $pagoId = $this->pagoModel->registrarCobro($_POST, $lineas);

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
     * Lee el cobro del formulario: una o varias líneas [método, monto] (partials/cobro_lineas)
     * o, en formularios antiguos, metodo_pago + monto. Valida cada línea y que el total no
     * supere el saldo. Devuelve [lineas, errores por campo, nota con el efectivo recibido].
     */
    private function leerCobro(?array $reserva): array
    {
        $entrada = $_POST['lineas'] ?? [['metodo' => $_POST['metodo_pago'] ?? '', 'monto' => $_POST['monto'] ?? '']];
        $lineas = [];
        $errores = [];

        foreach (is_array($entrada) ? array_slice($entrada, 0, 4) : [] as $linea) {
            $metodo = trim((string)($linea['metodo'] ?? ''));
            $monto = $linea['monto'] ?? '';
            if (!in_array($metodo, Pago::METODOS, true)) {
                $errores['lineas'] = 'Elige un método de pago válido en cada línea.';
            } elseif (!is_numeric($monto) || round((float)$monto, 2) <= 0) {
                $errores['lineas'] = 'Cada método debe llevar un monto mayor a cero.';
            } elseif (isset($lineas[$metodo])) {
                $errores['lineas'] = 'No repitas un método: suma ese monto en una sola línea.';
            } else {
                $lineas[$metodo] = ['metodo' => $metodo, 'monto' => round((float)$monto, 2)];
            }
        }
        if (!$lineas && !$errores) {
            $errores['lineas'] = 'Ingresa el monto a cobrar.';
        }
        // Formularios con un solo campo: el error va en ese campo
        if (isset($errores['lineas']) && !isset($_POST['lineas'])) {
            $errores = ['monto' => $errores['lineas']];
        }

        $total = round(array_sum(array_column($lineas, 'monto')), 2);
        if (!$errores && $reserva && $total > round((float)$reserva['saldo'], 2) + 0.009) {
            $errores[isset($_POST['lineas']) ? 'lineas' : 'monto'] = 'El cobro (' . dinero($total) . ') supera el saldo pendiente (' . dinero($reserva['saldo']) . ').';
        }

        // Efectivo recibido: el vuelto se calcula y queda en las notas del pago
        $nota = '';
        $efectivo = $lineas['Efectivo']['monto'] ?? 0;
        $recibido = $_POST['recibido'] ?? '';
        if (!$errores && $efectivo > 0 && $recibido !== '') {
            if (!is_numeric($recibido) || (float)$recibido < $efectivo) {
                $errores['recibido'] = 'El efectivo recibido no alcanza para la parte en efectivo (' . dinero($efectivo) . ').';
            } elseif ((float)$recibido > $efectivo) {
                $nota = 'Efectivo recibido ' . dinero((float)$recibido) . ', vuelto ' . dinero((float)$recibido - $efectivo) . '.';
            }
        }

        return [array_values($lineas), $errores, $nota];
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
            'lineas' => $this->pagoModel->lineasDeComprobante((int)$pago['reserva_id'], $pago['comprobante_tipo'], $pago['comprobante_numero']),
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
