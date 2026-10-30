<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Modificar Huésped</h3>
                <p class="text-muted mb-0 small">Editar datos de <?= htmlspecialchars($huesped['nombres'] . ' ' . $huesped['apellidos']) ?></p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('huespedes') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
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
                <form action="<?= url('huespedes/actualizar') ?>" method="POST" class="card shadow-sm">
                    <input type="hidden" name="id" value="<?= $huesped['id'] ?>">

                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-person-vcard me-2"></i>Actualizar Ficha del Huésped</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Tipo de Documento</label>
                                <select name="tipo_documento" class="form-select" required>
                                    <option value="DNI" <?= ($huesped['tipo_documento'] === 'DNI') ? 'selected' : '' ?>>DNI (Perú)</option>
                                    <option value="Pasaporte" <?= ($huesped['tipo_documento'] === 'Pasaporte') ? 'selected' : '' ?>>Pasaporte</option>
                                    <option value="Carnet Extranjería" <?= ($huesped['tipo_documento'] === 'Carnet Extranjería') ? 'selected' : '' ?>>Carnet Extranjería</option>
                                    <option value="Otro" <?= ($huesped['tipo_documento'] === 'Otro') ? 'selected' : '' ?>>Otro</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold">Número de Documento</label>
                                <input type="text" name="numero_documento" class="form-control" value="<?= htmlspecialchars($huesped['numero_documento']) ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" name="nombres" class="form-control" value="<?= htmlspecialchars($huesped['nombres']) ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control" value="<?= htmlspecialchars($huesped['apellidos']) ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Correo Electrónico</label>
                                <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($huesped['correo'] ?? '') ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Teléfono / Celular</label>
                                <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($huesped['telefono'] ?? '') ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Nacionalidad</label>
                                <input type="text" name="nacionalidad" class="form-control" value="<?= htmlspecialchars($huesped['nacionalidad'] ?? 'Perú') ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="1" <?= $huesped['estado'] ? 'selected' : '' ?>>Activo</option>
                                    <option value="0" <?= !$huesped['estado'] ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($huesped['direccion'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notas</label>
                                <textarea name="notas" class="form-control" rows="2"><?= htmlspecialchars($huesped['notas'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-body-tertiary d-flex justify-content-between">
                        <a href="<?= url('huespedes') ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
