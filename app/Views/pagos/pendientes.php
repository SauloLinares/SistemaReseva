<?php partial('page_header', [
    'titulo'    => 'Saldos Pendientes de Cobro',
    'icono'     => 'bi-exclamation-circle',
    'subtitulo' => 'Cuentas por cobrar activas de reservas y estadías',
    'acciones'  => [
        ['texto' => 'Volver a Pagos', 'url' => 'pagos', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
        ['texto' => 'Registrar Pago', 'url' => 'pagos/crear', 'icono' => 'bi-cash-coin'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-wallet-fill me-2"></i>Reservas con Saldo Pendiente (<?= count($pendientes) ?>)
                </h5>
                <?php
                    $totalDeuda = 0;
                    foreach ($pendientes as $p) $totalDeuda += (float)$p['saldo'];
                ?>
                <span class="badge badge-soft-danger">Total por Cobrar: <?= dinero($totalDeuda) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reserva</th>
                                <th>Huésped</th>
                                <th>Habitaciones</th>
                                <th>Entrada / Salida</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Abonado</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendientes)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted"><i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>¡Excelente! No hay saldos pendientes en este momento.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pendientes as $p): ?>
                                    <tr>
                                        <td class="text-nowrap">
                                            <a href="<?= url('reservas/editar/' . $p['id']) ?>" class="fw-semibold font-monospace text-decoration-none"><?= e($p['codigo_reserva']) ?></a>
                                            <small class="d-block text-body-secondary text-capitalize"><?= e(str_replace('_', ' ', $p['estado'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-nowrap"><?= e($p['huesped_nombre']) ?></div>
                                            <small class="text-body-secondary text-nowrap">
                                                <?= e($p['numero_documento']) ?><?php if (!empty($p['telefono'])): ?> · <?= e($p['telefono']) ?><?php endif; ?>
                                            </small>
                                        </td>
                                        <td class="text-nowrap"><i class="bi bi-door-closed text-body-secondary me-1"></i><?= e($p['habitaciones'] ?: '-') ?></td>
                                        <td class="text-nowrap">
                                            <div><i class="bi bi-box-arrow-in-right text-success me-1" title="Entrada"></i><?= fecha_es($p['fecha_entrada']) ?></div>
                                            <div><i class="bi bi-box-arrow-right text-danger me-1" title="Salida"></i><?= fecha_es($p['fecha_salida']) ?></div>
                                        </td>
                                        <td class="text-end text-nowrap"><?= dinero($p['total']) ?></td>
                                        <td class="text-end text-nowrap text-body-secondary"><?= dinero($p['anticipo']) ?></td>
                                        <td class="text-end text-nowrap fw-semibold text-danger"><?= dinero($p['saldo']) ?></td>
                                        <td class="text-end pe-3">
                                            <a href="<?= url('pagos/crear?reserva_id=' . $p['id']) ?>" class="btn btn-sm btn-success text-nowrap">
                                                <i class="bi bi-cash-coin me-1"></i>Cobrar
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
