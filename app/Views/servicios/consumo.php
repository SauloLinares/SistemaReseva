<?php partial('page_header', [
    'titulo'    => 'Registrar Consumo de Servicio',
    'icono'     => 'bi-cart-plus',
    'subtitulo' => 'Carga de consumos de bar, room service, lavandería o restaurante a la habitación',
    'acciones'  => [
        ['texto' => 'Ver Consumos', 'url' => 'servicios/porHuesped', 'icono' => 'bi-person-lines-fill', 'variante' => 'outline-secondary'],
        ['texto' => 'Catálogo', 'url' => 'servicios', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <form action="<?= url('servicios/guardarConsumo') ?>" method="POST" class="card" data-borrador>
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-cart4 me-2"></i>Cargar Consumo</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="reserva_id" class="form-label">Habitación / Huésped en Estadía <span class="text-danger">*</span></label>
                                <select id="reserva_id" name="reserva_id" class="form-select form-select-lg" required>
                                    <option value="">-- Seleccionar huésped alojado --</option>
                                    <?php foreach ($estadias as $est): ?>
                                        <option value="<?= $est['reserva_id'] ?>" <?= ($reservaIdFijada == $est['reserva_id']) ? 'selected' : '' ?>>
                                            Hab. <?= htmlspecialchars($est['habitacion_numero']) ?> (Piso <?= $est['piso'] ?>) - <?= htmlspecialchars($est['huesped_nombre']) ?> (Reserva: <?= htmlspecialchars($est['codigo_reserva']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-8">
                                <label for="servicio_id" class="form-label">Servicio Contratado / Consumido <span class="text-danger">*</span></label>
                                <select name="servicio_id" id="servicio_id" class="form-select form-select-lg" required>
                                    <option value="">-- Seleccionar del catálogo --</option>
                                    <?php foreach ($servicios as $s): ?>
                                        <option value="<?= $s['id'] ?>" data-precio="<?= $s['precio'] ?>">
                                            <?= htmlspecialchars($s['nombre']) ?> - <?= dinero($s['precio']) ?> (<?= htmlspecialchars($s['categoria']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="inputCantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" name="cantidad" id="inputCantidad" class="form-control form-control-lg text-center" value="1" min="1" max="50" required>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold">Total a Cargar:</span>
                                    <span class="fs-4 fw-bold text-success" id="displayTotalConsumo"><?= e(moneda()) ?> 0.00</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="pagado" class="form-label">Forma de Facturación</label>
                                <select id="pagado" name="pagado" class="form-select">
                                    <option value="0" selected>Cargar a la cuenta de la habitación (Pendiente de cobro al check-out)</option>
                                    <option value="1">Cobrado en el acto (Pagado inmediatamente)</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="metodo_pago" class="form-label">Método de Pago (Si se cobra de inmediato)</label>
                                <select id="metodo_pago" name="metodo_pago" class="form-select">
                                    <option value="Efectivo" selected>Efectivo</option>
                                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                    <option value="Yape/Plin">Yape / Plin</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="notas" class="form-label">Notas / Detalles Adicionales</label>
                                <input id="notas" type="text" name="notas" class="form-control" placeholder="Ej: Entregado a las 11:30 PM en habitación 201">
                            </div>
                        </div>
                    </div>
                    <?php partial('form_actions', ['cancelar' => 'servicios', 'texto' => 'Confirmar y Registrar Consumo', 'icono' => 'bi-check2-circle', 'variante' => 'success']) ?>
                </form>
            </div>
        </div>

    </div>
</div>

<?= script_js('vistas/servicios-consumo.js') ?>
