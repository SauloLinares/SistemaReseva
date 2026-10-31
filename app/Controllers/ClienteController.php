<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cliente;

/**
 * Controlador para la gestión de Clientes (CRUD)
 */
class ClienteController extends Controller
{
    private Cliente $clienteModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->clienteModel = new Cliente();
    }

    /**
     * Muestra el listado de todos los clientes
     */
    public function index(): void
    {
        $clientes = $this->clienteModel->listarTodos();

        $this->render('clientes/index', [
            'clientes' => $clientes
        ], 'Gestión de Clientes');
    }

    /**
     * Muestra el formulario para crear un nuevo cliente
     */
    public function crear(): void
    {
        $this->render('clientes/crear', [], 'Registrar Nuevo Cliente');
    }

    /**
     * Procesa y guarda un nuevo cliente (POST)
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('clientes');
        }

        $errores = $this->validarCliente($_POST);
        if ($errores) {
            $this->volverConErrores('clientes/crear', $errores);
        }

        if ($this->clienteModel->crear($_POST)) {
            $this->setFlash('success', '¡Cliente registrado exitosamente!');
            redirect('clientes');
        }
        $this->volverConErrores('clientes/crear', [], 'Ocurrió un error al registrar el cliente. Inténtalo de nuevo.');
    }

    /**
     * Reglas del formulario de cliente. Devuelve ['campo' => 'mensaje'] (vacío si todo está bien).
     *
     * @param int $idActual Al editar, el propio cliente no cuenta como DNI duplicado
     */
    private function validarCliente(array $datos, int $idActual = 0): array
    {
        $errores = [];
        $dni = trim($datos['dni'] ?? '');

        if (trim($datos['nombres'] ?? '') === '') {
            $errores['nombres'] = 'Ingresa los nombres.';
        }
        if (trim($datos['apellidos'] ?? '') === '') {
            $errores['apellidos'] = 'Ingresa los apellidos.';
        }
        if ($dni === '') {
            $errores['dni'] = 'Ingresa el DNI.';
        } else {
            $existe = $this->clienteModel->buscarPorDni($dni);
            if ($existe && (int)$existe['id'] !== $idActual) {
                $errores['dni'] = 'Este DNI ya pertenece a ' . $existe['nombres'] . ' ' . $existe['apellidos'] . '.';
            }
        }
        $correo = trim($datos['correo'] ?? '');
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'El correo no tiene un formato válido (ej. nombre@correo.com).';
        }

        return $errores;
    }

    /**
     * Muestra el formulario para editar un cliente
     */
    public function editar(?string $id = null): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            $this->setFlash('warning', 'Identificador de cliente no válido.');
            redirect('clientes');
        }

        $cliente = $this->clienteModel->obtenerPorId($id);

        if (!$cliente) {
            $this->setFlash('danger', 'El cliente solicitado no fue encontrado.');
            redirect('clientes');
        }

        $this->render('clientes/editar', [
            'cliente' => $cliente
        ], 'Editar Cliente #' . $cliente['id']);
    }

    /**
     * Procesa la actualización de un cliente (POST)
     */
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('clientes');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->setFlash('danger', 'Identificador no válido.');
            redirect('clientes');
        }

        $errores = $this->validarCliente($_POST, $id);
        if ($errores) {
            $this->volverConErrores('clientes/editar/' . $id, $errores);
        }

        if ($this->clienteModel->actualizar($id, $_POST)) {
            $this->setFlash('success', '¡Cliente actualizado exitosamente!');
            redirect('clientes');
        }
        $this->volverConErrores('clientes/editar/' . $id, [], 'No se pudo actualizar la información del cliente. Inténtalo de nuevo.');
    }

    /**
     * Elimina un cliente por su ID
     */
    public function eliminar(?string $id = null): void
    {
        $id = (int)$id;
        if ($id > 0) {
            $exito = $this->clienteModel->eliminar($id);
            if ($exito) {
                $this->setFlash('success', '¡Cliente eliminado correctamente!');
            } else {
                $this->setFlash('danger', 'No se pudo eliminar el cliente.');
            }
        }

        redirect('clientes');
    }
}
