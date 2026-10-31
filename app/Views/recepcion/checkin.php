<?php partial('page_header', [
    'titulo'    => 'Recepción: Check-in',
    'icono'     => 'bi-box-arrow-in-right',
    'subtitulo' => 'Registro de ingreso de huéspedes y entrega de habitaciones',
    'acciones'  => [
        ['texto' => 'Control de Estadías', 'url' => 'recepcion/estadias', 'icono' => 'bi-journal-bookmark', 'variante' => 'outline-secondary'],
        ['texto' => 'Nueva Reserva', 'url' => 'reservas/crear', 'icono' => 'bi-calendar-plus', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-4">
            <!-- Formulario de Check-in -->
            <div class="col-12 col-lg-6">
                <div class="card">
                    <?php partial('card_header', ['titulo' => 'Registrar ingreso de huésped', 'icono' => 'bi-key']) ?>
                    <div class="card-body">
                        <form method="POST" action="<?= url('recepcion/procesarCheckin') ?>">
                            <div class="mb-3">
                                <label for="selectReserva" class="form-label">Reserva programada <span class="text-danger">*</span></label>
                                <select name="reserva_id" id="selectReserva" class="form-select select-search" data-atajo="F7" data-atajo-desc="Elegir la reserva" required placeholder="Escribe el código, nombre del huésped o habitación...">
                                    <option value="">-- Elige una reserva lista para ingreso --</option>
                                    <?php foreach ($reservas as $r): ?>
                                        <option value="<?= $r['id'] ?>" <?= ($reservaSeleccionada && $reservaSeleccionada['id'] == $r['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($r['codigo_reserva']) ?> - <?= htmlspecialchars($r['huesped_nombre']) ?> (Hab: <?= htmlspecialchars($r['habitaciones_asignadas'] ?: 'Sin asignar') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Muestra reservas con estado "Confirmada" o "Pendiente".</div>
                            </div>

                            <div class="mb-3">
                                <label for="obsCheckin" class="form-label">Observaciones / entrega de llaves</label>
                                <textarea name="observaciones" id="obsCheckin" class="form-control" rows="3" placeholder="Ej: Se entregaron 2 tarjetas magnéticas, huésped presentó pasaporte original..."></textarea>
                            </div>

                            <div class="alert alert-info py-2 small d-flex align-items-center gap-2 mb-0">
                                <i class="bi bi-info-circle fs-5 flex-shrink-0"></i>
                                <div>Al confirmar, la habitación pasa a <strong>Ocupada</strong> y comienza la estadía.</div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-success" data-atajo="F9" data-atajo-desc="Confirmar el check-in">
                                    <i class="bi bi-check2-circle me-2"></i>Confirmar Check-in
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Ficha de la Reserva Seleccionada -->
            <div class="col-12 col-lg-6">
                <?php if ($reservaSeleccionada): ?>
                    <div class="card">
                        <?php partial('card_header', ['titulo' => 'Reserva ' . $reservaSeleccionada['codigo_reserva'], 'icono' => 'bi-card-text']) ?>
                        <div class="card-body">
                            <h5 class="fw-bold mb-1"><?= e($reservaSeleccionada['huesped_nombre']) ?></h5>
                            <div class="text-body-secondary small mb-3">
                                <?= e($reservaSeleccionada['tipo_documento']) ?> <?= e($reservaSeleccionada['numero_documento']) ?>
                                · Tel. <?= e($reservaSeleccionada['huesped_telefono'] ?: 'N/A') ?>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-muted d-block">Entrada Programada:</small>
                                        <strong><i class="bi bi-box-arrow-in-right text-success me-1"></i><?= fecha_es($reservaSeleccionada['fecha_entrada']) ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-muted d-block">Salida Programada:</small>
                                        <strong><i class="bi bi-box-arrow-right text-danger me-1"></i><?= fecha_es($reservaSeleccionada['fecha_salida']) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-2">Habitaciones Asignadas:</h6>
                            <ul class="list-group mb-3">
                                <?php foreach ($reservaSeleccionada['habitaciones'] as $hab): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <span class="fw-semibold">Hab. <?= e($hab['numero']) ?></span>
                                            <small class="text-body-secondary d-block"><?= e($hab['tipo_nombre']) ?> · Piso <?= e($hab['piso']) ?></small>
                                        </div>
                                        <span class="text-nowrap"><strong><?= dinero($hab['precio_noche']) ?></strong> <span class="text-body-secondary">/ noche</span></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="d-flex justify-content-between border-top pt-2">
                                <span>Total: <strong><?= dinero($reservaSeleccionada['total']) ?></strong></span>
                                <span>Abonado: <strong class="text-success"><?= dinero($reservaSeleccionada['anticipo']) ?></strong></span>
                                <span>Saldo: <strong class="<?= ((float)$reservaSeleccionada['saldo'] > 0) ? 'text-danger' : 'text-success' ?>"><?= dinero($reservaSeleccionada['saldo']) ?></strong></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-person-check"></i>
                        <p class="fw-semibold mb-1">Ninguna reserva seleccionada</p>
                        <p class="mb-0">Elige una reserva para ver sus habitaciones, fechas y saldo.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?= script_js('vistas/recepcion-checkin.js') ?>
