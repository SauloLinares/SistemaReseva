<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Auditoria;

class SeguridadController extends Controller
{
    private Usuario $usuarioModel;
    private Rol $rolModel;
    private Auditoria $auditoriaModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->usuarioModel = new Usuario();
        $this->rolModel = new Rol();
        $this->auditoriaModel = new Auditoria();
    }

    public function index(): void
    {
        $this->usuarios();
    }

    /**
     * Gestión de Usuarios
     */
    public function usuarios(): void
    {
        $usuarios = $this->usuarioModel->listarTodos();
        $roles = $this->rolModel->listarTodos();

        $this->render('seguridad/usuarios', [
            'usuarios' => $usuarios,
            'roles'    => $roles
        ], 'Gestión de Usuarios del Sistema');
    }

    /**
     * Guardar nuevo usuario
     */
    public function guardarUsuario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('seguridad/usuarios');
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $clave = (string)($_POST['password'] ?? '');

        // Contraseña temporal: la persona la cambia por una propia al ingresar
        if ((!$id || $clave !== '') && mb_strlen($clave) < \App\Controllers\PerfilController::CLAVE_MINIMO) {
            $this->setFlash('danger', 'La contraseña debe tener al menos ' . \App\Controllers\PerfilController::CLAVE_MINIMO . ' caracteres.');
            redirect('seguridad/usuarios');
        }

        if ($id) {
            $propio = $id === (int) auth_user('id');
            $ok = $this->usuarioModel->actualizar($id, $_POST, !$propio);
            $msg = $ok ? 'Usuario actualizado con éxito.' : 'Error al actualizar usuario.';
            if ($ok && $clave !== '' && !$propio) {
                $msg .= ' Deberá cambiar la contraseña al ingresar.';
            }
        } else {
            $email = trim($_POST['email'] ?? '');
            if ($this->usuarioModel->obtenerPorEmail($email)) {
                $this->setFlash('danger', 'Ya existe un usuario registrado con este correo.');
                redirect('seguridad/usuarios');
            }
            $ok = $this->usuarioModel->crear($_POST);
            $msg = $ok ? 'Usuario creado correctamente. Deberá cambiar la contraseña al ingresar.' : 'Error al crear usuario.';
        }

        $this->auditoriaModel->registrar('Seguridad', 'Usuarios', ($id ? 'Actualizar' : 'Crear') . ' usuario ' . ($_POST['email'] ?? ''));

        $this->setFlash($ok ? 'success' : 'danger', $msg);
        redirect('seguridad/usuarios');
    }

    /**
     * Interruptor de estado de la tabla (fetch): {success, mensaje}
     */
    public function cambiarEstadoUsuario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'mensaje' => 'Método no permitido.'], 405);
        }
        $id = (int)($_POST['id'] ?? 0);
        $activo = ($_POST['estado'] ?? '') === '1';
        if ($id === 1 || $id === (int)auth_user('id')) {
            $this->json(['success' => false, 'mensaje' => 'No puedes desactivar al administrador principal ni a tu propio usuario.']);
        }
        $ok = $id > 0 && $this->usuarioModel->cambiarEstado($id, $activo);
        if ($ok) {
            $this->auditoriaModel->registrar('Seguridad', 'Usuarios', ($activo ? 'Activar' : 'Desactivar') . ' usuario ID ' . $id);
        }
        $this->json([
            'success' => $ok,
            'mensaje' => $ok ? ('Usuario' . ($activo ? ' activo.' : ' inactivo: ya no puede ingresar.')) : 'No se pudo cambiar el estado.',
        ]);
    }

    /**
     * Eliminar usuario
     */
    public function eliminarUsuario(?string $id = null): void
    {
        $id = (int)$id;
        if ($id === 1 || $id === (int)auth_user('id')) {
            $this->setFlash('danger', 'No puedes eliminar este usuario administrador o tu propio usuario.');
            redirect('seguridad/usuarios');
        }

        $ok = $this->usuarioModel->eliminar($id);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Usuario eliminado.' : 'Error al eliminar usuario.');
        redirect('seguridad/usuarios');
    }

    /**
     * Gestión de Roles
     */
    public function roles(): void
    {
        $roles = $this->rolModel->listarTodos();
        $this->render('seguridad/roles', [
            'roles' => $roles
        ], 'Roles y Perfiles de Acceso');
    }

    /**
     * Guardar Rol
     */
    public function guardarRol(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('seguridad/roles');
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if ($id) {
            $ok = $this->rolModel->actualizar($id, $_POST);
        } else {
            $ok = $this->rolModel->crear($_POST);
        }

        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Rol guardado con éxito.' : 'Error al guardar rol.');
        redirect('seguridad/roles');
    }

    /**
     * Matriz de Permisos
     */
    public function permisos(): void
    {
        $roles = $this->rolModel->listarTodos();
        $rolSeleccionadoId = !empty($_GET['rol_id']) ? (int)$_GET['rol_id'] : ($roles[0]['id'] ?? 1);
        $permisos = $this->rolModel->obtenerPermisos($rolSeleccionadoId);

        $modulos = [
            'reservas'      => 'Gestión de Reservas',
            'huespedes'     => 'Directorio de Huéspedes',
            'habitaciones'  => 'Administración de Habitaciones',
            'recepcion'     => 'Check-in y Check-out',
            'pagos'         => 'Caja y Facturación',
            'servicios'     => 'Servicios Adicionales',
            'reportes'      => 'Reportes y Estadísticas',
            'seguridad'     => 'Seguridad y Usuarios',
            'configuracion' => 'Configuración del Hotel'
        ];

        $this->render('seguridad/permisos', [
            'roles'             => $roles,
            'rolSeleccionadoId' => $rolSeleccionadoId,
            'permisos'          => $permisos,
            'modulos'           => $modulos
        ], 'Matriz de Permisos por Rol');
    }

    /**
     * Guardar matriz de permisos
     */
    public function guardarPermisos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('seguridad/permisos');
        }

        $rolId = (int)($_POST['rol_id'] ?? 1);
        $permisosPost = $_POST['permisos'] ?? [];

        $ok = $this->rolModel->guardarPermisos($rolId, $permisosPost);

        $this->auditoriaModel->registrar('Seguridad', 'Permisos', "Permisos actualizados para el rol ID {$rolId}");

        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Permisos del rol actualizados.' : 'Error al actualizar permisos.');
        redirect('seguridad/permisos?rol_id=' . $rolId);
    }

    /**
     * Auditoría del Sistema
     */
    public function auditoria(): void
    {
        $registros = $this->auditoriaModel->listarTodos(150);

        $this->render('seguridad/auditoria', [
            'registros' => $registros
        ], 'Auditoría de Operaciones del Sistema');
    }
}
