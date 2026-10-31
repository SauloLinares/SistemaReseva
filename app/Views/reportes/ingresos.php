<?php partial('page_header', [
    'titulo'    => 'Reporte de Ingresos',
    'icono'     => 'bi-cash-stack',
    'subtitulo' => 'Control financiero, flujo de cobros y distribución por métodos de pago',
    'acciones'  => [
        ['texto' => 'Imprimir Reporte', 'imprimir' => true, 'icono' => 'bi-printer', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Filtro de Fechas -->
        <div class="card mb-4 d-print-none">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('reportes/ingresos') ?>" class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="desde" class="form-label">Fecha Inicial</label>
                        <input id="desde" type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($desde) ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="hasta" class="form-label">Fecha Final</label>
                        <input id="hasta" type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta) ?>">
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1 text-nowrap"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                        <a href="<?= url('reportes/ingresos') ?>" class="btn btn-sm btn-outline-secondary text-nowrap">Mes actual</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumen: total y reparto por método de pago -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-4">
                <div class="small-box text-bg-success h-100">
                    <div class="inner">
                        <h3><?= dinero($totalIngresos) ?></h3>
                        <p class="mb-1">Ingresos netos del período</p>
                        <small><?= count($pagos) ?> operaciones registradas</small>
                    </div>
                    <div class="small-box-icon"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
            <div class="col-12 col-lg-8">
                <div class="card h-100">
                    <?php partial('card_header', ['titulo' => 'Distribución por método de pago', 'icono' => 'bi-pie-chart']) ?>
                    <div class="card-body">
                        <?php if (empty($porMetodo) || $totalIngresos <= 0): ?>
                            <p class="text-body-secondary mb-0">Sin cobros en el período.</p>
                        <?php else: ?>
                            <?php arsort($porMetodo); ?>
                            <?php foreach ($porMetodo as $metodo => $monto): ?>
                                <?php $porcentaje = round(((float) $monto / (float) $totalIngresos) * 100, 1); ?>
                                <div class="barra-metodo">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span><?= e($metodo) ?></span>
                                        <span class="text-nowrap"><strong><?= dinero($monto) ?></strong> <span class="text-body-secondary">· <?= $porcentaje ?>%</span></span>
                                    </div>
                                    <div class="progress progress-sm" role="progressbar" aria-label="<?= e($metodo) ?>" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" style="width: <?= $porcentaje ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Cobros -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-receipt me-2"></i>Detalle de Cobros en el Período</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Comprobante</th>
                                <th>Reserva</th>
                                <th>Huésped</th>
                                <th>Método</th>
                                <th>Tipo</th>
                                <th class="text-end pe-3">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $p): ?>
                                <tr>
                                    <td class="text-nowrap"><?= fecha_es($p['fecha_pago'], true) ?></td>
                                    <td class="text-nowrap"><span class="fw-semibold"><?= e($p['comprobante_tipo']) ?></span> <small class="text-body-secondary font-monospace"><?= e($p['comprobante_numero']) ?></small></td>
                                    <td class="text-nowrap font-monospace"><?= e($p['codigo_reserva']) ?></td>
                                    <td class="text-nowrap"><?= e($p['huesped_nombre']) ?></td>
                                    <td class="text-nowrap"><?= e($p['metodo_pago']) ?></td>
                                    <td><span class="badge <?= $p['tipo'] === 'devolucion' ? 'badge-soft-danger' : ($p['tipo'] === 'anticipo' ? 'badge-soft-info' : 'badge-soft-success') ?> text-capitalize"><?= e($p['tipo']) ?></span></td>
                                    <td class="text-end pe-3 text-nowrap fw-semibold <?= ($p['tipo'] === 'devolucion') ? 'text-danger' : 'text-success' ?>">
                                        <?= ($p['tipo'] === 'devolucion' ? '−' : '+') . ' ' . dinero($p['monto']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
