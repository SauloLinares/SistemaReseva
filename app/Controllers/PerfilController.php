<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

/**
 * Cuenta del usuario que inició sesión: sus datos (Mi perfil) y su contraseña.
 * Cada usuario edita solo lo suyo; el rol y el estado los cambia el administrador.
 */
class PerfilController extends Controller
{
    private Usuario $usuarioModel;

    /** Tipos de imagen aceptados para la foto (constante IMAGETYPE_* => extensión) */
    private const TIPOS_FOTO = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];

    /** Largo mínimo de una contraseña nueva */
    public const CLAVE_MINIMO = 8;

    public function __construct()
    {
        $this->requireAuth();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Mi perfil
     */
    public function index(): void
    {
        $this->render('perfil/index', [
            'usuario' => $this->usuarioModel->obtenerPorId((int) auth_user('id')),
        ], 'Mi perfil');
    }

    /**
     * Guardar los datos de Mi perfil
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('perfil');
        }
        $id = (int) auth_user('id');
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        $errores = [];
        if (mb_strlen($nombre) < 3) {
            $errores['nombre'] = 'Escribe tu nombre completo.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Escribe un correo válido: es tu usuario para ingresar.';
        } elseif (($otro = $this->usuarioModel->obtenerPorEmail($email)) && (int) $otro['id'] !== $id) {
            $errores['email'] = 'Ese correo ya lo usa otro usuario.';
        }
        if ($telefono !== '' && !preg_match('/^[0-9+\s()-]{6,20}$/', $telefono)) {
            $errores['telefono'] = 'Escribe solo números (puede llevar +, espacios o guiones).';
        }
        $fotoSubida = !empty($_FILES['foto']['name']);
        if ($fotoSubida && ($error = $this->validarFoto($_FILES['foto']))) {
            $errores['foto'] = $error;
        }
        if ($errores) {
            $this->volverConErrores('perfil', $errores);
        }

        $foto = $fotoSubida ? $this->guardarFoto($_FILES['foto'], $id) : (!empty($_POST['quitar_foto']) ? '' : null);
        if ($fotoSubida && $foto === null) {
            $this->volverConErrores('perfil', ['foto' => 'No se pudo guardar la foto. Inténtalo de nuevo.']);
        }

        $anterior = $this->usuarioModel->obtenerPorId($id);
        if (!$this->usuarioModel->actualizarPerfil($id, $nombre, $email, $telefono, $foto)) {
            $this->volverConErrores('perfil', [], 'No se pudieron guardar tus datos. Inténtalo de nuevo.');
        }
        // La foto anterior ya no se usa
        if ($foto !== null && !empty($anterior['foto'])) {
            @unlink(self::carpetaFotos() . basename($anterior['foto']));
        }

        // La cabecera muestra estos datos desde la sesión
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['email'] = $email;
        if ($foto !== null) {
            $_SESSION['usuario']['foto'] = $foto !== '' ? $foto : null;
        }

        $this->setFlash('success', 'Tus datos se guardaron.');
        redirect('perfil');
    }

    /**
     * "Seguir trabajando" del aviso de inactividad (envios.js). Llegar hasta aquí ya renovó
     * el plazo de la sesión (Acceso::sincronizarSesion); solo se confirma.
     */
    public function pulso(): void
    {
        $this->json(['success' => true, 'minutos' => \App\Config\App::sesionMinutos()]);
    }

    /**
     * Formulario para cambiar la contraseña (también el obligatorio del primer ingreso)
     */
    public function clave(): void
    {
        $this->render('perfil/clave', [
            'obligatorio' => (bool) auth_user('debe_cambiar_clave'),
            'minimo'      => self::CLAVE_MINIMO,
        ], 'Cambiar contraseña');
    }

    /**
     * Guardar la contraseña nueva
     */
    public function guardarClave(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('perfil/clave');
        }
        $id = (int) auth_user('id');
        $actual = (string) ($_POST['clave_actual'] ?? '');
        $nueva = (string) ($_POST['clave_nueva'] ?? '');
        $confirmacion = (string) ($_POST['clave_confirmacion'] ?? '');

        $errores = [];
        if (!$this->usuarioModel->claveCorrecta($id, $actual)) {
            $errores['clave_actual'] = 'La contraseña actual no es correcta.';
        }
        if ($error = self::problemaDeClave($nueva, (string) auth_user('email'))) {
            $errores['clave_nueva'] = $error;
        } elseif (!isset($errores['clave_actual']) && hash_equals($actual, $nueva)) {
            $errores['clave_nueva'] = 'La contraseña nueva debe ser distinta de la actual.';
        }
        if (!isset($errores['clave_nueva']) && !hash_equals($nueva, $confirmacion)) {
            $errores['clave_confirmacion'] = 'No coincide con la contraseña nueva.';
        }
        if ($errores) {
            $this->volverConErrores('perfil/clave', $errores);
        }

        if (!$this->usuarioModel->cambiarClave($id, $nueva)) {
            $this->volverConErrores('perfil/clave', [], 'No se pudo cambiar la contraseña. Inténtalo de nuevo.');
        }

        // Sesión nueva tras cambiar la credencial
        $obligatorio = (bool) auth_user('debe_cambiar_clave');
        session_regenerate_id(true);
        $_SESSION['usuario']['debe_cambiar_clave'] = false;

        $this->setFlash('success', $obligatorio ? '¡Listo! Ya puedes usar el sistema con tu nueva contraseña.' : 'Tu contraseña se cambió.');
        redirect($obligatorio ? '' : 'perfil');
    }

    /**
     * Reglas de una contraseña nueva. Devuelve el problema o null si sirve.
     * Las mismas reglas se muestran en vivo en la pantalla (perfil-clave.js).
     */
    public static function problemaDeClave(string $clave, string $email = ''): ?string
    {
        if (mb_strlen($clave) < self::CLAVE_MINIMO) {
            return 'Usa al menos ' . self::CLAVE_MINIMO . ' caracteres.';
        }
        if (!preg_match('/\p{L}/u', $clave) || !preg_match('/\d/', $clave)) {
            return 'Combina letras y números.';
        }
        if ($clave !== trim($clave)) {
            return 'No empieces ni termines con un espacio.';
        }
        if (hash_equals(Usuario::CLAVE_INICIAL, $clave)) {
            return 'Esa es la contraseña de demostración: elige otra.';
        }
        $local = strtolower(strstr($email, '@', true) ?: '');
        if ($local !== '' && str_contains(strtolower($clave), $local)) {
            return 'No uses tu correo dentro de la contraseña.';
        }
        return null;
    }

    private static function carpetaFotos(): string
    {
        return dirname(__DIR__, 2) . '/public/uploads/usuarios/';
    }

    /**
     * Comprueba que el archivo sea una imagen PNG, JPG o WEBP de verdad (no basta la extensión)
     */
    private function validarFoto(array $archivo): ?string
    {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return 'No se pudo subir la foto (¿pesa demasiado?). Inténtalo con otra imagen.';
        }
        if ($archivo['size'] > 2 * 1024 * 1024) {
            return 'La foto no debe pesar más de 2 MB.';
        }
        $info = @getimagesize($archivo['tmp_name']);
        if ($info === false || !isset(self::TIPOS_FOTO[$info[2]])) {
            return 'La foto debe ser una imagen PNG, JPG o WEBP.';
        }
        return null;
    }

    /**
     * Guarda la foto (ya validada) con un nombre que no se puede adivinar; la extensión sale
     * del tipo real de la imagen. Devuelve el nombre del archivo o null si falló.
     */
    private function guardarFoto(array $archivo, int $id): ?string
    {
        $dir = self::carpetaFotos();
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return null;
        }
        $ext = self::TIPOS_FOTO[getimagesize($archivo['tmp_name'])[2]];
        $nombre = 'usuario_' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        return move_uploaded_file($archivo['tmp_name'], $dir . $nombre) ? $nombre : null;
    }
}
