<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-box-arrow-right text-danger me-2"></i>Recepción: Check-out</h3>
                <p class="text-muted mb-0 small">Liquidación de cuenta, cobro de saldo y liberación de habitación</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('recepcion/estadias') ?>" class="btn btn-outline-secondary btn-sm shadow-sm me-1">
                    <i class="bi bi-journal-bookmark me-1"></i> Control de Estadías
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
            <!-- Formulario de Check-out -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-top border-4 border-danger">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-door-closed text-danger me-2"></i>Procesar Salida de Habitación</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('recepcion/procesarCheckout') ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Seleccionar Huésped / Habitación Alojada <span class="text-danger">*</span></label>
                                <select name="estancia_id" class="form-select select-search" required onchange="cambiarEstancia(this.value)" placeholder="Escribe el número de habitación, nombre de huésped o código...">
                                    <option value="">-- Seleccionar huésped alojado --</option>
                                    <?php foreach ($activas as $act): ?>
                                        <option value="<?= $act['id'] ?>" <?= ($estanciaSeleccionada && $estanciaSeleccionada['id'] == $act['id']) ? 'selected' : '' ?>>
                                            Hab. <?= htmlspecialchars($act['habitacion_numero']) ?> - <?= htmlspecialchars($act['huesped_nombre']) ?> (In: <?= fecha_es($act['fecha_checkin']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if ($estanciaSeleccionada): ?>
                                <?php if ((float)$estanciaSeleccionada['reserva_saldo'] > 0): ?>
                                    <div class="alert alert-danger py-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Saldo Pendiente de Cobro:</strong>
                                            <span class="fs-4 fw-bold"><?= dinero($estanciaSeleccionada['reserva_saldo']) ?></span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="<?= url('pagos/crear?reserva_id=' . $estanciaSeleccionada['reserva_id']) ?>" class="btn btn-sm btn-danger fw-bold">
                                                <i class="bi bi-cash me-1"></i> Cobrar Ahora en Caja
                                            </a>
                                            <div class="form-check align-self-center ms-2">
                                                <input class="form-check-input" type="checkbox" name="confirmar_saldo" value="1" id="checkSaldo">
                                                <label class="form-check-label small" for="checkSaldo">
                                                    Permitir salida con saldo pendiente
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success py-2 small d-flex align-items-center">
                                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                        <div><strong>¡Cuenta al día!</strong> No existen saldos pendientes por concepto de habitación.</div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Observaciones de Salida / Estado de la Habitación</label>
                                <textarea name="observaciones" class="form-control" rows="3" placeholder="Ej: Se recibieron todas las llaves, toallas completas, sin desperfectos..."></textarea>
                            </div>

                            <div class="alert alert-info py-2 small d-flex align-items-center">
                                <i class="bi bi-stars me-2 fs-5"></i>
                                <div>Al confirmar, la habitación pasará automáticamente a estado <strong>LIMPIEZA</strong> y se generará una orden para el personal de limpieza.</div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-danger btn-lg fw-bold shadow-sm">
                                    <i class="bi bi-box-arrow-right me-2"></i>Finalizar Estadía y Liberar Habitación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Ficha Resumen -->
            <div class="col-12 col-lg-6">
                <?php if ($estanciaSeleccionada): ?>
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="bi bi-door-open me-2"></i>Habitación <?= htmlspecialchars($estanciaSeleccionada['habitacion_numero']) ?> (Piso <?= $estanciaSeleccionada['piso'] ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($estanciaSeleccionada['huesped_nombre']) ?></h5>
                            <div class="text-muted small mb-3">
                                Documento: <?= htmlspecialchars($estanciaSeleccionada['numero_documento']) ?> | Tel: <?= htmlspecialchars($estanciaSeleccionada['telefono'] ?: 'N/A') ?>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-muted d-block">Ingreso Registrado:</small>
                                        <strong class="text-success"><i class="bi bi-clock me-1"></i><?= fecha_es($estanciaSeleccionada['fecha_checkin'], true) ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-muted d-block">Código de Reserva:</small>
                                        <strong class="text-primary"><?= htmlspecialchars($estanciaSeleccionada['codigo_reserva']) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-body-secondary rounded border mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Costo Total Reserva:</span>
                                    <span class="fw-bold"><?= dinero($estanciaSeleccionada['reserva_total']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 text-success">
                                    <span>Total Pagado:</span>
                                    <span class="fw-bold"><?= dinero($estanciaSeleccionada['anticipo']) ?></span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Saldo Pendiente:</span>
                                    <span class="fw-bold fs-5 <?= ((float)$estanciaSeleccionada['reserva_saldo'] > 0) ? 'text-danger' : 'text-success' ?>">
                                        <?= dinero($estanciaSeleccionada['reserva_saldo']) ?>
                                    </span>
                                </div>
                            </div>

                            <a href="<?= url('reservas/editar/' . $estanciaSeleccionada['reserva_id']) ?>" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-card-text me-1"></i> Ver Cuenta Completa de la Reserva
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm border-dashed text-center p-5 text-muted">
                        <i class="bi bi-box-arrow-right display-3 mb-3 text-secondary"></i>
                        <h5>Selecciona un huésped o habitación para realizar el Check-out</h5>
                        <p class="small">El formulario cargará el saldo pendiente y el tiempo de estadía transcurrido.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
function cambiarEstancia(id) {
    if (id) {
        window.location.href = '<?= url("recepcion/checkout?id=") ?>' + id;
    }
}
</script>
