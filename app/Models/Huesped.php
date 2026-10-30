<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Huesped extends Model
{
    public function listarTodos(?string $busqueda = null): array
    {
        $sql = "SELECT h.*, 
                (SELECT COUNT(*) FROM reservas r WHERE r.huesped_id = h.id) as total_reservas,
                (SELECT MAX(fecha_entrada) FROM reservas r WHERE r.huesped_id = h.id) as ultima_estadia
                FROM huespedes h
                WHERE 1=1";
        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND (h.numero_documento LIKE :b1 OR h.nombres LIKE :b2 OR h.apellidos LIKE :b3 OR h.correo LIKE :b4 OR h.telefono LIKE :b5)";
            $term = "%" . trim($busqueda) . "%";
            $params[':b1'] = $term;
            $params[':b2'] = $term;
            $params[':b3'] = $term;
            $params[':b4'] = $term;
            $params[':b5'] = $term;
        }

        $sql .= " ORDER BY h.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM huespedes WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function buscarPorDocumento(string $documento): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM huespedes WHERE numero_documento = :doc LIMIT 1");
        $stmt->execute([':doc' => trim($documento)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function crear(array $datos): int|false
    {
        $sql = "INSERT INTO huespedes (tipo_documento, numero_documento, nombres, apellidos, correo, telefono, nacionalidad, direccion, notas, estado)
                VALUES (:tipo_doc, :num_doc, :nombres, :apellidos, :correo, :telefono, :nacionalidad, :direccion, :notas, :estado)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':tipo_doc'     => $datos['tipo_documento'] ?? 'DNI',
            ':num_doc'      => trim($datos['numero_documento'] ?? ''),
            ':nombres'      => trim($datos['nombres'] ?? ''),
            ':apellidos'    => trim($datos['apellidos'] ?? ''),
            ':correo'       => trim($datos['correo'] ?? ''),
            ':telefono'     => trim($datos['telefono'] ?? ''),
            ':nacionalidad' => trim($datos['nacionalidad'] ?? 'Perú'),
            ':direccion'    => trim($datos['direccion'] ?? ''),
            ':notas'        => trim($datos['notas'] ?? ''),
            ':estado'       => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);

        if ($ok) {
            $id = (int)$this->db->lastInsertId();
            // Mantener sincronizada la tabla clientes
            $stmtCli = $this->db->prepare("INSERT INTO clientes (nombres, apellidos, dni, correo, telefono, estado)
                                          VALUES (:nombres, :apellidos, :dni, :correo, :telefono, :estado)
                                          ON DUPLICATE KEY UPDATE nombres=VALUES(nombres), apellidos=VALUES(apellidos), correo=VALUES(correo)");
            $stmtCli->execute([
                ':nombres'   => trim($datos['nombres'] ?? ''),
                ':apellidos' => trim($datos['apellidos'] ?? ''),
                ':dni'       => trim($datos['numero_documento'] ?? ''),
                ':correo'    => trim($datos['correo'] ?? ''),
                ':telefono'  => trim($datos['telefono'] ?? ''),
                ':estado'    => isset($datos['estado']) ? (int)$datos['estado'] : 1
            ]);
            return $id;
        }
        return false;
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE huespedes 
                SET tipo_documento = :tipo_doc,
                    numero_documento = :num_doc,
                    nombres = :nombres,
                    apellidos = :apellidos,
                    correo = :correo,
                    telefono = :telefono,
                    nacionalidad = :nacionalidad,
                    direccion = :direccion,
                    notas = :notas,
                    estado = :estado
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'           => $id,
            ':tipo_doc'     => $datos['tipo_documento'] ?? 'DNI',
            ':num_doc'      => trim($datos['numero_documento'] ?? ''),
            ':nombres'      => trim($datos['nombres'] ?? ''),
            ':apellidos'    => trim($datos['apellidos'] ?? ''),
            ':correo'       => trim($datos['correo'] ?? ''),
            ':telefono'     => trim($datos['telefono'] ?? ''),
            ':nacionalidad' => trim($datos['nacionalidad'] ?? 'Perú'),
            ':direccion'    => trim($datos['direccion'] ?? ''),
            ':notas'        => trim($datos['notas'] ?? ''),
            ':estado'       => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM huespedes WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerHistorial(int $huespedId): array
    {
        $sql = "SELECT r.*, 
                GROUP_CONCAT(h.numero SEPARATOR ', ') as habitaciones,
                GROUP_CONCAT(th.nombre SEPARATOR ', ') as tipos
                FROM reservas r
                LEFT JOIN reserva_habitaciones rh ON rh.reserva_id = r.id
                LEFT JOIN habitaciones h ON h.id = rh.habitacion_id
                LEFT JOIN tipos_habitacion th ON th.id = h.tipo_id
                WHERE r.huesped_id = :id
                GROUP BY r.id
                ORDER BY r.fecha_entrada DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $huespedId]);
        return $stmt->fetchAll();
    }

    public function contarTotal(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM huespedes")->fetchColumn();
    }
}
