<?php partial('page_header', [
    'titulo'              => 'Comprobante de Pago',
    'icono'               => 'bi-printer',
    'subtitulo'           => 'Visualización oficial para entrega o impresión al cliente',
    'acciones'            => [
        ['texto' => 'Imprimir Documento', 'imprimir' => true, 'icono' => 'bi-printer-fill'],
        ['texto' => 'Volver a Pagos', 'url' => 'pagos', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
    'ocultar_al_imprimir' => true,
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-8">
                <div class="card shadow border p-4 bg-white" id="imprimible">
                    <!-- Encabezado del Hotel -->
                    <div class="row align-items-center border-bottom pb-4 mb-4">
                        <div class="col-sm-7">
                            <!-- Logo al costado del nombre (mismo marco que el voucher); sin logo, el emoji de siempre -->
                            <?php $hayLogo = !empty($config['hotel_logo']) && file_exists(__DIR__ . '/../../../public/uploads/hotel/' . $config['hotel_logo']); ?>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <?php if ($hayLogo): ?>
                                    <span class="logo-marco voucher-logo-marco"><img src="<?= e(base_url('uploads/hotel/' . $config['hotel_logo'])) ?>" alt="Logo de <?= e($config['hotel_nombre'] ?? 'el hotel') ?>"></span>
                                <?php endif; ?>
                                <h3 class="fw-bold text-primary mb-0"><?= $hayLogo ? '' : '🏨 ' ?><?= htmlspecialchars($config['hotel_nombre'] ?? 'Hotel Manager & Resort') ?></h3>
                            </div>
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
                                Método de Pago:
                                <?php foreach ($lineas as $l): ?>
                                    <span class="badge text-bg-light border"><?= e($l['metodo_pago']) ?><?= count($lineas) > 1 ? ' ' . dinero($l['monto']) : '' ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <?php
                        // Un cobro dividido en varios métodos es un solo comprobante: se suman sus líneas
                        $lineas = $lineas ?: [['metodo_pago' => $pago['metodo_pago'], 'monto' => $pago['monto']]];
                        $totalComprobante = array_sum(array_map(fn($l) => (float)$l['monto'], $lineas));
                    ?>
                    <!-- Tabla de Detalle -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripción / Concepto</th>
                                    <th class="text-center col-w-sm">Tipo</th>
                                    <th class="text-end col-w-md">Importe</th>
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
                                        <?= dinero($totalComprobante) ?>
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
                                    $totalMonto = $totalComprobante;
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
