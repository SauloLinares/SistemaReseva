<?php partial('page_header', [
    'titulo'    => 'Reporte de Ocupación',
    'icono'     => 'bi-pie-chart',
    'subtitulo' => 'Porcentajes de ocupación por período y habitaciones más demandadas',
    'extra'     => '<form method="GET" action="' . e(url('reportes/ocupacion')) . '" class="d-inline-flex gap-2">'
                 . '<input type="month" name="mes" class="form-control form-control-sm" aria-label="Mes del reporte" value="' . e($mes) . '" onchange="this.form.submit()">'
                 . '</form>',
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <?php
            $dias = $ocupacion['dias'];
            $porcentajes = array_map(fn($d) => (float) $d['porcentaje'], $dias);
            $promedio = $dias ? round(array_sum($porcentajes) / count($dias), 1) : 0;
            $pico = $dias ? $dias[array_search(max($porcentajes), $porcentajes)] : null;
            $diasConOcupacion = count(array_filter($porcentajes, fn($p) => $p > 0));
            // Escala del gráfico: el día más alto llega casi al tope (mínimo 10% para que barras bajas se distingan)
            $escala = max(10, max($porcentajes ?: [0]));
        ?>

        <!-- Resumen del mes -->
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-4">
                <div class="cash-stat">
                    <span class="cash-stat-label">Ocupación promedio</span>
                    <span class="cash-stat-valor"><?= $promedio ?>%</span>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="cash-stat">
                    <span class="cash-stat-label">Día de mayor ocupación</span>
                    <span class="cash-stat-valor"><?= $pico ? e($pico['porcentaje']) . '%' : '—' ?></span>
                    <span class="cash-stat-nota"><?= $pico && $pico['porcentaje'] > 0 ? fecha_es($pico['fecha']) . ' · ' . (int) $pico['ocupadas'] . ' hab.' : 'Sin ocupación' ?></span>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="cash-stat">
                    <span class="cash-stat-label">Días con ocupación</span>
                    <span class="cash-stat-valor"><?= $diasConOcupacion ?> <span class="fs-6 fw-normal text-body-secondary">de <?= count($dias) ?></span></span>
                </div>
            </div>
        </div>

        <!-- Gráfico de Ocupación Diaria del Mes -->
        <div class="card mb-4">
            <?php partial('card_header', ['titulo' => 'Ocupación diaria (' . $mes . ')', 'icono' => 'bi-bar-chart-line']) ?>
            <div class="card-body">
                <div class="grafico-dias" role="img" aria-label="Porcentaje de habitaciones ocupadas por día del mes; el detalle está en la tabla desplegable">
                    <?php foreach ($dias as $d): ?>
                        <?php $alto = $d['porcentaje'] > 0 ? max(2, round(($d['porcentaje'] / $escala) * 100)) : 0; ?>
                        <div class="grafico-dia" title="<?= fecha_es($d['fecha']) ?>: <?= (int) $d['ocupadas'] ?> ocupadas (<?= e($d['porcentaje']) ?>%)">
                            <div class="grafico-barra-zona">
                                <?php if ($d['porcentaje'] > 0): ?>
                                    <span class="grafico-valor"><?= e($d['porcentaje']) ?>%</span>
                                <?php endif; ?>
                                <div class="grafico-barra" style="height: <?= $alto ?>%"></div>
                            </div>
                            <span class="grafico-etiqueta"><?= (int) $d['dia'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Detalle numérico (desplegable) -->
        <details class="card mb-4 detalle-desplegable">
            <summary class="card-header">
                <span class="card-title mb-0"><i class="bi bi-table me-2"></i>Ver detalle por día</span>
            </summary>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Día</th>
                                <th>Fecha</th>
                                <th>Habitaciones Ocupadas</th>
                                <th>Habitaciones Libres</th>
                                <th>% Ocupación</th>
                                <th class="col-w-xl">Barra de Ocupación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ocupacion['dias'] as $d): ?>
                                <tr>
                                    <td><strong><?= $d['dia'] ?></strong></td>
                                    <td><?= fecha_es($d['fecha']) ?></td>
                                    <td><span class="badge badge-soft-danger"><?= $d['ocupadas'] ?></span></td>
                                    <td><span class="badge badge-soft-success"><?= $d['libres'] ?></span></td>
                                    <td><strong><?= $d['porcentaje'] ?>%</strong></td>
                                    <td>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar <?= ($d['porcentaje'] >= 80) ? 'bg-danger' : (($d['porcentaje'] >= 50) ? 'bg-warning' : 'bg-success') ?>" 
                                                 role="progressbar" style="width: <?= $d['porcentaje'] ?>%;">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </details>

        <!-- Habitaciones más solicitadas -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-trophy text-warning me-2"></i>Habitaciones Más Solicitadas (Ranking)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ranking</th>
                                <th>Habitación</th>
                                <th>Tipo</th>
                                <th>Piso</th>
                                <th>Tarifa Diaria</th>
                                <th>Veces Reservada</th>
                                <th>Total Noches Ocupadas</th>
                                <th class="text-end pe-3">Total Recaudado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $pos = 1; foreach ($populares as $pop): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= ($pos === 1) ? 'badge-soft-warning' : (($pos === 2) ? 'badge-soft-primary' : 'badge-soft-secondary') ?>">
                                            #<?= $pos++ ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-nowrap">Hab. <?= e($pop['numero']) ?></td>
                                    <td><?= htmlspecialchars($pop['tipo_nombre']) ?></td>
                                    <td>Piso <?= htmlspecialchars($pop['piso']) ?></td>
                                    <td><?= dinero($pop['precio_diario']) ?></td>
                                    <td><span class="badge badge-soft-info"><?= $pop['total_veces_reservada'] ?> veces</span></td>
                                    <td><strong><?= $pop['total_noches'] ?> noches</strong></td>
                                    <td class="text-end pe-3 text-nowrap fw-semibold"><?= dinero($pop['total_recaudado']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
