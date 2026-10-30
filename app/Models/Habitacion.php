<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Habitacion extends Model
{
    public function listarTodos(?string $filtroEstado = null, ?int $filtroTipo = null): array
    {
        $sql = "SELECT h.*, th.nombre as tipo_nombre, th.capacidad, th.caracteristicas
                FROM habitaciones h
                INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                WHERE 1=1";
        $params = [];

        if (!empty($filtroEstado)) {
            $sql .= " AND h.estado = :estado";
            $params[':estado'] = $filtroEstado;
        }

        if (!empty($filtroTipo)) {
            $sql .= " AND h.tipo_id = :tipo_id";
            $params[':tipo_id'] = $filtroTipo;
        }

        $sql .= " ORDER BY h.piso ASC, h.numero ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT h.*, th.nombre as tipo_nombre, th.capacidad, th.caracteristicas, th.precio_base
                FROM habitaciones h
                INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                WHERE h.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function obtenerPorNumero(string $numero): ?array
    {
        $sql = "SELECT * FROM habitaciones WHERE numero = :numero LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':numero' => trim($numero)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO habitaciones (numero, tipo_id, piso, precio_diario, estado, descripcion)
                VALUES (:numero, :tipo_id, :piso, :precio_diario, :estado, :descripcion)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':numero'        => trim($datos['numero'] ?? ''),
            ':tipo_id'       => (int)($datos['tipo_id'] ?? 1),
            ':piso'          => trim($datos['piso'] ?? '1'),
            ':precio_diario' => (float)($datos['precio_diario'] ?? 0),
            ':estado'        => $datos['estado'] ?? 'disponible',
            ':descripcion'   => trim($datos['descripcion'] ?? '')
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE habitaciones 
                SET numero = :numero, 
                    tipo_id = :tipo_id, 
                    piso = :piso, 
                    precio_diario = :precio_diario, 
                    estado = :estado, 
                    descripcion = :descripcion 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'            => $id,
            ':numero'        => trim($datos['numero'] ?? ''),
            ':tipo_id'       => (int)($datos['tipo_id'] ?? 1),
            ':piso'          => trim($datos['piso'] ?? '1'),
            ':precio_diario' => (float)($datos['precio_diario'] ?? 0),
            ':estado'        => $datos['estado'] ?? 'disponible',
            ':descripcion'   => trim($datos['descripcion'] ?? '')
        ]);
    }

    public function actualizarEstado(int $id, string $nuevoEstado): bool
    {
        $stmt = $this->db->prepare("UPDATE habitaciones SET estado = :estado WHERE id = :id");
        return $stmt->execute([
            ':id'     => $id,
            ':estado' => $nuevoEstado
        ]);
    }

    public function actualizarTarifa(int $id, float $nuevaTarifa): bool
    {
        $stmt = $this->db->prepare("UPDATE habitaciones SET precio_diario = :precio WHERE id = :id");
        return $stmt->execute([
            ':id'     => $id,
            ':precio' => $nuevaTarifa
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM habitaciones WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function contarPorEstado(): array
    {
        $sql = "SELECT estado, COUNT(*) as cantidad FROM habitaciones GROUP BY estado";
        $filas = $this->db->query($sql)->fetchAll();
        $conteo = [
            'total'         => 0,
            'disponible'    => 0,
            'ocupada'       => 0,
            'limpieza'      => 0,
            'mantenimiento' => 0
        ];
        foreach ($filas as $fila) {
            $conteo[$fila['estado']] = (int)$fila['cantidad'];
            $conteo['total'] += (int)$fila['cantidad'];
        }
        return $conteo;
    }
}
