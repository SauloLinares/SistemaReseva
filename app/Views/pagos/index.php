<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-credit-card-2-front text-info me-2"></i>Pagos y Facturación</h3>
                <p class="text-muted mb-0 small">Historial de pagos, ingresos de caja y comprobantes tributarios</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('pagos/crear') ?>" class="btn btn-success btn-sm shadow-sm me-1">
                    <i class="bi bi-cash-coin me-1"></i> Registrar Pago
                </a>
                <a href="<?= url('pagos/pendientes') ?>" class="btn btn-outline-danger btn-sm shadow-sm me-1">
                    <i class="bi bi-exclamation-circle me-1"></i> Saldos Pendientes
                </a>
                <a href="<?= url('pagos/anticipos') ?>" class="btn btn-outline-primary btn-sm shadow-sm">
                    <i class="bi bi-wallet2 me-1"></i> Anticipos
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Tarjetas de Caja -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card bg-success-subtle border-success text-success-emphasis p-3 shadow-xs">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="fw-semibold text-uppercase">Ingresos Cobrados Hoy</small>
                            <h2 class="fw-bold mb-0 mt-1"><?= dinero($ingresosHoy) ?></h2>
                        </div>
                        <i class="bi bi-cash-stack display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card bg-info-subtle border-info text-info-emphasis p-3 shadow-xs">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="fw-semibold text-uppercase">Ingresos Totales del Mes</small>
                            <h2 class="fw-bold mb-0 mt-1"><?= dinero($ingresosMes) ?></h2>
                        </div>
                        <i class="bi bi-calendar2-check display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Pagos -->
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Historial de Pagos y Movimientos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th># Comprobante</th>
                                <th>Tipo</th>
                                <th>Reserva</th>
                                <th>Huésped</th>
                                <th>Método de Pago</th>
                                <th>Monto</th>
                                <th>Fecha y Hora</th>
                                <th>Concepto / Notas</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagos)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">No se registran pagos en el sistema.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pagos as $p): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-body-emphasis"><?= htmlspecialchars($p['comprobante_tipo']) ?></span>
                                            <small class="d-block text-primary fw-semibold"><?= htmlspecialchars($p['comprobante_numero']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= ($p['tipo'] === 'devolucion') ? 'text-bg-danger' : (($p['tipo'] === 'anticipo') ? 'text-bg-info text-white' : 'text-bg-success') ?> text-uppercase">
                                                <?= htmlspecialchars($p['tipo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= url('reservas/editar/' . $p['reserva_id']) ?>" class="badge text-bg-light border text-decoration-none">
                                                <?= htmlspecialchars($p['codigo_reserva']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($p['huesped_nombre']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($p['numero_documento']) ?></small>
                                        </td>
                                        <td><span class="badge text-bg-light border"><?= htmlspecialchars($p['metodo_pago']) ?></span></td>
                                        <td>
                                            <span class="fs-6 fw-bold <?= ($p['tipo'] === 'devolucion') ? 'text-danger' : 'text-success' ?>">
                                                <?= ($p['tipo'] === 'devolucion' ? '-' : '+') . dinero($p['monto']) ?>
                                            </span>
                                        </td>
                                        <td><small class="text-muted"><?= fecha_es($p['fecha_pago'], true) ?></small></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($p['notas'] ?: '-') ?></small></td>
                                        <td class="text-end pe-3">
                                            <a href="<?= url('pagos/comprobante/' . $p['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver / Imprimir Comprobante">
                                                <i class="bi bi-printer me-1"></i> Imprimir
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
