<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Rol extends Model
{
    public function listarTodos(): array
    {
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM usuarios u WHERE u.rol_id = r.id OR u.rol = r.nombre) as total_usuarios
                FROM roles r ORDER BY r.id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO roles (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'      => trim($datos['nombre'] ?? ''),
            ':descripcion' => trim($datos['descripcion'] ?? '')
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE roles SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'          => $id,
            ':nombre'      => trim($datos['nombre'] ?? ''),
            ':descripcion' => trim($datos['descripcion'] ?? '')
        ]);
    }

    public function eliminar(int $id): bool
    {
        if ($id === 1) return false; // Proteger admin
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerPermisos(int $rolId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM permisos WHERE rol_id = :id");
        $stmt->execute([':id' => $rolId]);
        return $stmt->fetchAll();
    }

    /**
     * Reemplaza los permisos del rol: ['modulo' => ['ver', 'crear', ...]]. Todo o nada:
     * si algo falla, el rol conserva los permisos que tenía.
     */
    public function guardarPermisos(int $rolId, array $permisos): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM permisos WHERE rol_id = :id")->execute([':id' => $rolId]);
            $stmt = $this->db->prepare("INSERT INTO permisos (rol_id, modulo, accion) VALUES (:rol_id, :modulo, :accion)");
            foreach ($permisos as $modulo => $acciones) {
                foreach ((array) $acciones as $accion) {
                    $stmt->execute([':rol_id' => $rolId, ':modulo' => $modulo, ':accion' => $accion]);
                }
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
