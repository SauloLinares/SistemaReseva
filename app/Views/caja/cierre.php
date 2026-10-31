<?php partial('page_header', [
    'titulo'              => 'Reporte Oficial de Cierre de Caja',
    'icono'               => 'bi-file-earmark-check',
    'subtitulo'           => 'Comprobante de arqueo, entrega de turno y conciliación física',
    'acciones'            => [
        ['texto' => 'Imprimir Reporte', 'imprimir' => true, 'icono' => 'bi-printer'],
        ['texto' => 'Volver a Caja', 'url' => 'caja', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
    'ocultar_al_imprimir' => true,
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12 col-lg-8 col-xl-7">
                <!-- Hoja de Arqueo Oficial Imprimible -->
                <div class="card border-0 mb-4" id="imprimible">
                    <div class="card-body p-4 p-md-5">

                        <!-- Encabezado de la Empresa -->
                        <div class="text-center border-bottom pb-4 mb-4">
                            <h4 class="fw-bold mb-1 text-uppercase text-dark">Hotel Manager & Suites</h4>
                            <p class="text-muted small mb-0">RUC: 20601234567 • Av. Principal 123, Miraflores, Lima</p>
                            <span class="badge bg-dark text-white px-3 py-1.5 mt-2 fw-semibold" style="letter-spacing: 0.05em;">
                                ARQUEO Y CIERRE DE CAJA OFICIAL #<?= sprintf('%04d', $turno['id']) ?>
                            </span>
                        </div>

                        <!-- Metadatos del Turno -->
                        <div class="row g-3 mb-4 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Recepcionista:</span>
                                <strong class="text-dark"><?= htmlspecialchars($turno['usuario_nombre']) ?></strong>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted d-block">Estado del Turno:</span>
                                <span class="badge bg-secondary-subtle text-secondary border px-2">CERRADO</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Apertura:</span>
                                <strong><?= date('d/m/Y h:i A', strtotime($turno['fecha_apertura'])) ?></strong>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted d-block">Cierre:</span>
                                <strong><?= date('d/m/Y h:i A', strtotime($turno['fecha_cierre'])) ?></strong>
                            </div>
                        </div>

                        <!-- 1. RESUMEN DE ARQUEO FÍSICO EN EFECTIVO -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 text-secondary" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                                1. Conciliación de Efectivo en Gaveta
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">(+) Fondo Inicial de Apertura:</td>
                                        <td class="text-end fw-semibold"><?= dinero($turno['monto_inicial']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">(+) Cobros de Reservas en Efectivo:</td>
                                        <td class="text-end fw-semibold text-success">+<?= dinero($turno['total_ventas_efectivo']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">(+) Ingresos Extraordinarios:</td>
                                        <td class="text-end fw-semibold text-primary">+<?= dinero($turno['total_ingresos_extra']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">(-) Salidas y Gastos de Caja Chica:</td>
                                        <td class="text-end fw-semibold text-danger">-<?= dinero($turno['total_egresos']) ?></td>
                                    </tr>
                                    <tr class="border-top border-dark-subtle">
                                        <td class="fw-bold pt-2">(=) TOTAL EFECTIVO ESPERADO:</td>
                                        <td class="text-end fw-bold pt-2 fs-6"><?= dinero($turno['monto_esperado_efectivo']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-primary">(=) EFECTIVO REAL ENTREGADO (FÍSICO):</td>
                                        <td class="text-end fw-bold text-primary fs-5"><?= dinero($turno['monto_real_efectivo']) ?></td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold pt-2">DIFERENCIA DE CAJA:</td>
                                        <td class="text-end fw-bold pt-2">
                                            <?php $dif = (float)$turno['diferencia']; ?>
                                            <?php if (abs($dif) < 0.01): ?>
                                                <span class="text-success"><i class="bi bi-check-circle me-1"></i>S/. 0.00 (Cuadrado)</span>
                                            <?php elseif ($dif > 0): ?>
                                                <span class="text-warning"><i class="bi bi-plus-circle me-1"></i>+<?= dinero($dif) ?> (Sobrante)</span>
                                            <?php else: ?>
                                                <span class="text-danger"><i class="bi bi-dash-circle me-1"></i><?= dinero($dif) ?> (Faltante)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 2. VENTAS POR CANALES DIGITALES (BANCOS) -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3 text-secondary" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                                2. Pagos Recibidos en Cuentas del Hotel (Bancos)
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted"><i class="bi bi-credit-card me-1"></i>Tarjetas de Crédito / Débito:</td>
                                        <td class="text-end fw-semibold"><?= dinero($resumen['total_tarjetas']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class="bi bi-phone me-1"></i>Billeteras Digitales (Yape / Plin):</td>
                                        <td class="text-end fw-semibold"><?= dinero($resumen['total_yape_plin']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><i class="bi bi-bank me-1"></i>Transferencias Bancarias:</td>
                                        <td class="text-end fw-semibold"><?= dinero($resumen['total_transferencias']) ?></td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold pt-2">TOTAL RECAUDADO EN BANCOS:</td>
                                        <td class="text-end fw-bold pt-2"><?= dinero($turno['total_ventas_digital']) ?></td>
                                    </tr>
                                    <tr class="border-top border-dark border-2">
                                        <td class="fw-bold fs-6 pt-2 text-dark">GRAN TOTAL FACTURADO EN EL TURNO:</td>
                                        <td class="text-end fw-bold fs-5 pt-2 text-primary">
                                            <?= dinero($turno['total_ventas_efectivo'] + $turno['total_ventas_digital']) ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Observaciones si existen -->
                        <?php if (!empty($turno['observaciones_cierre'])): ?>
                            <div class="mb-4 p-3 bg-light rounded-3 border">
                                <small class="text-muted fw-bold d-block mb-1">Observaciones de Entrega de Turno:</small>
                                <p class="mb-0 small text-dark"><?= nl2br(htmlspecialchars($turno['observaciones_cierre'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Firmas de Conformidad -->
                        <div class="row pt-5 mt-4 text-center">
                            <div class="col-6">
                                <div class="border-top border-dark pt-2 mx-3">
                                    <strong class="d-block small text-dark"><?= htmlspecialchars($turno['usuario_nombre']) ?></strong>
                                    <span class="text-xs text-muted">Recepcionista Saliente</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border-top border-dark pt-2 mx-3">
                                    <strong class="d-block small text-dark">Administración / Receptor</strong>
                                    <span class="text-xs text-muted">Firma y Sello de Recepción</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
