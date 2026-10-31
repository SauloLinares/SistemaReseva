<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Modelo Usuario
 * Gestiona la autenticación y operaciones sobre la tabla 'usuarios'.
 */
class Usuario extends Model
{
    /** Contraseña del administrador que se crea en una instalación nueva (está en el README) */
    public const CLAVE_INICIAL = 'admin123';

    public function __construct()
    {
        parent::__construct();
        $this->inicializarTablaSiNoExiste();
    }

    /**
     * Asegura que la tabla de usuarios y el usuario inicial existan
     */
    private function inicializarTablaSiNoExiste(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                rol VARCHAR(50) DEFAULT 'Administrador',
                estado TINYINT(1) DEFAULT 1 COMMENT '1: Activo, 0: Inactivo',
                ultimo_login DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $this->db->exec($sql);

            // Verificar si hay al menos un usuario; si no, insertar el administrador inicial
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios");
            $total = (int)($stmt->fetch()['total'] ?? 0);

            if ($total === 0) {
                $passwordHash = password_hash(self::CLAVE_INICIAL, PASSWORD_BCRYPT);
                $insert = $this->db->prepare(
                    "INSERT INTO usuarios (nombre, email, password, rol, estado) VALUES (:nombre, :email, :password, :rol, 1)"
                );
                $insert->execute([
                    ':nombre'   => 'Administrador',
                    ':email'    => 'admin@admin.com',
                    ':password' => $passwordHash,
                    ':rol'      => 'Administrador'
                ]);
            }
        } catch (\Throwable $e) {
            // Silencioso para evitar romper en entornos con permisos restringidos
        }
        $this->asegurarColumnasDeCuenta();
    }

    /**
     * Columnas de "Mi perfil" y del cambio de contraseña obligatorio, para bases creadas
     * antes de que existieran (MySQL no tiene ADD COLUMN IF NOT EXISTS).
     */
    private function asegurarColumnasDeCuenta(): void
    {
        $nuevas = [
            'telefono'           => "VARCHAR(20) NULL AFTER email",
            'foto'               => "VARCHAR(255) NULL AFTER telefono",
            'debe_cambiar_clave' => "TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: debe cambiarla al ingresar'",
            'clave_cambiada_en'  => "DATETIME NULL",
        ];
        try {
            $existentes = $this->db->query("SHOW COLUMNS FROM usuarios")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($nuevas as $columna => $definicion) {
                if (!in_array($columna, $existentes, true)) {
                    $this->db->exec("ALTER TABLE usuarios ADD COLUMN {$columna} {$definicion}");
                }
            }
        } catch (\Throwable $e) {
            // Sin permiso para ALTER: el perfil funciona sin esas columnas hasta que se agreguen
        }
    }

    /**
     * Busca un usuario por su correo electrónico
     */
    public function obtenerPorEmail(string $email): ?array
    {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => trim($email)]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Busca un usuario por su ID
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT id, nombre, email, telefono, foto, rol, estado, debe_cambiar_clave, clave_cambiada_en, ultimo_login, created_at
                FROM usuarios WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Valida credenciales de acceso (email y password)
     * Retorna los datos del usuario si es correcto y está activo, o null si falla.
     */
    public function verificarCredenciales(string $email, string $password): ?array
    {
        $usuario = $this->obtenerPorEmail($email);

        if (!$usuario) {
            return null;
        }

        // Verificar contraseña con hash seguro bcrypt/argon2
        if (!password_verify($password, $usuario['password'])) {
            return null;
        }

        // Si la contraseña necesita rehasheo (por actualización de algoritmo en PHP)
        if (password_needs_rehash($usuario['password'], PASSWORD_DEFAULT)) {
            $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
            $stmt->execute([':password' => $nuevoHash, ':id' => $usuario['id']]);
        }

        return $usuario;
    }

    /**
     * Actualiza la fecha y hora del último inicio de sesión
     */
    public function actualizarUltimoLogin(int $id): bool
    {
        $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Registra un nuevo usuario con contraseña hasheada
     */
    public function crear(array $datos): bool
    {
        // La contraseña la eligió el administrador: el usuario debe cambiarla al ingresar
        $sql = "INSERT INTO usuarios (nombre, email, password, rol, estado, debe_cambiar_clave)
                VALUES (:nombre, :email, :password, :rol, :estado, 1)";

        $stmt = $this->db->prepare($sql);

        $passwordHash = password_hash($datos['password'] ?? '', PASSWORD_DEFAULT);

        return $stmt->execute([
            ':nombre'   => trim($datos['nombre'] ?? ''),
            ':email'    => trim($datos['email'] ?? ''),
            ':password' => $passwordHash,
            ':rol'      => trim($datos['rol'] ?? 'Administrador'),
            ':estado'   => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    /**
     * Lista todos los usuarios (sin exponer la contraseña)
     */
    public function listarTodos(): array
    {
        $sql = "SELECT id, nombre, email, rol, estado, ultimo_login, created_at FROM usuarios ORDER BY id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Actualización desde Seguridad > Usuarios. Si trae contraseña, es un restablecimiento:
     * el usuario deberá cambiarla al ingresar ($obligarCambio = false si se la cambia a sí mismo).
     */
    public function actualizar(int $id, array $datos, bool $obligarCambio = true): bool
    {
        if (!empty($datos['password'])) {
            // Contraseña restablecida por el administrador: el usuario debe cambiarla al ingresar
            $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, password = :pass, rol = :rol, estado = :estado,
                    debe_cambiar_clave = :obligar WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'     => $id,
                ':nombre' => trim($datos['nombre'] ?? ''),
                ':email'  => trim($datos['email'] ?? ''),
                ':pass'   => password_hash($datos['password'], PASSWORD_DEFAULT),
                ':obligar' => $obligarCambio ? 1 : 0,
                ':rol'    => trim($datos['rol'] ?? 'Administrador'),
                ':estado' => isset($datos['estado']) ? (int)$datos['estado'] : 1
            ]);
        } else {
            $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, rol = :rol, estado = :estado WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'     => $id,
                ':nombre' => trim($datos['nombre'] ?? ''),
                ':email'  => trim($datos['email'] ?? ''),
                ':rol'    => trim($datos['rol'] ?? 'Administrador'),
                ':estado' => isset($datos['estado']) ? (int)$datos['estado'] : 1
            ]);
        }
    }

    /**
     * Datos que cada usuario puede cambiar de sí mismo (Mi perfil). El rol y el estado no.
     * $foto: nombre del archivo nuevo, '' para quitarla o null para dejar la actual.
     */
    public function actualizarPerfil(int $id, string $nombre, string $email, ?string $telefono, ?string $foto): bool
    {
        $campos = "nombre = :nombre, email = :email, telefono = :telefono" . ($foto !== null ? ", foto = :foto" : "");
        $stmt = $this->db->prepare("UPDATE usuarios SET {$campos} WHERE id = :id");
        $parametros = [':id' => $id, ':nombre' => $nombre, ':email' => $email, ':telefono' => $telefono ?: null];
        if ($foto !== null) {
            $parametros[':foto'] = $foto !== '' ? $foto : null;
        }
        return $stmt->execute($parametros);
    }

    /**
     * Comprueba la contraseña actual de un usuario (para cambiarla)
     */
    public function claveCorrecta(int $id, string $clave): bool
    {
        $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $hash = $stmt->fetchColumn();
        return is_string($hash) && password_verify($clave, $hash);
    }

    /**
     * El propio usuario cambia su contraseña: deja de estar obligado a cambiarla
     */
    public function cambiarClave(int $id, string $nueva): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET password = :pass, debe_cambiar_clave = 0, clave_cambiada_en = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id, ':pass' => password_hash($nueva, PASSWORD_DEFAULT)]);
    }

    public function eliminar(int $id): bool
    {
        if ($id === 1) return false; // Proteger superadmin
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
