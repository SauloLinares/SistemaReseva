<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Reserva extends Model
{
    public function listarTodos(?string $filtroEstado = null, ?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        $sql = "SELECT r.*, 
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.numero_documento, hu.telefono as huesped_telefono, hu.correo as huesped_correo,
                GROUP_CONCAT(h.numero SEPARATOR ', ') as habitaciones_asignadas,
                GROUP_CONCAT(CONCAT(h.numero, ' (', th.nombre, ')') SEPARATOR ', ') as habitaciones_detalle
                FROM reservas r
                INNER JOIN huespedes hu ON hu.id = r.huesped_id
                LEFT JOIN reserva_habitaciones rh ON rh.reserva_id = r.id
                LEFT JOIN habitaciones h ON h.id = rh.habitacion_id
                LEFT JOIN tipos_habitacion th ON th.id = h.tipo_id
                WHERE 1=1";
        $params = [];

        if (!empty($filtroEstado)) {
            $sql .= " AND r.estado = :estado";
            $params[':estado'] = $filtroEstado;
        }

        if (!empty($fechaDesde)) {
            $sql .= " AND r.fecha_entrada >= :fdesde";
            $params[':fdesde'] = $fechaDesde;
        }

        if (!empty($fechaHasta)) {
            $sql .= " AND r.fecha_salida <= :fhasta";
            $params[':fhasta'] = $fechaHasta;
        }

        $sql .= " GROUP BY r.id ORDER BY r.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT r.*, 
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.tipo_documento, hu.numero_documento, hu.telefono as huesped_telefono, hu.correo as huesped_correo,
                hu.nacionalidad, hu.direccion
                FROM reservas r
                INNER JOIN huespedes hu ON hu.id = r.huesped_id
                WHERE r.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        // Obtener habitaciones asignadas
        $sqlHab = "SELECT rh.*, h.numero, h.piso, th.nombre as tipo_nombre, th.capacidad
                   FROM reserva_habitaciones rh
                   INNER JOIN habitaciones h ON h.id = rh.habitacion_id
                   INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                   WHERE rh.reserva_id = :id";
        $stmtHab = $this->db->prepare($sqlHab);
        $stmtHab->execute([':id' => $id]);
        $row['habitaciones'] = $stmtHab->fetchAll();

        // Obtener pagos
        $sqlPagos = "SELECT * FROM pagos WHERE reserva_id = :id ORDER BY id ASC";
        $stmtPagos = $this->db->prepare($sqlPagos);
        $stmtPagos->execute([':id' => $id]);
        $row['pagos'] = $stmtPagos->fetchAll();

        // Obtener consumos de servicios
        $sqlServ = "SELECT rs.*, s.nombre as servicio_nombre, s.categoria
                    FROM reserva_servicios rs
                    INNER JOIN servicios s ON s.id = rs.servicio_id
                    WHERE rs.reserva_id = :id ORDER BY rs.id ASC";
        $stmtServ = $this->db->prepare($sqlServ);
        $stmtServ->execute([':id' => $id]);
        $row['servicios'] = $stmtServ->fetchAll();

        return $row;
    }

    /**
     * Comprueba disponibilidad exacta de habitaciones en un rango de fechas.
     * Importante: una habitación no está disponible si se superpone con otra reserva activa
     * (reserva.fecha_entrada < :salida AND reserva.fecha_salida > :entrada)
     */
    public function obtenerHabitacionesDisponibles(string $fechaEntrada, string $fechaSalida, ?int $tipoId = null, ?int $ignorarReservaId = null): array
    {
        $sql = "SELECT h.*, th.nombre as tipo_nombre, th.capacidad, th.caracteristicas
                FROM habitaciones h
                INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                WHERE h.estado != 'mantenimiento'
                AND h.id NOT IN (
                    SELECT rh.habitacion_id
                    FROM reserva_habitaciones rh
                    INNER JOIN reservas r ON r.id = rh.reserva_id
                    WHERE r.estado NOT IN ('cancelada', 'finalizada')
                    AND r.fecha_entrada < :fecha_salida
                    AND r.fecha_salida > :fecha_entrada";

        $params = [
            ':fecha_entrada' => $fechaEntrada,
            ':fecha_salida'  => $fechaSalida,
        ];

        if ($ignorarReservaId !== null) {
            $sql .= " AND r.id != :ignorar_id";
            $params[':ignorar_id'] = $ignorarReservaId;
        }

        $sql .= " )";

        if (!empty($tipoId)) {
            $sql .= " AND h.tipo_id = :tipo_id";
            $params[':tipo_id'] = $tipoId;
        }

        $sql .= " ORDER BY h.piso ASC, h.numero ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Valida si una habitación específica está disponible en un rango de fechas
     */
    public function habitacionEstaDisponible(int $habitacionId, string $fechaEntrada, string $fechaSalida, ?int $ignorarReservaId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM reserva_habitaciones rh
                INNER JOIN reservas r ON r.id = rh.reserva_id
                WHERE rh.habitacion_id = :hab_id
                AND r.estado NOT IN ('cancelada', 'finalizada')
                AND r.fecha_entrada < :fecha_salida
                AND r.fecha_salida > :fecha_entrada";
        
        $params = [
            ':hab_id'        => $habitacionId,
            ':fecha_entrada' => $fechaEntrada,
            ':fecha_salida'  => $fechaSalida,
        ];

        if ($ignorarReservaId !== null) {
            $sql .= " AND r.id != :ignorar_id";
            $params[':ignorar_id'] = $ignorarReservaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $ocupada = (int)$stmt->fetchColumn();

        return ($ocupada === 0);
    }

    public function generarCodigoReserva(): string
    {
        $anio = date('Y');
        $stmt = $this->db->query("SELECT MAX(id) FROM reservas");
        $siguiente = ((int)$stmt->fetchColumn()) + 1;
        return sprintf("RES-%s-%04d", $anio, $siguiente);
    }

    public function crear(array $datos, array $habitacionesIds): int|false
    {
        $this->db->beginTransaction();

        try {
            $codigo = $this->generarCodigoReserva();
            $dias = (int)ceil((strtotime($datos['fecha_salida']) - strtotime($datos['fecha_entrada'])) / 86400);
            if ($dias < 1) $dias = 1;

            $totalHabitaciones = 0;
            $detallesHabitaciones = [];

            // Calcular monto por cada habitación y validar superposición
            $habModel = new Habitacion();
            foreach ($habitacionesIds as $habId) {
                if (!$this->habitacionEstaDisponible((int)$habId, $datos['fecha_entrada'], $datos['fecha_salida'])) {
                    $this->db->rollBack();
                    return false;
                }
                $hab = $habModel->obtenerPorId((int)$habId);
                if (!$hab) continue;
                $precioNoche = (float)$hab['precio_diario'];
                $subtotal = $precioNoche * $dias;
                $totalHabitaciones += $subtotal;
                $detallesHabitaciones[] = [
                    'habitacion_id' => (int)$habId,
                    'precio_noche'  => $precioNoche,
                    'noches'        => $dias,
                    'subtotal'      => $subtotal
                ];
            }

            // Calcular servicios adicionales si fueron seleccionados
            $totalServicios = 0;
            $detallesServicios = [];
            $serviciosSeleccionados = $datos['servicios'] ?? [];
            if (!empty($serviciosSeleccionados) && is_array($serviciosSeleccionados)) {
                $srvModel = new Servicio();
                foreach ($serviciosSeleccionados as $srvId => $cant) {
                    $cant = (int)$cant;
                    if ($cant <= 0) continue;
                    $srv = $srvModel->obtenerPorId((int)$srvId);
                    if (!$srv) continue;
                    $precioUnit = (float)$srv['precio'];
                    $subtServ = $precioUnit * $cant;
                    $totalServicios += $subtServ;
                    $detallesServicios[] = [
                        'servicio_id'     => (int)$srvId,
                        'cantidad'        => $cant,
                        'precio_unitario' => $precioUnit,
                        'subtotal'        => $subtServ
                    ];
                }
            }

            // Descuentos y totales
            $descuento = max(0, (float)($datos['descuento'] ?? 0));
            $motivoDescuento = trim($datos['motivo_descuento'] ?? '');

            $totalBruto = $totalHabitaciones + $totalServicios;
            $total = max(0, $totalBruto - $descuento);

            $anticipo = (float)($datos['anticipo'] ?? 0);
            $saldo = max(0, $total - $anticipo);

            // Preparar notas incluyendo preferencias de llegada si existen
            $notas = trim($datos['notas'] ?? '');
            if (!empty($datos['early_checkin'])) {
                $notas = "[Early Check-in Solicitado] " . $notas;
            }
            if (!empty($datos['late_checkout'])) {
                $notas = "[Late Check-out Solicitado] " . $notas;
            }

            $sql = "INSERT INTO reservas (codigo_reserva, huesped_id, fecha_entrada, fecha_salida, noches, adultos, ninos, estado, total, descuento, motivo_descuento, anticipo, saldo, metodo_pago, origen, notas, usuario_id)
                    VALUES (:codigo, :huesped_id, :fecha_entrada, :fecha_salida, :noches, :adultos, :ninos, :estado, :total, :descuento, :motivo_descuento, :anticipo, :saldo, :metodo_pago, :origen, :notas, :usuario_id)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':codigo'           => $codigo,
                ':huesped_id'       => (int)$datos['huesped_id'],
                ':fecha_entrada'    => $datos['fecha_entrada'],
                ':fecha_salida'     => $datos['fecha_salida'],
                ':noches'           => $dias,
                ':adultos'          => (int)($datos['adultos'] ?? 1),
                ':ninos'            => (int)($datos['ninos'] ?? 0),
                ':estado'           => $datos['estado'] ?? 'confirmada',
                ':total'            => $total,
                ':descuento'        => $descuento,
                ':motivo_descuento' => $motivoDescuento,
                ':anticipo'         => $anticipo,
                ':saldo'            => $saldo,
                ':metodo_pago'      => $datos['metodo_pago'] ?? 'Efectivo',
                ':origen'           => $datos['origen'] ?? 'manual',
                ':notas'            => trim($notas),
                ':usuario_id'       => auth_user('id') ?: 1
            ]);

            $reservaId = (int)$this->db->lastInsertId();

            // Insertar habitaciones
            $stmtDetalle = $this->db->prepare("INSERT INTO reserva_habitaciones (reserva_id, habitacion_id, precio_noche, noches, subtotal)
                                                VALUES (:reserva_id, :habitacion_id, :precio_noche, :noches, :subtotal)");
            foreach ($detallesHabitaciones as $det) {
                $stmtDetalle->execute([
                    ':reserva_id'    => $reservaId,
                    ':habitacion_id' => $det['habitacion_id'],
                    ':precio_noche'  => $det['precio_noche'],
                    ':noches'        => $det['noches'],
                    ':subtotal'      => $det['subtotal']
                ]);
            }

            // Insertar servicios contratados
            if (!empty($detallesServicios)) {
                $stmtSrv = $this->db->prepare("INSERT INTO reserva_servicios (reserva_id, servicio_id, cantidad, precio_unitario, subtotal, fecha_consumo, pagado, notas)
                                               VALUES (:reserva_id, :servicio_id, :cantidad, :precio_unitario, :subtotal, :fecha_consumo, 0, 'Contratado en reserva')");
                foreach ($detallesServicios as $sDet) {
                    $stmtSrv->execute([
                        ':reserva_id'       => $reservaId,
                        ':servicio_id'      => $sDet['servicio_id'],
                        ':cantidad'         => $sDet['cantidad'],
                        ':precio_unitario'  => $sDet['precio_unitario'],
                        ':subtotal'         => $sDet['subtotal'],
                        ':fecha_consumo'    => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // Si hubo anticipo > 0, registrar el pago automáticamente
            if ($anticipo > 0) {
                $pagoModel = new Pago();
                $pagoModel->registrarPago([
                    'reserva_id'         => $reservaId,
                    'huesped_id'         => (int)$datos['huesped_id'],
                    'tipo'               => 'anticipo',
                    'metodo_pago'        => $datos['metodo_pago'] ?? 'Efectivo',
                    'monto'              => $anticipo,
                    'comprobante_tipo'   => 'Recibo de Caja',
                    'comprobante_numero' => 'REC-' . date('Ymd') . '-' . sprintf("%04d", $reservaId),
                    'fecha_pago'         => date('Y-m-d H:i:s'),
                    'notas'              => 'Anticipo inicial al registrar reserva ' . $codigo
                ]);
            }

            // Registrar auditoría
            $auditoriaModel = new Auditoria();
            $auditoriaModel->registrar('Reservas', 'Crear', "Nueva reserva registrada con código {$codigo} para {$dias} noches. Total: S/. {$total}");

            $this->db->commit();
            return $reservaId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function actualizar(int $id, array $datos, array $habitacionesIds = []): bool
    {
        $this->db->beginTransaction();

        try {
            $reserva = $this->obtenerPorId($id);
            if (!$reserva) {
                $this->db->rollBack();
                return false;
            }

            $dias = (int)ceil((strtotime($datos['fecha_salida']) - strtotime($datos['fecha_entrada'])) / 86400);
            if ($dias < 1) $dias = 1;

            if (!empty($habitacionesIds)) {
                foreach ($habitacionesIds as $habId) {
                    if (!$this->habitacionEstaDisponible((int)$habId, $datos['fecha_entrada'], $datos['fecha_salida'], $id)) {
                        $this->db->rollBack();
                        return false;
                    }
                }
            }

            $total = (float)($datos['total'] ?? $reserva['total']);
            $anticipo = (float)($datos['anticipo'] ?? $reserva['anticipo']);
            $saldo = max(0, $total - $anticipo);

            $sql = "UPDATE reservas 
                    SET huesped_id = :huesped_id,
                        fecha_entrada = :fecha_entrada,
                        fecha_salida = :fecha_salida,
                        noches = :noches,
                        adultos = :adultos,
                        ninos = :ninos,
                        estado = :estado,
                        total = :total,
                        anticipo = :anticipo,
                        saldo = :saldo,
                        metodo_pago = :metodo_pago,
                        notas = :notas
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'            => $id,
                ':huesped_id'    => (int)$datos['huesped_id'],
                ':fecha_entrada' => $datos['fecha_entrada'],
                ':fecha_salida'  => $datos['fecha_salida'],
                ':noches'        => $dias,
                ':adultos'       => (int)($datos['adultos'] ?? 1),
                ':ninos'         => (int)($datos['ninos'] ?? 0),
                ':estado'        => $datos['estado'] ?? $reserva['estado'],
                ':total'         => $total,
                ':anticipo'      => $anticipo,
                ':saldo'         => $saldo,
                ':metodo_pago'   => $datos['metodo_pago'] ?? $reserva['metodo_pago'],
                ':notas'         => trim($datos['notas'] ?? '')
            ]);

            if (!empty($habitacionesIds)) {
                // Actualizar habitaciones asignadas
                $this->db->prepare("DELETE FROM reserva_habitaciones WHERE reserva_id = :id")->execute([':id' => $id]);
                $habModel = new Habitacion();
                $stmtDetalle = $this->db->prepare("INSERT INTO reserva_habitaciones (reserva_id, habitacion_id, precio_noche, noches, subtotal)
                                                    VALUES (:reserva_id, :habitacion_id, :precio_noche, :noches, :subtotal)");
                foreach ($habitacionesIds as $habId) {
                    $hab = $habModel->obtenerPorId((int)$habId);
                    $precioNoche = $hab ? (float)$hab['precio_diario'] : 0;
                    $stmtDetalle->execute([
                        ':reserva_id'    => $id,
                        ':habitacion_id' => $habId,
                        ':precio_noche'  => $precioNoche,
                        ':noches'        => $dias,
                        ':subtotal'      => $precioNoche * $dias
                    ]);
                }
            }

            $auditoriaModel = new Auditoria();
            $auditoriaModel->registrar('Reservas', 'Modificar', "Reserva {$reserva['codigo_reserva']} actualizada.");

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function cancelar(int $id, string $motivo = ''): bool
    {
        $reserva = $this->obtenerPorId($id);
        if (!$reserva) return false;

        $stmt = $this->db->prepare("UPDATE reservas SET estado = 'cancelada', motivo_cancelacion = :motivo WHERE id = :id");
        $ok = $stmt->execute([
            ':id'     => $id,
            ':motivo' => trim($motivo)
        ]);

        if ($ok) {
            // Liberar habitaciones si estaban marcadas
            $habModel = new Habitacion();
            foreach ($reserva['habitaciones'] as $hab) {
                // Si la habitación estaba en ocupada por esta reserva, pasar a disponible
                $habModel->actualizarEstado((int)$hab['habitacion_id'], 'disponible');
            }
            $auditoriaModel = new Auditoria();
            $auditoriaModel->registrar('Reservas', 'Cancelar', "Reserva {$reserva['codigo_reserva']} cancelada. Motivo: {$motivo}");
        }
        return $ok;
    }

    public function cambiarEstado(int $id, string $nuevoEstado): bool
    {
        $stmt = $this->db->prepare("UPDATE reservas SET estado = :estado WHERE id = :id");
        return $stmt->execute([
            ':id'     => $id,
            ':estado' => $nuevoEstado
        ]);
    }

    public function obtenerEventosCalendario(): array
    {
        $sql = "SELECT r.id, r.codigo_reserva, r.fecha_entrada, r.fecha_salida, r.estado, r.total,
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped,
                GROUP_CONCAT(h.numero SEPARATOR ', ') as habitaciones
                FROM reservas r
                INNER JOIN huespedes hu ON hu.id = r.huesped_id
                LEFT JOIN reserva_habitaciones rh ON rh.reserva_id = r.id
                LEFT JOIN habitaciones h ON h.id = rh.habitacion_id
                WHERE r.estado != 'cancelada'
                GROUP BY r.id";
        $filas = $this->db->query($sql)->fetchAll();

        $etiquetas = [
            'confirmada' => 'Confirmada',
            'pendiente'  => 'Pendiente',
            'en_estadia' => 'En estadía',
            'finalizada' => 'Finalizada',
        ];

        $eventos = [];
        foreach ($filas as $f) {
            $habitaciones = $f['habitaciones'] ?: 'Sin asignar';
            $eventos[] = [
                'id'            => $f['id'],
                // Título corto para que quepa en la barra; el código va en el detalle
                'title'         => ($f['habitaciones'] ? 'Hab. ' . $f['habitaciones'] . ' · ' : '') . $f['huesped'],
                'start'         => $f['fecha_entrada'],
                // Fin exclusivo de FullCalendar = día de salida: la barra cubre las noches
                // ocupadas, así una salida y una llegada el mismo día no se ven superpuestas
                'end'           => $f['fecha_salida'],
                // El color lo pone custom.css según la clase (mismos tonos que las insignias)
                'classNames'    => ['ev-estado-' . $f['estado']],
                'extendedProps' => [
                    'codigo'        => $f['codigo_reserva'],
                    'huesped'       => $f['huesped'],
                    'habitaciones'  => $habitaciones,
                    'estado'        => $f['estado'],
                    'estado_texto'  => $etiquetas[$f['estado']] ?? ucfirst($f['estado']),
                    'total'         => (float)$f['total'],
                    'fecha_entrada' => $f['fecha_entrada'],
                    'fecha_salida'  => $f['fecha_salida'],
                    'edit_url'      => url('reservas/editar/' . $f['id']),
                    'checkin_url'   => url('recepcion/checkin?reserva_id=' . $f['id'])
                ]
            ];
        }
        return $eventos;
    }

    /**
     * Estadías por habitación que se cruzan con el rango [desde, hasta) — para la planificación.
     * Devuelve [habitacion_id => [reservas ordenadas por fecha de entrada]].
     */
    public function obtenerOcupacionPorHabitacion(string $desde, string $hasta): array
    {
        $sql = "SELECT rh.habitacion_id, r.id, r.codigo_reserva, r.fecha_entrada, r.fecha_salida, r.estado,
                       hu.nombres, hu.apellidos
                FROM reserva_habitaciones rh
                INNER JOIN reservas r ON r.id = rh.reserva_id
                INNER JOIN huespedes hu ON hu.id = r.huesped_id
                WHERE r.estado <> 'cancelada'
                  AND r.fecha_entrada < :hasta
                  AND r.fecha_salida > :desde
                ORDER BY rh.habitacion_id, r.fecha_entrada";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);

        $porHabitacion = [];
        foreach ($stmt->fetchAll() as $fila) {
            $porHabitacion[(int)$fila['habitacion_id']][] = $fila;
        }
        return $porHabitacion;
    }

    public function obtenerReservasDeHoy(): array
    {
        $hoy = date('Y-m-d');
        $sql = "SELECT r.*, 
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.telefono,
                GROUP_CONCAT(h.numero SEPARATOR ', ') as habitaciones
                FROM reservas r
                INNER JOIN huespedes hu ON hu.id = r.huesped_id
                LEFT JOIN reserva_habitaciones rh ON rh.reserva_id = r.id
                LEFT JOIN habitaciones h ON h.id = rh.habitacion_id
                WHERE (r.fecha_entrada = :hoy_entrada OR r.fecha_salida = :hoy_salida)
                AND r.estado != 'cancelada'
                GROUP BY r.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':hoy_entrada' => $hoy,
            ':hoy_salida'  => $hoy
        ]);
        return $stmt->fetchAll();
    }
}
