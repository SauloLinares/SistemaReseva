<?php partial('page_header', [
    'titulo'    => 'Historial de Huéspedes',
    'icono'     => 'bi-clock-history',
    'subtitulo' => 'Selecciona un huésped para ver su historial completo de estadías',
    'acciones'  => [
        ['texto' => 'Directorio de Huéspedes', 'url' => 'huespedes', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-people me-2"></i>Todos los Huéspedes y Estadías</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th>Huésped</th>
                                <th>Documento</th>
                                <th>Teléfono</th>
                                <th>Total Estadías</th>
                                <th>Última Estadía</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($huespedes as $h): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?></td>
                                    <td><span class="badge badge-soft-secondary"><?= htmlspecialchars($h['tipo_documento'] . ' ' . $h['numero_documento']) ?></span></td>
                                    <td><?= htmlspecialchars($h['telefono'] ?: '-') ?></td>
                                    <td><span class="badge badge-soft-primary"><?= $h['total_reservas'] ?></span></td>
                                    <td><?= fecha_es($h['ultima_estadia']) ?></td>
                                    <td class="text-end pe-3">
                                        <a href="<?= url('huespedes/historial/' . $h['id']) ?>" class="btn btn-sm btn-primary btn-with-text text-nowrap" title="Ver Historial de Estadías">
                                            <i class="bi bi-clock-history me-1"></i> Ver Estadías
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
