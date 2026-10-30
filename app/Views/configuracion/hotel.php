<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-building text-primary me-2"></i>Datos del Hotel</h3>
                <p class="text-muted mb-0 small">Información institucional y comercial para comprobantes y reportes</p>
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
                <form action="<?= url('configuracion/guardarHotel') ?>" method="POST" class="card shadow-sm border-top border-4 border-primary">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-sliders me-2"></i>Perfil del Establecimiento</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold">Nombre Comercial del Hotel <span class="text-danger">*</span></label>
                                <input type="text" name="hotel_nombre" class="form-control form-control-lg" value="<?= htmlspecialchars($config['hotel_nombre'] ?? 'Hotel Manager & Resort Gran Sol') ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">RUC / NIF / Identificación Tributaria <span class="text-danger">*</span></label>
                                <input type="text" name="hotel_ruc" class="form-control form-control-lg" value="<?= htmlspecialchars($config['hotel_ruc'] ?? '20491823901') ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Teléfonos de Contacto</label>
                                <input type="text" name="hotel_telefono" class="form-control" value="<?= htmlspecialchars($config['hotel_telefono'] ?? '+51 1 450-8900 / +51 987 654 321') ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Correo Electrónico Oficial</label>
                                <input type="email" name="hotel_correo" class="form-control" value="<?= htmlspecialchars($config['hotel_correo'] ?? 'reservas@hotelmanager.com') ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Dirección Física del Hotel</label>
                                <input type="text" name="hotel_direccion" class="form-control" value="<?= htmlspecialchars($config['hotel_direccion'] ?? 'Av. Del Sol 1245, Zona Turística, Perú') ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Hora Oficial de Check-in</label>
                                <input type="time" name="hotel_checkin_hora" class="form-control" value="<?= htmlspecialchars($config['hotel_checkin_hora'] ?? '14:00') ?>">
                                <div class="form-text">Hora de entrega de llaves.</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Hora Oficial de Check-out</label>
                                <input type="time" name="hotel_checkout_hora" class="form-control" value="<?= htmlspecialchars($config['hotel_checkout_hora'] ?? '12:00') ?>">
                                <div class="form-text">Hora límite de desocupación.</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-body-tertiary text-end">
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="bi bi-save me-1"></i> Guardar Datos del Hotel
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
