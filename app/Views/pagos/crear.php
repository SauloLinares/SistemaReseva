<?php partial('page_header', [
    'titulo'    => 'Registrar Pago / Cobro',
    'icono'     => 'bi-cash-coin',
    'subtitulo' => 'Emisión de boletas, facturas o recibos de caja para reservas',
    'acciones'  => [
        ['texto' => 'Historial de Pagos', 'url' => 'pagos', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12 col-lg-8">
                <form action="<?= url('pagos/guardar') ?>" method="POST" class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-receipt me-2"></i>Datos del Comprobante y Cobro</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="selectReserva" class="form-label">Seleccionar Reserva con Saldo Pendiente <span class="text-danger">*</span></label>
                                <select name="reserva_id" id="selectReserva" class="form-select form-select-lg" required>
                                    <option value="">-- Elige una reserva para cobrar --</option>
                                    <?php foreach ($saldosPendientes as $sp): ?>
                                        <option value="<?= $sp['id'] ?>" data-saldo="<?= $sp['saldo'] ?>" data-total="<?= $sp['total'] ?>" data-huesped="<?= htmlspecialchars($sp['huesped_nombre']) ?>" <?= ($reservaSeleccionada && $reservaSeleccionada['id'] == $sp['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sp['codigo_reserva']) ?> - <?= htmlspecialchars($sp['huesped_nombre']) ?> (Hab: <?= htmlspecialchars($sp['habitaciones'] ?: 'S/N') ?> | Pendiente: <?= dinero($sp['saldo']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="comprobante_tipo" class="form-label">Tipo de Comprobante <span class="text-danger">*</span></label>
                                <select name="comprobante_tipo" id="comprobante_tipo" class="form-select" data-correlativos="<?= e(json_encode(['Boleta' => $numBoleta, 'Factura' => $numFactura, 'Recibo de Caja' => $numRecibo], JSON_UNESCAPED_UNICODE)) ?>">
                                    <option value="Boleta" selected>Boleta de Venta</option>
                                    <option value="Factura">Factura</option>
                                    <option value="Recibo de Caja">Recibo de Caja</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="comprobante_numero" class="form-label">Número de Comprobante <span class="text-danger">*</span></label>
                                <input type="text" name="comprobante_numero" id="comprobante_numero" class="form-control" value="<?= htmlspecialchars($numBoleta) ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="inputMonto" class="form-label">Monto a Cobrar (S/.) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" step="0.01" name="monto" id="inputMonto" class="form-control fw-bold" placeholder="0.00" value="<?= $reservaSeleccionada ? $reservaSeleccionada['saldo'] : '' ?>" required>
                                </div>
                                <div class="form-text text-muted" id="textoSaldoAyuda">Ingresa el valor total o parcial a abonar.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="metodo_pago" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                                <select id="metodo_pago" name="metodo_pago" class="form-select form-select-lg">
                                    <option value="Efectivo" selected>Efectivo</option>
                                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                    <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                    <option value="Yape/Plin">Yape / Plin</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="tipo" class="form-label">Tipo de Operación</label>
                                <select id="tipo" name="tipo" class="form-select">
                                    <option value="pago" selected>Pago / Liquidación</option>
                                    <option value="anticipo">Anticipo</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="fecha_pago" class="form-label">Fecha y Hora de Pago</label>
                                <input id="fecha_pago" type="datetime-local" name="fecha_pago" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="notas" class="form-label">Notas / Observaciones del Pago</label>
                                <textarea id="notas" name="notas" class="form-control" rows="2" placeholder="Ej: Cancelación de estadía, pago con POS Visa..."></textarea>
                            </div>
                        </div>
                    </div>
                    <?php partial('form_actions', ['cancelar' => 'pagos', 'texto' => 'Registrar Cobro y Emitir', 'icono' => 'bi-printer', 'variante' => 'success']) ?>
                </form>
            </div>
        </div>

    </div>
</div>

<?= script_js('vistas/pagos-crear.js') ?>
