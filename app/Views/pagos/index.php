<?php partial('page_header', [
    'titulo'    => 'Pagos y Facturación',
    'icono'     => 'bi-credit-card-2-front',
    'subtitulo' => 'Historial de pagos, ingresos de caja y comprobantes tributarios',
    'acciones'  => [
        ['texto' => 'Saldos Pendientes', 'url' => 'pagos/pendientes', 'icono' => 'bi-exclamation-circle', 'variante' => 'outline-secondary'],
        ['texto' => 'Anticipos', 'url' => 'pagos/anticipos', 'icono' => 'bi-wallet2', 'variante' => 'outline-secondary'],
        ['texto' => 'Registrar Pago', 'url' => 'pagos/crear', 'icono' => 'bi-cash-coin'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Resumen de ingresos -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="small-box text-bg-success h-100">
                    <div class="inner">
                        <h3><?= dinero($ingresosHoy) ?></h3>
                        <p class="mb-0">Ingresos cobrados hoy</p>
                    </div>
                    <div class="small-box-icon"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="small-box text-bg-info h-100">
                    <div class="inner">
                        <h3><?= dinero($ingresosMes) ?></h3>
                        <p class="mb-0">Ingresos totales del mes</p>
                    </div>
                    <div class="small-box-icon"><i class="bi bi-calendar2-check"></i></div>
                </div>
            </div>
        </div>

        <!-- Tabla de Pagos -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Historial de Pagos y Movimientos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th>Comprobante</th>
                                <th>Reserva</th>
                                <th>Huésped</th>
                                <th>Método</th>
                                <th class="text-end">Monto</th>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagos)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">No se registran pagos en el sistema.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pagos as $p): ?>
                                    <?php
                                        $esDevolucion = ($p['tipo'] === 'devolucion');
                                        $badgeTipo = match ($p['tipo']) {
                                            'devolucion' => 'badge-soft-danger',
                                            'anticipo'   => 'badge-soft-info',
                                            default      => 'badge-soft-success',
                                        };
                                    ?>
                                    <tr>
                                        <td class="text-nowrap">
                                            <div class="fw-semibold">
                                                <?= e($p['comprobante_tipo']) ?>
                                                <span class="badge <?= $badgeTipo ?> text-capitalize ms-1"><?= e($p['tipo']) ?></span>
                                            </div>
                                            <small class="text-body-secondary font-monospace"><?= e($p['comprobante_numero']) ?></small>
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="<?= url('reservas/editar/' . $p['reserva_id']) ?>" class="fw-semibold font-monospace text-decoration-none">
                                                <?= e($p['codigo_reserva']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-nowrap"><?= e($p['huesped_nombre']) ?></div>
                                            <small class="text-body-secondary"><?= e($p['numero_documento']) ?></small>
                                        </td>
                                        <td class="text-nowrap"><?= e($p['metodo_pago']) ?></td>
                                        <td class="text-end text-nowrap fw-semibold <?= $esDevolucion ? 'text-danger' : 'text-success' ?>">
                                            <?= ($esDevolucion ? '−' : '+') . ' ' . dinero($p['monto']) ?>
                                        </td>
                                        <td class="text-nowrap"><?= fecha_es($p['fecha_pago'], true) ?></td>
                                        <td>
                                            <?php $notas = $p['notas'] ?: '-'; ?>
                                            <span class="text-body-secondary text-clamp-2" title="<?= e($notas) ?>"><?= e($notas) ?></span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?= url('pagos/comprobante/' . $p['id']) ?>" target="_blank" class="btn btn-icon btn-outline-secondary" title="Ver / imprimir comprobante">
                                                <i class="bi bi-printer"></i>
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
