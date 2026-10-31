<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TipoHabitacion extends Model
{
    public function listarTodos(): array
    {
        $sql = "SELECT th.*, COUNT(h.id) as total_habitaciones 
                FROM tipos_habitacion th
                LEFT JOIN habitaciones h ON h.tipo_id = th.id
                GROUP BY th.id
                ORDER BY th.id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function listarActivos(): array
    {
        $sql = "SELECT * FROM tipos_habitacion WHERE estado = 1 ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tipos_habitacion WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO tipos_habitacion (nombre, descripcion, capacidad, precio_base, caracteristicas, estado)
                VALUES (:nombre, :descripcion, :capacidad, :precio_base, :caracteristicas, :estado)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'          => trim($datos['nombre'] ?? ''),
            ':descripcion'     => trim($datos['descripcion'] ?? ''),
            ':capacidad'       => (int)($datos['capacidad'] ?? 1),
            ':precio_base'     => (float)($datos['precio_base'] ?? 0),
            ':caracteristicas' => trim($datos['caracteristicas'] ?? ''),
            ':estado'          => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE tipos_habitacion 
                SET nombre = :nombre, 
                    descripcion = :descripcion, 
                    capacidad = :capacidad, 
                    precio_base = :precio_base, 
                    caracteristicas = :caracteristicas, 
                    estado = COALESCE(:estado, estado) 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'              => $id,
            ':nombre'          => trim($datos['nombre'] ?? ''),
            ':descripcion'     => trim($datos['descripcion'] ?? ''),
            ':capacidad'       => (int)($datos['capacidad'] ?? 1),
            ':precio_base'     => (float)($datos['precio_base'] ?? 0),
            ':caracteristicas' => trim($datos['caracteristicas'] ?? ''),
            ':estado'          => isset($datos['estado']) ? (int)$datos['estado'] : null // sin campo: se conserva
        ]);
    }

    public function eliminar(int $id): bool
    {
        // Verificar si tiene habitaciones asignadas
        $check = $this->db->prepare("SELECT COUNT(*) FROM habitaciones WHERE tipo_id = :id");
        $check->execute([':id' => $id]);
        if ((int)$check->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM tipos_habitacion WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Activa o desactiva el registro (interruptor de la tabla)
     */
    public function cambiarEstado(int $id, bool $activo): bool
    {
        $stmt = $this->db->prepare("UPDATE tipos_habitacion SET estado = :estado WHERE id = :id");
        $stmt->execute([':estado' => $activo ? 1 : 0, ':id' => $id]);
        return $stmt->rowCount() > 0 || $this->db->query("SELECT COUNT(*) FROM tipos_habitacion WHERE id = " . (int)$id)->fetchColumn() > 0;
    }
}
