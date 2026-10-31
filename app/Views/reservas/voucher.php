<?php partial('page_header', [
    'titulo'              => 'Voucher Oficial de Confirmación',
    'icono'               => 'bi-file-earmark-check',
    'subtitulo'           => 'Comprobante de reserva para el huésped y recepción',
    'acciones'            => [
        ['texto' => 'Imprimir Voucher', 'imprimir' => true, 'icono' => 'bi-printer'],
        ['texto' => 'Lista de Reservas', 'url' => 'reservas', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
        ['texto' => 'Modificar', 'url' => 'reservas/editar/' . $reserva['id'], 'icono' => 'bi-pencil', 'variante' => 'outline-primary'],
    ],
    'ocultar_al_imprimir' => true,
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($esNuevo)): ?>
            <!-- Banner de Éxito y Acciones Inmediatas -->
            <div class="alert alert-success d-print-none shadow-sm rounded-3 p-3 mb-4 border-success-subtle">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center avatar-md">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-success">¡Reserva <?= htmlspecialchars($reserva['codigo_reserva']) ?> Confirmada con Éxito!</h5>
                            <p class="mb-0 small text-muted">La reserva ya fue registrada en el calendario y se generó el voucher oficial.</p>
                        </div>
                    </div>
                    
                    <!-- Botón de Envío Instantáneo por WhatsApp -->
                    <?php 
                        $telLimpio = preg_replace('/[^0-9]/', '', $reserva['huesped_telefono'] ?? '');
                        if (strlen($telLimpio) === 9 && !str_starts_with($telLimpio, '51')) {
                            $telLimpio = '51' . $telLimpio; // Prefijo Perú por defecto
                        }
                        
                        $mensajeWs = "¡Hola " . urlencode($reserva['huesped_nombre']) . "! 👋 Le saludamos de *" . urlencode($hotel['hotel_nombre'] ?? 'Hotel Manager') . "*.\n\n"
                                   . "Su reserva ha sido *CONFIRMADA* con éxito ✅\n\n"
                                   . "📌 *Código:* " . $reserva['codigo_reserva'] . "\n"
                                   . "📅 *Check-in:* " . date('d/m/Y', strtotime($reserva['fecha_entrada'])) . " (14:00 hrs)\n"
                                   . "📅 *Check-out:* " . date('d/m/Y', strtotime($reserva['fecha_salida'])) . " (12:00 hrs)\n"
                                   . "🌙 *Noches:* " . $reserva['noches'] . "\n"
                                   . "💰 *Total:* S/. " . number_format($reserva['total'], 2) . "\n"
                                   . "💵 *Saldo en Check-in:* S/. " . number_format($reserva['saldo'], 2) . "\n\n"
                                   . "¡Será un placer recibirle! Cualquier duda estamos a su servicio.";
                        $urlWs = "https://api.whatsapp.com/send?phone=" . $telLimpio . "&text=" . $mensajeWs;
                    ?>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= url('reservas') ?>" class="btn btn-primary">
                            <i class="bi bi-list-check me-1"></i> Ver en Listado de Reservas
                        </a>

                        <?php if (!empty($telLimpio)): ?>
                            <a href="<?= $urlWs ?>" target="_blank" class="btn btn-success">
                                <i class="bi bi-whatsapp me-1"></i> Enviar por WhatsApp
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($reserva['fecha_entrada'] === date('Y-m-d') && $reserva['estado'] !== 'en_estadia'): ?>
                            <a href="<?= url('recepcion/checkin') ?>" class="btn btn-outline-dark">
                                <i class="bi bi-box-arrow-in-right text-success me-1"></i> Pasar a Check-in Hoy
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Voucher Oficial Imprimible -->
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9 col-xl-8">
                <div class="card border-0 mb-4" id="imprimible">
                    <div class="card-body p-4 p-md-5">

                        <!-- Encabezado del Hotel -->
                        <div class="row align-items-center border-bottom pb-4 mb-4">
                            <div class="col-sm-7">
                                <?php if (!empty($hotel['hotel_logo']) && file_exists(__DIR__ . '/../../../public/uploads/' . $hotel['hotel_logo'])): ?>
                                    <img src="<?= base_url('uploads/' . $hotel['hotel_logo']) ?>" alt="Logo Hotel" class="mb-2 voucher-logo">
                                <?php endif; ?>
                                <h4 class="fw-bold mb-1 text-uppercase text-dark"><?= htmlspecialchars($hotel['hotel_nombre'] ?? 'Hotel Manager & Suites') ?></h4>
                                <p class="text-muted small mb-0">RUC: <?= htmlspecialchars($hotel['hotel_ruc'] ?? '20601234567') ?> • <?= htmlspecialchars($hotel['hotel_direccion'] ?? 'Av. Principal 123, Miraflores, Lima') ?></p>
                                <p class="text-muted small mb-0"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($hotel['hotel_telefono'] ?? '+51 987 654 321') ?> • <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($hotel['hotel_correo'] ?? 'reservas@hotelmanager.com') ?></p>
                            </div>
                            <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                                <span class="badge bg-primary text-white px-3 py-1.5 fw-bold fs-6 mb-1 d-inline-block">
                                    RESERVA #<?= htmlspecialchars($reserva['codigo_reserva']) ?>
                                </span>
                                <div class="text-muted small">Fecha emisión: <?= date('d/m/Y h:i A') ?></div>
                                <span class="badge bg-success-subtle text-success border px-2 py-1 mt-1 text-uppercase">
                                    <?= htmlspecialchars($reserva['estado']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- 1. Datos del Huésped Titular -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 text-secondary section-label">
                                1. Datos del Huésped Titular
                            </h6>
                            <div class="row g-2 small">
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Huésped Titular:</span>
                                    <strong class="text-dark fs-6"><?= htmlspecialchars($reserva['huesped_nombre']) ?></strong>
                                </div>
                                <div class="col-sm-6 text-sm-end">
                                    <span class="text-muted d-block">Documento de Identidad:</span>
                                    <strong><?= htmlspecialchars($reserva['tipo_documento'] ?? 'DNI') ?>: <?= htmlspecialchars($reserva['numero_documento'] ?? '') ?></strong>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Teléfono / WhatsApp:</span>
                                    <strong><?= htmlspecialchars($reserva['huesped_telefono'] ?: 'No registrado') ?></strong>
                                </div>
                                <div class="col-sm-6 text-sm-end">
                                    <span class="text-muted d-block">Correo Electrónico:</span>
                                    <strong><?= htmlspecialchars($reserva['huesped_correo'] ?: 'No registrado') ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Detalles de la Estadía -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 text-secondary section-label">
                                2. Detalles de Estadía y Horarios
                            </h6>
                            <div class="row g-3 text-center">
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block mb-1"><i class="bi bi-box-arrow-in-right text-success me-1"></i>Check-in</small>
                                        <strong class="fs-6 d-block"><?= date('d/m/Y', strtotime($reserva['fecha_entrada'])) ?></strong>
                                        <small class="text-xs text-muted">Desde las 14:00 hrs</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block mb-1"><i class="bi bi-box-arrow-right text-danger me-1"></i>Check-out</small>
                                        <strong class="fs-6 d-block"><?= date('d/m/Y', strtotime($reserva['fecha_salida'])) ?></strong>
                                        <small class="text-xs text-muted">Hasta las 12:00 hrs</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block mb-1"><i class="bi bi-moon-stars text-primary me-1"></i>Duración</small>
                                        <strong class="fs-6 d-block"><?= $reserva['noches'] ?> <?= $reserva['noches'] == 1 ? 'noche' : 'noches' ?></strong>
                                        <small class="text-xs text-muted"><?= $reserva['adultos'] ?> ad. / <?= $reserva['ninos'] ?> niños</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Habitaciones Reservadas -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 text-secondary section-label">
                                3. Habitaciones Asignadas
                            </h6>
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Habitación</th>
                                        <th>Categoría</th>
                                        <th class="text-center">Capacidad</th>
                                        <th class="text-end">Tarifa/Noche</th>
                                        <th class="text-end">Noches</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $subtotalHab = 0;
                                        foreach ($reserva['habitaciones'] as $hab): 
                                            $subtotalHab += (float)$hab['subtotal'];
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-primary">Hab. <?= htmlspecialchars($hab['numero']) ?> (Piso <?= htmlspecialchars($hab['piso']) ?>)</td>
                                            <td><?= htmlspecialchars($hab['tipo_nombre']) ?></td>
                                            <td class="text-center small"><i class="bi bi-person me-1"></i><?= $hab['capacidad'] ?> pers.</td>
                                            <td class="text-end"><?= dinero($hab['precio_noche']) ?></td>
                                            <td class="text-end"><?= $hab['noches'] ?></td>
                                            <td class="text-end fw-semibold"><?= dinero($hab['subtotal']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- 4. Servicios Adicionales (Si fueron contratados) -->
                        <?php if (!empty($reserva['servicios'])): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 text-secondary section-label">
                                    4. Servicios Adicionales Contratados
                                </h6>
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Servicio / Paquete</th>
                                            <th>Categoría</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Precio Unit.</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $subtotalSrv = 0;
                                            foreach ($reserva['servicios'] as $srv): 
                                                $subtotalSrv += (float)$srv['subtotal'];
                                        ?>
                                            <tr>
                                                <td class="fw-semibold"><i class="bi bi-check-circle-fill text-success me-1"></i><?= htmlspecialchars($srv['servicio_nombre']) ?></td>
                                                <td><?= htmlspecialchars($srv['categoria']) ?></td>
                                                <td class="text-center"><?= $srv['cantidad'] ?></td>
                                                <td class="text-end"><?= dinero($srv['precio_unitario']) ?></td>
                                                <td class="text-end fw-semibold"><?= dinero($srv['subtotal']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <!-- 5. Liquidación Económica y Saldos -->
                        <div class="row justify-content-end mb-4">
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted">Subtotal Habitaciones:</td>
                                                <td class="text-end fw-semibold"><?= dinero($subtotalHab ?? $reserva['total']) ?></td>
                                            </tr>
                                            <?php if (!empty($subtotalSrv)): ?>
                                                <tr>
                                                    <td class="text-muted">Servicios Adicionales:</td>
                                                    <td class="text-end fw-semibold">+<?= dinero($subtotalSrv) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php if ((float)$reserva['descuento'] > 0): ?>
                                                <tr class="text-danger">
                                                    <td>Descuento <?= !empty($reserva['motivo_descuento']) ? '(' . htmlspecialchars($reserva['motivo_descuento']) . ')' : '' ?>:</td>
                                                    <td class="text-end fw-bold">-<?= dinero($reserva['descuento']) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr class="border-top border-dark-subtle">
                                                <td class="fw-bold fs-6">TOTAL RESERVA:</td>
                                                <td class="text-end fw-bold fs-5 text-primary"><?= dinero($reserva['total']) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Anticipo Abonado:</td>
                                                <td class="text-end fw-semibold text-success">-<?= dinero($reserva['anticipo']) ?></td>
                                            </tr>
                                            <tr class="border-top">
                                                <td class="fw-bold fs-6 text-danger">SALDO PENDIENTE:</td>
                                                <td class="text-end fw-bold fs-5 text-danger"><?= dinero($reserva['saldo']) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Notas y Políticas del Hotel -->
                        <div class="p-3 bg-light rounded-3 border mb-4 small text-muted">
                            <strong class="text-dark d-block mb-1"><i class="bi bi-info-circle me-1"></i>Términos y Políticas de Estadía:</strong>
                            <ul class="mb-0 ps-3">
                                <li>Horario de Check-in: a partir de las <strong>14:00 hrs</strong>. Check-out: hasta las <strong>12:00 hrs</strong>.</li>
                                <li>Para el ingreso es indispensable que todos los huéspedes adultos presenten su documento físico original (DNI o Pasaporte).</li>
                                <li>El saldo pendiente de S/. <?= number_format($reserva['saldo'], 2) ?> se cancelará en la recepción al momento del Check-in.</li>
                                <?php if (!empty($reserva['notas'])): ?>
                                    <li class="text-dark"><strong>Requerimientos del huésped:</strong> <?= htmlspecialchars($reserva['notas']) ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- Firmas -->
                        <div class="row pt-4 text-center">
                            <div class="col-6">
                                <div class="border-top border-dark pt-2 mx-3">
                                    <strong class="d-block small text-dark"><?= htmlspecialchars($reserva['huesped_nombre']) ?></strong>
                                    <span class="text-xs text-muted">Firma del Huésped Titular</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border-top border-dark pt-2 mx-3">
                                    <strong class="d-block small text-dark"><?= htmlspecialchars($hotel['hotel_nombre'] ?? 'Hotel Manager') ?></strong>
                                    <span class="text-xs text-muted">Recepción y Reservas</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
