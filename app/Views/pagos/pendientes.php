<?php partial('page_header', [
    'titulo'    => 'Saldos Pendientes de Cobro',
    'icono'     => 'bi-exclamation-circle',
    'subtitulo' => 'Cuentas por cobrar activas de reservas y estadías',
    'acciones'  => [
        ['texto' => 'Registrar Pago', 'url' => 'pagos/crear', 'icono' => 'bi-cash-coin', 'variante' => 'success'],
        ['texto' => 'Volver a Pagos', 'url' => 'pagos', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card border-danger">
            <div class="card-header bg-danger-subtle text-danger-emphasis d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-wallet-fill me-2"></i>Reservas con Saldo Pendiente (<?= count($pendientes) ?>)
                </h5>
                <?php
                    $totalDeuda = 0;
                    foreach ($pendientes as $p) $totalDeuda += (float)$p['saldo'];
                ?>
                <span class="badge text-bg-danger fs-6 px-3 py-2">Total por Cobrar: <?= dinero($totalDeuda) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código Reserva</th>
                                <th>Huésped</th>
                                <th>Habitaciones</th>
                                <th>Fecha Entrada</th>
                                <th>Fecha Salida</th>
                                <th>Total Reserva</th>
                                <th>Abonado</th>
                                <th>Saldo Pendiente</th>
                                <th class="text-end pe-3">Acción de Cobro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendientes)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>¡Excelente! No hay saldos pendientes en este momento.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pendientes as $p): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary"><?= htmlspecialchars($p['codigo_reserva']) ?></span>
                                            <small class="d-block text-muted"><?= htmlspecialchars($p['estado']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($p['huesped_nombre']) ?></div>
                                            <small class="text-muted">Doc: <?= htmlspecialchars($p['numero_documento']) ?> | Tel: <?= htmlspecialchars($p['telefono'] ?: '-') ?></small>
                                        </td>
                                        <td><span class="badge text-bg-light border"><?= htmlspecialchars($p['habitaciones'] ?: '-') ?></span></td>
                                        <td><?= fecha_es($p['fecha_entrada']) ?></td>
                                        <td><?= fecha_es($p['fecha_salida']) ?></td>
                                        <td><?= dinero($p['total']) ?></td>
                                        <td><span class="text-success"><?= dinero($p['anticipo']) ?></span></td>
                                        <td>
                                            <span class="fs-6 fw-bold text-danger"><?= dinero($p['saldo']) ?></span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?= url('pagos/crear?reserva_id=' . $p['id']) ?>" class="btn btn-sm btn-success">
                                                <i class="bi bi-cash-coin me-1"></i> Cobrar Saldo
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
