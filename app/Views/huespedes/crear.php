<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-person-plus text-success me-2"></i>Registrar Nuevo Huésped</h3>
                <p class="text-muted mb-0 small">Ingresa la información personal y datos de contacto del cliente</p>
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

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <form action="<?= url('huespedes/guardar') ?>" method="POST" class="card shadow-sm">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-person-vcard me-2"></i>Ficha del Huésped</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Tipo de Documento <span class="text-danger">*</span></label>
                                <select name="tipo_documento" class="form-select" required>
                                    <option value="DNI">DNI (Perú)</option>
                                    <option value="Pasaporte">Pasaporte (Extranjero)</option>
                                    <option value="Carnet Extranjería">Carnet de Extranjería</option>
                                    <option value="Otro">Otro Documento</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold">Número de Documento <span class="text-danger">*</span></label>
                                <input type="text" name="numero_documento" class="form-control" placeholder="Ej: 71829341 o PAS902188" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombres" class="form-control" placeholder="Ej: Carlos Alberto" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" class="form-control" placeholder="Ej: Mendoza Quispe" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Correo Electrónico</label>
                                <input type="email" name="correo" class="form-control" placeholder="cliente@correo.com">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Teléfono / Celular</label>
                                <input type="text" name="telefono" class="form-control" placeholder="+51 987 654 321">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Nacionalidad</label>
                                <input type="text" name="nacionalidad" class="form-control" value="Perú" placeholder="Ej: Perú, España, Colombia...">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="1" selected>Activo (Habilitado para reservar)</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Dirección de Domicilio / Ciudad</label>
                                <input type="text" name="direccion" class="form-control" placeholder="Av. Principal 123, Ciudad">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notas o Preferencias Especiales</label>
                                <textarea name="notas" class="form-control" rows="2" placeholder="Ej: Habitación en piso bajo, vegetariano, cliente frecuente VIP..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-body-tertiary d-flex justify-content-between">
                        <a href="<?= url('huespedes') ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success fw-semibold">
                            <i class="bi bi-check-circle me-1"></i> Guardar Huésped
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
