<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Reporte extends Model
{
    public function reporteReservas(?string $desde = null, ?string $hasta = null, ?string $estado = null): array
    {
        $sql = "SELECT r.*, 
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.numero_documento,
                GROUP_CONCAT(h.numero SEPARATOR ', ') as habitaciones
                FROM reservas r
                INNER JOIN huespedes hu ON hu.id = r.huesped_id
                LEFT JOIN reserva_habitaciones rh ON rh.reserva_id = r.id
                LEFT JOIN habitaciones h ON h.id = rh.habitacion_id
                WHERE 1=1";
        $params = [];

        if (!empty($desde)) {
            $sql .= " AND r.fecha_entrada >= :desde";
            $params[':desde'] = $desde;
        }
        if (!empty($hasta)) {
            $sql .= " AND r.fecha_salida <= :hasta";
            $params[':hasta'] = $hasta;
        }
        if (!empty($estado)) {
            $sql .= " AND r.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " GROUP BY r.id ORDER BY r.fecha_entrada DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function reporteOcupacion(?string $mes = null): array
    {
        if (empty($mes)) $mes = date('Y-m');

        // Total de habitaciones en el hotel
        $totalHabitaciones = (int)$this->db->query("SELECT COUNT(*) FROM habitaciones")->fetchColumn();
        if ($totalHabitaciones === 0) $totalHabitaciones = 1;

        // Días del mes
        $primerDia = $mes . '-01';
        $diasEnMes = (int)date('t', strtotime($primerDia));
        $ultimoDia = date('Y-m-t', strtotime($primerDia));

        // Reservas activas en este mes
        $sql = "SELECT r.fecha_entrada, r.fecha_salida, COUNT(rh.habitacion_id) as habs_reservadas
                FROM reservas r
                INNER JOIN reserva_habitaciones rh ON rh.reserva_id = r.id
                WHERE r.estado NOT IN ('cancelada')
                AND r.fecha_entrada <= :ultimoDia AND r.fecha_salida >= :primerDia
                GROUP BY r.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':primerDia' => $primerDia, ':ultimoDia' => $ultimoDia]);
        $reservas = $stmt->fetchAll();

        // Ocupación diaria calculada
        $ocupacionDiaria = [];
        for ($d = 1; $d <= $diasEnMes; $d++) {
            $fechaActual = sprintf("%s-%02d", $mes, $d);
            $ocupadas = 0;
            foreach ($reservas as $res) {
                if ($fechaActual >= $res['fecha_entrada'] && $fechaActual < $res['fecha_salida']) {
                    $ocupadas += (int)$res['habs_reservadas'];
                }
            }
            $ocupadas = min($ocupadas, $totalHabitaciones);
            $porcentaje = round(($ocupadas / $totalHabitaciones) * 100, 1);
            $ocupacionDiaria[] = [
                'dia'        => $d,
                'fecha'      => $fechaActual,
                'ocupadas'   => $ocupadas,
                'libres'     => max(0, $totalHabitaciones - $ocupadas),
                'porcentaje' => $porcentaje
            ];
        }

        return [
            'mes'               => $mes,
            'totalHabitaciones' => $totalHabitaciones,
            'dias'              => $ocupacionDiaria
        ];
    }

    public function reporteIngresos(?string $desde = null, ?string $hasta = null): array
    {
        $sql = "SELECT p.*, r.codigo_reserva,
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre
                FROM pagos p
                INNER JOIN reservas r ON r.id = p.reserva_id
                INNER JOIN huespedes hu ON hu.id = p.huesped_id
                WHERE 1=1";
        $params = [];

        if (!empty($desde)) {
            $sql .= " AND DATE(p.fecha_pago) >= :desde";
            $params[':desde'] = $desde;
        }
        if (!empty($hasta)) {
            $sql .= " AND DATE(p.fecha_pago) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $sql .= " ORDER BY p.fecha_pago DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $pagos = $stmt->fetchAll();

        // Agrupación por método de pago
        $porMetodo = [];
        $totalIngresos = 0;
        foreach ($pagos as $p) {
            $monto = ($p['tipo'] === 'devolucion') ? -((float)$p['monto']) : ((float)$p['monto']);
            $metodo = $p['metodo_pago'] ?: 'Otro';
            $porMetodo[$metodo] = ($porMetodo[$metodo] ?? 0) + $monto;
            $totalIngresos += $monto;
        }

        return [
            'pagos'         => $pagos,
            'porMetodo'     => $porMetodo,
            'totalIngresos' => $totalIngresos
        ];
    }

    public function reporteHuespedes(): array
    {
        $sql = "SELECT hu.*, 
                COUNT(r.id) as total_reservas,
                IFNULL(SUM(r.total), 0) as total_gastado,
                MAX(r.fecha_entrada) as ultima_visita
                FROM huespedes hu
                LEFT JOIN reservas r ON r.huesped_id = hu.id AND r.estado != 'cancelada'
                GROUP BY hu.id
                ORDER BY total_reservas DESC, total_gastado DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function reporteCancelaciones(?string $desde = null, ?string $hasta = null): array
    {
        $sql = "SELECT r.*, 
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.numero_documento, hu.telefono
                FROM reservas r
                INNER JOIN huespedes hu ON hu.id = r.huesped_id
                WHERE r.estado = 'cancelada'";
        $params = [];

        if (!empty($desde)) {
            $sql .= " AND r.fecha_entrada >= :desde";
            $params[':desde'] = $desde;
        }
        if (!empty($hasta)) {
            $sql .= " AND r.fecha_salida <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $sql .= " ORDER BY r.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function habitacionesMasSolicitadas(): array
    {
        $sql = "SELECT h.numero, h.piso, th.nombre as tipo_nombre, h.precio_diario,
                -- Solo reservas no canceladas: r queda NULL para las canceladas (condición en el JOIN)
                COUNT(r.id) as total_veces_reservada,
                IFNULL(SUM(CASE WHEN r.id IS NOT NULL THEN rh.noches END), 0) as total_noches,
                IFNULL(SUM(CASE WHEN r.id IS NOT NULL THEN rh.subtotal END), 0) as total_recaudado
                FROM habitaciones h
                INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                LEFT JOIN reserva_habitaciones rh ON rh.habitacion_id = h.id
                LEFT JOIN reservas r ON r.id = rh.reserva_id AND r.estado != 'cancelada'
                GROUP BY h.id
                ORDER BY total_veces_reservada DESC, total_noches DESC";
        return $this->db->query($sql)->fetchAll();
    }
}
