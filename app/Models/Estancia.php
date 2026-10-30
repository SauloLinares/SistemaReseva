<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Estancia extends Model
{
    public function listarActivas(): array
    {
        $sql = "SELECT e.*, r.codigo_reserva, r.fecha_entrada, r.fecha_salida, r.saldo as reserva_saldo,
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.numero_documento, hu.telefono, hu.nacionalidad,
                h.numero as habitacion_numero, h.piso, th.nombre as tipo_nombre
                FROM estancias e
                INNER JOIN reservas r ON r.id = e.reserva_id
                INNER JOIN huespedes hu ON hu.id = e.huesped_id
                INNER JOIN habitaciones h ON h.id = e.habitacion_id
                INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                WHERE e.estado = 'activa'
                ORDER BY e.fecha_checkin DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function listarHistorial(): array
    {
        $sql = "SELECT e.*, r.codigo_reserva,
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.numero_documento,
                h.numero as habitacion_numero, th.nombre as tipo_nombre
                FROM estancias e
                INNER JOIN reservas r ON r.id = e.reserva_id
                INNER JOIN huespedes hu ON hu.id = e.huesped_id
                INNER JOIN habitaciones h ON h.id = e.habitacion_id
                INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                ORDER BY e.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT e.*, r.codigo_reserva, r.total as reserva_total, r.anticipo, r.saldo as reserva_saldo,
                r.fecha_entrada, r.fecha_salida,
                CONCAT(hu.nombres, ' ', hu.apellidos) as huesped_nombre,
                hu.numero_documento, hu.telefono, hu.correo,
                h.numero as habitacion_numero, h.piso, h.precio_diario, th.nombre as tipo_nombre
                FROM estancias e
                INNER JOIN reservas r ON r.id = e.reserva_id
                INNER JOIN huespedes hu ON hu.id = e.huesped_id
                INNER JOIN habitaciones h ON h.id = e.habitacion_id
                INNER JOIN tipos_habitacion th ON th.id = h.tipo_id
                WHERE e.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function obtenerPorReserva(int $reservaId): array
    {
        $sql = "SELECT e.*, h.numero as habitacion_numero 
                FROM estancias e 
                INNER JOIN habitaciones h ON h.id = e.habitacion_id 
                WHERE e.reserva_id = :reserva_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':reserva_id' => $reservaId]);
        return $stmt->fetchAll();
    }

    public function registrarCheckin(int $reservaId, ?int $habitacionId = null, string $observaciones = ''): bool
    {
        $this->db->beginTransaction();

        try {
            $reservaModel = new Reserva();
            $reserva = $reservaModel->obtenerPorId($reservaId);
            if (!$reserva) {
                $this->db->rollBack();
                return false;
            }

            $habModel = new Habitacion();

            // Si se pasa una habitación específica o si se usan las de la reserva
            $habitacionesAProcesar = [];
            if ($habitacionId) {
                $habitacionesAProcesar[] = $habitacionId;
            } else {
                foreach ($reserva['habitaciones'] as $hab) {
                    $habitacionesAProcesar[] = (int)$hab['habitacion_id'];
                }
            }

            if (empty($habitacionesAProcesar)) {
                $this->db->rollBack();
                return false;
            }

            foreach ($habitacionesAProcesar as $hId) {
                // Insertar estancia
                $sql = "INSERT INTO estancias (reserva_id, habitacion_id, huesped_id, fecha_checkin, observaciones, estado, usuario_id)
                        VALUES (:reserva_id, :habitacion_id, :huesped_id, NOW(), :observaciones, 'activa', :usuario_id)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':reserva_id'    => $reservaId,
                    ':habitacion_id' => $hId,
                    ':huesped_id'    => $reserva['huesped_id'],
                    ':observaciones' => $observaciones,
                    ':usuario_id'    => auth_user('id') ?: 1
                ]);

                // Marcar habitación como ocupada
                $habModel->actualizarEstado($hId, 'ocupada');
            }

            // Cambiar estado de la reserva a en_estadia
            $reservaModel->cambiarEstado($reservaId, 'en_estadia');

            $auditoriaModel = new Auditoria();
            $auditoriaModel->registrar('Recepción', 'Check-in', "Check-in realizado para reserva {$reserva['codigo_reserva']} en habitaciones " . implode(', ', $habitacionesAProcesar));

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function registrarCheckout(int $estanciaId, string $observaciones = ''): bool
    {
        $this->db->beginTransaction();

        try {
            $estancia = $this->obtenerPorId($estanciaId);
            if (!$estancia) {
                $this->db->rollBack();
                return false;
            }

            // 1. Finalizar estancia
            $sql = "UPDATE estancias 
                    SET fecha_checkout = NOW(), 
                        observaciones = CONCAT(IFNULL(observaciones,''), ' | Checkout: ', :obs), 
                        estado = 'finalizada' 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'  => $estanciaId,
                ':obs' => $observaciones
            ]);

            // 2. Marcar habitación para limpieza
            $habModel = new Habitacion();
            $habModel->actualizarEstado((int)$estancia['habitacion_id'], 'limpieza');

            // 3. Crear tarea de limpieza automática
            $mantModel = new Mantenimiento();
            $mantModel->crear([
                'habitacion_id' => $estancia['habitacion_id'],
                'tipo'          => 'limpieza',
                'descripcion'   => 'Limpieza profunda y cambio de blancos tras Check-out de habitación ' . $estancia['habitacion_numero'],
                'responsable'   => 'Personal de Limpieza de Turno',
                'estado'        => 'pendiente'
            ]);

            // 4. Si todas las estancias de la reserva ya finalizaron, marcar reserva como finalizada
            $stmtPend = $this->db->prepare("SELECT COUNT(*) FROM estancias WHERE reserva_id = :rid AND estado = 'activa'");
            $stmtPend->execute([':rid' => $estancia['reserva_id']]);
            $quedanActivas = (int)$stmtPend->fetchColumn();

            if ($quedanActivas === 0) {
                $reservaModel = new Reserva();
                $reservaModel->cambiarEstado((int)$estancia['reserva_id'], 'finalizada');
            }

            $auditoriaModel = new Auditoria();
            $auditoriaModel->registrar('Recepción', 'Check-out', "Check-out completado para estancia ID {$estanciaId}, habitación {$estancia['habitacion_numero']}");

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function cambiarHabitacion(int $estanciaId, int $nuevaHabitacionId, string $motivo = ''): bool
    {
        $this->db->beginTransaction();

        try {
            $estancia = $this->obtenerPorId($estanciaId);
            if (!$estancia) {
                $this->db->rollBack();
                return false;
            }

            $habAntiguaId = (int)$estancia['habitacion_id'];

            // Poner la antigua en limpieza
            $habModel = new Habitacion();
            $habModel->actualizarEstado($habAntiguaId, 'limpieza');

            // Poner la nueva en ocupada
            $habModel->actualizarEstado($nuevaHabitacionId, 'ocupada');

            // Actualizar la estancia
            $stmt = $this->db->prepare("UPDATE estancias SET habitacion_id = :nueva, observaciones = CONCAT(IFNULL(observaciones,''), ' | Reubicado desde hab: ', :antigua, ' Motivo: ', :motivo) WHERE id = :id");
            $stmt->execute([
                ':nueva'   => $nuevaHabitacionId,
                ':antigua' => $estancia['habitacion_numero'],
                ':motivo'  => $motivo,
                ':id'      => $estanciaId
            ]);

            // Actualizar en reserva_habitaciones
            $stmtRh = $this->db->prepare("UPDATE reserva_habitaciones SET habitacion_id = :nueva WHERE reserva_id = :rid AND habitacion_id = :antigua LIMIT 1");
            $stmtRh->execute([
                ':nueva'   => $nuevaHabitacionId,
                ':rid'     => $estancia['reserva_id'],
                ':antigua' => $habAntiguaId
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
