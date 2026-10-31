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

        $errores = $this->validarHuesped($_POST);
        if ($errores) {
            $this->volverConErrores('huespedes/crear', $errores);
        }

        $id = $this->huespedModel->crear($_POST);

        if ($id) {
            $this->setFlash('success', '¡Huésped registrado correctamente!');
            redirect('huespedes');
        } else {
            $this->volverConErrores('huespedes/crear', [], 'No se pudo guardar el huésped en la base de datos. Inténtalo de nuevo.');
        }
    }

    /**
     * Reglas del formulario de huésped. Devuelve ['campo' => 'mensaje'] (vacío si todo está bien).
     *
     * @param int $idActual Al editar, el propio huésped no cuenta como documento duplicado
     */
    private function validarHuesped(array $datos, int $idActual = 0): array
    {
        $errores = [];
        $numDoc = trim($datos['numero_documento'] ?? '');

        if ($numDoc === '') {
            $errores['numero_documento'] = 'Ingresa el número de documento.';
        } else {
            $existe = $this->huespedModel->buscarPorDocumento($numDoc);
            if ($existe && (int)$existe['id'] !== $idActual) {
                $errores['numero_documento'] = 'Este documento ya pertenece a ' . $existe['nombres'] . ' ' . $existe['apellidos'] . '.';
            }
        }
        if (trim($datos['nombres'] ?? '') === '') {
            $errores['nombres'] = 'Ingresa los nombres.';
        }
        if (trim($datos['apellidos'] ?? '') === '') {
            $errores['apellidos'] = 'Ingresa los apellidos.';
        }
        $correo = trim($datos['correo'] ?? '');
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'El correo no tiene un formato válido (ej. nombre@correo.com).';
        }

        return $errores;
    }

    /**
     * Endpoint API para crear un huésped vía AJAX (Modal)
     */
    public function apiGuardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        $numDoc = trim($_POST['numero_documento'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');

        if (empty($numDoc) || empty($nombres) || empty($apellidos)) {
            $this->json(['success' => false, 'mensaje' => 'El número de documento, nombres y apellidos son obligatorios.']);
            return;
        }

        $existe = $this->huespedModel->buscarPorDocumento($numDoc);
        if ($existe) {
            $this->json([
                'success' => false,
                'mensaje' => 'Ya existe un huésped registrado con el documento ' . htmlspecialchars($numDoc) . ' (' . htmlspecialchars($existe['nombres'] . ' ' . $existe['apellidos']) . ').'
            ]);
            return;
        }

        $id = $this->huespedModel->crear($_POST);
        if ($id) {
            $nuevo = $this->huespedModel->obtenerPorId($id);
            $textoSelect = $nuevo['nombres'] . ' ' . $nuevo['apellidos'] . ' (Doc: ' . $nuevo['tipo_documento'] . ' ' . $nuevo['numero_documento'] . ' - Tel: ' . ($nuevo['telefono'] ?: 'S/N') . ')';

            $this->json([
                'success' => true,
                'mensaje' => '¡Huésped registrado correctamente!',
                'huesped' => [
                    'id'               => $id,
                    'nombre_completo'  => $nuevo['nombres'] . ' ' . $nuevo['apellidos'],
                    'tipo_documento'   => $nuevo['tipo_documento'],
                    'numero_documento' => $nuevo['numero_documento'],
                    'telefono'         => $nuevo['telefono'] ?: 'S/N',
                    'texto_select'     => $textoSelect
                ]
            ]);
        } else {
            $this->json(['success' => false, 'mensaje' => 'No se pudo guardar el huésped en la base de datos.']);
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
        $errores = $this->validarHuesped($_POST, $id);
        if ($errores) {
            $this->volverConErrores('huespedes/editar/' . $id, $errores);
        }

        $ok = $this->huespedModel->actualizar($id, $_POST);

        if ($ok) {
            $this->setFlash('success', 'Datos del huésped actualizados con éxito.');
            redirect('huespedes');
        }
        $this->volverConErrores('huespedes/editar/' . $id, [], 'Error al actualizar los datos del huésped. Inténtalo de nuevo.');
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
                // Intentar búsqueda general por nombre o documento
                $varios = $this->huespedModel->listarTodos($doc);
                if (!empty($varios)) {
                    $resultado = $varios[0];
                }
            }
        }

        $frecuentes = $this->huespedModel->listarFrecuentes(6);
        $totalHuespedes = $this->huespedModel->contarTotal();

        $this->render('huespedes/buscar', [
            'documento'      => $doc,
            'huesped'        => $resultado,
            'frecuentes'     => $frecuentes,
            'totalHuespedes' => $totalHuespedes
        ], 'Terminal de Recepción - Consulta de Huéspedes');
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
