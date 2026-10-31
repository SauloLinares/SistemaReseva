<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Servicio;
use App\Models\Reserva;
use App\Models\Estancia;

class ServicioController extends Controller
{
    private Servicio $servicioModel;
    private Reserva $reservaModel;
    private Estancia $estanciaModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->servicioModel = new Servicio();
        $this->reservaModel = new Reserva();
        $this->estanciaModel = new Estancia();
    }

    /**
     * Gestionar servicios (Catálogo)
     */
    public function index(): void
    {
        $servicios = $this->servicioModel->listarTodos();
        $this->render('servicios/index', [
            'servicios' => $servicios
        ], 'Gestionar Servicios Adicionales');
    }

    /**
     * Guardar nuevo o editar servicio
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('servicios');
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if ($id) {
            $ok = $this->servicioModel->actualizar($id, $_POST);
            $msg = $ok ? 'Servicio actualizado.' : 'Error al actualizar servicio.';
        } else {
            $ok = $this->servicioModel->crear($_POST);
            $msg = $ok ? 'Servicio registrado correctamente.' : 'Error al registrar servicio.';
        }

        $this->setFlash($ok ? 'success' : 'danger', $msg);
        redirect('servicios');
    }

    /**
     * Eliminar servicio
     */
    public function eliminar(?string $id = null): void
    {
        $id = (int)$id;
        $ok = $this->servicioModel->eliminar($id);

        if ($ok) {
            $this->setFlash('success', 'Servicio eliminado.');
        } else {
            $this->setFlash('danger', 'No se puede eliminar porque tiene consumos registrados.');
        }

        redirect('servicios');
    }

    /**
     * Registrar consumo de servicio a una habitación o reserva
     */
    public function consumo(): void
    {
        $serviciosActivos = $this->servicioModel->listarActivos();
        $estadiasActivas = $this->estanciaModel->listarActivas();

        $reservaId = !empty($_GET['reserva_id']) ? (int)$_GET['reserva_id'] : null;

        $this->render('servicios/consumo', [
            'servicios'       => $serviciosActivos,
            'estadias'        => $estadiasActivas,
            'reservaIdFijada' => $reservaId
        ], 'Registrar Consumo de Servicio');
    }

    /**
     * Procesar guardado de consumo
     */
    public function guardarConsumo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('servicios/consumo');
        }

        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $servicioId = (int)($_POST['servicio_id'] ?? 0);

        if (!$reservaId || !$servicioId) {
            $this->setFlash('danger', 'Debe seleccionar una habitación/reserva y un servicio.');
            redirect('servicios/consumo');
        }

        $ok = $this->servicioModel->registrarConsumo($_POST);

        if ($ok) {
            $this->setFlash('success', 'Consumo registrado exitosamente en la cuenta del huésped.');
            redirect('servicios/porHuesped?reserva_id=' . $reservaId);
        } else {
            $this->setFlash('danger', 'Error al procesar el consumo.');
            redirect('servicios/consumo');
        }
    }

    /**
     * Endpoint API para registrar consumo vía AJAX (Modal Rápido)
     */
    public function apiGuardarConsumo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $servicioId = (int)($_POST['servicio_id'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 1);

        if (!$reservaId || !$servicioId || $cantidad < 1) {
            $this->json(['success' => false, 'mensaje' => 'Debe seleccionar un servicio válido e ingresar una cantidad mayor a cero.']);
            return;
        }

        $ok = $this->servicioModel->registrarConsumo($_POST);

        if ($ok) {
            $this->json([
                'success' => true,
                'mensaje' => '¡Consumo registrado exitosamente en la cuenta de la habitación!'
            ]);
        } else {
            $this->json(['success' => false, 'mensaje' => 'Error al registrar el consumo.']);
        }
    }

    /**
     * Interruptor de estado de la tabla (fetch): {success, mensaje}
     */
    public function cambiarEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'mensaje' => 'Método no permitido.'], 405);
        }
        $id = (int)($_POST['id'] ?? 0);
        $activo = ($_POST['estado'] ?? '') === '1';
        $ok = $id > 0 && $this->servicioModel->cambiarEstado($id, $activo);
        $this->json([
            'success' => $ok,
            'mensaje' => $ok ? ('Servicio' . ($activo ? ' disponible.' : ' deshabilitado: ya no se ofrece en reservas ni consumos.')) : 'No se pudo cambiar el estado.',
        ]);
    }

    /**
     * Servicios por huésped (Listado de consumos)
     */
    public function porHuesped(): void
    {
        $reservaId = !empty($_GET['reserva_id']) ? (int)$_GET['reserva_id'] : null;
        $consumos = $this->servicioModel->listarConsumos($reservaId);

        $this->render('servicios/por_huesped', [
            'consumos'  => $consumos,
            'reservaId' => $reservaId
        ], 'Consumos de Servicios por Huésped');
    }
}
