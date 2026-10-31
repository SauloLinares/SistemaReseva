<?php partial('page_header', [
    'titulo'    => 'Turno de Caja Activo',
    'icono'     => 'bi-cash-coin',
    'insignia'  => ['texto' => 'Abierto #' . $turno['id'], 'variante' => 'success'],
    'subtitulo' => 'Responsable: ' . $turno['usuario_nombre'] . ' • Apertura: ' . date('d/m/Y h:i A', strtotime($turno['fecha_apertura'])),
    'acciones'  => [
        ['texto' => 'Registrar Gasto', 'modal' => '#modalEgreso', 'icono' => 'bi-dash-circle', 'variante' => 'outline-secondary'],
        ['texto' => 'Ingreso Extra', 'modal' => '#modalIngreso', 'icono' => 'bi-plus-circle', 'variante' => 'outline-secondary'],
        ['texto' => 'Cerrar Turno', 'modal' => '#modalCierreCaja', 'icono' => 'bi-lock', 'variante' => 'outline-danger'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Métricas Principales del Turno en Tiempo Real -->
        <div class="row g-3 mb-4">
            <!-- 1. Fondo Inicial -->
            <div class="col-6 col-xl">
                <div class="cash-stat">
                    <span class="cash-stat-label">Fondo inicial</span>
                    <span class="cash-stat-valor"><?= dinero($resumen['fondo_inicial']) ?></span>
                    <span class="cash-stat-nota">Base para cambio</span>
                </div>
            </div>

            <!-- 2. Ventas en Efectivo -->
            <div class="col-6 col-xl">
                <div class="cash-stat">
                    <span class="cash-stat-label">Cobros en efectivo</span>
                    <span class="cash-stat-valor">+ <?= dinero($resumen['total_efectivo']) ?></span>
                    <span class="cash-stat-nota">Ingresado al cajón</span>
                </div>
            </div>

            <!-- 3. Pagos Digitales (Bancos / Tarjetas): no entran al cajón -->
            <div class="col-6 col-xl">
                <div class="cash-stat">
                    <span class="cash-stat-label">Pagos digitales</span>
                    <span class="cash-stat-valor"><?= dinero($resumen['total_digital']) ?></span>
                    <span class="cash-stat-nota">Tarjetas <?= dinero($resumen['total_tarjetas']) ?> · Yape/Plin <?= dinero($resumen['total_yape_plin']) ?></span>
                </div>
            </div>

            <!-- 4. Egresos / Gastos Menores -->
            <div class="col-6 col-xl">
                <div class="cash-stat">
                    <span class="cash-stat-label">Salidas / gastos</span>
                    <span class="cash-stat-valor<?= (float) $resumen['total_egresos'] > 0 ? ' text-danger' : '' ?>">− <?= dinero($resumen['total_egresos']) ?></span>
                    <span class="cash-stat-nota">Caja chica del turno</span>
                </div>
            </div>

            <!-- 5. TOTAL EFECTIVO ESPERADO EN CAJÓN: el dato que se compara al cerrar -->
            <div class="col-12 col-xl">
                <div class="cash-stat cash-stat-total">
                    <span class="cash-stat-label">Efectivo en cajón</span>
                    <span class="cash-stat-valor">= <?= dinero($resumen['monto_esperado_efectivo']) ?></span>
                    <span class="cash-stat-nota">Esperado al cierre</span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Columna Izquierda: Tabla Cronológica de Movimientos -->
            <div class="col-12 col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history text-primary me-2"></i>Movimientos del Turno en Tiempo Real
                        </h5>
                        <span class="badge badge-soft-primary">
                            <?= count($resumen['pagos']) + count($resumen['movimientos']) ?> registros
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Hora</th>
                                        <th>Tipo / Concepto</th>
                                        <th>Método</th>
                                        <th>Comprobante</th>
                                        <th class="text-end pe-3">Monto</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- 1. Fondo Inicial -->
                                    <tr class="table-light-subtle">
                                        <td class="ps-3 text-nowrap text-body-secondary"><?= date('h:i A', strtotime($turno['fecha_apertura'])) ?></td>
                                        <td>
                                            <span class="badge badge-soft-secondary me-1">Base</span>
                                            <strong>Fondo Inicial de Caja</strong>
                                            <small class="d-block text-muted">Apertura por <?= htmlspecialchars($turno['usuario_nombre']) ?></small>
                                        </td>
                                        <td class="text-nowrap">Efectivo</td>
                                        <td class="text-muted small">--</td>
                                        <td class="text-end pe-3 text-nowrap fw-semibold"><?= dinero($resumen['fondo_inicial']) ?></td>
                                        <td class="text-center text-muted"><i class="bi bi-lock"></i></td>
                                    </tr>

                                    <!-- 2. Pagos de Reservas / Estancias -->
                                    <?php foreach ($resumen['pagos'] as $p): ?>
                                        <tr>
                                            <td class="ps-3 text-nowrap text-body-secondary"><?= date('h:i A', strtotime($p['fecha_pago'])) ?></td>
                                            <td>
                                                <span class="badge badge-soft-success me-1">Cobro</span>
                                                <strong>Reserva <?= htmlspecialchars($p['codigo_reserva'] ?? 'S/C') ?></strong>
                                                <small class="d-block text-muted"><?= htmlspecialchars($p['huesped_nombre'] ?? 'Huésped') ?> (<?= ucfirst($p['tipo']) ?>)</small>
                                            </td>
                                            <td class="text-nowrap"><?= e($p['metodo_pago']) ?></td>
                                            <td>
                                                <a href="<?= url('pagos/comprobante/' . $p['id']) ?>" target="_blank" class="text-decoration-none small">
                                                    <i class="bi bi-file-earmark-text me-1"></i><?= htmlspecialchars($p['comprobante_tipo'] . ' ' . $p['comprobante_numero']) ?>
                                                </a>
                                            </td>
                                            <td class="text-end pe-3 text-nowrap fw-semibold text-success">+ <?= dinero($p['monto']) ?></td>
                                            <td class="text-center">
                                                <a href="<?= url('pagos/comprobante/' . $p['id']) ?>" target="_blank" class="btn btn-icon btn-outline-secondary" title="Ver comprobante">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <!-- 3. Movimientos de Caja Chica (Egresos e Ingresos Extra) -->
                                    <?php foreach ($resumen['movimientos'] as $m): ?>
                                        <tr>
                                            <td class="ps-3 text-nowrap text-body-secondary"><?= date('h:i A', strtotime($m['created_at'])) ?></td>
                                            <td>
                                                <?php if ($m['tipo'] === 'egreso'): ?>
                                                    <span class="badge badge-soft-danger me-1">Gasto</span>
                                                <?php else: ?>
                                                    <span class="badge badge-soft-primary me-1">Extra</span>
                                                <?php endif; ?>
                                                <strong><?= htmlspecialchars($m['concepto']) ?></strong>
                                                <small class="d-block text-muted"><?= htmlspecialchars($m['categoria'] ?: 'General') ?></small>
                                            </td>
                                            <td class="text-nowrap"><?= e($m['metodo_pago']) ?></td>
                                            <td class="text-muted small">
                                                <?= htmlspecialchars($m['comprobante'] ?: '--') ?>
                                            </td>
                                            <td class="text-end pe-3 text-nowrap fw-semibold <?= $m['tipo'] === 'egreso' ? 'text-danger' : 'text-success' ?>">
                                                <?= $m['tipo'] === 'egreso' ? '−' : '+' ?> <?= dinero($m['monto']) ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= url('caja/eliminarMovimiento/' . $m['id']) ?>" class="btn btn-icon btn-outline-danger" data-confirm="¿Deseas eliminar este movimiento de caja chica?" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if (empty($resumen['pagos']) && empty($resumen['movimientos'])): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                                Aún no se registran cobros ni gastos en este turno.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Resumen de Medios de Pago y Accesos Rápidos -->
            <div class="col-12 col-lg-4">
                <!-- Desglose por Medio de Pago -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-pie-chart text-info me-2"></i>Desglose de Ingresos por Canal
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-cash text-success me-2"></i>Efectivo en Recepción:</span>
                                <strong class="text-success"><?= dinero($resumen['total_efectivo']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-credit-card text-primary me-2"></i>Tarjetas Débito / Crédito:</span>
                                <strong><?= dinero($resumen['total_tarjetas']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-phone text-purple me-2"></i>Billeteras Digitales (Yape / Plin):</span>
                                <strong><?= dinero($resumen['total_yape_plin']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-bank text-info me-2"></i>Transferencias Bancarias:</span>
                                <strong><?= dinero($resumen['total_transferencias']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 pt-2 border-top">
                                <span class="fw-bold">Total Facturado en Turno:</span>
                                <strong class="fs-6 text-primary"><?= dinero($resumen['total_recaudado']) ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tarjeta de Arqueo Inmediato -->
                <div class="card border-primary border-opacity-25 mb-4">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-safe2 fs-1 text-primary d-block mb-2"></i>
                        <h5 class="fw-bold">¿Listo para entregar el turno?</h5>
                        <p class="text-muted small mb-3">
                            Realiza el conteo de billetes y monedas en tu gaveta para comparar con el sistema y generar el cierre oficial.
                        </p>
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalCierreCaja">
                            <i class="bi bi-check2-circle me-1"></i> Iniciar Arqueo y Cierre
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL 1: REGISTRAR EGRESO (GASTO DE CAJA CHICA)           -->
<!-- ========================================================== -->
<div class="modal fade" id="modalEgreso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-dash-circle me-2"></i>Registrar Salida / Gasto de Caja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="<?= url('caja/guardarMovimiento') ?>" method="POST">
                <input type="hidden" name="tipo" value="egreso">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Concepto o Motivo del Gasto <span class="text-danger">*</span></label>
                        <input type="text" name="concepto" class="form-control" placeholder="Ej: Compra de bidón de agua, taxi urgente, bombillas..." required autofocus>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Monto (S/.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">S/.</span>
                                <input type="number" step="0.10" min="0.10" name="monto" class="form-control fw-bold text-danger" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Categoría</label>
                            <select name="categoria" class="form-select">
                                <option value="Insumos">Insumos de Recepción</option>
                                <option value="Limpieza">Limpieza y Aseo</option>
                                <option value="Mantenimiento">Mantenimiento Rápido</option>
                                <option value="Transporte">Transporte / Envíos</option>
                                <option value="Alimentos">Cafetería / Agua</option>
                                <option value="Otro">Otro Gasto</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nº Comprobante / Recibo (Opcional)</label>
                        <input type="text" name="comprobante" class="form-control form-control-sm" placeholder="Ej: Ticket 04829, Boleta B001-291">
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-check-circle me-1"></i> Registrar Gasto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL 2: REGISTRAR INGRESO EXTRAORDINARIO                  -->
<!-- ========================================================== -->
<div class="modal fade" id="modalIngreso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Registrar Ingreso Extra a Caja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="<?= url('caja/guardarMovimiento') ?>" method="POST">
                <input type="hidden" name="tipo" value="ingreso">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Concepto del Ingreso <span class="text-danger">*</span></label>
                        <input type="text" name="concepto" class="form-control" placeholder="Ej: Inyección de sencillo por gerencia, cobro no tarifario..." required autofocus>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Monto (S/.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">S/.</span>
                                <input type="number" step="0.10" min="0.10" name="monto" class="form-control fw-bold text-success" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Método</label>
                            <select name="metodo_pago" class="form-select">
                                <option value="Efectivo" selected>Efectivo</option>
                                <option value="Yape/Plin">Yape/Plin</option>
                                <option value="Transferencia Bancaria">Transferencia</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Referencia / Comprobante (Opcional)</label>
                        <input type="text" name="comprobante" class="form-control form-control-sm" placeholder="Ej: Recibo Interno 001">
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Registrar Ingreso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL 3: ARQUEO Y CIERRE DE TURNO                         -->
<!-- ========================================================== -->
<div class="modal fade" id="modalCierreCaja" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-lock-fill me-2"></i>Arqueo y Cierre Oficial de Turno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="<?= url('caja/cerrar') ?>" method="POST" id="formCierreCaja">
                <div class="modal-body p-4">
                    <!-- Resumen de Cuadre Esperado -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-3 border text-center">
                                <small class="text-muted d-block mb-1">Fondo Inicial</small>
                                <strong class="fs-5 text-secondary"><?= dinero($resumen['fondo_inicial']) ?></strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-3 border text-center">
                                <small class="text-muted d-block mb-1">Cobros Efectivo Neto</small>
                                <strong class="fs-5 text-success">+<?= dinero($resumen['total_efectivo'] + $resumen['total_ingresos_extra'] - $resumen['total_egresos'] - $resumen['total_devoluciones']) ?></strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-primary-subtle border border-primary-subtle rounded-3 text-center">
                                <small class="text-primary fw-semibold d-block mb-1">Total Esperado en Gaveta</small>
                                <strong class="fs-4 text-primary" id="montoEsperadoSpan">S/. <?= number_format($resumen['monto_esperado_efectivo'], 2) ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Input de Efectivo Real Contado -->
                    <div class="mb-3">
                        <label class="form-label">Ingresa el Efectivo Físico Contado en tu Cajón (S/.) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white fw-bold">S/.</span>
                            <input type="number" step="0.10" min="0" name="monto_real_efectivo" id="monto_real_efectivo" class="form-control fw-bold fs-3 text-success" value="<?= number_format($resumen['monto_esperado_efectivo'], 2, '.', '') ?>" required autofocus>
                            <button type="button" class="btn btn-outline-secondary" id="btnCopiarEsperado" title="Usar monto exacto esperado">
                                <i class="bi bi-magic me-1"></i> Monto Exacto
                            </button>
                        </div>
                    </div>

                    <!-- Alerta Dinámica de Diferencia -->
                    <div id="alertaDiferencia" class="alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-5" id="iconoDif"></i>
                        <span id="textoDif"><strong>¡Caja Cuadrada!</strong> El conteo coincide exactamente con los registros del sistema.</span>
                    </div>

                    <!-- Observaciones de Cierre -->
                    <div class="mb-2">
                        <label class="form-label">Observaciones de Cierre / Novedades</label>
                        <textarea name="observaciones_cierre" class="form-control form-control-sm" rows="2" placeholder="Ej: Se deja sobre con S/. 450 en caja fuerte, se entrega turno a María..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" data-confirm="¿Confirmas el cierre del turno? Esta acción no se puede deshacer y se generará el arqueo oficial.">
                        <i class="bi bi-lock-fill me-1"></i> Confirmar y Cerrar Turno
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const esperado = <?= (float)$resumen['monto_esperado_efectivo'] ?>;
    const inputReal = document.getElementById('monto_real_efectivo');
    const alertaDif = document.getElementById('alertaDiferencia');
    const iconoDif = document.getElementById('iconoDif');
    const textoDif = document.getElementById('textoDif');
    const btnCopiar = document.getElementById('btnCopiarEsperado');

    function calcularDiferencia() {
        const real = parseFloat(inputReal.value || 0);
        const dif = real - esperado;

        if (Math.abs(dif) < 0.01) {
            alertaDif.className = 'alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2';
            iconoDif.className = 'bi bi-check-circle-fill fs-5 text-success';
            textoDif.innerHTML = '<strong>¡Caja Cuadrada!</strong> El conteo coincide exactamente con los registros del sistema (Diferencia: S/. 0.00).';
        } else if (dif > 0) {
            alertaDif.className = 'alert alert-warning py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2';
            iconoDif.className = 'bi bi-plus-circle-fill fs-5 text-warning';
            textoDif.innerHTML = `<strong>Sobrante de caja: +S/. ${dif.toFixed(2)}</strong>. Hay más dinero físico en gaveta del registrado.`;
        } else {
            alertaDif.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2';
            iconoDif.className = 'bi bi-exclamation-triangle-fill fs-5 text-danger';
            textoDif.innerHTML = `<strong>Faltante de caja: -S/. ${Math.abs(dif).toFixed(2)}</strong>. Falta dinero físico respecto a lo esperado.`;
        }
    }

    if (inputReal) {
        inputReal.addEventListener('input', calcularDiferencia);
    }

    if (btnCopiar) {
        btnCopiar.addEventListener('click', function() {
            inputReal.value = esperado.toFixed(2);
            calcularDiferencia();
        });
    }
});
</script>
