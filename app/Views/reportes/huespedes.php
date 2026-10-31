<?php partial('page_header', [
    'titulo'    => 'Reporte de Huéspedes Frecuentes',
    'icono'     => 'bi-people-fill',
    'subtitulo' => 'Fidelización de clientes, historial de visitas y gasto acumulado',
    'acciones'  => [
        ['texto' => 'Imprimir Reporte', 'imprimir' => true, 'icono' => 'bi-printer', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Ranking de Clientes por Visitas e Ingresos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Huésped</th>
                                <th>Documento</th>
                                <th>Contacto</th>
                                <th>Nacionalidad</th>
                                <th>Total Estadías</th>
                                <th>Última Visita</th>
                                <th class="text-end pe-3">Gasto Total Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($huespedes as $h): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= ($i <= 3) ? 'badge-soft-warning' : 'badge-soft-secondary' ?>">
                                            #<?= $i++ ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?></div>
                                    </td>
                                    <td><span class="badge badge-soft-secondary"><?= htmlspecialchars($h['tipo_documento'] . ' ' . $h['numero_documento']) ?></span></td>
                                    <td>
                                        <small class="d-block"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($h['telefono'] ?: '-') ?></small>
                                        <small class="text-muted"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($h['correo'] ?: '-') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($h['nacionalidad'] ?: 'Perú') ?></td>
                                    <td><span class="badge badge-soft-primary"><?= $h['total_reservas'] ?> reservas</span></td>
                                    <td><small class="text-muted"><?= fecha_es($h['ultima_visita']) ?></small></td>
                                    <td class="text-end pe-3 fw-bold text-success fs-6"><?= dinero($h['total_gastado']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
