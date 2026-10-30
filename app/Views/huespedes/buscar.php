<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-search text-primary me-2"></i>Buscar Huésped por Documento</h3>
                <p class="text-muted mb-0 small">Búsqueda rápida por DNI, pasaporte o carnet de extranjería</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('huespedes/crear') ?>" class="btn btn-success btn-sm shadow-sm me-1">
                    <i class="bi bi-person-plus me-1"></i> Nuevo Huésped
                </a>
                <a href="<?= url('huespedes') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-card-list me-1"></i> Directorio
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <!-- Buscador Principal -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <form method="GET" action="<?= url('huespedes/buscar') ?>">
                            <label class="form-label fs-5 fw-bold text-body-emphasis mb-2">
                                <i class="bi bi-person-badge text-primary me-2"></i>Ingresa el número de DNI o Pasaporte:
                            </label>
                            <div class="input-group input-group-lg mb-2">
                                <input type="text" name="documento" class="form-control" placeholder="Ej: 71829341 ó PAS902188" value="<?= htmlspecialchars($documento) ?>" autofocus required>
                                <button class="btn btn-primary px-4 fw-bold" type="submit">
                                    <i class="bi bi-search me-1"></i> Buscar Huésped
                                </button>
                            </div>
                            <small class="text-muted">Búsqueda exacta por DNI, pasaporte, o nombre parcial.</small>
                        </form>
                    </div>
                </div>

                <?php if (!empty($documento)): ?>
                    <?php if ($huesped): ?>
                        <!-- Ficha de Resultado -->
                        <div class="card shadow-sm border-success">
                            <div class="card-header bg-success-subtle text-success-emphasis d-flex justify-content-between align-items-center">
                                <span class="fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Huésped Encontrado</span>
                                <span class="badge text-bg-success">Cliente Registrado</span>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($huesped['nombres'] . ' ' . $huesped['apellidos']) ?></h4>
                                        <div class="text-muted mb-2">
                                            <span class="badge text-bg-light border me-2">
                                                <?= htmlspecialchars($huesped['tipo_documento']) ?>: <?= htmlspecialchars($huesped['numero_documento']) ?>
                                            </span>
                                            <span class="badge text-bg-secondary-subtle text-secondary-emphasis">
                                                Nacionalidad: <?= htmlspecialchars($huesped['nacionalidad'] ?: 'Perú') ?>
                                            </span>
                                        </div>
                                        <div class="small">
                                            <i class="bi bi-telephone text-muted me-1"></i> <?= htmlspecialchars($huesped['telefono'] ?: 'No registrado') ?> &nbsp;|&nbsp;
                                            <i class="bi bi-envelope text-muted me-1"></i> <?= htmlspecialchars($huesped['correo'] ?: 'No registrado') ?> &nbsp;|&nbsp;
                                            <i class="bi bi-geo-alt text-muted me-1"></i> <?= htmlspecialchars($huesped['direccion'] ?: 'No registrada') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-body-tertiary d-flex justify-content-end gap-2">
                                <a href="<?= url('huespedes/historial/' . $huesped['id']) ?>" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-clock-history me-1"></i> Ver Historial de Estadías
                                </a>
                                <a href="<?= url('huespedes/editar/' . $huesped['id']) ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil-square me-1"></i> Editar Datos
                                </a>
                                <a href="<?= url('reservas/crear?huesped_id=' . $huesped['id']) ?>" class="btn btn-success btn-sm">
                                    <i class="bi bi-calendar-plus me-1"></i> Crear Nueva Reserva
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No Encontrado -->
                        <div class="card shadow-sm border-warning text-center p-4">
                            <i class="bi bi-person-x text-warning display-4 mb-2"></i>
                            <h5>No se encontró ningún huésped con el documento: "<?= htmlspecialchars($documento) ?>"</h5>
                            <p class="text-muted small">Puedes registrar a este nuevo cliente en el sistema en menos de un minuto.</p>
                            <div class="mt-2">
                                <a href="<?= url('huespedes/crear?num_doc=' . urlencode($documento)) ?>" class="btn btn-success">
                                    <i class="bi bi-person-plus me-1"></i> Registrar como Nuevo Huésped
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
