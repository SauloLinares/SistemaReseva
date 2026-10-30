<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-box-arrow-right text-danger me-2"></i>Recepción: Check-out</h3>
                <p class="text-muted mb-0 small">Liquidación de cuenta, cobro de saldo y liberación de habitación</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('recepcion/estadias') ?>" class="btn btn-outline-secondary btn-sm shadow-sm me-1">
                    <i class="bi bi-journal-bookmark me-1"></i> Control de Estadías
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

        <div class="row g-4">
            <!-- Formulario de Check-out -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-top border-4 border-danger">
                    <div class="card-header bg-body-tertiary">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-door-closed text-danger me-2"></i>Procesar Salida de Habitación</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('recepcion/procesarCheckout') ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Seleccionar Huésped / Habitación Alojada <span class="text-danger">*</span></label>
                                <select name="estancia_id" class="form-select select-search" required onchange="cambiarEstancia(this.value)" placeholder="Escribe el número de habitación, nombre de huésped o código...">
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
                                    <div class="alert alert-danger py-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Saldo Pendiente de Cobro:</strong>
                                            <span class="fs-4 fw-bold"><?= dinero($estanciaSeleccionada['reserva_saldo']) ?></span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-danger fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalCobroRapido">
                                                <i class="bi bi-cash-coin me-1"></i> Cobrar Ahora en Caja
                                            </button>
                                            <div class="form-check align-self-center ms-2">
                                                <input class="form-check-input" type="checkbox" name="confirmar_saldo" value="1" id="checkSaldo">
                                                <label class="form-check-label small" for="checkSaldo">
                                                    Permitir salida con saldo pendiente
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success py-2 small d-flex align-items-center">
                                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                        <div><strong>¡Cuenta al día!</strong> No existen saldos pendientes por concepto de habitación.</div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Observaciones de Salida / Estado de la Habitación</label>
                                <textarea name="observaciones" class="form-control" rows="3" placeholder="Ej: Se recibieron todas las llaves, toallas completas, sin desperfectos..."></textarea>
                            </div>

                            <div class="alert alert-info py-2 small d-flex align-items-center">
                                <i class="bi bi-stars me-2 fs-5"></i>
                                <div>Al confirmar, la habitación pasará automáticamente a estado <strong>LIMPIEZA</strong> y se generará una orden para el personal de limpieza.</div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-danger btn-lg fw-bold shadow-sm">
                                    <i class="bi bi-box-arrow-right me-2"></i>Finalizar Estadía y Liberar Habitación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Ficha Resumen -->
            <div class="col-12 col-lg-6">
                <?php if ($estanciaSeleccionada): ?>
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="bi bi-door-open me-2"></i>Habitación <?= htmlspecialchars($estanciaSeleccionada['habitacion_numero']) ?> (Piso <?= $estanciaSeleccionada['piso'] ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($estanciaSeleccionada['huesped_nombre']) ?></h5>
                            <div class="text-muted small mb-3">
                                Documento: <?= htmlspecialchars($estanciaSeleccionada['numero_documento']) ?> | Tel: <?= htmlspecialchars($estanciaSeleccionada['telefono'] ?: 'N/A') ?>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-muted d-block">Ingreso Registrado:</small>
                                        <strong class="text-success"><i class="bi bi-clock me-1"></i><?= fecha_es($estanciaSeleccionada['fecha_checkin'], true) ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-body-secondary">
                                        <small class="text-muted d-block">Código de Reserva:</small>
                                        <strong class="text-primary"><?= htmlspecialchars($estanciaSeleccionada['codigo_reserva']) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-body-secondary rounded border mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Costo Total Reserva:</span>
                                    <span class="fw-bold"><?= dinero($estanciaSeleccionada['reserva_total']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 text-success">
                                    <span>Total Pagado:</span>
                                    <span class="fw-bold"><?= dinero($estanciaSeleccionada['anticipo']) ?></span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Saldo Pendiente:</span>
                                    <span class="fw-bold fs-5 <?= ((float)$estanciaSeleccionada['reserva_saldo'] > 0) ? 'text-danger' : 'text-success' ?>">
                                        <?= dinero($estanciaSeleccionada['reserva_saldo']) ?>
                                    </span>
                                </div>
                            </div>

                            <a href="<?= url('reservas/editar/' . $estanciaSeleccionada['reserva_id']) ?>" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-card-text me-1"></i> Ver Cuenta Completa de la Reserva
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm border-dashed text-center p-5 text-muted">
                        <i class="bi bi-box-arrow-right display-3 mb-3 text-secondary"></i>
                        <h5>Selecciona un huésped o habitación para realizar el Check-out</h5>
                        <p class="small">El formulario cargará el saldo pendiente y el tiempo de estadía transcurrido.</p>
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
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold" id="modalCobroRapidoLabel">
                    <i class="bi bi-cash-stack me-2"></i>Registrar Pago / Liquidar Cuenta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                        <label class="form-label fw-semibold small">Monto a Cobrar (S/.) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text fw-bold text-success">S/.</span>
                            <input type="number" step="0.01" name="monto" id="inputMontoCobro" class="form-control fw-bold fs-4 text-success" value="<?= $estanciaSeleccionada['reserva_saldo'] ?>" required>
                        </div>
                        <div class="form-text">Saldo pendiente actual: <strong><?= dinero($estanciaSeleccionada['reserva_saldo']) ?></strong></div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Método de Pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago" class="form-select" required>
                                <option value="Efectivo" selected>Efectivo</option>
                                <option value="Yape">Yape</option>
                                <option value="Plin">Plin</option>
                                <option value="Tarjeta">Tarjeta Débito/Crédito</option>
                                <option value="Transferencia">Transferencia Bancaria</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Comprobante <span class="text-danger">*</span></label>
                            <select name="comprobante_tipo" class="form-select" required>
                                <option value="Boleta" selected>Boleta de Venta</option>
                                <option value="Factura">Factura</option>
                                <option value="Recibo de Caja">Recibo de Caja</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Notas / Observación</label>
                        <input type="text" name="notas" class="form-control" placeholder="Ej: Liquidación total en Check-out">
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success fw-bold" id="btnGuardarCobroModal">
                        <i class="bi bi-check-circle-fill me-1"></i> Cobrar e Imprimir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function cambiarEstancia(id) {
    if (id) {
        window.location.href = '<?= url("recepcion/checkout?id=") ?>' + id;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const formModalCobro = document.getElementById('formModalCobro');
    const btnGuardarCobro = document.getElementById('btnGuardarCobroModal');
    const modalCobroEl = document.getElementById('modalCobroRapido');
    const alertModalCobro = document.getElementById('alertModalCobro');

    if (formModalCobro && btnGuardarCobro) {
        formModalCobro.addEventListener('submit', function(e) {
            e.preventDefault();
            alertModalCobro.classList.add('d-none');
            alertModalCobro.textContent = '';

            const formData = new FormData(formModalCobro);
            const originalBtnHtml = btnGuardarCobro.innerHTML;

            btnGuardarCobro.disabled = true;
            btnGuardarCobro.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            fetch('<?= url("pagos/apiGuardar") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                btnGuardarCobro.disabled = false;
                btnGuardarCobro.innerHTML = originalBtnHtml;

                if (!data.success) {
                    alertModalCobro.textContent = data.mensaje || 'Error al procesar el pago.';
                    alertModalCobro.classList.remove('d-none');
                    return;
                }

                // Cerrar modal
                const modalInstance = bootstrap.Modal.getInstance(modalCobroEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Cobro Registrado!',
                        text: `Se emitió el comprobante ${data.comprobante_numero} con éxito.`,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-printer me-1"></i> Ver Comprobante',
                        cancelButtonText: 'Continuar Check-out',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#16a34a'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(data.url_comprobante, '_blank');
                        }
                        // Recargar para reflejar saldo 0 y cuenta al día
                        window.location.reload();
                    });
                } else {
                    window.location.reload();
                }
            })
            .catch(err => {
                btnGuardarCobro.disabled = false;
                btnGuardarCobro.innerHTML = originalBtnHtml;
                alertModalCobro.textContent = 'Ocurrió un error inesperado al procesar el pago.';
                alertModalCobro.classList.remove('d-none');
            });
        });
    }
});
</script>
