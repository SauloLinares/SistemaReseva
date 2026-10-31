<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use App\Models\Mantenimiento;

class HabitacionController extends Controller
{
    private Habitacion $habModel;
    private TipoHabitacion $tipoModel;
    private Mantenimiento $mantModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->habModel = new Habitacion();
        $this->tipoModel = new TipoHabitacion();
        $this->mantModel = new Mantenimiento();
    }

    /**
     * Listar habitaciones
     */
    public function index(): void
    {
        $estado = $_GET['estado'] ?? null;
        $tipoId = !empty($_GET['tipo_id']) ? (int)$_GET['tipo_id'] : null;

        $habitaciones = $this->habModel->listarTodos($estado, $tipoId);
        $tipos = $this->tipoModel->listarTodos();
        $conteo = $this->habModel->contarPorEstado();

        $this->render('habitaciones/index', [
            'habitaciones' => $habitaciones,
            'tipos'        => $tipos,
            'conteo'       => $conteo,
            'filtroEstado' => $estado,
            'filtroTipo'   => $tipoId
        ], 'Listado de Habitaciones - Hotel Manager');
    }

    /**
     * Formulario de registro de habitación
     */
    public function crear(): void
    {
        $tipos = $this->tipoModel->listarActivos();
        $this->render('habitaciones/crear', [
            'tipos' => $tipos
        ], 'Registrar Habitación');
    }

    /**
     * Guardar nueva habitación
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('habitaciones');
        }

        $numero = trim($_POST['numero'] ?? '');
        if (empty($numero)) {
            $this->setFlash('danger', 'El número de habitación es obligatorio.');
            redirect('habitaciones/crear');
        }

        // Verificar duplicados
        $existe = $this->habModel->obtenerPorNumero($numero);
        if ($existe) {
            $this->setFlash('danger', "La habitación {$numero} ya se encuentra registrada.");
            redirect('habitaciones/crear');
        }

        // Procesar subida de fotografía de habitación
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $permitidas)) {
                $dirUploads = __DIR__ . '/../../../public/uploads/habitaciones/';
                if (!is_dir($dirUploads)) {
                    mkdir($dirUploads, 0777, true);
                }
                $nombreArchivo = 'hab_' . $numero . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $dirUploads . $nombreArchivo)) {
                    $_POST['foto'] = $nombreArchivo;
                }
            }
        }

        $ok = $this->habModel->crear($_POST);

        if ($ok) {
            $this->setFlash('success', "Habitación {$numero} registrada con éxito.");
            redirect('habitaciones');
        } else {
            $this->setFlash('danger', 'Error al guardar la habitación.');
            redirect('habitaciones/crear');
        }
    }

    /**
     * Formulario para editar habitación
     */
    public function editar(?string $id = null): void
    {
        $id = (int)$id;
        $habitacion = $this->habModel->obtenerPorId($id);

        if (!$habitacion) {
            $this->setFlash('danger', 'Habitación no encontrada.');
            redirect('habitaciones');
        }

        $tipos = $this->tipoModel->listarActivos();
        $this->render('habitaciones/editar', [
            'habitacion' => $habitacion,
            'tipos'      => $tipos
        ], 'Modificar Habitación ' . $habitacion['numero']);
    }

    /**
     * Actualizar habitación
     */
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('habitaciones');
        }

        $id = (int)($_POST['id'] ?? 0);
        $numero = trim($_POST['numero'] ?? '');

        // Procesar subida de fotografía de habitación
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $permitidas)) {
                $dirUploads = __DIR__ . '/../../../public/uploads/habitaciones/';
                if (!is_dir($dirUploads)) {
                    mkdir($dirUploads, 0777, true);
                }
                $nombreArchivo = 'hab_' . ($numero ?: $id) . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $dirUploads . $nombreArchivo)) {
                    $_POST['foto'] = $nombreArchivo;
                }
            }
        } elseif (!empty($_POST['foto_actual'])) {
            $_POST['foto'] = $_POST['foto_actual'];
        }

        $ok = $this->habModel->actualizar($id, $_POST);

        if ($ok) {
            $this->setFlash('success', 'Habitación actualizada correctamente.');
        } else {
            $this->setFlash('danger', 'No se pudo actualizar la habitación.');
        }

        redirect('habitaciones');
    }

    /**
     * Eliminar habitación
     */
    public function eliminar(?string $id = null): void
    {
        $id = (int)$id;
        $ok = $this->habModel->eliminar($id);

        if ($ok) {
            $this->setFlash('success', 'Habitación eliminada.');
        } else {
            $this->setFlash('danger', 'No se puede eliminar la habitación porque tiene reservas asociadas.');
        }

        redirect('habitaciones');
    }

    /**
     * Módulo de Tipos de Habitación
     */
    public function tipos(): void
    {
        $tipos = $this->tipoModel->listarTodos();
        $this->render('habitaciones/tipos', [
            'tipos' => $tipos
        ], 'Tipos de Habitación');
    }

    /**
     * Guardar tipo de habitación
     */
    public function guardarTipo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('habitaciones/tipos');
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if ($id) {
            $ok = $this->tipoModel->actualizar($id, $_POST);
            $msg = $ok ? 'Tipo de habitación actualizado.' : 'Error al actualizar tipo.';
        } else {
            $ok = $this->tipoModel->crear($_POST);
            $msg = $ok ? 'Tipo de habitación creado.' : 'Error al crear tipo.';
        }

        $this->setFlash($ok ? 'success' : 'danger', $msg);
        redirect('habitaciones/tipos');
    }

    /**
     * Eliminar tipo de habitación
     */
    public function eliminarTipo(?string $id = null): void
    {
        $id = (int)$id;
        $ok = $this->tipoModel->eliminar($id);

        if ($ok) {
            $this->setFlash('success', 'Tipo de habitación eliminado.');
        } else {
            $this->setFlash('danger', 'No se puede eliminar porque existen habitaciones asociadas a este tipo.');
        }

        redirect('habitaciones/tipos');
    }

    /**
     * Módulo de Tarifas por Noche (Edición rápida y masiva de precios)
     */
    public function tarifas(): void
    {
        $habitaciones = $this->habModel->listarTodos();
        $tipos = $this->tipoModel->listarTodos();

        $this->render('habitaciones/tarifas', [
            'habitaciones' => $habitaciones,
            'tipos'        => $tipos
        ], 'Tarifas por Noche');
    }

    /**
     * Actualizar tarifa rápida
     */
    public function guardarTarifa(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('habitaciones/tarifas');
        }

        $id = (int)($_POST['id'] ?? 0);
        $precio = (float)($_POST['precio_diario'] ?? 0);

        $ok = $this->habModel->actualizarTarifa($id, $precio);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Tarifa actualizada correctamente.' : 'Error al actualizar tarifa.');
        redirect('habitaciones/tarifas');
    }

    /**
     * Módulo de Estados de Habitaciones (Grid interactivo con cambio de estado de un clic)
     */
    public function estados(): void
    {
        $habitaciones = $this->habModel->listarTodos();
        $conteo = $this->habModel->contarPorEstado();

        $this->render('habitaciones/estados', [
            'habitaciones' => $habitaciones,
            'conteo'       => $conteo
        ], 'Estado de Habitaciones en Tiempo Real');
    }

    /**
     * Cambio rápido de estado (disponible, limpieza, mantenimiento, ocupada)
     */
    public function cambiarEstado(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $nuevoEstado = $_POST['estado'] ?? 'disponible';

        $ok = $this->habModel->actualizarEstado($id, $nuevoEstado);

        if ($ok && $nuevoEstado === 'limpieza') {
            // Generar orden de limpieza
            $this->mantModel->crear([
                'habitacion_id' => $id,
                'tipo'          => 'limpieza',
                'descripcion'   => 'Orden de limpieza generada desde el panel de estados.',
                'responsable'   => 'Personal de Limpieza'
            ]);
        }

        if (isset($_POST['ajax'])) {
            $this->json(['success' => $ok]);
        }

        $this->setFlash('success', 'Estado de habitación actualizado.');
        redirect('habitaciones/estados');
    }

    /**
     * Módulo de Mantenimiento y Limpieza
     */
    public function mantenimiento(): void
    {
        $mantenimientos = $this->mantModel->listarTodos();
        $habitaciones = $this->habModel->listarTodos();

        $this->render('habitaciones/mantenimiento', [
            'mantenimientos' => $mantenimientos,
            'habitaciones'   => $habitaciones
        ], 'Mantenimiento y Limpieza de Habitaciones');
    }

    /**
     * Crear orden de mantenimiento
     */
    public function guardarMantenimiento(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('habitaciones/mantenimiento');
        }

        $ok = $this->mantModel->crear($_POST);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Orden de mantenimiento/limpieza registrada.' : 'Error al registrar orden.');
        redirect('habitaciones/mantenimiento');
    }

    /**
     * Finalizar orden de mantenimiento y liberar habitación
     */
    public function finalizarMantenimiento(?string $id = null): void
    {
        $id = (int)$id;
        $ok = $this->mantModel->finalizar($id);

        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Mantenimiento finalizado. La habitación vuelve a estar disponible.' : 'Error al finalizar.');
        redirect('habitaciones/mantenimiento');
    }
}
