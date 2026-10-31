<?php
$accionesReserva = [
    ['texto' => 'Volver a Reservas', 'url' => 'reservas', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
];
if ($reserva['estado'] === 'confirmada') {
    $accionesReserva[] = ['texto' => 'Realizar Check-in', 'url' => 'recepcion/checkin?reserva_id=' . $reserva['id'], 'icono' => 'bi-box-arrow-in-right', 'variante' => 'success'];
}
partial('page_header', [
    'titulo'    => 'Reserva ' . $reserva['codigo_reserva'],
    'icono'     => 'bi-pencil-square',
    'subtitulo' => 'Modificación de fechas, gestión de pagos y detalles de la reserva',
    'acciones'  => $accionesReserva,
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-4">
            <!-- Formulario de Modificación -->
            <div class="col-12 col-lg-8">
                <form action="<?= url('reservas/actualizar') ?>" method="POST" class="card mb-4">
                    <input type="hidden" name="id" value="<?= $reserva['id'] ?>">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-card-checklist me-2"></i>Información Principal de la Reserva
                        </h5>
                        <?= badge_estado_reserva($reserva['estado']) ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Huésped Titular <span class="text-danger">*</span></label>
                                <select name="huesped_id" class="form-select select-search select-huesped" required>
                                    <?php foreach ($huespedes as $h): ?>
                                        <option value="<?= $h['id'] ?>" <?= ($h['id'] == $reserva['huesped_id']) ? 'selected' : '' ?>
                                            data-nombres="<?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?>"
                                            data-doc="<?= htmlspecialchars($h['numero_documento']) ?>"
                                            data-tipo-doc="<?= htmlspecialchars($h['tipo_documento'] ?? 'DNI') ?>"
                                            data-telefono="<?= htmlspecialchars($h['telefono'] ?? '') ?>"
                                            data-correo="<?= htmlspecialchars($h['correo'] ?? '') ?>"
                                            data-nacionalidad="<?= htmlspecialchars($h['nacionalidad'] ?? 'Perú') ?>"
                                            data-total-reservas="<?= (int)($h['total_reservas'] ?? 0) ?>"
                                            data-data='<?= json_encode([
                                                "value" => (string)$h['id'],
                                                "nombres" => $h['nombres'] . ' ' . $h['apellidos'],
                                                "doc" => $h['numero_documento'],
                                                "tipoDoc" => $h['tipo_documento'] ?? 'DNI',
                                                "telefono" => $h['telefono'] ?? '',
                                                "correo" => $h['correo'] ?? '',
                                                "nacionalidad" => $h['nacionalidad'] ?? 'Perú',
                                                "totalReservas" => (int)($h['total_reservas'] ?? 0)
                                            ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                            <?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?> (Doc: <?= htmlspecialchars($h['numero_documento']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Estado de Reserva</label>
                                <select name="estado" class="form-select">
                                    <option value="confirmada" <?= ($reserva['estado'] === 'confirmada') ? 'selected' : '' ?>>Confirmada</option>
                                    <option value="pendiente" <?= ($reserva['estado'] === 'pendiente') ? 'selected' : '' ?>>Pendiente de Pago</option>
                                    <option value="en_estadia" <?= ($reserva['estado'] === 'en_estadia') ? 'selected' : '' ?>>En Estadía (Check-in)</option>
                                    <option value="finalizada" <?= ($reserva['estado'] === 'finalizada') ? 'selected' : '' ?>>Finalizada (Check-out)</option>
                                    <option value="cancelada" <?= ($reserva['estado'] === 'cancelada') ? 'selected' : '' ?>>Cancelada</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha de Entrada</label>
                                <input type="date" name="fecha_entrada" class="form-control" value="<?= htmlspecialchars($reserva['fecha_entrada']) ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha de Salida</label>
                                <input type="date" name="fecha_salida" class="form-control" value="<?= htmlspecialchars($reserva['fecha_salida']) ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Noches</label>
                                <input type="text" class="form-control bg-light" value="<?= $reserva['noches'] ?> noches" readonly>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label">Adultos</label>
                                <input type="number" name="adultos" class="form-control" value="<?= $reserva['adultos'] ?>" min="1">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Niños</label>
                                <input type="number" name="ninos" class="form-control" value="<?= $reserva['ninos'] ?>" min="0">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Método de Pago Preferido</label>
                                <select name="metodo_pago" class="form-select">
                                    <option value="Efectivo" <?= ($reserva['metodo_pago'] === 'Efectivo') ? 'selected' : '' ?>>Efectivo</option>
                                    <option value="Tarjeta de Crédito" <?= ($reserva['metodo_pago'] === 'Tarjeta de Crédito') ? 'selected' : '' ?>>Tarjeta de Crédito</option>
                                    <option value="Tarjeta de Débito" <?= ($reserva['metodo_pago'] === 'Tarjeta de Débito') ? 'selected' : '' ?>>Tarjeta de Débito</option>
                                    <option value="Transferencia Bancaria" <?= ($reserva['metodo_pago'] === 'Transferencia Bancaria') ? 'selected' : '' ?>>Transferencia Bancaria</option>
                                    <option value="Yape/Plin" <?= ($reserva['metodo_pago'] === 'Yape/Plin') ? 'selected' : '' ?>>Yape / Plin</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notas y Observaciones</label>
                                <textarea name="notas" class="form-control" rows="2"><?= htmlspecialchars($reserva['notas'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Habitaciones Asignadas -->
                        <h6 class="fw-bold mt-4 mb-2"><i class="bi bi-door-open text-warning me-1"></i>Habitaciones Asignadas a esta Reserva:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Habitación</th>
                                        <th>Tipo</th>
                                        <th>Piso</th>
                                        <th>Precio / Noche</th>
                                        <th>Noches</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reserva['habitaciones'] as $rh): ?>
                                        <tr>
                                            <td class="fw-bold text-primary">Hab. <?= htmlspecialchars($rh['numero']) ?></td>
                                            <td><?= htmlspecialchars($rh['tipo_nombre']) ?></td>
                                            <td>Piso <?= htmlspecialchars($rh['piso']) ?></td>
                                            <td><?= dinero($rh['precio_noche']) ?></td>
                                            <td><?= $rh['noches'] ?></td>
                                            <td class="fw-bold"><?= dinero($rh['subtotal']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>

                <!-- Consumo de Servicios -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cart-check text-info me-2"></i>Consumos de Servicios Adicionales
                        </h5>
                        <a href="<?= url('servicios/consumo?reserva_id=' . $reserva['id']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle me-1"></i> Cargar Servicio
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Categoría</th>
                                        <th>Cant.</th>
                                        <th>P. Unitario</th>
                                        <th>Subtotal</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reserva['servicios'])): ?>
                                        <tr><td colspan="7" class="text-center py-3 text-muted">No se registran consumos adicionales en esta reserva.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($reserva['servicios'] as $serv): ?>
                                            <tr>
                                                <td class="fw-semibold"><?= htmlspecialchars($serv['servicio_nombre']) ?></td>
                                                <td><span class="badge text-bg-light border"><?= htmlspecialchars($serv['categoria']) ?></span></td>
                                                <td><?= $serv['cantidad'] ?></td>
                                                <td><?= dinero($serv['precio_unitario']) ?></td>
                                                <td class="fw-bold"><?= dinero($serv['subtotal']) ?></td>
                                                <td><small><?= fecha_es($serv['fecha_consumo'], true) ?></small></td>
                                                <td>
                                                    <?= $serv['pagado'] ? '<span class="badge text-bg-success">Pagado</span>' : '<span class="badge text-bg-warning text-dark">A cuenta</span>' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Estado Financiero y Pagos -->
            <div class="col-12 col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cash-coin me-2"></i>Balance Financiero
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2 fs-6">
                            <span>Monto Total:</span>
                            <span class="fw-bold"><?= dinero($reserva['total']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success fs-6">
                            <span>Abonado / Pagado:</span>
                            <span class="fw-bold"><?= dinero($reserva['anticipo']) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <span class="fs-5 fw-bold">Saldo Pendiente:</span>
                            <span class="fs-4 fw-bold <?= ((float)$reserva['saldo'] > 0) ? 'text-danger' : 'text-success' ?>">
                                <?= dinero($reserva['saldo']) ?>
                            </span>
                        </div>

                        <?php if ((float)$reserva['saldo'] > 0): ?>
                            <a href="<?= url('pagos/crear?reserva_id=' . $reserva['id']) ?>" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-credit-card me-1"></i> Cobrar Saldo Pendiente
                            </a>
                        <?php else: ?>
                            <div class="alert alert-success py-2 text-center small mb-2">
                                <i class="bi bi-check-circle-fill me-1"></i> La cuenta se encuentra totalmente pagada.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Historial de Pagos de esta Reserva -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-receipt me-1"></i>Comprobantes y Pagos
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php if (empty($reserva['pagos'])): ?>
                                <li class="list-group-item text-center py-3 text-muted small">No hay pagos registrados aún.</li>
                            <?php else: ?>
                                <?php foreach ($reserva['pagos'] as $p): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <div class="fw-bold small"><?= htmlspecialchars($p['comprobante_tipo']) ?>: <?= htmlspecialchars($p['comprobante_numero']) ?></div>
                                            <small class="text-muted"><?= fecha_es($p['fecha_pago'], true) ?> (<?= htmlspecialchars($p['metodo_pago']) ?>)</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold <?= ($p['tipo'] === 'devolucion') ? 'text-danger' : 'text-success' ?>">
                                                <?= ($p['tipo'] === 'devolucion' ? '-' : '+') . dinero($p['monto']) ?>
                                            </span>
                                            <div>
                                                <a href="<?= url('pagos/comprobante/' . $p['id']) ?>" target="_blank" class="badge text-bg-light border text-decoration-none" title="Imprimir">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
