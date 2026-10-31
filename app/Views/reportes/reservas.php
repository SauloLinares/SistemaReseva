<?php partial('page_header', [
    'titulo'    => 'Reporte de Reservas',
    'icono'     => 'bi-file-earmark-bar-graph',
    'subtitulo' => 'Análisis detallado de reservas por rango de fechas y estado',
    'acciones'  => [
        ['texto' => 'Imprimir Reporte', 'imprimir' => true, 'icono' => 'bi-printer', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Filtros del Reporte -->
        <div class="card mb-4 d-print-none">
            <div class="card-body">
                <form method="GET" action="<?= url('reportes/reservas') ?>" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label for="desde" class="form-label">Desde</label>
                        <input id="desde" type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($desde ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="hasta" class="form-label">Hasta</label>
                        <input id="hasta" type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" name="estado" class="form-select form-select-sm">
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Resultados (<?= count($reservas) ?> reservas encontradas)</h5>
                <?php
                    $totalMonto = 0;
                    foreach ($reservas as $r) $totalMonto += (float)$r['total'];
                ?>
                <span class="badge badge-soft-primary">Total Facturado: <?= dinero($totalMonto) ?></span>
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
