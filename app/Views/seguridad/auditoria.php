<?php partial('page_header', [
    'titulo'    => 'Auditoría del Sistema',
    'icono'     => 'bi-activity',
    'subtitulo' => 'Trazabilidad, registro de operaciones y eventos de seguridad',
    'acciones'  => [
        ['texto' => 'Imprimir Log', 'imprimir' => true, 'icono' => 'bi-printer', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-journal-text me-2"></i>Últimos Eventos Registrados (<?= count($registros) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha y Hora</th>
                                <th>Usuario</th>
                                <th>Módulo</th>
                                <th>Acción</th>
                                <th>Detalle del Evento</th>
                                <th>IP Origen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $reg): ?>
                                <tr>
                                    <td><?= $reg['id'] ?></td>
                                    <td><small class="text-muted"><?= fecha_es($reg['created_at'], true) ?></small></td>
                                    <td>
                                        <i class="bi bi-person me-1"></i><strong><?= htmlspecialchars($reg['usuario_nombre'] ?: 'Sistema') ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-secondary"><?= htmlspecialchars($reg['modulo']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-primary text-uppercase"><?= htmlspecialchars($reg['accion']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($reg['detalle']) ?></td>
                                    <td><small class="text-muted font-monospace"><?= htmlspecialchars($reg['ip'] ?: '127.0.0.1') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
