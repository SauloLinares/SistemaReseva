<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Caja extends Model
{
    /**
     * Obtiene el turno de caja que se encuentra actualmente abierto
     */
    public function obtenerTurnoActivo(): ?array
    {
        $sql = "SELECT t.*, u.nombre as usuario_nombre, u.email as usuario_login, COALESCE(r.nombre, u.rol) as rol_nombre
                FROM turnos_caja t
                INNER JOIN usuarios u ON u.id = t.usuario_id
                LEFT JOIN roles r ON r.id = u.rol_id
                WHERE t.estado = 'abierta'
                ORDER BY t.id DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Obtiene un turno específico por ID
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT t.*, u.nombre as usuario_nombre, u.email as usuario_login, COALESCE(r.nombre, u.rol) as rol_nombre
                FROM turnos_caja t
                INNER JOIN usuarios u ON u.id = t.usuario_id
                LEFT JOIN roles r ON r.id = u.rol_id
                WHERE t.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Abre un nuevo turno de caja con un fondo inicial
     */
    public function abrirTurno(int $usuarioId, float $montoInicial, ?string $obs = null): int|false
    {
        // Verificar que no haya un turno ya abierto
        if ($this->obtenerTurnoActivo()) {
            return false;
        }

        $sql = "INSERT INTO turnos_caja (usuario_id, fecha_apertura, monto_inicial, estado, observaciones_apertura)
                VALUES (:usuario_id, :fecha, :monto, 'abierta', :obs)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':fecha'      => date('Y-m-d H:i:s'),
            ':monto'      => $montoInicial,
            ':obs'        => trim($obs ?? '')
        ]);

        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Registra un movimiento de caja chica (Ingreso o Egreso)
     */
    public function registrarMovimiento(int $turnoId, int $usuarioId, string $tipo, string $concepto, float $monto, string $categoria = 'General', ?string $comprobante = null, string $metodoPago = 'Efectivo'): bool
    {
        $sql = "INSERT INTO caja_movimientos (turno_caja_id, usuario_id, tipo, concepto, categoria, monto, metodo_pago, comprobante)
                VALUES (:turno_id, :usuario_id, :tipo, :concepto, :categoria, :monto, :metodo_pago, :comprobante)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':turno_id'    => $turnoId,
            ':usuario_id'  => $usuarioId,
            ':tipo'        => $tipo,
            ':concepto'    => trim($concepto),
            ':categoria'   => trim($categoria ?: 'General'),
            ':monto'       => $monto,
            ':metodo_pago' => $metodoPago,
            ':comprobante' => trim($comprobante ?? '')
        ]);
    }

    /**
     * Elimina un movimiento de caja chica (solo si el turno sigue abierto)
     */
    public function eliminarMovimiento(int $movimientoId, int $turnoId): bool
    {
        $sql = "DELETE FROM caja_movimientos WHERE id = :id AND turno_caja_id = :turno_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $movimientoId, ':turno_id' => $turnoId]);
    }

    /**
     * Obtiene el resumen consolidado financiero y de arqueo de un turno
     */
    public function obtenerResumenTurno(int $turnoId): array
    {
        $turno = $this->obtenerPorId($turnoId);
        if (!$turno) return [];

        // 1. Cobros en pagos asociados al turno
        $sqlPagos = "SELECT p.*, r.codigo_reserva, CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre
                     FROM pagos p
                     LEFT JOIN reservas r ON r.id = p.reserva_id
                     LEFT JOIN huespedes hu ON hu.id = p.huesped_id
                     WHERE p.turno_caja_id = :turno_id
                     ORDER BY p.id ASC";
        $stmtPagos = $this->db->prepare($sqlPagos);
        $stmtPagos->execute([':turno_id' => $turnoId]);
        $pagos = $stmtPagos->fetchAll();

        // 2. Movimientos de caja chica (egresos e ingresos extra)
        $sqlMovs = "SELECT cm.*, u.nombre as usuario_nombre
                    FROM caja_movimientos cm
                    LEFT JOIN usuarios u ON u.id = cm.usuario_id
                    WHERE cm.turno_caja_id = :turno_id
                    ORDER BY cm.id ASC";
        $stmtMovs = $this->db->prepare($sqlMovs);
        $stmtMovs->execute([':turno_id' => $turnoId]);
        $movimientos = $stmtMovs->fetchAll();

        // Totales por método de pago
        $totalEfectivo = 0.0;
        $totalTarjetas = 0.0;
        $totalYapePlin = 0.0;
        $totalTransferencias = 0.0;
        $totalDevoluciones = 0.0;

        foreach ($pagos as $p) {
            $m = (float)$p['monto'];
            if ($p['tipo'] === 'devolucion') {
                $totalDevoluciones += $m;
                continue;
            }

            switch ($p['metodo_pago']) {
                case 'Efectivo':
                    $totalEfectivo += $m;
                    break;
                case 'Tarjeta de Crédito':
                case 'Tarjeta de Débito':
                    $totalTarjetas += $m;
                    break;
                case 'Yape/Plin':
                    $totalYapePlin += $m;
                    break;
                case 'Transferencia Bancaria':
                    $totalTransferencias += $m;
                    break;
                default:
                    $totalEfectivo += $m;
                    break;
            }
        }

        // Totales caja chica
        $totalIngresosExtra = 0.0;
        $totalEgresos = 0.0;

        foreach ($movimientos as $mov) {
            $m = (float)$mov['monto'];
            if ($mov['tipo'] === 'ingreso') {
                $totalIngresosExtra += $m;
            } elseif ($mov['tipo'] === 'egreso') {
                $totalEgresos += $m;
            }
        }

        $fondoInicial = (float)$turno['monto_inicial'];
        $montoEsperadoEfectivo = $fondoInicial + $totalEfectivo + $totalIngresosExtra - $totalEgresos - $totalDevoluciones;
        $totalVentasDigitales = $totalTarjetas + $totalYapePlin + $totalTransferencias;
        $totalRecaudadoGeneral = $totalEfectivo + $totalVentasDigitales;

        return [
            'turno'                  => $turno,
            'pagos'                  => $pagos,
            'movimientos'            => $movimientos,
            'fondo_inicial'          => $fondoInicial,
            'total_efectivo'         => $totalEfectivo,
            'total_tarjetas'         => $totalTarjetas,
            'total_yape_plin'        => $totalYapePlin,
            'total_transferencias'   => $totalTransferencias,
            'total_digital'          => $totalVentasDigitales,
            'total_devoluciones'     => $totalDevoluciones,
            'total_ingresos_extra'   => $totalIngresosExtra,
            'total_egresos'          => $totalEgresos,
            'total_recaudado'        => $totalRecaudadoGeneral,
            'monto_esperado_efectivo'=> max(0, $montoEsperadoEfectivo)
        ];
    }

    /**
     * Cierra el turno de caja registrando el arqueo físico
     */
    public function cerrarTurno(int $turnoId, float $montoRealEfectivo, ?string $obs = null): bool
    {
        $resumen = $this->obtenerResumenTurno($turnoId);
        if (empty($resumen)) return false;

        $montoEsperado = (float)$resumen['monto_esperado_efectivo'];
        $diferencia = $montoRealEfectivo - $montoEsperado;

        $sql = "UPDATE turnos_caja SET
                fecha_cierre = :fecha_cierre,
                monto_esperado_efectivo = :esperado,
                monto_real_efectivo = :real_m,
                diferencia = :diferencia,
                total_ventas_efectivo = :ventas_efectivo,
                total_ventas_digital = :ventas_digital,
                total_ingresos_extra = :ingresos_extra,
                total_egresos = :egresos,
                estado = 'cerrada',
                observaciones_cierre = :obs
                WHERE id = :id AND estado = 'abierta'";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':fecha_cierre'     => date('Y-m-d H:i:s'),
            ':esperado'         => $montoEsperado,
            ':real_m'           => $montoRealEfectivo,
            ':diferencia'       => $diferencia,
            ':ventas_efectivo'  => $resumen['total_efectivo'],
            ':ventas_digital'   => $resumen['total_digital'],
            ':ingresos_extra'   => $resumen['total_ingresos_extra'],
            ':egresos'          => $resumen['total_egresos'],
            ':obs'              => trim($obs ?? ''),
            ':id'               => $turnoId
        ]);
    }

    /**
     * Listado histórico de turnos de caja
     */
    public function listarHistorial(): array
    {
        $sql = "SELECT t.*, u.nombre as usuario_nombre, u.email as usuario_login
                FROM turnos_caja t
                INNER JOIN usuarios u ON u.id = t.usuario_id
                ORDER BY t.id DESC";
        return $this->db->query($sql)->fetchAll();
    }
}
