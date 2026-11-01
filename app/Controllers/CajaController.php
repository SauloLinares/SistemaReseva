<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Caja;
use App\Models\Auditoria;

class CajaController extends Controller
{
    private Caja $cajaModel;
    private Auditoria $auditoriaModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->cajaModel = new Caja();
        $this->auditoriaModel = new Auditoria();
    }

    /**
     * Pantalla principal de Caja:
     * Si no hay turno abierto -> Muestra pantalla de Apertura
     * Si hay turno abierto -> Muestra Panel de Control en Vivo del Turno
     */
    public function index(): void
    {
        $turnoActivo = $this->cajaModel->obtenerTurnoActivo();

        if (!$turnoActivo) {
            $this->render('caja/apertura', [
                'usuario' => auth_user()
            ], 'Apertura de Caja - Hotel Manager');
            return;
        }

        $resumen = $this->cajaModel->obtenerResumenTurno($turnoActivo['id']);

        $this->render('caja/index', [
            'turno'   => $turnoActivo,
            'resumen' => $resumen
        ], 'Control de Turno de Caja - Hotel Manager');
    }

    /**
     * Procesar la Apertura de Caja
     */
    public function abrir(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('caja');
        }

        $montoInicial = (float)($_POST['monto_inicial'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');
        $usuarioId = (int)(auth_user('id') ?: 1);

        if ($montoInicial < 0) {
            $this->setFlash('danger', 'El monto inicial no puede ser negativo.');
            redirect('caja');
        }

        $turnoId = $this->cajaModel->abrirTurno($usuarioId, $montoInicial, $observaciones);

        if ($turnoId) {
            $this->auditoriaModel->registrar('Caja', 'Apertura', "Apertura de turno de caja #{$turnoId} con fondo inicial de " . moneda() . " {$montoInicial}");
            $this->setFlash('success', "¡Caja y turno #{$turnoId} abiertos exitosamente!");
        } else {
            $this->setFlash('warning', 'Ya existe un turno de caja abierto en este momento.');
        }

        redirect('caja');
    }

    /**
     * Registrar Movimiento de Caja Chica (Ingreso o Egreso)
     */
    public function guardarMovimiento(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('caja');
        }

        $turnoActivo = $this->cajaModel->obtenerTurnoActivo();
        if (!$turnoActivo) {
            $this->setFlash('danger', 'No hay ningún turno de caja abierto para registrar movimientos.');
            redirect('caja');
        }

        $tipo = $_POST['tipo'] ?? 'egreso';
        $concepto = trim($_POST['concepto'] ?? '');
        $categoria = trim($_POST['categoria'] ?? 'General');
        $monto = (float)($_POST['monto'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? 'Efectivo';
        $comprobante = trim($_POST['comprobante'] ?? '');
        $usuarioId = (int)(auth_user('id') ?: 1);

        if (empty($concepto) || $monto <= 0) {
            $this->setFlash('danger', 'Debe ingresar un concepto claro y un monto válido mayor a cero.');
            redirect('caja');
        }

        $ok = $this->cajaModel->registrarMovimiento(
            $turnoActivo['id'],
            $usuarioId,
            $tipo,
            $concepto,
            $monto,
            $categoria,
            $comprobante,
            $metodoPago
        );

        if ($ok) {
            $tipoLabel = ($tipo === 'ingreso') ? 'Ingreso extra' : 'Egreso/Gasto';
            $this->auditoriaModel->registrar('Caja', 'Movimiento', "{$tipoLabel} de " . moneda() . " {$monto} por: {$concepto}");
            $this->setFlash('success', "{$tipoLabel} de " . moneda() . " " . number_format($monto, 2) . " registrado correctamente.");
        } else {
            $this->setFlash('danger', 'Ocurrió un error al registrar el movimiento.');
        }

        redirect('caja');
    }

    /**
     * Eliminar un movimiento de caja chica
     */
    public function eliminarMovimiento(string $id): void
    {
        $turnoActivo = $this->cajaModel->obtenerTurnoActivo();
        if (!$turnoActivo) {
            $this->setFlash('danger', 'No hay caja abierta activa.');
            redirect('caja');
        }

        $movId = (int)$id;
        $ok = $this->cajaModel->eliminarMovimiento($movId, $turnoActivo['id']);

        if ($ok) {
            $this->setFlash('warning', 'Movimiento eliminado correctamente.');
        } else {
            $this->setFlash('danger', 'No se pudo eliminar el movimiento.');
        }

        redirect('caja');
    }

    /**
     * Procesar Arqueo y Cierre de Caja
     */
    public function cerrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('caja');
        }

        $turnoActivo = $this->cajaModel->obtenerTurnoActivo();
        if (!$turnoActivo) {
            $this->setFlash('danger', 'No hay un turno de caja abierto para cerrar.');
            redirect('caja');
        }

        $montoReal = (float)($_POST['monto_real_efectivo'] ?? 0);
        $observaciones = trim($_POST['observaciones_cierre'] ?? '');
        $turnoId = (int)$turnoActivo['id'];

        $ok = $this->cajaModel->cerrarTurno($turnoId, $montoReal, $observaciones);

        if ($ok) {
            $this->auditoriaModel->registrar('Caja', 'Cierre', "Cierre y arqueo de turno #{$turnoId}. Conteo físico en efectivo: " . moneda() . " {$montoReal}");
            $this->setFlash('success', "¡Turno #{$turnoId} cerrado exitosamente! Puedes revisar el reporte de arqueo.");
            redirect('caja/cierre/' . $turnoId);
        } else {
            $this->setFlash('danger', 'Ocurrió un error al cerrar el turno de caja.');
            redirect('caja');
        }
    }

    /**
     * Ver Reporte / Ticket de Arqueo y Cierre de Caja
     */
    public function cierre(string $id): void
    {
        $turnoId = (int)$id;
        $resumen = $this->cajaModel->obtenerResumenTurno($turnoId);

        if (empty($resumen)) {
            $this->setFlash('danger', 'El turno de caja especificado no existe.');
            redirect('caja/historial');
        }

        $this->render('caja/cierre', [
            'turno'   => $resumen['turno'],
            'resumen' => $resumen
        ], 'Reporte de Cierre de Caja #' . $turnoId);
    }

    /**
     * Historial de todos los turnos de caja anteriores
     */
    public function historial(): void
    {
        $historial = $this->cajaModel->listarHistorial();

        $this->render('caja/historial', [
            'historial' => $historial
        ], 'Historial de Cierres de Caja - Hotel Manager');
    }
}
