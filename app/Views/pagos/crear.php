<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-cash-coin text-success me-2"></i>Registrar Pago / Cobro</h3>
                <p class="text-muted mb-0 small">Emisión de boletas, facturas o recibos de caja para reservas</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('pagos') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Historial de Pagos
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
                <form action="<?= url('pagos/guardar') ?>" method="POST" class="card shadow-sm">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>Datos del Comprobante y Cobro</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Seleccionar Reserva con Saldo Pendiente <span class="text-danger">*</span></label>
                                <select name="reserva_id" id="selectReserva" class="form-select form-select-lg" required onchange="actualizarSaldo(this)">
                                    <option value="">-- Elige una reserva para cobrar --</option>
                                    <?php foreach ($saldosPendientes as $sp): ?>
                                        <option value="<?= $sp['id'] ?>" data-saldo="<?= $sp['saldo'] ?>" data-total="<?= $sp['total'] ?>" data-huesped="<?= htmlspecialchars($sp['huesped_nombre']) ?>" <?= ($reservaSeleccionada && $reservaSeleccionada['id'] == $sp['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sp['codigo_reserva']) ?> - <?= htmlspecialchars($sp['huesped_nombre']) ?> (Hab: <?= htmlspecialchars($sp['habitaciones'] ?: 'S/N') ?> | Pendiente: <?= dinero($sp['saldo']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Tipo de Comprobante <span class="text-danger">*</span></label>
                                <select name="comprobante_tipo" id="comprobante_tipo" class="form-select" onchange="actualizarNumeroComprobante(this.value)">
                                    <option value="Boleta" selected>Boleta de Venta</option>
                                    <option value="Factura">Factura</option>
                                    <option value="Recibo de Caja">Recibo de Caja</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Número de Comprobante <span class="text-danger">*</span></label>
                                <input type="text" name="comprobante_numero" id="comprobante_numero" class="form-control" value="<?= htmlspecialchars($numBoleta) ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Monto a Cobrar (S/.) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" step="0.01" name="monto" id="inputMonto" class="form-control fw-bold" placeholder="0.00" value="<?= $reservaSeleccionada ? $reservaSeleccionada['saldo'] : '' ?>" required>
                                </div>
                                <div class="form-text text-muted" id="textoSaldoAyuda">Ingresa el valor total o parcial a abonar.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Método de Pago <span class="text-danger">*</span></label>
                                <select name="metodo_pago" class="form-select form-select-lg">
                                    <option value="Efectivo" selected>Efectivo</option>
                                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                    <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                    <option value="Yape/Plin">Yape / Plin</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Tipo de Operación</label>
                                <select name="tipo" class="form-select">
                                    <option value="pago" selected>Pago / Liquidación</option>
                                    <option value="anticipo">Anticipo</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Fecha y Hora de Pago</label>
                                <input type="datetime-local" name="fecha_pago" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notas / Observaciones del Pago</label>
                                <textarea name="notas" class="form-control" rows="2" placeholder="Ej: Cancelación de estadía, pago con POS Visa..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-body-tertiary d-flex justify-content-between">
                        <a href="<?= url('pagos') ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success btn-lg fw-bold">
                            <i class="bi bi-printer me-1"></i> Registrar Cobro y Emitir
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
const correlativos = {
    'Boleta': '<?= htmlspecialchars($numBoleta) ?>',
    'Factura': '<?= htmlspecialchars($numFactura) ?>',
    'Recibo de Caja': '<?= htmlspecialchars($numRecibo) ?>'
};

function actualizarNumeroComprobante(tipo) {
    document.getElementById('comprobante_numero').value = correlativos[tipo] || '001-000001';
}

function actualizarSaldo(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.saldo) {
        document.getElementById('inputMonto').value = opt.dataset.saldo;
        document.getElementById('textoSaldoAyuda').textContent = 'Saldo total adeudado: S/. ' + opt.dataset.saldo;
    }
}
</script>
