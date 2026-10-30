<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-plus-square text-warning me-2"></i>Registrar Habitación</h3>
                <p class="text-muted mb-0 small">Añadir nueva habitación al inventario hotelero</p>
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
                <form action="<?= url('habitaciones/guardar') ?>" method="POST" class="card shadow-sm">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-door-closed me-2"></i>Datos de la Habitación</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Número de Habitación <span class="text-danger">*</span></label>
                                <input type="text" name="numero" class="form-control form-control-lg fw-bold" placeholder="Ej: 101, 204B" required autofocus>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Piso / Nivel <span class="text-danger">*</span></label>
                                <input type="text" name="piso" class="form-control form-control-lg" value="1" placeholder="Ej: 1, 2, PB" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Tipo de Habitación <span class="text-danger">*</span></label>
                                <select name="tipo_id" id="tipo_id" class="form-select form-select-lg" required>
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?= $t['id'] ?>" data-precio="<?= $t['precio_base'] ?>">
                                            <?= htmlspecialchars($t['nombre']) ?> (Base: <?= dinero($t['precio_base']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Precio por Noche (S/.) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" step="0.01" name="precio_diario" id="precio_diario" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Estado Inicial</label>
                                <select name="estado" class="form-select">
                                    <option value="disponible" selected>Disponible</option>
                                    <option value="ocupada">Ocupada</option>
                                    <option value="limpieza">Limpieza</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción / Características Específicas</label>
                                <textarea name="descripcion" class="form-control" rows="3" placeholder="Ej: Vista a la piscina, cama matrimonial, frigobar, baño con tina..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-body-tertiary d-flex justify-content-between">
                        <a href="<?= url('habitaciones') ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-warning text-dark fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Guardar Habitación
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo_id');
    const precioInput = document.getElementById('precio_diario');

    function actualizarPrecioBase() {
        const selected = tipoSelect.options[tipoSelect.selectedIndex];
        if (selected) {
            precioInput.value = selected.dataset.precio || '0.00';
        }
    }

    tipoSelect.addEventListener('change', actualizarPrecioBase);
    actualizarPrecioBase();
});
</script>
