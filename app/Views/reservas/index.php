<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-calendar-check text-primary me-2"></i>Listado de Reservas</h3>
                <p class="text-muted mb-0 small">Administración, filtros y control de reservas hoteleras</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('reservas/crear') ?>" class="btn btn-primary btn-sm shadow-sm me-1">
                    <i class="bi bi-plus-circle me-1"></i> Registrar Reserva
                </a>
                <a href="<?= url('reservas/disponibilidad') ?>" class="btn btn-outline-info btn-sm shadow-sm me-1">
                    <i class="bi bi-calendar-range me-1"></i> Disponibilidad
                </a>
                <a href="<?= url('reservas/calendario') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-calendar3 me-1"></i> Calendario
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

        <!-- Tarjeta de Filtros -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="<?= url('reservas') ?>" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Estado de Reserva</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">-- Todos los estados --</option>
                            <option value="confirmada" <?= ($filtroEstado === 'confirmada') ? 'selected' : '' ?>>Confirmada</option>
                            <option value="pendiente" <?= ($filtroEstado === 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                            <option value="en_estadia" <?= ($filtroEstado === 'en_estadia') ? 'selected' : '' ?>>En Estadía</option>
                            <option value="finalizada" <?= ($filtroEstado === 'finalizada') ? 'selected' : '' ?>>Finalizada</option>
                            <option value="cancelada" <?= ($filtroEstado === 'cancelada') ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Desde (Fecha Entrada)</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($filtroDesde ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Hasta (Fecha Salida)</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($filtroHasta ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filtrar
                        </button>
                        <a href="<?= url('reservas') ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Reservas -->
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-list-check me-2"></i>Reservas Registradas (<?= count($reservas) ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Huésped</th>
                                <th>Habitaciones</th>
                                <th>Fechas (In / Out)</th>
                                <th>Noches</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservas)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No se encontraron reservas con los criterios especificados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reservas as $r): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                                <?= htmlspecialchars($r['codigo_reserva']) ?>
                                            </span>
                                            <small class="d-block text-muted"><?= htmlspecialchars($r['origen']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($r['huesped_nombre']) ?></div>
                                            <small class="text-muted">Doc: <?= htmlspecialchars($r['numero_documento']) ?> | Tel: <?= htmlspecialchars($r['huesped_telefono'] ?: 'N/A') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-light border shadow-xs">
                                                <i class="bi bi-door-closed me-1"></i><?= htmlspecialchars($r['habitaciones_asignadas'] ?: 'Sin asignar') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small text-success fw-semibold"><i class="bi bi-box-arrow-in-right me-1"></i><?= fecha_es($r['fecha_entrada']) ?></div>
                                            <div class="small text-danger fw-semibold"><i class="bi bi-box-arrow-right me-1"></i><?= fecha_es($r['fecha_salida']) ?></div>
                                        </td>
                                        <td><span class="badge text-bg-secondary"><?= $r['noches'] ?> n</span></td>
                                        <td><strong><?= dinero($r['total']) ?></strong></td>
                                        <td>
                                            <?php if ((float)$r['saldo'] > 0): ?>
                                                <span class="badge text-bg-danger"><?= dinero($r['saldo']) ?></span>
                                            <?php else: ?>
                                                <span class="badge text-bg-success"><i class="bi bi-check2"></i> S/. 0.00</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= badge_estado_reserva($r['estado']) ?></td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= url('reservas/editar/' . $r['id']) ?>" class="btn btn-outline-primary" title="Editar / Ver Detalle">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <?php if ($r['estado'] === 'confirmada' || $r['estado'] === 'pendiente'): ?>
                                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelar<?= $r['id'] ?>" title="Cancelar">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Modal Cancelación -->
                                            <div class="modal fade" id="modalCancelar<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog text-start">
                                                    <form method="POST" action="<?= url('reservas/cancelar') ?>" class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title fs-6"><i class="bi bi-exclamation-triangle-fill me-2"></i>Cancelar Reserva <?= htmlspecialchars($r['codigo_reserva']) ?></h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                            <p>¿Estás seguro de cancelar esta reserva para el huésped <strong><?= htmlspecialchars($r['huesped_nombre']) ?></strong>?</p>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold small">Motivo de Cancelación</label>
                                                                <textarea name="motivo_cancelacion" class="form-control" rows="2" placeholder="Ej: Cancelación del viaje por motivos laborales..." required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Volver</button>
                                                            <button type="submit" class="btn btn-sm btn-danger">Confirmar Cancelación</button>
                                                        </div>
                                                    </form>
                                                </div>
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

    </div>
</div>
