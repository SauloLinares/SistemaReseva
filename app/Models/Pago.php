<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Pago extends Model
{
    public function listarTodos(): array
    {
        $sql = "SELECT p.*, r.codigo_reserva, 
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.numero_documento, hu.correo
                FROM pagos p
                INNER JOIN reservas r ON r.id = p.reserva_id
                INNER JOIN huespedes hu ON hu.id = p.huesped_id
                ORDER BY p.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT p.*, r.codigo_reserva, r.total as reserva_total, r.fecha_entrada, r.fecha_salida,
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.tipo_documento, hu.numero_documento, hu.correo, hu.telefono, hu.direccion
                FROM pagos p
                INNER JOIN reservas r ON r.id = p.reserva_id
                INNER JOIN huespedes hu ON hu.id = p.huesped_id
                WHERE p.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function registrarPago(array $datos): int|false
    {
        $this->db->beginTransaction();

        try {
            $monto = (float)($datos['monto'] ?? 0);
            $tipo = $datos['tipo'] ?? 'pago';
            $reservaId = (int)$datos['reserva_id'];
            $huespedId = (int)$datos['huesped_id'];

            $comprobanteTipo = $datos['comprobante_tipo'] ?? 'Boleta';
            $comprobanteNumero = !empty($datos['comprobante_numero']) ? trim($datos['comprobante_numero']) : $this->generarNumeroComprobante($comprobanteTipo);

            $sql = "INSERT INTO pagos (reserva_id, huesped_id, tipo, metodo_pago, monto, comprobante_tipo, comprobante_numero, fecha_pago, notas, usuario_id)
                    VALUES (:reserva_id, :huesped_id, :tipo, :metodo_pago, :monto, :comprobante_tipo, :comprobante_numero, :fecha_pago, :notas, :usuario_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':reserva_id'          => $reservaId,
                ':huesped_id'          => $huespedId,
                ':tipo'                => $tipo,
                ':metodo_pago'         => $datos['metodo_pago'] ?? 'Efectivo',
                ':monto'               => $monto,
                ':comprobante_tipo'    => $comprobanteTipo,
                ':comprobante_numero'  => $comprobanteNumero,
                ':fecha_pago'          => !empty($datos['fecha_pago']) ? $datos['fecha_pago'] : date('Y-m-d H:i:s'),
                ':notas'               => trim($datos['notas'] ?? ''),
                ':usuario_id'          => auth_user('id') ?: 1
            ]);

            $pagoId = (int)$this->db->lastInsertId();

            // Actualizar anticipo y saldo en la reserva
            // Sumar todos los pagos (menos devoluciones) de esta reserva
            $sqlSuma = "SELECT 
                        SUM(CASE WHEN tipo != 'devolucion' THEN monto ELSE 0 END) as pagado,
                        SUM(CASE WHEN tipo = 'devolucion' THEN monto ELSE 0 END) as devuelto
                        FROM pagos WHERE reserva_id = :id";
            $stmtSuma = $this->db->prepare($sqlSuma);
            $stmtSuma->execute([':id' => $reservaId]);
            $totales = $stmtSuma->fetch();

            $totalAbonado = ((float)$totales['pagado']) - ((float)$totales['devuelto']);

            $reservaStmt = $this->db->prepare("SELECT total FROM reservas WHERE id = :id");
            $reservaStmt->execute([':id' => $reservaId]);
            $reservaTotal = (float)$reservaStmt->fetchColumn();

            $nuevoSaldo = max(0, $reservaTotal - $totalAbonado);

            $stmtUpdate = $this->db->prepare("UPDATE reservas SET anticipo = :abonado, saldo = :saldo WHERE id = :id");
            $stmtUpdate->execute([
                ':abonado' => $totalAbonado,
                ':saldo'   => $nuevoSaldo,
                ':id'      => $reservaId
            ]);

            // Registrar auditoría
            $auditoriaModel = new Auditoria();
            $auditoriaModel->registrar('Pagos', 'Registrar', "Pago {$comprobanteNumero} por S/. {$monto} registrado para la reserva ID: {$reservaId}");

            $this->db->commit();
            return $pagoId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function generarNumeroComprobante(string $tipo): string
    {
        $prefijo = match($tipo) {
            'Factura'        => 'F001-',
            'Recibo de Caja' => 'RC-',
            'Nota de Crédito'=> 'NC01-',
            default          => 'B001-',
        };
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM pagos WHERE comprobante_tipo = :t");
        $stmt->execute([':t' => $tipo]);
        $conteo = ((int)$stmt->fetchColumn()) + 1;
        return $prefijo . sprintf("%06d", $conteo);
    }

    public function obtenerSaldosPendientes(): array
    {
        $sql = "SELECT r.*, 
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.numero_documento, hu.telefono,
                GROUP_CONCAT(h.numero SEPARATOR ', ') as habitaciones
                FROM reservas r
                INNER JOIN huespedes hu ON hu.id = r.huesped_id
                LEFT JOIN reserva_habitaciones rh ON rh.reserva_id = r.id
                LEFT JOIN habitaciones h ON h.id = rh.habitacion_id
                WHERE r.saldo > 0 AND r.estado NOT IN ('cancelada', 'finalizada')
                GROUP BY r.id
                ORDER BY r.fecha_entrada ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerAnticipos(): array
    {
        $sql = "SELECT p.*, r.codigo_reserva,
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre
                FROM pagos p
                INNER JOIN reservas r ON r.id = p.reserva_id
                INNER JOIN huespedes hu ON hu.id = p.huesped_id
                WHERE p.tipo = 'anticipo'
                ORDER BY p.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function registrarDevolucion(int $reservaId, float $monto, string $motivo, string $metodo = 'Efectivo'): bool
    {
        $reservaStmt = $this->db->prepare("SELECT huesped_id FROM reservas WHERE id = :id");
        $reservaStmt->execute([':id' => $reservaId]);
        $huespedId = (int)$reservaStmt->fetchColumn();
        if (!$huespedId) return false;

        $pagoId = $this->registrarPago([
            'reserva_id'         => $reservaId,
            'huesped_id'         => $huespedId,
            'tipo'               => 'devolucion',
            'metodo_pago'        => $metodo,
            'monto'              => $monto,
            'comprobante_tipo'   => 'Nota de Crédito',
            'comprobante_numero' => $this->generarNumeroComprobante('Nota de Crédito'),
            'fecha_pago'         => date('Y-m-d H:i:s'),
            'notas'              => 'Devolución de importe. Motivo: ' . $motivo
        ]);

        return ($pagoId !== false);
    }

    public function ingresosHoy(): float
    {
        $hoy = date('Y-m-d');
        $sql = "SELECT SUM(CASE WHEN tipo != 'devolucion' THEN monto ELSE -monto END) as total 
                FROM pagos WHERE DATE(fecha_pago) = :hoy";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':hoy' => $hoy]);
        return (float)($stmt->fetchColumn() ?: 0);
    }

    public function ingresosMes(): float
    {
        $mes = date('Y-m');
        $sql = "SELECT SUM(CASE WHEN tipo != 'devolucion' THEN monto ELSE -monto END) as total 
                FROM pagos WHERE DATE_FORMAT(fecha_pago, '%Y-%m') = :mes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':mes' => $mes]);
        return (float)($stmt->fetchColumn() ?: 0);
    }
}
