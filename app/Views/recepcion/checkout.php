<?php partial('page_header', [
    'titulo'    => 'Recepción: Check-out',
    'icono'     => 'bi-box-arrow-right',
    'subtitulo' => 'Liquidación de cuenta, cobro de saldo y liberación de habitación',
    'acciones'  => [
        ['texto' => 'Control de Estadías', 'url' => 'recepcion/estadias', 'icono' => 'bi-journal-bookmark', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-4">
            <!-- Formulario de Check-out -->
            <div class="col-12 col-lg-6">
                <div class="card">
                    <?php partial('card_header', ['titulo' => 'Registrar salida de huésped', 'icono' => 'bi-door-closed']) ?>
                    <div class="card-body">
                        <form method="POST" action="<?= url('recepcion/procesarCheckout') ?>">
                            <div class="mb-3">
                                <label for="estancia_id" class="form-label">Huésped alojado <span class="text-danger">*</span></label>
                                <select id="estancia_id" name="estancia_id" class="form-select select-search" data-atajo="F7" data-atajo-desc="Elegir el huésped alojado" required placeholder="Escribe el número de habitación, nombre de huésped o código...">
                                    <option value="">-- Seleccionar huésped alojado --</option>
                                    <?php foreach ($activas as $act): ?>
                                        <option value="<?= $act['id'] ?>" <?= ($estanciaSeleccionada && $estanciaSeleccionada['id'] == $act['id']) ? 'selected' : '' ?>>
                                            Hab. <?= htmlspecialchars($act['habitacion_numero']) ?> - <?= htmlspecialchars($act['huesped_nombre']) ?> (In: <?= fecha_es($act['fecha_checkin']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if ($estanciaSeleccionada): ?>
                                <?php if ((float)$estanciaSeleccionada['reserva_saldo'] > 0): ?>
                                    <!-- Hay deuda: es lo único que debe llamar la atención en esta pantalla -->
                                    <div class="alert alert-danger mb-3">
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                            <strong><i class="bi bi-exclamation-triangle me-2"></i>Saldo pendiente</strong>
                                            <span class="fs-5 fw-bold text-nowrap"><?= dinero($estanciaSeleccionada['reserva_saldo']) ?></span>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalCobroRapido" data-atajo="F8" data-atajo-desc="Cobrar el saldo">
                                                <i class="bi bi-cash-coin me-1"></i>Cobrar ahora
                                            </button>
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" name="confirmar_saldo" value="1" id="checkSaldo">
                                                <label class="form-check-label small" for="checkSaldo">
                                                    Permitir salida con saldo pendiente
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-success small mb-3"><i class="bi bi-check2-circle me-1"></i>Cuenta al día: no hay saldo pendiente.</p>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones / estado de la habitación</label>
                                <textarea id="observaciones" name="observaciones" class="form-control" rows="3" placeholder="Ej: Se recibieron todas las llaves, toallas completas, sin desperfectos..."></textarea>
                            </div>

                            <div class="alert alert-info py-2 small d-flex align-items-center gap-2 mb-0">
                                <i class="bi bi-info-circle fs-5 flex-shrink-0"></i>
                                <div>Al confirmar, la habitación pasa a <strong>Limpieza</strong> y se crea la orden para housekeeping.</div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary" data-atajo="F9" data-atajo-desc="Confirmar el check-out">
                                    <i class="bi bi-box-arrow-right me-2"></i>Confirmar Check-out
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Ficha Resumen -->
            <div class="col-12 col-lg-6">
                <?php if ($estanciaSeleccionada): ?>
                    <div class="card">
                        <?php partial('card_header', [
                            'titulo' => 'Hab. ' . $estanciaSeleccionada['habitacion_numero'] . ' · Piso ' . $estanciaSeleccionada['piso'],
                            'icono'  => 'bi-door-open',
                        ]) ?>
                        <div class="card-body">
                            <h5 class="fw-bold mb-1"><?= e($estanciaSeleccionada['huesped_nombre']) ?></h5>
                            <div class="text-body-secondary small mb-3">
                                <?= e($estanciaSeleccionada['numero_documento']) ?> · Tel. <?= e($estanciaSeleccionada['telefono'] ?: 'N/A') ?>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-body-secondary d-block">Ingreso registrado</small>
                                        <strong><i class="bi bi-box-arrow-in-right text-success me-1"></i><?= fecha_es($estanciaSeleccionada['fecha_checkin'], true) ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-body-secondary d-block">Código de reserva</small>
                                        <strong class="font-monospace"><?= e($estanciaSeleccionada['codigo_reserva']) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-body-secondary rounded border mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total de la reserva</span>
                                    <span class="fw-semibold"><?= dinero($estanciaSeleccionada['reserva_total']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Pagado</span>
                                    <span class="fw-semibold"><?= dinero($estanciaSeleccionada['anticipo']) ?></span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Saldo</span>
                                    <span class="fw-bold fs-5 <?= ((float)$estanciaSeleccionada['reserva_saldo'] > 0) ? 'text-danger' : 'text-success' ?>">
                                        <?= dinero($estanciaSeleccionada['reserva_saldo']) ?>
                                    </span>
                                </div>
                            </div>

                            <a href="<?= url('reservas/editar/' . $estanciaSeleccionada['reserva_id']) ?>" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-card-text me-1"></i>Ver cuenta completa
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-box-arrow-right"></i>
                        <p class="fw-semibold mb-1">Ningún huésped seleccionado</p>
                        <p class="mb-0">Elige un huésped alojado para ver su saldo y su estadía.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php if ($estanciaSeleccionada): ?>
<!-- MODAL: COBRO RÁPIDO EN CAJA (CHECK-OUT) -->
<div class="modal fade" id="modalCobroRapido" tabindex="-1" aria-labelledby="modalCobroRapidoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCobroRapidoLabel">
                    <i class="bi bi-cash-stack me-2 text-success"></i>Registrar Pago / Liquidar Cuenta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formModalCobro">
                <input type="hidden" name="reserva_id" value="<?= $estanciaSeleccionada['reserva_id'] ?>">
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none py-2 px-3 small" id="alertModalCobro" role="alert"></div>

                    <!-- Ficha Resumen del Huésped -->
                    <div class="p-3 bg-body-secondary rounded border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Huésped Titular:</span>
                            <span class="fw-bold small"><?= htmlspecialchars($estanciaSeleccionada['huesped_nombre']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Habitación:</span>
                            <span class="fw-bold small">Hab. <?= htmlspecialchars($estanciaSeleccionada['habitacion_numero']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Código Reserva:</span>
                            <span class="fw-bold small text-primary"><?= htmlspecialchars($estanciaSeleccionada['codigo_reserva']) ?></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?php partial('cobro_lineas', [
                            'monto'       => $estanciaSeleccionada['reserva_saldo'],
                            'idMonto'     => 'inputMontoCobro',
                            'cajaAbierta' => $cajaAbierta,
                        ]) ?>
                        <div class="form-text">Saldo pendiente actual: <strong><?= dinero($estanciaSeleccionada['reserva_saldo']) ?></strong></div>
                    </div>

                    <div class="mb-3">
                        <label for="comprobante_tipo" class="form-label">Comprobante <span class="text-danger">*</span></label>
                        <select id="comprobante_tipo" name="comprobante_tipo" class="form-select" required>
                            <option value="Boleta" selected>Boleta de Venta</option>
                            <option value="Factura">Factura</option>
                            <option value="Recibo de Caja">Recibo de Caja</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="notas" class="form-label">Notas / Observación</label>
                        <input id="notas" type="text" name="notas" class="form-control" placeholder="Ej: Liquidación total en Check-out">
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardarCobroModal" data-atajo="F9" data-atajo-desc="Cobrar">
                        <i class="bi bi-check-circle-fill me-1"></i> Cobrar e Imprimir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= script_js('vistas/recepcion-checkout.js') ?>
