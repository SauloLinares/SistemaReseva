<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-journal-bookmark text-primary me-2"></i>Control de Estadías</h3>
                <p class="text-muted mb-0 small">Supervisión en vivo de huéspedes alojados en el hotel</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('recepcion/checkin') ?>" class="btn btn-success btn-sm shadow-sm me-1">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Check-in
                </a>
                <a href="<?= url('recepcion/checkout') ?>" class="btn btn-outline-danger btn-sm shadow-sm me-1">
                    <i class="bi bi-box-arrow-right me-1"></i> Check-out
                </a>
                <a href="<?= url('recepcion/asignacion') ?>" class="btn btn-outline-primary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left-right me-1"></i> Cambiar Habitación
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Estadías Activas -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success-subtle text-success-emphasis d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-door-open-fill me-2"></i>Huéspedes Alojados Actualmente (<?= count($activas) ?>)
                </h5>
                <span class="badge text-bg-success px-3 py-2">En Casa / En Estadía</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Habitación</th>
                                <th>Huésped</th>
                                <th>Documento</th>
                                <th>Fecha Check-in</th>
                                <th>Salida Prevista</th>
                                <th>Saldo Pendiente</th>
                                <th>Observaciones</th>
                                <th class="text-end pe-3">Acciones de Recepción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activas)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">No hay huéspedes alojados en este momento.</td></tr>
                            <?php else: ?>
                                <?php foreach ($activas as $act): ?>
                                    <tr>
                                        <td>
                                            <span class="fs-5 fw-bold text-primary">Hab. <?= htmlspecialchars($act['habitacion_numero']) ?></span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($act['tipo_nombre']) ?> - Piso <?= $act['piso'] ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($act['huesped_nombre']) ?></div>
                                            <small class="text-muted"><i class="bi bi-globe me-1"></i><?= htmlspecialchars($act['nacionalidad'] ?: 'Perú') ?></small>
                                        </td>
                                        <td><span class="badge text-bg-light border"><?= htmlspecialchars($act['numero_documento']) ?></span></td>
                                        <td><span class="text-success fw-semibold"><i class="bi bi-clock me-1"></i><?= fecha_es($act['fecha_checkin'], true) ?></span></td>
                                        <td><span class="text-danger fw-semibold"><?= fecha_es($act['fecha_salida']) ?></span></td>
                                        <td>
                                            <?php if ((float)$act['reserva_saldo'] > 0): ?>
                                                <span class="badge text-bg-danger fs-6"><?= dinero($act['reserva_saldo']) ?></span>
                                            <?php else: ?>
                                                <span class="badge text-bg-success"><i class="bi bi-check2"></i> Al día</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($act['observaciones'] ?: '-') ?></small></td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= url('recepcion/checkout?id=' . $act['id']) ?>" class="btn btn-outline-danger" title="Realizar Check-out">
                                                    <i class="bi bi-box-arrow-right me-1"></i> Check-out
                                                </a>
                                                <button type="button" class="btn btn-outline-primary btn-abrir-consumo" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalCargarConsumo"
                                                        data-reserva-id="<?= $act['reserva_id'] ?>"
                                                        data-habitacion="<?= htmlspecialchars($act['habitacion_numero']) ?>"
                                                        data-huesped="<?= htmlspecialchars($act['huesped_nombre']) ?>"
                                                        title="Cargar Consumo / Room Service">
                                                    <i class="bi bi-cart-plus me-1"></i> Consumo
                                                </button>
                                                <a href="<?= url('reservas/editar/' . $act['reserva_id']) ?>" class="btn btn-outline-secondary" title="Ver Cuenta">
                                                    <i class="bi bi-card-text"></i>
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
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h6 class="card-title mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Historial de Salidas / Estadías Finalizadas</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Habitación</th>
                                <th>Huésped</th>
                                <th>Ingreso</th>
                                <th>Salida Efectiva</th>
                                <th>Estado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $h): ?>
                                <tr>
                                    <td><?= $h['id'] ?></td>
                                    <td><strong>Hab. <?= htmlspecialchars($h['habitacion_numero']) ?></strong> (<?= htmlspecialchars($h['tipo_nombre']) ?>)</td>
                                    <td><?= htmlspecialchars($h['huesped_nombre']) ?></td>
                                    <td><?= fecha_es($h['fecha_checkin'], true) ?></td>
                                    <td><?= fecha_es($h['fecha_checkout'], true) ?></td>
                                    <td>
                                        <span class="badge <?= ($h['estado'] === 'activa') ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($h['estado'])) ?>
                                        </span>
                                    </td>
                                    <td><small class="text-muted"><?= htmlspecialchars($h['observaciones'] ?: '-') ?></small></td>
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
                        <label class="form-label fw-semibold small">Seleccionar Producto o Servicio <span class="text-danger">*</span></label>
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
                            <label class="form-label fw-semibold small">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad" id="modalConsumoCantidad" class="form-control" value="1" min="1" max="100" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Precio Unitario (S/.) <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-semibold small">Observaciones / Notas</label>
                        <input type="text" name="notas" class="form-control" placeholder="Ej: Solicitado por teléfono, entregado por mozo">
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary fw-semibold" id="btnGuardarConsumoModal">
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
