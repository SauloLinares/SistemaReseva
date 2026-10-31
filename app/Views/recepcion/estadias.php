<?php partial('page_header', [
    'titulo'    => 'Control de Estadías',
    'icono'     => 'bi-journal-bookmark',
    'subtitulo' => 'Supervisión en vivo de huéspedes alojados en el hotel',
    'acciones'  => [
        ['texto' => 'Check-out', 'url' => 'recepcion/checkout', 'icono' => 'bi-box-arrow-right', 'variante' => 'outline-secondary'],
        ['texto' => 'Cambiar Habitación', 'url' => 'recepcion/asignacion', 'icono' => 'bi-arrow-left-right', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Estadías Activas -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-door-open me-2"></i>Huéspedes alojados
                </h5>
                <span class="badge badge-soft-success"><?= count($activas) ?> en casa</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Habitación</th>
                                <th>Huésped</th>
                                <th>Check-in</th>
                                <th>Salida prevista</th>
                                <th class="text-end">Saldo</th>
                                <th>Observaciones</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activas)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">No hay huéspedes alojados en este momento.</td></tr>
                            <?php else: ?>
                                <?php foreach ($activas as $act): ?>
                                    <tr>
                                        <td class="text-nowrap">
                                            <div class="fw-semibold">Hab. <?= e($act['habitacion_numero']) ?></div>
                                            <small class="text-body-secondary"><?= e($act['tipo_nombre']) ?> · Piso <?= e($act['piso']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-nowrap"><?= e($act['huesped_nombre']) ?></div>
                                            <small class="text-body-secondary text-nowrap"><?= e($act['numero_documento']) ?> · <?= e($act['nacionalidad'] ?: 'Perú') ?></small>
                                        </td>
                                        <td class="text-nowrap"><?= fecha_es($act['fecha_checkin'], true) ?></td>
                                        <td class="text-nowrap"><?= fecha_es($act['fecha_salida']) ?></td>
                                        <td class="text-end text-nowrap">
                                            <?php if ((float)$act['reserva_saldo'] > 0): ?>
                                                <span class="fw-semibold text-danger"><?= dinero($act['reserva_saldo']) ?></span>
                                            <?php else: ?>
                                                <span class="text-success"><i class="bi bi-check2-circle me-1"></i>Al día</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $obs = $act['observaciones'] ?: '-'; ?>
                                            <span class="text-body-secondary text-clamp-2" title="<?= e($obs) ?>"><?= e($obs) ?></span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="table-actions">
                                                <button type="button" class="btn btn-icon btn-outline-primary btn-abrir-consumo"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalCargarConsumo"
                                                        data-reserva-id="<?= $act['reserva_id'] ?>"
                                                        data-habitacion="<?= htmlspecialchars($act['habitacion_numero']) ?>"
                                                        data-huesped="<?= htmlspecialchars($act['huesped_nombre']) ?>"
                                                        title="Cargar consumo / room service">
                                                    <i class="bi bi-cart-plus"></i>
                                                </button>
                                                <a href="<?= url('reservas/editar/' . $act['reserva_id']) ?>" class="btn btn-icon btn-outline-secondary" title="Ver cuenta">
                                                    <i class="bi bi-card-text"></i>
                                                </a>
                                                <a href="<?= url('recepcion/checkout?id=' . $act['id']) ?>" class="btn btn-sm btn-outline-danger text-nowrap" title="Realizar check-out">
                                                    <i class="bi bi-box-arrow-right me-1"></i>Check-out
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Historial de Estadías Pasadas -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Historial de estadías</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Habitación</th>
                                <th>Huésped</th>
                                <th>Ingreso</th>
                                <th>Salida efectiva</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $h): ?>
                                <tr>
                                    <td class="text-body-secondary"><?= (int) $h['id'] ?></td>
                                    <td class="text-nowrap"><span class="fw-semibold">Hab. <?= e($h['habitacion_numero']) ?></span> <small class="text-body-secondary"><?= e($h['tipo_nombre']) ?></small></td>
                                    <td class="text-nowrap"><?= e($h['huesped_nombre']) ?></td>
                                    <td class="text-nowrap"><?= fecha_es($h['fecha_checkin'], true) ?></td>
                                    <td class="text-nowrap"><?= !empty($h['fecha_checkout']) ? fecha_es($h['fecha_checkout'], true) : '—' ?></td>
                                    <td>
                                        <span class="badge <?= ($h['estado'] === 'activa') ? 'badge-soft-success' : 'badge-soft-secondary' ?>">
                                            <?= e(ucfirst($h['estado'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php $obs = $h['observaciones'] ?: '-'; ?>
                                        <span class="text-body-secondary text-clamp-2" title="<?= e($obs) ?>"><?= e($obs) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL: CARGAR CONSUMO RÁPIDO A LA HABITACIÓN -->
<div class="modal fade" id="modalCargarConsumo" tabindex="-1" aria-labelledby="modalCargarConsumoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="modalCargarConsumoLabel">
                    <i class="bi bi-cart-plus-fill me-2"></i>Cargar Consumo a Habitación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formModalConsumo">
                <input type="hidden" name="reserva_id" id="modalConsumoReservaId" value="">
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none py-2 px-3 small" id="alertModalConsumo" role="alert"></div>

                    <!-- Ficha Resumen -->
                    <div class="p-3 bg-body-secondary rounded border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Habitación:</span>
                            <span class="fw-bold small fs-6 text-primary" id="modalConsumoHabitacion">--</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Huésped Alojado:</span>
                            <span class="fw-bold small" id="modalConsumoHuesped">--</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Seleccionar Producto o Servicio <span class="text-danger">*</span></label>
                        <select name="servicio_id" id="modalSelectServicio" class="form-select" required>
                            <option value="">-- Elige un producto o servicio --</option>
                            <?php if (!empty($servicios)): ?>
                                <?php foreach ($servicios as $s): ?>
                                    <option value="<?= $s['id'] ?>" data-precio="<?= $s['precio'] ?>">
                                        <?= htmlspecialchars($s['nombre']) ?> (<?= htmlspecialchars($s['categoria']) ?>) - <?= dinero($s['precio']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad" id="modalConsumoCantidad" class="form-control" value="1" min="1" max="100" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Precio Unitario (S/.) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="precio_unitario" id="modalConsumoPrecio" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="p-2 mb-3 bg-light-subtle rounded border d-flex justify-content-between align-items-center">
                        <span class="fw-semibold small">Total a Cargar:</span>
                        <span class="fs-5 fw-bold text-success" id="modalConsumoTotalText">S/. 0.00</span>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="pagado" value="1" id="modalConsumoPagado">
                        <label class="form-check-label small" for="modalConsumoPagado">
                            Pagado al instante en recepción / efectivo (no acumular a saldo)
                        </label>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Observaciones / Notas</label>
                        <input type="text" name="notas" class="form-control" placeholder="Ej: Solicitado por teléfono, entregado por mozo">
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarConsumoModal">
                        <i class="bi bi-cart-check me-1"></i> Cargar a la Cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('modalCargarConsumo');
    const selectServicio = document.getElementById('modalSelectServicio');
    const inputCantidad = document.getElementById('modalConsumoCantidad');
    const inputPrecio = document.getElementById('modalConsumoPrecio');
    const totalText = document.getElementById('modalConsumoTotalText');
    const formModal = document.getElementById('formModalConsumo');
    const btnGuardar = document.getElementById('btnGuardarConsumoModal');
    const alertModal = document.getElementById('alertModalConsumo');

    function actualizarTotal() {
        const cant = parseFloat(inputCantidad.value) || 0;
        const precio = parseFloat(inputPrecio.value) || 0;
        const total = (cant * precio).toFixed(2);
        totalText.textContent = 'S/. ' + total;
    }

    if (selectServicio) {
        selectServicio.addEventListener('change', function() {
            const opt = selectServicio.options[selectServicio.selectedIndex];
            if (opt && opt.dataset.precio) {
                inputPrecio.value = parseFloat(opt.dataset.precio).toFixed(2);
            } else {
                inputPrecio.value = '';
            }
            actualizarTotal();
        });
    }

    if (inputCantidad) inputCantidad.addEventListener('input', actualizarTotal);
    if (inputPrecio) inputPrecio.addEventListener('input', actualizarTotal);

    // Al abrir el modal desde un botón de fila
    document.querySelectorAll('.btn-abrir-consumo').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalConsumoReservaId').value = this.dataset.reservaId || '';
            document.getElementById('modalConsumoHabitacion').textContent = 'Hab. ' + (this.dataset.habitacion || '');
            document.getElementById('modalConsumoHuesped').textContent = this.dataset.huesped || '';
            alertModal.classList.add('d-none');
            alertModal.textContent = '';
            formModal.reset();
            inputCantidad.value = 1;
            totalText.textContent = 'S/. 0.00';
        });
    });

    if (formModal && btnGuardar) {
        formModal.addEventListener('submit', function(e) {
            e.preventDefault();
            alertModal.classList.add('d-none');
            alertModal.textContent = '';

            const formData = new FormData(formModal);
            const originalBtnHtml = btnGuardar.innerHTML;

            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cargando...';

            fetch('<?= url("servicios/apiGuardarConsumo") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = originalBtnHtml;

                if (!data.success) {
                    alertModal.textContent = data.mensaje || 'Error al cargar el consumo.';
                    alertModal.classList.remove('d-none');
                    return;
                }

                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.mensaje || '¡Consumo cargado exitosamente!',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    window.location.reload();
                }
            })
            .catch(err => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = originalBtnHtml;
                alertModal.textContent = 'Ocurrió un error inesperado al conectar con el servidor.';
                alertModal.classList.remove('d-none');
            });
        });
    }
});
</script>
