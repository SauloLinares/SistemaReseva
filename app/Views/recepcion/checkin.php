<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-box-arrow-in-right text-success me-2"></i>Recepción: Check-in</h3>
                <p class="text-muted mb-0 small">Registro de ingreso de huéspedes y entrega de habitaciones</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('recepcion/estadias') ?>" class="btn btn-outline-secondary btn-sm shadow-sm me-1">
                    <i class="bi bi-journal-bookmark me-1"></i> Control de Estadías
                </a>
                <a href="<?= url('reservas/crear') ?>" class="btn btn-primary btn-sm shadow-sm">
                    <i class="bi bi-calendar-plus me-1"></i> Nueva Reserva Inmediata
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

        <div class="row g-4">
            <!-- Formulario de Check-in -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-top border-4 border-success">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-key-fill text-success me-2"></i>Registrar Ingreso de Huésped</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('recepcion/procesarCheckin') ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Seleccionar Reserva Programada <span class="text-danger">*</span></label>
                                <select name="reserva_id" id="selectReserva" class="form-select select-search" required onchange="cambiarReserva(this.value)" placeholder="Escribe el código, nombre del huésped o habitación...">
                                    <option value="">-- Elige una reserva lista para ingreso --</option>
                                    <?php foreach ($reservas as $r): ?>
                                        <option value="<?= $r['id'] ?>" <?= ($reservaSeleccionada && $reservaSeleccionada['id'] == $r['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($r['codigo_reserva']) ?> - <?= htmlspecialchars($r['huesped_nombre']) ?> (Hab: <?= htmlspecialchars($r['habitaciones_asignadas'] ?: 'Sin asignar') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Muestra reservas con estado "Confirmada" o "Pendiente".</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Observaciones / Entrega de Llaves</label>
                                <textarea name="observaciones" class="form-control" rows="3" placeholder="Ej: Se entregaron 2 tarjetas magnéticas, huésped presentó pasaporte original..."></textarea>
                            </div>

                            <div class="alert alert-warning py-2 small d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                                <div>Al confirmar el Check-in, la habitación cambiará automáticamente a estado <strong>OCUPADA</strong> y se iniciará la estadía oficial.</div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm">
                                    <i class="bi bi-check-circle-fill me-2"></i>Confirmar Ingreso y Entregar Habitación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Ficha de la Reserva Seleccionada -->
            <div class="col-12 col-lg-6">
                <?php if ($reservaSeleccionada): ?>
                    <div class="card shadow-sm border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="bi bi-card-text me-2"></i>Detalles: <?= htmlspecialchars($reservaSeleccionada['codigo_reserva']) ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold text-primary mb-1"><?= htmlspecialchars($reservaSeleccionada['huesped_nombre']) ?></h5>
                            <div class="text-muted small mb-3">
                                <span class="badge text-bg-light border"><?= htmlspecialchars($reservaSeleccionada['tipo_documento']) ?>: <?= htmlspecialchars($reservaSeleccionada['numero_documento']) ?></span>
                                &nbsp;|&nbsp; Tel: <?= htmlspecialchars($reservaSeleccionada['huesped_telefono'] ?: 'N/A') ?>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-muted d-block">Entrada Programada:</small>
                                        <strong class="text-success"><i class="bi bi-calendar-check me-1"></i><?= fecha_es($reservaSeleccionada['fecha_entrada']) ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-muted d-block">Salida Programada:</small>
                                        <strong class="text-danger"><i class="bi bi-calendar-x me-1"></i><?= fecha_es($reservaSeleccionada['fecha_salida']) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-2">Habitaciones Asignadas:</h6>
                            <ul class="list-group mb-3">
                                <?php foreach ($reservaSeleccionada['habitaciones'] as $hab): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <span class="fw-bold text-primary">Hab. <?= htmlspecialchars($hab['numero']) ?></span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($hab['tipo_nombre']) ?> - Piso <?= $hab['piso'] ?></small>
                                        </div>
                                        <span class="badge text-bg-success"><?= dinero($hab['precio_noche']) ?> / noche</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="d-flex justify-content-between border-top pt-2">
                                <span>Total: <strong><?= dinero($reservaSeleccionada['total']) ?></strong></span>
                                <span>Abonado: <strong class="text-success"><?= dinero($reservaSeleccionada['anticipo']) ?></strong></span>
                                <span>Saldo: <strong class="<?= ((float)$reservaSeleccionada['saldo'] > 0) ? 'text-danger' : 'text-success' ?>"><?= dinero($reservaSeleccionada['saldo']) ?></strong></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm border-dashed text-center p-5 text-muted">
                        <i class="bi bi-person-check display-3 mb-3 text-secondary"></i>
                        <h5>Selecciona una reserva para inspeccionar sus detalles</h5>
                        <p class="small">Elige del desplegable a la izquierda para ver habitaciones, fechas y saldo del huésped.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
function cambiarReserva(id) {
    if (id) {
        window.location.href = '<?= url("recepcion/checkin?reserva_id=") ?>' + id;
    }
}
</script>
