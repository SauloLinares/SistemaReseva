<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-cart-plus text-success me-2"></i>Registrar Consumo de Servicio</h3>
                <p class="text-muted mb-0 small">Carga de consumos de bar, room service, lavandería o restaurante a la habitación</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('servicios/porHuesped') ?>" class="btn btn-outline-secondary btn-sm shadow-sm me-1">
                    <i class="bi bi-person-lines-fill me-1"></i> Ver Consumos
                </a>
                <a href="<?= url('servicios') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Catálogo
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
                <form action="<?= url('servicios/guardarConsumo') ?>" method="POST" class="card shadow-sm border-top border-4 border-success">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-cart4 me-2"></i>Cargar Consumo</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Habitación / Huésped en Estadía <span class="text-danger">*</span></label>
                                <select name="reserva_id" class="form-select form-select-lg" required>
                                    <option value="">-- Seleccionar huésped alojado --</option>
                                    <?php foreach ($estadias as $est): ?>
                                        <option value="<?= $est['reserva_id'] ?>" <?= ($reservaIdFijada == $est['reserva_id']) ? 'selected' : '' ?>>
                                            Hab. <?= htmlspecialchars($est['habitacion_numero']) ?> (Piso <?= $est['piso'] ?>) - <?= htmlspecialchars($est['huesped_nombre']) ?> (Reserva: <?= htmlspecialchars($est['codigo_reserva']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold">Servicio Contratado / Consumido <span class="text-danger">*</span></label>
                                <select name="servicio_id" id="servicio_id" class="form-select form-select-lg" required onchange="calcularTotal()">
                                    <option value="">-- Seleccionar del catálogo --</option>
                                    <?php foreach ($servicios as $s): ?>
                                        <option value="<?= $s['id'] ?>" data-precio="<?= $s['precio'] ?>">
                                            <?= htmlspecialchars($s['nombre']) ?> - <?= dinero($s['precio']) ?> (<?= htmlspecialchars($s['categoria']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" name="cantidad" id="inputCantidad" class="form-control form-control-lg text-center" value="1" min="1" max="50" required onchange="calcularTotal()" onkeyup="calcularTotal()">
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold">Total a Cargar:</span>
                                    <span class="fs-4 fw-bold text-success" id="displayTotalConsumo">S/. 0.00</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Forma de Facturación</label>
                                <select name="pagado" class="form-select">
                                    <option value="0" selected>Cargar a la cuenta de la habitación (Pendiente de cobro al check-out)</option>
                                    <option value="1">Cobrado en el acto (Pagado inmediatamente)</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Método de Pago (Si se cobra de inmediato)</label>
                                <select name="metodo_pago" class="form-select">
                                    <option value="Efectivo" selected>Efectivo</option>
                                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                    <option value="Yape/Plin">Yape / Plin</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notas / Detalles Adicionales</label>
                                <input type="text" name="notas" class="form-control" placeholder="Ej: Entregado a las 11:30 PM en habitación 201">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-body-tertiary d-flex justify-content-between">
                        <a href="<?= url('servicios') ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Confirmar y Registrar Consumo
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function calcularTotal() {
    const sel = document.getElementById('servicio_id');
    const cantInput = document.getElementById('inputCantidad');
    const opt = sel.options[sel.selectedIndex];
    const precio = opt ? parseFloat(opt.dataset.precio || 0) : 0;
    const cant = parseInt(cantInput.value || 1);
    const total = precio * cant;
    document.getElementById('displayTotalConsumo').textContent = 'S/. ' + total.toFixed(2);
}
</script>
