<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-arrow-left-right text-primary me-2"></i>Asignación y Cambio de Habitación</h3>
                <p class="text-muted mb-0 small">Reubicación de huéspedes en estadía a nuevas habitaciones disponibles</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('recepcion/estadias') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Control de Estadías
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

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-top border-4 border-primary">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-shuffle me-2"></i>Mover Huésped a Otra Habitación</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('recepcion/procesarCambio') ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">1. Seleccionar Huésped y Habitación Actual <span class="text-danger">*</span></label>
                                <select name="estancia_id" class="form-select form-select-lg" required>
                                    <option value="">-- Elige la habitación a desocupar --</option>
                                    <?php foreach ($activas as $act): ?>
                                        <option value="<?= $act['id'] ?>">
                                            Hab. <?= htmlspecialchars($act['habitacion_numero']) ?> (Piso <?= $act['piso'] ?> - <?= htmlspecialchars($act['tipo_nombre']) ?>) - Huésped: <?= htmlspecialchars($act['huesped_nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">2. Seleccionar Nueva Habitación de Destino (Disponible) <span class="text-danger">*</span></label>
                                <select name="nueva_habitacion_id" class="form-select form-select-lg" required>
                                    <option value="">-- Elige la nueva habitación disponible --</option>
                                    <?php foreach ($disponibles as $d): ?>
                                        <option value="<?= $d['id'] ?>">
                                            Hab. <?= htmlspecialchars($d['numero']) ?> - <?= htmlspecialchars($d['tipo_nombre']) ?> (Piso <?= $d['piso'] ?> - <?= dinero($d['precio_diario']) ?>/noche)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Motivo del Cambio o Reubicación</label>
                                <textarea name="motivo" class="form-control" rows="2" placeholder="Ej: Falla en aire acondicionado, ascenso a suite, requerimiento de piso bajo..."></textarea>
                            </div>

                            <div class="alert alert-secondary py-2 small d-flex align-items-center">
                                <i class="bi bi-info-circle me-2 fs-5"></i>
                                <div>La habitación anterior cambiará automáticamente a estado <strong>LIMPIEZA</strong> para que el personal de servicio la acondicione de inmediato.</div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                    <i class="bi bi-arrow-left-right me-2"></i>Ejecutar Reubicación de Habitación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
