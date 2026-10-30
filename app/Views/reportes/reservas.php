<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Reporte de Reservas</h3>
                <p class="text-muted mb-0 small">Análisis detallado de reservas por rango de fechas y estado</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimir Reporte
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Filtros del Reporte -->
        <div class="card shadow-sm mb-4 d-print-none">
            <div class="card-body">
                <form method="GET" action="<?= url('reportes/reservas') ?>" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Desde</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($desde ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Hasta</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">-- Todos los estados --</option>
                            <option value="confirmada" <?= ($estado === 'confirmada') ? 'selected' : '' ?>>Confirmada</option>
                            <option value="pendiente" <?= ($estado === 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                            <option value="en_estadia" <?= ($estado === 'en_estadia') ? 'selected' : '' ?>>En Estadía</option>
                            <option value="finalizada" <?= ($estado === 'finalizada') ? 'selected' : '' ?>>Finalizada</option>
                            <option value="cancelada" <?= ($estado === 'cancelada') ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                        <a href="<?= url('reportes/reservas') ?>" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla del Reporte -->
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">Resultados (<?= count($reservas) ?> reservas encontradas)</h5>
                <?php
                    $totalMonto = 0;
                    foreach ($reservas as $r) $totalMonto += (float)$r['total'];
                ?>
                <span class="badge text-bg-primary fs-6 px-3 py-2">Total Facturado: <?= dinero($totalMonto) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Huésped</th>
                                <th>Habitaciones</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Noches</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $r): ?>
                                <tr>
                                    <td><span class="fw-bold text-primary"><?= htmlspecialchars($r['codigo_reserva']) ?></span></td>
                                    <td><?= htmlspecialchars($r['huesped_nombre']) ?></td>
                                    <td><?= htmlspecialchars($r['habitaciones'] ?: '-') ?></td>
                                    <td><?= fecha_es($r['fecha_entrada']) ?></td>
                                    <td><?= fecha_es($r['fecha_salida']) ?></td>
                                    <td><?= $r['noches'] ?> n</td>
                                    <td><?= badge_estado_reserva($r['estado']) ?></td>
                                    <td class="text-end pe-3 fw-bold"><?= dinero($r['total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
