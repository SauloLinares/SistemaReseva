<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-journal-bookmark text-primary me-2"></i>Control de Estadías</h3>
                <p class="text-muted mb-0 small">Supervisión en vivo de huéspedes alojados en el hotel</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('recepcion/checkin') ?>" class="btn btn-success btn-sm shadow-sm me-1">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Check-in
                </a>
                <a href="<?= url('recepcion/checkout') ?>" class="btn btn-outline-danger btn-sm shadow-sm me-1">
                    <i class="bi bi-box-arrow-right me-1"></i> Check-out
                </a>
                <a href="<?= url('recepcion/asignacion') ?>" class="btn btn-outline-primary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left-right me-1"></i> Cambiar Habitación
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

        <!-- Estadías Activas -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success-subtle text-success-emphasis d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-door-open-fill me-2"></i>Huéspedes Alojados Actualmente (<?= count($activas) ?>)
                </h5>
                <span class="badge text-bg-success px-3 py-2">En Casa / En Estadía</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Habitación</th>
                                <th>Huésped</th>
                                <th>Documento</th>
                                <th>Fecha Check-in</th>
                                <th>Salida Prevista</th>
                                <th>Saldo Pendiente</th>
                                <th>Observaciones</th>
                                <th class="text-end pe-3">Acciones de Recepción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activas)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">No hay huéspedes alojados en este momento.</td></tr>
                            <?php else: ?>
                                <?php foreach ($activas as $act): ?>
                                    <tr>
                                        <td>
                                            <span class="fs-5 fw-bold text-primary">Hab. <?= htmlspecialchars($act['habitacion_numero']) ?></span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($act['tipo_nombre']) ?> - Piso <?= $act['piso'] ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($act['huesped_nombre']) ?></div>
                                            <small class="text-muted"><i class="bi bi-globe me-1"></i><?= htmlspecialchars($act['nacionalidad'] ?: 'Perú') ?></small>
                                        </td>
                                        <td><span class="badge text-bg-light border"><?= htmlspecialchars($act['numero_documento']) ?></span></td>
                                        <td><span class="text-success fw-semibold"><i class="bi bi-clock me-1"></i><?= fecha_es($act['fecha_checkin'], true) ?></span></td>
                                        <td><span class="text-danger fw-semibold"><?= fecha_es($act['fecha_salida']) ?></span></td>
                                        <td>
                                            <?php if ((float)$act['reserva_saldo'] > 0): ?>
                                                <span class="badge text-bg-danger fs-6"><?= dinero($act['reserva_saldo']) ?></span>
                                            <?php else: ?>
                                                <span class="badge text-bg-success"><i class="bi bi-check2"></i> Al día</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($act['observaciones'] ?: '-') ?></small></td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= url('recepcion/checkout?id=' . $act['id']) ?>" class="btn btn-outline-danger" title="Realizar Check-out">
                                                    <i class="bi bi-box-arrow-right me-1"></i> Check-out
                                                </a>
                                                <a href="<?= url('servicios/consumo?reserva_id=' . $act['reserva_id']) ?>" class="btn btn-outline-primary" title="Cargar Consumo / Room Service">
                                                    <i class="bi bi-cart-plus"></i>
                                                </a>
                                                <a href="<?= url('reservas/editar/' . $act['reserva_id']) ?>" class="btn btn-outline-secondary" title="Ver Cuenta">
                                                    <i class="bi bi-card-text"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Historial de Estadías Pasadas -->
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h6 class="card-title mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Historial de Salidas / Estadías Finalizadas</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Habitación</th>
                                <th>Huésped</th>
                                <th>Ingreso</th>
                                <th>Salida Efectiva</th>
                                <th>Estado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $h): ?>
                                <tr>
                                    <td><?= $h['id'] ?></td>
                                    <td><strong>Hab. <?= htmlspecialchars($h['habitacion_numero']) ?></strong> (<?= htmlspecialchars($h['tipo_nombre']) ?>)</td>
                                    <td><?= htmlspecialchars($h['huesped_nombre']) ?></td>
                                    <td><?= fecha_es($h['fecha_checkin'], true) ?></td>
                                    <td><?= fecha_es($h['fecha_checkout'], true) ?></td>
                                    <td>
                                        <span class="badge <?= ($h['estado'] === 'activa') ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($h['estado'])) ?>
                                        </span>
                                    </td>
                                    <td><small class="text-muted"><?= htmlspecialchars($h['observaciones'] ?: '-') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
