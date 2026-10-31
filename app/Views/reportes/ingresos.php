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
                        <label class="form-label">Fecha Inicial</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($desde) ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Fecha Final</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta) ?>">
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filtrar Período</button>
                        <a href="<?= url('reportes/ingresos') ?>" class="btn btn-sm btn-outline-secondary">Mes Actual</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumen por Método de Pago -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card bg-success text-white p-3 shadow-xs h-100">
                    <small class="text-uppercase fw-semibold opacity-75">Total Ingresos Netos</small>
                    <h2 class="fw-bold mb-0 mt-1"><?= dinero($totalIngresos) ?></h2>
                    <small class="opacity-75 mt-2 d-block"><?= count($pagos) ?> operaciones registradas</small>
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="card h-100">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0 small text-uppercase">Distribución por Método de Pago</h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <?php foreach ($porMetodo as $metodo => $monto): ?>
                                <div class="col-6 col-sm-4">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <small class="text-muted d-block"><?= htmlspecialchars($metodo) ?></small>
                                        <span class="fw-bold text-success"><?= dinero($monto) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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
                                    <td><?= fecha_es($p['fecha_pago'], true) ?></td>
                                    <td><strong><?= htmlspecialchars($p['comprobante_tipo'] . ' ' . $p['comprobante_numero']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['codigo_reserva']) ?></td>
                                    <td><?= htmlspecialchars($p['huesped_nombre']) ?></td>
                                    <td><span class="badge text-bg-light border"><?= htmlspecialchars($p['metodo_pago']) ?></span></td>
                                    <td><span class="badge text-bg-secondary text-uppercase"><?= htmlspecialchars($p['tipo']) ?></span></td>
                                    <td class="text-end pe-3 fw-bold <?= ($p['tipo'] === 'devolucion') ? 'text-danger' : 'text-success' ?>">
                                        <?= ($p['tipo'] === 'devolucion' ? '-' : '+') . dinero($p['monto']) ?>
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
