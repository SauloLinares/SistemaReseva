<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-cash-stack text-success me-2"></i>Tarifas por Noche</h3>
                <p class="text-muted mb-0 small">Ajuste y actualización de precios diarios por habitación</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('habitaciones/tipos') ?>" class="btn btn-outline-primary btn-sm shadow-sm me-1">
                    <i class="bi bi-tags me-1"></i> Precios Base por Tipo
                </a>
                <a href="<?= url('habitaciones') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Habitaciones
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
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Tarifario Actual de Habitaciones</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Piso</th>
                                <th>Tipo de Habitación</th>
                                <th>Tarifa Base Tipo</th>
                                <th>Tarifa Actual de la Habitación</th>
                                <th class="text-end pe-4">Actualizar Tarifa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($habitaciones as $h): ?>
                                <tr>
                                    <td class="fw-bold fs-5 text-primary">Hab. <?= htmlspecialchars($h['numero']) ?></td>
                                    <td>Piso <?= htmlspecialchars($h['piso']) ?></td>
                                    <td><span class="badge text-bg-light border"><?= htmlspecialchars($h['tipo_nombre']) ?></span></td>
                                    <td><?= dinero($h['precio_base'] ?? 0) ?></td>
                                    <td>
                                        <span class="fs-5 fw-bold text-success"><?= dinero($h['precio_diario']) ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form method="POST" action="<?= url('habitaciones/guardarTarifa') ?>" class="d-inline-flex align-items-center gap-2">
                                            <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                            <div class="input-group input-group-sm" style="width: 170px;">
                                                <span class="input-group-text">S/.</span>
                                                <input type="number" step="0.01" name="precio_diario" class="form-control text-end fw-bold" value="<?= $h['precio_diario'] ?>" required>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Guardar tarifa">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
