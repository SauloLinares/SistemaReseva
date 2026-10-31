<?php partial('page_header', [
    'titulo'    => 'Reporte de Reservas',
    'icono'     => 'bi-file-earmark-bar-graph',
    'subtitulo' => 'Análisis detallado de reservas por rango de fechas y estado',
    'acciones'  => [
        ['texto' => 'Exportar', 'exportar' => '#tablaReporteReservas', 'nombre' => 'reporte-reservas', 'icono' => 'bi-download', 'variante' => 'outline-secondary'],
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

        <?php
            // Las canceladas no son venta: se cuentan aparte
            $validas = array_filter($reservas, fn($r) => $r['estado'] !== 'cancelada');
            $canceladas = count($reservas) - count($validas);
            $totalMonto = array_sum(array_map(fn($r) => (float)$r['total'], $validas));
            $nochesVendidas = array_sum(array_map(fn($r) => (int)$r['noches'], $validas));
            $ticketPromedio = $validas ? $totalMonto / count($validas) : 0;
            $tasaCancelacion = $reservas ? round($canceladas * 100 / count($reservas), 1) : 0;
        ?>
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="cash-stat">
                    <span class="cash-stat-label">Total facturado</span>
                    <span class="cash-stat-valor"><?= dinero($totalMonto) ?></span>
                    <span class="cash-stat-nota"><?= count($validas) ?> reservas, sin canceladas</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="cash-stat">
                    <span class="cash-stat-label">Noches vendidas</span>
                    <span class="cash-stat-valor"><?= $nochesVendidas ?></span>
                    <span class="cash-stat-nota"><?= $validas ? round($nochesVendidas / count($validas), 1) : 0 ?> noches por reserva</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="cash-stat">
                    <span class="cash-stat-label">Ticket promedio</span>
                    <span class="cash-stat-valor"><?= dinero($ticketPromedio) ?></span>
                    <span class="cash-stat-nota"><?= $nochesVendidas ? dinero($totalMonto / $nochesVendidas) . ' por noche' : '—' ?></span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="cash-stat">
                    <span class="cash-stat-label">Canceladas</span>
                    <span class="cash-stat-valor"><?= $canceladas ?> <span class="fs-6 fw-normal text-body-secondary"><?= $tasaCancelacion ?>%</span></span>
                    <span class="cash-stat-nota">del total del período</span>
                </div>
            </div>
        </div>

        <!-- Tabla del Reporte -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Resultados (<?= count($reservas) ?> reservas encontradas)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tablaReporteReservas" class="table table-hover align-middle mb-0">
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
