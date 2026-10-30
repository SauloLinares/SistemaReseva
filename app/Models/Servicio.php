<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Servicio extends Model
{
    public function listarTodos(): array
    {
        $sql = "SELECT s.*, 
                (SELECT COUNT(*) FROM reserva_servicios rs WHERE rs.servicio_id = s.id) as total_pedidos
                FROM servicios s 
                ORDER BY s.categoria ASC, s.nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function listarActivos(): array
    {
        $sql = "SELECT * FROM servicios WHERE estado = 1 ORDER BY categoria ASC, nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM servicios WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO servicios (nombre, categoria, descripcion, precio, estado)
                VALUES (:nombre, :categoria, :descripcion, :precio, :estado)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'      => trim($datos['nombre'] ?? ''),
            ':categoria'   => $datos['categoria'] ?? 'Restaurante',
            ':descripcion' => trim($datos['descripcion'] ?? ''),
            ':precio'      => (float)($datos['precio'] ?? 0),
            ':estado'      => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE servicios 
                SET nombre = :nombre, 
                    categoria = :categoria, 
                    descripcion = :descripcion, 
                    precio = :precio, 
                    estado = :estado 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'          => $id,
            ':nombre'      => trim($datos['nombre'] ?? ''),
            ':categoria'   => $datos['categoria'] ?? 'Restaurante',
            ':descripcion' => trim($datos['descripcion'] ?? ''),
            ':precio'      => (float)($datos['precio'] ?? 0),
            ':estado'      => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM servicios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function registrarConsumo(array $datos): bool
    {
        $this->db->beginTransaction();

        try {
            $servicio = $this->obtenerPorId((int)$datos['servicio_id']);
            if (!$servicio) {
                $this->db->rollBack();
                return false;
            }

            $cantidad = (int)($datos['cantidad'] ?? 1);
            if ($cantidad < 1) $cantidad = 1;
            $precio = (float)$servicio['precio'];
            $subtotal = $precio * $cantidad;
            $reservaId = (int)$datos['reserva_id'];
            $pagado = !empty($datos['pagado']) ? 1 : 0;

            $sql = "INSERT INTO reserva_servicios (reserva_id, servicio_id, cantidad, precio_unitario, subtotal, fecha_consumo, pagado, notas)
                    VALUES (:reserva_id, :servicio_id, :cantidad, :precio_unitario, :subtotal, :fecha_consumo, :pagado, :notas)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':reserva_id'       => $reservaId,
                ':servicio_id'      => $servicio['id'],
                ':cantidad'         => $cantidad,
                ':precio_unitario'  => $precio,
                ':subtotal'         => $subtotal,
                ':fecha_consumo'    => !empty($datos['fecha_consumo']) ? $datos['fecha_consumo'] : date('Y-m-d H:i:s'),
                ':pagado'           => $pagado,
                ':notas'            => trim($datos['notas'] ?? '')
            ]);

            // Si NO está pagado al momento, sumarlo al total y saldo de la reserva
            if (!$pagado) {
                $stmtUp = $this->db->prepare("UPDATE reservas SET total = total + :monto_total, saldo = saldo + :monto_saldo WHERE id = :id");
                $stmtUp->execute([
                    ':monto_total' => $subtotal,
                    ':monto_saldo' => $subtotal,
                    ':id'          => $reservaId
                ]);
            } else {
                // Registrar pago automático de consumo
                $reservaModel = new Reserva();
                $res = $reservaModel->obtenerPorId($reservaId);
                if ($res) {
                    $pagoModel = new Pago();
                    $pagoModel->registrarPago([
                        'reserva_id'         => $reservaId,
                        'huesped_id'         => $res['huesped_id'],
                        'tipo'               => 'consumo',
                        'metodo_pago'        => $datos['metodo_pago'] ?? 'Efectivo',
                        'monto'              => $subtotal,
                        'comprobante_tipo'   => 'Boleta',
                        'comprobante_numero' => $pagoModel->generarNumeroComprobante('Boleta'),
                        'fecha_pago'         => date('Y-m-d H:i:s'),
                        'notas'              => 'Consumo de ' . $servicio['nombre'] . ' x' . $cantidad
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function listarConsumos(?int $reservaId = null, ?int $huespedId = null): array
    {
        $sql = "SELECT rs.*, s.nombre as servicio_nombre, s.categoria,
                r.codigo_reserva,
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.numero_documento,
                GROUP_CONCAT(h.numero SEPARATOR ', ') as habitaciones
                FROM reserva_servicios rs
                INNER JOIN servicios s ON s.id = rs.servicio_id
                INNER JOIN reservas r ON r.id = rs.reserva_id
                INNER JOIN huespedes hu ON hu.id = r.huesped_id
                LEFT JOIN reserva_habitaciones rh ON rh.reserva_id = r.id
                LEFT JOIN habitaciones h ON h.id = rh.habitacion_id
                WHERE 1=1";
        $params = [];

        if ($reservaId) {
            $sql .= " AND rs.reserva_id = :rid";
            $params[':rid'] = $reservaId;
        }

        if ($huespedId) {
            $sql .= " AND r.huesped_id = :hid";
            $params[':hid'] = $huespedId;
        }

        $sql .= " GROUP BY rs.id ORDER BY rs.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
