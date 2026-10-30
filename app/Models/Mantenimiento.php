<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Mantenimiento extends Model
{
    public function listarTodos(): array
    {
        $sql = "SELECT m.*, h.numero as habitacion_numero, h.piso, th.nombre as tipo_nombre
                FROM mantenimientos m
                INNER JOIN habitaciones h ON h.id = m.habitacion_id
                INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                ORDER BY m.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT m.*, h.numero as habitacion_numero, h.piso, th.nombre as tipo_nombre
                FROM mantenimientos m
                INNER JOIN habitaciones h ON h.id = m.habitacion_id
                INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                WHERE m.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO mantenimientos (habitacion_id, tipo, descripcion, responsable, fecha_inicio, fecha_fin, costo, estado)
                VALUES (:habitacion_id, :tipo, :descripcion, :responsable, :fecha_inicio, :fecha_fin, :costo, :estado)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':habitacion_id' => (int)$datos['habitacion_id'],
            ':tipo'          => $datos['tipo'] ?? 'limpieza',
            ':descripcion'   => trim($datos['descripcion'] ?? ''),
            ':responsable'   => trim($datos['responsable'] ?? ''),
            ':fecha_inicio'  => !empty($datos['fecha_inicio']) ? $datos['fecha_inicio'] : date('Y-m-d H:i:s'),
            ':fecha_fin'     => !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null,
            ':costo'         => (float)($datos['costo'] ?? 0),
            ':estado'        => $datos['estado'] ?? 'pendiente'
        ]);

        if ($ok) {
            // Actualizar estado de habitación a limpieza o mantenimiento si aplica
            $habModel = new Habitacion();
            $nuevoEstadoHab = ($datos['tipo'] === 'limpieza') ? 'limpieza' : 'mantenimiento';
            $habModel->actualizarEstado((int)$datos['habitacion_id'], $nuevoEstadoHab);
        }
        return $ok;
    }

    public function finalizar(int $id): bool
    {
        $item = $this->obtenerPorId($id);
        if (!$item) return false;

        $stmt = $this->db->prepare("UPDATE mantenimientos SET estado = 'finalizado', fecha_fin = NOW() WHERE id = :id");
        $ok = $stmt->execute([':id' => $id]);

        if ($ok) {
            // Poner la habitación disponible nuevamente
            $habModel = new Habitacion();
            $habModel->actualizarEstado((int)$item['habitacion_id'], 'disponible');
        }
        return $ok;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM mantenimientos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
