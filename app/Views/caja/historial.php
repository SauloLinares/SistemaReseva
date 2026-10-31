<?php partial('page_header', [
    'titulo'    => 'Historial de Turnos y Cierres de Caja',
    'icono'     => 'bi-archive',
    'subtitulo' => 'Auditoría completa de turnos, arqueos físicos y diferencias',
    'acciones'  => [
        ['texto' => 'Ir a Caja Actual', 'url' => 'caja', 'icono' => 'bi-calculator'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-1"></i>Registro de Turnos
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="filtroHistorialCaja" class="form-control form-control-sm col-w-xl" placeholder="Buscar por recepcionista, fecha...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaHistorialCaja">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"># Turno</th>
                                <th>Recepcionista</th>
                                <th>Apertura</th>
                                <th>Cierre</th>
                                <th class="text-end">Fondo Inicial</th>
                                <th class="text-end">Ventas Efectivo</th>
                                <th class="text-end">Ventas Digital</th>
                                <th class="text-end">Gastos</th>
                                <th class="text-end">Entregado</th>
                                <th class="text-center">Diferencia</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $t): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">#<?= sprintf('%04d', $t['id']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($t['usuario_nombre']) ?></strong>
                                        <small class="d-block text-muted">@<?= htmlspecialchars($t['usuario_login']) ?></small>
                                    </td>
                                    <td class="small">
                                        <?= date('d/m/Y', strtotime($t['fecha_apertura'])) ?>
                                        <span class="d-block text-muted"><?= date('h:i A', strtotime($t['fecha_apertura'])) ?></span>
                                    </td>
                                    <td class="small">
                                        <?php if (!empty($t['fecha_cierre'])): ?>
                                            <?= date('d/m/Y', strtotime($t['fecha_cierre'])) ?>
                                            <span class="d-block text-muted"><?= date('h:i A', strtotime($t['fecha_cierre'])) ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-soft-success">En curso</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end small"><?= dinero($t['monto_inicial']) ?></td>
                                    <td class="text-end small text-success fw-semibold"><?= dinero($t['total_ventas_efectivo']) ?></td>
                                    <td class="text-end small text-info fw-semibold"><?= dinero($t['total_ventas_digital']) ?></td>
                                    <td class="text-end small text-danger"><?= dinero($t['total_egresos']) ?></td>
                                    <td class="text-end fw-bold"><?= dinero($t['monto_real_efectivo'] ?? 0) ?></td>
                                    <td class="text-center">
                                        <?php if ($t['estado'] === 'abierta'): ?>
                                            <span class="text-muted small">--</span>
                                        <?php else: ?>
                                            <?php $dif = (float)($t['diferencia'] ?? 0); ?>
                                            <?php if (abs($dif) < 0.01): ?>
                                                <span class="badge badge-soft-success">Cuadrado</span>
                                            <?php elseif ($dif > 0): ?>
                                                <span class="badge badge-soft-warning">+<?= dinero($dif) ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-soft-danger"><?= dinero($dif) ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($t['estado'] === 'abierta'): ?>
                                            <span class="badge badge-soft-success">Abierta</span>
                                        <?php else: ?>
                                            <span class="badge badge-soft-secondary">Cerrada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center pe-3">
                                        <a href="<?= url('caja/cierre/' . $t['id']) ?>" class="btn btn-outline-primary btn-sm" title="Ver Reporte de Arqueo">
                                            <i class="bi bi-file-earmark-text me-1"></i> Reporte
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($historial)): ?>
                                <tr>
                                    <td colspan="12" class="text-center py-5 text-muted">
                                        <i class="bi bi-archive fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        No hay turnos de caja registrados en el sistema.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?= script_js('vistas/caja-historial.js') ?>
