<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Modelo Cliente
 * Representa la tabla 'clientes' en MySQL y contiene las operaciones CRUD con PDO.
 */
class Cliente extends Model
{
    /**
     * Obtiene todos los clientes ordenados por ID descendente
     */
    public function listarTodos(): array
    {
        $sql = "SELECT * FROM clientes ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un cliente por su ID
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT * FROM clientes WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $cliente = $stmt->fetch();
        return $cliente ?: null;
    }

    /**
     * Registra un nuevo cliente con sentencias preparadas para prevenir inyección SQL
     */
    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO clientes (nombres, apellidos, dni, correo, telefono, estado) 
                VALUES (:nombres, :apellidos, :dni, :correo, :telefono, :estado)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombres'   => trim($datos['nombres'] ?? ''),
            ':apellidos' => trim($datos['apellidos'] ?? ''),
            ':dni'       => trim($datos['dni'] ?? ''),
            ':correo'    => trim($datos['correo'] ?? ''),
            ':telefono'  => trim($datos['telefono'] ?? ''),
            ':estado'    => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    /**
     * Actualiza los datos de un cliente existente
     */
    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE clientes 
                SET nombres = :nombres, 
                    apellidos = :apellidos, 
                    dni = :dni, 
                    correo = :correo, 
                    telefono = :telefono, 
                    estado = :estado 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'        => $id,
            ':nombres'   => trim($datos['nombres'] ?? ''),
            ':apellidos' => trim($datos['apellidos'] ?? ''),
            ':dni'       => trim($datos['dni'] ?? ''),
            ':correo'    => trim($datos['correo'] ?? ''),
            ':telefono'  => trim($datos['telefono'] ?? ''),
            ':estado'    => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    /**
     * Elimina un cliente por su ID
     */
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM clientes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Cuenta el total de clientes registrados
     */
    public function contarTotal(): int
    {
        $sql = "SELECT COUNT(*) as total FROM clientes";
        $stmt = $this->db->query($sql);
        $resultado = $stmt->fetch();
        return (int)($resultado['total'] ?? 0);
    }

    /**
     * Cuenta los clientes activos
     */
    public function contarActivos(): int
    {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE estado = 1";
        $stmt = $this->db->query($sql);
        $resultado = $stmt->fetch();
        return (int)($resultado['total'] ?? 0);
    }
}
