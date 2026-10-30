<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Huesped;

class HuespedController extends Controller
{
    private Huesped $huespedModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->huespedModel = new Huesped();
    }

    /**
     * Listar huéspedes
     */
    public function index(): void
    {
        $busqueda = $_GET['q'] ?? null;
        $huespedes = $this->huespedModel->listarTodos($busqueda);

        $this->render('huespedes/index', [
            'huespedes' => $huespedes,
            'busqueda'  => $busqueda
        ], 'Listado de Huéspedes - Hotel Manager');
    }

    /**
     * Formulario de registro de huésped
     */
    public function crear(): void
    {
        $this->render('huespedes/crear', [], 'Registrar Nuevo Huésped');
    }

    /**
     * Guardar nuevo huésped
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('huespedes');
        }

        $numDoc = trim($_POST['numero_documento'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');

        if (empty($numDoc) || empty($nombres) || empty($apellidos)) {
            $this->setFlash('danger', 'El documento, nombres y apellidos son obligatorios.');
            redirect('huespedes/crear');
        }

        // Verificar si ya existe documento
        $existe = $this->huespedModel->buscarPorDocumento($numDoc);
        if ($existe) {
            $this->setFlash('danger', 'Ya existe un huésped registrado con el documento ' . htmlspecialchars($numDoc));
            redirect('huespedes/crear');
        }

        $id = $this->huespedModel->crear($_POST);

        if ($id) {
            $this->setFlash('success', '¡Huésped registrado correctamente!');
            redirect('huespedes');
        } else {
            $this->setFlash('danger', 'No se pudo guardar el huésped en la base de datos.');
            redirect('huespedes/crear');
        }
    }

    /**
     * Formulario para editar huésped
     */
    public function editar(?string $id = null): void
    {
        $id = (int)$id;
        $huesped = $this->huespedModel->obtenerPorId($id);

        if (!$huesped) {
            $this->setFlash('danger', 'Huésped no encontrado.');
            redirect('huespedes');
        }

        $this->render('huespedes/editar', [
            'huesped' => $huesped
        ], 'Editar Huésped - ' . $huesped['nombres']);
    }

    /**
     * Actualizar datos del huésped
     */
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('huespedes');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->huespedModel->actualizar($id, $_POST);

        if ($ok) {
            $this->setFlash('success', 'Datos del huésped actualizados con éxito.');
        } else {
            $this->setFlash('danger', 'Error al actualizar datos del huésped.');
        }

        redirect('huespedes');
    }

    /**
     * Buscar huésped por DNI o pasaporte (interfaz dedicada y búsqueda rápida)
     */
    public function buscar(): void
    {
        $doc = trim($_GET['documento'] ?? '');
        $resultado = null;

        if (!empty($doc)) {
            $resultado = $this->huespedModel->buscarPorDocumento($doc);
            if (!$resultado) {
                // Intentar búsqueda general
                $varios = $this->huespedModel->listarTodos($doc);
                if (!empty($varios)) {
                    $resultado = $varios[0];
                }
            }
        }

        $this->render('huespedes/buscar', [
            'documento' => $doc,
            'huesped'   => $resultado
        ], 'Búsqueda de Huéspedes por Documento');
    }

    /**
     * Endpoint API para autocompletar huésped por AJAX
     */
    public function apiBuscar(): void
    {
        $doc = trim($_GET['q'] ?? '');
        $resultado = $this->huespedModel->buscarPorDocumento($doc);
        $this->json($resultado ?: ['encontrado' => false]);
    }

    /**
     * Historial de estadías de un huésped
     */
    public function historial(?string $id = null): void
    {
        $id = (int)$id;
        if (!$id) {
            // Si no se pasó id, listar todos con su conteo
            $huespedes = $this->huespedModel->listarTodos();
            $this->render('huespedes/historial_general', [
                'huespedes' => $huespedes
            ], 'Historial General de Huéspedes');
            return;
        }

        $huesped = $this->huespedModel->obtenerPorId($id);
        if (!$huesped) {
            $this->setFlash('danger', 'Huésped no encontrado.');
            redirect('huespedes');
        }

        $historial = $this->huespedModel->obtenerHistorial($id);

        $this->render('huespedes/historial', [
            'huesped'   => $huesped,
            'historial' => $historial
        ], 'Historial de Estadías - ' . $huesped['nombres'] . ' ' . $huesped['apellidos']);
    }

    /**
     * Eliminar huésped
     */
    public function eliminar(?string $id = null): void
    {
        $id = (int)$id;
        $ok = $this->huespedModel->eliminar($id);

        if ($ok) {
            $this->setFlash('success', 'Huésped eliminado.');
        } else {
            $this->setFlash('danger', 'No se puede eliminar el huésped porque tiene reservas o pagos asociados.');
        }

        redirect('huespedes');
    }
}
