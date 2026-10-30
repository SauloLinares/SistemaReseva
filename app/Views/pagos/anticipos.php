<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-wallet2 text-info me-2"></i>Anticipos de Reservas</h3>
                <p class="text-muted mb-0 small">Control de adelantos y depósitos de garantía entregados por los huéspedes</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('pagos/crear') ?>" class="btn btn-primary btn-sm shadow-sm me-1">
                    <i class="bi bi-plus-circle me-1"></i> Registrar Anticipo
                </a>
                <a href="<?= url('pagos') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Historial General
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Listado de Anticipos Registrados (<?= count($anticipos) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th># Comprobante</th>
                                <th>Código Reserva</th>
                                <th>Huésped</th>
                                <th>Método de Pago</th>
                                <th>Monto Abonado</th>
                                <th>Fecha</th>
                                <th>Notas</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($anticipos)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">No se registran anticipos en el sistema.</td></tr>
                            <?php else: ?>
                                <?php foreach ($anticipos as $a): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold"><?= htmlspecialchars($a['comprobante_tipo']) ?></span>
                                            <small class="d-block text-primary"><?= htmlspecialchars($a['comprobante_numero']) ?></small>
                                        </td>
                                        <td>
                                            <a href="<?= url('reservas/editar/' . $a['reserva_id']) ?>" class="badge text-bg-light border text-decoration-none">
                                                <?= htmlspecialchars($a['codigo_reserva']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($a['huesped_nombre']) ?></td>
                                        <td><span class="badge text-bg-light border"><?= htmlspecialchars($a['metodo_pago']) ?></span></td>
                                        <td><span class="fw-bold text-success fs-6"><?= dinero($a['monto']) ?></span></td>
                                        <td><small class="text-muted"><?= fecha_es($a['fecha_pago'], true) ?></small></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($a['notas'] ?: '-') ?></small></td>
                                        <td class="text-end pe-3">
                                            <a href="<?= url('pagos/comprobante/' . $a['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Imprimir Comprobante">
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
