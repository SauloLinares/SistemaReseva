<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-clock-history text-info me-2"></i>Historial de Estadías</h3>
                <p class="text-muted mb-0 small">Registro cronológico de visitas de <?= htmlspecialchars($huesped['nombres'] . ' ' . $huesped['apellidos']) ?></p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('reservas/crear?huesped_id=' . $huesped['id']) ?>" class="btn btn-success btn-sm shadow-sm me-1">
                    <i class="bi bi-calendar-plus me-1"></i> Nueva Reserva para este Huésped
                </a>
                <a href="<?= url('huespedes') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Huéspedes
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Ficha de resumen del cliente -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-md-6">
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($huesped['nombres'] . ' ' . $huesped['apellidos']) ?></h5>
                        <div class="text-muted small">
                            <span class="badge text-bg-light border"><?= htmlspecialchars($huesped['tipo_documento']) ?>: <?= htmlspecialchars($huesped['numero_documento']) ?></span>
                            &nbsp;|&nbsp; Tel: <?= htmlspecialchars($huesped['telefono'] ?: '-') ?>
                            &nbsp;|&nbsp; Email: <?= htmlspecialchars($huesped['correo'] ?: '-') ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
                        <span class="badge text-bg-primary fs-6 px-3 py-2">
                            Total Estadías: <?= count($historial) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Histórica -->
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Estadías y Reservas Anteriores</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código Reserva</th>
                                <th>Habitaciones</th>
                                <th>Fecha Entrada</th>
                                <th>Fecha Salida</th>
                                <th>Noches</th>
                                <th>Monto Total</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($historial)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">Este huésped aún no registra reservas o estadías.</td></tr>
                            <?php else: ?>
                                <?php foreach ($historial as $item): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary"><?= htmlspecialchars($item['codigo_reserva']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-light border">
                                                <i class="bi bi-door-closed me-1"></i><?= htmlspecialchars($item['habitaciones'] ?: 'N/A') ?>
                                            </span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($item['tipos'] ?: '') ?></small>
                                        </td>
                                        <td><span class="text-success fw-semibold"><i class="bi bi-box-arrow-in-right me-1"></i><?= fecha_es($item['fecha_entrada']) ?></span></td>
                                        <td><span class="text-danger fw-semibold"><i class="bi bi-box-arrow-right me-1"></i><?= fecha_es($item['fecha_salida']) ?></span></td>
                                        <td><?= $item['noches'] ?> n</td>
                                        <td class="fw-bold"><?= dinero($item['total']) ?></td>
                                        <td><?= badge_estado_reserva($item['estado']) ?></td>
                                        <td class="text-end pe-3">
                                            <a href="<?= url('reservas/editar/' . $item['id']) ?>" class="btn btn-sm btn-outline-primary" title="Ver detalle de reserva">
                                                <i class="bi bi-eye"></i>
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
