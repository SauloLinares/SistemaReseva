<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Modificar Habitación <?= htmlspecialchars($habitacion['numero']) ?></h3>
                <p class="text-muted mb-0 small">Editar detalles, tipo, tarifa o estado</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('habitaciones') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <form action="<?= url('habitaciones/actualizar') ?>" method="POST" class="card shadow-sm">
                    <input type="hidden" name="id" value="<?= $habitacion['id'] ?>">

                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-door-closed me-2"></i>Actualizar Habitación</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Número de Habitación</label>
                                <input type="text" name="numero" class="form-control form-control-lg fw-bold" value="<?= htmlspecialchars($habitacion['numero']) ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Piso / Nivel</label>
                                <input type="text" name="piso" class="form-control form-control-lg" value="<?= htmlspecialchars($habitacion['piso']) ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Tipo de Habitación</label>
                                <select name="tipo_id" class="form-select form-select-lg" required>
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($t['id'] == $habitacion['tipo_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Precio por Noche (S/.)</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" step="0.01" name="precio_diario" class="form-control" value="<?= $habitacion['precio_diario'] ?>" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="disponible" <?= ($habitacion['estado'] === 'disponible') ? 'selected' : '' ?>>Disponible</option>
                                    <option value="ocupada" <?= ($habitacion['estado'] === 'ocupada') ? 'selected' : '' ?>>Ocupada</option>
                                    <option value="limpieza" <?= ($habitacion['estado'] === 'limpieza') ? 'selected' : '' ?>>Limpieza</option>
                                    <option value="mantenimiento" <?= ($habitacion['estado'] === 'mantenimiento') ? 'selected' : '' ?>>Mantenimiento</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción / Características</label>
                                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($habitacion['descripcion'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-body-tertiary d-flex justify-content-between">
                        <a href="<?= url('habitaciones') ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
