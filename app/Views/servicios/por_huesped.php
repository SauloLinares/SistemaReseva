<?php partial('page_header', [
    'titulo'    => 'Consumos de Servicios por Huésped',
    'icono'     => 'bi-person-lines-fill',
    'subtitulo' => 'Historial de consumos cargados a habitaciones y huéspedes',
    'acciones'  => [
        ['texto' => 'Registrar Nuevo Consumo', 'url' => 'servicios/consumo', 'icono' => 'bi-cart-plus', 'variante' => 'success'],
        ['texto' => 'Catálogo de Servicios', 'url' => 'servicios', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Registro de Consumos (<?= count($consumos) ?>)</h5>
                <?php if ($reservaId): ?>
                    <a href="<?= url('servicios/porHuesped') ?>" class="btn btn-sm btn-outline-secondary">Ver Todos los Huéspedes</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Huésped</th>
                                <th>Habitación</th>
                                <th>Reserva</th>
                                <th>Servicio</th>
                                <th>Cant.</th>
                                <th>P. Unitario</th>
                                <th>Subtotal</th>
                                <th>Fecha y Hora</th>
                                <th>Estado de Pago</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($consumos)): ?>
                                <tr><td colspan="11" class="text-center py-4 text-muted">No se registran consumos para este criterio.</td></tr>
                            <?php else: ?>
                                <?php foreach ($consumos as $c): ?>
                                    <tr>
                                        <td><?= $c['id'] ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($c['huesped_nombre']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($c['numero_documento']) ?></small>
                                        </td>
                                        <td><span class="badge badge-soft-secondary">Hab. <?= htmlspecialchars($c['habitaciones'] ?: '-') ?></span></td>
                                        <td>
                                            <a href="<?= url('reservas/editar/' . $c['reserva_id']) ?>" class="badge badge-soft-primary text-decoration-none">
                                                <?= htmlspecialchars($c['codigo_reserva']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <strong class="text-body-emphasis"><?= htmlspecialchars($c['servicio_nombre']) ?></strong>
                                            <small class="text-muted d-block"><?= htmlspecialchars($c['categoria']) ?></small>
                                        </td>
                                        <td><span class="badge badge-soft-secondary"><?= $c['cantidad'] ?></span></td>
                                        <td><?= dinero($c['precio_unitario']) ?></td>
                                        <td><strong class="text-success fs-6"><?= dinero($c['subtotal']) ?></strong></td>
                                        <td><small class="text-muted"><?= fecha_es($c['fecha_consumo'], true) ?></small></td>
                                        <td>
                                            <?= $c['pagado'] ? '<span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>Pagado</span>' : '<span class="badge badge-soft-warning"><i class="bi bi-clock me-1"></i>A Cuenta</span>' ?>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($c['notas'] ?: '-') ?></small></td>
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
