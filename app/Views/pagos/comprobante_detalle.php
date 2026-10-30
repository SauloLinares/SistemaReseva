<div class="app-content-header d-print-none">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-printer text-primary me-2"></i>Comprobante de Pago</h3>
                <p class="text-muted mb-0 small">Visualización oficial para entrega o impresión al cliente</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <button type="button" class="btn btn-primary btn-sm shadow-sm me-1" onclick="window.print()">
                    <i class="bi bi-printer-fill me-1"></i> Imprimir Documento
                </button>
                <a href="<?= url('pagos') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Pagos
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-8">
                <div class="card shadow border p-4 bg-white" id="imprimible">
                    <!-- Encabezado del Hotel -->
                    <div class="row align-items-center border-bottom pb-4 mb-4">
                        <div class="col-sm-7">
                            <h3 class="fw-bold text-primary mb-1">🏨 <?= htmlspecialchars($config['hotel_nombre'] ?? 'Hotel Manager & Resort') ?></h3>
                            <p class="text-muted small mb-1">
                                RUC: <strong><?= htmlspecialchars($config['hotel_ruc'] ?? '20491823901') ?></strong><br>
                                <?= htmlspecialchars($config['hotel_direccion'] ?? 'Av. Del Sol 1245, Zona Turística') ?><br>
                                Tel: <?= htmlspecialchars($config['hotel_telefono'] ?? '+51 987 654 321') ?> | Email: <?= htmlspecialchars($config['hotel_correo'] ?? 'reservas@hotel.com') ?>
                            </p>
                        </div>
                        <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                            <div class="border border-2 border-primary rounded p-3 text-center bg-light">
                                <h5 class="fw-bold mb-1 text-uppercase"><?= htmlspecialchars($pago['comprobante_tipo']) ?></h5>
                                <div class="fs-4 fw-bold text-primary"><?= htmlspecialchars($pago['comprobante_numero']) ?></div>
                                <small class="text-muted">Fecha: <?= fecha_es($pago['fecha_pago'], true) ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Cliente y Reserva -->
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h6 class="fw-bold text-secondary text-uppercase small">Datos del Huésped / Adquiriente</h6>
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($pago['huesped_nombre']) ?></h5>
                            <div class="small text-muted">
                                Documento: <strong><?= htmlspecialchars($pago['tipo_documento'] . ' ' . $pago['numero_documento']) ?></strong><br>
                                Dirección: <?= htmlspecialchars($pago['direccion'] ?: 'No registrada') ?><br>
                                Teléfono: <?= htmlspecialchars($pago['telefono'] ?: '-') ?> | Email: <?= htmlspecialchars($pago['correo'] ?: '-') ?>
                            </div>
                        </div>
                        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                            <h6 class="fw-bold text-secondary text-uppercase small">Referencia de Estadía</h6>
                            <div class="small">
                                Reserva Código: <strong class="text-primary"><?= htmlspecialchars($pago['codigo_reserva']) ?></strong><br>
                                Check-in: <?= fecha_es($pago['fecha_entrada']) ?><br>
                                Check-out: <?= fecha_es($pago['fecha_salida']) ?><br>
                                Método de Pago: <span class="badge text-bg-light border"><?= htmlspecialchars($pago['metodo_pago']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Detalle -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripción / Concepto</th>
                                    <th class="text-center" style="width: 100px;">Tipo</th>
                                    <th class="text-end" style="width: 150px;">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>Pago de Servicios Hoteleros y Hospedaje</strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($pago['notas'] ?: ('Pago registrado para la reserva ' . $pago['codigo_reserva'])) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-secondary text-uppercase"><?= htmlspecialchars($pago['tipo']) ?></span>
                                    </td>
                                    <td class="text-end fw-bold fs-6">
                                        <?= dinero($pago['monto']) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totales -->
                    <div class="row justify-content-end mb-4">
                        <div class="col-sm-5">
                            <div class="p-3 bg-light rounded border">
                                <?php
                                    $igvPorcentaje = 18;
                                    $totalMonto = (float)$pago['monto'];
                                    $subtotal = round($totalMonto / (1 + ($igvPorcentaje / 100)), 2);
                                    $igv = round($totalMonto - $subtotal, 2);
                                ?>
                                <div class="d-flex justify-content-between mb-1 small">
                                    <span>Op. Gravada:</span>
                                    <span><?= dinero($subtotal) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 small">
                                    <span>I.G.V. (<?= $igvPorcentaje ?>%):</span>
                                    <span><?= dinero($igv) ?></span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5">TOTAL:</span>
                                    <span class="fw-bold fs-4 text-primary"><?= dinero($totalMonto) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pie del Comprobante -->
                    <div class="border-top pt-3 text-center small text-muted">
                        <p class="mb-1">¡Gracias por su preferencia! Que disfrute su estadía en <strong><?= htmlspecialchars($config['hotel_nombre'] ?? 'Hotel Manager') ?></strong>.</p>
                        <p class="mb-0">Este documento constituye comprobante válido de pago conforme a las políticas del establecimiento.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
@media print {
    .app-header, .app-sidebar, .app-footer, .d-print-none {
        display: none !important;
    }
    .app-main, .app-wrapper, body {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
    }
}
</style>
