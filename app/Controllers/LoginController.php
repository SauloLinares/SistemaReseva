<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

/**
 * Controlador de Autenticación (Login / Logout)
 */
class LoginController extends Controller
{
    protected Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Muestra la vista del formulario de Login
     */
    public function index(): void
    {
        // Si el usuario ya está autenticado, enviarlo directamente al panel
        $this->requireGuest();

        $this->renderAuth('auth/login', [
            'correoPrevio' => old('email', '')
        ], 'Iniciar Sesión');
    }

    /**
     * Procesa las credenciales enviadas por el formulario (POST)
     */
    public function autenticar(): void
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validación de campos vacíos
        if (empty($email) || empty($password)) {
            $_SESSION['old']['email'] = $email;
            $this->setFlash('danger', 'Por favor, ingresa tu correo y contraseña.');
            redirect('login');
        }

        // Validación de formato de correo
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['old']['email'] = $email;
            $this->setFlash('danger', 'El formato del correo electrónico no es válido.');
            redirect('login');
        }

        // Verificar credenciales con el modelo
        $usuario = $this->usuarioModel->verificarCredenciales($email, $password);

        if (!$usuario) {
            $_SESSION['old']['email'] = $email;
            $this->setFlash('danger', 'Correo o contraseña incorrectos. Verifica tus credenciales.');
            redirect('login');
        }

        // Validar si la cuenta está activa
        if ((int)($usuario['estado'] ?? 1) !== 1) {
            $_SESSION['old']['email'] = $email;
            $this->setFlash('danger', 'Tu cuenta se encuentra inactiva. Por favor contacta al administrador.');
            redirect('login');
        }

        // Regenerar ID de sesión para prevenir Session Fixation
        session_regenerate_id(true);

        // Guardar datos en la sesión
        // Debe cambiar la contraseña si se la asignó el administrador, o si sigue usando la
        // contraseña inicial fuera de desarrollo (la conoce cualquiera que lea el README)
        $debeCambiar = (int)($usuario['debe_cambiar_clave'] ?? 0) === 1
            || (!\App\Config\App::esDesarrollo() && hash_equals(Usuario::CLAVE_INICIAL, $password));

        $_SESSION['usuario'] = [
            'id'                 => $usuario['id'],
            'nombre'             => $usuario['nombre'],
            'email'              => $usuario['email'],
            'foto'               => $usuario['foto'] ?? null,
            'rol'                => $usuario['rol'] ?? '',
            'rol_id'             => (int)($usuario['rol_id'] ?? 0),
            'ultimo_login'       => $usuario['ultimo_login'],
            'debe_cambiar_clave' => $debeCambiar,
        ];

        // Registrar timestamp de último login
        $this->usuarioModel->actualizarUltimoLogin((int)$usuario['id']);

        if ($debeCambiar) {
            $this->setFlash('warning', 'Antes de continuar, crea tu propia contraseña.');
            redirect('perfil/clave');
        }

        $this->setFlash('success', '¡Bienvenido(a), ' . htmlspecialchars($usuario['nombre']) . '!');
        redirect('');
    }

    /**
     * Cierra la sesión activa del usuario
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Limpiar información de usuario
        unset($_SESSION['usuario']);

        // Mensaje flash de despedida
        $this->setFlash('info', 'Has cerrado tu sesión de forma segura.');
        redirect('login');
    }
}
