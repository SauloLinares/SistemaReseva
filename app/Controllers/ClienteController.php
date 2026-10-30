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

        // Validación básica
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $dni = trim($_POST['dni'] ?? '');

        if (empty($nombres) || empty($apellidos) || empty($dni)) {
            $this->setFlash('danger', 'Los campos Nombres, Apellidos y DNI son obligatorios.');
            redirect('clientes/crear');
        }

        $exito = $this->clienteModel->crear($_POST);

        if ($exito) {
            $this->setFlash('success', '¡Cliente registrado exitosamente!');
        } else {
            $this->setFlash('danger', 'Ocurrió un error al registrar el cliente. Es posible que el DNI ya exista.');
        }

        redirect('clientes');
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

        $exito = $this->clienteModel->actualizar($id, $_POST);

        if ($exito) {
            $this->setFlash('success', '¡Cliente actualizado exitosamente!');
        } else {
            $this->setFlash('danger', 'No se pudo actualizar la información del cliente.');
        }

        redirect('clientes');
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
