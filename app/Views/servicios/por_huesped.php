<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill text-primary me-2"></i>Consumos de Servicios por Huésped</h3>
                <p class="text-muted mb-0 small">Historial de consumos cargados a habitaciones y huéspedes</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('servicios/consumo') ?>" class="btn btn-success btn-sm shadow-sm me-1">
                    <i class="bi bi-cart-plus me-1"></i> Registrar Nuevo Consumo
                </a>
                <a href="<?= url('servicios') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Catálogo de Servicios
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

        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Registro de Consumos (<?= count($consumos) ?>)</h5>
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
                                        <td><span class="badge text-bg-light border">Hab. <?= htmlspecialchars($c['habitaciones'] ?: '-') ?></span></td>
                                        <td>
                                            <a href="<?= url('reservas/editar/' . $c['reserva_id']) ?>" class="badge text-bg-primary text-decoration-none">
                                                <?= htmlspecialchars($c['codigo_reserva']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <strong class="text-body-emphasis"><?= htmlspecialchars($c['servicio_nombre']) ?></strong>
                                            <small class="text-muted d-block"><?= htmlspecialchars($c['categoria']) ?></small>
                                        </td>
                                        <td><span class="badge text-bg-secondary fs-6"><?= $c['cantidad'] ?></span></td>
                                        <td><?= dinero($c['precio_unitario']) ?></td>
                                        <td><strong class="text-success fs-6"><?= dinero($c['subtotal']) ?></strong></td>
                                        <td><small class="text-muted"><?= fecha_es($c['fecha_consumo'], true) ?></small></td>
                                        <td>
                                            <?= $c['pagado'] ? '<span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Pagado</span>' : '<span class="badge badge-soft-warning"><i class="bi bi-clock me-1"></i>A Cuenta</span>' ?>
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
