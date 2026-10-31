<?php partial('page_header', [
    'titulo'    => 'Historial de Estadías',
    'icono'     => 'bi-clock-history',
    'subtitulo' => 'Registro cronológico de visitas de ' . ($huesped['nombres'] . ' ' . $huesped['apellidos']),
    'acciones'  => [
        ['texto' => 'Nueva Reserva para este Huésped', 'url' => 'reservas/crear?huesped_id=' . $huesped['id'], 'icono' => 'bi-calendar-plus'],
        ['texto' => 'Volver a Huéspedes', 'url' => 'huespedes', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Ficha de resumen del cliente -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-md-6">
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($huesped['nombres'] . ' ' . $huesped['apellidos']) ?></h5>
                        <div class="text-muted small">
                            <span class="badge badge-soft-secondary"><?= htmlspecialchars($huesped['tipo_documento']) ?>: <?= htmlspecialchars($huesped['numero_documento']) ?></span>
                            &nbsp;|&nbsp; Tel: <?= htmlspecialchars($huesped['telefono'] ?: '-') ?>
                            &nbsp;|&nbsp; Email: <?= htmlspecialchars($huesped['correo'] ?: '-') ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
                        <span class="badge badge-soft-primary">
                            Total Estadías: <?= count($historial) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Histórica -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Estadías y Reservas Anteriores</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código Reserva</th>
                                <th>Habitaciones</th>
                                <th>Fecha Entrada</th>
                                <th>Fecha Salida</th>
                                <th>Noches</th>
                                <th>Monto Total</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($historial)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">Este huésped aún no registra reservas o estadías.</td></tr>
                            <?php else: ?>
                                <?php foreach ($historial as $item): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary"><?= htmlspecialchars($item['codigo_reserva']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-secondary">
                                                <i class="bi bi-door-closed me-1"></i><?= htmlspecialchars($item['habitaciones'] ?: 'N/A') ?>
                                            </span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($item['tipos'] ?: '') ?></small>
                                        </td>
                                        <td><span class="text-success fw-semibold"><i class="bi bi-box-arrow-in-right me-1"></i><?= fecha_es($item['fecha_entrada']) ?></span></td>
                                        <td><span class="text-danger fw-semibold"><i class="bi bi-box-arrow-right me-1"></i><?= fecha_es($item['fecha_salida']) ?></span></td>
                                        <td><?= $item['noches'] ?> n</td>
                                        <td class="fw-bold"><?= dinero($item['total']) ?></td>
                                        <td><?= badge_estado_reserva($item['estado']) ?></td>
                                        <td class="text-end pe-3">
                                            <a href="<?= url('reservas/editar/' . $item['id']) ?>" class="btn btn-icon btn-outline-secondary" title="Ver detalle de reserva">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
