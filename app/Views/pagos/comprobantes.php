<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-receipt text-primary me-2"></i>Comprobantes de Pago</h3>
                <p class="text-muted mb-0 small">Catálogo de boletas, facturas y recibos emitidos</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('pagos/crear') ?>" class="btn btn-success btn-sm shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Emitir Comprobante
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-printer me-2"></i>Documentos Emitidos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo Comprobante</th>
                                <th>Número</th>
                                <th>Reserva</th>
                                <th>Huésped</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Fecha Emisión</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $p): ?>
                                <tr>
                                    <td><span class="badge text-bg-secondary text-uppercase"><?= htmlspecialchars($p['comprobante_tipo']) ?></span></td>
                                    <td><span class="fw-bold text-primary"><?= htmlspecialchars($p['comprobante_numero']) ?></span></td>
                                    <td><?= htmlspecialchars($p['codigo_reserva']) ?></td>
                                    <td><?= htmlspecialchars($p['huesped_nombre']) ?></td>
                                    <td><span class="fw-bold text-success fs-6"><?= dinero($p['monto']) ?></span></td>
                                    <td><?= htmlspecialchars($p['metodo_pago']) ?></td>
                                    <td><small class="text-muted"><?= fecha_es($p['fecha_pago'], true) ?></small></td>
                                    <td class="text-end pe-3">
                                        <a href="<?= url('pagos/comprobante/' . $p['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> Ver / Imprimir
                                        </a>
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
