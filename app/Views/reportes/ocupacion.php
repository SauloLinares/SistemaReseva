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

        <!-- Gráfico / Tabla de Ocupación Diaria del Mes -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart-line text-primary me-2"></i>Ocupación Diaria del Mes (<?= htmlspecialchars($mes) ?>)
                </h5>
            </div>
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
                                <th style="width: 250px;">Barra de Ocupación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ocupacion['dias'] as $d): ?>
                                <tr>
                                    <td><strong><?= $d['dia'] ?></strong></td>
                                    <td><?= fecha_es($d['fecha']) ?></td>
                                    <td><span class="badge text-bg-danger"><?= $d['ocupadas'] ?></span></td>
                                    <td><span class="badge text-bg-success"><?= $d['libres'] ?></span></td>
                                    <td><strong><?= $d['porcentaje'] ?>%</strong></td>
                                    <td>
                                        <div class="progress" style="height: 14px;">
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
        </div>

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
                                        <span class="badge <?= ($pos === 1) ? 'text-bg-warning text-dark' : (($pos === 2) ? 'text-bg-secondary' : 'text-bg-light border') ?> fs-6">
                                            #<?= $pos++ ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold fs-5 text-primary">Hab. <?= htmlspecialchars($pop['numero']) ?></td>
                                    <td><?= htmlspecialchars($pop['tipo_nombre']) ?></td>
                                    <td>Piso <?= htmlspecialchars($pop['piso']) ?></td>
                                    <td><?= dinero($pop['precio_diario']) ?></td>
                                    <td><span class="badge text-bg-info text-white fs-6"><?= $pop['total_veces_reservada'] ?> veces</span></td>
                                    <td><strong><?= $pop['total_noches'] ?> noches</strong></td>
                                    <td class="text-end pe-3 fw-bold text-success fs-6"><?= dinero($pop['total_recaudado']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
