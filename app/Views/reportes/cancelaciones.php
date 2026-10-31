<?php partial('page_header', [
    'titulo'    => 'Reporte de Cancelaciones',
    'icono'     => 'bi-x-octagon',
    'subtitulo' => 'Auditoría y análisis de reservas canceladas y motivos',
    'acciones'  => [
        ['texto' => 'Imprimir Reporte', 'imprimir' => true, 'icono' => 'bi-printer', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card border-danger">
            <div class="card-header bg-danger-subtle text-danger-emphasis d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-calendar-x me-2"></i>Reservas Canceladas (<?= count($cancelaciones) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código Reserva</th>
                                <th>Huésped</th>
                                <th>Contacto</th>
                                <th>Fechas Anuladas</th>
                                <th>Total Reserva</th>
                                <th>Motivo de Cancelación</th>
                                <th>Fecha Cancelación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cancelaciones)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">No se registran cancelaciones en el sistema.</td></tr>
                            <?php else: ?>
                                <?php foreach ($cancelaciones as $c): ?>
                                    <tr>
                                        <td><span class="badge text-bg-danger fw-bold"><?= htmlspecialchars($c['codigo_reserva']) ?></span></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($c['huesped_nombre']) ?></div>
                                            <small class="text-muted">Doc: <?= htmlspecialchars($c['numero_documento']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($c['telefono'] ?: '-') ?></td>
                                        <td><?= fecha_es($c['fecha_entrada']) ?> al <?= fecha_es($c['fecha_salida']) ?></td>
                                        <td><?= dinero($c['total']) ?></td>
                                        <td>
                                            <div class="text-danger small fw-semibold">
                                                <i class="bi bi-chat-left-quote me-1"></i><?= htmlspecialchars($c['motivo_cancelacion'] ?: 'Sin motivo registrado') ?>
                                            </div>
                                        </td>
                                        <td><small class="text-muted"><?= fecha_es($c['updated_at'], true) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
